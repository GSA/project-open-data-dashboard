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

function getBureauITLeadershipTable($archive_dir, $office_id, $bd_status) {
    $last_crawl = $bd_status->last_crawl;
    $crawl_file = $archive_dir . "/bureaudirectory/" . date("Y-m-d", $last_crawl) . "/$office_id.json";
    if (file_exists($crawl_file)) {
        $data = file_get_contents($crawl_file);
        $bureau_directory = json_decode($data);
    }
    $retval = '
        <!-- Agency Bureau Table -->
        <div class="panel panel-default">
        <div class="panel-heading">
            Bureau IT Leadership Directory';
    $retval .= "        <a class=\"info-icon\" href=\"" . site_url('docs') . '#bureaudirectory_excerpts">';
    $retval .= "            <span class=\"glyphicon glyphicon-info-sign\"></span>
            </a>
        </div>
        <div style=\"padding : 1em;\">";
            $sections = array("1.2.4" => "edi_schedule_delivered",
                "1.2.5" => "schedule",
                "1.2.6" => "pe_feedback_specified",
                "1.2.7" => "ps_publication_process");

            if (!empty($bureau_directory->generated)) {
                if ($published_date = strtotime($bureau_directory->generated)) {
                    $published_date = date("l, d-M-Y H:i:s T", $published_date);
                    $retval .= '<h2><span style="color:#666">Date specified: </span>' . "$published_date</h2>";
                }
            }

            if (!empty($bd_status->filetime) && $bd_status->filetime > 0) {
                $retval .= 'Date of bureaudirectory.json file: ' . date("l, d-M-Y H:i:s T", $bd_status->filetime);
            }

            if (!empty($bureau_directory->leaders)) {
                $retval .= '<table class="table table-striped table-hover" style="border-bottom : 3px solid #ccc">';
                $retval .= "<tr>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2' style='width: 1%'>Bureau Code</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Bureau Name</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>First Name</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Last Name</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Key Bureau CIO</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Employment Type</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Employment Type Other</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Type of Appointment</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Other Responsibilities</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Rating Official Title</th>";
                $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Reviewing Official Title</th>";
                $retval .= "</tr>\n";
                foreach($bureau_directory->leaders as $leader) {
                    $retval .= "<tr>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11' style='width: 1%'>" . (isset($leader->bureauCode) ? $leader->bureauCode : "") . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->bureauName) ? $leader->bureauName : "Agency-wide")  . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->firstName) ? $leader->firstName : "") . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->lastName) ? $leader->lastName : "") . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->keyBureauCIO) ? $leader->keyBureauCIO : "") . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->employmentType) ? $leader->employmentType : "") . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->employmentTypeOther) ? $leader->employmentTypeOther : "") . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->typeOfAppointment) ? $leader->typeOfAppointment : "") . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->otherResponsibilities) ? $leader->otherResponsibilities : "") . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->evaluationRatingOfficialTitle) ? $leader->evaluationRatingOfficialTitle : "") . "</td>";
                    $retval .= "<td class='col-sm-11 col-md-11 col-lg-11'>" . (isset($leader->evaluationReviewingOfficialTitle) ? $leader->evaluationReviewingOfficialTitle : "") . "</td>";
                    $retval .= "</tr>\n";
                }
                $retval .= '</table>';
            }

        $retval .= "</div>";
    $retval .= "</div>";
    return $retval;

}

function getGovernanceBoardTable($archive_dir, $office_id, $gb_status) {
    $last_crawl = $gb_status->last_crawl;
    $crawl_file = $archive_dir . "/governanceboard/" . date("Y-m-d", $last_crawl) . "/$office_id.json";
    if (file_exists($crawl_file)) {
        $data = file_get_contents($crawl_file);
        $gb_directory = json_decode($data);
    }
    else {
        error_log("getGovernanceBoard : no file for $crawl_file");
    }

    $retval = "<div class=\"panel panel-default\">
                    <div class=\"panel-heading\">
                        Governance Boards
                        <a class=\"info-icon\" href=\"" . site_url('docs') . "#governanceboard_excerpts\">
                            <span class=\"glyphicon glyphicon-info-sign\"></span>
                        </a>
                    </div>
                    <div style=\"padding : 1em;\">";
                        $sections = array("1.2.4" => "edi_schedule_delivered",
                            "1.2.5" => "schedule",
                            "1.2.6" => "pe_feedback_specified",
                            "1.2.7" => "ps_publication_process");

                if (!empty($governance_board->generated)) {
                    if ($published_date = strtotime($governance_board->generated)) {
                        $published_date = date("l, d-M-Y H:i:s T", $published_date);
                        $retval .= '<h2><span style="color:#666">Date specified: </span>' . "$published_date</h2>";
                    }
                }

                if (!empty($gb_status->filetime) && $gb_status->filetime > 0) {
                    $retval .= 'Date of governanceboard.json file: ' . date("l, d-M-Y H:i:s T", $gb_status->filetime);
                }

                ?>

                <?php
                if (!empty($gb_directory->boards)) {
                    $retval .= '<table class="table table-striped table-hover" style="border-bottom : 3px solid #ccc">';
                    $retval .= "<tr>";
                    $retval .= "<th class='col-sm-2 col-md-2 col-lg-2' style='width: 1%'>Bureau Code</th>";
                    $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Bureau Name</th>";
                    $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Governance Board Name</th>";
                    $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Federal Program Inventory Code</th>";
                    $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>Federal Program Inventory Name</th>";
                    $retval .= "<th class='col-sm-2 col-md-2 col-lg-2'>CIO Involvement Description</th>";
                    $retval .= "</tr>";
                    foreach ($gb_directory->boards as $board) {
                        $retval .= "<tr>";
                        $retval .= "<td class='col-sm-2 col-md-2 col-lg-2' style='width: 1%'>" . (isset($board->bureauCode) ? $board->bureauCode : "") . "</td>";
                        $retval .= "<td class='col-sm-2 col-md-2 col-lg-2'>" . (isset($board->bureauName) ? $board->bureauName : "Agency-wide")  . "</td>";
                        $retval .= "<td class='col-sm-2 col-md-6 col-lg-2'>" . (isset($board->governanceBoardName) ? $board->governanceBoardName : "") . "</td>";
                        $retval .= "<td class='col-sm-2 col-md-2 col-lg-2'>" . (isset($board->programCodeFPI) ? $board->programCodeFPI : "") . "</td>";
                        $retval .= "<td class='col-sm-2 col-md-2 col-lg-2'>" . (isset($board->programNameFPI) ? $board->programNameFPI : "") . "</td>";
                        $retval .= "<td class='col-sm-6 col-md-6 col-lg-6'>" . (isset($board->cioInvolvementDescription) ? $board->cioInvolvementDescription : "") . "</td>";
                        $retval .= "</tr>\n";
                    }
                    $retval .= "</table>\n";
                }

            $retval .= "</div>
        </div>";
    return $retval;

}

?>
