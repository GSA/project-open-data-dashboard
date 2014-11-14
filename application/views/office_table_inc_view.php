<?php 
function status_table_full($title, $rows, $tracker, $config = null, $selected_milestone = null, $milestone_specified = null) {
?>

		<?php
			if($milestone_specified == "true" && !empty($selected_milestone)) {
				$milestone_url = '/' . $selected_milestone;
			} else {
				$milestone_url = '';
			}
		?>


	<table class="dashboard table table-striped table-hover table-bordered">
		<tr class="dashboard-heading">
				<th class="vertical-heading"><div>Agency</div></th>
			<?php foreach ($tracker as $tracker_field => $tracker_value):?>

				<?php $field_class = substr($tracker_field, 0, strpos($tracker_field, '_')); ?>

				<th class="vertical-heading <?php echo $field_class ?>"><div><?php echo $tracker_field; ?></div></th>
			<?php endforeach; ?>
		</tr>

		<?php foreach ($rows as $office):?>

			<?php
				if(!empty($office->tracker_fields)) {
					$office->tracker_fields = json_decode($office->tracker_fields);
				}
			?>

			<tr>
				
				<th><a href="<?php echo site_url('offices/detail') ?>/<?php echo $office->id . $milestone_url;?>"><?php echo $office->name;?></a></th>				
				

				<?php reset($tracker); ?>

				<?php foreach ($tracker as $tracker_field_name => $tracker_field_meta):?>
					
						<?php if(!empty($office->tracker_fields->$tracker_field_name)): ?>
							<td>
								<?php echo $office->tracker_fields->$tracker_field_name ?>
							</td>
						<?php else: ?>
							<td class="empty-field"></td>	
						<?php endif; ?>
				<?php endforeach; ?>
			</tr>

		<?php endforeach; ?>

	</table>
	

<?php 
}
?>




<?php

