function onDOMLoaded() {
    setTimeout(function() {
        giPaymentWrapper();
    }, 500);
}

document.addEventListener("DOMContentLoaded", onDOMLoaded);

function startGIPayment(merchantGatewayKey, orderId, amount, currencyId, callbackUrl, successUrl, cardOnFile, customerEmail, billingAddress, merchantLogoUrl, headerColor, billingAddressString, shippingAddressString) {
    try {
        var onSuccess = function(_message, _statusCode) {
            setTimeout(document.location.href = successUrl, 1000);
        }

        var onError = function(error) {
            var div = document.getElementById("gi_payment_errors").style.display = "block";
            var error_span = document.getElementById("gi_payment_error_message");
            error_span.innerHTML = error.responseMessage;
        }

        var onCancel = function() {
            document.location.href = "/";
        }

        var billingAddress = JSON.parse(billingAddressString);
        var shippingAddress = JSON.parse(shippingAddressString);

        var api = new GeideaApi(merchantGatewayKey, onSuccess, onError, onCancel);

        api.configurePayment({
            callbackUrl: callbackUrl,
            amount: amount,
            currency: currencyId,
            merchantReferenceId: orderId,
            cardOnFile: Boolean(cardOnFile),
            initiatedBy: "Internet",
            customerEmail: customerEmail,
            billingAddress: billingAddress,
            shippingAddress: shippingAddress,
            merchantLogoUrl: merchantLogoUrl,
            styles: { "headerColor": headerColor }
        });
        api.startPayment();
    } catch (err) {
        alert(err);
    }
}