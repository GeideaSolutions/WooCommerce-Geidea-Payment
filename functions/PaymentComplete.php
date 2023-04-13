<?php

/**
 * 
 */
trait PaymentComplete
{
    public function payment_complete($order)
    {
        do_action('woocommerce_pre_payment_complete', $order->get_id());

        if (null !== WC()->session) {
            WC()->session->set('order_awaiting_payment', false);
        }
        if ($order->get_id()) {
            $order_needs_processing = false;
            if (sizeof($order->get_items()) > 0) {
                foreach ($order->get_items() as $item) {
                    if ($_product = $order->get_product_from_item($item)) {
                        $virtual_downloadable_item = $_product->is_downloadable() && $_product->is_virtual();

                        if (apply_filters('woocommerce_order_item_needs_processing', !$virtual_downloadable_item, $_product, $order->get_id())) {
                            $order_needs_processing = true;
                            break;
                        }
                    } else {
                        $order_needs_processing = true;
                        break;
                    }
                }
            }

            $options = $this->get_options();
            $order->update_status(apply_filters('woocommerce_payment_complete_order_status', str_replace('wc-', '', $options["orderStatusSuccess"]), $order->get_id()));

            add_post_meta($order->get_id(), '_paid_date', current_time('mysql'), true);

            // Payment is complete so reduce stock levels
            if (apply_filters('woocommerce_payment_complete_reduce_order_stock', !get_post_meta($order->get_id(), '_order_stock_reduced', true), $order->get_id())) {
                $order->reduce_order_stock();
            }
            do_action('woocommerce_payment_complete', $order->get_id());
        } else {
            do_action('woocommerce_payment_complete_order_status_' . $order->get_status(), $order->get_id());
        }
    }
}
