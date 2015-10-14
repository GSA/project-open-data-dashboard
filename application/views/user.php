<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2>Account</h2>
           <table class="table table-bordered table-hover">
                <tr>
                    <th scope='row'>Name</th>
                    <td><?php echo ($this->session->userdata('name_full')); ?></td>
                </tr>

                <tr>
                    <th scope='row'>Username</th>
                    <td><a href="<?php echo ($this->session->userdata('provider_url')); ?>"><?php echo ($this->session->userdata('username')); ?></a></td>
                </tr>

                <tr>
                    <th scope='row'>Permissions</th>
                    <td><?php echo ($this->session->userdata('permissions')); ?></td>
                </tr>




           </table>
        </div>
    </div>

<?php include 'footer.php'; ?>