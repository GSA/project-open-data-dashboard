
<?php

function status_table($title, $rows, $config = null) {

?>
	<div class="panel panel-default">
	<div class="panel-heading"><?php echo $title?></div>
	<table class="dashboard table table-striped table-hover table-bordered">
		<tr class="dashboard-heading">
			<th class="col-sm-3">		<div class="sr-only">Agency			</div></th>

			<th class="tilt"><div>Datasets 			</div></th>
			<th class="tilt"><div>Percent valid 	</div></th>

			<th class="tilt"><div>Data.gov Harvest	</div></th>
			<th class="tilt"><div>Inventory	</div></th>
			<th class="tilt"><div>Inventory Superset	</div></th>
			<th class="tilt"><div>/data	</div></th>
			<th class="tilt"><div>Feedback	</div></th>
			<th class="tilt"><div>Schedule	</div></th>


		</tr>
		<?php foreach ($rows as $office):?>

		<?php



			if(!empty($office->datajson_status)) {
				$office->datajson_status = json_decode($office->datajson_status);
			}

			if(!empty($office->datapage_status)) {
				$office->datapage_status = json_decode($office->datapage_status);
			}

			if(!empty($office->tracker_fields)) {
				$office->tracker_fields = json_decode($office->tracker_fields);
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

				if ($percent_valid == 1 && $valid_schema === true) {
					$percent_valid = "100%";
				}
				else if (!empty($error_count) && $valid_schema === false) {
					$percent_valid = sprintf("%.1f%%", $percent_valid * 100);
				} else {
					$percent_valid = '';
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

    		<td class="boolean-metric <?php if (!empty($office->tracker_fields->pdl_datagov_harvested)) echo status_color($office->tracker_fields->pdl_datagov_harvested) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php if (!empty($office->tracker_fields->pdl_datagov_harvested)) echo page_status($office->tracker_fields->pdl_datagov_harvested); 		?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php if (!empty($office->tracker_fields->edi_updated)) echo status_color($office->tracker_fields->edi_updated) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php if (!empty($office->tracker_fields->edi_updated)) echo page_status($office->tracker_fields->edi_updated); 		?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php if (!empty($office->tracker_fields->edi_superset)) echo status_color($office->tracker_fields->edi_superset) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php if (!empty($office->tracker_fields->edi_superset)) echo page_status($office->tracker_fields->edi_superset);	?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php if (!empty($office->tracker_fields->pdl_slashdata)) echo status_color($office->tracker_fields->pdl_slashdata) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php if (!empty($office->tracker_fields->pdl_slashdata)) echo page_status($office->tracker_fields->pdl_slashdata); 	?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php if (!empty($office->tracker_fields->pe_feedback_specified)) echo status_color($office->tracker_fields->pe_feedback_specified) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php if (!empty($office->tracker_fields->pe_feedback_specified)) echo page_status($office->tracker_fields->pe_feedback_specified); 				?>&nbsp;</span></a></td>
    		<td class="boolean-metric <?php if (!empty($office->tracker_fields->edi_schedule_delivered)) echo status_color($office->tracker_fields->edi_schedule_delivered) ?>"><a href="/offices/detail/<?php echo $office->id;?>#"><span><?php if (!empty($office->tracker_fields->edi_schedule_delivered)) echo page_status($office->tracker_fields->edi_schedule_delivered); 		?>&nbsp;</span></a></td>
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

	if(empty($data_status)) return '';

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


function process_percentage ($numerator, $denominator) {

    if (is_numeric($denominator) && is_numeric($numerator)) {
        $percent_valid = $numerator/$denominator;
    } else {
        $percent_valid = null;
    }

    if(is_numeric($percent_valid)) {

        if ($percent_valid == 1) {
            $percent_valid = "100%";
        }
        else {
            $percent_valid = sprintf("%.1f%%", $percent_valid * 100);
        }

    }

    return $percent_valid;

}



?>