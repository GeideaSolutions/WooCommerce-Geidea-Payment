<?php

namespace Geidea\Functions;

use WC_Payment_Tokens;

/**
 * ProcessPayment
 */
trait ProcessPayment
{

    public static function getInstance(): ?WC_Gateway_Geidea
    {
        null === self::$instance and self::$instance = new self;
        return self::$instance;
    }

    public function process_payment($order_id): array
    {
        $order = wc_get_order($order_id);
        $user_id = get_current_user_id();
        $save_card = false;
        if ($user_id != 0) {
            $token = $this->get_token();
            if ($token) {
                $success = $this->tokenise_payment($order, $token);
                if ($success) {
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order),
                    );
                } else {
                    return array('result' => 'fail');
                }
            }
        }
        $token_id = null;
        try {
            if (isset($_POST['wc-geidea-payment-token']) && isset($_POST['woocommerce-process-checkout-nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce'])), 'woocommerce-process_checkout')) {
                $selected_token_id = sanitize_text_field(wp_unslash($_POST['wc-geidea-payment-token']));
                $token = WC_Payment_Tokens::get($selected_token_id);
                if ($token) {
                    $token_id = $token->get_token();
                }
            }
            if (isset($_POST['wc-geidea-new-payment-method']) && isset($_POST['woocommerce-process-checkout-nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce'])), 'woocommerce-process_checkout')) {
                $save_card = true;
            }
        } catch (Exception $e) {
            $token_id = null;
        }

        $order = wc_get_order($order_id);
        // $items = $order->get_items();
        // $orderItems = array();
        // foreach ($items as $item) {
        //     $product = $item->get_product();
        //     $tempItem = array(
        //         "merchantItemId" => (string)$item->get_product_id(),
        //         "name" => $item->get_name(),
        //         "description" => $item->get_name(),
        //         "categories" => "categories",
        //         "count" => $item->get_quantity(),
        //         "price" => number_format($product->get_price(), 2, '.', ''),
        //     );
        //     $orderItems[] = $tempItem;
        // }
        $order_currency = $order->get_currency();
        $available_currencies = $this->config['availableCurrencies'];
        $result_currency = in_array($order_currency, $available_currencies) ? $order_currency : $this->get_option('currency_id');
        $billingAddress = $this->get_formatted_billing_address($order);
        $shippingAddress = $this->get_formatted_shipping_address($order);
        global $wp_version;
        $encode_params = json_encode(array(
            "merchantPublicKey" => $this->get_option('merchant_gateway_key'),
            "apiPassword" => $this->get_option('merchant_password'),
            "callbackUrl" => str_replace('http://', 'https://', get_site_url() . '/?wc-api=geidea'),
            "amount" => number_format($order->get_total(), 2, '.', ''),
            "currency" => $result_currency,
            "cardOnFile" => $save_card,
            "merchantReferenceId" => (string)$order->get_id(),
            "initiatedBy" => "Internet",
            "tokenId" => $token_id,
            "language" => $this->get_option('language'),
            "customer" => array(
                "create" => false,
                "setDefaultMethod" => false,
                "email" => sanitize_text_field($order->get_billing_email()),
                "phoneNumber" => $order->get_billing_phone()[0] != '+' ? '+' . $order->get_billing_phone() : $order->get_billing_phone(),
                "address" => array(
                    "billing" => array(
                        "city" => $billingAddress['city'],
                        "country" => $billingAddress['country'],
                        "postcode" => $billingAddress['postcode'],
                        "street" => $billingAddress['street'],
                    ),
                    "shipping" => array(
                        "city" => $shippingAddress['city'],
                        "country" => $shippingAddress['country'],
                        "postcode" => $shippingAddress['postcode'],
                        "street" => $shippingAddress['street'],
                    )
                )
            ),
            "appearance" => array(
                "showAddress" => $token_id ? false : ($this->get_option('address_enabled') === 'yes'),
                "showEmail" => $token_id ? false : ($this->get_option('email_enabled') === 'yes'),
                "showPhone" => $token_id ? false : ($this->get_option('phonenumber_enabled') === 'yes'),
                "receiptPage" => ($this->get_option('receipt_enabled') === 'yes') ? true : false,
                "merchant" => array(
                    "logoUrl" => (strlen($this->get_option('logo')) > 0) ? str_replace('http://', 'https://', $this->get_option('logo')) : null,
                ),
                "styles" => array(
                    "headerColor" => $this->get_option('header_color') ? $this->get_option('header_color') : null,
                    "hppProfile" => $this->get_option('hppprofile'),
                    "hideGeideaLogo" => ($this->get_option('hide_GeideaLogo') === 'yes') ? true : false,
                )
            ),
            "order" => array(
                "integrationType" => 'plugin',
                // "items" => $orderItems
            ),
            "platform" => array(
                "name" => "Wordpress",
                "version" => $wp_version,
                "pluginVersion" => GEIDEA_ONLINE_PAYMENTS_CURRENT_VERSION,
                "partnerId" => (strlen($this->get_option('partner_id')) > 0) ? $this->get_option('partner_id') : null,
            )
        ));

        $response = $this->send_gi_request(
            $this->config['createSessionUrl'],
            $this->get_option('merchant_gateway_key'),
            $this->get_option('merchant_password'),
            $encode_params
        );

        if (is_wp_error($response)) {
            error_log(json_encode($response));
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $body['successUrl'] = $this->get_return_url($order);
            $response['body'] = json_encode($body);
        }
        $script = '
        <script>
        startV2HPP(' . $response['body'] . ');
        </script>
        ';
        return array(
            'result' => 'success',
            'messages' => $script,
            'refresh' => true,
            'reload' => false,
        );
    }

    public function init_payment()
    {
        if (wp_verify_nonce(sanitize_text_field(wp_unslash(isset($_POST['fields'])))) && !empty($_POST['fields'])) {
            $payment_obj = WC_Gateway_Geidea::getInstance();
            $order = null;
            $data = [];
            foreach (sanitize_text_field(wp_unslash($_POST['fields'])) as $values) {
                $data[$values['name']] = sanitize_text_field($values['value']);
            }
            $order_id = sanitize_text_field(wp_unslash(isset($_POST['order_id'])));
            if ($order_id != 0) {
                $order = wc_get_order($order_id);
            }
            if ($order) {
                $order_currency = $order->get_currency();
                $available_currencies = $payment_obj->config['availableCurrencies'];
                $result_currency = in_array($order_currency, $available_currencies) ? $order_currency : $payment_obj->get_option('currency_id');
                $save_card = $data["wc-geidea-new-payment-method"];
                global $wp_version;
                $lang = $payment_obj->get_option('language');
                $payment_data = [];
                $payment_data['orderId'] = (string) $order->get_id();
                $payment_data['amount'] = number_format($order->get_total(), 2, '.', '');
                $payment_data['merchantGatewayKey'] = $payment_obj->get_option('merchant_gateway_key');
                $payment_data['currencyId'] = $result_currency;
                $payment_data['successUrl'] = $payment_obj->get_return_url($order);
                $payment_data['failUrl'] = $payment_obj->get_return_url($order);
                $payment_data['headerColor'] = $payment_obj->get_option('header_color');
                $payment_data['hideGeideaLogo'] = $payment_obj->get_option('hide_GeideaLogo');
                $payment_data['cardOnFile'] = $save_card;
                $payment_data['customerEmail'] = sanitize_text_field($order->get_billing_email());
                $payment_data['billingAddress'] = json_encode($payment_obj->get_formatted_billing_address($order));
                $payment_data['shippingAddress'] = json_encode($payment_obj->get_formatted_shipping_address($order));
                $payment_data['receiptEnabled'] = $payment_obj->get_option('receipt_enabled');
                $callbackUrl = get_site_url() . '/?wc-api=geidea';
                $payment_data['callbackUrl'] = str_replace('http://', 'https://', $callbackUrl);
                $logoUrl = $payment_obj->get_option('logo');
                if (!empty($logoUrl)) {
                    $logoUrl = sanitize_url($logoUrl);
                }
                $payment_data['merchantLogoUrl'] = str_replace('http://', 'https://', $logoUrl);
                $payment_data['language'] = $lang;
                $payment_data['integrationType'] = 'plugin';
                $payment_data['name'] = 'Wordpress';
                $payment_data['version'] = $wp_version;
                $payment_data['pluginVersion'] = GEIDEA_ONLINE_PAYMENTS_CURRENT_VERSION;
                $payment_data['partnerId'] = $payment_obj->get_option('partner_id');
                $text = sprintf(geideaOrderResultCreated, $order->get_id(), $payment_obj->get_option('order_status_waiting'));
                $order->add_order_note($text);
                $status = str_replace('wc-', '', $payment_obj->get_option('order_status_waiting'));
                $order->update_status($status, $text);

                return json_encode($payment_data);
            } else {
                $response = [
                    'result' => 'failure',
                    'messages' => geideaOrderNotFound,
                    "refresh" => false,
                    "reload" => false,
                ];
                return json_encode($response);
            }
        } else {
            $response = [
                'result' => 'failure',
                'messages' => geideaEmptyRequest,
                "refresh" => false,
                "reload" => false,
            ];
            return json_encode($response);
        }
    }

    public function payment_fields()
    {
        if ($this->description) {
            echo esc_attr($this->description);
        }
        $this->tokenization_script();
        $this->saved_payment_methods();
        if ($this->get_option("cardOnFile") === 'yes') {
            $this->save_payment_method_checkbox();
        }
    }
}
