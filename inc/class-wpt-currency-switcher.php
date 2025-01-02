<?php

class WPT_Currency_Swithcer{

    private static $instance = null;

    private $exchange_rates = [];
    private $user_currency = 'USD';

    public static function init(){
        if(self::$instance === null){
            self::$instance = new self;
        }
    }

    public function __construct() {
        // Hooks
        add_action('init', [$this, 'initialize']);
        add_action('woocommerce_product_get_price', [$this, 'convert_product_price'], 10, 2);
        add_action('woocommerce_product_get_regular_price', [$this, 'convert_product_price'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_wpt_set_user_location', [$this, 'handle_ajax_set_user_location']);
        add_action('wp_ajax_nopriv_wpt_set_user_location', [$this, 'handle_ajax_set_user_location']);
    }

    /**
     * Initialize plugin
     */
    public function initialize() {
        $this->user_currency = $this->get_user_currency();
        $this->exchange_rates = $this->get_exchange_rates();
    }

    /**
     * Country to Currency Mapping
     */
    private function get_country_currency_mapping() {
        return [
            'US' => 'USD',
            'BD' => 'BDT',
            'GB' => 'GBP',
            'IN' => 'INR',
            'CA' => 'CAD',
            'AU' => 'AUD',
            'EU' => 'EUR',
            'JP' => 'JPY',
        ];
    }

    /**
     * Fetch Exchange Rates
     */
    private function get_exchange_rates() {
        $rates = get_transient('wc_exchange_rates');
        if (!$rates) {
            $response = wp_remote_get('https://api.exchangerate-api.com/v4/latest/USD'); // Replace with your API URL
            if (is_wp_error($response)) {
                return [];
            }
            $rates = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($rates['rates'])) {
                set_transient('wc_exchange_rates', $rates['rates'], 12 * HOUR_IN_SECONDS);
                return $rates['rates'];
            }
        }
        return $rates;
    }

    /**
     * Get User's Currency Based on Location
     */
    private function get_user_currency() {
        $country = get_transient('wc_user_country');
        if (!$country) {
            $country = 'US'; // Default fallback to US
        }

        $mapping = $this->get_country_currency_mapping();
        return isset($mapping[$country]) ? $mapping[$country] : 'USD';
    }

    /**
     * Convert WooCommerce Product Prices
     */
    public function convert_product_price($price, $product) {
        if (!$this->user_currency || empty($this->exchange_rates)) {
            return $price; // Return original price if no conversion is possible
        }

        $default_currency = 'USD'; // Base currency of your store
        if ($this->user_currency !== $default_currency && isset($this->exchange_rates[$this->user_currency])) {
            $exchange_rate = $this->exchange_rates[$this->user_currency];
            $price = $price * $exchange_rate;
        }
        return $price;
    }

    /**
     * Enqueue Scripts
     */
    public function enqueue_scripts() {
        if (!is_admin()) {
            wp_enqueue_script('wpt-currency-switcher', plugin_dir_url(__FILE__) . 'assets/js/wpt-script.js', ['jquery'], '1.0', true);
            wp_localize_script('wpt-currency-switcher', 'wptCurrencySwitcher', [
                'ajax_url' => admin_url('admin-ajax.php'),
            ]);
        }
    }

    /**
     * Handle AJAX Request for User Location
     */
    public function handle_ajax_set_user_location() {
        $latitude = isset($_POST['latitude']) ? sanitize_text_field($_POST['latitude']) : '';
        $longitude = isset($_POST['longitude']) ? sanitize_text_field($_POST['longitude']) : '';

        if ($latitude && $longitude) {
            $response = wp_remote_get("https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=$latitude&longitude=$longitude&localityLanguage=en");
            if (!is_wp_error($response)) {
                $location = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($location['countryCode'])) {
                    set_transient('wc_user_country', $location['countryCode'], 12 * HOUR_IN_SECONDS);
                    wp_send_json_success(['country' => $location['countryCode']]);
                }
            }
        }

        wp_send_json_error(['message' => 'Unable to retrieve location.']);
    }

}