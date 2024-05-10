<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class WC_geidea_Blocks extends AbstractPaymentMethodType
{
    private $gateway;
    protected $name = 'geidea';

    public function initialize()
    {
        $this->settings = get_option('woocommerce_geidea_settings', array());
        $gateways       = WC()->payment_gateways->payment_gateways();
        $this->gateway  = $gateways[$this->name];
    }

    public function is_active()
    {
        return $this->get_setting('enabled') === 'yes';
    }

    public function get_payment_method_script_handles()
    {
        wp_register_script(
            'wc-geidea-blocks-integration',
            plugins_url('assets/js/checkout.js', __FILE__),
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            false,
            true
        );
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('wc-geidea-blocks-integration');
        }
        return ['wc-geidea-blocks-integration'];
    }

    public function get_payment_method_data()
    {
        return array(
            'title'             => $this->get_setting('title'),
            'description'       => $this->get_setting('description'),
            'supports'          => array_filter($this->gateway->supports, array($this->gateway, 'supports')),
            'cardOnFile'        => $this->get_setting('cardOnFile'),
            'logo_url'         => (strlen($this->get_setting('checkout_icon')) > 0) ? str_replace('http://', 'https://', $this->get_setting('checkout_icon')) : $this->gateway->icon,
        );
    }
}
