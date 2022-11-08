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
    <div class="video-box" style="">
      <video id="remote-video" controls autoplay muted></video>
    </div>
  </div>
</div>

    

    <script type="module">

      import WHEPClient from '<?php echo plugin_dir_url(__DIR__);?>assets/WHEPclient.js';
      const videoElement = document.getElementById('remote-video');
      var timer = {};
      let online = 0;

      function go_online(){
      }

      function go_offline(){
      }

      function checkStreamHealth(timer){

        jQuery.ajax({
          url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
          method: "POST",
          dataType: 'json',
          data: {
            action : 'cloudflare_check_stream_health',
            channel_id : '<?php echo $channel_id;?>',
            channel_name : '<?php echo $channel_name; ?>'
          },
          success: function(d){

            if( d.result.status.current.state == 'connected' && online == 0 ){
              
              const url = 'https://customer-85isopi7l4huoj8o.cloudflarestream.com/355966db3d747f4f0f8d3e3941e4b98c/webRTC/play';
              self.whepClient = new WHEPClient(url, videoElement);
              videoElement.play();

              jQuery('#cover-image').hide();
              jQuery('#video-container').show();
              clearInterval(timer);
              return;

            }

          }

        });
      }

      videoElement.addEventListener('playing', (event) => {
        console.log('Video is no longer paused');
        go_online();
      });

      videoElement.addEventListener('ended', (event) => {
        console.log('Video is no longer playing');
        go_offline();
      });

      checkStreamHealth();

      timer = setInterval(function(){
        checkStreamHealth(timer);
      },10000, online);

    </script>