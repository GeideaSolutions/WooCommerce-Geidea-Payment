<?php

namespace Geidea\Functions;

use WC_Payment_Tokens;

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

    public function need_to_save_new_card($user_id): bool
    {
        $token_id = sanitize_key(wp_verify_nonce(sanitize_text_field(wp_unslash(isset($_POST[$this->token_id_param])))));
        $save_token = sanitize_key(wp_verify_nonce(sanitize_text_field(wp_unslash(isset($_POST[$this->tokenise_param])))));

        $all_tokens = WC_Payment_Tokens::get_customer_tokens($user_id, $this->id);

        // if token is new or there are no tokens for this customer
        if (($token_id === 'new' || !$all_tokens) && $save_token) {
            return true;
        } else {
            return false;
        }
    }
}
