<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

<?php include 'office_table_inc_view.php';?>

<?php $permission_level = 'admin' ?>


    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div>
		
          <h2><?php echo $office->name ?></h2>

            <div style="margin-bottom : 1em">
    			<div><a href="<?php echo $office->url ?>"><?php echo $office->url ?></a></div>
    			<div><?php echo $office->notes ?></div>				
		
		
    			<?php if(!empty($office->parent_office_id)): ?>
    				<div><a href="<?php echo $office->parent_office_id ?>">Parent Office</a></div>				
    			<?php endif; ?>
			</div>
	
		
        </div>

        <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
            <form method="post" action="/datagov/status-update" role="form">
        <?php endif; ?>        
        <div class="panel panel-default">

            

            <div class="panel-heading">Status 
                <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                    <button type="submit" class="btn btn-success btn-xs pull-right" href="/datagov/status/<?php echo $office->id; ?>">Update</button> <button class="btn btn-default btn-xs pull-right" style="margin-right : 1em" id="accShow">Show All Notes</button>
                <?php endif; ?>
            </div>
     

            <table class="table table-striped table-hover" id="note-expander-parent">

            <!--
                <tr>
                    <th>Contact Name</th>
                    <td><?php echo $office_campaign->contact_name ?></td>
                </tr>   

                <tr>
                    <th>Contact Email</th>
                    <td><?php echo $office_campaign->contact_email ?></td>
                </tr>
            -->


                <?php 

                $status_fields = array(
                'datagov_harvest' => "Data.gov Harvest", 
                'inventory_posted' => "EDI Posted", 
                'inventory_superset' => "EDI is a superset of PDL", 
                'datajson_posted' => "PDL data.json", 
                'datajson_slashdata' => "PDL /data", 
                'feedback' => "Feedback Mechanism", 
                'schedule_posted' => "Schedule", 
                'publication_process_posted' => "Data Publication Process" 
                );

                $crawl_details = array('datajson_posted', 'datajson_slashdata', 'feedback', 'schedule_posted', 'publication_process_posted');

                ?>

                <?php foreach ($status_fields as $status_field_name => $status_field_label) : ?>

                    <tr>
                        <td class="col-md-1"><?php echo $office_campaign->$status_field_name ?></td>

                        <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                            <td class="col-md-2">
                                <select name="<?php echo $status_field_name ?>">
                                    <option value="" disabled <?php echo (empty($office_campaign->$status_field_name)) ? 'selected = "selected"' : '' ?>>Select Status</option>                                
                                    <option <?php echo ($office_campaign->$status_field_name == "yes") ? 'selected = "selected"' : '' ?> value="yes">Yes</option>
                                    <option <?php echo ($office_campaign->$status_field_name == "no") ? 'selected = "selected"' : '' ?> value="no">No</option>
                                    <option <?php echo ($office_campaign->$status_field_name == "partially") ? 'selected = "selected"' : '' ?> value="partially">Partially</option>
                                    <option <?php echo ($office_campaign->$status_field_name == "other") ? 'selected = "selected"' : '' ?> value="other">Other</option>
                                </select>
                            </td>
                        <?php endif; ?>
                        
                        <td><strong><?php echo $status_field_label ?></strong></td>                        
                        <td>
                            <?php if (array_search($status_field_name, $crawl_details) !== false):?> 

                                <a href="#<?php echo $status_field_name ?>">Crawl details</a>

                            <?php endif; ?>
                        </td>     
                        <td>
                            <a class="btn btn-xs btn-default collapsed pull-right" href="#note-expander-<?php echo $status_field_name ?>" data-parent="note-expander-parent" data-toggle="collapse">
                                Notes
                            </a>
                        </td>     
                    </tr>
                    <tr>
                        <td colspan="5" class="hidden-row">
                            <div class="edit-toggle collapse container form-group" id="note-expander-<?php echo $status_field_name ?>">
                                
                                <?php 
                                    $note_field = "note_$status_field_name";
                                    $note_data = (!empty($notes[$note_field])) ? $notes[$note_field] : '';
                                ?>    

                                <div class="edit-area"><?php echo $note_data; ?></div>
                                <div class="edit-raw hidden" data-fieldname="note_<?php echo $status_field_name ?>"><?php echo $note_data; ?></div>
                                
                                <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                    <button class="btn btn-primary edit-button pull-right" type="button">Edit</button>                                
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>




                <?php endforeach; ?>

            </table>   

        </div>

        <input type="hidden" name="office_id" value="<?php echo $office->id; ?>">


        <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
            </form>
        <?php endif; ?>        

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
							
		?>
		
		
		
		
		<?php if(!empty($office->url)): ?>
		
            
        <a name="datajson_posted" class="anchor-point"></a>
        <p>
            See the <a href="/docs">documentation</a> for an explanation of this table.
        </p>    

		<div class="panel panel-default">
		<div class="panel-heading">data.json <a type="button" class="btn btn-success btn-xs pull-right hidden" href="/datagov/status/<?php echo $office->id; ?>">Refresh</a></div>
		
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
        
		<tr class="<?php echo ($valid_schema == true) ? 'success' : 'danger'?>">
			<th>Valid Schema</th>
			<td>
			<span class="text-<?php echo ($valid_schema == true) ? 'success' : 'danger'?>">
			<?php
			//var_dump($office_campaign->datajson_status); exit;

            if($office_campaign->datajson_status->download_content_length > $config['max_size']) {
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
                $validation_url = '/validate?schema=&output=browser&datajson_url=' . urlencode($office_campaign->expected_datajson_status->url);

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


        <?php if(!empty($office_campaign->expected_datajson_status->filetime)): ?>
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



		<tr>
			<th>Data.json Notes</th>
			<td><?php echo '' ?></td>
		</tr>		
		
		</table>
		</div>
		
		

		<?php if(!empty($office_campaign->datapage_status)): ?>
        <a name="datajson_slashdata" class="anchor-point"></a>

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

        </table>
        </div>
        <?php endif; ?>
                 
                
         <?php if($valid_json == true && !empty($digital_strategy)): ?>

     	    <div class="panel panel-default">
     	    <div class="panel-heading">Digital Strategy</div>
     	    <div style="padding : 1em;">
            <?php 
                $sections = array(  "1.2.4" => "schedule_posted", 
                                    "1.2.5" => "schedule", 
                                    "1.2.6" => "feedback", 
                                    "1.2.7" => "publication_process_posted");
        


                foreach ($digital_strategy->items as $item) {
                    if (!empty($sections[$item->id])) {

                        echo "<a name=\"{$sections[$item->id]}\" class=\"anchor-point\"></a>";
                        echo "<h3>{$item->id} {$item->text}</h3>";
                        
                        if($item->multiple === false) {
                            echo "<h4>{$item->fields[0]->label}</h4>";
                            echo '<br>';
                            echo $item->fields[0]->value ;                           
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