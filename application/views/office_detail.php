<?php $page_title = $office->name;?>

<?php include 'header_meta_inc_view.php';?>

    <script src="<?php echo site_url('js/vendor/raphael-min.js')?>"></script>
    <script src="<?php echo site_url('js/vendor/g.raphael.js')?>"></script>
    <script src="<?php echo site_url('js/vendor/g.pie.js')?>"></script>
    <script src="<?php echo site_url('js/vendor/morris.min.js')?>"></script>

    <link href="<?php echo site_url('css/morris.css')?>" rel="stylesheet">

<?php include 'header_inc_view.php';?>

<?php include 'office_table_inc_view.php';?>

<?php $permission_level = 'admin' ?>


    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div>
		

            <?php if($this->session->flashdata('outcome') && $this->session->flashdata('status')): ?>
                <p class="form-flash bg-<?php echo $this->session->flashdata('outcome'); ?>"><?php echo $this->session->flashdata('status'); ?></p>
            <?php endif; ?>


              <h2><?php echo $office->name ?> - <?php echo $milestone->milestones[$milestone->selected_milestone];?> - <?php echo date("F jS Y", strtotime($milestone->selected_milestone)) ?></h2>

        
			<div><a href="<?php echo $office->url ?>"><?php echo $office->url ?></a></div>
			<div><?php echo $office->notes ?></div>				
	
	
			<?php if(!empty($office->parent_office_id)): ?>
				<div class="hidden"><a href="<?php echo site_url('office/') . $office->parent_office_id; ?>">Parent Office</a></div>				
			<?php endif; ?>
		
	
		
        </div>



        <?php if(!empty($office_campaign)): ?>
        
        <?php       
        
            if(!empty($office_campaign->datajson_status)) {
                $office_campaign->datajson_status = json_decode($office_campaign->datajson_status);         
            }
            
            if(!empty($office_campaign->datapage_status)) {
                $office_campaign->datapage_status = json_decode($office_campaign->datapage_status);         
            }   
                
            
            if(!empty($office_campaign->digitalstrategy_status)) {
                $office_campaign->digitalstrategy_status = json_decode($office_campaign->digitalstrategy_status);           
            }      


            if(!empty($office_campaign->tracker_fields)) {
                $office_campaign->tracker_fields = json_decode($office_campaign->tracker_fields);           
            }     


            if(!empty($office_campaign->tracker_status)) {
                $office_campaign->tracker_status = json_decode($office_campaign->tracker_status);           
            }  


                
            $crawl_details = array(
                                    'pdl_datajson', 
                                    'pdl_slashdata', 
                                    'pdl_valid_metadata', 
                                    'pdl_datasets', 
                                    'pdl_link_check', 
                                    'pe_feedback_specified', 
                                    'edi_schedule_delivered', 
                                    'ps_publication_process',
                                    'pdl_downloadable',
                                    'edi_license_present'
                                    );


            $active_section = (!empty($selected_category)) ? $selected_category : 'cb';  


        ?>



            <?php /*if ($this->session->userdata('permissions') == $permission_level): ?>

                <?php if(!empty($office_campaign->contact_email)): ?>
                    <div>Contact: <a href="mailto:<?php echo $office_campaign->contact_email; ?>"><?php echo $office_campaign->contact_email; ?></a></div>
                <?php else:?>
                    <div class="bg-danger">No Data Lead listed!</div>
                <?php endif;?>

            <?php endif;*/ ?>
                


           <?php if($milestone->selected_milestone == $milestone->current): ?>
                <p class="form-flash text-danger bg-danger"><strong>Current Milestone:</strong> The milestone selected is still in progress. The Automated Metrics will update daily until the milestone date.</p>
            <?php endif; ?>


           <?php if($milestone->selected_milestone == $milestone->previous): ?>
                <p class="form-flash text-warning bg-warning"><strong>Previous Milestone:</strong> The milestone selected is the most recently complete one. The Automated Metrics are a snapshot from the milestone date.</p>
            <?php endif; ?>  


           <?php if(empty($office_campaign->tracker_status->status) OR $office_campaign->tracker_status->status == 'not-started'): ?>
                <p class="form-flash text-danger bg-danger"><strong>OMB Review Has Not Begun:</strong> OMB has not begun reviewing the agency for this milestone. The review will begin after the milestone date.</p>
            <?php endif; ?>  

           <?php if(!empty($office_campaign->tracker_status->status) && $office_campaign->tracker_status->status == 'in-progress'): ?>
                <p class="form-flash text-warning bg-warning"><strong>OMB Review In Progress:</strong> OMB is currently reviewing the agency for this milestone. This review status indicator will change once the review is complete.</p>
            <?php endif; ?>            

           <?php if(!empty($office_campaign->tracker_status->status) && $office_campaign->tracker_status->status == 'complete'): ?>
                <p class="form-flash text-success bg-success"><strong>OMB Review Complete:</strong> OMB has completed the agency review for this milestone. Agencies should contact their OMB desk officer if anything looks incorrect.</p>
            <?php endif; ?>                 


            <ul class="milestone-selector nav nav-pills">
                <li class="dropdown active">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                      Selected: <?php echo $milestone->milestones[$milestone->selected_milestone]  . ' - ' . date("F jS Y", strtotime($milestone->selected_milestone)); ?> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <?php foreach ($milestone->milestones as $milestone_date => $milestone_name): ?>
                            <li><a href="<?php echo site_url();?>offices/detail/<?php echo $office->id; ?>/<?php echo $milestone_date;?>"><?php echo $milestone_name . ' - ' . date("F jS Y", strtotime($milestone_date));?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>



            <a name="general_indicators" class="anchor-point"></a>
            <h3>General Indicators <a class="info-icon" href="<?php echo site_url('docs'); ?>#general_indicators"><span class="glyphicon glyphicon-info-sign"></span></a></h3>
            <p>These indicators are reviewed by the Office of Management and Budget</p>


           <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                <form method="post" action="<?php echo site_url(); ?>datagov/status-review-update" role="form">
            <?php endif;?>

                <table class="table">

                    
                        <tr>
                            <th>Review Status</th>
                            <td>                                
                                <?php 

                                    
                                    if (!empty($office_campaign->tracker_status->status)) {
                                        echo  $office_campaign->tracker_status->status; 
                                    } 
                                ?>
                            </td>


                            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                <td>
                                                
                                    <select name="status">
                                        <option value="" <?php echo (empty($office_campaign->tracker_status->status)) ? 'selected = "selected"' : '' ?>>Select Status</option>                                
                                        <option <?php echo (!empty($office_campaign->tracker_status) && $office_campaign->tracker_status->status == "not-started") ? 'selected = "selected"' : '' ?> value="not-started">Not Reviewed</option>
                                        <option <?php echo (!empty($office_campaign->tracker_status) && $office_campaign->tracker_status->status == "in-progress") ? 'selected = "selected"' : '' ?> value="in-progress">In Progress</option>
                                        <option <?php echo (!empty($office_campaign->tracker_status) && $office_campaign->tracker_status->status == "complete") ? 'selected = "selected"' : '' ?> value="complete">Review Complete</option>
                                    </select>
                                    
                                </td>
                            <?php endif; ?>

                        </tr>


                        <tr>
                            <th>Reviewer</th>
                            <td>                                
                                <?php if (!empty($office_campaign->tracker_status->reviewer_email)) echo  $office_campaign->tracker_status->reviewer_email ?>
                            </td>


                            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                <td>
                                    <input type="text" name="reviewer_email" value="<?php if (!empty($office_campaign->tracker_status->reviewer_email)) echo  $office_campaign->tracker_status->reviewer_email ?>">
                                </td>
                            <?php endif; ?>

                        </tr>
                    

                    
                        <tr>
                            <th>Last Updated</th>
                            <td>
                                <?php if (!empty( $office_campaign->tracker_status->last_updated)): ?>
                                    <?php echo  $office_campaign->tracker_status->last_updated ?>
                                    <?php if (!empty( $office_campaign->tracker_status->last_editor)) echo  ' by ' . $office_campaign->tracker_status->last_editor ?>
                                <?php endif; ?>
                            </td>

                            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                <td></td>  
                            <?php endif; ?>                         

                        </tr>
                    

                </table>

            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>                

                    <input type="hidden" name="status_id" value="<?php echo $office_campaign->status_id; ?>">  
                    <input type="hidden" name="office_id" value="<?php echo $office->id; ?>">   
                    <input type="hidden" name="milestone" value="<?php echo $milestone->selected_milestone; ?>">                       

                    <button type="submit" class="btn btn-success" name="review_status_submit">Update</button>                    

                </form>
            <?php endif;?>
  

           <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                <form method="post" action="<?php echo site_url(); ?>datagov/status-update" role="form">
            <?php endif;?>

                <div class="general-notes">
                    
                    <?php 
                        $status_field_name = 'office_general';
                        $note_field = "note_office_general";
                        $note_data = (!empty($notes[$note_field])) ? $notes[$note_field] : '';

                        if(!empty($notes[$note_field])) {                                        
                            $note_data = $notes[$note_field];
                        } else {
                            $note_data = $note_model;
                        }
                    ?>

                    <?php if(empty($note_data->current->note)): ?>
                        <div class="note-heading">
                            <span class="note-metadata">
                                No general notes have been added yet
                            </span>                                    
                        </div>
                    <?php endif;?>

                    <?php if(!empty($note_data->current->note) OR ($this->session->userdata('permissions') == $permission_level)): ?>
                    <div class="edit-toggle">
                        <div class="edit-area"><?php echo $note_data->current->note_html; ?></div>
                        <div class="edit-raw hidden" data-fieldname="note_<?php echo $status_field_name ?>"><?php echo $note_data->current->note; ?></div>

                        <?php if (!empty($note_data->current->date) && !empty($note_data->current->author)): ?>
                            <div class="note-metadata">
                                Lasted edited on <?php echo $note_data->current->date;?> by <?php echo $note_data->current->author;?>
                            </div> 
                        <?php endif; ?>

                        <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                            <button class="btn btn-primary edit-button" type="button">Edit</button>                                
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>


            <div class="row">
            <?php
                $edi_public         = !empty($office_campaign->tracker_fields->edi_access_public) ? $office_campaign->tracker_fields->edi_access_public : 0;
                $edi_restricted     = !empty($office_campaign->tracker_fields->edi_access_restricted) ? $office_campaign->tracker_fields->edi_access_restricted : 0;
                $edi_nonpublic      = !empty($office_campaign->tracker_fields->edi_access_nonpublic) ? $office_campaign->tracker_fields->edi_access_nonpublic : 0;

                $edi_total = $edi_public + $edi_restricted + $edi_nonpublic;

                if ($edi_total > 0) :
            ?>

            <div class="col-sm-4">
                <h3 style="text-align:center">Inventory Composition</h3>
                <div id="edi-breakdown" style="height: 250px;"></div>
            </div>

            <script>
                new Morris.Donut({
                  element: 'edi-breakdown',
                  data: [
                    {value: <?php echo floor(($edi_public/$edi_total) * 100) ;?>, label: 'Public'},
                    {value: <?php echo floor(($edi_restricted/$edi_total) * 100) ;?>, label: 'Restricted'},
                    {value: <?php echo floor(($edi_nonpublic/$edi_total) * 100) ;?>, label: 'Non-Public'},
                  ],
                  backgroundColor: '',
                  labelColor: '#666',
                  colors: [
                    '#5cb85c',
                    '#5bc0de',
                    '#f0ad4e',
                    '#95D7BB'
                  ],
                  formatter: function (x) { return x + "%"}
                });

            </script>

            <?php endif; ?>

            <?php
                $pdl_total          = !empty($office_campaign->tracker_fields->pdl_datasets) ? $office_campaign->tracker_fields->pdl_datasets : 0;
                $pdl_public         = !empty($office_campaign->tracker_fields->pdl_downloadable) ? $office_campaign->tracker_fields->pdl_downloadable : 0;

                $pdl_unreleased = $pdl_total - $pdl_public;

                if ($pdl_total > 0) :
            ?>


            <div class="col-sm-4">
                <h3 style="text-align:center">Public Dataset Status</h3>
                <div id="pdl-breakdown" style="height: 250px;"></div>
            </div>

            <script>
                new Morris.Donut({
                  element: 'pdl-breakdown',
                  data: [
                    {value: <?php echo floor(($pdl_public/$pdl_total) * 100) ;?>, label: 'Published'},
                    {value: <?php echo floor(($pdl_unreleased/$pdl_total) * 100) ;?>, label: 'Unpublished'}
                  ],
                  backgroundColor: '',
                  labelColor: '#666',
                  colors: [
                    '#5cb85c',
                    '#5bc0de',
                    '#f0ad4e',
                    '#95D7BB'
                  ],
                  formatter: function (x) { return x + "%"}
                });

            </script>            

            <?php endif; ?>


            <?php
                $pdl_link_total          = !empty($office_campaign->tracker_fields->pdl_link_total) ? $office_campaign->tracker_fields->pdl_link_total : 0;
                $pdl_link_2xx            = !empty($office_campaign->tracker_fields->pdl_link_2xx)   ? $office_campaign->tracker_fields->pdl_link_2xx : 0;
                $pdl_link_3xx            = !empty($office_campaign->tracker_fields->pdl_link_3xx)   ? $office_campaign->tracker_fields->pdl_link_3xx : 0;
                $pdl_link_4xx            = !empty($office_campaign->tracker_fields->pdl_link_4xx)   ? $office_campaign->tracker_fields->pdl_link_4xx : 0;
                $pdl_link_5xx            = !empty($office_campaign->tracker_fields->pdl_link_5xx)   ? $office_campaign->tracker_fields->pdl_link_5xx : 0;                                                

                $pdl_link_other         = $pdl_link_total - ($pdl_link_2xx + $pdl_link_3xx + $pdl_link_4xx + $pdl_link_5xx);

                if ($pdl_link_total > 0 && $pdl_link_2xx > 0) :
            ?>


            <div class="col-sm-4">
                <h3 style="text-align:center">Dataset Link Quality</h3>
                <div id="link-status" style="height: 250px;"></div>
            </div>

            <script>
                new Morris.Donut({
                  element: 'link-status',
                  data: [
                    {value: <?php echo round(($pdl_link_2xx/$pdl_link_total) * 100, 1) ;?>, label: 'Working'},
                    {value: <?php echo round(($pdl_link_3xx/$pdl_link_total) * 100, 1) ;?>, label: 'Redirected'},
                    {value: <?php echo round(($pdl_link_4xx/$pdl_link_total) * 100, 1) ;?>, label: 'Broken'},
                    {value: <?php echo round(($pdl_link_5xx/$pdl_link_total) * 100, 1) ;?>, label: 'Errors'},
                    {value: <?php echo round(($pdl_link_other/$pdl_link_total) * 100, 1) ;?>, label: 'Other'}
                  ],
                  backgroundColor: '',
                  labelColor: '#666',
                  colors: [
                    '#5cb85c',
                    '#5bc0de',
                    '#a94442',
                    '#f0ad4e',
                    '#ccc'
                  ],
                  formatter: function (x) { return x + "%"}
                });

            </script>            

            <?php endif; ?>


                
            </div>    


            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                <div  class="pull-right" style="margin : 1em 0;">                                
                    <button class="btn btn-default btn-xs" id="accShow">Show All Notes</button>
                    <button type="submit" class="btn btn-success btn-xs">Update</button> 
                </div>  
            <?php endif;?>


            <!-- Nav tabs -->
            <ul class="nav nav-tabs tracker-sections">
                
                <?php foreach ($section_breakdown as $section_abbreviation => $section_title): ?>

                    <?php
                        if ($milestone->selected_milestone < '2014-11-30' && $section_abbreviation == 'ui') continue;
                        
                        $aggregate_score = $section_abbreviation . '_aggregate_score';

                        if(!empty($office_campaign->tracker_fields->$aggregate_score)) {
                            $section_score =  'bg-' . status_color($office_campaign->tracker_fields->$aggregate_score);
                        } else {
                            $section_score =  '';
                        }
                    ?>

                    <li  <?php if($section_abbreviation == $active_section) echo 'class="active"'; ?>>
                        <a name="<?php echo $section_abbreviation . '_tab';?>" href="#<?php echo $section_abbreviation;?>" data-toggle="tab">
                            <?php echo $section_title;?>
                            <div class="section-score <?php echo $section_score?>"></div>
                        </a>
                    </li>
                <?php endforeach; reset($section_breakdown);  ?>
            </ul>

                <!-- Tab panes -->
                <div class="tab-content tracker-content">
                  
                  <?php foreach ($section_breakdown as $section_abbreviation => $section_title): ?>
                        <div class="tab-pane <?php if($section_abbreviation == $active_section) echo 'active'; ?>" id="<?php echo $section_abbreviation;?>">



                            <div class="section-notes">
                                
                                <?php 
                                    $note_field = 'note_' . $section_abbreviation . '_aggregate_score';
                                    $note_data = (!empty($notes[$note_field])) ? $notes[$note_field] : $note_model;
                                ?>

                                <div><?php echo $note_data->current->note_html; ?></div>
                                
                                <?php if (!empty($note_data->current->date) && !empty($note_data->current->author)): ?>
                                    <div class="note-metadata">
                                        Lasted edited on <?php echo $note_data->current->date;?> by <?php echo $note_data->current->author;?>
                                    </div> 
                                <?php endif; ?>                                

                            </div>

                            <?php 
                                $highlight_field = $section_abbreviation . '_selected_best_practice';
                            ?>

                           <?php if(!empty($office_campaign->tracker_fields->$highlight_field) && $office_campaign->tracker_fields->$highlight_field == 'yes'): ?>
                                <p class="form-flash text-success bg-success"><strong>Best Practice:</strong> <?php echo $office->name ?> has been highlighted for demonstrating a best practice on the <?php echo $section_title ?> indicator</p>
                            <?php endif; ?>                                  

                            
                           <table class="table table-striped table-hover" id="note-expander-parent">

                                <tr class="table-header">
                                    <th>Indicator</th>
                                    <th>Status</th>

                                    <!--
                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                        <th></th>
                                    <?php endif; ?> 
                                    <th>Automated Metrics</th>
                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                        <th></th>
                                    <?php endif; ?>   
                                    -->
                                </th>


                                <?php foreach ($tracker_model as $tracker_field_name => $tracker_field_meta) : ?>

                                    <?php

                                        // Skip this field if it's not part of current section
                                        if (substr($tracker_field_name, 0, strlen($section_abbreviation)) !== $section_abbreviation) continue;                        
                                        
                                        // Skip this field if it's no longer relevant in later milestones
                                        if ($tracker_field_name == 'edi_schedule_risk' && $milestone->selected_milestone > '2014-11-30') continue;   

                                        // If this is a best practice highlight field, don't show it unless logged in
                                        if(strpos($tracker_field_name, 'selected_best_practice') !== false && !$this->session->userdata('permissions') == $permission_level) continue;

                                        if (!empty($office_campaign->tracker_fields->$tracker_field_name)) {
                                            if($office_campaign->tracker_fields->$tracker_field_name == 'yes' || $office_campaign->tracker_fields->$tracker_field_name == 'green') {
                                                $status_icon = '<i class="text-success fa fa-check-square"></i>';  
                                                $status_class = 'success';  
                                            } else if ($office_campaign->tracker_fields->$tracker_field_name == 'no' || $office_campaign->tracker_fields->$tracker_field_name == 'red') {
                                                $status_icon =  '<i class="text-danger fa fa-times-circle"></i>';    
                                                $status_class = 'danger';
                                            } else {
                                                $status_icon = '<i class="text-warning fa fa-exclamation-triangle"></i>';            
                                                $status_class = '';
                                            } 
                                        } else {
                                            //$office_campaign->tracker_fields->$tracker_field_name = '';
                                            $status_icon = '';            
                                            $status_class = '';
                                        }
                                   
                                    ?>

                                    <?php if ($this->session->userdata('permissions') == $permission_level || 
                                                ($this->session->userdata('permissions') != $permission_level && 
                                                    ($tracker_field_meta->type !== "textarea" || 
                                                        ($tracker_field_meta->type === "textarea" && !empty($office_campaign->tracker_fields->$tracker_field_name))))): ?>
                                        <tr <?php //if(!empty($status_class)) echo "class=\"$status_class\""; ?>>

                                            <td class="tracker-field<?php if (isset($tracker_field_meta->indent)) echo " tracker-field-indent" . $tracker_field_meta->indent;?>">                                            
                                                <a name="tracker_<?php echo $tracker_field_name ?>" class="anchor-point"></a>
                                                <strong>
                                                    <a href="<?php echo site_url('docs') . '#' . $tracker_field_name ?>">
                                                        <span class="glyphicon glyphicon-info-sign"></span>
                                                    </a>
                                                        <?php echo $tracker_field_meta->label ?>
                                                </strong>
                                            </td>                        

                                            <?php if ($this->session->userdata('permissions') != $permission_level) : ?>
                                                <td>
                                                    <?php 
                                                        $overflow_text = false;

                                                        if (!empty($status_icon) && ($tracker_field_meta->type == "select" || $tracker_field_meta->type == "traffic")) {
                                                            echo $status_icon;     
                                                        } elseif ($tracker_field_meta->type == "status") {
                                                            if ($office_campaign->tracker_fields->$tracker_field_name === "not-submitted") {
                                                                echo "Not submitted";
                                                            } elseif ($office_campaign->tracker_fields->$tracker_field_name === "on-time") {
                                                                echo "Submitted on time";
                                                            } elseif ($office_campaign->tracker_fields->$tracker_field_name === "late") {
                                                                echo "Submitted Late";
                                                            } elseif ($office_campaign->tracker_fields->$tracker_field_name === "rev-requested") {
                                                                echo "Revision Requested";
                                                            } elseif ($office_campaign->tracker_fields->$tracker_field_name === "approved") {
                                                                echo "Approved";
                                                            }
                                                        } else {
                                                            if (!empty($office_campaign->tracker_fields->$tracker_field_name)) {
                                                                if(strlen($office_campaign->tracker_fields->$tracker_field_name) < 20) {
                                                                    echo $office_campaign->tracker_fields->$tracker_field_name;
                                                                } else {
                                                                    $overflow_text = true;
                                                                    echo '<em>See below</em>';
                                                                }

                                                            }
                                                        }                       
                                                    ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                                <td>

                                                    <?php if ($tracker_field_meta->type == "select") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Status</option>                                
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "yes") ? 'selected = "selected"' : '' ?> value="yes">Yes</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "no") ? 'selected = "selected"' : '' ?> value="no">No</option>
                                                        </select>
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "grade") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Grade</option>                                
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "A") ? 'selected = "selected"' : '' ?> value="A">A</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "B") ? 'selected = "selected"' : '' ?> value="B">B</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "C") ? 'selected = "selected"' : '' ?> value="C">C</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "D") ? 'selected = "selected"' : '' ?> value="D">D</option>
                                                        </select>
                                                    <?php endif; ?>


                                                    <?php if ($tracker_field_meta->type == "progress") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Progress</option>                                
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "progress") ? 'selected = "selected"' : '' ?> value="progress">Progress</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "neutral") ? 'selected = "selected"' : '' ?> value="neutral">Neutral</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "retrogress") ? 'selected = "selected"' : '' ?> value="retrogress">Retrogress</option>
                                                        </select>
                                                    <?php endif; ?>     


                                                    <?php if ($tracker_field_meta->type == "traffic") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Status</option>                                
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "red") ? 'selected = "selected"' : '' ?> value="red">Red</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "yellow") ? 'selected = "selected"' : '' ?> value="yellow">Yellow</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "green") ? 'selected = "selected"' : '' ?> value="green">Green</option>
                                                        </select>
                                                    <?php endif; ?>  


                                                    <?php if ($tracker_field_meta->type == "status") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Status</option>                                
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "not-submitted") ? 'selected = "selected"' : '' ?> value="not-submitted">Not Submitted</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "on-time") ? 'selected = "selected"' : '' ?> value="on-time">Submitted on Time</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "late") ? 'selected = "selected"' : '' ?> value="late">Submitted Late</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "rev-requested") ? 'selected = "selected"' : '' ?> value="rev-requested">Revision Requested</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "approved") ? 'selected = "selected"' : '' ?> value="approved">Approved</option>
                                                        </select>
                                                    <?php endif; ?>  

                                                    <?php if ($tracker_field_meta->type == "integer") : ?>
                                                        <input type="number" name="<?php echo $tracker_field_name ?>" value="<?php echo $office_campaign->tracker_fields->$tracker_field_name;?>" min="0" step="1">
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "url") : ?>
                                                        <input type="url" name="<?php echo $tracker_field_name ?>" value="<?php echo $office_campaign->tracker_fields->$tracker_field_name;?>">
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "string") : ?>
                                                        <input type="text" name="<?php echo $tracker_field_name ?>" value="<?php echo $office_campaign->tracker_fields->$tracker_field_name;?>" maxlength="<?php echo $tracker_field_meta->maxlength;?>">
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "textarea") : ?>
                                                        <textarea name="<?php echo $tracker_field_name ?>" cols="80" rows="5" maxlength="<?php echo $tracker_field_meta->maxlength;?>"><?php echo $office_campaign->tracker_fields->$tracker_field_name;?></textarea>
                                                    <?php endif; ?>

                                                </td>
                                            <?php endif; ?>

                                            <!--
                                            <td>

                                                <?php if (array_search($tracker_field_name, $crawl_details) !== false):?> 

                                                    <a href="#<?php echo $tracker_field_name ?>">Crawl details</a>

                                                <?php endif; ?>

                                            </td>

                                            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>     
                                            <td>                                            
                                                <a class="btn btn-xs btn-default collapsed pull-right" href="#note-expander-<?php echo $tracker_field_name ?>" data-parent="note-expander-parent" data-toggle="collapse">
                                                    Notes
                                                </a>                                            
                                            </td>   
                                            <?php endif; ?>  
                                            -->
                                        </tr>
                                    <?php endif; ?>


                                    <?php if (isset($overflow_text) && $overflow_text): ?>
                                    <tr>
                                        <td colspan="5" class="overflow-row">
                                            <?php echo $office_campaign->tracker_fields->$tracker_field_name; ?>
                                        </td>
                                    </tr>
                                    <?php endif;?>

                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                    <tr>
                                        <td colspan="5" class="hidden-row">
                                            <div class="edit-toggle collapse container form-group" id="note-expander-<?php echo $tracker_field_name ?>">
                                                
                                                <?php 
                                                    $note_field = "note_$tracker_field_name";

                                                    $note_data = (!empty($notes[$note_field])) ? $notes[$note_field] : '';

                                                    if(!empty($notes[$note_field])) {                                        
                                                        $note_data = $notes[$note_field];
                                                    } else {
                                                        $note_data = $note_model;
                                                    }
                                                ?>  
                                                
                                                <div class="edit-area"><?php echo $note_data->current->note_html; ?></div>
                                                <div class="edit-raw hidden" data-fieldname="note_<?php echo $tracker_field_name ?>"><?php echo $note_data->current->note; ?></div>

                                                <?php if (!empty($note_data->current->date) && !empty($note_data->current->author)): ?>
                                                    <div class="note-metadata">
                                                        Lasted edited on <?php echo $note_data->current->date;?> by <?php echo $note_data->current->author;?>
                                                    </div> 
                                                <?php endif; ?>

                                                <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                                    <button class="btn btn-primary edit-button pull-right" type="button">Edit</button>                                
                                                <?php endif; ?>
                                                

                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif;?>

                                <?php reset($tracker_model); endforeach; ?>

                            </table>   



                        </div>
                    <?php endforeach; ?>
                </div>


            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>


                    <?php if(!empty($office_campaign->tracker_status)): ?>
                        <input type="hidden" name="reviewer_email" value="<?php if (!empty($office_campaign->tracker_status->reviewer_email)) echo  $office_campaign->tracker_status->reviewer_email ?>">
                        <input type="hidden" name="status" value="<?php if (!empty($office_campaign->tracker_status->status)) echo  $office_campaign->tracker_status->status ?>">
                    <?php endif; ?>

                    <input type="hidden" name="office_id" value="<?php echo $office->id; ?>">   
                    <input type="hidden" name="status_id" value="<?php echo $office_campaign->status_id; ?>">  
                    <input type="hidden" name="milestone" value="<?php echo $milestone->selected_milestone; ?>">                       
                </form>
            <?php endif; ?>   



