<?php
function is_cookies_accepted()
{
    /*
     * Temporary return true because we remove the axeptio script
     * This part will have to be continue after the Altavia work
     */

    return true;

//    if (!isset($_COOKIE['axeptio_cookies'])) {
//        return false;
//    }
//
//    $axeptio_cookies = str_replace('\\', '', $_COOKIE['axeptio_cookies']);
//    $axeptio_accepted_cookies = json_decode($axeptio_cookies);
//
//    return $axeptio_accepted_cookies->Google_Ads || $axeptio_accepted_cookies->google_analytics;
}

// Disable RSS feeds

remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );
function disable_feed() {
    $message = sprintf(
        __( 'Aucun flux disponible', 'meo'),
        esc_url( home_url( '/' ) )
    );

    wp_die( wp_kses_post( $message ) );
}

add_action('do_feed', 'disable_feed', 1);
add_action('do_feed_rdf', 'disable_feed', 1);
add_action('do_feed_rss', 'disable_feed', 1);
add_action('do_feed_rss2', 'disable_feed', 1);
add_action('do_feed_atom', 'disable_feed', 1);
add_action('do_feed_rss2_comments', 'disable_feed', 1);
add_action('do_feed_atom_comments', 'disable_feed', 1);
