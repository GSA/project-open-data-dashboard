
<?php

function status_table($title, $rows, $config = null) {

?>
	<div class="panel panel-default">
	<div class="panel-heading"><?php echo $title?></div>
	<table class="dashboard table table-striped table-hover table-bordered">
		<tr class="dashboard-heading">
			<th class="col-sm-3">		<div class="sr-only">Agency			</div></th>

			<th class="tilt"><div>Records 			</div></th>
			<th class="tilt"><div>Percent valid 	</div></th>

			<th class="tilt"><div>Data.gov Harvest	</div></th>
			<th class="tilt"><div>Inventory	</div></th>
			<th class="tilt"><div>Inventory Superset	</div></th>
			<th class="tilt"><div>/data	</div></th>
			<th class="tilt"><div>Feedback	</div></th>
			<th class="tilt"><div>Schedule	</div></th>
			<th class="tilt"><div>Publication Process	</div></th>



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


			if (!empty($config['max_size']) && !empty($office->datajson_status->download_content_length) &&
				($office->datajson_status->download_content_length > $config['max_size'])) {
				$schema_status = 'warning';
			}

			//echo $office->datajson_status->download_content_length;


			$json_icon       = page_status($json_status);
			$schema_icon 	 = page_status($schema_status);

			$page_icon       = page_status($html_status);



			$error_count 		= (!empty($office->datajson_status->error_count)) ? $office->datajson_status->error_count : 0;
			$total_records	 	=	(!empty($office->datajson_status->total_records)) ? $office->datajson_status->total_records : '';

			$percent_valid 		= '';
			$percent_valid		=	(!empty($total_records)) ? ($total_records - $error_count)/$total_records : '';

			if($percent_valid) {

				if ($percent_valid == 1) {
					$percent_valid = "100%";
				}
				else {
					$percent_valid = sprintf("%.1f%%", $percent_valid * 100);
				}

			}

			if ($percent_valid === 0) {
				$percent_valid = "0%";
				$status_color = 'danger';
			}


			if(empty($total_records)) {
				$total_records = $schema_icon;
			}


		?>

		<tr class="metrics-row">
			<th><a href="/offices/detail/<?php echo $office->id;?>"><?php echo $office->name;?></a></th>
			<td class="content-metric <?php echo $json_status?>"><a href="/offices/detail/<?php echo $office->id;?>#datajson_posted"><span><?php echo $total_records; ?>&nbsp;</span></a></td>
			<td class="content-metric <?php echo $schema_status ?>"><a href="/offices/detail/<?php echo $office->id;?>#datajson_posted"><span><?php echo $percent_valid?>&nbsp;</span></a> </td>

    		<td class="boolean-metric <?php echo status_color($office->datagov_harvest) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php echo page_status($office->datagov_harvest); 		?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php echo status_color($office->inventory_posted) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php echo page_status($office->inventory_posted); 		?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php echo status_color($office->inventory_superset) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php echo page_status($office->inventory_superset);	?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php echo status_color($office->datajson_slashdata) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php echo page_status($office->datajson_slashdata); 	?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php echo status_color($office->feedback) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php echo page_status($office->feedback); 				?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php echo status_color($office->schedule_posted) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php echo page_status($office->schedule_posted); 		?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php echo status_color($office->publication_process_posted) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php echo page_status($office->publication_process_posted); ?>&nbsp;</span></a></td>
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

function status_color($status) {

	if(empty($status)) return '';

	if ($status == 'yes') {
		return 'success';
	} else if ($status == 'no') {
		return 'danger';
	} else {
		return 'warning';
	}

}

function page_status($data_status, $status_color = null) {

	if($data_status == 'yes') $data_status = 'success';
	if($data_status == 'no') $data_status = 'danger';

	if ($data_status == 'success') {
	    $icon = '<i class="text-' . $data_status . ' fa fa-check-square"></i>';
	}

	if ($data_status == 'danger') {
	    $icon = '<i class="text-' . $data_status . ' fa fa-times-circle"></i>';
	}

	if ($data_status == 'warning' || $status_color == 'warning') {
        $icon = '<i class="text-' . $status_color . ' fa fa-exclamation-triangle"></i>';
	}

	if(empty($icon) && !empty($data_status))  $icon = '<i class="text-' . $status_color . ' fa fa-exclamation-triangle"></i>';

	if(empty($icon)) $icon = '';

	return $icon;
}


?>