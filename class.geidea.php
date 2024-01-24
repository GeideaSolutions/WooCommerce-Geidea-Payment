<?php

/**
 * Geidea Payment Gateway class
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class       WC_Geidea
 * @extends     WC_Payment_Gateway
 * @version     2.1.0
 * @author      Geidea
 */

defined('ABSPATH') || exit;

require_once 'functions/Address.php';
require_once 'functions/AdminOptions.php';
require_once 'functions/ApiHandler.php';
require_once 'functions/Checkout.php';
require_once 'functions/CardOptions.php';
require_once 'functions/InitFormFields.php';
require_once 'functions/PaymentComplete.php';
require_once 'functions/ProcessPayment.php';
require_once 'functions/ProcessRefund.php';
require_once 'functions/ReturnHandler.php';
require_once 'functions/TokenisePayment.php';
require_once 'includes/GITable.php';
require_once 'uninstall.php';

use Geidea\Functions\Address;
use Geidea\Functions\AdminOptions;
use Geidea\Functions\ApiHandler;
use Geidea\Functions\Checkout;
use Geidea\Functions\CardOptions;
use Geidea\Functions\InitFormFields;
use Geidea\Functions\PaymentComplete;
use Geidea\Functions\ProcessPayment;
use Geidea\Functions\ProcessRefund;
use Geidea\Functions\ReturnHandler;
use Geidea\Functions\TokenisePayment;

class WC_Gateway_Geidea extends \WC_Payment_Gateway
{
    use Address;
    use AdminOptions;
    use ApiHandler;
    use Checkout;
    use CardOptions;
    use InitFormFields;
    use PaymentComplete;
    use ProcessPayment;
    use ProcessRefund;
    use ReturnHandler;
    use TokenisePayment;

    public static ?WC_Gateway_Geidea $instance = null;

    public $config;
    public $logo;
    public $tokenise_param;
    public $token_id_param;

    public function __construct()
    {
        $this->id = 'geidea';

        // Working with non wp language translation
        $lang = get_bloginfo('language');
        if ($lang == "ar") {
            include_once 'lang/settings.ar.php';
        } else {
            include_once 'lang/settings.en.php';
        }

        $this->config = require 'config.php';
        $this->method_title = geideaTitle;
        $this->has_fields = true;
        $this->errors = [];
        //Initialization the form fields
        $this->init_form_fields();
        //Initialization the settings
        $this->init_settings();
        $this->supports = array(
            'products',
            'tokenization',
            'refunds',
        );
        $this->title = $this->get_option('title');
        $this->enabled = $this->get_option('enabled');
        $this->description = $this->get_option('description');
        $this->logo = $this->get_option('checkout_icon');
        $this->icon = apply_filters('woocommerce_' . $this->id . '_icon', (!empty($this->logo)
            ? $this->logo
            : plugins_url('assets/imgs/geidea-logo.svg', __FILE__)));

        $this->tokenise_param = "wc-$this->id-new-payment-method";
        $this->token_id_param = "wc-$this->id-payment-token";
        if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
        } else {
            add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
        }
        add_action('woocommerce_receipt_' . $this->id, array(&$this, 'receipt_page'));
        add_action('woocommerce_api_' . $this->id, array($this, 'return_handler'));
        add_action('wp_footer', array($this, 'checkout_js_order_handler'));
        add_action('wp_enqueue_scripts', array($this, 'add_scroll_script'));
        if ((wp_verify_nonce(sanitize_text_field(wp_unslash(isset($_GET['wc-api']) && $_GET['wc-api'] == 'geidea'))))) {
            do_action('woocommerce_api_' . $this->id, array($this, 'return_handler'));
        }
        add_action('wp_ajax_ajax_order', array('WC_Gateway_Geidea', 'init_payment'));
        add_action('wp_ajax_nopriv_ajax_order', array('WC_Gateway_Geidea', 'init_payment'));
    }

    public function add_scroll_script()
    {
        if (is_checkout() && !is_wc_endpoint_url()) {
            wp_register_style('geidea', plugins_url('assets/css/gi-styles.css', __FILE__));
            wp_enqueue_style('geidea');

            wp_register_script('geidea', plugins_url('assets/js/script.js', __FILE__));
            wp_enqueue_script('geidea');

            wp_register_script('geidea_sdk', $this->config['jsSdkUrl']);
            wp_enqueue_script('geidea_sdk');
        }
    }

    public static function add_card_tokens_menu()
    {
        $lang = get_bloginfo('language');

        if ($lang == "ar") {
            include_once 'lang/settings.ar.php';
        } else {
            include_once 'lang/settings.en.php';
        }

        add_submenu_page(
            'woocommerce',
            geideaTokensTitle,
            geideaTokensTitle,
            'manage_woocommerce',
            'card_tokens',
            array('WC_Gateway_Geidea', 'tokens_table'),
            3
        );
    }

    private function get_options(): array
    {
        $options = get_option('woocommerce_' . $this->id . '_settings');
        $settings = [];
        if (isset($options['merchant_gateway_key'])) {
            $settings['merchantGatewayKey'] = $options['merchant_gateway_key'];
        }
        if (isset($options['currency_id'])) {
            $settings['currencyId'] = $options['currency_id'];
        }
        if (isset($options['currency_default'])) {
            $settings['currencyDefault'] = $options['currency_default'];
        }
        if (isset($options['order_status_success'])) {
            $settings['orderStatusSuccess'] = str_replace("wc-", '', $options['order_status_success']);
        }
        if (isset($options['order_status_waiting'])) {
            $settings['orderStatusWaiting'] = $options['order_status_waiting'];
        }

        $settings['returnUrl'] = get_site_url() . '/?wc-api=geidea';

        return $settings;
    }
}
