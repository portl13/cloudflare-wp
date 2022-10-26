<?php 

  if( empty($channel_id) && empty($channel_name) ){

     $channel_id = get_query_var('channel_id');
     $channel_name = get_query_var('channel_name');
  }

?>
<style>
#fluidMedia {
  position: relative;
  padding-bottom: 56.25%; /* proportion value to aspect ratio 16:9 (9 / 16 = 0.5625 or 56.25%) */
  padding-top: 30px;
  height: 0;
  overflow: hidden;
  width: 100% !important;
  max-width: 80% !important;
  margin: 0 auto;
}

#video-container .video-box iframe {
  border: none; 
  position: absolute; 
  top: 0; 
  left: 0; 
  height: 100%; 
  width: 100%;
}
</style>

<?php 

    $image = plugin_dir_url(__DIR__) . 'assets/img/livepeer-banner.jpg';

    if( $image_id = get_post_thumbnail_id() ){

      $image = wp_get_attachment_image_url( $image_id, 'full' );

    } else { 

      global $wpdb;
      $user_id = $wpdb->get_var("SELECT `user_id` FROM `{$wpdb->prefix}usermeta` WHERE `meta_key` = '_channel_name' AND `meta_value` = '{$channel_name}'");
      $channel_banner = get_user_meta($user_id, '_channel_banner', true);
      
      if( $channel_banner ){
        $image = wp_get_attachment_image_url( $channel_banner, 'full' );
      }
    }
?>

                        
<!-- HTML -->
<div id="fluidMedia">
  <div id="cover-image">
      <img src="<?php echo esc_url( $image ) ?>" alt="<?php echo $options['livepeer_stream_name']?> Offline Banner" />
  </div>
  <div id="video-container"  style="display: none;">
    <div class="video-box" style="position: relative; padding-top: 56.25%;"></div>
  </div>
</div>


<script>
  jQuery(document).ready(function($){
    var x = 0;
    function checkStreamHealth(timer){
      $.ajax({
        url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
        method: "POST",
        dataType: 'json',
        data: {
          action : 'cloudflare_check_stream_health',
          channel_id : '<?php echo $channel_id;?>',
          channel_name : '<?php echo $channel_name; ?>'
        },
        success: function(d){

          if( d.errors.length ){
            go_offline();
          } else {
            if( d.result.status.current.state == 'connected' ){
              if(!x){
                $('#video-container .video-box')
                  .html('<iframe src="'+d.result.webRTCPlayback.url.replace('webRTC/play', 'iframe')+'" \
                    allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" \
                    allowfullscreen="true"></iframe>')
              }
              x = 1;
              $('#video-container').show();
              $('#cover-image').hide();
            } else {
              x = 0;
              go_offline();
            }
          }
        }
      })
    }

    function go_online(d,x){
    }

    function go_offline(){
      $('#cover-image').show();
      $('#video-container').hide();
    }

    var timer = setInterval(function(){
      checkStreamHealth(timer);
    },10000);

    checkStreamHealth(timer);
  })
</script>