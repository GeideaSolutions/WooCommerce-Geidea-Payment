<?php

/**
 * 
 */
trait Address
{
    public function get_formatted_billing_address($order): array
    {
        $billing_street = $order->get_billing_address_1();
        $billing_street .= " " . $order->get_billing_address_2();
        return [
            'country' => sanitize_text_field(
                $this->functions->convert_country_code($order->get_billing_country())
            ),
            'street' => sanitize_text_field($billing_street),
            'city' => sanitize_text_field($order->get_billing_city()),
            'postcode' => sanitize_text_field($order->get_billing_postcode()),
        ];
    }

    public function get_formatted_shipping_address($order): array
    {
        $shipping_street = $order->get_shipping_address_1();
        $shipping_street .= " " . $order->get_shipping_address_2();
        return [
            'country' => sanitize_text_field(
                $this->functions->convert_country_code($order->get_shipping_country())
            ),
            'street' => sanitize_text_field($shipping_street),
            'city' => sanitize_text_field($order->get_shipping_city()),
            'postcode' => sanitize_text_field($order->get_shipping_postcode()),
        ];
    }
}
