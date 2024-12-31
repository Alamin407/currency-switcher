<?php
/**
 * Plugin Name: WPT Currency Switcher
 * Description: Automatically switches WooCommerce prices based on the user's location.
 * Virsion: 1.1
 * Author Name: MD AL AMIN ISLAM
 * Author URI: https://wpthinkers.com
 * Plugin URI: https://wpthinkers.com/plugins
 * License: GPL v2
 * Text Domain: wptwc-currency-switcher
 */

 if(!defined('ABSPATH')){
    exit;
 }

 if(!defined('WPWC_PLUGIN_PATH')){
    define('WPWC_PLUGIN_PATH', plugin_dir_path(__FILE__));
 }

 if(!defined('WPWC_PLUGIN_URL')){
    define('WPWC_PLUGIN_URL', plugin_dir_url(__FILE__));
 }

 require_once WPWC_PLUGIN_PATH . 'inc/class-wpwc-currency-switcher.php';

 add_action( 'plugins_loaded', ['WPWC_Currency_Swithcer', 'init'] );