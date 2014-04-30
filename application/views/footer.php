</div> <!-- /#main-container -->

<hr>
      <footer class="footer">
        <div class="container">
        <div class="pull-left"><a href="https://github.com/GSA/project-open-data-dashboard/issues">Fork me on Github</a></div>


     

        <?php if (!$this->session->userdata('username')) : ?>
          <div class="pull-right">
            <a class="btn btn-default btn-auth btn-github" href="/login">Sign in with <b>GitHub</b></a>
          </div> 
        <?php endif; ?>
    
    

      </div>
      </footer>
    </div> <!-- /container -->        

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.1.min.js"><\/script>')</script>

        <script src="/js/vendor/bootstrap.min.js"></script>

        <script src="/js/main.js"></script>


        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', '<?php if($this->config->item('google_analytics_id')) echo $this->config->item('google_analytics_id') ?>', '<?php if($this->config->item('google_analytics_domain')) echo $this->config->item('google_analytics_domain') ?>');
          ga('send', 'pageview');

        </script>


    </body>
</html>
