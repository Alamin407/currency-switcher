<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Enqueue{
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts'));
    }

    public function load_admin_scripts(){
        wp_enqueue_script('wpt-admin-script', WPWC_PLUGIN_URL . 'assets/js/script.js', array('jquery'), '1.1', true );
    }
}

new Enqueue();