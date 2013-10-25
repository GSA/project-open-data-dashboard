<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>


    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-lg-4">
          <h2>Agencies</h2>


			<h3>CFO Act Agencies</h3>
			<table>
				<?php foreach ($cfo_offices as $office):?>
				<tr>
					<td><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></td>
				</tr>
				<?php endforeach;?>
			</table>
			
			<h3>Other Offices Reporting to the White House</h3>
			<table>
				<?php foreach ($executive_offices as $office):?>
				<tr>
					<td><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></td>
				</tr>
				<?php endforeach;?>
			</table>
			
			<h3>Other Independent Offices</h3>
			<table>
				<?php foreach ($independent_offices as $office):?>
				<tr>
					<td><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></td>
				</tr>
				<?php endforeach;?>
			</table>						

        </div>
      </div>

      <hr>

<?php include 'footer.php'; ?>