<?php

add_shortcode( 'cloudflare_stream_golive', 'cfstream_viewer_shortcode' );
function cfstream_viewer_shortcode( $atts = array(), $content = '' ) {

  $atts = shortcode_atts( array(
    'id' => 'value',
  ), $atts, 'cloudflare_stream_golive' );
  $user_id = get_current_user_id();
  if( $user_id ){
    
    $stream_created = cfstream_get_or_create_stream();
    $options = get_option('cloudflare_stream_wp_options');
    $user_meta = get_user_meta($user_id, 'cfs_stream_config', true);

    $webRTC = $user_meta->webRTC->url;

    ob_start();
    include dirname(__DIR__).'/partial/golive.php';
    return ob_get_clean();

  }


  // do shortcode actions here
}

add_shortcode( 'cloudflare_stream_player', 'cfstream_player_shortcode' );
function cfstream_player_shortcode( $atts = array(), $content = '' ) {

  $atts = shortcode_atts( array(
    'channel' => '',
  ), $atts, 'cloudflare_stream_player' );

  global $post;
  // TODO
  // This is psuedo for featured image on event page
  //$image = wp_get_attachment_image_url();


  ob_start();
  include dirname(__DIR__).'/partial/player.php';
  return ob_get_clean();


  // do shortcode actions here
}

add_shortcode( 'cfstream_event_date', 'cfstream_eventdate_shortcode' );  
function cfstream_eventdate_shortcode( $atts = array(), $content = '' ) {
  $atts = shortcode_atts( array(
    'id' => 'value',
  ), $atts, 'cfstream_event_date' );  

    global $post;
    $date = get_post_meta($post->ID, '_cfstream_datepicker', true);
    return $date;

  // do shortcode actions here
}
