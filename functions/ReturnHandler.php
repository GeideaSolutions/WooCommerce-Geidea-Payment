<?php

namespace Geidea\Functions;

use Exception;

/**
 * ReturnHandler
 */
trait ReturnHandler
{
    /**
     * Return handler for Hosted Payments
     */
    public function return_handler()
    {
        try {
            $json_body = file_get_contents("php://input");
            $result = json_decode($json_body, true);

            if ($result == null) {
                echo esc_html("Invalid request!");
                http_response_code(400);
                die();
            }

            $order = $result["order"];
            $callback_signature = $result["signature"];

            if ($order["merchantReferenceId"] == null) {
                echo esc_html("Order id is not defined!");
                http_response_code(400);
                die();
            }

            $merchant_key = $this->get_option('merchant_gateway_key');
            $currency = $order['currency'];
            $order_id = $order['orderId'];
            $order_status = $order['status'];
            $merchant_reference_id = $order['merchantReferenceId'];
            $merchant_password = $this->get_option('merchant_password');
            $timeStamp = $result["timeStamp"];

            $amount = number_format($order['amount'], 2, '.', '');

            $result_string = $merchant_key . $amount . $currency . $order_id . $order_status . $merchant_reference_id. $timeStamp;

            $hash = hash_hmac('sha256', $result_string, $merchant_password, true);
            $result_signature = base64_encode($hash);

            if ($result_signature != $callback_signature) {
                echo esc_html("Invalid signature!");
                http_response_code(400);
                die();
            }

            try {
                $wc_order = wc_get_order($order["merchantReferenceId"]);
            } catch (Exception $e) {
                echo esc_html("Order with id " . $order["merchantReferenceId"] . " not found!");
                http_response_code(404);
                die();
            }

            $options = $this->get_options();
            if (
                mb_strtolower($order["status"]) == "success" &&
                mb_strtolower($order["detailedStatus"]) == "paid"
            ) {
                //save token block
                $user_id = $wc_order->get_user_id();
                if ($order["cardOnFile"] && $user_id != 0) {
                    $token_id = $order["tokenId"];
                    $card_number = substr($order["paymentMethod"]["maskedCardNumber"], -4);
                    $expiry_date = $order["paymentMethod"]["expiryDate"];
                    $card_type = $order["paymentMethod"]["brand"];

                    $this->save_token($token_id, $card_number, $expiry_date, $card_type, $user_id);
                }

                $wc_order->update_meta_data('Geidea Order Id', $order_id);
                $wc_order->update_meta_data('Merchant Reference Id', $merchant_reference_id);
                $wc_order->update_meta_data('Order Success Status Setting', "wc-" . $options["orderStatusSuccess"]);

                $this->payment_complete($wc_order);
                // Remove cart
                WC()->cart->empty_cart();

                echo esc_html("Order is completed!");
                http_response_code(200);
                die();
            } elseif (
                mb_strtolower($order["status"]) == "failed" &&
                $wc_order->get_status() != $options["orderStatusSuccess"]
            ) {
                $last_transaction = end($order['transactions']);
                $codes = $last_transaction['codes'];

                $text = sprintf(
                    "%s: %s; %s: %s",
                    $codes["responseCode"],
                    $codes["responseMessage"],
                    $codes["detailedResponseCode"],
                    $codes["detailedResponseMessage"]
                );
                $wc_order->add_order_note($text);
                $wc_order->update_meta_data('Geidea Order Id', $order_id);
                $wc_order->update_meta_data('Merchant Reference Id', $merchant_reference_id);
                $wc_order->update_meta_data('Detailed payment gate response message', $text);
                $wc_order->update_meta_data('Order Success Status Setting', "wc-" . $options["orderStatusSuccess"]);

                $wc_order->update_status(apply_filters('woocommerce_payment_complete_order_status', 'failed', $wc_order->id));

                echo esc_html("Payment failed!");
                http_response_code(200);
            } else {
                echo esc_html("Not found!");
                http_response_code(404);
            }
            die();
        } catch (Exception $return_handler_exc) {
            echo esc_html("Internal Server Error!");
            echo esc_html($return_handler_exc);
            http_response_code(500);
            die();
        }
    }
}
