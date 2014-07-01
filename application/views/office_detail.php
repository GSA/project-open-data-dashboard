<?php $page_title = $office->name;?>

<?php include 'header_meta_inc_view.php';?>

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


              <h2><?php echo $office->name ?> - <?php echo $milestone->milestones[$milestone->selected_milestone];?></h2>

        
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
                                    'pe_feedback_specified', 
                                    'edi_schedule_delivered', 
                                    'ps_publication_process',
                                    'pdl_downloadable'
                                    );


            $active_section = (!empty($selected_category)) ? $selected_category : 'pdl';  


        ?>



            <?php if ($this->session->userdata('permissions') == $permission_level): ?>

                <?php if(!empty($office_campaign->contact_email)): ?>
                    <div>Contact: <a href="mailto:<?php echo $office_campaign->contact_email; ?>"><?php echo $office_campaign->contact_email; ?></a></div>
                <?php else:?>
                    <div class="bg-danger">No Data Lead listed!</div>
                <?php endif;?>

            <?php endif;?>
                


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
                            <li><a href="<?php echo site_url();?>offices/detail/<?php echo $office->id; ?>/<?php echo $milestone_date;?>"><?php echo $milestone_name . ' - ' . date("F jS Y", strtotime($milestone_date));?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>



            <a name="leading_indicators" class="anchor-point"></a>
            <h3>Leading Indicators <a class="info-icon" href="<?php echo site_url('docs'); ?>#leading_indicators"><span class="glyphicon glyphicon-info-sign"></span></a></h3>
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




            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                <div  class="pull-right" style="margin : 1em 0;">                                
                    <button class="btn btn-default btn-xs" id="accShow">Show All Notes</button>
                    <button type="submit" class="btn btn-success btn-xs">Update</button> 
                </div>  
            <?php endif;?>


            <!-- Nav tabs -->
            <ul class="nav nav-tabs tracker-sections">
                
                <?php foreach ($section_breakdown as $section_abbreviation => $section_title): ?>
                    <li  <?php if($section_abbreviation == $active_section) echo 'class="active"'; ?>><a name="<?php echo $section_abbreviation . '_tab';?>" href="#<?php echo $section_abbreviation;?>" data-toggle="tab"><?php echo $section_title;?></a></li>
                <?php endforeach; reset($section_breakdown);  ?>
            </ul>

                <!-- Tab panes -->
                <div class="tab-content tracker-content">
                  
                  <?php foreach ($section_breakdown as $section_abbreviation => $section_title): ?>
                        <div class="tab-pane <?php if($section_abbreviation == $active_section) echo 'active'; ?>" id="<?php echo $section_abbreviation;?>">


                            
                           <table class="table table-striped table-hover" id="note-expander-parent">

                                <tr class="table-header">
                                    <th>Status</th>

                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                        <th></th>
                                    <?php endif; ?> 

                                    <th>Indicator</th>
                                    <th>Automated Metrics</th>

                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                        <th></th>
                                    <?php endif; ?>   

                                </th>


                                <?php foreach ($tracker_model as $tracker_field_name => $tracker_field_meta) : ?>

                                    <?php

                                        // Skip this field if it's not part of current section
                                        if (substr($tracker_field_name, 0, strlen($section_abbreviation)) !== $section_abbreviation) continue;                        
                                        
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

                                    <tr <?php //if(!empty($status_class)) echo "class=\"$status_class\""; ?>>
                                        <td class="col-md-1">
                                            <?php 
                                                if (!empty($status_icon) && ($tracker_field_meta->type == "select" || $tracker_field_meta->type == "traffic")) {
                                                    echo $status_icon;     
                                                } else {
                                                    echo $office_campaign->tracker_fields->$tracker_field_name;
                                                }                       
                                            ?>
                                        </td>

                                        <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                            <td class="col-md-2">

                                                <?php if ($tracker_field_meta->type == "select") : ?>
                                                    <select name="<?php echo $tracker_field_name ?>">
                                                        <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Status</option>                                
                                                        <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "yes") ? 'selected = "selected"' : '' ?> value="yes">Yes</option>
                                                        <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "no") ? 'selected = "selected"' : '' ?> value="no">No</option>
                                                        <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "partially") ? 'selected = "selected"' : '' ?> value="partially">Partially</option>
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


                                                <?php if ($tracker_field_meta->type == "string") : ?>
                                                    <input type="text" name="<?php echo $tracker_field_name ?>" value="<?php echo $office_campaign->tracker_fields->$tracker_field_name;?>">
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        
                                        <td class="tracker-field">                                            
                                            <a name="tracker_<?php echo $tracker_field_name ?>" class="anchor-point"></a>
                                            <strong>
                                                <a href="<?php echo site_url('docs') . '#' . $tracker_field_name ?>">
                                                    <span class="glyphicon glyphicon-info-sign"></span>
                                                </a>
                                                    <?php echo $tracker_field_meta->label ?>
                                            </strong>
                                        </td>                        
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
                                    </tr>

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
                    <input type="hidden" name="milestone" value="<?php echo $milestone->selected_milestone; ?>">                       
                </form>
            <?php endif; ?>   



