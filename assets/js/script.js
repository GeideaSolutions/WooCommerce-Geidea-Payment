let y_offsetWhenScrollDisabled = 0;
function disableScrollOnBody() {
    y_offsetWhenScrollDisabled = jQuery(window).scrollTop();
    jQuery('body').addClass('scrollDisabled').css('margin-top', -y_offsetWhenScrollDisabled);
}

function enableScrollOnBody() {
    jQuery('body').removeClass('scrollDisabled').css('margin-top', 0);
    jQuery(window).scrollTop(y_offsetWhenScrollDisabled);
}

let onError = function (error) {
    enableScrollOnBody();
    jQuery("#place_order").removeAttr("disabled");
    alert("Geidea Payment Gateway error: " + error.responseMessage);
}

let onCancel = function () {
    enableScrollOnBody();
    jQuery("#place_order").removeAttr("disabled");
}

const startV2HPP = (data) => {
    console.log("Session create API response", data);
    disableScrollOnBody();
    let onSuccess = function (_message, _statusCode) {
        setTimeout(document.location.href = data.successUrl, 1000);
    }
    try {
        if (data.responseCode !== '000') {
            throw data
        }
        const api = new GeideaCheckout(onSuccess, onError, onCancel);
        api.startPayment(data.session.id);
    } catch (error) {
        let receivedError;
        const errorFields = [];

        if (error.status && error.errors) {
            const errorsObject = error.errors;

            for (const key of Object.keys(errorsObject)) {
                errorFields.push(key.replace('$.', ''))
            }
            receivedError = {
                responseCode: '100',
                responseMessage: 'Field formatting errors',
                detailResponseMessage: `Fields with errors: ${errorFields.toString()}`,
                reference: error.reference,
                detailResponseCode: null,
                orderId: null
            }
        } else {
            receivedError = {
                responseCode: error.responseCode,
                responseMessage: error.responseMessage,
                detailResponseMessage: error.detailedResponseMessage,
                detailResponseCode: error.detailedResponseCode,
                orderId: null,
                reference: error.reference,
            }
        }
        onError(receivedError);
    }
}