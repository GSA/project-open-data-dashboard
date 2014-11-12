<?php include 'header_meta_inc_view.php';?>

<link rel="stylesheet" href="css/highlight.css">
<script src="js/vendor/highlight.pack.js"></script>
<script>hljs.initHighlightingOnLoad();</script>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">

            <h2>Validation Results</h2>

            <div class="panel panel-default">
                <div class="panel-heading">
                    Metadata Validation
                </div>

                <table class="table table-striped table-hover">
                    <tbody>

                        <?php if(!empty($datajson_url)) : ?>
                            <tr>
                                <th>Source</th> <td><?php echo $datajson_url; ?> </td>
                            </tr>
                        <?php endif; ?>

                        <?php if(!empty($schema)) : ?>
                            <tr>
                                <th>Schema</th> <td><?php echo $schema; ?></td>
                            </tr>
                        <?php endif; ?> 

                       <?php if(isset($validation['valid_json'])) : ?>
                            <tr <?php if ($validation['valid_json'] === false) echo 'class="danger"' ?>>
                                <th>Valid JSON</th> <td><?php var_export($validation['valid_json']); ?></td>
                            </tr>
                        <?php endif; ?>                          

                        <?php if(!empty($validation['source'])) : ?>
                            <tr>
                                <th>Total datasets</th> <td><?php echo count($validation['source']); ?></td>
                            </tr>
                        <?php endif; ?>                       


                        <?php if(!empty($validation['fail'])) : ?>
                            <tr>
                                <th>Errors</th>
                                <td>
                                    <?php foreach ($validation['fail'] as $fail) {   ?>

                                        <p><?php echo $fail ?></p>

                                    <?php } ?>
                                </td>
                            </tr>

                        <?php endif; ?>

                        <?php if(!empty($validation['errors'])) : ?>                
                            <tr class="danger">
                                <th>Datasets with invalid metadata</th> <td><span class="text-danger"><?php echo count($validation['errors'])?></span></td>
                            </tr>
                        <?php endif; ?> 

                    </tbody>
                </table>

            </div>


            <?php if(!empty($validation['qa'])) : ?>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        Completeness
                    </div>

                    <table class="table table-striped table-hover">
                        <tbody>

                                <?php if(!empty($validation['qa']['accessURL_present'])) : ?>
                                    <tr>
                                        <th>Datasets with Downloadable URLs (accessURL)</th><td><?php echo $validation['qa']['accessURL_present']; ?> </td>
                                    </tr>
                                 <?php endif; ?>

                                <?php if(!empty($validation['qa']['accessURL_total'])) : ?>
                                    <tr>
                                        <th>Total Downloadable URLs (accessURL)</th><td><?php echo $validation['qa']['accessURL_total']; ?> </td>
                                    </tr>
                                <?php endif; ?>


                                <?php if(!empty($validation['qa']['programCodes'])) : ?>
                                    <tr>
                                        <th>Programs Represented</th><td> <?php echo count($validation['qa']['programCodes']); ?>    </td>
                                    </tr>
                                 <?php endif; ?>

                                <?php if(!empty($validation['qa']['bureauCodes'])) : ?>
                                    <tr>
                                        <th>Bureaus Represented</th><td> <?php echo count($validation['qa']['bureauCodes']); ?>   </td>
                                    </tr>    
                                <?php endif; ?>


                                <?php if(!empty($validation['qa']['accessLevel_public'])) : ?>
                                    <tr>
                                        <th>Access Level: Public</th><td><?php echo $validation['qa']['accessLevel_public']; ?> </td>
                                    </tr>
                                 <?php endif; ?>

                                <?php if(!empty($validation['qa']['accessLevel_restricted'])) : ?>
                                    <tr>
                                        <th>Access Level: Restricted</th><td><?php echo $validation['qa']['accessLevel_restricted']; ?> </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if(!empty($validation['qa']['accessLevel_nonpublic'])) : ?>
                                    <tr>
                                        <th>Access Level: Non-Public</th><td><?php echo $validation['qa']['accessLevel_nonpublic']; ?> </td>
                                    </tr>
                                <?php endif; ?>


                        </tbody>
                    </table>

                </div>
            <?php endif; ?>

            <?php
                if(!empty($validation['errors'])) {

                 $key_count = array();
                foreach ($validation['errors'] as $key => $error) {

                    $source_key = (strpos($key, '[') !== false) ? get_between($key, '[', ']') : $key;

            ?>
                    <?php if(!empty($key_count)): ?>
                    </div>
                    </div>
                    <?php endif; ?>

                <?php  if(!isset($key_count[$key])): ?>

                <div class="validation-record row">

                    <div class="validation-source col-md-6">
                        <h4>Report for identifier: <?php echo (!empty($validation['source'][$source_key]->identifier)) ? $validation['source'][$source_key]->identifier : '' ?></h4>
                        <pre><code><?php print htmlentities(prettyPrint(str_replace('\/', '/', json_encode($validation['source'][$source_key])))); ?></code></pre>
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
                                    <td>
                                        <a href="https://project-open-data.cio.gov/schema/#<?php echo $field; ?>">
                                            <code class="hljs-attribute"><?php echo $field; ?></code>
                                        </a>
                                    </td>
                                    <td>
                                        <ul>
                                        <?php foreach ($description['errors'] as $error_description) { ?>

                                            <?php

                                                if(strpos($error_description, 'but a null is required')) continue;
                                                if(strpos($error_description, 'regex pattern')) {
                                                    $error_description = substr($error_description, 0, strpos($error_description, 'pattern') + 8);
                                                }

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