<?php 
if($show_all_fields) $container_class = "full-width";
?>

<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

<?php include 'office_table_inc_view.php';?>

<?php 

if($show_all_fields) {
  $dashboard_type = 'full';
} else if($show_qa_fields) {
  $dashboard_type = 'qa';
} else {
  $dashboard_type = '';
}



?>

    <div class="container <?php if(!empty($container_class)) echo $container_class; ?>">
      <!-- Example row of columns -->
      <div class="row">
        <div>
          
          <p class="intro-blurb">This is a public dashboard showing how Federal agencies are performing on the Open Data Policy. <a href="<?php echo site_url();?>docs">Learn more</a></p>

           <?php if($milestone->selected_milestone == $milestone->current): ?>
                <p class="form-flash text-danger bg-danger"><strong>Current Milestone:</strong> The milestone selected is still in progress. The status of each field will be updated as frequently as possible, but won't be final until the milestone has passed</p>
            <?php endif; ?>


           <?php if($milestone->selected_milestone == $milestone->previous && empty($review_status)): ?>
                <p class="form-flash text-warning bg-warning"><strong>Previous Milestone:</strong> The milestone selected is the most recently completed one. The status of each field won't be final until a few weeks after the milestone has passed</p>
            <?php endif; ?>  

           <?php if($milestone->selected_milestone == $milestone->previous && !empty($review_status) && $review_status == "in-progress"): ?>
                <p class="form-flash text-warning bg-warning"><strong>Under Review:</strong> The milestone selected is the most recently completed one but OMB is still review some agencies</p>
            <?php endif; ?>     

           <?php if(!empty($review_status) && $review_status == "complete"): ?>
                <p class="form-flash text-success bg-success"><strong>Reviews Complete:</strong> 
                  <?php if($milestone->selected_milestone == $milestone->previous): ?>
                    The milestone selected is the most recently completed one and 
                  <?php endif; ?>
                  OMB has completed all agency reviews</p>
            <?php endif; ?>       

            <ul class="milestone-selector nav nav-pills">
                <li class="dropdown active">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                      Selected: <?php echo $milestone->milestones[$milestone->selected_milestone]  . ' - ' . date("F jS Y", strtotime($milestone->selected_milestone)); ?> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <?php foreach ($milestone->milestones as $milestone_date => $milestone_name): ?>
                            <li><a href="<?php echo site_url();?>offices/<?php echo $milestone_date; if(!empty($dashboard_type)) echo '/' . $dashboard_type;?>"><?php echo $milestone_name . ' - ' . date("F jS Y", strtotime($milestone_date));?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>

			<?php

			$config = (!empty($max_remote_size)) ? array('max_remote_size' => $max_remote_size) : null;

			if(!empty($cfo_offices)) {

        if($show_all_fields) {
          status_table_full('CFO Act Agencies', $cfo_offices, $tracker, $config, $milestone->selected_milestone, $milestone->specified);        
        } elseif ($show_qa_fields) {
          status_table_qa('CFO Act Agencies', $cfo_offices, $tracker, $config, $section_breakdown, $milestone);
        } else {
          status_table('CFO Act Agencies', $cfo_offices, $tracker, $config, $section_breakdown, $milestone);
        }
				
			}

			if(!empty($executive_offices)) {
				status_table('Other Offices Reporting to the White House', $executive_offices, $config, $milestone->selected_milestone, $milestone->specified);
			}

			if(!empty($independent_offices)) {
				status_table('Other Independent Offices', $independent_offices, $config, $milestone->selected_milestone, $milestone->specified);
			}

      if(!empty($office_totals)) {
      ?>
        <h4>Totals for CFO-Act Agencies</h4>
        <table class="table">          
            <tr>
              <th>Field</th>
              <th>Total</th>
              <th>Average</th>
              <th>Agencies</th>
              <th>Errors</th>
            </tr>

            <?php foreach ($office_totals as $field => $total): 

            if ($total['type'] == 'percent') {
              if ($total['average'] > 1) {
                $average = 'See errors';
              } else {
                $average = $total['average']  * 100 . '%';
              }              
            } else {
              $average = $total['average'];
            }

            ?>
              <tr>
                <td><?php echo $tracker->$field->label; ?></td>
                <td><?php if($total['type'] !== 'percent') echo $total['total']; ?></td>
                <td><?php echo $average; ?></td>
                <td><?php echo $total['office_count']; ?></td>
                <td><?php echo $total['errors']; ?></td>
              </tr>
            <?php endforeach; ?>
        </table>
      <?php
      }
			?>


        </div>
      </div>

<?php include 'footer.php'; ?>