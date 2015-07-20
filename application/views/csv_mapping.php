<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2>CSV Converter - Map Fields</h2>

            <form class="form-horizontal form-striped" action="<?php echo site_url(); ?>datagov/csv_to_json" method="post" role="form">

                <?php echo $select_mapping; ?>

                <div class="form-group">

                    <input type="hidden" name="schema" value="<?php echo $schema; ?>">
                    <input type="hidden" name="csv_id" value="<?php echo $csv_id; ?>">
                    <input type="submit" value="Convert" class="btn btn-primary">
                </div>

            </form>


        </div>
    </div>

<?php include 'footer.php'; ?>