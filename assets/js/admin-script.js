function onDOMLoaded() {
    setTimeout(function() {
        document.getElementById('woocommerce_geidea_merchant_gateway_key').autocomplete = "off";
        document.getElementById('woocommerce_geidea_merchant_password').autocomplete = "off";

    }, 500);

    jQuery('.geidea-extra-field-hidden').each(function(i, obj) {
        obj.closest('tr').style.display = "none";
    });

};

document.addEventListener("DOMContentLoaded", onDOMLoaded);