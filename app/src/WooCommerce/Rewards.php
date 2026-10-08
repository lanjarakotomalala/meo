<?php

namespace MyApp\WooCommerce;

use WC_Points_Rewards_Manager;
use WPEmerge\ServiceProviders\ServiceProviderInterface;

class Rewards implements ServiceProviderInterface
{

    public function register($container)
    {
        // Nothing to register.
    }

    public function bootstrap($container)
    {
        add_filter('wc_points_rewards_earn_points_message', [$this, 'showCurrentRewardsInAccount'], 10, 2);
    }

    public function showCurrentRewardsInAccount($message, $points_earned): string
    {
        if (!is_user_logged_in() || !class_exists('WC_Points_Rewards_Manager')) {
            return '';
        }

        global $wc_points_rewards, $wpdb;

        $user_id = get_current_user_id();

        $query = "SELECT SUM(points_balance) AS total_points FROM {$wc_points_rewards->user_points_db_tablename} WHERE user_id = %d AND points_balance != 0";
        $result = $wpdb->get_row($wpdb->prepare($query, $user_id));

        $points_balance = $result ? (int)$result->total_points : 0;


        return $this->renderRewardsInfo($message, $points_balance);
    }

    private function renderRewardsInfo(string $message, int $points): string
    {

        $pointsMessage = sprintf(__('You currently have <strong>%s points</strong> in your account', 'meo'), $points);

        return sprintf(
            '<div class="woocommerce-info wc_points_rewards_earn_points pb-down">%s</div>%s',
            $pointsMessage,
            $message
        );
    }
}
