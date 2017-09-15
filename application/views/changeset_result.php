<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2 style="margin-bottom : 0; padding-bottom : 0">Changeset Report for <?php echo htmlentities($datajson_domain); ?></h2>
          <div style="color: #666">date: <?php echo date('l, F j Y g:i a')?></div>


<div style="margin-bottom : 2em; border-bottom : 1px solid #ccc">

    <h3 style="color : #666; margin-bottom : 0; padding-bottom : 0">Data listings in catalog.data.gov: <span style="color : #000"><?php echo ($old_count + 0) ?></span></h3>
    <div style="color: #666">source: <a style="color: #666" href="<?php echo htmlentities($json_old_url); ?>"><?php echo htmlentities($json_old_request); ?></a></div>

    <h3 style="color : #666; margin-bottom : 0; padding-bottom : 0">Data listings in data.json: <span style="color : #000"><?php echo ($new_count + 0) ?></span></h3>
    <div style="color: #666">source: <a style="color: #666" href="<?php echo htmlentities($datajson_new_url) ?>"><?php echo htmlentities($datajson_new_url); ?></a></div>

    <h3 style="color : blue">Matches: <?php echo ($match_count + 0) ?></h3>

    <h3 style="color : red">Removed: <?php echo ($old_count - $match_count) ?></h3>
    <h3 style="color : green">Added: <?php echo ($new_count - $match_count) ?></h3>

</div>

<?php

if(!empty($changeset)) {

    foreach($changeset as $change) {

?>
    <?php if ($change['match']): ?>
        <div style="margin : 2em 0 2em 0">
            <div style="color: green">Matches found for <a href="<?php echo htmlentities($change['url']) ?>"><?php echo htmlentities($change['url']) ?></a></div>

            <?php foreach ($change['matches'] as $match) { ?>
                <div><?php echo htmlentities($match); ?></div>
            <?php } ?>

        </div>

    <?php endif;  ?>


    <?php if (!$change['match']): ?>
        <div style="color: red">No match found for <a href="<?php echo htmlentities($change['url']) ?>"><?php echo htmlentities($change['url']) ?></a></div>
    <?php endif;  ?>


<?php
    }

}
?>

        </div>
    </div>

<?php include 'footer.php'; ?>