<!-- ################################################################################ -->



            
            <a name="automated_metrics" class="anchor-point"></a>
            <h3>Automated Metrics <a class="info-icon" href="<?php echo site_url('docs'); ?>#automated_metrics"><span class="glyphicon glyphicon-info-sign"></span></a></h3>


		     <?php if(empty($office_campaign->datajson_status) && empty($office_campaign->datapage_status) && empty($office_campaign->digitalstrategy_status)): ?>
                <p>No automated metrics are currently available for this milestone</p>
             <?php else: ?> 
                <p>These metrics are generated by an automated analysis that runs every 24 hours</p>
            <?php endif; ?> 
		
		
		
		<?php if(!empty($office_campaign->datajson_status)): ?>
		
            
        <a name="pdl_datajson" class="anchor-point"></a>              

		<div class="panel panel-default">
		<div class="panel-heading">data.json <a type="button" class="btn btn-success btn-xs pull-right hidden" href="<?php echo site_url('datagov/status'); ?>/<?php echo $office->id; ?>">Refresh</a></div>
		
		<table class="table table-striped table-hover">		

		<tr>
			<th>Expected Data.json URL</th>
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
			<th>Resolved Data.json URL</th>
			<td>
				<a href="<?php echo $office_campaign->expected_datajson_status->url ?>"><?php echo $office_campaign->expected_datajson_status->url ?></a>
			</td>
		</tr>	
		
		<tr>
			<th>Redirects</th>
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
			<th>HTTP Status</th>
			<td>
				<span class="text-<?php echo $status_color;?>">
					<?php echo $office_campaign->expected_datajson_status->http_code?>
				</span>			
			</td>
		</tr>		
	
		<tr class="<?php echo $mime_color;?>">
			<th>Content Type</th>
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
            
            $percent_valid      = process_percentage($valid_count, $total_records);

        ?>		
		
		
		<tr class="<?php echo ($valid_json == true) ? 'success' : 'danger'?>">
			<th>Valid JSON</th>
			<td>
			<span class="text-<?php echo ($valid_json == true) ? 'success' : 'danger'?>">
			<?php		
				if($valid_json == true) echo 'Valid';
				if(($valid_json == false && $valid_json !== null) || ($office_campaign->expected_datajson_status->http_code == 200 && $valid_json != true)) echo 'Invalid <span><a href="http://jsonlint.com/">Check a JSON Validator</a></span>';			
			?>
			</td>
		</tr>	

        
        <tr>
            <th>Datasets</th>
            <td>      
                <a name="pdl_datasets" class="anchor-point"></a>          
                <?php echo $total_records;?>                
            </td>
        </tr> 


        <?php if(!empty($office_campaign->datajson_status->qa)): ?>


            <?php if(!empty($office_campaign->datajson_status->qa->accessURL_present)): ?>
            <tr>
                <th>Datasets with Downloadable URLs (accessURL)</th>
                <td>
                    <a name="pdl_downloadable" class="anchor-point"></a>
                    <?php echo process_percentage($office_campaign->datajson_status->qa->accessURL_present, $total_records); ?>
                    <span style="color:#666">(<?php echo $office_campaign->datajson_status->qa->accessURL_present . ' of ' . $total_records; ?>)</span>
                </td>
            </tr> 
            <?php endif; ?>

            <?php if(!empty($office_campaign->datajson_status->qa->accessURL_total)): ?>
            <tr>
                <th>Total Downloadable URLs (accessURL)</th>
                <td>
                    <?php echo $office_campaign->datajson_status->qa->accessURL_total; ?>
                </td>
            </tr> 
            <?php endif; ?>            


            <?php if(!empty($office_campaign->datajson_status->qa->bureauCodes)): ?>
            <tr>
                <th>Bureaus Represented</th>
                <td>
                    <a name="pdl_bureaus" class="anchor-point"></a>
                    <?php echo count($office_campaign->datajson_status->qa->bureauCodes); ?>
                </td>
            </tr> 
            <?php endif; ?>

            <?php if(!empty($office_campaign->datajson_status->qa->programCodes)): ?>
            <tr>
                <th>Programs Represented</th>
                <td>
                    <a name="pdl_programs" class="anchor-point"></a>
                    <?php echo count($office_campaign->datajson_status->qa->programCodes); ?>
                </td>
            </tr> 
            <?php endif; ?>


        <?php endif; ?>


        <tr class="<?php echo ($percent_valid == '100%') ? 'success' : 'danger'?>">
            <th>Datasets with Valid Metadata</th>
            <td>
                <a name="pdl_valid_metadata" class="anchor-point"></a>

                <?php if(!empty($percent_valid)): ?>
                    <span class="text-<?php echo ($percent_valid == '100%') ? 'success' : 'danger'?>">
                        <?php echo $percent_valid;?> <span style="color:#666">(<?php echo $valid_count . ' of ' . $total_records?>)</span>
                    </span>
                <?php endif; ?>
            </td>
        </tr>   
     
		<tr class="<?php echo ($valid_schema == true) ? 'success' : 'danger'?>">
			<th>Valid Schema</th>
			<td>
			<span class="text-<?php echo ($valid_schema == true) ? 'success' : 'danger'?>">
			<?php
			//var_dump($office_campaign->datajson_status); exit;

            if($office_campaign->datajson_status->download_content_length > $config['max_remote_size']) {
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
		
	
		if(isset($office_campaign->datajson_status->schema_errors)): ?>
		
		<tr class="danger">
			<th>Schema Errors</th>
			<td>
			<span>
			<?php
                $validation_url = site_url('validate?schema=federal&output=browser&datajson_url=') . urlencode($office_campaign->expected_datajson_status->url);

                echo "<p><strong>For more readable validation results, see the <a href=\"$validation_url\">validator</a></strong></p>\n";
	
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
			
        <?php if(!empty($office_campaign->expected_datajson_status->download_content_length)): ?>
        <tr>
            <th>File Size</th>
            <td>
                <span>
                    <?php echo human_filesize($office_campaign->expected_datajson_status->download_content_length)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?>		


        <?php if(!empty($office_campaign->expected_datajson_status->filetime) && $office_campaign->expected_datajson_status->filetime > 0): ?>
        <tr>
            <th>Last modified</th>
            <td>
                <span>
                    <?php echo date("l, d-M-Y H:i:s T", $office_campaign->expected_datajson_status->filetime)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?> 


        <?php if(!empty($office_campaign->expected_datajson_status->last_crawl)): ?>
        <tr>
            <th>Last crawl</th>
            <td>
                <span>
                    <?php echo date("l, d-M-Y H:i:s T", $office_campaign->expected_datajson_status->last_crawl)?>
                </span>         
            </td>
        </tr>   
        <?php endif; ?>         

		
		</table>
		</div>
		
		

		<?php if(!empty($office_campaign->datapage_status)): ?>
        <a name="pdl_slashdata" class="anchor-point"></a>

    	<div class="panel panel-default">
    	<div class="panel-heading">/data page</div>

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



    	<div class="panel panel-default">
    	<div class="panel-heading">/digitalstrategy.json</div>

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
     	    <div class="panel-heading">Digital Strategy</div>
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

	


      </div>

      <hr>

<?php include 'footer.php'; ?>