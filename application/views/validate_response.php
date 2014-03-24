<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->
      
      <div class="row">
        <div class="col-lg-12">

            <h2>Validation Results</h2>

            <?php 
                if(!empty($validation['source'])) {
                    echo '<p>' . count($validation['source']) . ' total records</p>'; 
                }
            ?>


            <?php if(!empty($validation['fail'])) : ?>

                <?php foreach ($validation['fail'] as $fail) {   ?>

                    <p><?php echo $fail ?></p>

                <?php } ?>


            <?php endif; ?>


            <?php 
                if(!empty($validation['errors'])) {

                    $erroneous_record_count = count($validation['errors']);
            ?>

                    <?php if($erroneous_record_count > 100): ?>
                        <p class="text-danger">Only displaying first 100 invalid records</p>
                    <?php else: ?>
                        <p class="text-danger"><?php echo $erroneous_record_count;?> records are invalid</p>
                    <?php endif; ?>

            <?php
                $key_count = array();
                foreach ($validation['errors'] as $key => $error) {   

            ?>
                    <?php if(!empty($key_count)): ?>
                    </div>
                    </div>
                    <?php endif; ?>

                <?php  if(!isset($key_count[$key])): ?>

                <div class="validation-record row">

                    <div class="validation-source col-md-6">
                        <h4>Report for identifier: <?php echo $validation['source'][$key]->identifier ?></h4>
                        <pre><?php print prettyPrint(json_encode($validation['source'][$key])); ?></pre>
                    </div>

                    <div class="validation-errors col-md-6">
                        <h4>Errors</h4>
                <?php endif; ?>

                <?php if(!empty($error['ALL'])): ?>

                        <ul class="validation-full-record">
                        <?php foreach ($error['ALL']['errors'] as $error_description) { ?>

                            <?php if(strpos($error_description, 'but a null is required')) continue; ?>

                            <li><?php echo $error_description ?></li>
                        <?php } ?>
                        </ul>

                <?php 
                    unset($error['ALL']);
                endif; 
                ?>

                        <table class="table table-striped">
                            <tr>
                                <th>Field</th>
                                <th>Errors</th>
                            </tr>
                            <?php foreach ($error as $field => $description) { ?>
                                <tr>                                    
                                    <td><?php echo $field; ?></td>
                                    <td>
                                        <ul>
                                        <?php foreach ($description['errors'] as $error_description) { ?>

                                            <?php 

                                                if(strpos($error_description, 'but a null is required')) continue; 
                                                if(strpos($error_description, 'regex pattern')) continue; 

                                            ?>

                                            <li><?php echo $error_description ?></li>
                                        <?php } ?>


                                        <?php if(!empty($description['sub_fields'])):?>
                                            <li>Sub fields
                                                <ul>
                                                <?php foreach ($description['sub_fields'] as $sub_field => $sub_field_error) { ?>
                                                    <li><strong><?php echo $sub_field ?>:</strong> <?php echo $sub_field_error[0] ?></li>
                                                 <?php } ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>

                                        </ul>
                                    </td>
                                </tr>    
                            <?php } ?>           
                        </table>


            <?php 

                $key_count[$key] = true;

            } 

            ?>  
                        </div>
                    </div>


        <?php } ?>

        <?php if(empty($validation['fail']) && empty($validation['errors'])) : ?>
            100% Valid!
        <?php endif; ?>

        </div>
    </div>      

<?php include 'footer.php'; ?>