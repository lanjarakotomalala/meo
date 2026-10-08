<?php

namespace MyApp\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

class FriendReferrerServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register($container) {}

    /**
     * @inheritDoc
     */
    public function bootstrap($container)
    {
        add_filter('wc_points_rewards_action_settings', [$this, 'registerReferrerAdminSettings']);
        add_action('woocommerce_register_form', [$this, 'addReferrerField']);
        add_action('woocommerce_register_post', [$this, 'verifyReferrerField'], 10, 3);
        add_filter('woocommerce_new_customer_data', [$this, 'addReferrerToUserMeta'], 10, 1);
        add_filter('woocommerce_thankyou', [$this, 'referrerReward'], 10, 1);
        add_filter('wc_points_rewards_event_description', [$this, 'addRewardDescription'], 10, 3);
        add_filter('woocommerce_registration_redirect', [$this, 'addReferralSuccessNoticeQueryArg'], 10, 1);
        add_action('woocommerce_account_dashboard', [$this, 'addReferralSuccessNotice']);
        add_action('woocommerce_before_checkout_form', [$this, 'addReferralInfoNotice'], 5);
    }

    public function registerReferrerAdminSettings($settings)
    {
        $settings[] = [
            'title' => __('Points gagnés par le parrainage', 'wc_points_rewards'),
            'desc_tip' => __('Entrez le nombre de points gagnés quand un client parraine quelqu\'un', 'wc_points_rewards'),
            'id' => 'meo_points_rewards_friend_referrer_points',
        ];

        return $settings;
    }

    public function addReferrerField()
    {
        ?>
        <p class="form-row form-row-wide">
            <label for="referrer"><?php _e('E-mail du parrain (facultatif)', 'woocommerce'); ?></label>
            <input type="text" class="input-text" name="referrer" id="referrer" value="<?php echo (isset($_GET['referrer']) ? $_GET['referrer'] : ''); ?>" />
            <span class="field-description"><?php _e('Parrainez vos proches et recevez chacun '.(int)get_option('meo_points_rewards_friend_referrer_points', 0).' points fidélité lors de votre commande', 'woocommerce'); ?></span>
        </p>
        <?php
    }

    public function verifyReferrerField($username, $email, $validation_errors)
    {
        if (isset($_POST['referrer']) && !empty($_POST['referrer'])) {
            if (!is_email($_POST['referrer'])) {
                $validation_errors->add('invalid_referrer', __('Le parrain doit être un email valide', 'woocommerce'));
            }

            if (!email_exists($_POST['referrer'])) {
                $validation_errors->add('invalid_referrer', __('L\'email de parrainage ne semble pas exister', 'woocommerce'));
            }
        }
    }

    public function addReferrerToUserMeta($user_meta)
    {
        if(!$user = get_user_by('email', $_POST['referrer'] ?? '')) return $user_meta;

        $user_meta['meta_input']['meo_referrer'] = $user->ID;

        return $user_meta;
    }

    public function referrerReward($order_id)
    {
        $user = wp_get_current_user();
        if (!$referrer = get_user_meta($user->ID, 'meo_referrer', true)) return;

        if (!$order_id) return;

        $referrer = get_user_by('id', $referrer);

        $pointsToAdd = (int)get_option('meo_points_rewards_friend_referrer_points', 0);

        \WC_Points_Rewards_Manager::increase_points($referrer->ID, $pointsToAdd, 'refer-a-friend-success', []);
        \WC_Points_Rewards_Manager::increase_points($user->ID, $pointsToAdd, 'referred-by-a-friend', [], $order_id);

        delete_user_meta($user->ID, 'meo_referrer');
    }

    public function addRewardDescription($desc, $type, $event)
    {
        $points_label = get_option('wc_points_rewards_points_label');
        $points_label = explode(':', $points_label)[1] ?? $points_label;

        switch ($type) {
            case 'refer-a-friend-success':
                $desc = sprintf(__('%s gagnés pour avoir parrainé un ami', 'wc_points_rewards'), $points_label);
                break;
            case 'referred-by-a-friend':
                $desc = sprintf(__('%s gagnés pour avoir été parrainé par un ami', 'wc_points_rewards'), $points_label);
                break;
        }

        return $desc;
    }

    public function addReferralSuccessNotice()
    {
        if(!isset($_GET['referral_success'])) return;
        echo '<div class="woocommerce-message" role="alert">' . __( 'L’e-mail de votre parrain a bien été enregistré, passez votre première commande pour profiter de vos grains de fidélité', 'wc_points_rewards' ) . '</div>';
    }

    public function addReferralSuccessNoticeQueryArg($permalink)
    {
        if (isset($_POST['referrer']) && !empty($_POST['referrer'])) {
            return add_query_arg('referral_success', 1, wc_get_page_permalink('myaccount'));
        }

        return $permalink;
    }

    public function addReferralInfoNotice() {
        $message = '<div class="woocommerce-info wc_points_rewards_earn_points">' . __('C’est votre première commande et vous avez été parrainés ? Gagnez {points_value} grains de fidélité supplémentaires', 'wc_points_reward') . '</div>';
        $message = str_replace('{points_value}', '<strong>'.(int)get_option('meo_points_rewards_friend_referrer_points', 0).'</strong>', $message);

        echo $message;
    }
}
