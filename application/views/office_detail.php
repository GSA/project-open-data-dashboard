<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>


    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-lg-4">
		
          <h2><?php echo $office->name ?></h2>

			<div><a href="<?php echo $office->url ?>"><?php echo $office->url ?></a></div>
			<div><?php echo $office->notes ?></div>				
		
		
			<?php if(!empty($office->parent_office_id)): ?>
				<div><a href="<?php echo $office->parent_office_id ?>">Parent Office</a></div>				
			<?php endif; ?>
		
        </div>



		<?php if(!empty($office_campaign)): ?>
		
		<div class="panel panel-default">
		<div class="panel-heading">Data.gov Status</div>
		
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
			<th>Expected Data.json URL</th>
			<td>
				<a href="<?php echo $office_campaign->expected_datajson_url ?>"><?php echo $office_campaign->expected_datajson_url ?></a>

				<?php 
					switch ($office_campaign->expected_datajson_status['http_code']) {
					    case 404:
					        $status_color = 'red';
					        break;
					    case 200:
					        $status_color = 'green';
					        break;
					    default:
							$status_color = 'orange';
					}	
					
					if (strpos($office_campaign->expected_datajson_status['content_type'], 'application/json') !== false) {
						$mime_color = 'green';
					} else {
						$mime_color = 'red';
					}
								
				?>
				<span style="color:<?php echo $status_color;?>">
					<?php echo $office_campaign->expected_datajson_status['http_code']?>
				</span>			
			
				<span style="color:<?php echo $mime_color;?>">
					<?php echo $office_campaign->expected_datajson_status['content_type']?>
				</span>
			
				
			</td>
		</tr>
				
		
		
		
		
		<tr>
			<th>Data.json URL</th>
			<td><?php echo $office_campaign->datajson_url ?></td>
		</tr>
		
		<tr>
			<th>Data.json Notes</th>
			<td><?php echo $office_campaign->datajson_notes ?></td>
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



		
		<?php endif; ?>


		
		
		<?php if(!empty($child_offices)) : ?>
		<div class="panel panel-default">
		<div class="panel-heading">Sub Agencies</div>
		<table class="table table-striped table-hover">
			<tr>
				<th>Agency</th>
				<th>Status</th>
				<th>Content-Type</th>					
			</tr>
			<?php foreach ($child_offices as $office):?>
			
			<?php 
			
				if(!empty($office->datajson_status)) {
					$office->datajson_status = json_decode($office->datajson_status);
				}				
			
				$http_code = (!empty($office->datajson_status->http_code)) ? $office->datajson_status->http_code : 0;
			
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
				
				$content_type = (!empty($office->datajson_status->content_type)) ? $office->datajson_status->content_type : null;
				
				if (strpos($content_type, 'application/json') !== false) {
					$mime_color = 'success';
				} else {
					$mime_color = 'danger';
				}
							
			?>				
			
			<tr class="<?php echo $status_color ?>">
				<td><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></td>
				<td><?php if (!empty($office->datajson_status->http_code)): ?><a class="text-<?php echo $status_color ?>" href="<?php echo $office->datajson_status->url;?>"><?php echo $office->datajson_status->http_code ?></a><?php endif; ?></td>
				<td><?php if (!empty($office->datajson_status->content_type)): ?><span class="text-<?php echo $mime_color ?>"><?php echo $office->datajson_status->content_type?></span><?php endif; ?></td>					
			</tr>
			<?php endforeach;?>
		</table>
		</div>
		<?php endif; ?>


      </div>

      <hr>

<?php include 'footer.php'; ?>