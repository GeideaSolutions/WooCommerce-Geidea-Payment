<?php
return [
    "payByTokenUrl" => "https://api.merchant.geidea.net/pgw/api/v1/direct/pay/token",
    "refundUrl" => "https://api.merchant.geidea.net/pgw/api/v1/direct/refund",
    "merchantConfigUrl" => "https://api.merchant.geidea.net/pgw/api/v1/config", 
    "jsSdkUrl" => "https://www.merchant.geidea.net/hpp/geideapay.min.js",
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
?>