
<?php 

function status_table($title, $rows) {

?>
	<div class="panel panel-default">
	<div class="panel-heading"><?php echo $title?></div>
	<table class="table table-striped table-hover">
		<tr>
			<th>Agency</th>
			<th>Status</th>									
		</tr>
		<?php foreach ($rows as $office):?>
		
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
			        $status_color = 'danger';
			        break;					
			    default:
					$status_color = 'danger';
			}	
			
			$valid_json = (!empty($office->datajson_status->valid_json)) ? $office->datajson_status->valid_json : null;
			if ($valid_json !== true && $status_color == 'success') {
				$status_color = 'warning';
			}
			
			$http_status_color = ($http_code == 200) ? 'success' : $status_color;
			
			
			$content_type = (!empty($office->datajson_status->content_type)) ? $office->datajson_status->content_type : null;
			
			if (strpos($content_type, 'application/json') !== false) {
				$mime_color = 'success';
			} else {
				$mime_color = 'danger';
			}
			
			$icon = null;
			
			if (isset($office->datajson_status->valid_json)) {
			    $json_status = ($office->datajson_status->valid_json == true) ? 'success' : 'danger';									    
			} else {
			    $json_status = 'danger';
			}

			
			if ($json_status == 'success') {
			    $icon = '<i class="text-' . $json_status . ' fa fa-check-square"></i>';			    
			}
			
			if ($json_status == 'danger') {
			    $icon = '<i class="text-' . $json_status . ' fa fa-times-circle"></i>';			    
			}
			
			if ($status_color == 'warning') {
                $icon = '<i class="text-' . $status_color . ' fa fa-exclamation-triangle"></i>';			    			    
			}
			    
		
						
		?>				
		
		<tr class="<?php echo $status_color ?>">
			<td><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></td>
			<td><?php echo $icon; ?>
		</tr>
		<?php endforeach;?>
	</table>
	</div>

<?php 	
}
?>