<?php
namespace Geidea\Includes;

class GIHtml  {

  public function __construct() { 
  }
  

  /**
   * Create for for payment.
   * 
   * @return string 
   */
  public function create_form($result_fields){
      $html = '
              <div id="gi_payment_errors">
                <span>Error: </span><span id="gi_payment_error_message"></span>
              </div>
            ';

      $arg_string = "'". $result_fields['merchantGatewayKey'] ."', '".$result_fields['orderId'] . "', '".$result_fields['amount'] . "', '". $result_fields['currencyId'] . "', '". $result_fields["callbackUrl"] . "', '". $result_fields["successUrl"]."' , '". 
              $result_fields["saveCard"] ."', '".$result_fields['customerEmail'] ."', '".$result_fields['billingAddress'] ."', '".$result_fields['merchantLogoUrl'] ."', '".$result_fields['headerColor'] ."', '".$result_fields['billingAddress'] ."', '".$result_fields['shippingAddress']   ."'";

      $inline_script = 'function giPaymentWrapper(){
          startGIPayment('.$arg_string.');
      }';
      wp_add_inline_script( 'geidea', $inline_script );
      
      return $html;
  }

    /**
   * Output messages + errors.
   * 
   * @return string
   */
  public function get_error_message($errors) {
      $html = '';
      foreach ($errors as $error) {
        $html .= '<div id="message" class="error inline"><p><strong>' . esc_html($error) . '</strong></p></div>';
      }
      return $html;
  }
  
}
