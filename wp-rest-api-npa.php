<?php
/*
Plugin Name: WP REST API NPA
Plugin URI: https://wordpress.org/plugins/wp-rest-api-contact/
Description: Create REST API endpoint for Contact and Subscribe Newsletter, manager to list all contacts, manager to list all subscribe newsletter, send mail to the list all subscibers.
Author: Thien Pham, NPA
Version: 1.0.6
Author URI: https://www.facebook.com/thien.pham.5074
Text Domain: tp-custom
Domain Path: /languales/
License: MIT
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define('WPRAC_VERSION', '1.0.6');
define('WPRAC_MINIMUM_WP_VERSION', '4.6');
define('WPRAC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPRAC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPRAC_PLUGIN_LANGUAGES', dirname(plugin_basename(__FILE__) . '/languages/'));

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/*******************
 *
 *Include tp-custom-controller
 * 
 *******************/
require_once(WPRAC_PLUGIN_DIR . 'includes/class.wprac-custom-controller.php');

/*******************
 *
 *Include tools-controller
 * 
 *******************/
require_once(WPRAC_PLUGIN_DIR . 'includes/class.wprac-tools-controller.php');


/**********************
 *
 * Include API CONTACT CUSTOM
 *
 **********************/
require_once(WPRAC_PLUGIN_DIR . 'includes/api-custom/class.wprac-api-contact-controller.php');

/**********************
 *
 * Include API NEWSLETTER CUSTOM
 *
 **********************/
require_once(WPRAC_PLUGIN_DIR . 'includes/api-custom/class.wprac-api-newsletter-controller.php');


register_activation_hook(__FILE__, array('WPRAC_Controller', 'wprac_plugin_activation'));
register_deactivation_hook(__FILE__, array('WPRAC_Controller', 'wprac_plugin_deactivation'));

WPRAC_Controller::run();





