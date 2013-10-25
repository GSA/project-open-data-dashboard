<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>


    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-lg-4">
		
          <h2><?php echo $office['name'] ?></h2>

		<div><a href="<?php echo $office['url'] ?>"><?php echo $office['url'] ?></a></div>
		<div><?php echo $office['notes'] ?></div>				
		
        </div>
      </div>

      <hr>

<?php include 'footer.php'; ?>