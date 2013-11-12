<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

<?php include 'office_table_inc_view.php';?>




    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div>
		
          <h2><?php echo $office->name ?></h2>

			<div><a href="<?php echo $office->url ?>"><?php echo $office->url ?></a></div>
			<div><?php echo $office->notes ?></div>				
		
		
			<?php if(!empty($office->parent_office_id)): ?>
				<div><a href="<?php echo $office->parent_office_id ?>">Parent Office</a></div>				
			<?php endif; ?>
		
        </div>



		<?php if(!empty($office_campaign)): ?>
		
		<?php 		
			if(!empty($office_campaign->datajson_status)) {
				$office_campaign->datajson_status = json_decode($office_campaign->datajson_status);			
			}
		?>
		
		
		
		
		<?php if(!empty($office_campaign->datajson_status->expected_datajson_url)): ?>
		
		<div class="panel panel-default">
		<div class="panel-heading">data.json <a type="button" class="btn btn-success btn-xs pull-right" href="?refresh=true">Refresh</a></div>
		
		<table class="table table-striped table-hover">		

		<tr>
			<th>Expected Data.json URL</th>
			<td>
				<?php if(!empty($office_campaign->datajson_status->expected_datajson_url)): ?>
					<a href="<?php echo $office_campaign->datajson_status->expected_datajson_url ?>"><?php echo $office_campaign->datajson_status->expected_datajson_url ?></a>
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

					
					if (empty($office_campaign->datajson_status->valid_json)) $office_campaign->datajson_status->valid_json = false;					
					if (empty($office_campaign->datajson_status->valid_schema)) $office_campaign->datajson_status->valid_schema = false;
								
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
				<span class="text-warning">
					<?php echo $office_campaign->expected_datajson_status->redirect_count . ' redirects'; ?>
				</span>				
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
		
		<tr class="<?php echo ($office_campaign->datajson_status->valid_json == true) ? 'success' : 'danger'?>">
			<th>Valid JSON</th>
			<td>
			<?php
				$valid_json = (!empty($office_campaign->datajson_status->valid_json)) ? $office_campaign->datajson_status->valid_json : null;
			?>
			<span class="text-<?php echo ($office_campaign->datajson_status->valid_json == true) ? 'success' : 'danger'?>">
			<?php		
				if($valid_json == true) echo 'Valid';
				if($valid_json === false || ($office_campaign->expected_datajson_status->http_code == 200 && $valid_json != true)) echo 'Invalid';			
			?>
			</td>
		</tr>		
		
		<tr class="<?php echo ($office_campaign->datajson_status->valid_schema == true) ? 'success' : 'danger'?>">
			<th>Valid Schema</th>
			<td>
			<?php
				
				$valid_schema = (!empty($office_campaign->datajson_status->valid_schema)) ? $office_campaign->datajson_status->valid_schema : null;
			
			?>
			<span class="text-<?php echo ($office_campaign->datajson_status->valid_schema == true) ? 'success' : 'danger'?>">
			<?php
				if($valid_schema == true) echo 'Valid';
				if($valid_schema === false) echo 'Invalid';							
			?>
			</span>
			</td>
			
			
			
		</tr>		
		
		<tr>
			<th>Data.json Notes</th>
			<td><?php echo '' ?></td>
		</tr>		
		
		</table>
		</div>
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