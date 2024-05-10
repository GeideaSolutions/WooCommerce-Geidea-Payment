<?php

/*
Plugin Name: Geidea Online Payments
Description: Geidea Online Payments.
Version: 3.2.0
Author: Geidea
Author URI: https://geidea.net

Copyright 2021

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined('ABSPATH') || exit;

define('GEIDEA_DIR', plugin_dir_path(__FILE__));
define('GEIDEA_ICONS_URL', plugins_url("assets/imgs/icons/", __FILE__));

$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$plugin_version = $plugin_data['Version'];

define('GEIDEA_ONLINE_PAYMENTS_CURRENT_VERSION', $plugin_version);

/* start function plugin */
add_action('plugins_loaded', 'woocommerce_geidea', 0);

/**
 * Add the gateway to WooCommerce
 * */
function geidea_add_gateway($methods)
{
    require_once GEIDEA_DIR . 'class.geidea.php';
    $methods[] = 'WC_Gateway_Geidea';
    return $methods;
}

function geidea_add_card_tokens_menu(): void
{
    \WC_Gateway_Geidea::add_card_tokens_menu();
}

/**
 * Function for declare compatibility with cart_checkout_blocks feature
 */
function declare_cart_checkout_blocks_compatibility()
{
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            __FILE__,
            true
        );
    }
}

/**
 * Function for register a payment method type
 */
function register_order_approval_payment_method_type()
{
    if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }
    require_once('class.blocks-checkout.php');
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            $payment_method_registry->register(new WC_geidea_Blocks);
        }
    );
}

/*
 * Function for load plugin
 */
function woocommerce_geidea(): void
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    if (class_exists('WC_Gateway_Geidea')) {
        return;
    }
    require_once GEIDEA_DIR . 'class.geidea.php';
    if (class_exists('WooCommerce_Payment_Status')) {
        add_filter(
            'woocommerce_valid_order_statuses_for_payment',
            array('WC_Gateway_Geidea', 'valid_order_statuses_for_payment'),
            52,
            2
        );
    }
    add_filter('woocommerce_payment_gateways', 'geidea_add_gateway');
    add_action('admin_menu', 'geidea_add_card_tokens_menu', 99);
    add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');
    add_action('woocommerce_blocks_loaded', 'register_order_approval_payment_method_type');
}
