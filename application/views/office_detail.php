<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

<?php include 'office_table_inc_view.php';?>




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
			
			
            <p>
                See the <a href="/docs">documentation</a> for an explanation of this table.
            </p>			
		
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
							
		?>
		
		
		
		
		<?php if(!empty($office->url)): ?>
		
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
			<th>Declared Data.json URL</th>
			<td><?php echo $office_campaign->datajson_url ?></td>
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
			//var_dump($valid_schema);
				if($valid_schema == true) echo 'Valid';
				if($valid_schema == false && $valid_schema !== null) echo 'Invalid';							
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
			    $datajson_errors = $office_campaign->datajson_status->schema_errors;
			    
			    if(count($datajson_errors) < 50) {
			        echo 'There are ' . count($datajson_errors) . ' validation errors: <br><br>';
			    } else {
			        echo 'Only showing first 50 validation errors: <br><br>';			        
			    }
			    
			    foreach ($datajson_errors as $error) {
                    if (is_string($error)){
                        echo $error;
                    } else {
                        echo sprintf("[%s] %s\n", $error->property, $error->message);    
                    }
                    
                    echo "<br>";
			    }
    			    
			?>
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

		<tr>
			<th>Data.json Notes</th>
			<td><?php echo '' ?></td>
		</tr>		
		
		</table>
		</div>
		
		

		<?php if(!empty($office_campaign->datapage_status)): ?>

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
                $sections = array("1.2.4", "1.2.5", "1.2.6", "1.2.7");
            
                foreach ($digital_strategy->items as $item) {
                    if (in_array($item->id, $sections)) {
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





		
		
		
		
		
		
		
		
		<div class="panel panel-default">
		<div class="panel-heading">Project Open Data</div>

		<table class="table table-striped table-hover">
		<tr>
			<th>Posted an Enterprise Data Inventory Schedule</th>
			<td><?php  ?></td>
		</tr>
		<tr>
			<th>Created an Enterprise Data Inventory</th>
			<td><?php  ?></td>
		</tr>
		<tr>
			<th>Developed a Public Data Listing (machine readable)</th>
			<td><?php  ?></td>
		</tr>
		<tr>
			<th>Developed a Public Data Listing (human readable)</th>
			<td><?php  ?></td>
		</tr>
		<tr>
			<th>Developed a Customer Feedback Process </th>
			<td><?php  ?></td>
		</tr>								
		<tr>
			<th>Described the Data Publication Process</th>
			<td><?php  ?></td>
		</tr>		
		<tr>
			<th>Identified agency Point of Contact</th>
			<td><?php  ?></td>
		</tr>					
		</table>
		</div>
		
		
		
		<!--
		<div class="panel panel-default">
		<div class="panel-heading">Data.gov Support</div>
		
		<table class="table table-striped table-hover">
		<tr>
			<th>Contact Name</th>
			<td><?php echo $office_campaign->contact_name ?></td>
		</tr>	

		<tr>
			<th>Contact Email</th>
			<td><?php echo $office_campaign->contact_email ?></td>
		</tr>

		<tr>
			<th>Feedback Mechanism</th>
			<td><?php echo $office_campaign->feedback_mechanism ?></td>
		</tr>

		<tr>
			<th>Catalog View</th>
			<td><?php echo $office_campaign->catalog_view ?></td>
		</tr>								

		<tr>
			<th>Community Plan</th>
			<td><?php echo $office_campaign->community_plan ?></td>
		</tr>

		<tr>
			<th>Central Inventory</th>
			<td><?php echo $office_campaign->central_inventory ?></td>
		</tr>				

		<tr>
			<th>Inventory Plan</th>
			<td><?php echo $office_campaign->inventory_plan ?></td>
		</tr>	

		</table>		
		</div>
		-->


		
		<?php endif; ?>


		<?php
				
		if(!empty($child_offices)) {
			status_table('Sub Agencies', $child_offices); 	
		}
					
		?>

	


      </div>

      <hr>

<?php include 'footer.php'; ?>