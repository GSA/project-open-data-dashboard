<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>


    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div>
          <h2>Agencies</h2>

			<?php if(!empty($cfo_offices)) : ?>
			<div class="panel panel-default">
			<div class="panel-heading">CFO Act Agencies</div>
			<table class="table table-striped table-hover">
				<tr>
					<th>Agency</th>
					<th>Status</th>
					<th>Content-Type</th>					
				</tr>
				<?php foreach ($cfo_offices as $office):?>
				
				<?php 
				
					if(!empty($office->datajson_status)) {
						$office->datajson_status = json_decode($office->datajson_status);
					}				
				
					switch ($office->datajson_status->http_code) {
					    case 404:
					        $status_color = 'danger';
					        break;
					    case 200:
					        $status_color = 'success';
					        break;
					    default:
							$status_color = 'warning';
					}	
					
					if (strpos($office->datajson_status->content_type, 'application/json') !== false) {
						$mime_color = 'success';
					} else {
						$mime_color = 'danger';
					}
								
				?>				
				
				<tr class="<?php echo $status_color ?>">
					<td><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></td>
					<td><a class="text-<?php echo $status_color ?>" href="<?php echo $office->datajson_status->url;?>"><?php echo $office->datajson_status->http_code ?></a></td>
					<td><span class="text-<?php echo $mime_color ?>"><?php echo $office->datajson_status->content_type?></span></td>					
				</tr>
				<?php endforeach;?>
			</table>
			</div>
			<?php endif; ?>
			
			<?php if(!empty($cfo_offices)) : ?>
			<h3>Other Offices Reporting to the White House</h3>
			<table>
				<?php foreach ($executive_offices as $office):?>
				<tr>
					<td><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></td>
				</tr>
				<?php endforeach;?>
			</table>
			<?php endif; ?>			
			
			<?php if(!empty($cfo_offices)) : ?>
			<h3>Other Independent Offices</h3>
			<table>
				<?php foreach ($independent_offices as $office):?>
				<tr>
					<td><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></td>
				</tr>
				<?php endforeach;?>
			</table>	
			<?php endif; ?>								

        </div>
      </div>

      <hr>

<?php include 'footer.php'; ?>