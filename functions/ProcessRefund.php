<?php

namespace Geidea\Functions;

trait ProcessRefund
{
    /**
     * @throws Exception
     */
    public function process_refund($order_id, $amount = null, $reason = ''): bool
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }
        $options = $this->get_options();
        $successStatus = $order->get_meta('Order Success Status Setting');
        if (
            $order->get_status() != $options['orderStatusSuccess']
            && $order->get_status() != $successStatus
        ) {
            throw new \Exception(geideaRefundNotCompletedOrderError);
        }
        $values = [];
        $values['orderId'] = $order->get_meta('Geidea Order Id');
        $callbackUrl = get_site_url() . '/?wc-api=geidea';
        $values['callbackUrl'] = str_replace('http://', 'https://', $callbackUrl);
        $values['refundAmount'] = floatval($amount);
        $merchantKey = $this->get_option('merchant_gateway_key');
        $password = $this->get_option('merchant_password');
        $result = $this->send_gi_request(
            $this->config['refundUrl'],
            $merchantKey,
            $password,
            json_encode($values)
        );
        if ($result instanceof WP_Error) {
            $error = $result->get_error_message();
            wc_add_notice($error, 'error');
            return false;
        } else {
            $decoded_result = json_decode($result["body"], true);
        }
        if (!empty($decoded_result['errors'])) {
            throw new \Exception(geideaPaymentGatewayError);
        }
        if ($decoded_result['responseCode'] != '000') {
            $error_message = sprintf(geideaRefundTransactionError, $decoded_result['detailedResponseMessage']);
            throw new \Exception($error_message);
        }
        if ($decoded_result['order']['detailedStatus'] != 'Refunded' && $decoded_result['order']['detailedStatus'] != 'PartiallyRefunded') {
            throw new \Exception(geideaRefundIncorrectStatus);
        }
        $transactions = $decoded_result['order']['transactions'];
        $refund_transaction = null;
        foreach ($transactions as $t) {
            if ($t['type'] == 'Refund') {
                $refund_transaction = $t;
            }
        }
        if (!empty($refund_transaction)) {
            $text = sprintf(
                geideaOrderRefunded,
                $reason,
                $refund_transaction["transactionId"],
                $refund_transaction["amount"]
            );
            $order->add_order_note($text);
        }
        return true;
    }
}
