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
		<h3>Data.gov Status</h3>
		
		<table>
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




		
		<?php endif; ?>


		
		
		<?php if(!empty($child_offices)): ?>
		
		<h3>Sub-agencies</h3>
		
			<?php foreach ($child_offices as $office):?>
		

	        <div class="col-lg-4">
		
	          <h5><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name ?></a></h5>

				<div><a href="<?php echo $office->url ?>"><?php echo $office->url ?></a></div>
				<div><?php echo $office->notes ?></div>				
		
	        </div>

			<?php endforeach;?>
		<?php endif; ?>


      </div>

      <hr>

<?php include 'footer.php'; ?>