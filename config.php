<?php
$env = $this->get_option('geidea_environment');

if ($env === "EGY-PROD") {
    return [
        "payByTokenUrl" => "https://api.merchant.geidea.net/pgw/api/v1/direct/pay/token",
        "refundUrl" => "https://api.merchant.geidea.net/pgw/api/v1/direct/refund",
        "merchantConfigUrl" => "https://api.merchant.geidea.net/pgw/api/v1/config",
        "jsSdkUrl" => "https://www.merchant.geidea.net/hpp/geideaCheckout.min.js",
        "createSessionUrl" => "https://api.merchant.geidea.net/payment-intent/api/v2/direct/session",
        "availableCurrencies" => [
            'SAR',
            'USD',
            'EGP'
        ],
        "currenciesMapping" => [
            'USD' => 'US Dollar',
            'SAR' => 'Saudi Riyal',
            'EGP' => 'Egyptian Pound',
            'AED' => 'United Arab Emirates Dirham',
            'BHD' => 'Bahraini Dinar',
            'EUR' => 'Euro',
            'GBP' => 'Pound Sterling',
            'KWD' => 'Kuwaiti Dinar',
            'OMR' => 'Rial Omani',
            'QAR' => 'Qatari Rial'
        ],
        "paymentMethodsMapping" => [
            'mada' => 'Mada',
            'visa' => 'VISA',
            'mastercard' => 'MasterCard',
            'meeza' => 'Meeza',
            'applepay' => 'Apple Pay'
        ]
    ];
} elseif ($env === "KSA-PROD") {
    return [
        "payByTokenUrl" => "https://api.ksamerchant.geidea.net/pgw/api/v1/direct/pay/token",
        "refundUrl" => "https://api.ksamerchant.geidea.net/pgw/api/v1/direct/refund",
        "merchantConfigUrl" => "https://api.ksamerchant.geidea.net/pgw/api/v1/config",
        "jsSdkUrl" => "https://www.ksamerchant.geidea.net/hpp/geideaCheckout.min.js",
        "createSessionUrl" => "https://api.ksamerchant.geidea.net/payment-intent/api/v2/direct/session",
        "availableCurrencies" => [
            'SAR',
            'USD',
            'EGP'
        ],
        "currenciesMapping" => [
            'USD' => 'US Dollar',
            'SAR' => 'Saudi Riyal',
            'EGP' => 'Egyptian Pound',
            'AED' => 'United Arab Emirates Dirham',
            'BHD' => 'Bahraini Dinar',
            'EUR' => 'Euro',
            'GBP' => 'Pound Sterling',
            'KWD' => 'Kuwaiti Dinar',
            'OMR' => 'Rial Omani',
            'QAR' => 'Qatari Rial'
        ],
        "paymentMethodsMapping" => [
            'mada' => 'Mada',
            'visa' => 'VISA',
            'mastercard' => 'MasterCard',
            'meeza' => 'Meeza',
            'applepay' => 'Apple Pay'
        ]
    ];
} elseif ($env === "UAE-PROD") {
    return [
        "payByTokenUrl" => "https://api.merchant.geidea.ae/pgw/api/v1/direct/pay/token",
        "refundUrl" => "https://api.merchant.geidea.ae/pgw/api/v1/direct/refund",
        "merchantConfigUrl" => "https://api.merchant.geidea.ae/pgw/api/v1/config",
        "jsSdkUrl" => "https://www.merchant.geidea.ae/hpp/geideaCheckout.min.js",
        "createSessionUrl" => "https://api.merchant.geidea.ae/payment-intent/api/v2/direct/session",
        "availableCurrencies" => [
            'SAR',
            'USD',
            'EGP'
        ],
        "currenciesMapping" => [
            'USD' => 'US Dollar',
            'SAR' => 'Saudi Riyal',
            'EGP' => 'Egyptian Pound',
            'AED' => 'United Arab Emirates Dirham',
            'BHD' => 'Bahraini Dinar',
            'EUR' => 'Euro',
            'GBP' => 'Pound Sterling',
            'KWD' => 'Kuwaiti Dinar',
            'OMR' => 'Rial Omani',
            'QAR' => 'Qatari Rial'
        ],
        "paymentMethodsMapping" => [
            'mada' => 'Mada',
            'visa' => 'VISA',
            'mastercard' => 'MasterCard',
            'meeza' => 'Meeza',
            'applepay' => 'Apple Pay'
        ]
    ];
}
