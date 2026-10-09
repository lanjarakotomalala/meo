<?php

namespace Meo\Abonnements;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A focused WordPress admin interface; the storefront is never modified.
 */
final class Admin
{
    private Repository $repository;
    private Actions $actions;

    public function __construct(Repository $repository, Actions $actions)
    {
        $this->repository = $repository;
        $this->actions = $actions;
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_meo_abonnements_update', [$this->actions, 'handle']);
        add_action('admin_enqueue_scripts', [$this, 'styles']);
    }

    public function menu(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Abonnements Méo', 'meo-abonnements'),
            __('Abonnements Méo', 'meo-abonnements'),
            'manage_woocommerce',
            'meo-abonnements',
            [$this, 'render']
        );
    }

    public function styles(string $hook): void
    {
        if ($hook !== 'woocommerce_page_meo-abonnements') {
            return;
        }

        wp_enqueue_style(
            'meo-abonnements-admin',
            plugin_dir_url(dirname(__DIR__) . '/meo-abonnements.php') . 'assets/admin.css',
            [],
            '0.1.0'
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Accès refusé.', 'meo-abonnements'), '', ['response' => 403]);
        }

        $tab = sanitize_key($this->queryString('tab')) ?: 'subscriptions';
        if (!in_array($tab, ['subscriptions', 'plans', 'guide'], true)) {
            $tab = 'subscriptions';
        }

        $view = absint($this->queryString('view'));
        echo '<div class="wrap meo-abonnements-admin">';
        echo '<h1>' . esc_html__('Abonnements Méo', 'meo-abonnements') . '</h1>';
        echo '<p class="description">' . esc_html__(
            'Un point d’entrée simple pour les contrats existants. WooCommerce Subscriptions continue de gérer les paiements et renouvellements.',
            'meo-abonnements'
        ) . '</p>';

        $this->notice();

        if ($view) {
            $this->renderDetail($view);
        } else {
            $this->tabs($tab);
            if ($tab === 'subscriptions') {
                $this->renderSubscriptions();
            } elseif ($tab === 'plans') {
                $this->renderPlans();
            } else {
                $this->renderGuide();
            }
        }

        echo '</div>';
    }

    private function url(array $args = []): string
    {
        return add_query_arg(
            array_merge(['page' => 'meo-abonnements'], $args),
            admin_url('admin.php')
        );
    }

    private function queryString(string $key): string
    {
        if (!isset($_GET[$key]) || !is_scalar($_GET[$key])) {
            return '';
        }

        return sanitize_text_field(wp_unslash((string) $_GET[$key]));
    }

    private function notice(): void
    {
        $notice = sanitize_key($this->queryString('notice'));
        $messages = [
            'saved' => __('Modification enregistrée.', 'meo-abonnements'),
            'unavailable' => __('Cette action n’est plus disponible pour cet abonnement ou ce moyen de paiement.', 'meo-abonnements'),
            'error' => __('La modification a échoué. Consultez les journaux WooCommerce, source « meo-abonnements ».', 'meo-abonnements'),
        ];

        if (!isset($messages[$notice])) {
            return;
        }

        $class = $notice === 'saved' ? 'notice-success' : 'notice-error';
        echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($messages[$notice]) . '</p></div>';
    }

    private function tabs(string $active): void
    {
        $tabs = [
            'subscriptions' => __('Abonnements', 'meo-abonnements'),
            'plans' => __('Produits et plans', 'meo-abonnements'),
            'guide' => __('Démarrage et paiements', 'meo-abonnements'),
        ];

        echo '<nav class="nav-tab-wrapper" aria-label="' . esc_attr__('Sections abonnements', 'meo-abonnements') . '">';
        foreach ($tabs as $key => $label) {
            $class = $key === $active ? 'nav-tab nav-tab-active' : 'nav-tab';
            echo '<a class="' . esc_attr($class) . '" href="' . esc_url($this->url(['tab' => $key])) . '">' . esc_html($label) . '</a>';
        }
        echo '</nav>';
    }

    private function renderSubscriptions(): void
    {
        $statuses = $this->repository->statuses();
        $status = sanitize_key($this->queryString('status'));
        if ($status && !isset($statuses['wc-' . $status])) {
            $status = '';
        }

        $search = $this->queryString('s');
        $page = max(1, absint($this->queryString('paged')));
        $result = $this->repository->listSubscriptions($page, $status, $search);

        echo '<div class="meo-summary">';
        $this->summaryCard(__('Actifs', 'meo-abonnements'), $this->repository->count('active'), 'active');
        $this->summaryCard(__('Suspendus', 'meo-abonnements'), $this->repository->count('on-hold'), 'on-hold');
        $this->summaryCard(__('En attente d’annulation', 'meo-abonnements'), $this->repository->count('pending-cancel'), 'pending-cancel');
        echo '</div>';

        echo '<form method="get" class="meo-filters">';
        echo '<input type="hidden" name="page" value="meo-abonnements">';
        echo '<label for="meo-search">' . esc_html__('N° d’abonnement ou email de facturation', 'meo-abonnements') . '</label>';
        echo '<input id="meo-search" type="search" name="s" value="' . esc_attr($search) . '" placeholder="1234 ou client@example.com">';
        echo '<label for="meo-status">' . esc_html__('État', 'meo-abonnements') . '</label>';
        echo '<select id="meo-status" name="status"><option value="">' . esc_html__('Tous', 'meo-abonnements') . '</option>';
        foreach ($statuses as $key => $label) {
            $value = str_replace('wc-', '', $key);
            echo '<option value="' . esc_attr($value) . '" ' . selected($status, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select><button class="button button-primary" type="submit">' . esc_html__('Rechercher', 'meo-abonnements') . '</button>';
        echo '</form>';

        echo '<table class="widefat striped meo-table"><thead><tr>';
        foreach ([__('Abonnement', 'meo-abonnements'), __('Client', 'meo-abonnements'), __('État', 'meo-abonnements'), __('Prochain paiement', 'meo-abonnements'), __('Montant', 'meo-abonnements'), __('Paiement', 'meo-abonnements')] as $heading) {
            echo '<th scope="col">' . esc_html($heading) . '</th>';
        }
        echo '</tr></thead><tbody>';

        if (empty($result->orders)) {
            echo '<tr><td colspan="6">' . esc_html__('Aucun abonnement trouvé.', 'meo-abonnements') . '</td></tr>';
        } else {
            foreach ($result->orders as $subscription) {
                if (!$subscription instanceof \WC_Subscription) {
                    continue;
                }
                $id = $subscription->get_id();
                $customer = get_user_by('id', $subscription->get_customer_id());
                $customerName = $customer ? $customer->display_name : $subscription->get_formatted_billing_full_name();
                $email = $subscription->get_billing_email();
                echo '<tr>';
                echo '<td><a href="' . esc_url($this->url(['view' => $id])) . '"><strong>#' . esc_html((string) $id) . '</strong></a></td>';
                echo '<td>' . esc_html($customerName ?: __('Client inconnu', 'meo-abonnements')) . '<br><small>' . esc_html($email) . '</small></td>';
                echo '<td>' . $this->statusBadge($subscription->get_status()) . '</td>';
                echo '<td>' . esc_html($subscription->get_date_to_display('next_payment') ?: '—') . '</td>';
                echo '<td>' . wp_kses_post($subscription->get_formatted_order_total()) . '</td>';
                echo '<td>' . esc_html($subscription->get_payment_method_title() ?: __('Manuel', 'meo-abonnements')) . '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody></table>';

        $this->pagination($page, (int) ($result->max_num_pages ?? 0), [
            'status' => $status,
            's' => $search,
        ]);
    }

    private function summaryCard(string $title, int $count, string $status): void
    {
        echo '<a class="meo-summary-card" href="' . esc_url($this->url(['status' => $status])) . '">';
        echo '<span>' . esc_html($title) . '</span><strong>' . esc_html((string) $count) . '</strong></a>';
    }

    private function statusBadge(string $status): string
    {
        $statuses = $this->repository->statuses();
        $label = $statuses['wc-' . $status] ?? $status;
        return '<span class="meo-status meo-status-' . esc_attr(sanitize_html_class($status)) . '">' . esc_html($label) . '</span>';
    }

    private function pagination(int $page, int $pages, array $args = []): void
    {
        if ($pages < 2) {
            return;
        }

        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo wp_kses_post(paginate_links([
            'base' => add_query_arg(array_merge(['paged' => '%#%'], $args), $this->url()),
            'current' => $page,
            'total' => $pages,
            'prev_text' => __('Précédent', 'meo-abonnements'),
            'next_text' => __('Suivant', 'meo-abonnements'),
        ]));
        echo '</div></div>';
    }

    private function renderDetail(int $id): void
    {
        $subscription = $this->repository->find($id);
        echo '<p><a href="' . esc_url($this->url()) . '">← ' . esc_html__('Tous les abonnements', 'meo-abonnements') . '</a></p>';
        if (!$subscription instanceof \WC_Subscription) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Abonnement introuvable.', 'meo-abonnements') . '</p></div>';
            return;
        }

        echo '<h2>' . sprintf(esc_html__('Abonnement #%d', 'meo-abonnements'), $id) . ' ' . $this->statusBadge($subscription->get_status()) . '</h2>';
        echo '<div class="meo-detail-grid">';
        echo '<section class="meo-panel"><h3>' . esc_html__('Contrat', 'meo-abonnements') . '</h3><dl>';
        $this->detailRow(__('Client', 'meo-abonnements'), $subscription->get_formatted_billing_full_name());
        $this->detailRow(__('Email', 'meo-abonnements'), $subscription->get_billing_email());
        $this->detailRow(__('Fréquence', 'meo-abonnements'), sprintf(
            __('Tous les %s', 'meo-abonnements'),
            $this->periodLabel((int) $subscription->get_billing_interval(), $subscription->get_billing_period())
        ));
        $this->detailRow(__('Prochain paiement', 'meo-abonnements'), $subscription->get_date_to_display('next_payment') ?: '—');
        $this->detailRow(__('Dernier paiement', 'meo-abonnements'), $subscription->get_date_to_display('last_payment') ?: '—');
        $this->detailRow(__('Fin', 'meo-abonnements'), $subscription->get_date_to_display('end') ?: '—');
        $this->detailRow(__('Moyen de paiement', 'meo-abonnements'), $subscription->get_payment_method_title() ?: __('Paiement manuel', 'meo-abonnements'));
        $this->detailRow(__('Renouvellement', 'meo-abonnements'), $subscription->is_manual()
            ? __('Paiement demandé au client', 'meo-abonnements')
            : __('Paiement automatique', 'meo-abonnements'));
        $this->detailRow(__('Paiements échoués', 'meo-abonnements'), (string) $subscription->get_failed_payment_count());
        echo '</dl><p><strong>' . esc_html__('Montant récurrent :', 'meo-abonnements') . '</strong> ' . wp_kses_post($subscription->get_formatted_order_total()) . '</p>';
        if (method_exists($subscription, 'get_edit_order_url')) {
            echo '<p><a class="button" href="' . esc_url($subscription->get_edit_order_url()) . '">' . esc_html__('Ouvrir la fiche WooCommerce complète', 'meo-abonnements') . '</a></p>';
        }
        echo '</section>';

        echo '<section class="meo-panel"><h3>' . esc_html__('Articles', 'meo-abonnements') . '</h3><ul class="meo-items">';
        foreach ($subscription->get_items() as $item) {
            echo '<li>' . esc_html($item->get_name()) . ' <small>× ' . esc_html((string) $item->get_quantity()) . '</small></li>';
        }
        echo '</ul><h3>' . esc_html__('Livraison', 'meo-abonnements') . '</h3>';
        echo '<div>' . wp_kses_post($subscription->get_formatted_shipping_address() ?: __('Aucune adresse de livraison.', 'meo-abonnements')) . '</div>';
        echo '</section>';
        echo '</div>';

        $this->renderControls($subscription);
        $this->renderRenewalOrders($subscription);
    }

    private function detailRow(string $label, string $value): void
    {
        echo '<dt>' . esc_html($label) . '</dt><dd>' . esc_html($value ?: '—') . '</dd>';
    }

    private function renderControls($subscription): void
    {
        $id = $subscription->get_id();
        echo '<section class="meo-panel"><h3>' . esc_html__('Actions', 'meo-abonnements') . '</h3>';
        echo '<p class="description">' . esc_html__('Chaque action modifie le contrat WooCommerce existant. Les renouvellements restent gérés par WooCommerce Subscriptions et sa passerelle de paiement.', 'meo-abonnements') . '</p>';
        echo '<div class="meo-actions">';

        if ($this->actions->allowed($subscription, 'pause')) {
            $this->actionForm($id, 'pause', __('Mettre en pause', 'meo-abonnements'));
        }
        if ($this->actions->allowed($subscription, 'resume')) {
            $this->actionForm($id, 'resume', __('Reprendre', 'meo-abonnements'));
        }
        if ($this->actions->allowed($subscription, 'skip')) {
            $this->actionForm($id, 'skip', __('Sauter une échéance', 'meo-abonnements'), true);
        }
        if ($this->actions->allowed($subscription, 'reschedule')) {
            $utc = $subscription->get_date('next_payment', 'gmt');
            $local = (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))
                ->setTimezone(wp_timezone())->format('Y-m-d\TH:i');
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="meo-date-form">';
            $this->actionFields($id, 'reschedule');
            echo '<label for="meo-next-payment">' . esc_html__('Déplacer le prochain paiement', 'meo-abonnements') . '</label>';
            echo '<input id="meo-next-payment" type="datetime-local" name="next_payment" value="' . esc_attr($local) . '" required>';
            echo '<button class="button" type="submit">' . esc_html__('Enregistrer la date', 'meo-abonnements') . '</button></form>';
        }
        echo '</div>';

        if ($this->actions->allowed($subscription, 'cancel')) {
            echo '<div class="meo-danger"><h4>' . esc_html__('Annulation', 'meo-abonnements') . '</h4>';
            echo '<p>' . esc_html__('L’annulation immédiate est définitive et arrête les prochains paiements. Pour une autre échéance, utilisez la fiche WooCommerce complète.', 'meo-abonnements') . '</p>';
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            $this->actionFields($id, 'cancel');
            echo '<label><input type="checkbox" name="confirm" value="1" required> ' . esc_html__('Je confirme l’annulation immédiate de cet abonnement.', 'meo-abonnements') . '</label> ';
            echo '<button class="button" type="submit">' . esc_html__('Annuler immédiatement', 'meo-abonnements') . '</button></form></div>';
        }
        echo '</section>';
    }

    private function actionForm(int $id, string $action, string $label, bool $confirm = false): void
    {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        $this->actionFields($id, $action);
        if ($confirm) {
            echo '<label><input type="checkbox" name="confirm" value="1" required> ' . esc_html__('Confirmer', 'meo-abonnements') . '</label> ';
        }
        echo '<button class="button" type="submit">' . esc_html($label) . '</button></form>';
    }

    private function actionFields(int $id, string $action): void
    {
        echo '<input type="hidden" name="action" value="meo_abonnements_update">';
        echo '<input type="hidden" name="subscription_id" value="' . esc_attr((string) $id) . '">';
        echo '<input type="hidden" name="subscription_action" value="' . esc_attr($action) . '">';
        wp_nonce_field('meo_abonnements_' . $id . '_' . $action);
    }

    private function renderRenewalOrders($subscription): void
    {
        $ids = array_values($subscription->get_related_orders('ids', 'renewal'));
        rsort($ids, SORT_NUMERIC);
        echo '<section class="meo-panel"><h3>' . esc_html__('Renouvellements récents', 'meo-abonnements') . '</h3>';
        if (!$ids) {
            echo '<p>' . esc_html__('Aucun renouvellement enregistré.', 'meo-abonnements') . '</p></section>';
            return;
        }

        echo '<ul class="meo-items">';
        foreach (array_slice($ids, 0, 10) as $orderId) {
            $order = wc_get_order($orderId);
            if (!$order) {
                continue;
            }
            echo '<li><a href="' . esc_url($order->get_edit_order_url()) . '">#' . esc_html((string) $orderId) . '</a> — ';
            echo esc_html(wc_get_order_status_name($order->get_status())) . '</li>';
        }
        echo '</ul></section>';
    }

    private function renderPlans(): void
    {
        $kind = sanitize_key($this->queryString('kind')) === 'plans' ? 'plans' : 'products';
        $page = max(1, absint($this->queryString('paged')));
        $query = $this->repository->listPlanProducts($kind, $page);

        echo '<p>' . esc_html__('Les plans utilisés par la boutique sont configurés sur les produits WooCommerce. Cette page permet de les retrouver rapidement sans créer une seconde source de prix.', 'meo-abonnements') . '</p>';
        echo '<p class="meo-kind-switch"><a class="button ' . ($kind === 'products' ? 'button-primary' : '') . '" href="' . esc_url($this->url(['tab' => 'plans', 'kind' => 'products'])) . '">' . esc_html__('Produits abonnement', 'meo-abonnements') . '</a> ';
        echo '<a class="button ' . ($kind === 'plans' ? 'button-primary' : '') . '" href="' . esc_url($this->url(['tab' => 'plans', 'kind' => 'plans'])) . '">' . esc_html__('Plans sur produits classiques', 'meo-abonnements') . '</a></p>';
        echo '<table class="widefat striped meo-table"><thead><tr><th>' . esc_html__('Produit', 'meo-abonnements') . '</th><th>' . esc_html__('Offre', 'meo-abonnements') . '</th><th>' . esc_html__('Publication', 'meo-abonnements') . '</th><th>' . esc_html__('Modifier', 'meo-abonnements') . '</th></tr></thead><tbody>';

        if (!$query->posts) {
            echo '<tr><td colspan="4">' . esc_html__('Aucun produit trouvé.', 'meo-abonnements') . '</td></tr>';
        }

        foreach ($query->posts as $productId) {
            $product = wc_get_product($productId);
            if (!$product) {
                continue;
            }

            echo '<tr><td><strong>' . esc_html($product->get_name()) . '</strong><br><small>#' . esc_html((string) $productId) . '</small></td><td>';
            if ($kind === 'plans') {
                $schemes = get_post_meta($productId, '_wcsatt_schemes', true);
                $this->renderSchemes($schemes);
            } else {
                $interval = (int) get_post_meta($productId, '_subscription_period_interval', true);
                $period = get_post_meta($productId, '_subscription_period', true);
                $price = get_post_meta($productId, '_subscription_price', true);
                if ($interval && $period) {
                    echo esc_html(sprintf(
                        '%s / %s',
                        wp_strip_all_tags(wc_price((float) $price)),
                        $this->periodLabel($interval, $period)
                    ));
                } else {
                    echo esc_html__('Voir les variations du produit', 'meo-abonnements');
                }
            }
            $postStatus = get_post_status_object(get_post_status($productId));
            echo '</td><td>' . esc_html($postStatus ? $postStatus->label : '—') . '</td><td><a class="button" href="' . esc_url(get_edit_post_link($productId)) . '">' . esc_html__('Modifier dans WooCommerce', 'meo-abonnements') . '</a></td></tr>';
        }
        echo '</tbody></table>';
        $this->pagination($page, (int) $query->max_num_pages, ['tab' => 'plans', 'kind' => $kind]);
        wp_reset_postdata();
    }

    private function renderSchemes($schemes): void
    {
        if (!is_array($schemes) || !$schemes) {
            echo esc_html__('Aucun plan actif détecté.', 'meo-abonnements');
            return;
        }

        echo '<ul class="meo-schemes">';
        foreach ($schemes as $scheme) {
            if (!is_array($scheme)) {
                continue;
            }
            $interval = isset($scheme['subscription_period_interval']) ? absint($scheme['subscription_period_interval']) : 1;
            $period = isset($scheme['subscription_period']) ? sanitize_text_field($scheme['subscription_period']) : '';
            $discount = isset($scheme['subscription_discount']) ? (float) $scheme['subscription_discount'] : 0;
            echo '<li>' . esc_html(sprintf(
                __('Tous les %s', 'meo-abonnements'),
                $this->periodLabel($interval, $period)
            ));
            if (($scheme['subscription_pricing_method'] ?? '') === 'override'
                && isset($scheme['subscription_price']) && is_numeric($scheme['subscription_price'])) {
                echo ' · ' . wp_kses_post(wc_price((float) $scheme['subscription_price']));
            }
            if ($discount > 0) {
                echo ' · ' . esc_html(sprintf('−%s %%', wc_format_decimal($discount)));
            }
            echo '</li>';
        }
        echo '</ul>';
    }

    private function periodLabel(int $interval, string $period): string
    {
        $periods = wcs_get_subscription_period_strings($interval);
        return $periods[$period] ?? $period;
    }

    private function renderGuide(): void
    {
        echo '<div class="meo-detail-grid"><section class="meo-panel"><h2>' . esc_html__('Créer une offre', 'meo-abonnements') . '</h2><ol>';
        echo '<li>' . esc_html__('Créez ou ouvrez un produit WooCommerce.', 'meo-abonnements') . '</li>';
        echo '<li>' . esc_html__('Choisissez le type « Abonnement simple » ou ajoutez des plans au produit avec la fonction de plans WooCommerce Subscriptions disponible sur le site.', 'meo-abonnements') . '</li>';
        echo '<li>' . esc_html__('Définissez le prix, la fréquence et la durée dans l’éditeur du produit.', 'meo-abonnements') . '</li>';
        echo '<li>' . esc_html__('Effectuez un achat de test et vérifiez l’abonnement et sa prochaine échéance ici.', 'meo-abonnements') . '</li>';
        echo '</ol><p><a class="button button-primary" href="' . esc_url(admin_url('post-new.php?post_type=product')) . '">' . esc_html__('Créer un produit', 'meo-abonnements') . '</a></p></section>';

        echo '<section class="meo-panel"><h2>' . esc_html__('Moyens de paiement', 'meo-abonnements') . '</h2>';
        echo '<p>' . esc_html__('Seules les passerelles qui annoncent leur compatibilité avec les abonnements peuvent traiter les renouvellements automatiques.', 'meo-abonnements') . '</p>';
        echo '<ul class="meo-items">';
        foreach (WC()->payment_gateways()->payment_gateways() as $gateway) {
            if ($gateway->enabled !== 'yes') {
                continue;
            }
            echo '<li>' . esc_html($gateway->get_title()) . ' — ';
            echo $gateway->supports('subscriptions')
                ? esc_html__('abonnements annoncés comme compatibles', 'meo-abonnements')
                : esc_html__('renouvellement automatique non annoncé', 'meo-abonnements');
            echo '</li>';
        }
        echo '</ul><p><a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout')) . '">' . esc_html__('Ouvrir les réglages de paiement', 'meo-abonnements') . '</a></p></section></div>';
    }
}
