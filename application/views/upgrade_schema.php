<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2>Schema Converter</h2>

            <p>The conversion uses the following rules:</p>

            <h3 style="margin-top : 1em;">Convert a v1.0 data.json to a v1.1 data.json</h3>

            <form action="<?php echo site_url(); ?>upgrade-schema" method="post" enctype="multipart/form-data" role="form">

                 <div class="form-group">
                    <label for="datajson_upload">Upload a v1.0 data.json file</label>
                    <input type="file" name="datajson_upload">
                </div>

                <div class="form-group">
                    <input name="qa" value="true" type="hidden">
                    <input type="hidden" name="output" value="browser">
                    <input type="submit" value="Convert" class="btn btn-primary">
                </div>


            </form>

        </div>
    </div>

<?php include 'footer.php'; ?>