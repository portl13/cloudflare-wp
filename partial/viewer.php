<style>
#lpwp-video {
  height: 300px;
  width: 400px;
  border: 1px solid rgb(158, 140, 252);
  background-color: black;
  border-radius: 3px;
}

#lpwp-button {
  font-size: 18px;
  border-radius: 3px;
  border: 1px solid rgb(158, 140, 252);
  color: #202020;
  background: none;
  cursor: pointer;
  padding: 8px 16px;
  margin: 0 auto;
  margin-top: 10px;
}
.player-placeholder {
  width: 400px; height: 220px;
  background-color: #404040;
  color: #fff;
  text-align: center;
  position: relative;
}

.player-placeholder span {
  position: absolute;
  top: 50%;
  left: 50%;
  color: #fff;
  transform: translate(-50%, -50%);
  -moz-transform: translate(-50%, -50%);
  -webkit-transform: translate(-50%, -50%);
}
#golive {
  font-size: 18px;
  border-radius: 3px;
  border: 1px solid rgb(158, 140, 252);
  color: #202020;
  background: none;
  cursor: pointer;
  padding: 8px 16px;
  margin: 0 auto;
  margin-top: 10px;
}
#stream-video {
  background-color: #000;
  width: 100%;
  border-radius: 3px;
  margin-top: 10px;
}
</style>
<?php if( $options ): ?>

  <div style="text-align: center">
    <video id="stream-video" autoplay muted></video>
  </div>
  <div>
    <button style="width: 100%;" id="golive">Go Live!</button>

  </div>

  <script type="module">

      import WHIPClient from '<?php echo plugin_dir_url(__DIR__);?>assets/WHIPClient.js';

      const video = document.getElementById("stream-video");
      const golive = document.getElementById("golive");
      const disconnect = document.getElementById("disconnect");
      var client = {};

      video.volume = 0;

      var live = 0;
      golive.onclick = (e) => {
        e.preventDefault();
        if( live == 1 ){
          console.log('going to stop');
          live = 0;
          golive.innerText = 'Go Live!';
          client.disconnectStream();
          return;
        }
        console.log('going to start');

        live = 1;
        golive.innerText = 'Stop Stream';

        const url = "<?php echo $webRTC; ?>";
        const videoElement = document.getElementById("stream-video");
        client = new WHIPClient(url, videoElement);
        return;
      };
    /**/
  </script>

<?php else: ?>

  <p>Please update your Livepeer options in the admin area.</p>
<?php endif;?>