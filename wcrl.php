<?php
/**
 * Plugin Name: Reports for WooCommerce Lite
 * Plugin URI: https://nikanwp.com/woocommerce-reporting/
 * Description: WooCommerce reporting system
 * Version: 1.0.0
 * Author: NikanWP
 * Author URI: https://nikanwp.com/
 * Text Domain: wc-reports-lite
 * Domain Path: /languages
 */

if (!defined( 'ABSPATH' ) ) {
	exit;// Exit if accessed directly.
}

// Define WCRL_PLUGIN_FILE.
if (!defined('WCRL_PLUGIN_FILE') ) {
	define( 'WCRL_PLUGIN_FILE', __FILE__ );
}

// Include the main wcrl class.
if(!class_exists('WCRL')) {
	include_once dirname(__FILE__).'/includes/class-wcrl.php';
}

/**
 * Plugin action links
 *
 * @param $links
 *
 * @return array
 */
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wcrl_plugin_action_links');
function wcrl_plugin_action_links( $links ) {
	$nurl = get_locale() === WCRL_FARSILANG ? 'https://nikanwp.ir/product/woocommerce-reporting/' : 'https://nikanwp.com/woocommerce-reporting/';
	$links[] = '<a href="'.$nurl.'" style="color: #389e38;font-weight: bold;" target="_blank">' . __( 'Get Pro', 'wc-reports-lite' ) . '</a>';
	return $links;
}

WCRL::getInstance();