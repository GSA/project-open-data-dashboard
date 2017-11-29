</div> <!-- /#main-container -->

<hr>
      <footer class="footer">
        <div class="container <?php if(!empty($container_class)) echo $container_class; ?>">
          <div class="pull-left"><a href="https://github.com/GSA/project-open-data-dashboard/issues">Fork me on Github</a></div>
        </div>
      </footer>
      <?php if ($this->session->userdata('username')) : ?>
          <script>
          $(document).ready(function(){
            setTimeout(function() {
              setInterval(function(){
                $.post( "/campaign/lastActivity", function( data ) {
                    var jsonobj = $.parseJSON(data);
                    var currenttime = moment.unix(jsonobj.currenttime);
                    var lastactivity = moment.unix(jsonobj.lastactivity);
                    var lastaddminutes =  moment(lastactivity).add(14, 'm');
                    var timer = moment.duration(lastaddminutes.diff(currenttime)).seconds();
                    var manualtime = 60;
                    if((lastaddminutes).isSameOrBefore(currenttime)){
                        $("#sessionSecondsRemaining").text(manualtime);
                        $("#secondsRemaining").show();
                        setInterval(function() {
                          if(manualtime>0){
                            $("#sessionSecondsRemaining").text(manualtime--);
                          }
                        }, 1000);
                      setTimeout(function() {
                        window.location = "/logout?exired=true";
                      }, 60000);
                    }
                });
              }, 240000);
             }, 600000);
          });
          $("#logoutSession").on('click', function(){
            $("#secondsRemaining").hide();
            window.location = "/logout?exired=true";
          });
          $("#extendSession").on('click', function(){
            $("#secondsRemaining").hide();
            location.reload();
          });
          </script>
      <?php endif; ?>
    </div> <!-- /container -->

    </body>
</html>
