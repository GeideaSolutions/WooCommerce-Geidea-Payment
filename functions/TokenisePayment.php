<?php
trait TokenisePayment
{

    public function tokenise_payment($order, $token): bool
    {
        $params = [];

        $order_currency = $order->get_currency();
        $available_currencies = $this->config['availableCurrencies'];

        $result_currency = in_array($order_currency, $available_currencies) ? $order_currency : $this->get_option('currency_id');
        $params["currency"] = $result_currency;

        $params["amount"] = number_format($order->get_total(), 2, '.', '');

        $params["tokenId"] = $token->get_token();

        $params["initiatedBy"] = "Internet";
        $params["merchantReferenceId"] = (string) $order->get_id();

        $callbackUrl = get_site_url() . '/?wc-api=geidea';
        // Force https for Geidea Gateway
        $params['callbackUrl'] = str_replace('http://', 'https://', $callbackUrl);

        $merchantKey = $this->get_option('merchant_gateway_key');
        $password = $this->get_option('merchant_password');

        $result = $this->functions->send_gi_request(
            $this->config['payByTokenUrl'],
            $merchantKey,
            $password,
            $params
        );

        if ($result instanceof WP_Error) {
            $error = $result->get_error_message();
            wc_add_notice($error, 'error');
            return false;
        } else {
            $decoded_result = json_decode($result["body"], true);
        }

        if (!empty($decoded_result["errors"])) {
            foreach ($decoded_result["errors"] as $v) {
                foreach ($v as $error) {
                    wc_add_notice($error, 'error');
                }
            }
            return false;
        }

        if (isset($decoded_result["detailedResponseMessage"])) {
            if ($decoded_result["responseMessage"] != 'Success') {
                wc_add_notice($decoded_result["detailedResponseMessage"], 'error');
                return false;
            }
        }

        return true;
    }

    public function save_token($token_id, $card_number, $expiry_date, $card_type, $user_id)
    {
        WC_Payment_Tokens::get($token_id);

        $token_exists = false;
        $all_tokens = WC_Payment_Tokens::get_customer_tokens($user_id, $this->id);

        foreach ($all_tokens as $t) {
            if ($t->get_token() == $token_id) {
                $token_exists = true;
            }
        }

        if (!$token_exists) {
            $new_token = new WC_Payment_Token_CC();
            $new_token->set_token($token_id); // Token comes from payment processor
            $new_token->set_gateway_id($this->id);
            $new_token->set_last4($card_number);
            $new_token->set_expiry_year("20" . $expiry_date['year']);
            $new_token->set_expiry_month((string) $expiry_date['month']);
            $new_token->set_card_type($card_type);
            $new_token->set_user_id($user_id);
            // Save the new token to the database
            $new_token->save();
        } else {
            echo "The token already exists!";
        }
    }

    private function get_token()
    {
        $token_id = sanitize_key(wp_verify_nonce(sanitize_text_field(wp_unslash(isset($_POST[$this->token_id_param])))));

        if (!$token_id) {
            return null;
        }

        if ($token_id === 'new') {
            return false;
        }

        $token = WC_Payment_Tokens::get($token_id);

        if ($token->get_user_id() !== get_current_user_id()) {
            return null;
        }

        return $token;
    }

    public static function tokens_table()
    {
        $second_action = (wp_verify_nonce(sanitize_text_field(wp_unslash(isset($_POST['action2']))))) ? sanitize_key($_POST['action2']) : false;
        if ($second_action == 'delete') {
            foreach ($_POST as $k => $param) {
                $san_param = sanitize_key($k);
                if (str_starts_with($san_param, "delete_token")) {
                    $token_id = str_replace("delete_token_", "", $san_param);
                    WC_Gateway_Geidea::delete_token($token_id);
                }
            }
        }

        if (wp_verify_nonce(sanitize_text_field(wp_unslash(isset($_GET['action']))))) {
            $san_token = (wp_verify_nonce(sanitize_text_field(wp_unslash(isset($_GET['token']))))) ? sanitize_key($_GET['token']) : false;
            if ($san_token && is_numeric($san_token)) {
                $token_id = (int) $san_token;
                WC_Gateway_Geidea::delete_token($token_id);
            }
        }

?>
        <div class="wrap">
            <h2><?php echo geideaTokensTitle ?></h2>
            <form method="post">
                <?php
                render_tokens_table();
                ?>
            </form>
        </div>
<?php
    }

    private function delete_token($token_id)
    {
        WC_Payment_Tokens::delete((int) $token_id);
    }
}
