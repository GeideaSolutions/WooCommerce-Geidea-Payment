var y_offsetWhenScrollDisabled = 0;

function disableScrollOnBody() {
    y_offsetWhenScrollDisabled = jQuery(window).scrollTop();
    jQuery('body').addClass('scrollDisabled').css('margin-top', -y_offsetWhenScrollDisabled);
}

function enableScrollOnBody() {
    jQuery('body').removeClass('scrollDisabled').css('margin-top', 0);
    jQuery(window).scrollTop(y_offsetWhenScrollDisabled);
}

function initGIPaymentOnCheckoutPage(data) {
    disableScrollOnBody();

    try {
        var onSuccess = function(_message, _statusCode) {
            setTimeout(document.location.href = data.successUrl, 1000);
        }

        var onError = function(error) {
            enableScrollOnBody();
            jQuery("#place_order").removeAttr("disabled");
            alert("Geidea Payment Gateway error: " + error.responseMessage);
        }

        var onCancel = function() {
            enableScrollOnBody();
            jQuery("#place_order").removeAttr("disabled");
        }

        var billingAddress = JSON.parse(data.billingAddress);
        var shippingAddress = JSON.parse(data.shippingAddress);

        var api = new GeideaApi(data.merchantGatewayKey, onSuccess, onError, onCancel);

        api.configurePayment({
            callbackUrl: data.callbackUrl,
            amount: parseFloat(data.amount),
            currency: data.currencyId,
            merchantReferenceId: data.orderId.toString(),
            cardOnFile: Boolean(data.cardOnFile),
            initiatedBy: "Internet",
            email: data.customerEmail,
            showPhone: true,
            customerPhoneNumber: data.customerPhoneNumber,
            address: {
                showAddress: false,
                billing: billingAddress,
                shipping: shippingAddress
            },
            merchantLogoUrl: data.merchantLogoUrl,
            language: data.language,
            styles: { "headerColor": data.headerColor },
            integrationType: data.integrationType,
            name: data.name,
            version: data.version,
            pluginVersion: data.pluginVersion,
            partnerId: data.partnerId,
            isTransactionReceiptEnabled: data.receiptEnabled === "yes"
        });
        api.startPayment();

    } catch (err) {
        enableScrollOnBody();
        alert(err);
    }
}