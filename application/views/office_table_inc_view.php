<?php

function status_table($title, $rows, $tracker, $config = null, $sections_breakdown, $subsections_breakdown, $milestone = null) {

    ?>
	<div class="panel panel-default">
	<table class="dashboard table table-striped table-hover table-bordered">

            <tr class="dashboard-meta-heading">
                <td><?php echo $title ?></td>

                <?php foreach ($sections_breakdown as $key => $name): ?>
                    <td colspan="<?php echo count($subsections_breakdown[$key]); ?>" class="section-<?php echo $key; ?>">
                        <span><?php echo $name; ?>
                            <a href="<?php echo site_url('docs') . '#general_indicators' ?>">
                                <span class="glyphicon glyphicon-info-sign"></span>
                            </a>
                        </span>
                    </td>
                <?php endforeach; ?>

            </tr>
            <tr class="dashboard-heading">

                    <th class="col-sm-3"><div class="sr-only">Agency</div></th>

                    <?php foreach ($subsections_breakdown as $section_name => $subsections): ?>
                        <?php foreach ($subsections as $subsection): ?>
                            <th class="tilt"><div><?php echo $subsection->label;?></div></th>
                        <?php endforeach; ?>
                    <?php endforeach; reset($subsections_breakdown); ?>

            </tr>

            <?php
            if($milestone && !empty($milestone->selected_milestone)) {
                    $milestone_url = '/' . $milestone->selected_milestone;
            }
            ?>

            <?php foreach ($rows as $office):?>

		<?php
                if(!empty($office->tracker_fields)) {
                    $office->tracker_fields = json_decode($office->tracker_fields);
                }
		?>

		<tr class="metrics-row">

                    <th><a href="<?php echo site_url('offices/detail') ?>/<?php echo $office->id . $milestone_url;?>"><?php echo $office->name;?></a></th>

                    <?php foreach ($subsections_breakdown as $section_name => $subsections): ?>
                        <?php foreach ($subsections as $subsection): ?>

                            <?php
                            $status = '';

                            if ($subsection->label === 'Self-Assessment') {
                                $status = @$office->tracker_fields->cb_self_assessment;
                            } elseif ($subsection->label === 'Implementation Plan') {
                                $status = @$office->tracker_fields->cb_implementation_plan;
                            } else if ($subsection->label === 'CIO Assignment Plan (Optional)') {
                                $status = @$office->tracker_fields->cb_cio_assignment_plan;
                            } elseif ($subsection->label === 'Bureau IT Leadership') {
                                $status = @$office->tracker_fields->pa_bureau_it_leadership;
                            } elseif ($subsection->label === 'CIO Governance Board List') {
                                $status = @$office->tracker_fields->pa_cio_governance_board;
                            } else if ($subsection->label === 'IT Policy Archive') {
                                $status = @$office->tracker_fields->pa_it_policy_archive;
                            }

                            $column_anchor = $section_name . '_tab';
                            $subsection_selection = ($section_name == 'pdl') ? '' : '?highlight=' . $section_name;
                            $metric_type = $subsection->label === 'GAO Recommendations' ? 'number-metric' : 'boolean-metric';
                            ?>

                            <td class="<?php echo $metric_type; ?> <?php if (!empty($status)) echo status_color($status); ?> <?php if($status) echo $status; ?>">
                                <a href="<?php echo site_url('offices/detail') ?>/<?php echo $office->id . $milestone_url;?><?php echo $subsection_selection . '#' . $column_anchor; ?>">
                                    <span>
                                        <?php
                                        // TO DO - once the gr_open_gao_recommendations value is set correctly
                                        // in tracker_fields, use that instead.
                                        $rec_status = json_decode($office->recommendation_status);
                                        if ($subsection->label === 'GAO Recommendations') {
                                            echo isset($rec_status->tracker_fields->gr_open_gao_recommendations) ? $rec_status->tracker_fields->gr_open_gao_recommendations : '';
                                        }
                                        elseif (!empty($status)) {
                                            echo page_status($status);
                                        }
                                        ?>&nbsp;
                                    </span>
                                </a>
                            </td>

                        <?php endforeach; ?>
                    <?php endforeach; reset($subsections_breakdown); ?>

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
	if($data_status == 'yellow') $data_status = 'warning';

	if ($data_status == 'highlight') {
	    $icon = '<i class="text-success fa fa-star"></i>';
	}

	if ($data_status == 'success') {
	    $icon = '<i class="text-success fa fa-check-square"></i>';
	}

	if ($data_status == 'danger') {
	    $icon = '<i class="text-danger fa fa-times-circle"></i>';
	}

	if ($data_status == 'warning' || $status_color == 'warning') {
        $icon = '<i class="text-warning fa fa-exclamation-triangle"></i>';
	}

	if ($data_status == 'unknown') {
		$status_color = (!empty($status_color)) ? 'text-'. $status_color : '';
		 $icon = '<i class="unknown-value ' . $status_color . ' fa fa-question-circle"></i>';
	}

	if(empty($icon) && !empty($data_status))  $icon = '<i class="text-' . $status_color . ' fa fa-question-circle"></i>';

	if(empty($icon)) $icon = '';

	return $icon;
}

function metric_status_color($metric, $success_basis, $weight) {

	if(empty($metric)) return '';

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

    if ( (is_numeric($denominator) && !empty($denominator)) && is_numeric($numerator)) {
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