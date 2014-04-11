
<?php 

function status_table($title, $rows, $config = null) {

?>
	<div class="panel panel-default">
	<div class="panel-heading"><?php echo $title?></div>
	<table class="table table-striped table-hover">
		<tr>
			<th class="col-sm-6">Agency</th>
		    <th class="col-sm-2">/data</th>												
			<th class="col-sm-2">/data.json</th>
			<th class="col-sm-2">valid schema</th>										
		</tr>
		<?php foreach ($rows as $office):?>
		
		<?php 
		
		
		
			if(!empty($office->datajson_status)) {
				$office->datajson_status = json_decode($office->datajson_status);
			}
			
			if(!empty($office->datapage_status)) {
				$office->datapage_status = json_decode($office->datapage_status);
			}							
		
			$json_http_code = (!empty($office->datajson_status->http_code)) ? $office->datajson_status->http_code : 0;
			$html_http_code = (!empty($office->datapage_status->http_code)) ? $office->datapage_status->http_code : 0;

						
			$status_color = http_status_color($json_http_code);	
			
			$valid_json = (!empty($office->datajson_status->valid_json)) ? $office->datajson_status->valid_json : null;
			if ($valid_json !== true && $status_color == 'success') {
				$status_color = 'danger';
			}		
			
			$valid_schema = (!empty($office->datajson_status->valid_schema)) ? $office->datajson_status->valid_schema : false;
			if ($valid_schema !== true && $valid_json === true) {
				$status_color = 'warning';
			}

			
			$html_status = http_status_color($html_http_code);	
			
			
			$icon = null;
			
			if (isset($office->datajson_status->valid_json)) {
			    $json_status = ($office->datajson_status->valid_json == true) ? 'success' : 'danger';									    
			} else {
			    $json_status = 'danger';
			}

			if (isset($valid_schema)) {
			    $schema_status = ($valid_schema === true) ? 'success' : 'danger';									    
			} else {
			    $schema_status = 'danger';
			}				


			// var_dump($office->datajson_status); exit;


			if (!empty($config['max_size']) && 				
				($office->datajson_status->download_content_length > $config['max_size'])) {
				$schema_status = 'warning';
			}

			//echo $office->datajson_status->download_content_length;


			$json_icon       = page_status($json_status);
			$schema_icon 	 = page_status($schema_status);
			
			$page_icon       = page_status($html_status);
			
			
				
		?>				
		
		<tr class="<?php echo $status_color ?>">
			<td><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></td>
			<td><?php if($html_status != 'success') echo $page_icon; ?>
			<td><?php echo $json_icon; ?>	
			<td><?php echo $schema_icon; ?>		
		</tr>
		<?php endforeach;?>
	</table>
	</div>

<?php 	
}

function http_status_color($status_code) {
    
    switch ($status_code) {
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
    
    return $status_color;
}

function page_status($data_status, $status_color = null) {
    
	if ($data_status == 'success') {
	    $icon = '<i class="text-' . $data_status . ' fa fa-check-square"></i>';			    
	}

	if ($data_status == 'danger') {
	    $icon = '<i class="text-' . $data_status . ' fa fa-times-circle"></i>';			    
	}

	if ($data_status == 'warning' || $status_color == 'warning') {
        $icon = '<i class="text-' . $status_color . ' fa fa-exclamation-triangle"></i>';			    			    
	}	
	
	return $icon;		    
}


?>