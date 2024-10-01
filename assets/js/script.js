let geidea_y_offsetWhenScrollDisabled = 0;
function geidea_disableScrollOnBody() {
    geidea_y_offsetWhenScrollDisabled = jQuery(window).scrollTop();
    jQuery('body').addClass('scrollDisabled').css('margin-top', -geidea_y_offsetWhenScrollDisabled);
}

function geidea_enableScrollOnBody() {
    jQuery('body').removeClass('scrollDisabled').css('margin-top', 0);
    jQuery(window).scrollTop(geidea_y_offsetWhenScrollDisabled);
}

let geidea_onError = function (error) {
    geidea_enableScrollOnBody();
    jQuery("#place_order").removeAttr("disabled");
    alert("Geidea Payment Gateway error: " + error.responseMessage);
}

let geidea_onCancel = function () {
    geidea_enableScrollOnBody();
    jQuery("#place_order").removeAttr("disabled");
}

const geidea_startV2HPP = (data) => {
    console.log("Session create API response", data);
    geidea_disableScrollOnBody();
    let onSuccess = function (_message, _statusCode) {
        setTimeout(document.location.href = data.successUrl, 1000);
    }
    try {
        if (data.responseCode !== '000') {
            throw data
        }
        const api = new GeideaCheckout(onSuccess, geidea_onError, geidea_onCancel);
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
        geidea_onError(receivedError);
    }
}