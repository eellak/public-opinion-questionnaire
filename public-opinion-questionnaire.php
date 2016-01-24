<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
 * Plugin Name: Public Opinion Questionnaire
 * Plugin URI:  https://github.com/eellak/public-opinion-questionnaire
 * Description: Create and display questionnaires with predefined data and profile matching. Requires TwigPress.
 * Version:     0.1
 * Author:      Dimosthenis Nikoudis, Aggeliki Fokou
 * Author URI:  https://github.com/eellak/public-opinion-questionnaire
 * License:     EUPL
 * Depends:     TwigPress
 */

/* Plugin localization */
function ellak_poq_textdomain() {
        load_plugin_textdomain(
                'public-opinion-questionnaire', false,
                dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
}
add_action( 'plugins_loaded', 'ellak_poq_textdomain' );

/* dependency check */
function ellak_poq_dependency_check () {
    if( !is_plugin_active( 'timber-library/timber.php' ) ) {
        echo __('Please install and activate Timber before activating this plugin.', 'public-opinion-questionnaire');
        @trigger_error(__('Please install and activate Timber before activating this plugin.', 'public-opinion-questionnaire'), E_USER_ERROR);
    }
}
register_activation_hook( __FILE__, 'ellak_poq_dependency_check' );

/* db functions */
require_once('ellak_poq_db_install.php');
// This function is a hack from https://wordpress.org/support/topic/register_activation_hook-does-not-work
function ellak_poq_db_install_wrapper () {
    ellak_poq_db_install();
}
register_activation_hook( __FILE__, 'ellak_poq_db_install_wrapper' );

/* page functions */
require_once('ellak_poq_page_install.php');
function ellak_poq_page_install_wrapper () {
    ellak_poq_page_install();
}
function ellak_poq_page_uninstall_wrapper () {
    ellak_poq_page_uninstall();
}
register_activation_hook( __FILE__, 'ellak_poq_page_install_wrapper' );
register_deactivation_hook( __FILE__, 'ellak_poq_page_uninstall_wrapper' );

/* admin functions */
require_once('ellak_poq_admin.php');

/* register the style for this plugin */
function ellak_poq_style() {
    $the_page_name = get_option( "ellak_poq_page_name" );
    if ( is_page( $the_page_name ) ) {
        wp_register_style( 'ellak-poq-pure-css', 'http://yui.yahooapis.com/pure/0.6.0/pure-min.css' );
        wp_enqueue_style( 'ellak-poq-pure-css' );
        wp_register_style( 'ellak-poq-css', plugin_dir_url( __FILE__ ).'/css/style.css', array('ellak-poq-pure-css') );
        wp_enqueue_style( 'ellak-poq-css' );
        wp_register_script( 'ellak-poq-highcharts', '//code.highcharts.com/highcharts.js', array( 'jquery' ) );
        wp_enqueue_script( 'ellak-poq-highcharts' );
    }
}
add_action( 'wp_enqueue_scripts', 'ellak_poq_style' );

/* register the filters that display the page to the user */
$ellakPoqContent = '';
function ellak_poq_create_content() {
    global $ellakPoqContent;
    $the_page_name = get_option( "ellak_poq_page_name" );
    if ( is_page( $the_page_name ) ) {
        require_once('ellak_poq_controller.php');
        $controller = new EllakPoQController();
        $ellakPoqContent = $controller->routeRequest();
    }
}
add_action( 'wp', 'ellak_poq_create_content' );

function ellak_poq_page_filter( $content ) {
    global $ellakPoqContent;
    $the_page_name = get_option( "ellak_poq_page_name" );
    if ( is_page( $the_page_name ) ) {
        $content = $ellakPoqContent;
    }
    return $content;
}
add_filter( 'the_content', 'ellak_poq_page_filter' );

/* set tracking cookie */
function ellak_poq_set_cookie() {
    if(!isset( $_COOKIE['ellak-poq-session'] )) {
        /* private function to retrieve the user's ip */
        $getIP = function() {
            foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
                if (array_key_exists($key, $_SERVER) === true) {
                    foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                            return $ip;
                        }
                    }
                }
            }
        };
        /* end private function to retrieve the user's ip */
        $uniqid = md5(uniqid($getIP(), true)); // Generate a unique id based on the user's IP
    } else {
        $uniqid = $_COOKIE['ellak-poq-session'];
    }
    $oneyear = 31104000; // 1 year in seconds
    setcookie( 'ellak-poq-session', $uniqid, time() + $oneyear, COOKIEPATH, COOKIE_DOMAIN );
}
add_action( 'init', 'ellak_poq_set_cookie' );