<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Geidea Payment Gateway class
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class       WC_Geidea
 * @extends     WC_Payment_Gateway
 * @version     1.0.10
 * @author      Geidea
 */ 

class WC_Gateway_Geidea extends WC_Payment_Gateway {
  
    public function __construct() {
        $this->id = 'geidea';

        include_once 'lang/settings.en.php';
        
        require_once 'includes/GIFunctions.php';
        require_once 'includes/GITable.php';

        require_once 'includes/GIHtml.php';
        $this->html = new \Geidea\Includes\GIHtml();
        $this->functions = new \Geidea\Includes\GIFunctions();

        $this->method_title = geideaTitle;
        $this->has_fields = true;

        $this->errors = [];

        //Initialization the form fields
        $this->init_form_fields();

        //Initialization the settings
        $this->init_settings();

        $this->supports = array(
            'products',
            'tokenization'
        );

        //Hel: Get setting values
        $this->title = $this->get_option('title');
        $this->enabled = $this->get_option('enabled');
        $this->description = $this->get_option('description');
        
        $this->logo = $this->get_option('logo');
        $this->icon = apply_filters('woocommerce_' . $this->id . '_icon', (!empty($this->logo) 
            ? $this->logo
            : plugins_url( 'assets/imgs/geidea-logo.svg' , __FILE__ )));

        $this->tokenise_param = "wc-{$this->id}-new-payment-method";
        $this->token_id_param = "wc-{$this->id}-payment-token";

        $this->config = require 'config.php';

        //The connections hooks
        if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
        }
        else {
            add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
        }
        add_action('woocommerce_receipt_' . $this->id, array(&$this, 'receipt_page'));
        add_action('woocommerce_api_wc_' . $this->id, array($this, 'return_handler'));
        
