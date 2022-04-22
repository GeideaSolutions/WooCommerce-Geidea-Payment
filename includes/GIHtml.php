<?php
namespace Geidea\Includes;

class GIHtml  {

    public function __construct() { 
    }
    

    public function create_form($result_fields){
        $arg_string = "'". $result_fields['merchantGatewayKey'] ."', '".$result_fields['orderId'] . "', '".$result_fields['amount'] . "', '". $result_fields['currencyId'] . "', '". $result_fields["callbackUrl"] . "', '". $result_fields["successUrl"]."' , '". 
                $result_fields["saveCard"] ."', '".$result_fields['customerEmail'] ."', '".$result_fields['billingAddress'] ."', '".$result_fields['merchantLogoUrl'] ."', '".$result_fields['headerColor'] ."', '".$result_fields['billingAddress'] ."', '".$result_fields['shippingAddress'] ."', '".$result_fields['integrationType'] ."', '".$result_fields['name'] ."', '".$result_fields['version'] ."', '".$result_fields['pluginVersion'] ."', '".$result_fields['partnerId']   ."'";

        $inline_script = 'function giPaymentWrapper(){
            startGIPayment('.$arg_string.');
        }';
        wp_add_inline_script( 'geidea', $inline_script );
        
        echo '<div id="gi_payment_errors">
                  <span>Error: </span><span id="gi_payment_error_message"></span>
              </div>';
    } 
}
?>