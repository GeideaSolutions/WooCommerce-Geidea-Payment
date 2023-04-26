<?php

namespace Geidea\Functions;

/**
 * Checkout
 */
trait Checkout
{
    public function checkout_js_order_handler()
    {
        $is_checkout_page = is_checkout() && !is_wc_endpoint_url();
        $is_order_pay_page = is_checkout() && is_wc_endpoint_url('order-pay');

        $form_selector = $is_checkout_page ? 'form.checkout' : 'form#order_review';
        if ($is_order_pay_page) {
            wp_register_script('geidea_sdk', $this->config['jsSdkUrl']);
            wp_enqueue_script('geidea_sdk');

            $order_id = absint(get_query_var('order-pay'));
?>
            <script type="text/javascript">
                jQuery(function($) {
                    if (typeof wc_checkout_params === 'undefined')
                        return false;

                    $(document.body).on("click", "#place_order", function(evt) {
                        let choosenPaymentMethod = $('input[name^="payment_method"]:checked').val();
                        let choosenToken = $('input[name^="wc-geidea-payment-token"]:checked').val();

                        let newCardPayment = false;
                        if (typeof choosenToken === 'undefined') {
                            newCardPayment = true;
                        } else if (choosenToken === 'new') {
                            newCardPayment = true;
                        }
                        if (choosenPaymentMethod === 'geidea' && newCardPayment) {
                            $('#place_order').attr('disabled', 'disabled');
                            evt.preventDefault();
                            $.ajax({
                                type: 'POST',
                                url: wc_checkout_params.ajax_url,
                                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                                enctype: 'multipart/form-data',
                                data: {
                                    'action': 'ajax_order',
                                    'fields': $('<?php echo htmlspecialchars(json_encode($form_selector), ENT_QUOTES, 'UTF-8');  ?>').serializeArray(),
                                    'user_id': <?php echo esc_js(get_current_user_id()); ?>,
                                    'order_id': <?php echo esc_js($order_id); ?>,
                                },
                                dataType: 'json',
                                success: function(result) {
                                    if (result.result === 'failure') {
                                        alert(result.message);
                                        $('#place_order').removeAttr('disabled');
                                    } else {
                                        initGIPaymentOnOrderPayPage(result);
                                    }
                                },
                                error: function(error) {
                                    $('#place_order').removeAttr('disabled');
                                    console.log(error);
                                }
                            });
                        }
                    });
                });

                function initGIPaymentOnOrderPayPage(data) {
                    try {
                        let onSuccess = function(_message, _statusCode) {
                            setTimeout(document.location.href = data.successUrl, 1000);
                        }

                        let onError = function(error) {
                            jQuery('#place_order').removeAttr('disabled');
                            alert(<?php echo esc_html(geideaPaymentGatewayError); ?> + error.responseMessage);
                        }

                        let onCancel = function() {
                            jQuery('#place_order').removeAttr('disabled');
                        }

                        let api = new GeideaApi(data.merchantGatewayKey, onSuccess, onError, onCancel);

                        api.configurePayment({
                            callbackUrl: data.callbackUrl,
                            amount: parseFloat(data.amount),
                            currency: data.currencyId,
                            merchantReferenceId: data.orderId,
                            cardOnFile: Boolean(data.cardOnFile),
                            initiatedBy: "Internet",
                            customerEmail: data.customerEmail,
                            address: {
                                showAddress: false,
                                billing: data.billingAddress,
                                shipping: data.shippingAddress
                            },
                            merchantLogoUrl: data.merchantLogoUrl,
                            language: data.language,
                            styles: {
                                "headerColor": data.headerColor
                            },
                            integrationType: data.integrationType,
                            name: data.name,
                            version: data.version,
                            pluginVersion: data.pluginVersion,
                            partnerId: data.partnerId,
                            isTransactionReceiptEnabled: data.receiptEnabled === 'yes'
                        });

                        api.startPayment();
                    } catch (err) {
                        alert(err);
                    }

                }
            </script>
<?php
        }
    }
}
