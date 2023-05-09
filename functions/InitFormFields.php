<?php

namespace Geidea\Functions;

trait InitFormFields
{
    /**
     * Output form of setting payment system.
     */
    public function init_form_fields()
    {
        wp_register_style('geidea', plugins_url('../assets/css/gi-styles.css', __FILE__));
        wp_enqueue_style('geidea');

        $statuses = wc_get_order_statuses();
        $languages = array(
            "ar" => "Arabic",
            "en" => "English",
        );
        $hppprofile = array(
            "simple" => "Simple",
            "compressed" => "Compressed"
        );
        $options = get_option('woocommerce_' . $this->id . '_settings');

        $logo = $options['logo'];
        if (isset($logo)) {
            $merchantLogo = sanitize_url($logo);
        }
        $merchantLogoDescr = geideaMerchantLogoDescription;
        if (!empty($merchantLogo)) {
            $merchantLogoDescr .= '</br><img src="' . esc_html($merchantLogo) . '" width="70"></br>';
        }

        $checkoutIcon = $options['checkout_icon'];
        $checkoutIcon = sanitize_text_field($checkoutIcon);
        $checkoutIconDescr = geideaCheckoutIconDescription;
        if (empty($checkoutIcon)) {
            $checkoutIcon = plugins_url('../assets/imgs/geidea-logo.svg', __FILE__);
        }
        $checkoutIconDescr .= '</br><img src="' . esc_html($checkoutIcon) . '" width="70"></br>';

        $available_currencies = explode(",", $options['available_currencies']);
        $currency_options = ['' => ''];
        foreach ($available_currencies as $currency) {
            $currency_options[$currency] = $this->config['currenciesMapping'][$currency];
        }

        $availablePaymentMethods = [];
        foreach (explode(",", $options['avaliable_payment_methods']) as $paymentMethod) {
            $availablePaymentMethods[] = $this->config['paymentMethodsMapping'][$paymentMethod];
        }

        $disable_extra_fields = !$options['valid_creds'];

        if ($availablePaymentMethods) {
            $default_title = implode(", ", $availablePaymentMethods);
        } else {
            $default_title = geideaAvailablePaymentMethodsByDefault;
        }

        $this->form_fields = array(
            'enabled' => array(
                'title' => geideaSettingsActive,
                'type' => 'checkbox',
                'label' => ' ',
                'default' => 'no',
            ),
            'merchant_gateway_key' => array(
                'title' => geideaSettingsMerchant . ' *',
                'type' => 'text',
                'description' => '<p class="geidea-error-message">' . geideaInvalidCredentials . '</p>',
                'default' => '',
                'class' => 'geidea-merchant-gateway-key',
            ),
            'merchant_password' => array(
                'title' => geideaSettingsPassword . ' *',
                'type' => 'text',
                'description' => '',
                'default' => '',
                'class' => 'geidea-merchant-password',
            ),
            'title' => array(
                'title' => geideaSettingsName,
                'type' => 'text',
                'default' => $default_title,
                'class' => 'geidea-extra-field',
                'description' => sprintf(geideaForExample, $default_title),
                'disabled' => $disable_extra_fields,
            ),
            'description' => array(
                'title' => geideaSettingsDesc,
                'type' => 'textarea',
                'description' => geideaDescriptionHint,
                'default' => geideaTitleDesc,
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'currency_id' => array(
                'title' => geideaSettingsCurrency . ' *',
                'type' => 'select',
                'options' => $currency_options,
                'default' => '',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'checkout_icon' => array(
                'title' => geideaCheckoutIcon,
                'type' => 'file',
                'description' => $checkoutIconDescr,
                'default' => '',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'logo' => array(
                'title' => geideaMerchantLogo,
                'type' => 'file',
                'description' => $merchantLogoDescr,
                'default' => '',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'header_color' => array(
                'title' => geideaSettingsHeaderColor,
                'type' => 'text',
                'description' => geideaSettingsHeaderColorDesc,
                'default' => '',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'hide_GeideaLogo' => array(
                'title' => geideaSettingsHideLogoEnabled,
                'type' => 'checkbox',
                'label' => ' ',
                'default' => 'no',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'partner_id' => array(
                'title' => geideaSettingsPartnerId,
                'type' => 'text',
                'description' => '',
                'default' => '',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'receipt_enabled' => array(
                'title' => geideaSettingsReceiptEnabled,
                'type' => 'checkbox',
                'label' => ' ',
                'default' => 'no',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'email_enabled' => array(
                'title' => geideaSettingsEmailEnabled,
                'type' => 'checkbox',
                'label' => ' ',
                'default' => 'no',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'phonenumber_enabled' => array(
                'title' => geideaSettingsPhoneNumberEnabled,
                'type' => 'checkbox',
                'label' => ' ',
                'default' => 'no',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'address_enabled' => array(
                'title' => geideaSettingsAddressEnabled,
                'type' => 'checkbox',
                'label' => ' ',
                'default' => 'no',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'cardOnFile' => array(
                'title' => geideaSettingsCardOnFileEnabled,
                'type' => 'checkbox',
                'label' => ' ',
                'default' => 'no',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'language' => array(
                'title' => geideaSettingsLanguage,
                'type' => 'select',
                'options' => $languages,
                'default' => 'en',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'hppprofile' => array(
                'title' => geideaSettingsHppProfile,
                'type' => 'select',
                'options' => $hppprofile,
                'default' => 'simple',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields
            ),
            'order_status_success' => array(
                'title' => geideaSettingsOrderStatusSuccess,
                'type' => 'select',
                'options' => $statuses,
                'default' => 'processing',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'order_status_waiting' => array(
                'title' => geideaSettingsOrderStatusWaiting,
                'type' => 'select',
                'options' => $statuses,
                'default' => 'wc-pending',
                'class' => 'geidea-extra-field',
                'disabled' => $disable_extra_fields,
            ),
            'needs_setup' => array(
                'type' => 'hidden',
                'default' => 'true',
                'class' => 'geidea-extra-field-hidden',
            ),
            'valid_creds' => array(
                'type' => 'hidden',
                'default' => 'false',
                'class' => 'geidea-extra-field-hidden',
            ),
            'available_currencies' => array(
                'type' => 'hidden',
                'default' => '',
                'class' => 'geidea-extra-field-hidden',
            ),
            'avaliable_payment_methods' => array(
                'type' => 'hidden',
                'default' => '',
                'class' => 'geidea-extra-field-hidden',
            ),
        );
    }
}
