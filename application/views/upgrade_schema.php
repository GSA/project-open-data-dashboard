<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2>Schema Converter</h2>

            <?php if(!empty($errors)):?>
                <p class="form-flash bg-danger text-danger">
                    <?php echo $errors;?>
                </p>
            <?php endif;?>

            <p>The conversion uses the following rules:</p>

            <ul>
                <li>
                    <strong>accrualperiodicity</strong> - Values will be converted to ISO 8601 syntax based on the <a href="https://project-open-data.cio.gov/iso8601_guidance/#accrualperiodicity">Project Open Data guidance</a>.
                </li>
                <li>
                    <strong>license</strong> - Values that are not URLs will be appended to the following URL: https://project-open-data.cio.gov/unknown-license/#v1-legacy/
                </li>

                <li>
                    <strong>mbox</strong> - Email addresses will be moved to <code>contactPoint.hasEmail</code> and will be appended with <code>mailto:</code>
                </li>
                <li>
                    <strong>format</strong> - Values will be moved to <code>distribution.mediaType</code>
                </li>                
                <li>
                    <strong>accessURL</strong> - Values will be moved to <code>distribution.downloadURL</code>
                </li>  
                <li>
                    <strong>webService</strong> - Values will be moved to <code>distribution.accessURL</code> with <code>distribution.format</code> set as <code>API</code>
                </li>                                

            </ul>


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