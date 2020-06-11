</div> <!-- /#main-container -->

<hr>
      <footer class="footer">
        <div class="container <?php if(!empty($container_class)) echo $container_class; ?>">
          <div class="pull-left"><a href="https://github.com/GSA/project-open-data-dashboard/issues">Fork me on Github</a></div>
        </div>
        <div class="modal" id="secondsRemaining" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Session Expiration Warning</h4>
                    </div>
                    <div class="modal-body">
                        <p>You've been inactive for a while. For your security, we'll log you out automatically. Click "Stay Online" to continue your session. </p>
                        <p>Your session will expire in <span class="bold" id="sessionSecondsRemaining">60</span> seconds.</p>
                    </div>
                    <div class="modal-footer">
                        <button id="extendSession" type="button" class="btn btn-default btn-success" data-dismiss="modal">Stay Online</button>
                        <button id="logoutSession" type="button" class="btn btn-default" data-dismiss="modal">Logout</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal" id="mdlLoggedOut" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">You have been logged out</h4>
                    </div>
                    <div class="modal-body">
                        <p>Your session has expired.</p>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
      </footer>
    </div> <!-- /container -->

    </body>
</html>
