<?php

namespace Geidea\Functions;

/**
 * Card Options
 */
trait CardOptions
{
    private function get_card_icon($icon_name): string
    {
        $iconPath = GEIDEA_DIR . "assets/imgs/icons/$icon_name.svg";
        $icon = '';
        if (file_exists($iconPath)) {
            $icon = GEIDEA_ICONS_URL . "$icon_name.svg";
        }

        return $icon;
    }

    public function get_saved_payment_method_option_html($token)
    {
        $icon_url = $this->get_card_icon($token->get_data()['card_type']);

        $html = sprintf(
            '<li class="woocommerce-SavedPaymentMethods-token">
                <input id="wc-%1$s-payment-token-%2$s" type="radio" name="wc-%1$s-payment-token" value="%2$s" style="width:auto;" class="woocommerce-SavedPaymentMethods-tokenInput" %4$s />
                <img class="gi-card-icon" src="%5$s" />
                <label for="wc-%1$s-payment-token-%2$s">%3$s</label>
            </li>',
            esc_attr($this->id),
            esc_attr($token->get_id()),
            esc_html($token->get_display_name()),
            checked($token->is_default(), true, false),
            esc_attr($icon_url)
        );

        return apply_filters('woocommerce_payment_gateway_get_saved_payment_method_option_html', $html, $token, $this);
    }
}
