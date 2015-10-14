
                <table class="table">


                    <tr>
                        <th scope="row">Review Status</th>
                        <td>                                
                        <?php
                        if (!empty($office_campaign->tracker_status->status)) {
                            echo $office_campaign->tracker_status->status;
                        }
                        ?>
                        </td>


                        <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                            <td>

                                <select name="status">
                                    <option value="" <?php echo (empty($office_campaign->tracker_status->status)) ? 'selected = "selected"' : '' ?>>Select Status</option>                                
                                    <option <?php echo (!empty($office_campaign->tracker_status) && $office_campaign->tracker_status->status == "not-started") ? 'selected = "selected"' : '' ?> value="not-started">Not Reviewed</option>
                                    <option <?php echo (!empty($office_campaign->tracker_status) && $office_campaign->tracker_status->status == "in-progress") ? 'selected = "selected"' : '' ?> value="in-progress">In Progress</option>
                                    <option <?php echo (!empty($office_campaign->tracker_status) && $office_campaign->tracker_status->status == "complete") ? 'selected = "selected"' : '' ?> value="complete">Review Complete</option>
                                </select>

                            </td>
                        <?php endif; ?>

                    </tr>


                    <tr>
                        <th scope="row">Reviewer</th>
                        <td>                                
                        <?php if (!empty($office_campaign->tracker_status->reviewer_email)) echo $office_campaign->tracker_status->reviewer_email ?>
                        </td>


                        <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                            <td>
                                <input type="text" name="reviewer_email" value="<?php if (!empty($office_campaign->tracker_status->reviewer_email)) echo $office_campaign->tracker_status->reviewer_email ?>">
                            </td>
                        <?php endif; ?>

                    </tr>



                    <tr>
                        <th scope="row">Last Updated</th>
                        <td>
                        <?php if (!empty($office_campaign->tracker_status->last_updated)): ?>
                            <?php echo $office_campaign->tracker_status->last_updated ?>
                            <?php if (!empty($office_campaign->tracker_status->last_editor)) echo ' by ' . $office_campaign->tracker_status->last_editor ?>
                        <?php endif; ?>
                        </td>

                        <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                            <td></td>  
                        <?php endif; ?>                         

                    </tr>


                </table>
