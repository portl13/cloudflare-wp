<?php

function cfstream_create_stream($user_id, $stream_name = null, $recording = true){
    
    $cloudflare_stream_wp_options = get_option('cloudflare_stream_wp_options');

    $channel_name = sanitize_title($stream_name);
    
    $cloudflare_stream_url = 'https://api.cloudflare.com/client/v4/accounts/'.$cloudflare_stream_wp_options['cloudflare_stream_account_id'].'/stream/live_inputs';

    $shop_name = $channel_name;

    if (!$shop_name) {
    
        return [
    
            'status' => false,
    
            'code' => 'has_no_channel_created'
    
        ];
    
    }

    $body = [    
        "meta" => [
            "name" => sanitize_title($shop_name)
        ],
    ];

    if( $recording ){

        $body["recording"] = [
            "mode" => "automatic"
        ];

    } else {
        $body["recording"] = [
            "mode" => "off"
        ];
    }

    $body['defaultCreator'] = (string) get_current_user_id();

    $api_response = wp_remote_post($cloudflare_stream_url, [

        "body" => wp_json_encode($body),
        
        "headers" => [
        
            "Content-Type" => "application/json",
            "X-Auth-Key"   => $cloudflare_stream_wp_options['cloudflare_stream_API_TOKEN'],
            "X-Auth-Email" => $cloudflare_stream_wp_options['cloudflare_stream_email']
        
        ],

    ]);

    $api_body = wp_remote_retrieve_body($api_response);

    $api_body = json_decode($api_body);

    if (empty($api_body)) {

        return [
        
            "status" => false,
        
            "code" => "error_creating_data_api"
        
        ];

    }

    if( $api_body->success ){


        $update_success = update_user_meta(
            
            $user_id,
            
            "cfs_stream_config",
            
            $api_body->result
        );


        $assign_channel_name = update_user_meta(
            $user_id,
            "_channel_name",
            $channel_name
        );

        $assign_channel_id = update_user_meta(
            $user_id,
            "_channel_id",
            $api_body->result->uid
        );


        return $api_body->result;

    }

    return false;
}

function cfstream_verify_stream($stream_id){

    $cloudflare_stream_wp_options = get_option('cloudflare_stream_wp_options');

    $stream_account_id = $cloudflare_stream_wp_options['cloudflare_stream_account_id'];

    $cloudflare_stream_url = 'https://api.cloudflare.com/client/v4/accounts/'.$stream_account_id.'/stream/live_inputs/'.$stream_id;

    $api_response = wp_remote_get($cloudflare_stream_url, [
        
        "headers" => [
        
            "Content-Type" => "application/json",
            "X-Auth-Key"   => $cloudflare_stream_wp_options['cloudflare_stream_API_TOKEN'],
            "X-Auth-Email" => $cloudflare_stream_wp_options['cloudflare_stream_email']
        
        ],

    ]);

    $api_body = wp_remote_retrieve_body($api_response);
    return $api_body;
}

function cfstream_get_or_create_stream($stream_name = null, $recording = true) {

    $user_id = get_current_user_id();

    $user_meta = get_user_meta($user_id, "cfs_stream_config", true);

    $stream_id = $user_meta->uid;

    if (empty($user_meta)) {

        $stream_created = cfstream_create_stream($user_id, $stream_name, $recording);
        
        if ( !$stream_created ){
            return false;
        }

        return $stream_created;

    } else {
        cfstream_update_stream($stream_id, $stream_name, $recording);
    }
}

function cfstream_update_stream($stream_id, $stream_name, $recording){
    
    $user_id = get_current_user_id();

    $cloudflare_stream_wp_options = get_option('cloudflare_stream_wp_options');

    $stream_account_id = $cloudflare_stream_wp_options['cloudflare_stream_account_id'];

    $cloudflare_stream_url = 'https://api.cloudflare.com/client/v4/accounts/'.$stream_account_id.'/stream/live_inputs/'.$stream_id;

    $body = [    
        "meta" => [
            "name" => sanitize_title($stream_name)
        ],
    ];

    if( $recording ){

        $body["recording"] = [
            "mode" => "automatic"
        ];

    } else {
        $body["recording"] = [
            "mode" => "off"
        ];
    }

    $api_response = wp_remote_post($cloudflare_stream_url, [
        
        "body" => wp_json_encode($body),

        "headers" => [
        
            "Content-Type" => "application/json",
            "X-Auth-Key"   => $cloudflare_stream_wp_options['cloudflare_stream_API_TOKEN'],
            "X-Auth-Email" => $cloudflare_stream_wp_options['cloudflare_stream_email']
        
        ],

    ]);

    $api_body = wp_remote_retrieve_body($api_response);

    $api_body = json_decode($api_body);

    if (empty($api_body)) {

        return [
        
            "status" => false,
        
            "code" => "error_creating_data_api"
        
        ];

    }

    if( $api_body->success ){

        $update_success = update_user_meta(
            
            $user_id,
            
            "cfs_stream_config",
            
            $api_body->result
        );

        $channel_name = sanitize_title($stream_name);

        $assign_channel_name = update_user_meta(
            $user_id,
            "_channel_name",
            $channel_name
        );

        $assign_channel_id = update_user_meta(
            $user_id,
            "_channel_id",
            $api_body->result->uid
        );


        return $api_body->result;

    }

    return false;
}