        if (!empty($_GET['wc-api']) && $_GET['wc-api'] == 'geidea') {
            do_action('woocommerce_api_wc_' . $this->id);
        }
    }

    public function process_admin_options() {
		function generate_logo_filename($dir, $name, $ext){
			return "logo_".bin2hex(random_bytes(16)).$ext;
		}

        $this->init_settings();

        $post_data = $this->get_post_data();

        foreach ( $this->get_form_fields() as $key => $field ) {
            if ( 'title' !== $this->get_field_type( $field ) && $key !== 'logo' ) {
                try {
                    $this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
                } catch ( Exception $e ) {
                    $this->add_error( $e->getMessage() );
                }
            }
        }

        if (isset($_FILES['woocommerce_geidea_logo']) && ($_FILES['woocommerce_geidea_logo']['size'] > 0)) {
            $arr_file_type = wp_check_filetype(basename($_FILES['woocommerce_geidea_logo']['name']));
            $uploaded_file_type = $arr_file_type['type'];
        
            $allowed_file_types = array('image/jpg','image/jpeg','image/png','image/svg+xml');
        
            if (in_array($uploaded_file_type, $allowed_file_types)) {

                if (!function_exists('wp_handle_upload')) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }
                
                $uploaded_file = wp_handle_upload($_FILES['woocommerce_geidea_logo'], array( 'test_form' => false, 'unique_filename_callback' => 'generate_logo_filename' ));
        
                if (isset($uploaded_file['file'])) {
                    $this->settings['logo'] = $uploaded_file['url'];        
                } else {
                    $this->errors[] = "Something went wrong while uploading file!";
                }
        
            } else {
                $this->errors[] = "Wrong file type!";
            }
        }

        $san_merchant_gateway_key = sanitize_text_field($this->settings['merchant_gateway_key']);
        $san_merchant_password = sanitize_text_field($this->settings['merchant_password']);
        if(empty($san_merchant_gateway_key)){
            $this->errors[] = sprintf(geideaErrorRequired, "Merchant gateway key");
        }
        if(empty($san_merchant_password)){
            $this->errors[] = sprintf(geideaErrorRequired, "Merchant password");
        } 
        if(!empty($this->errors)){
            $this->enabled = false;
            $this->settings['enabled'] = 'no';

            foreach ($this->errors as $v) {
                WC_Admin_Settings::add_error($v);
            }
        } 
        $this->settings['return_url'] = 'yes';

        return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
    }

    /**
     * Output form of setting payment system.
     */
    public function init_form_fields() {   
        //Get order status
        $statuses = wc_get_order_statuses();
        
        $options = get_option('woocommerce_' . $this->id . '_settings');

        $logo = $options['logo'];
        $merchantLogo = sanitize_text_field($logo);

        if (empty($merchantLogo)) {
            $merchantLogo = plugins_url( 'assets/imgs/geidea-logo.svg' , __FILE__ );
        }
            
        $this->form_fields = array(
            'enabled' => array(
                'title' => geideaSettingsActive,
                'type' => 'checkbox',
                'label' => ' ',
                'default' => 'no'
            ),
            'title' => array(
                'title' => geideaSettingsName,
                'type' => 'text',
                'default' => geideaTitle
            ),
            'description' => array(
                'title' => geideaSettingsDesc,
                'type' => 'textarea',
                'description' => '',
                'default' => geideaTitleDesc
            ),
            'merchant_gateway_key' => array(
                'title' => geideaSettingsMerchant.' *',
                'type' => 'text',
                'description' => geideaSettingsMerchantDesc,
                'default' => ''
            ),
            'merchant_password' => array(
                'title' => geideaSettingsPassword.' *',
                'type' => 'text',
                'description' => '',
                'default' => ''
            ),
            'currency_id' => array(
                'title' => geideaSettingsCurrency.' *',
                'type' => 'select',
                'options' => [
                    'USD' => "US Dollar",
                    'SAR' => "Saudi Riyal",
                    'EGP' => "Egyptian Pound"
                ],
                'default' => 'SAR'
            ),
            'logo' => array(
                'title' => geideaSettingsLogo,
                'type' => 'file',
                'description' => '<img src="' . esc_html($merchantLogo) . '" width="70">',
                'default' => plugins_url('assets/imgs/geidea-logo.svg',__FILE__ )
            ),
            'header_color' => array(
                'title' => geideaSettingsHeaderColor,
                'type' => 'text',
                'description' => geideaSettingsHeaderColorDesc,
                'default' => ''
            ),
            'order_status_sucess' => array(
                'title' => geideaSettingsOrderStatusSuccess,
                'type' => 'select',
                'options' => $statuses,
                'default' => 'wc-processing'
            ),
            'order_status_waiting' => array(
                'title' => geideaSettingsOrderStatusWaiting,
                'type' => 'select',
                'options' => $statuses,
                'default' => 'wc-pending' 
            )
        );
    }
    

    function payment_fields()
    {
        if ($this->description) echo wpautop(wptexturize($this->description));

        $this->tokenization_script();
        $this->saved_payment_methods();
        $this->save_payment_method_checkbox();
    }


  /**
   * 
   * @return array
   */
    private function get_options(){
        $options = get_option('woocommerce_' . $this->id . '_settings');
        $settings = [];
        if(isset($options['merchant_gateway_key'])){
        $settings['merchantGatewayKey'] = $options['merchant_gateway_key'];
        }
        if(isset($options['currency_id'])){
        $settings['currencyId'] = $options['currency_id'];
        }
        if(isset($options['currency_default'])){
        $settings['currencyDefault'] = $options['currency_default'];
        }
        if(isset($options['order_status_sucess'])){
        $settings['orderStatusSuccess'] = $options['order_status_sucess'];
        }
        if(isset($options['order_status_waiting'])){
        $settings['orderStatusWaiting'] = $options['order_status_waiting'];
        }

        $settings['returnUrl'] = get_site_url().'/?wc-api=geidea';

        return $settings;
    }

    private function delete_token($token_id){
        $result = WC_Payment_Tokens::delete((int)$token_id);
    }

    /**
     * Admin Panel Options 
     * The output html form - settings to the admin panel
     * */
    public function admin_options() {
        $second_action = ( isset($_POST['action2']) ) ? sanitize_key( $_POST['action2'] ) : false;
        if($second_action && $second_action == 'delete'){
            foreach($_POST as $k => $param){
                $san_param = sanitize_key($k);
                if(substr( $san_param, 0, 12 ) == "delete_token"){   
                    $token_id = substr($san_param, -1);
                    $this->delete_token($token_id);
                }
            }
        }

        if(isset($_GET['action'])){
            $san_token = ( isset($_GET['token']) )  ? sanitize_key( $_GET['token'] ) : false;
            if($san_token && is_numeric($san_token)){
                $token_id = (int)$san_token;
                $this->delete_token($token_id);
            }
        }

        ?>
        <h3><?php echo geideaTitle?></h3>
        <table class="form-table">
        <?php
        //Generate the HTML For the settings form.
        $form_fields = $this->get_form_fields();

        $this->generate_settings_html($form_fields, true);
        ?>
        </table>
        <?php
        render_tokens_table();
    }

    function get_formatted_billing_address($order){
        $billing_street = $order->get_billing_address_1();
        $billing_street .= " " .$order->get_billing_address_2();
        $formatted_address = [
            'country' => sanitize_text_field($order->get_billing_country()),
            'street' => sanitize_text_field($billing_street),
            'city' => sanitize_text_field($order->get_billing_city()),
            'postcode' => sanitize_text_field($order->get_billing_postcode())
        ];

        return $formatted_address;
    }

    function get_formatted_shipping_address($order){
        $shipping_street = $order->get_shipping_address_1();
        $shipping_street .= " " .$order->get_shipping_address_2();
        $formatted_address = [
            'country' => sanitize_text_field($order->get_shipping_country()),
            'street' => sanitize_text_field($shipping_street),
            'city' => sanitize_text_field($order->get_shipping_city()),
            'postcode' => sanitize_text_field($order->get_shipping_postcode())
        ];

        return $formatted_address;
    }

  
    function receipt_page($order_id) {
        wp_register_style('geidea', plugins_url('assets/css/gi-styles.css',__FILE__ ));
        wp_enqueue_style('geidea');

        wp_register_script( 'geidea', plugins_url('assets/js/script.js',__FILE__ ));
        wp_enqueue_script('geidea');
        wp_register_script( 'geidea_sdk', $this->config['jsSdkUrl']);
        wp_enqueue_script('geidea_sdk');

        echo '<p>' . esc_html($this->get_option('result_order_text')) . '</p>';

        $save_card = false;
        $san_save_card_param = ( isset($_GET['save_card']) )  ? sanitize_key( $_GET['save_card'] ) : false;
        if($san_save_card_param && $san_save_card_param == "true"){
            $save_card = true;
        }

        //get information of order
        $order = wc_get_order($order_id);

        $order_currency = $order->currency;
        $available_currencies = $this->config['availableCurrencies'];

        $result_currency = in_array($order_currency, $available_currencies) ? $order_currency : $this->get_option('currency_id');

        $result_fields = [];
        $result_fields['orderId'] = $order->id;
        $result_fields['amount'] = number_format($order->order_total, 2, '.', '');
        $result_fields['merchantGatewayKey'] =  $this->get_option('merchant_gateway_key');
        $result_fields['currencyId'] = $result_currency;
        $result_fields['successUrl'] = $this->get_return_url($order);
        $result_fields['failUrl'] = $this->get_return_url($order);
        $result_fields['callbackUrl'] = get_site_url().'/?wc-api=geidea';
        $result_fields['headerColor'] = $this->get_option('header_color');
        $result_fields['saveCard'] = $save_card;
        $result_fields['customerEmail'] = sanitize_text_field($order->get_billing_email());
        $result_fields['billingAddress'] = json_encode($this->get_formatted_billing_address($order));
        $result_fields['shippingAddress'] = json_encode($this->get_formatted_shipping_address($order));
        $result_fields['merchantLogoUrl'] = $this->get_option('logo');

        $this->html->create_form($result_fields);
        
        $text = sprintf(geideaOrderResultCreated, $order->id, $this->get_option('order_status_waiting'));
        $order->add_order_note($text);
        $status = str_replace('wc-', '', $this->get_option('order_status_waiting'));
        //change status order
        $order->update_status($status, $text);
    }


    private function get_token(){
        $token_id = sanitize_key($_POST[$this->token_id_param]);

        if (!$token_id) {
            return null;
        }

        if ($token_id === 'new') {
            return false;
        }

        $token = WC_Payment_Tokens::get($token_id);

        if ($token->get_user_id() !== get_current_user_id()) {
            return null;
        }

        return $token;
    }


    function need_to_save_new_card($user_id){
        $token_id = sanitize_key($_POST[$this->token_id_param]);
        $save_token = sanitize_key($_POST[$this->tokenise_param]);

        $all_tokens = WC_Payment_Tokens::get_customer_tokens($user_id, $this->id);

        // if token is new or there are no tokens for this customer
        if (($token_id === 'new' || !$all_tokens) && $save_token) {
            return true;
        } else {
            return false;
        }
    }


    function tokenise_payment($order, $token){
        $values = [];
        
        $order_currency = $order->currency;
        $available_currencies = $this->config['availableCurrencies'];
    
        $result_currency = in_array($order_currency, $available_currencies) ? $order_currency : $this->get_option('currency_id');
        $values["currency"] = $result_currency;

        $values["amount"] = number_format($order->order_total, 2, '.', '');

        $values["tokenId"] = $token->get_token();
        
        $values["initiatedBy"] = "Internet";
        $values["merchantReferenceId"] = (string)$order->id;
        $values["callbackUrl"] = get_site_url().'/?wc-api=geidea';

        $merchantKey = $this->get_option('merchant_gateway_key');
        $password = $this->get_option('merchant_password');

        $result = $this->functions->send_gi_request($this->config['payByTokenUrl'], $merchantKey, $password, $values);

        if(!empty($result["errors"])){
            foreach($result["errors"] as $k => $v ){
                foreach($v as $error){
                    wc_add_notice($error, 'error');       
                }
            }
            return false;
        }

        if(isset($result["detailedResponseMessage"])){
            if($result["responseMessage"] != 'Success'){
                wc_add_notice($result["detailedResponseMessage"], 'error');
                return false;
            }
        }

        return true;
    }

    /**
     * Process the payment and return the result
     * */
    function process_payment($order_id) {
        $order = new WC_Order($order_id);

        $user_id = get_current_user_id();

        $save_card = false;

        if($user_id != 0){
            $token = $this->get_token();
            if($token){
                $success = $this->tokenise_payment($order, $token);

                if($success){
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order)
                    );
                } else {
                    return array('result' => 'fail');
                }
            }

            $save_card = $this->need_to_save_new_card($user_id);
        }
        
        $redirect_url = $order->get_checkout_payment_url( true );
        if($save_card){
            $redirect_url .= "&save_card=true";
        }

        return array(
            'result' => 'success',
            'redirect' => $redirect_url
        );
    }

    /**
     * Return handler for Hosted Payments
     */
    function return_handler() {
        try {
            $json_body = file_get_contents("php://input");
            $result = json_decode($json_body, true);

            if($result == NULL){
                echo "Invalid request!";
                http_response_code(400);
                die();
            }
            
            $order = $result["order"];
            $callback_signature = $result["signature"];
    
            if($order["merchantReferenceId"] == NULL){
                echo "Order id is not defined!";
                http_response_code(400);
                die();
            }
    
            $merchant_key = $this->get_option('merchant_gateway_key');
            $currency = $order['currency'];
            $order_id = $order['orderId'];
            $order_status = $order['status'];
            $merchant_reference_id = $order['merchantReferenceId'];
            $merchant_password = $this->get_option('merchant_password');
    
            $amount = (string)number_format($order['amount'], 2, '.', '');
    
            $result_string = $merchant_key.$amount.$currency.$order_id.$order_status.$merchant_reference_id;
            
            $hash = hash_hmac('sha256', $result_string, $merchant_password, true);
            $result_signature = base64_encode($hash);
    
            if($result_signature != $callback_signature){
                echo "Invalid signature!";
                http_response_code(400);
                die();
            }

            try {
                $wc_order = new \WC_Order($order["merchantReferenceId"]);
            } catch (Exception $e) {
                echo esc_html("Order with id " . $order["merchantReferenceId"] . " not found!");
                http_response_code(404);
                die();
            }

            //get the order amount
            global $wpdb;
            $orders_fields = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id = " . $wc_order->id . " ;");
            foreach ($orders_fields as $value) {
                if ($value->meta_key != '_order_total') {
                    continue;
                }
                $order_total = $value->meta_value;
            }

            //checking on the order amount
            if (number_format($order_total, 2, '.', '') != $order["amount"] &&
                (empty($wc_order->post_status) || $wc_order->post_status != 'wc-failed')) {
                echo "Invalid order amount!";
                http_response_code(400);
                die();
            }

            $options = $this->get_options();
            if(mb_strtolower($order["status"]) == "success" &&
                mb_strtolower($order["detailedStatus"]) == "paid"){
                    //save token block
                    $user_id = $wc_order->get_user_id();
                    if($order["cardOnFile"] == true && $user_id != 0){
                        $token_id = $order["tokenId"];
                        $card_number = substr($order["paymentMethod"]["maskedCardNumber"], -4);;
                        $expiry_date = $order["paymentMethod"]["expiryDate"];
                        $card_type = $order["paymentMethod"]["brand"];

                        $this->save_token($token_id, $card_number, $expiry_date, $card_type, $user_id);
                    }

                    $this->payment_complete($wc_order);
                    // Remove cart
                    WC()->cart->empty_cart();

                    echo "Order is completed!";
                    http_response_code(200);
                    die();
            } elseif (mb_strtolower($order["status"]) == "failed" && 
                        $wc_order->post_status != $options["orderStatusSuccess"]){
                $last_transaction = end($order['transactions']);
                $codes = $last_transaction['codes'];
                
                $text = sprintf(
                    "%s: %s; %s: %s",
                    $codes["responseCode"],
                    $codes["responseMessage"],
                    $codes["detailedResponseCode"],
                    $codes["detailedResponseMessage"]
                );
                $wc_order->add_order_note($text);

                $wc_order->update_status(apply_filters('woocommerce_payment_complete_order_status', 'failed', $wc_order->id));

                echo "Payment failed!";
                http_response_code(400);
                die();
            } else {
                echo "Not found!";
                http_response_code(404);
                die();
            }
        } catch(Exception $return_handler_exc) {
            echo "Internal Server Error!";
            echo esc_html($return_handler_exc);
            http_response_code(500);
            die();
        }
    }

    function save_token($token_id, $card_number, $expiry_date, $card_type, $user_id){
        $token = WC_Payment_Tokens::get($token_id);

        $token_exists = false;
        $all_tokens = WC_Payment_Tokens::get_customer_tokens($user_id, $this->id);

        foreach($all_tokens as $t){
            if($t->get_token() == $token_id){
                $token_exists = true;
            }
        }

        if(!$token_exists){
            $new_token = new WC_Payment_Token_CC();
            $new_token->set_token( $token_id ); // Token comes from payment processor
            $new_token->set_gateway_id( $this->id );
            $new_token->set_last4( $card_number );
            $new_token->set_expiry_year( "20" . $expiry_date['year'] );
            $new_token->set_expiry_month( (string)$expiry_date['month'] );
            $new_token->set_card_type( $card_type );
            $new_token->set_user_id( $user_id );
            // Save the new token to the database
            $new_token->save();
        } else {
            echo "The token already exists!";
        }
    }


    function payment_complete($order) {
		do_action('woocommerce_pre_payment_complete', $order->id);
        
        if (null !== WC()->session) {
            WC()->session->set('order_awaiting_payment', false);
        }
        if ($order->id) {
            $order_needs_processing = false;
        
            if (sizeof($order->get_items()) > 0) {
                foreach ($order->get_items() as $item) {
                    if ($_product = $order->get_product_from_item($item)) {
                        $virtual_downloadable_item = $_product->is_downloadable() && $_product->is_virtual();

                        if (apply_filters('woocommerce_order_item_needs_processing', !$virtual_downloadable_item, $_product, $order->id)) {
                            $order_needs_processing = true;
                            break;
                        }
                    }
                    else {
                        $order_needs_processing = true;
                        break;
                    }
                }
            }
        
            $options = $this->get_options();
            $order->update_status(apply_filters('woocommerce_payment_complete_order_status', str_replace('wc-', '', $options["orderStatusSuccess"]), $order->id));

            add_post_meta($order->id, '_paid_date', current_time('mysql'), true);

            // Payment is complete so reduce stock levels
            if (apply_filters('woocommerce_payment_complete_reduce_order_stock', !get_post_meta($order->id, '_order_stock_reduced', true), $order->id)) {
                $order->reduce_order_stock();
            }
            do_action('woocommerce_payment_complete', $order->id);
        } else {
            do_action('woocommerce_payment_complete_order_status_' . $order->get_status(), $order->id);
        }
    }

}
?>