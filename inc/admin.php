<?php

/**
 * Hook it into Wordpress
 */
add_action('admin_menu', 'cloudflare_stream_wp_admin_pages'); 

/**
 * Place all the add_menu_page functions in here
 */
function cloudflare_stream_wp_admin_pages(){

  add_menu_page( 'CF Stream WP', 'CF Stream WP', 'manage_options', 'cloudflare-stream-start', 'cloudflare_stream_wp_start_page' );
  add_submenu_page( 'cloudflare-stream-start', 'CF Stream Options', 'CF Stream Options', 'read', 'cloudflare-stream-options', 'cloudflare_stream_wp_options_page' );
}


function cloudflare_stream_wp_start_page(){
  ob_start(); include dirname(__DIR__) . '/partial/start.php'; $template = ob_get_clean();

  echo $template;
}
/**
 * Admin page function
 */
function cloudflare_stream_wp_options_page(){

  $message = NULL;

  $options = array();

  if ( !current_user_can( 'manage_options' ) )  {
  
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );  
  }

  if( isset( $_POST['publish'] ) ){
    flush_rewrite_rules();
    // TODO: Need to figure out how to play a live stream if this is set to off.
    // for now, leaving it as a hard recording = on.
    $recording = true; //isset($_POST['cloudflare_stream_recording']);


    if( isset($_POST['cloudflare_stream_name']) ){
      $stream_created = cfstream_get_or_create_stream($_POST['cloudflare_stream_name'],$recording);
      update_option('cloudflare_needs_reset', TRUE);
    }
    update_option( 'cloudflare_stream_wp_options', $_POST );
    update_user_meta(get_current_user_id(),'_channel_banner', $_POST['rudr_img']);
  }
  
  $options = get_option( 'cloudflare_stream_wp_options' );

  $user_meta = get_user_meta(get_current_user_id(), 'cfs_stream_config', true);
  $channel_banner = get_user_meta(get_current_user_id(), '_channel_banner', true);

  ob_start(); include dirname(__DIR__) . '/partial/admin.php'; $template = ob_get_clean();

  echo $template;
}

add_action( 'admin_enqueue_scripts', 'rudr_include_js' );
function rudr_include_js() {
  
  // I recommend to add additional conditions here
  // because we probably do not need the scripts on every admin page, right?
  if( !isset($_GET['page']) )
    return;

  if( $_GET['page'] != 'cloudflare-stream-options' )
    return;

  // WordPress media uploader scripts
  if ( ! did_action( 'wp_enqueue_media' ) ) {
    wp_enqueue_media();
  }
  // our custom JS
  wp_enqueue_script( 
    'uploader', 
    plugin_dir_url(__DIR__) . '/assets/uploader.js',
    array( 'jquery' )
  );
}