<!-- ################################################################################ 



            
            <a name="automated_metrics" class="anchor-point"></a>
            <h3 id="automated-metrics-heading">Automated Metrics <a class="info-icon" href="<?php echo site_url('docs'); ?>#automated_metrics"><span class="glyphicon glyphicon-info-sign"></span></a></h3>


		     <?php if(empty($office_campaign->datajson_status) && empty($office_campaign->datapage_status) && empty($office_campaign->digitalstrategy_status)): ?>
                <p>No automated metrics are currently available for this milestone</p>
             <?php else: ?> 
                <p>These metrics are generated by an automated analysis that runs every 24 hours until the end of the quarter at which point they become a historical snapshot</p>
            <?php endif; ?> 
		
		
		
		<?php if(!empty($office_campaign->datajson_status)): ?>
		
            
        <a name="pdl_datajson" class="anchor-point"></a>              

		<div id="datajson-heading" class="panel panel-default">
		<div class="panel-heading">data.json <a type="button" class="btn btn-success btn-xs pull-right hidden" href="<?php echo site_url('datagov/status'); ?>/<?php echo $office->id; ?>">Refresh</a></div>
		
		<table class="table table-striped table-hover dashboard-list">		

		<tr>
			<th id="metrics-datajson-expected-url">               
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_expected_url' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Expected Data.json URL               
            </th>
			<td>
				<?php if(!empty($office_campaign->datajson_status->expected_url)): ?>
					<a href="<?php echo $office_campaign->datajson_status->expected_url ?>"><?php echo $office_campaign->datajson_status->expected_url ?></a>
			        <span style="color:#ccc"> (From <a style="color:#ccc; text-decoration:underline" href="http://www.usa.gov/About/developer-resources/federal-agency-directory/">USA.gov Directory</a>)</span>			
				<?php endif; ?>
				
				<?php 
				
					$http_code = (!empty($office_campaign->expected_datajson_status->http_code)) ? $office_campaign->expected_datajson_status->http_code : 0;
			
					switch ($http_code) {
					    case 404:
					        $status_color = 'danger';
					        break;
					    case 200:
					        $status_color = 'success';
					        break;
					    case 0:
					        $status_color = '';
					        break;					
					    default:
							$status_color = 'warning';
					}	
					
					if(!empty($office_campaign->expected_datajson_status->content_type)) {
						if (strpos($office_campaign->expected_datajson_status->content_type, 'application/json') !== false) {
							$mime_color = 'success';
						} else {
							$mime_color = 'danger';
						}						
					} else {
							$mime_color = 'danger';
					}

				
				?>
				
			

				

			
				
			</td>
		</tr>
							
		<tr>
			<th id="metrics-datajson-resolved-url">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_resolved_url' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Resolved Data.json URL
            </th>
			<td>
				<a href="<?php echo $office_campaign->expected_datajson_status->url ?>"><?php echo $office_campaign->expected_datajson_status->url ?></a>
			</td>
		</tr>	
		
		<tr>
            <th id="metrics-datajson-redirect-count">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_redirects' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Number of Redirects
            </th>            
			<td>
				<?php if(!empty($office_campaign->expected_datajson_status->redirect_count)): ?>
				<span class="text-<?php echo ($office_campaign->expected_datajson_status->redirect_count > 3) ? 'danger' : 'warning'?>">
					<?php echo $office_campaign->expected_datajson_status->redirect_count . ' redirects'; ?>
				</span>				
            		<?php if($office_campaign->expected_datajson_status->redirect_count > 5): ?>			
            		    <span style="color:#ccc"> (stops tracking after 6)</span>
            		<?php endif; ?>
				<?php endif; ?>			
			</td>
		</tr>		
		
		
		<tr class="<?php echo $status_color;?>">
            <th id="metrics-datajson-http-status">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_http_status' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                HTTP Status
            </th>             
			<td>
				<span class="text-<?php echo $status_color;?>">
					<?php echo $office_campaign->expected_datajson_status->http_code?>
				</span>			
			</td>
		</tr>		
	
		<tr class="<?php echo $mime_color;?>">
            <th id="metrics-datajson-mimetype">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_content_type' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Content Type
            </th>             
			<td>
				<span class="text-<?php echo $mime_color;?>">
					<?php echo $office_campaign->expected_datajson_status->content_type?>
				</span>			
			</td>
		</tr>	

        <?php
            $valid_json = (isset($office_campaign->datajson_status->valid_json)) ? $office_campaign->datajson_status->valid_json : null;
            $valid_schema = (isset($office_campaign->datajson_status->valid_schema)) ? $office_campaign->datajson_status->valid_schema : null;

            $error_count        = (isset($office_campaign->datajson_status->error_count) && is_numeric($office_campaign->datajson_status->error_count)) ? $office_campaign->datajson_status->error_count : null;
            $total_records      =  (!empty($office_campaign->datajson_status->total_records)) ? $office_campaign->datajson_status->total_records : '';

            $valid_count        = (is_numeric($error_count) && is_numeric($total_records)) ? $total_records - $error_count : null;
            $valid_count       = ($valid_count < 0) ? 0 : $valid_count;
            
            $percent_valid      = process_percentage($valid_count, $total_records);

        ?>		
		
		
		<tr class="<?php echo ($valid_json == true) ? 'success' : 'danger'?>">
            <th id="metrics-datajson-valid-json">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_valid_json' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Valid JSON
            </th>              
			<td>
			<span id="valid_json" class="text-<?php echo ($valid_json == true) ? 'success' : 'danger'?>">
			<?php		
				if($valid_json == true) echo 'Valid';
				if(($valid_json == false && $valid_json !== null) || ($office_campaign->expected_datajson_status->http_code == 200 && $valid_json != true)) echo 'Invalid <span><a href="http://jsonlint.com/">Check a JSON Validator</a></span>';			
			?>
			</td>
		</tr>	


        <?php if (!empty($office_campaign->datajson_status->schema_version)): ?>

        <tr>
            <th id="metrics-datajson-schema-version">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_schema_version' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Detected Data.json Schema
            </th>
            <td>
                <?php echo $office_campaign->datajson_status->schema_version ?>
            </td>
        </tr>
        <?php endif; ?>

        <?php 

        if ($percent_valid == '100%' && $valid_json == true) {
            $percent_valid_color = 'success';
        } else if ($percent_valid == '100%' && $valid_json !== true) {
            $percent_valid_color = 'warning';
        } else {
            $percent_valid_color = 'danger';
        }

        ?>


        <tr class="<?php echo $percent_valid_color; ?>">
            <th id="metrics-datajson-valid-count">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_valid_count' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Datasets with Valid Metadata
            </th>              
            <td>
                <a name="pdl_valid_metadata" class="anchor-point"></a>

                <?php if(!empty($percent_valid)): ?>
                    <span class="text-<?php echo ($percent_valid == '100%') ? 'success' : 'danger'?>">
                        <?php echo $percent_valid;?> <span id="metrics_valid_count" style="color:#666">(<?php echo $valid_count . ' of ' . $total_records?>)</span>
                        <?php if($valid_json !== true):?>
                           - <span class="text-danger">The <a href="./#valid_json">JSON file is invalid</a> and can't be parsed without special processing</span>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </td>
        </tr>   
     
        <tr class="<?php echo ($valid_schema == true) ? 'success' : 'danger'?>">
            <th id="metrics-datajson-valid-schmea">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_valid_schema' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Valid Schema
            </th>             
            <td>
            <span class="text-<?php echo ($valid_schema == true) ? 'success' : 'danger'?>">
            <?php

            if(empty($valid_count) && $office_campaign->datajson_status->download_content_length > $config['max_remote_size']) {
                echo 'File is too large to validate';
            } else {
                if($valid_schema == true) echo 'Valid';
                if($valid_schema == false && $valid_schema !== null) echo 'Invalid';                            
            }
    
            ?>
            </span>
            </td>

        </tr>   
        
        <?php 
        
            if (!empty($office_campaign->datajson_status->schema_version)) {
                $schema_version = $office_campaign->datajson_status->schema_version;
            } else {
                if (strtotime($milestone->selected_milestone) >= strtotime('2015-02-28')) {
                    $schema_version = 'federal-v1.1';
                } else {
                    $schema_version = 'federal';
                }                
            }

        if(isset($office_campaign->datajson_status->schema_errors)): ?>
       
         <?php

             $validation_url = site_url('validate?schema=' . $schema_version . '&output=browser&datajson_url=') . urlencode($office_campaign->expected_datajson_status->url);
         ?>

        <tr class="info" id="schema_validation_results">
            <td colspan="2">
                <span class="glyphicon glyphicon-download"></span>  
                For more complete and readable validation results, see the full <a href="<?php echo $validation_url?>">schema validator results</a> 
            </td>
        </tr>         


        <tr class="danger">
            <th id="metrics-datajson-schema-errors">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_schema_errors' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Schema Errors
            </th>  
            <td>
            <span>
            <?php
    
                $datajson_errors = (array) $office_campaign->datajson_status->schema_errors;

                $error_count        = (!empty($office_campaign->datajson_status->error_count)) ? $office_campaign->datajson_status->error_count : 0;

                echo 'There are validation errors on ' . $error_count . ' records <br><br>';
                
                if($error_count > 10) {
                    echo 'Only showing errors from the first 10 records: <br><br>';                  
                }
                ?>
             

                <?php foreach ($datajson_errors as $key => $fields) : ?>
                    
                    <strong>Errors on record <?php echo $key ?>: </strong> <br>

                    <?php if(!empty($fields->ALL)): ?>
    
                            <ul class="validation-full-record">
                                <?php foreach ($fields->ALL->errors as $error_description) : ?>
                                    <?php if(strpos($error_description, 'but a null is required')) continue; ?>
                                    <li><?php echo $error_description ?></li>
                                <?php endforeach; ?>
                            </ul>
    
                    <?php 
                        unset($fields->ALL);
                        endif; 
                    ?>
                
    
    
                    <?php
                        foreach ($fields as $field => $details) {
                            echo "<code>$field</code><br>";
    
                            if(!empty($details->errors)) {
                                echo "<ul>";
    
                                foreach($details->errors as $error) {
                                    echo "<li>$error</li>";
                                }
                            
                                echo "</ul>";
    
                            }
                            
                        }
                    ?>

                 <?php endforeach; ?>

            </span>
            </td>

        </tr>   
        <?php endif; ?> 




        
        <tr>
            <th id="metrics_total_records">
                <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_total_records' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Datasets
            </th>             
            <td>      
                <a name="pdl_datasets" class="anchor-point"></a>          
                <?php echo $total_records;?>                
            </td>
        </tr> 


        <?php if(!empty($office_campaign->datajson_status->qa)): ?>


            <?php if(isset($office_campaign->datajson_status->qa->accessURL_present)): ?>
            <tr>
                <th id="metrics_accessURL_present">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_accessURL_present' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    Datasets with Distribution URLs
                </th>                  
                <td>
                    <a name="pdl_downloadable" class="anchor-point"></a>
                    <?php echo process_percentage($office_campaign->datajson_status->qa->accessURL_present, $total_records); ?>
                    <span style="color:#666">(<?php echo $office_campaign->datajson_status->qa->accessURL_present . ' of ' . $total_records; ?>)</span>
                </td>
            </tr> 
            <?php endif; ?>

            <?php if(isset($office_campaign->datajson_status->qa->downloadURL_present)): ?>
            <tr>
                <th id="metrics_downloadURL_present">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_downloadURL_present' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    Datasets with Download URLs
                </th>                  
                <td>
                    <?php echo process_percentage($office_campaign->datajson_status->qa->downloadURL_present, $total_records); ?>
                    <span style="color:#666">(<?php echo $office_campaign->datajson_status->qa->downloadURL_present . ' of ' . $total_records; ?>)</span>
                </td>
            </tr> 
            <?php endif; ?>            

            <?php if(isset($office_campaign->datajson_status->qa->accessURL_total)): ?>
            <tr>
                <th id="metrics_accessURL_total">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_accessURL_total' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    Total Distribution URLs
                </th>                 
                <td id="metrics_accessURL_working">
                    <?php echo $office_campaign->datajson_status->qa->accessURL_total; ?>

                    <?php if(!empty($office_campaign->datajson_status->qa->validation_counts->http_4xx)): ?>
                        <span class="text-danger">(but only <?php echo $office_campaign->datajson_status->qa->validation_counts->http_2xx; ?> accessible)</span>
                    <?php endif; ?>
                </td>
            </tr> 
            <?php endif; ?>            

            <?php if(isset($office_campaign->datajson_status->qa->downloadURL_total)): ?>
            <tr>
                <th id="metrics_downloadURL_total">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_downloadURL_total' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    Total Download URLs
                </th>                 
                <td>
                    <?php echo $office_campaign->datajson_status->qa->downloadURL_total; ?>
                </td>
            </tr> 
            <?php endif; ?>  


            <?php if(isset($office_campaign->datajson_status->qa->API_total)): ?>
            <tr>
                <th id="metrics_API_total">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_API_total' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    Total APIs
                </th>                 
                <td>
                    <?php echo $office_campaign->datajson_status->qa->API_total; ?>
                </td>
            </tr> 
            <?php endif; ?>              

            
            <?php if(isset($office_campaign->datajson_status->qa->validation_counts->http_2xx) && $office_campaign->datajson_status->qa->validation_counts->http_2xx > 0): ?>


                <tr class="info" id="pdl_link_check">
                    <td colspan="2">
                        <p>The fields below serve as quality assurance to verify that the download links included within the metadata are functioning properly</p>

                        <?php if($milestone->selected_milestone == $milestone->current): ?>
                            <?php 
                                $error_log = $office->id . '.csv';
                                $error_path = $config['archive_dir'] . '/error_log/' . $error_log;
                                
                                if(file_exists($error_path)): 
                            ?>
                                                           
                            <span class="glyphicon glyphicon-download"></span> 
                            To see a detailed breakdown of these issues, download the <a href="<?php echo site_url('archive/error_log/' . $error_log)?>">full error log as a CSV</a>
                                
                            <?php endif;?>
                        <?php endif;?>

                    </td>
                </tr>                

                <?php if(isset($office_campaign->datajson_status->qa->validation_counts->http_0)): ?>
                <tr class="<?php echo ($office_campaign->datajson_status->qa->validation_counts->http_0 > 0) ? 'danger' : 'success'?>">
                    <th id="metrics-datajson-download-urls-0">
                        <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_downloadable_0' ?>">
                            <span class="glyphicon glyphicon-info-sign"></span>
                        </a>
                        Server Not Found
                    </th>                 
                    <td>
                       <?php echo process_percentage($office_campaign->datajson_status->qa->validation_counts->http_0, $office_campaign->datajson_status->qa->accessURL_total);?> 
                         
                        <span style="color:#666">
                        <?php echo '(' . $office_campaign->datajson_status->qa->validation_counts->http_0 . ' of ' . $office_campaign->datajson_status->qa->accessURL_total . ')' ?>
                        </span>
                    </td>
                </tr> 
                <?php endif;?>

                <?php if(isset($office_campaign->datajson_status->qa->validation_counts->http_4xx)): ?>
                <tr class="<?php echo ($office_campaign->datajson_status->qa->validation_counts->http_4xx > 0) ? 'danger' : 'success'?>">
                    <th id="metrics-datajson-download-urls-4xx">
                        <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_downloadable_4xx' ?>">
                            <span class="glyphicon glyphicon-info-sign"></span>
                        </a>
                        Broken links (HTTP 4xx)
                    </th>                 
                    <td>
                       <?php echo process_percentage($office_campaign->datajson_status->qa->validation_counts->http_4xx, $office_campaign->datajson_status->qa->accessURL_total);?> 
                         
                        <span style="color:#666">
                        <?php echo '(' . $office_campaign->datajson_status->qa->validation_counts->http_4xx . ' of ' . $office_campaign->datajson_status->qa->accessURL_total . ')' ?>
                        </span>
                    </td>
                </tr> 
                <?php endif;?>

                <?php if(isset($office_campaign->datajson_status->qa->validation_counts->http_5xx)): ?>
                <tr class="<?php echo ($office_campaign->datajson_status->qa->validation_counts->http_5xx > 0) ? 'danger' : 'success'?>">
                    <th id="metrics-datajson-download-urls-5xx">
                        <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_downloadable_5xx' ?>">
                            <span class="glyphicon glyphicon-info-sign"></span>
                        </a>
                        Error Links (HTTP 5xx)
                    </th>                 
                    <td>
                       <?php echo process_percentage($office_campaign->datajson_status->qa->validation_counts->http_5xx, $office_campaign->datajson_status->qa->accessURL_total);?> 
                         
                        <span style="color:#666">
                        <?php echo '(' . $office_campaign->datajson_status->qa->validation_counts->http_5xx . ' of ' . $office_campaign->datajson_status->qa->accessURL_total . ')' ?>
                        </span>
                    </td>
                </tr>  
                <?php endif;?>

                <?php if(isset($office_campaign->datajson_status->qa->validation_counts->http_3xx)): ?>
                <tr class="<?php echo ($office_campaign->datajson_status->qa->validation_counts->http_3xx > 0) ? 'warning' : 'success'?>">
                    <th id="metrics-datajson-download-urls-3xx">
                        <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_downloadable_3xx' ?>">
                            <span class="glyphicon glyphicon-info-sign"></span>
                        </a>
                        Redirected Links (HTTP 3xx)
                    </th>                 
                    <td>
                       <?php echo process_percentage($office_campaign->datajson_status->qa->validation_counts->http_3xx, $office_campaign->datajson_status->qa->accessURL_total);?> 
                         
                        <span style="color:#666">
                        <?php echo '(' . $office_campaign->datajson_status->qa->validation_counts->http_3xx . ' of ' . $office_campaign->datajson_status->qa->accessURL_total . ')' ?>
                        </span>
                    </td>
                </tr>             
                <?php endif;?>

                <?php if(isset($office_campaign->datajson_status->qa->validation_counts->http_2xx)): ?>
                <tr class="<?php echo ($office_campaign->datajson_status->qa->validation_counts->format_mismatch > 0) ? 'danger' : 'success'?>">
                    <th id="metrics_accessURL_format">
                        <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_accessURL_format' ?>">
                            <span class="glyphicon glyphicon-info-sign"></span>
                        </a>
                        Correct format
                    </th>                 
                    <td>

                        <?php 
                        $correct_format_count = $office_campaign->datajson_status->qa->validation_counts->http_2xx - $office_campaign->datajson_status->qa->validation_counts->format_mismatch;
                        echo process_percentage($correct_format_count, $office_campaign->datajson_status->qa->validation_counts->http_2xx); 
                        ?> 
                        <span style="color:#666">
                        <?php echo '(' . $correct_format_count . ' of ' . $office_campaign->datajson_status->qa->validation_counts->http_2xx . ')' ?>
                        </span>
                    </td>
                </tr>             
                <?php endif;?>

                <?php if(isset($office_campaign->datajson_status->qa->validation_counts->pdf)): ?>
                <tr class="<?php echo ($office_campaign->datajson_status->qa->validation_counts->pdf == 0) ? 'success' : '' ?>">
                    <th id="metrics_accessURL_pdf">
                        <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_accessURL_pdf' ?>">
                            <span class="glyphicon glyphicon-info-sign"></span>
                        </a>
                        PDF for raw data
                    </th>                 
                    <td>
                       <?php echo process_percentage($office_campaign->datajson_status->qa->validation_counts->pdf, $office_campaign->datajson_status->qa->validation_counts->http_2xx);?> 
                         
                        <span style="color:#666">
                        <?php echo '(' . $office_campaign->datajson_status->qa->validation_counts->pdf . ' of ' . $office_campaign->datajson_status->qa->validation_counts->http_2xx . ')' ?>
                        </span>
                    </td>
                </tr> 
                <?php endif;?>

                <?php if(isset($office_campaign->datajson_status->qa->validation_counts->html)): ?>
                <tr>
                    <th id="metrics_accessURL_html">
                        <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_accessURL_html' ?>">
                            <span class="glyphicon glyphicon-info-sign"></span>
                        </a>
                        HTML for raw data
                    </th>                 
                    <td>
                       <?php echo process_percentage($office_campaign->datajson_status->qa->validation_counts->html, $office_campaign->datajson_status->qa->validation_counts->http_2xx);?> 
                         
                        <span style="color:#666">
                        <?php echo '(' . $office_campaign->datajson_status->qa->validation_counts->html . ' of ' . $office_campaign->datajson_status->qa->validation_counts->http_2xx . ')' ?>
                        </span>
                    </td>
                </tr>             
                <?php endif;?>

            <?php else: ?>

                <tr class="info" id="pdl_link_check">
                    <td colspan="2">
                        Normally there would be a set of quality assurance fields here to verify that the download links included within the metadata are functioning properly, but the results of those tests are not currently available. 
                    </td>
                </tr>            


            <?php endif; ?>    

            <?php if(isset($office_campaign->datajson_status->qa->bureauCodes)): ?>
            <tr>
                <th id="metrics_bureaus">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_bureaus' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    Bureaus Represented
                </th>                
                <td>
                    <a name="pdl_bureaus" class="anchor-point"></a>
                    <?php echo count($office_campaign->datajson_status->qa->bureauCodes); ?>
                </td>
            </tr> 
            <?php endif; ?>

            <?php if(isset($office_campaign->datajson_status->qa->programCodes)): ?>
            <tr>
                <th id="metrics_programs">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#metrics_programs' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    Programs Represented
                </th>                 
                <td>
                    <a name="pdl_programs" class="anchor-point"></a>
                    <?php echo count($office_campaign->datajson_status->qa->programCodes); ?>
                </td>
            </tr> 
            <?php endif; ?>

            <?php if(isset($office_campaign->datajson_status->qa->license_present)): ?>
            <tr>
                <th id="license_present">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#license_present' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    License Specified
                </th>                 
                <td id="edi_license_present">
                    <?php echo process_percentage($office_campaign->datajson_status->qa->license_present, $total_records); ?>
                    <span style="color:#666">(<?php echo $office_campaign->datajson_status->qa->license_present . ' of ' . $total_records; ?>)</span>                    
                </td>
            </tr> 
            <?php endif; ?>   

            <?php if(isset($office_campaign->datajson_status->qa->redaction_present)): ?>
            <tr>
                <th id="redaction_present">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#redaction_present' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    Datasets with Redactions
                </th>                 
                <td id="edi_redaction_present">
                    <?php echo process_percentage($office_campaign->datajson_status->qa->redaction_present, $total_records); ?>
                    <span style="color:#666">(<?php echo $office_campaign->datajson_status->qa->redaction_present . ' of ' . $total_records; ?>)</span>                    
                </td>
            </tr> 
            <?php endif; ?>   

            <?php if(isset($office_campaign->datajson_status->qa->redaction_no_explanation)): ?>
            <tr>
                <th id="redaction_no_explanation">
                    <a class="info-icon" href="<?php echo site_url('docs') . '#redaction_no_explanation' ?>">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    Redactions without explanation (rights field)
                </th>                 
                <td id="edi_redaction_no_explanation">
                    <?php echo process_percentage($office_campaign->datajson_status->qa->redaction_no_explanation, $total_records); ?>
                    <span style="color:#666">(<?php echo $office_campaign->datajson_status->qa->redaction_no_explanation . ' of ' . $total_records; ?>)</span>                    
                </td>
            </tr> 
            <?php endif; ?>                                 


        <?php endif; ?>


			
        <?php if(isset($office_campaign->expected_datajson_status->download_content_length)): ?>
        <tr>
            <th id="metrics-datajson-file-size">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_file_size' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                File Size
            </th>             
            <td>
                <span>
                    <?php echo human_filesize($office_campaign->expected_datajson_status->download_content_length)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?>		


        <?php if(isset($office_campaign->expected_datajson_status->filetime) && $office_campaign->expected_datajson_status->filetime > 0): ?>
        <tr>
            <th id="metrics-datajson-last-modified">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_last_modified' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Last modified
            </th>
            <td>
                <span>
                    <?php echo date("l, d-M-Y H:i:s T", $office_campaign->expected_datajson_status->filetime)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?> 


        <?php if(isset($office_campaign->expected_datajson_status->last_crawl)): ?>
        <tr>
            <th id="metrics-datajson-last-crawl">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_last_crawl' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Last crawl
            </th>
            <td>
                <span>
                    <?php echo date("l, d-M-Y H:i:s T", $office_campaign->expected_datajson_status->last_crawl)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?>         




        <?php 



            $archive_file = $office->id . '.json';
            $origin_date = $milestone->selected_milestone;
            $archive_path = '/datajson/' . $origin_date . '/' . $archive_file;
            $archive_path_local = $config['archive_dir'] .  $archive_path;
            $schema_version = (!empty($schema_version)) ? $schema_version : 'federal-v1.1';
            
            if(file_exists($archive_path_local)):
                $archive_path_url = site_url('archive' . $archive_path);
                $archive_validation = site_url('validate?schema=' . $schema_version . '&output=browser&qa=true&datajson_url=') . urlencode($archive_path_url );
        ?>


        <tr>
            <th id="metrics-datajson-analyze-archive">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_analyze_archive' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Analyze archive copies
            </th>
            <td>
                <span>
                    <a href="<?php echo $archive_validation ?>">Analyze archive from <?php echo $origin_date; ?></a>
                </span>         
            </td>
        </tr>   
        <?php endif; ?>   





        <?php if(!empty($nearby_crawls)): ?>
        <tr>
            <th id="metrics-datajson-last-crawl">
                <a class="info-icon" href="<?php echo site_url('docs') . '#datajson_last_crawl' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>
                Nearby Daily Crawls
            </th>
            <td>
                <?php foreach ($nearby_crawls as $daily_crawl): 
                        if(!empty($daily_crawl['crawl_start'])):
                ?>     

                        <div> <a href="<?php echo site_url('offices/detail/' . $office->id . '/' . $milestone->selected_milestone . '/status/' . $daily_crawl['status_id']); ?>"><?php echo date("l, d-M-Y H:i:s T", strtotime($daily_crawl['crawl_start'])); ?></a>

                    <?php endif; ?>
                <?php endforeach; ?>
            </td>
        </tr>   
        <?php endif; ?>  


		
		</table>
		</div>
		
		

		<?php if(!empty($office_campaign->datapage_status)): ?>
        <a name="pdl_slashdata" class="anchor-point"></a>

    	<div id="slashdata-heading" class="panel panel-default">
    	<div class="panel-heading">
            /data page 
            <a class="info-icon" href="<?php echo site_url('docs') . '#datapage' ?>">
                <span class="glyphicon glyphicon-info-sign"></span>
            </a>            
        </div>

    	<table class="table table-striped table-hover">		

    	<tr>
    		<th>Expected /data URL</th>
    		<td>
    			<?php if(!empty($office_campaign->datapage_status->expected_url)): ?>
    				<a href="<?php echo $office_campaign->datapage_status->expected_url ?>"><?php echo $office_campaign->datapage_status->expected_url ?></a>
    		        <span style="color:#ccc"> (From <a style="color:#ccc; text-decoration:underline" href="http://www.usa.gov/About/developer-resources/federal-agency-directory/">USA.gov Directory</a>)</span>			
    			<?php endif; ?>

    			<?php 

    				$http_code = (!empty($office_campaign->datapage_status->http_code)) ? $office_campaign->datapage_status->http_code : 0;

    				switch ($http_code) {
    				    case 404:
    				        $status_color = 'danger';
    				        break;
    				    case 200:
    				        $status_color = 'success';
    				        break;
    				    case 0:
    				        $status_color = '';
    				        break;					
    				    default:
    						$status_color = 'warning';
    				}	

    				if(!empty($office_campaign->datapage_status->content_type)) {
    					if (strpos($office_campaign->datapage_status->content_type, 'text/html') !== false) {
    						$mime_color = 'success';
    					} else {
    						$mime_color = 'danger';
    					}						
    				} else {
    						$mime_color = 'danger';
    				}

    			?>

    		</td>
    	</tr>



        <tr>
        	<th>Resolved /data URL</th>
        	<td>
        		<a href="<?php echo $office_campaign->datapage_status->url ?>"><?php echo $office_campaign->datapage_status->url ?></a>
        	</td>
        </tr>	

        <tr>
        	<th>Redirects</th>
        	<td>
        		<?php if(!empty($office_campaign->datapage_status->redirect_count)): ?>
        		<span class="text-<?php echo ($office_campaign->datapage_status->redirect_count > 5) ? 'danger' : 'warning'?>">
        			<?php echo $office_campaign->datapage_status->redirect_count . ' redirects'; ?>
        		</span>	
            		<?php if($office_campaign->datapage_status->redirect_count > 5): ?>			
            		    <span style="color:#ccc"> (stops tracking after 6)</span>
            		<?php endif; ?>			        		
        		<?php endif; ?>			
        	</td>
        </tr>		


        <tr class="<?php echo $status_color;?>">
        	<th>HTTP Status</th>
        	<td>
        		<span class="text-<?php echo $status_color;?>">
        			<?php echo $office_campaign->datapage_status->http_code?>
        		</span>			
        	</td>
        </tr>				

        <tr class="<?php echo $mime_color;?>">
        	<th>Content Type</th>
        	<td>
        		<span class="text-<?php echo $mime_color;?>">
        			<?php echo $office_campaign->datapage_status->content_type?>
        		</span>			
        	</td>
        </tr>	

        <?php if(!empty($office_campaign->datapage_status->filetime) && $office_campaign->datapage_status->filetime > 0): ?>
        <tr>
            <th>Last modified</th>
            <td>
                <span>
                    <?php echo date("l, d-M-Y H:i:s T", $office_campaign->datapage_status->filetime)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?> 




       <?php if(!empty($office_campaign->datapage_status->last_crawl)): ?>
        <tr>
            <th>Last crawl</th>
            <td>
                <span>
                    <?php echo date("l, d-M-Y H:i:s T", $office_campaign->datapage_status->last_crawl)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?>          


        </table>
        </div>
        <?php endif; ?>	
                
                
                
		<?php if(!empty($office_campaign->digitalstrategy_status)): ?>



    	<div id="digitalstrategy-heading" class="panel panel-default">
    	<div class="panel-heading">
            /digitalstrategy.json 
            <a class="info-icon" href="<?php echo site_url('docs') . '#digitalstrategy' ?>">
                <span class="glyphicon glyphicon-info-sign"></span>
            </a>             
        </div>

    	<table class="table table-striped table-hover">		

    	<tr>
    		<th>Expected /digitalstrategy.json URL</th>
    		<td>
    			<?php if(!empty($office_campaign->digitalstrategy_status->expected_url)): ?>
    				<a href="<?php echo $office_campaign->digitalstrategy_status->expected_url ?>"><?php echo $office_campaign->digitalstrategy_status->expected_url ?></a>
    		        <span style="color:#ccc"> (From <a style="color:#ccc; text-decoration:underline" href="http://www.usa.gov/About/developer-resources/federal-agency-directory/">USA.gov Directory</a>)</span>			
    			<?php endif; ?>

    			<?php 

    				$http_code = (!empty($office_campaign->digitalstrategy_status->http_code)) ? $office_campaign->digitalstrategy_status->http_code : 0;

    				switch ($http_code) {
    				    case 404:
    				        $status_color = 'danger';
    				        break;
    				    case 200:
    				        $status_color = 'success';
    				        break;
    				    case 0:
    				        $status_color = '';
    				        break;					
    				    default:
    						$status_color = 'warning';
    				}	

    				if(!empty($office_campaign->digitalstrategy_status->content_type)) {
    					if (strpos($office_campaign->digitalstrategy_status->content_type, 'application/json') !== false) {
    						$mime_color = 'success';
    					} else {
    						$mime_color = 'danger';
    					}						
    				} else {
    						$mime_color = 'danger';
    				}

    			?>

    		</td>
    	</tr>



        <tr>
        	<th>Resolved /digitalstrategy.json URL</th>
        	<td>
        		<a href="<?php echo $office_campaign->digitalstrategy_status->url ?>"><?php echo $office_campaign->digitalstrategy_status->url ?></a>
        	</td>
        </tr>	

        <tr>
        	<th>Redirects</th>
        	<td>
        		<?php if(!empty($office_campaign->digitalstrategy_status->redirect_count)): ?>
        		<span class="text-<?php echo ($office_campaign->digitalstrategy_status->redirect_count > 5) ? 'danger' : 'warning'?>">
        			<?php echo $office_campaign->digitalstrategy_status->redirect_count . ' redirects'; ?>
        		</span>	
            		<?php if($office_campaign->digitalstrategy_status->redirect_count > 5): ?>			
            		    <span style="color:#ccc"> (stops tracking after 6)</span>
            		<?php endif; ?>			        		
        		<?php endif; ?>			
        	</td>
        </tr>		


        <tr class="<?php echo $status_color;?>">
        	<th>HTTP Status</th>
        	<td>
        		<span class="text-<?php echo $status_color;?>">
        			<?php echo $office_campaign->digitalstrategy_status->http_code?>
        		</span>			
        	</td>
        </tr>				

        <tr class="<?php echo $mime_color;?>">
        	<th>Content Type</th>
        	<td>
        		<span class="text-<?php echo $mime_color;?>">
        			<?php echo $office_campaign->digitalstrategy_status->content_type?>
        		</span>			
        	</td>
        </tr>	
        
        <?php if($http_code == 200 && $digital_strategy = curl_from_json($office_campaign->digitalstrategy_status->url, false, true)) {
                $valid_json = true;
              } else {
                $valid_json = false;
              }
        ?>
        
        
        
		<tr class="<?php echo ($valid_json == true) ? 'success' : 'danger'?>">
			<th>Valid JSON</th>
			<td>
			<span class="text-<?php echo ($valid_json == true) ? 'success' : 'danger'?>">
			<?php		
				if($valid_json == true) echo 'Valid';
				if(($valid_json == false && $valid_json !== null) || ($office_campaign->digitalstrategy_status->http_code == 200 && $valid_json != true)) echo 'Invalid <span><a href="http://jsonlint.com/">Check a JSON Validator</a></span>';			
			?>
			</td>
		</tr>        	

        <?php if(!empty($office_campaign->digitalstrategy_status->filetime) && $office_campaign->digitalstrategy_status->filetime > 0): ?>
        <tr>
            <th>Last modified</th>
            <td>
                <span>
                    <?php echo date("l, d-M-Y H:i:s T", $office_campaign->digitalstrategy_status->filetime)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?> 


       <?php if(!empty($office_campaign->digitalstrategy_status->last_crawl)): ?>
        <tr>
            <th>Last crawl</th>
            <td>
                <span>
                    <?php echo date("l, d-M-Y H:i:s T", $office_campaign->digitalstrategy_status->last_crawl)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?>  


        </table>
        </div>
        <?php endif; ?>
                 
                
         <?php if($valid_json == true && !empty($digital_strategy)): ?>

     	    <div class="panel panel-default">
     	    <div class="panel-heading">
                Digital Strategy 
                <a class="info-icon" href="<?php echo site_url('docs') . '#digitalstrategy_excerpts' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>                  
            </div>
     	    <div style="padding : 1em;">
            <?php 
                $sections = array(  "1.2.4" => "edi_schedule_delivered", 
                                    "1.2.5" => "schedule", 
                                    "1.2.6" => "pe_feedback_specified", 
                                    "1.2.7" => "ps_publication_process");
        


                if (!empty($digital_strategy->generated)) {
                    if ($published_date = strtotime($digital_strategy->generated))  {
                        $published_date = date("l, d-M-Y H:i:s T", $published_date);
                        echo '<h2><span style="color:#666">Date specified: </span>' . "$published_date</h2>";



                        

                    }
                }

                if(!empty($office_campaign->digitalstrategy_status->filetime) && $office_campaign->digitalstrategy_status->filetime > 0) {
                    echo 'Date of digitalstrategy.json file: ' . date("l, d-M-Y H:i:s T", $office_campaign->digitalstrategy_status->filetime);
                }

                echo "<hr>";
                
            ?>

            <?php
                if(!empty($digital_strategy->items)) {

                    foreach ($digital_strategy->items as $item) {
                        if (!empty($sections[$item->id])) {

                            echo "<a name=\"{$sections[$item->id]}\" class=\"anchor-point\"></a>";
                            echo "<h3>{$item->id} {$item->text}</h3>";
                            
                            if($item->multiple === false) {
                                echo "<h4>{$item->fields[0]->label}</h4>";
                                echo '<br>';
                                echo '<pre style="white-space: pre-wrap; word-break: keep-all; ">' . $item->fields[0]->value . '</pre>';                           
                            } else {
                                
                                $columns = count($item->fields);
                                $rows   = count($item->fields[0]->value);
                                
                                

                                for ($row=0; $row < $rows; $row++) {
                                    
                                    echo '<table class="table table-striped table-hover" style="margin-bottom : 4em; border-bottom : 3px solid #ccc">';
                                    
                                    for ($column=0; $column< $columns; $column++) {
                                        echo '<tr>';
                                        echo '<th class="col-sm-2 col-md-2 col-lg-2">' . "{$item->fields[$column]->label}</th>";
                                        
                                        echo '<td class="col-sm-10 col-md-10 col-lg-10">';
                                        if(!empty($item->fields[$column]->value[$row])) {
                                            echo $item->fields[$column]->value[$row];
                                        }
                                        echo "</td>";
                                        
                                        echo "</tr>";                                    
                                    }
                                    
                                    echo '</table>';                                 

                                }
                                
                            }
                            
                            echo '<hr>';
                                                
                        }
                    }


                }
             ?>

             </div>
             </div>
 		
 		<?php endif;?>       
                
                

        <?php endif; ?>	





		
		
		
		
		
		



		
		<?php endif; ?>


		<?php
				
		if(!empty($child_offices)) {
			status_table('Sub Agencies', $child_offices); 	
		}
					
		?>

	
-->


      </div>

      <hr>

<?php include 'footer.php'; ?>