                <!-- Nav tabs -->
                <ul class="nav nav-tabs tracker-sections">

                    <?php foreach ($section_breakdown as $section_abbreviation => $section_title): ?>

                        <?php
                        if ($milestone->selected_milestone < '2014-11-30' && $section_abbreviation == 'ui')
                            continue;

                        $aggregate_score = $section_abbreviation . '_aggregate_score';

                        if (!empty($office_campaign->tracker_fields->$aggregate_score)) {
                            $section_score = 'bg-' . status_color($office_campaign->tracker_fields->$aggregate_score);
                        } else {
                            $section_score = '';
                        }
                        ?>

                        <li  <?php if ($section_abbreviation == $active_section) echo 'class="active"'; ?>>
                            <a name="<?php echo $section_abbreviation . '_tab'; ?>" href="#<?php echo $section_abbreviation; ?>" data-toggle="tab">
                        <?php echo $section_title; ?>
                                <div class="section-score <?php echo $section_score ?>"></div>
                            </a>
                        </li>
                            <?php endforeach;
                            reset($section_breakdown); ?>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content tracker-content">

                    <?php foreach ($section_breakdown as $section_abbreviation => $section_title): ?>
                         <div class="tab-pane <?php if ($section_abbreviation == $active_section) echo 'active'; ?>" id="<?php echo $section_abbreviation; ?>">

                            <div class="section-notes">

                                <?php
                                $note_field = 'note_' . $section_abbreviation . '_aggregate_score';
                                $note_data = (!empty($notes[$note_field])) ? $notes[$note_field] : $note_model;
                                ?>

                                <div><?php echo $note_data->current->note_html; ?></div>

                                <?php if (!empty($note_data->current->date) && !empty($note_data->current->author)): ?>
                                    <div class="note-metadata">
                                        Lasted edited on <?php echo $note_data->current->date; ?> by <?php echo $note_data->current->author; ?>
                                    </div>
                                <?php endif; ?>

                            </div>

                            <?php
                            $highlight_field = $section_abbreviation . '_selected_best_practice';
                            ?>

                            <?php if (!empty($office_campaign->tracker_fields->$highlight_field) && $office_campaign->tracker_fields->$highlight_field == 'yes'): ?>
                                <p class="form-flash text-success bg-success"><strong>Best Practice:</strong> <?php echo $office->name ?> has been highlighted for demonstrating a best practice on the <?php echo $section_title ?> indicator</p>
                            <?php endif; ?>


                            <table class="table table-striped table-hover" id="note-expander-parent">

                                <tr class="table-header">
                                    <th>Indicator</th>
                                    <th>Status</th>

                                    <th>Automated Metrics</th>
                                    <!--
                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                            <th></th>
                                    <?php endif; ?>
                                    -->
                                    <!-- NOTES
                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                            <th></th>
                                    <?php endif; ?>
                                    -->

                                    <?php foreach ($tracker_model as $tracker_field_name => $tracker_field_meta) : ?>

                                        <?php
                                        // Skip this field if it's not part of current section
                                        if (substr($tracker_field_name, 0, strlen($section_abbreviation)) !== $section_abbreviation)
                                            continue;

                                        // Skip this field if it's no longer relevant in later milestones
                                        if ($tracker_field_name == 'edi_schedule_risk' && $milestone->selected_milestone > '2014-11-30')
                                            continue;

                                        // If this is a best practice highlight field, don't show it unless logged in
                                        if (strpos($tracker_field_name, 'selected_best_practice') !== false && !$this->session->userdata('permissions') == $permission_level)
                                            continue;

                                        if (!empty($office_campaign->tracker_fields->$tracker_field_name) && $office_campaign->tracker_fields->$tracker_field_name != 'none') {
                                            if ($office_campaign->tracker_fields->$tracker_field_name == 'yes' || $office_campaign->tracker_fields->$tracker_field_name == 'green') {
                                                $status_icon = '<i class="text-success fa fa-check-square"></i><span class="sr-only">OK</span>';
                                                $status_class = 'success';
                                            } else if ($office_campaign->tracker_fields->$tracker_field_name == 'no' || $office_campaign->tracker_fields->$tracker_field_name == 'red') {
                                                $status_icon = '<i class="text-danger fa fa-times-circle"></i><span class="sr-only">Error</span>';
                                                $status_class = 'danger';
                                            } else {
                                                $status_icon = '<i class="text-warning fa fa-exclamation-triangle"></i><span class="sr-only">Warning</span>';
                                                $status_class = '';
                                            }
                                        } else {
                                            //$office_campaign->tracker_fields->$tracker_field_name = '';
                                            $status_icon = '';
                                            $status_class = '';
                                        }
                                        
                                        $overflow_text = $tracker_field_meta->type == 'table' ? true : false;
                                        
                                        ?>

                                        <?php
                                        if ($this->session->userdata('permissions') == $permission_level ||
                                                ($this->session->userdata('permissions') != $permission_level &&
                                                ($tracker_field_meta->type !== "textarea" ||
                                                ($tracker_field_meta->type === "textarea" && !empty($office_campaign->tracker_fields->$tracker_field_name))))):
                                            ?>
                                        <tr <?php //if(!empty($status_class)) echo "class=\"$status_class\"";  ?>>

                                        <!-- Indicator Column below  -->
                                            <td class="tracker-field<?php if (isset($tracker_field_meta->indent)) echo " tracker-field-indent" . $tracker_field_meta->indent; ?>">
                                                <a name="tracker_<?php echo $tracker_field_name ?>" class="anchor-point"></a>
                                                <strong>
                                                    <!-- Remove visually-hidden unused info link so as not to confuse visually-challenged users
                                                    <a href="<?php echo site_url('docs') . '#' . $tracker_field_name ?>">
                                                        <span class="glyphicon glyphicon-info-sign"></span>
                                                    </a>
                                                    -->
                                                    <?php if ($this->session->userdata('permissions') == $permission_level && !in_array($tracker_field_name, $tracker_field_tables)) : ?>
                                                        <label for="<?php echo $tracker_field_name; ?>">
                                                    <?php endif; ?>
                                                        <?php echo isset($tracker_field_meta->description) ? $tracker_field_meta->description : $tracker_field_meta->label ?>
                                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                                        </label>
                                                    <?php endif; ?>
                                                </strong>
                                            </td>
                                            
                                            <?php if ($this->session->userdata('permissions') != $permission_level) : ?>
                                                <td>

                                                   <?php

                                                    if (!empty($status_icon) && ($tracker_field_meta->type == "select" || $tracker_field_meta->type == "approval" || $tracker_field_meta->type == "traffic")) {
                                                        echo $status_icon;
                                                    } elseif ($tracker_field_meta->type == "status") {
                                                        if ($office_campaign->tracker_fields->$tracker_field_name === "not-submitted") {
                                                            echo "Not submitted";
                                                        } elseif ($office_campaign->tracker_fields->$tracker_field_name === "on-time") {
                                                            echo "Submitted on time";
                                                        } elseif ($office_campaign->tracker_fields->$tracker_field_name === "late") {
                                                            echo "Submitted Late";
                                                        } elseif ($office_campaign->tracker_fields->$tracker_field_name === "rev-requested") {
                                                            echo "Revision Requested";
                                                        } elseif ($office_campaign->tracker_fields->$tracker_field_name === "approved") {
                                                            echo "Approved";
                                                        }
                                                    } elseif ($tracker_field_meta->type == "table") {
                                                        echo '<em>See below</em>';
                                                    } else {
                                                        if (!empty($office_campaign->tracker_fields->$tracker_field_name)) {
                                                            if (strlen($office_campaign->tracker_fields->$tracker_field_name) < 20) {
                                                                echo $office_campaign->tracker_fields->$tracker_field_name;
                                                            } else {
                                                                $overflow_text = true;
                                                                echo '<em>See below</em>';
                                                            }
                                                        }
                                                    }

                                                    ?>

                                                </td>
                                            <?php endif; ?>

                                            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                                <td>

                                                    <?php if ($tracker_field_meta->type == "select") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Status</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "yes") ? 'selected = "selected"' : '' ?> value="yes">Yes</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "no") ? 'selected = "selected"' : '' ?> value="no">No</option>
                                                        </select>
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "approval") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Status</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "green") ? 'selected = "selected"' : '' ?> value="green">Approved</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "red") ? 'selected = "selected"' : '' ?> value="red">Not Received</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "none") ? 'selected = "selected"' : '' ?> value="none">Not Yet Approved</option>
                                                        </select>
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "grade") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Grade</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "A") ? 'selected = "selected"' : '' ?> value="A">A</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "B") ? 'selected = "selected"' : '' ?> value="B">B</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "C") ? 'selected = "selected"' : '' ?> value="C">C</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "D") ? 'selected = "selected"' : '' ?> value="D">D</option>
                                                        </select>
                                                    <?php endif; ?>


                                                    <?php if ($tracker_field_meta->type == "progress") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Progress</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "progress") ? 'selected = "selected"' : '' ?> value="progress">Progress</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "neutral") ? 'selected = "selected"' : '' ?> value="neutral">Neutral</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "retrogress") ? 'selected = "selected"' : '' ?> value="retrogress">Retrogress</option>
                                                        </select>
                                                    <?php endif; ?>


                                                    <?php if ($tracker_field_meta->type == "traffic") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Status</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "red") ? 'selected = "selected"' : '' ?> value="red">Red</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "yellow") ? 'selected = "selected"' : '' ?> value="yellow">Yellow</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "green") ? 'selected = "selected"' : '' ?> value="green">Green</option>
                                                        </select>
                                                    <?php endif; ?>


                                                    <?php if ($tracker_field_meta->type == "status") : ?>
                                                        <select name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>">
                                                            <option value="" <?php echo (empty($office_campaign->tracker_fields->$tracker_field_name)) ? 'selected = "selected"' : '' ?>>Select Status</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "not-submitted") ? 'selected = "selected"' : '' ?> value="not-submitted">Not Submitted</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "on-time") ? 'selected = "selected"' : '' ?> value="on-time">Submitted on Time</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "late") ? 'selected = "selected"' : '' ?> value="late">Submitted Late</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "rev-requested") ? 'selected = "selected"' : '' ?> value="rev-requested">Revision Requested</option>
                                                            <option <?php echo ($office_campaign->tracker_fields->$tracker_field_name == "approved") ? 'selected = "selected"' : '' ?> value="approved">Approved</option>
                                                        </select>
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "integer") : ?>
                                                        <input type="number" name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>" value="<?php echo $office_campaign->tracker_fields->$tracker_field_name; ?>" min="0" step="1">
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "percent") : ?>
                                                        <input type="number" name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>" value="<?php echo $office_campaign->tracker_fields->$tracker_field_name; ?>" min="0" step="1">%
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "url") : ?>
                                                        <input type="url" name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>" value="<?php echo htmlentities($office_campaign->tracker_fields->$tracker_field_name); ?>">
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "string") : ?>
                                                        <input type="text" name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>" value="<?php echo htmlentities($office_campaign->tracker_fields->$tracker_field_name); ?>" maxlength="<?php if (isset($tracker_field_meta->maxlength)) echo $tracker_field_meta->maxlength; ?>">
                                                    <?php endif; ?>
                                                        
                                                    <?php if ($tracker_field_meta->type == "date") : ?>
                                                        <input type="text" pattern="[0-9]{4}\-[0-9]{2}\-[0-9]{2}" title='Date format: YYYY-MM-DD' class="datepicker" name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>" value="<?php echo htmlentities($office_campaign->tracker_fields->$tracker_field_name); ?>" maxlength="<?php if (isset($tracker_field_meta->maxlength)) echo $tracker_field_meta->maxlength; ?>">
                                                    <?php endif; ?>

                                                    <?php if ($tracker_field_meta->type == "textarea") : ?>
                                                        <textarea name="<?php echo $tracker_field_name ?>" id="<?php echo $tracker_field_name ?>" cols="<?php echo isset($tracker_field_meta->cols) ? $tracker_field_meta->cols : 80; ?>" rows="<?php echo isset($tracker_field_meta->rows) ? $tracker_field_meta->rows : 5; ?>" maxlength="<?php echo isset($tracker_field_meta->maxlength) ? $tracker_field_meta->maxlength : 9999; ?>"><?php echo $office_campaign->tracker_fields->$tracker_field_name; ?></textarea>
                                                    <?php endif; ?>
                                                        
                                                    <?php if ($tracker_field_meta->type == "table") : ?>
                                                        <em>See below</em>
                                                    <?php endif; ?>

                                                </td>
                                            <?php endif; ?>

                                            <td>

                                            <?php if (array_search($tracker_field_name, $crawl_details) !== false): ?>

                                                        <a href="#<?php echo $tracker_field_name ?>">Crawl details</a>

                                            <?php endif; ?>

                                            </td>

                                            <!-- NOTES
                                            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                                <td>
                                                    <a class="btn btn-xs btn-default collapsed pull-right" href="#note-expander-<?php echo $tracker_field_name ?>" data-parent="note-expander-parent" data-toggle="collapse">
                                                        Notes
                                                    </a>
                                                </td>
                                            <?php endif; ?>
                                            -->
                                        </tr>
                                    <?php endif; ?>


                                    <?php if (isset($overflow_text) && $overflow_text): ?>
                                        <tr>
                                            <td colspan="3" class="overflow-row">
                                                <?php echo $office_campaign->tracker_fields->$tracker_field_name; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                        <tr>
                                            <td colspan="3" class="hidden-row">
                                                <div class="edit-toggle collapse container form-group" id="note-expander-<?php echo $tracker_field_name ?>">

                                                    <?php
                                                    $note_field = "note_$tracker_field_name";

                                                    $note_data = (!empty($notes[$note_field])) ? $notes[$note_field] : '';

                                                    if (!empty($notes[$note_field])) {
                                                        $note_data = $notes[$note_field];
                                                    } else {
                                                        $note_data = $note_model;
                                                    }
                                                    ?>

                                                    <div class="edit-area"><?php echo $note_data->current->note_html; ?></div>
                                                    <div class="edit-raw hidden" data-fieldname="note_<?php echo $tracker_field_name ?>"><?php echo $note_data->current->note; ?></div>

                                                    <?php if (!empty($note_data->current->date) && !empty($note_data->current->author)): ?>
                                                        <div class="note-metadata">
                                                            Lasted edited on <?php echo $note_data->current->date; ?> by <?php echo $note_data->current->author; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                                        <button class="btn btn-primary edit-button pull-right" type="button">Edit</button>
                                                    <?php endif; ?>


                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php reset($tracker_model);
                                endforeach; ?>

                            </table>

                        <?php if($section_abbreviation == 'gr') include 'recommendation_detail.php'; ?>

                        </div>

                    <?php endforeach; ?>

                </div>




