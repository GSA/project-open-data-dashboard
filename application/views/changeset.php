<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2>Changeset Report Generator</h2>


            <form action="<?php echo site_url(); ?>/changeset" method="get" role="form">

                <div class="form-group">

                    <label>Agency</label>
                    <select id="agency-select" name="json_old_select" class="form-control">
                        <option value="">Select an Agency</option>
                        <?php
                            foreach ($orgs as $org) {
                        ?>
                                <option value="organization:<?php echo $org['id']; ?>"><?php echo $org['name']; ?></option>
                        <?php
                            }
                        ?>

                    </select>
                </div>

                <div class="form-group">
                    <label for="json_old">Optional: manually overide selected CKAN organizations</label>
                    <input placeholder="organization:(eia-gov OR doe-gov OR ornl-gov OR osti-gov)" name="json_old" id="json_old" class="form-control">
                </div>


                <div class="form-group">
                    <label for="datajson_new">New data.json URL</label>
                    <input placeholder="http://energy.gov/data.json" name="datajson_new" id="datajson_new" class="form-control">
                </div>

                <div class="form-group">
                    <input type="submit" value="Generate Report" class="btn btn-primary">
                </div>

            </form>


        </div>
    </div>

<?php include 'footer.php'; ?>