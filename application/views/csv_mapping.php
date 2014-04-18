<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->
      
      <div class="row">
        <div class="col-lg-12">
          <h2>CSV Converter - Map Fields</h2>
          
            <form class="form-horizontal form-striped" action="./csv_to_json" method="post" role="form">

                <?php $match = false; ?>
                <?php $count = 0; ?>    
                <?php foreach ($headings as $field): ?>
                    
                    <div class="form-group">
                        <label class="col-sm-2" for="<?php echo $field; ?>"><?php echo $field; ?></label>
                        <div class="col-sm-3">
                            <select id="<?php echo $field; ?>" type="text" name="mapping[<?php echo $count; ?>]">
                                <option value="null">Select a corresponding field</option>
                                <?php foreach ($datajson_model as $pod_field => $null): ?>
                                    <?php 
                                        if (strtolower(trim($field)) == strtolower($pod_field)) {
                                            $selected = 'selected="selected"';
                                            $match = true;
                                        } else {
                                            $selected = '';
                                        }
                                    ?>
                                    <option value="<?php echo $pod_field ?>" <?php echo $selected ?>><?php echo $pod_field ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <?php 
                                if (!$match) echo '<span class="text-danger">No match found</span>';
                                $match = false; 
                                $count++;
                            ?>
                        </div>
                    </div>

                <?php endforeach; ?>

                <div class="form-group">
                    
                    <input type="hidden" name="csv_id" value="<?php echo $csv_id; ?>">
                    <input type="submit" value="Convert" class="btn btn-primary">
                </div>

            </form>

            
        </div>
    </div>      

<?php include 'footer.php'; ?>