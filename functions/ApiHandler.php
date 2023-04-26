<?php

namespace Geidea\Functions;

trait ApiHandler
{
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
                'accept' => 'application/json',
            ),
            'body' => $post_params,
        );
        return wp_remote_post($gateway_url, $args);
    }
}
