<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

<?php include 'office_table_inc_view.php';?>


    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div>
          <h2>Agencies</h2>

            <ul class="milestone-selector nav nav-pills">
                <li class="dropdown active">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                      Selected: <?php echo $milestones[$selected_milestone]  . ' - ' . date("F jS Y", strtotime($selected_milestone)); ?> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <?php foreach ($milestones as $milestone_date => $milestone): ?>
                            <li><a href="?milestone=<?php echo $milestone_date;?>"><?php echo $milestone . ' - ' . date("F jS Y", strtotime($milestone_date));?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>

			<?php

			$config = (!empty($max_remote_size)) ? array('max_remote_size' => $max_remote_size) : null;

			if(!empty($cfo_offices)) {
				status_table('CFO Act Agencies', $cfo_offices, $config);
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