function status_table($title, $rows, $tracker, $config = null, $sections_breakdown, $milestone = null) {

?>
	<div class="panel panel-default">
	<table class="dashboard table table-striped table-hover table-bordered">
		<tr class="dashboard-meta-heading">
			<td><?php echo $title ?></td>

			<?php $colspan = ($milestone->selected_milestone < '2014-11-30') ? '5' : '6'; ?>
			<td colspan="<?php echo $colspan; ?>">
				Leading Indicators Strategy 
                <a href="<?php echo site_url('docs') . '#leading_indicators_strategy' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>			
			</td>
			
			<td colspan="2" class="pdl-heading">
				Public Data Listing Metrics
                <a href="<?php echo site_url('docs') . '#automated_metrics' ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a>				
			</td>
		</tr>
		<tr class="dashboard-heading">
			<th class="col-sm-3">		<div class="sr-only">Agency			</div></th>

			<?php foreach ($sections_breakdown as $section_name => $section_title): ?>
				<?php if ($milestone->selected_milestone < '2014-11-30' && $section_name == 'ui') continue; ?>
				<th class="tilt"><div><?php echo $section_title;?></div></th>
			<?php endforeach; reset($sections_breakdown); ?>

			<th class="tilt pdl-heading"><div>Public Datasets</div></th>
			<th class="tilt pdl-heading"><div>Valid Metadata</div></th>

		</tr>

		<?php
			if($milestone && !empty($milestone->selected_milestone)) {
				$milestone_url = '/' . $milestone->selected_milestone;
			}
		?>



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


			if (!empty($config['max_remote_size']) && !empty($office->datajson_status->download_content_length) &&
				($office->datajson_status->download_content_length > $config['max_remote_size'])) {
				$schema_status = 'warning';
			}

			if ($schema_status != 'danger' && $json_status == 'danger') {
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
			<th><a href="<?php echo site_url('offices/detail') ?>/<?php echo $office->id . $milestone_url;?>"><?php echo $office->name;?></a></th>

			<?php foreach ($sections_breakdown as $section_name => $section_title): ?>

				<?php 
					if ($milestone->selected_milestone < '2014-11-30' && $section_name == 'ui') continue;

					$column = $section_name . '_aggregate_score'; 
					$column_anchor = $section_name . '_tab';
					$section_selection = ($section_name == 'pdl') ? '' : '?highlight=' . $section_name;
				?>

				<td class="boolean-metric <?php if (!empty($office->tracker_fields->$column)) echo status_color($office->tracker_fields->$column) ?>">
					<a href="<?php echo site_url('offices/detail') ?>/<?php echo $office->id . $milestone_url;?><?php echo $section_selection . '#' . $column_anchor; ?>">
						<span>
							<?php if (!empty($office->tracker_fields->$column)) echo page_status($office->tracker_fields->$column);?>&nbsp;
						</span>
					</a>
				</td>

			<?php endforeach; reset($sections_breakdown); ?>

			<td class="content-metric <?php echo $json_status?>"><a href="<?php echo site_url('offices/detail') ?>/<?php echo $office->id . $milestone_url;?>#pdl_datasets"><span><?php echo $total_records; ?>&nbsp;</span></a></td>
			<td class="content-metric <?php echo $schema_status ?>"><a href="<?php echo site_url('offices/detail') ?>/<?php echo $office->id . $milestone_url;?>#pdl_valid_metadata"><span><?php echo $percent_valid?>&nbsp;</span></a> </td>

		</tr>
		<?php endforeach;?>
	</table>
	</div>

<?php
} 
?>



<?php
function status_table_qa($title, $rows, $tracker, $config = null, $sections_breakdown = null, $milestone = null) {

		$model = $sections_breakdown;

?>

	<table class="dashboard table table-striped table-hover table-bordered qa-table">

		<tr class="dashboard-heading">
			<th class="col-sm-3">		<div class="sr-only">Agency			</div></th>


			<?php foreach ($model as $qa_field) : ?>
				<th class="vertical-heading"><div><?php echo $qa_field->label;?></div></th>
			<?php endforeach; ?>


		</tr>

		<?php
			if($milestone && !empty($milestone->selected_milestone)) {
				$milestone_url = '/' . $milestone->selected_milestone;
			}
		?>



		<?php foreach ($rows as $office):?>

		<?php

			if(!empty($office->datajson_status)) {
				$office->datajson_status = json_decode($office->datajson_status);
			}
	
			if(!empty($office->datajson_status->qa->validation_counts)) {



			$error_count 		= (!empty($office->datajson_status->error_count)) ? $office->datajson_status->error_count : 0;
			$total_records	 	= (!empty($office->datajson_status->total_records)) ? $office->datajson_status->total_records : '';

			$percent_valid		= (!empty($total_records)) ? process_percentage(($total_records - $error_count), $total_records) : '';

			//var_dump($office->datajson_status->qa); exit;

			$model->total_records->value 			= $office->datajson_status->total_records;
    		$model->valid_count->value 				= $office->datajson_status->total_records - $office->datajson_status->error_count;
			$model->programs->value 				= count($office->datajson_status->qa->programCodes);
			$model->bureaus->value 					= count($office->datajson_status->qa->bureauCodes);

			$model->accessLevel_public->value 		= $office->datajson_status->qa->accessLevel_public;
			$model->accessLevel_nonpublic->value 	= $office->datajson_status->qa->accessLevel_nonpublic;
			$model->accessLevel_restricted->value 	= $office->datajson_status->qa->accessLevel_restricted;
			$model->accessURL_present->value 		= $office->datajson_status->qa->accessURL_present;
			$model->accessURL_total->value 			= $office->datajson_status->qa->accessURL_total;
			$model->accessURL_working->value 		= $office->datajson_status->qa->validation_counts->http_2xx;
			$model->accessURL_format->value 		= $model->accessURL_working->value - $office->datajson_status->qa->validation_counts->format_mismatch;
			$model->accessURL_html->value 			= $office->datajson_status->qa->validation_counts->html;
			$model->accessURL_pdf->value 			= $office->datajson_status->qa->validation_counts->pdf;

			reset($model);			

		?>

		<tr>
			<th><a href="<?php echo site_url('offices/detail') ?>/<?php echo $office->id . $milestone_url;?>"><?php echo $office->name;?></a></th>

			<?php foreach ($model as $qa_field_name => $qa_field) : ?>


				<?php 

						if(!empty($qa_field->total_field)) {
							$metric = process_percentage($qa_field->value, $model->{$qa_field->total_field}->value);
						} else {
							$metric = $qa_field->value; 	
						}
			
				?>


				<td style="<?php echo metric_status_color($metric, $qa_field->success_basis, $qa_field->success_weight); ?>">
					<a href="<?php echo site_url('offices/detail') ?>/<?php echo $office->id . $milestone_url;?><?php echo '#metrics_' . $qa_field_name; ?>">
						<span>
							<?php 
								echo $metric;
							?>
						</span>
					</a>
				</td>

			<?php endforeach; ?>

		</tr>
		<?php } endforeach;?>
	</table>
	

<?php
}
?>





<?php 

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

	if ($status == 'yes' || $status == 'green') {
		return 'success';
	} else if ($status == 'no' || $status == 'red') {
		return 'danger';
	} else {
		return 'warning';
	}

}

function page_status($data_status, $status_color = null) {

	if(empty($data_status)) return '';

	if($data_status == 'yes' || $data_status == 'green') $data_status = 'success';
	if($data_status == 'no' || $data_status == 'red') $data_status = 'danger';

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

function metric_status_color($metric, $success_basis, $weight) {

	if(!empty($success_basis)) {

		$emphasis = false;
		
		// curve the percentage
		$curve = pow(100, 1-$weight) * pow($metric, $weight);

		$value = ($curve * .01);

		if ($success_basis == 'low') {
			$value = 1 - $value;

			if($metric > 50) {
				$emphasis = true;			
			} 

		} else {
			if($metric < 50) {
				$emphasis = true;				
			} 			
		}

		if($emphasis) {
			$saturation = '80%';
			$lightness  = '80%';				
		} else {
			$saturation = '75%';
			$lightness  = '85%';			
		}



		$hue = round(($value) * 120);		
		$status_color = "background-color : hsl($hue, $saturation, $lightness);";
	} else {
		$status_color = '';
	}

	return $status_color;
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