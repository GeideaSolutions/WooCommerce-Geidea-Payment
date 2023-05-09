<?php

namespace Geidea\Functions;

/**
 * Admin options
 */
trait AdminOptions
{
    /**
     * Admin Panel Options
     * The output html form - settings to the admin panel
     * */
    public function admin_options()
    {
        wp_register_script('geidea-admin-script', plugins_url('../assets/js/admin-script.js', __FILE__));
        wp_enqueue_script('geidea-admin-script');
        $options = get_option('woocommerce_' . $this->id . '_settings');
        $settings = [];
        $settings['needs_setup'] = true;
        if (isset($options['needs_setup'])) {
            $settings['needs_setup'] = filter_var($options['needs_setup'], FILTER_VALIDATE_BOOLEAN);
        }
        $settings['valid_creds'] = false;
        if (isset($options['valid_creds'])) {
            $settings['valid_creds'] = filter_var($options['valid_creds'], FILTER_VALIDATE_BOOLEAN);
        }
        $are_valid_credentials = $settings['valid_creds'];
        $merchant_config = [];
        if ($settings['needs_setup']) {
            if (!empty($options['merchant_gateway_key'])) {
                $result = $this->get_merchant_config(
                    $options['merchant_gateway_key'],
                    $options['merchant_password']
                );
                if ($result['errors']) {
                    $are_valid_credentials = false;
                } else {
                    $are_valid_credentials = true;
                    $merchant_config = $result['config'];
                }
            }
        }

        if (!$are_valid_credentials) { ?>
            <script>
                jQuery(function($) {
                    let $geideaMerchantGatewayKey = $("#woocommerce_geidea_merchant_gateway_key");
                    if ($geideaMerchantGatewayKey.val()) {
                        $('.geidea-error-message').each(function(i, obj) {
                            obj.style.display = "block";
                        });
                        $geideaMerchantGatewayKey.addClass('geidea-invalid-field');
                    }
                })
            </script>
        <?php }
        ?>
        <h1><?php echo esc_html(geideaTitle) ?></h1>
        <p><em><?php echo esc_html(geideaEditableFieldsHint) ?></em></p>
        <table class="form-table">
            <?php
            //Generate the HTML for the settings form.
            $form_fields = $this->get_form_fields();
            if ($are_valid_credentials && $settings['needs_setup']) {
                $currency_options = [];
                foreach ($merchant_config['currencies'] as $currency) {
                    $currency_options[$currency] = $this->config['currenciesMapping'][$currency];
                }
                $form_fields['currency_id']['options'] = $currency_options;
                $availablePaymentMethods = [];
                foreach ($merchant_config['paymentMethods'] as $paymentMethod) {
                    $availablePaymentMethods[] = $this->config['paymentMethodsMapping'][$paymentMethod];
                }
                if ($merchant_config['applePay']['isApplePayWebEnabled']) {
                    $availablePaymentMethods[] = $this->config['paymentMethodsMapping']['applepay'];
                }
                if ($availablePaymentMethods) {
                    $default_title = implode(", ", $availablePaymentMethods);
                } else {
                    $default_title = geideaAvailablePaymentMethodsByDefault;
                }
                $form_fields['title']['description'] = sprintf(geideaForExample, $default_title);
            }

            $this->generate_settings_html($form_fields);
            ?>
        </table>
<?php
    }

    /*
 * Returns array with merchant config and errors if they occurred
 */
    private function get_merchant_config($merchant_key, $password = ''): array
    {
        $errors = [];
        $config = [];
        $response = $this->send_gi_request(
            $this->config['merchantConfigUrl'] . '/' . $merchant_key,
            $merchant_key,
            $password,
            [],
            'GET'
        );

        if ($response instanceof WP_Error) {
            $error = $response->get_error_message();
            $errors[] = $error;
            return [
                'config' => $config,
                'errors' => $errors,
            ];
        }

        $decoded_response = json_decode($response["body"], true);

        if (!empty($decoded_response["errors"])) {
            foreach ($decoded_response["errors"] as $v) {
                foreach ($v as $error) {
                    $errors[] = $error;
                }
            }
        }

        if (isset($decoded_response["detailedResponseMessage"])) {
            if ($decoded_response["responseMessage"] != 'Success') {
                $errors[] = $decoded_response["detailedResponseMessage"];
            } else {
                $config = $decoded_response;
            }
        }

        return [
            'config' => $config,
            'errors' => $errors,
        ];
    }

