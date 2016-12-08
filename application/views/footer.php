</div> <!-- /#main-container -->

<hr>
      <footer class="footer">
        <div class="container <?php if(!empty($container_class)) echo $container_class; ?>">
        <div class="pull-left"><a href="https://github.com/GSA/project-open-data-dashboard/issues">Fork me on Github</a></div>




        <?php if (!$this->session->userdata('username')) : ?>
          <div class="pull-right">
            <a class="btn btn-default btn-auth" href="<?php echo site_url('user/login')?>">Sign in with <b>MAX</b></a>
          </div>
        <?php endif; ?>



      </div>
      </footer>
    </div> <!-- /container -->

    </body>
</html>
