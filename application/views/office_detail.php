<?php $page_title = $office->name; ?>

<?php include 'header_meta_inc_view.php'; ?>

<script src="<?php echo site_url('js/vendor/raphael-min.js') ?>"></script>
<script src="<?php echo site_url('js/vendor/g.raphael.js') ?>"></script>
<script src="<?php echo site_url('js/vendor/g.pie.js') ?>"></script>
<script src="<?php echo site_url('js/vendor/morris.min.js') ?>"></script>
<script src="<?php echo site_url('js/vendor/bootstrap-datepicker.js') ?>"></script>

<link href="<?php echo site_url('css/morris.css') ?>" rel="stylesheet">
<link href="<?php echo site_url('css/datepicker.css') ?>" rel="stylesheet">

<?php include 'header_inc_view.php'; ?>

<?php include 'office_table_inc_view.php'; ?>

<?php $permission_level = 'admin' ?>


<div class="container">
    <!-- Example row of columns -->
    <div class="row">

        <div>

            <?php if ($this->session->flashdata('outcome') && $this->session->flashdata('status')): ?>
                <p class="form-flash bg-<?php echo $this->session->flashdata('outcome'); ?>"><?php echo $this->session->flashdata('status'); ?></p>
            <?php endif; ?>

            <h2><?php echo $office->name ?> - <?php echo $milestone->milestones[$milestone->selected_milestone]; ?> - <?php echo date("F jS Y", strtotime($milestone->selected_milestone)) ?></h2>

            <div><a href="<?php echo $office->url ?>"><?php echo $office->url ?></a></div>

            <div><?php echo $office->notes ?></div>

            <?php if (!empty($office->parent_office_id)): ?>
                <div class="hidden"><a href="<?php echo site_url('office/') . $office->parent_office_id; ?>">Parent Office</a></div>
            <?php endif; ?>

        </div>

        <?php if (!empty($office_campaign)): ?>

            <?php
            if (!empty($office_campaign->bureaudirectory_status)) {
                $office_campaign->bureaudirectory_status = json_decode($office_campaign->bureaudirectory_status);
            }

            if (!empty($office_campaign->governanceboard_status)) {
                $office_campaign->governanceboard_status = json_decode($office_campaign->governanceboard_status);
            }

            if (!empty($office_campaign->tracker_fields)) {
                $office_campaign->tracker_fields = json_decode($office_campaign->tracker_fields);
            }

            if (!empty($office_campaign->bureaudirectory_status)) {
                $office_campaign->tracker_fields->pa_bureau_it_leadership_table = getBureauITLeadershipTable($config['archive_dir'], (!empty($office->parent_office_id) ? $office->parent_office_id : $office->id), $office_campaign, $office->agencyCode, $this->db);
            }

            if (!empty($office_campaign->governanceboard_status)) {
                $office_campaign->tracker_fields->pa_cio_governance_board_table = getGovernanceBoardTable($config['archive_dir'], (!empty($office->parent_office_id) ? $office->parent_office_id : $office->id), $office_campaign, $office->agencyCode, $this->db);
            }

            if (!empty($office_campaign->tracker_status)) {
                $office_campaign->tracker_status = json_decode($office_campaign->tracker_status);
            }

            $crawl_details = array(
                'pa_bureau_it_leadership',
                'pa_bureau_it_leaders',
                'pa_key_bureau_it_leaders',
                'pa_political_appointees',
                'pa_bureau_it_leadership_link',
                'pa_cio_governance_board',
                'pa_mapped_to_program_inventory',
                'pa_cio_governance_board_link',
                'pa_it_policy_archive',
                'pa_it_policy_archive_files',
                'pa_it_policy_archive_filenames',
                'pa_it_policy_archive_link',
                'gr_open_gao_recommendations'
            );

            $active_section = (!empty($selected_category)) ? $selected_category : 'cb';
            ?>


            <?php /* if ($this->session->userdata('permissions') == $permission_level): ?>

              <?php if(!empty($office_campaign->contact_email)): ?>
              <div>Contact: <a href="mailto:<?php echo $office_campaign->contact_email; ?>"><?php echo $office_campaign->contact_email; ?></a></div>
              <?php else:?>
              <div class="bg-danger">No Data Lead listed!</div>
              <?php endif;?>

              <?php endif; */ ?>


            <?php if ($milestone->selected_milestone == $milestone->current): ?>
                <p class="form-flash text-danger bg-danger"><strong>Current Milestone:</strong> The milestone selected is still in progress. The Automated Metrics will update daily until the milestone date.</p>
            <?php endif; ?>

            <?php if ($milestone->selected_milestone == $milestone->previous): ?>
                <p class="form-flash text-warning bg-warning"><strong>Previous Milestone:</strong> The milestone selected is the most recently complete one. The Automated Metrics are a snapshot from the milestone date.</p>
            <?php endif; ?>

            <?php /*if (empty($office_campaign->tracker_status->status) OR $office_campaign->tracker_status->status == 'not-started'): ?>
                <p class="form-flash text-danger bg-danger"><strong>OMB Review Has Not Begun:</strong> OMB has not begun reviewing the agency for this milestone. The review will begin after the milestone date.</p>
            <?php endif;*/ ?>

            <?php /*if (!empty($office_campaign->tracker_status->status) && $office_campaign->tracker_status->status == 'in-progress'): ?>
                <p class="form-flash text-warning bg-warning"><strong>OMB Review In Progress:</strong> OMB is currently reviewing the agency for this milestone. This review status indicator will change once the review is complete.</p>
            <?php endif;*/ ?>

            <?php /*if (!empty($office_campaign->tracker_status->status) && $office_campaign->tracker_status->status == 'complete'): ?>
                <p class="form-flash text-success bg-success"><strong>OMB Review Complete:</strong> OMB has completed the agency review for this milestone. Agencies should contact their OMB desk officer if anything looks incorrect.</p>
            <?php endif;*/ ?>


            <ul class="milestone-selector nav nav-pills">
                <li class="dropdown active">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        Selected: <?php echo $milestone->milestones[$milestone->selected_milestone] . ' - ' . date("F jS Y", strtotime($milestone->selected_milestone)); ?> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <?php foreach ($milestone->milestones as $milestone_date => $milestone_name): ?>
                            <li><a href="<?php echo site_url(); ?>offices/detail/<?php echo $office->id; ?>/<?php echo $milestone_date; ?>"><?php echo $milestone_name . ' - ' . date("F jS Y", strtotime($milestone_date)); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>


            <!--
            <a name="general_indicators" class="anchor-point"></a>
            <h3>General Indicators <a class="info-icon" href="<?php echo site_url('docs'); ?>#general_indicators"><span class="glyphicon glyphicon-info-sign"></span></a></h3>
            <p>These indicators are reviewed by the Office of Management and Budget</p>


            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                <form method="post" action="<?php echo site_url(); ?>ciogov/status-review-update" role="form">
            <?php endif; ?>

                <?php /*include 'office_detail_review_status.php';*/ ?>

                <?php if ($this->session->userdata('permissions') == $permission_level) : ?>

                    <input type="hidden" name="status_id" value="<?php echo $office_campaign->status_id; ?>">
                    <input type="hidden" name="office_id" value="<?php echo $office->id; ?>">
                    <input type="hidden" name="milestone" value="<?php echo $milestone->selected_milestone; ?>">

                    <button type="submit" class="btn btn-success" name="review_status_submit">Update</button>
                <?php endif; ?>

            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                </form>
            <?php endif; ?>
            -->


            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                <form method="post" action="<?php echo site_url(); ?>ciogov/status-update" role="form">
            <?php endif; ?>

               <?php include 'office_detail_general_notes.php'; ?>

               <?php include 'office_detail_tracker.php'; ?>

                <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                    <?php if (!empty($office_campaign->tracker_status)): ?>
                        <input type="hidden" name="reviewer_email" value="<?php if (!empty($office_campaign->tracker_status->reviewer_email)) echo $office_campaign->tracker_status->reviewer_email ?>">
                        <input type="hidden" name="status" value="<?php if (!empty($office_campaign->tracker_status->status)) echo $office_campaign->tracker_status->status ?>">
                    <?php endif; ?>
                    <input type="hidden" name="office_id" value="<?php echo $office->id; ?>">
                    <input type="hidden" name="status_id" value="<?php echo $office_campaign->status_id; ?>">
                    <input type="hidden" name="milestone" value="<?php echo $milestone->selected_milestone; ?>">
                <?php endif; ?>

            <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                </form>
            <?php endif; ?>

            <?php include 'office_detail_automated_metrics.php'; ?>

        <?php endif; ?>

        <?php
        if (!empty($child_offices)) {
            status_table('Sub Agencies', $child_offices);
        }
        ?>

    </div>

</div>

<hr>

<?php include 'footer.php'; ?>
