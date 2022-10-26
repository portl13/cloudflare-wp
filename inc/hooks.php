<?php

add_action('init', 'custom_rewrite_tag', 10, 0);

function custom_rewrite_tag() {

  add_rewrite_tag('%channel_id%', '([^/]+)/?$');
  add_rewrite_tag('%channel_name%','([^/]+)/?$');
}

add_action( 'init',  function() {

    $user_id = get_current_user_id();

    $user_meta = get_user_meta($user_id, '_stream_cofig', true);

    $options = get_option('cloudflare_stream_wp_options');
    
    $channel_name = sanitize_title($options['cloudflare_stream_name']);

    add_rewrite_rule( '^channel/'.$channel_name, 'index.php?pagename=player&channel_name='.$channel_name.'&channel_id='.$user_meta->uid, 'top' );
});

add_filter('query_vars', function($vars) {

    $vars[] = "channel_id";

    return $vars;
});

add_action( 'init', function() {

  $labels = array(
    'name'               => __( 'Stream Events', 'cfstream-wp' ),
    'singular_name'      => __( 'Stream Event', 'cfstream-wp' ),
    'add_new'            => _x( 'Add New Stream Event', 'cfstream-wp', 'cfstream-wp' ),
    'add_new_item'       => __( 'Add New Stream Event', 'cfstream-wp' ),
    'edit_item'          => __( 'Edit Stream Event', 'cfstream-wp' ),
    'new_item'           => __( 'New Stream Event', 'cfstream-wp' ),
    'view_item'          => __( 'View Stream Event', 'cfstream-wp' ),
    'search_items'       => __( 'Search Stream Events', 'cfstream-wp' ),
    'not_found'          => __( 'No Stream Events found', 'cfstream-wp' ),
    'not_found_in_trash' => __( 'No Stream Events found in Trash', 'cfstream-wp' ),
    'parent_item_colon'  => __( 'Parent Stream Event:', 'cfstream-wp' ),
    'menu_name'          => __( 'Stream Events', 'cfstream-wp' ),
  );

  $args = array(
    'labels'              => $labels,
    'hierarchical'        => false,
    'description'         => 'description',
    'taxonomies'          => array(),
    'public'              => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_admin_bar'   => true,
    'menu_position'       => null,
    'menu_icon'           => null,
    'show_in_nav_menus'   => true,
    'publicly_queryable'  => true,
    'exclude_from_search' => false,
    'has_archive'         => true,
    'query_var'           => true,
    'can_export'          => true,
    'rewrite'             => true,
    'capability_type'     => 'post',
    'supports'            => array(
      'title',
      'editor',
      'author',
      'thumbnail',
      'excerpt',
      'custom-fields',
      'trackbacks',
      'comments',
      'revisions',
      'page-attributes',
      'post-formats',
    ),
  );

  register_post_type( 'cfstream-events', $args );
});

add_action('wp_enqueue_scripts', function(){
  $options = get_option('cloudflare_stream_wp_options');
  $user_id = get_current_user_id();
  $user_meta = get_user_meta($user_id, '_stream_cofig', true);

  wp_register_script('cfstream-script', plugin_dir_url(__DIR__) . '/assets/cfstream-script.js', array(), null, true);
  wp_localize_script( 'cfstream-script', 'cfstream_jsobject',
    array( 
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'api_token' => $options['cloudflare_stream_API_TOKEN'],
      'stream_id' => $user_meta->id,
      'account_id' => $options['cloudflare_stream_account_id']
    )
  );
  wp_enqueue_script('cfstream-script');

});


add_action('admin_enqueue_scripts', 'cfstream_event_datepicker');
function cfstream_event_datepicker(){
  global $post;
  if( $post->post_type == 'cfstream-events' ){

    wp_enqueue_script( 'jquery-ui-datepicker-init',
      plugins_url( 'jquery-ui-datepicker-init.js', __FILE__ ),
      array( 'jquery', 'jquery-ui-datepicker' ),
      '1.00' );

    wp_enqueue_style( 'jquery-ui',
      'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css',
      array(),
      '1.00' );

    wp_enqueue_script(
      'timepicker', 
      plugin_dir_url(__DIR__) . 'assets/timepicker.js', 
      array('jquery-ui-datepicker-init'));

    wp_enqueue_style( 'timepicker-css',
      plugin_dir_url(__DIR__) . 'assets/timepicker.css',
      array(),
      '1.00' );
  }
}

// PAGE META
add_action('add_meta_boxes', 'add_event_meta');
function add_event_meta()
{
  add_meta_box(
      'cfstream_event_date', // $id
      'Event Date', // $title
      'display_date_picker', // $callback
      array('cfstream-events'), // $page
      'normal', // $context
      'high'); // $priority
}
function display_date_picker($post){
    $options = get_post_meta($post->ID, '_cfstream_datepicker', true); 
    ?>
      <p>Use the Shortcode <strong>[cfstream_event_date]</strong> anywhere in your post or page builder to display the event date on your flyer.</p>
      <label for="cfstream_datepicker">Event Date</label>
      <input id="cfstream_datepicker" name="cfstream_datepicker" placeholder="mm/dd/yyyy" type="text" style="width: 400px;" value="<?php echo esc_attr($options); ?>">
      <script>
        jQuery(document).ready(function($){
          $('#cfstream_datepicker').datetimepicker({
            timeFormat: "hh:mm tt"
          });
        });
      </script>
    <?php
}

add_action( 'save_post', 'wporg_save_postdata' );
function wporg_save_postdata( $post_id ) {
    // HERO BANNER
    if ( array_key_exists( 'cfstream_datepicker', $_POST ) ) 
        update_post_meta($post_id, '_cfstream_datepicker', $_POST['cfstream_datepicker'] );
}

add_action( 'wp_ajax_cloudflare_check_stream_health', 'cloudflare_check_stream_health_callback' );
function cloudflare_check_stream_health_callback() {
  
    $cloudflare_stream_wp_options = get_option('cloudflare_stream_wp_options');

    global $wpdb;
    $user_id = $wpdb->get_var("SELECT `user_id` FROM `{$wpdb->prefix}usermeta` WHERE `meta_key` = '_channel_name' AND `meta_value` = '{$_POST['channel_name']}'");
    $stream_config = get_user_meta($user_id, '_stream_cofig', true);

    $cloudflare_stream_url = 'https://api.cloudflare.com/client/v4/accounts/'.$_POST['channel_id'].'/stream/live_inputs/'.$stream_config->uid.'/videos';
    
    $result = cfstream_verify_stream($stream_config->uid);

    die($result);
}