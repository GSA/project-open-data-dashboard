<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

<?php include 'office_table_inc_view.php';?>


    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div>
          <h2>Agencies</h2>
          <p>
            See the <a href="/docs">documentation</a> for an explanation of this table.
          </p>

			<?php

			if(!empty($cfo_offices)) {
				status_table('CFO Act Agencies', $cfo_offices); 	
			}

			if(!empty($executive_offices)) {
				status_table('Other Offices Reporting to the White House', $executive_offices); 	
			}

			if(!empty($independent_offices)) {
				status_table('Other Independent Offices', $independent_offices); 	
			}

			?>

        </div>
      </div>

      <hr>

<?php include 'footer.php'; ?>