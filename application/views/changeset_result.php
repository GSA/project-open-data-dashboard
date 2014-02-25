<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->
      
      <div class="row">
        <div class="col-lg-12">
          <h2>Changeset Report</h2>
           


<h3 style="color : #666">Data listings in CKAN: <span style="color : #000"><?php echo $old_count ?></span></h3>
<h3 style="color : #666">Data listings in data.json: <span style="color : #000"><?php echo $new_count ?></span></h3>
<h3 style="color : #666">Matches: <span style="color : #000"><?php echo $match_count ?></span></h3>

<h3 style="color : red">Removed: <?php echo ($old_count - $match_count) ?></h3>
<h3 style="color : green">Added: <?php echo ($new_count - $match_count) ?></h3>


<?php

if(!empty($changeset)) {

    foreach($changeset as $change) {

?>
    <?php if ($change['match']): ?>
        <div style="margin : 2em 0 2em 0">
            <div style="color: green">Matches found for <a href="<?php echo $change['url'] ?>"><?php echo $change['url'] ?></a></div>

            <?php foreach ($change['matches'] as $match) { ?>
                <div><?php echo $match; ?></div>
            <?php } ?>

        </div>

    <?php endif;  ?>


    <?php if (!$change['match']): ?>
        <div style="color: red">No match found for <a href="<?php echo $change['url'] ?>"><?php echo $change['url'] ?></a></div>
    <?php endif;  ?>


<?php
    }

}
?>

        </div>
    </div>      

<?php include 'footer.php'; ?>