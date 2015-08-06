<!-- GAO Recommendation status tracker detail table -->

        <div id="recommendation-heading" class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo $office_campaign->recommendation_status->expected_url  ?>
                    </div>

                    <table class="table table-striped table-hover">
                        <tr>
                            <th>Expected GAO Recommendation json URL for office</th>
                            <td>
                                <?php if (!empty($office_campaign->recommendation_status->expected_url)): ?>
                                    <?php echo $office_campaign->recommendation_status->expected_url ?>
                                <?php endif; ?>

                                <?php

                                if (!empty($office_campaign->recommendation_status->content_type)) {
                                    if (strpos($office_campaign->recommendation_status->content_type, 'application/json') !== false) {
                                        $mime_color = 'success';
                                    } else {
                                        $mime_color = 'danger';
                                    }
                                } else {
                                    $mime_color = 'danger';
                                }
                                ?>

                            </td>
                        </tr>

                        <tr class="success">
                            <th>Content Type</th>
                            <td>
                                <span class="text-success">
                                    <?php echo $office_campaign->recommendation_status->content_type ?>
                                </span>
                            </td>
                        </tr>
                        <tr class="success">
                            <th>Valid JSON</th>
                            <td>
                                <span class="text-success">Valid</span>
                            </td>
                        </tr>

                        <?php if (!empty($office_campaign->recommendation_status->filetime)): ?>
                            <tr>
                                <th>Last modified</th>
                                <td>
                                    <span>
                                        <?php echo $office_campaign->recommendation_status->filetime ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($office_campaign->recommendation_status->last_crawl)): ?>
                            <tr>
                                <th>Last crawl</th>
                                <td>
                                    <span>
                                        <?php echo $office_campaign->recommendation_status->last_crawl ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </table>
                </div>