    public function process_admin_options(): bool
    {
        function generate_logo_filename($dir, $name, $ext): string
        {
            return "logo_" . bin2hex(random_bytes(16)) . $ext;
        }
        $this->init_settings();
        $post_data = $this->get_post_data();
        foreach ($this->get_form_fields() as $key => $field) {
            if ('title' !== $this->get_field_type($field) && ($key !== 'logo' && $key !== 'checkout_icon')) {
                try {
                    $this->settings[$key] = $this->get_field_value($key, $field, $post_data);
                } catch (Exception $e) {
                    $this->add_error($e->getMessage());
                }
            }
        }
        if (isset($_FILES['woocommerce_geidea_logo']) && ($_FILES['woocommerce_geidea_logo']['size'] > 0)) {
            $upload_errors = $this->upload_logo($_FILES['woocommerce_geidea_logo'], 'logo');
            $this->errors = array_merge($this->errors, $upload_errors);
        }
        if (isset($_FILES['woocommerce_geidea_checkout_icon']) && ($_FILES['woocommerce_geidea_checkout_icon']['size'] > 0)) {
            $upload_errors = $this->upload_logo($_FILES['woocommerce_geidea_checkout_icon'], 'checkout_icon');
            $this->errors = array_merge($this->errors, $upload_errors);
        }
        if (!empty($this->errors)) {
            $this->enabled = false;
            $this->settings['enabled'] = 'no';
            foreach ($this->errors as $v) {
                WC_Admin_Settings::add_error($v);
            }
        }
        $this->settings['return_url'] = 'yes';
        $are_valid_credentials = false;
        if (!empty($this->settings['merchant_gateway_key'])) {
            $result = $this->get_merchant_config($this->settings['merchant_gateway_key'], $this->settings['merchant_password']);
            if (!$result['errors']) {
                $are_valid_credentials = true;
                $merchant_config = $result['config'];

                $this->settings['available_currencies'] = implode(',', $merchant_config['currencies']);

                $availablePaymentMethods = [];
                foreach ($merchant_config['paymentMethods'] as $paymentMethod) {
                    $availablePaymentMethods[] = $paymentMethod;
                }
                if ($merchant_config['applePay']['isApplePayWebEnabled']) {
                    $availablePaymentMethods[] = 'applepay';
                }
                $this->settings['avaliable_payment_methods'] = implode(',', $availablePaymentMethods);
            }
        }
        if (!$are_valid_credentials) {
            $this->settings['valid_creds'] = false;
            if (!empty($this->settings['merchant_gateway_key'])) {
                WC_Admin_Settings::add_error(geideaInvalidCredentials);
            }
        } else {
            $this->settings['valid_creds'] = true;
        }
        if ($this->settings['needs_setup'] == 'true') {
            $this->settings['needs_setup'] = 'false';
        }
        return update_option($this->get_option_key(), apply_filters('woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings), 'yes');
    }

    /**
     * Return errors if occurred
     *
     * @param $logo
     * @param $field
     * @return array
     */
    public function upload_logo($logo, $field): array
    {
        $errors = [];
        $arr_file_type = wp_check_filetype(basename($logo['name']));
        $uploaded_file_type = $arr_file_type['type'];
        $allowed_file_types = array('image/jpg', 'image/jpeg', 'image/png', 'image/svg+xml');
        if (in_array($uploaded_file_type, $allowed_file_types)) {
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $uploaded_file = wp_handle_upload($logo, array('test_form' => false, 'unique_filename_callback' => 'generate_logo_filename'));
            if (isset($uploaded_file['file'])) {
                $this->settings[$field] = $uploaded_file['url'];
            } else {
                $errors[] = geideaFileUploadingError;
            }
        } else {
            $errors[] = geideaWrongFileType;
        }
        return $errors;
    }
}
