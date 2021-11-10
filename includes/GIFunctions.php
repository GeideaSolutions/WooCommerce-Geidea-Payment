<?php
namespace Geidea\Includes;
class GIFunctions  {

    public function __construct() { 
    }

    public function send_gi_request($gateway_url, $merchantKey, $password, $values){
        $orig_string = $merchantKey.":".$password;
        $auth_key = base64_encode($orig_string);
        $post_params = json_encode($values);
    
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth_key,
                'Content-Type' => 'application/json'
            ),
            'body'        => $post_params
        );
        $result = wp_remote_post( $gateway_url, $args );
        return json_decode($result["body"], true);
    }
}
?>