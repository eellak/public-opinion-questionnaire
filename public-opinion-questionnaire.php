<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
 * Plugin Name: Public Opinion Questionnaire
 * Plugin URI:  https://github.com/eellak/public-opinion-questionnaire
 * Description: Create and display questionnaires with predefined data and profile matching
 * Version:     0.1
 * Author:      Dimosthenis Nikoudis, Aggeliki Fokou
 * Author URI:  https://github.com/eellak/public-opinion-questionnaire
 * License:     EUPL
 */

/* Plugin localization */
function ellak_poq_textdomain() {
        load_plugin_textdomain(
                'public-opinion-questionnaire', false,
                dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
}
add_action( 'plugins_loaded', 'ellak_poq_textdomain' );

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
        wp_register_style( 'ellak-poq-css', plugin_dir_url( __FILE__ )
                . '/css/style.css' );
	wp_enqueue_style( 'ellak-poq-css' );
}
add_action( 'wp_enqueue_scripts', 'ellak_poq_style' );

/* register the filter that displays the page to the user */
function ellak_poq_page_filter( $content ) {
   $the_page_name = get_option( "ellak_poq_page_name" );
   if ( is_page( $the_page_name ) ) {
      $content = require_once('views/ellak_poq_question.php');
   }
   return $content;
}
add_filter( 'the_content', 'ellak_poq_page_filter' );