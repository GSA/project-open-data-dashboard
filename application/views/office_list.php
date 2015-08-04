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
          
          <p class="intro-blurb">This is a public dashboard showing Federal agency progress towards implementing the Federal Information Technology Acquisition Reform Act (FITARA). <a href="https://management.cio.gov/">Learn more</a></p>

           <?php if($milestone->selected_milestone == $milestone->current): ?>
                <p class="form-flash text-danger bg-danger"><strong>Current Milestone:</strong> The milestone selected is still in progress. The status of each field will be updated as frequently as possible, but won't be final until the milestone has passed</p>
            <?php endif; ?>


           <?php if($milestone->selected_milestone == $milestone->previous): ?>
                <p class="form-flash text-warning bg-warning"><strong>Previous Milestone:</strong> The milestone selected is the most recently complete one. The status of each field won't be final until a few weeks after the milestone has passed</p>
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
                status_table('CFO Act Agencies', $cfo_offices, $tracker, $config, $section_breakdown, $subsection_breakdown, $milestone);
            }

            if(!empty($executive_offices)) {
                status_table('Other Offices Reporting to the White House', $executive_offices, $config, $milestone->selected_milestone, $milestone->specified);
            }

            if(!empty($independent_offices)) {
                status_table('Other Independent Offices', $independent_offices, $config, $milestone->selected_milestone, $milestone->specified);
            }

            ?>

        </div>
      </div>

<?php include 'footer.php'; ?>