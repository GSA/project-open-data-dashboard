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
					<th>JSON</th>					
					<th>Schema</th>										
				</tr>
				<?php foreach ($cfo_offices as $office):?>
				
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
							$status_color = 'danger';
					}	
					
					$valid_json = (!empty($office->datajson_status->valid_json)) ? $office->datajson_status->valid_json : null;
					if ($valid_json !== true && $status_color == 'success') {
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
					<td><?php if (isset($office->datajson_status->valid_json)): ?><span class="text-<?php echo ($office->datajson_status->valid_json == true) ? 'success' : 'danger'?>"><?php echo ($office->datajson_status->valid_json == true) ? 'Valid' : 'Invalid'?></span><?php endif; ?></td>					
					<td><?php if (isset($office->datajson_status->valid_schema)): ?><span class="text-<?php echo ($office->datajson_status->valid_json == true) ? 'success' : 'danger'?>"><?php echo ($office->datajson_status->valid_json == true) ? 'Valid' : 'Invalid' ?></span><?php endif; ?></td>					
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