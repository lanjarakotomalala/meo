<?php

namespace Meo\Abonnements;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Administrative commands. Renewals and card charges remain owned by WCS.
 */
final class Actions
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function allowed($subscription, string $action): bool
    {
        if (!$subscription instanceof \WC_Subscription) {
            return false;
        }

        switch ($action) {
            case 'pause':
                return $subscription->has_status('active')
                    && $subscription->can_be_updated_to('on-hold')
                    && $subscription->payment_method_supports('subscription_suspension');
            case 'resume':
                return $subscription->has_status('on-hold')
                    && $subscription->can_be_updated_to('active')
                    && $subscription->payment_method_supports('subscription_reactivation');
            case 'cancel':
                return $subscription->has_status(['active', 'on-hold', 'pending-cancel'])
                    && $subscription->can_be_updated_to('cancelled')
                    && $subscription->payment_method_supports('subscription_cancellation');
            case 'reschedule':
            case 'skip':
                return $subscription->has_status('active')
                    && $subscription->can_date_be_updated('next_payment')
                    && $subscription->payment_method_supports('subscription_date_changes')
                    && (bool) $subscription->get_date('next_payment');
            default:
                return false;
        }
    }

    public function handle(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Accès refusé.', 'meo-abonnements'), '', ['response' => 403]);
        }

        $id = absint($this->postedString('subscription_id'));
        $action = sanitize_key($this->postedString('subscription_action'));

        check_admin_referer('meo_abonnements_' . $id . '_' . $action);

        $subscription = $this->repository->find($id);
        if (!$this->allowed($subscription, $action)) {
            $this->redirect($id, 'unavailable');
        }

        if (in_array($action, ['skip', 'cancel'], true)
            && $this->postedString('confirm') !== '1') {
            $this->redirect($id, 'unavailable');
        }

        try {
            switch ($action) {
                case 'pause':
                    $subscription->update_status('on-hold', 'Mis en pause depuis le tableau de bord Méo.');
                    break;
                case 'resume':
                    $subscription->update_status('active', 'Réactivé depuis le tableau de bord Méo.');
                    break;
                case 'cancel':
                    $subscription->update_status('cancelled', 'Annulé par un gestionnaire depuis le tableau de bord Méo.');
                    break;
                case 'reschedule':
                    $this->reschedule($subscription);
                    break;
                case 'skip':
                    $this->skip($subscription);
                    break;
            }
        } catch (\Throwable $error) {
            if (function_exists('wc_get_logger')) {
                wc_get_logger()->error(
                    sprintf('Abonnement #%d : %s', $id, $error->getMessage()),
                    ['source' => 'meo-abonnements']
                );
            }
            $this->redirect($id, 'error');
        }

        $this->redirect($id, 'saved');
    }

    private function reschedule($subscription): void
    {
        $raw = $this->postedString('next_payment');
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $raw, wp_timezone());
        $errors = \DateTimeImmutable::getLastErrors();

        if (!$date || ($errors && ($errors['warning_count'] || $errors['error_count']))) {
            throw new \InvalidArgumentException('Date de renouvellement invalide.');
        }

        if ($date->getTimestamp() <= time()) {
            throw new \InvalidArgumentException('La date doit être dans le futur.');
        }

        $this->setNextPayment($subscription, $date->getTimestamp());
    }

    private function skip($subscription): void
    {
        if (!function_exists('wcs_add_time') || !function_exists('wcs_date_to_time')) {
            throw new \RuntimeException('Le calcul des dates WooCommerce Subscriptions est indisponible.');
        }

        $current = wcs_date_to_time($subscription->get_date('next_payment', 'gmt'));
        $next = wcs_add_time(
            (int) $subscription->get_billing_interval(),
            $subscription->get_billing_period(),
            $current
        );

        if ($next <= $current) {
            throw new \RuntimeException('La prochaine échéance ne peut pas être calculée.');
        }

        $this->setNextPayment($subscription, $next);
    }

    private function setNextPayment($subscription, int $timestamp): void
    {
        if ($timestamp <= time()) {
            throw new \InvalidArgumentException('La prochaine échéance doit être dans le futur.');
        }

        $end = (int) $subscription->get_time('end');
        if ($end > 0 && $timestamp >= $end) {
            throw new \InvalidArgumentException('La nouvelle échéance dépasse la fin du contrat.');
        }

        $old = $subscription->get_date('next_payment', 'gmt');
        $new = gmdate('Y-m-d H:i:s', $timestamp);
        $subscription->update_dates(['next_payment' => $new], 'gmt');
        $subscription->add_order_note(sprintf(
            'Prochaine échéance modifiée depuis le tableau de bord Méo : %s → %s (UTC).',
            $old,
            $new
        ));
    }

    private function redirect(int $id, string $notice): void
    {
        $url = add_query_arg(
            ['page' => 'meo-abonnements', 'view' => $id, 'notice' => $notice],
            admin_url('admin.php')
        );
        wp_safe_redirect($url);
        exit;
    }

    private function postedString(string $key): string
    {
        if (!isset($_POST[$key]) || !is_scalar($_POST[$key])) {
            return '';
        }

        return sanitize_text_field(wp_unslash((string) $_POST[$key]));
    }
}
