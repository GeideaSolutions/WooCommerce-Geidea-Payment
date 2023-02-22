<?php
namespace Geidea\Includes;

class GIFunctions
{

    public function __construct()
    {
    }

    public function send_gi_request($gateway_url, $merchantKey, $password, $values, $method = 'POST')
    {
        $orig_string = $merchantKey . ":" . $password;
        $auth_key = base64_encode($orig_string);
        $post_params = $values;

        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Basic ' . $auth_key,
                'Content-Type' => 'application/json',
            ),
            'body' => $post_params,
        );
        $result = wp_remote_post($gateway_url, $args);
        return $result;
    }
}