<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Campaign extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->helper('api');
        $this->load->helper('url');
        $this->load->helper('security');

        // Determine the environment we're run from for debugging/output
        if (php_sapi_name() == 'cli') {
            if (isset($_SERVER['TERM'])) {
                $this->environment = 'terminal';
            } else {
                $this->environment = 'cron';
            }
        } else {
            $this->environment = 'server';
        }


    }


    /**
    * Index Page for this controller.
    *
    * Maps to the following URL
    *        http://example.com/index.php/welcome
    *    - or -
    *        http://example.com/index.php/welcome/index
    *    - or -
    * Since this controller is set as the default controller in
    * config/routes.php, it's displayed at http://example.com/
    *
    * So any other public methods not prefixed with an underscore will
    * map to /index.php/welcome/<method_name>
    * @see http://codeigniter.com/user_guide/general/urls.html
    */
    public function index()
    {


    }

    public function convert($orgs = null, $geospatial = null, $harvest = null, $from_export = null)
    {
        $this->load->model('campaign_model', 'campaign');

        $orgs = (!empty($orgs)) ? $orgs : $this->input->get('orgs', TRUE);
        $geospatial = (!empty($geospatial)) ? $geospatial : $this->input->get('geospatial', TRUE);
        $harvest = (!empty($harvest)) ? $harvest : $this->input->get('harvest', TRUE);
        $from_export = (!empty($from_export)) ? $from_export : $this->input->get('from_export', TRUE);

        if (!$orgs) {
            show_error('Invalid orgs parameter, cannot be empty', 400);
            return;
        }

        $row_total = 100;
        $row_count = 0;

        $row_pagesize = 100;
        $raw_data = array();

        while ($row_count < $row_total) {
            $result = $this->campaign->get_datagov_json($orgs, $geospatial, $row_pagesize, $row_count, true, $harvest);

            if (!empty($result->result)) {

                $row_total = $result->result->count;
                $row_count = $row_count + $row_pagesize;

                $raw_data = array_merge($raw_data, $result->result->results);

                if ($from_export == 'true') break;

            } else {
            break;
        }

    }

    if (!empty($raw_data)) {

        $json_schema = $this->campaign->datajson_schema();
        $datajson_model = $this->campaign->schema_to_model($json_schema->items->properties);

        $convert = array();
        foreach ($raw_data as $ckan_data) {
            $model = clone $datajson_model;
            $convert[] = $this->campaign->datajson_crosswalk($ckan_data, $model);
        }

        if ($this->environment == 'terminal') {
            $filepath = 'export.json';

            echo 'Creating file at ' . $filepath . PHP_EOL . PHP_EOL;

            $export_file = fopen($filepath, 'w');
            fwrite($export_file, json_encode($convert, JSON_PRETTY_PRINT));
            fclose($export_file);
        } else {

            header('Content-type: application/json');
            print json_encode($convert, JSON_PRETTY_PRINT);
            exit;
        }

    } else {

        if ($this->environment == 'terminal') {
            echo 'No results found for ' . $orgs;
        } else {
            header('Content-type: application/json');
            print json_encode(array("error" => "no results"));
            exit;
        }
    }

    }

    public function csv_to_json() {
		$this->load->helper('url');
		$this->load->view('csv_upload_removed');
    }

    private function do_upload($field_name = null)
    {

        if (!$this->upload->do_upload($field_name)) {
            return false;
        } else {
            return true;
        }
    }

    public function csv_field_mapper($headings, $datajson_model, $inception = false)
    {

        $matched = array();
        $match = false;
        $count = 0;
        $selected = '';

        ob_start();
        foreach ($headings as $field) {
            $field = html_escape($field);
            ?>
            <div class="form-group">
            <label class="col-sm-2" for="<?php echo $field; ?>"><?php echo $field; ?></label>
            <div class="col-sm-3">
            <select id="<?php echo $field; ?>" type="text" name="mapping[<?php echo $count; ?>]">
            <option value="null">Select a corresponding field</option>
            <?php //var_dump($datajson_model); ?>
            <?php foreach ($datajson_model as $pod_field => $pod_value): ?>
                <?php

                if (is_object($pod_value) OR (is_array($pod_value) && count($pod_value) > 0)) {

                    foreach ($pod_value as $parent_field => $pod_value_child) {

                        if (is_object($pod_value_child)) {
                            foreach ($pod_value_child as $child_field => $child_value) {

                                if (strtolower(trim($field)) == strtolower(trim("$pod_field.$child_field")) && !$matched[$field]) {
                                    $selected = 'selected="selected"';
                                    $match = true;
                                } else {
                                    $selected = '';
                                }
                                ?>

                                <option value="<?php echo "$pod_field.$child_field" ?>" <?php echo $selected ?>><?php echo $pod_field . ' - ' . $child_field ?></option>

                                <?php
                                if ($match) {
                                    $match = false;
                                    $selected = '';
                                    $matched[$field] = true;
                                }

                            }
                        } else {
                            if (strtolower(trim($field)) == strtolower(trim("$pod_field.$parent_field")) && !$matched[$field]) {
                                $selected = 'selected="selected"';
                                $match = true;
                            } else {
                                $selected = '';
                            }
                            ?>

                            <option value="<?php echo "$pod_field.$parent_field" ?>" <?php echo $selected ?>><?php echo $pod_field . ' - ' . $parent_field ?></option>

                            <?php
                            if ($match) {
                                $match = false;
                                $selected = '';
                                $matched[$field] = true;
                            }
                        }

                    }


                } else {

                    if (strtolower(trim($field)) == strtolower(trim($pod_field)) && !isset($matched[$field])) {
                        $selected = 'selected="selected"';
                        $match = true;
                    } else {
                        $selected = '';
                    }


                }

                ?>
                <option value="<?php echo $pod_field ?>" <?php echo $selected ?>><?php echo $pod_field ?></option>
                <?php

                if ($match) {
                    $match = false;
                    $selected = '';
                    $matched[$field] = true;
                }


                ?>
                <?php endforeach; ?>
                </select>
                </div>
                <div class="col-sm-2">
                <?php
                if (!($match OR (isset($matched[$field]) && isset($matched[$field])))) echo '<span class="text-danger">No match found</span>';
                $match = false;
                $count++;
                ?>
                </div>
                </div>
                <?php
                reset($datajson_model);
            }

            return ob_get_clean();

        }

        public function schema_map_filter($field, $value, $schema = null)
        {

            if (is_json($value)) {
                $value = json_decode($value);
            } else if ($field == 'keyword' |
            $field == 'language' |
            $field == 'references' |
            $field == 'theme' |
            $field == 'programCode' |
            $field == 'bureauCode'
            ) {
                $value = str_getcsv($value);
            } else if ($field == 'dataQuality' && !empty($value)) {
                $value = (bool)$value;
            }

            if (is_array($value)) {
                $value = array_map("make_utf8", $value);
                $value = array_map("trim", $value);
                $value = array_filter($value); // removes any empty elements in an array
                $value = array_values($value); // ensures array_filter doesn't create an associative array
            } else if (is_string($value)) {
                $value = trim($value);
                $value = make_utf8($value);
            }

            $value = (!is_bool($value) && empty($value)) ? null : $value;

            return $value;

        }

        public function csv($orgs = null)
        {
            $this->load->model('campaign_model', 'campaign');

            if ($orgs == 'all') {
                $orgs = '*';
            }

            if (empty($orgs)) {
                $orgs = $this->input->get('orgs', TRUE);
            }

            if (empty($orgs)) {
                $geospatial = $this->input->get('geospatial', TRUE);
            } else {
                $geospatial = false;
            }

            // if we didn't get any requests, bail
            if (empty($orgs)) {
                show_404($orgs, false);
                exit;
            }

            $row_total = 100;
            $row_count = 0;

            $row_pagesize = 500;
            $raw_data = array();

            while ($row_count < $row_total) {
                $result = $this->campaign->get_datagov_json($orgs, $geospatial, $row_pagesize, $row_count, true);

                if (!empty($result)) {
                    $row_total = $result->result->count;
                    $row_count = $row_count + $row_pagesize;

                    if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                        echo 'Exporting ' . $row_count . ' of ' . $row_total . PHP_EOL;
                    }

                    $raw_data = array_merge($raw_data, $result->result->results);
                } else {
                break;
            }

        }

        // if we didn't get any data, bail
        if (empty($raw_data)) {
            show_404($orgs, false);
            exit;
        }


        // use data.json model
        $json_schema = $this->campaign->datajson_schema();
        $datajson_model = $this->campaign->schema_to_model($json_schema->items->properties);

        $csv_rows = array();
        foreach ($raw_data as $ckan_data) {

            $special_extras = $this->special_extras($ckan_data);

            $model = clone $datajson_model;
            $csv_row = $this->campaign->datajson_crosswalk($ckan_data, $model);

            $csv_row->accessURL = array();
            $csv_row->format = array();
            foreach ($csv_row->distribution as $distribution) {
                $csv_row->accessURL[] = $distribution->accessURL;
                $csv_row->format[] = $distribution->format;
            }

            foreach ($csv_row as $key => $value) {

                if (empty($value) OR is_object($value) == true OR (is_array($value) == true && !empty($value[0]) && is_object($value[0]) == true)) {
                    $csv_row->$key = null;
                }

                if (is_array($value) == true && !empty($value[0]) && is_object($value[0]) == false) {
                    $csv_row->$key = implode(',', $value);
                }


            }

            $csv_row->_extra_catalog_url = 'http://catalog.data.gov/dataset/' . $csv_row->identifier;
            $csv_row->_extra_communities = $special_extras->groups;
            $csv_row->_extra_communities_categories = $special_extras->group_categories;

            $csv_rows[] = (array)$csv_row;
        }

        //header('Content-type: application/json');
        //print json_encode($csv_rows);
        //exit;


        $headings = array_keys($csv_rows[0]);

        // Open the output stream
        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
            $filepath = realpath('./csv/output.csv');
            $fh = fopen($filepath, 'w');
            echo 'Attempting to save csv to ' . $filepath . PHP_EOL;
        } else {
            $fh = fopen('php://output', 'w');
        }


        // Start output buffering (to capture stream contents)
        ob_start();
        fputcsv($fh, $headings);

        // Loop over the * to export
        if (!empty($csv_rows)) {
            foreach ($csv_rows as $row) {
                fputcsv($fh, $row);
            }
        }

        if ($this->environment !== 'terminal') {
            // Get the contents of the output buffer
            $string = ob_get_clean();
            $filename = 'csv_' . date('Ymd') . '_' . date('His');
            // Output CSV-specific headers

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header('Content-type: text/csv');
            header("Content-Disposition: attachment; filename=\"$filename.csv\";");
            header("Content-Transfer-Encoding: binary");

            exit($string);
        } else {
            echo 'Done' . PHP_EOL;
            exit;
        }


    }


    private function special_extras($ckan_data)
    {

        $special_extras = new stdClass();

        // communities
        $groups = array();
        if (!empty($ckan_data->groups)) {
            foreach ($ckan_data->groups as $group) {
                if (!empty($group->title)) {
                    $groups[] = $group->title;
                    $groups_id[] = $group->id;
                }
            }
        }
        $special_extras->groups = (!empty($groups)) ? implode(',', $groups) : null;

        // community categories
        $group_categories = array();
        if (!empty($groups_id)) {
            foreach ($groups_id as $group_id) {
                $group_category_id = '__category_tag_' . $group_id;

                if (!empty($ckan_data->extras)) {
                    foreach ($ckan_data->extras as $extra) {

                        if ($extra->key == $group_category_id) {
                            $categories = json_decode($extra->value);
                            if (is_array($categories)) {
                                foreach ($categories as $category_name) {
                                    $group_categories[$category_name] = true;
                                }
                            }

                        }
                    }

                }
            }
        }
        if (!empty($group_categories)) {
            $group_categories = array_keys($group_categories);
            $special_extras->group_categories = implode(',', $group_categories);
        } else {
            $special_extras->group_categories = null;
        }


        // formats
        $formats = array();
        if (!empty($ckan_data->resources)) {
            foreach ($ckan_data->resources as $resource) {
                if (!empty($resource->format)) {
                    $formats[] = (string)$resource->format;
                }
            }
        }
        $special_extras->formats = (!empty($formats)) ? implode(',', $formats) : null;


        return $special_extras;
    }


    public function digitalstrategy($id = null)
    {


        $this->load->model('campaign_model', 'campaign');

        $this->db->select('*');
        $this->db->from('offices');
        $this->db->join('datagov_campaign', 'datagov_campaign.office_id = offices.id', 'left');
        $this->db->where('offices.omb_monitored', 'true');
        $this->db->where('offices.no_parent', 'true');

        if (!empty($id) && $id != 'all') {
            $this->db->where('offices.id', $id);
        }

        $this->db->order_by("offices.name", "asc");
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $view_data['digitalstrategy'] = $query->result();
            $query->free_result();

            $this->load->view('digitalstrategy', $view_data);
        } else {
            show_404('digitalgov', false);
        }


    }


    /*
    $id can be all, cfo-act, omb-monitored, or a specific id
    $component can be full-scan, all, datajson, datapage, digitalstrategy, download
    */
    public function status($id = null, $component = null, $selected_milestone = null, $url_override = null)
    {

        // enforce explicit component selection
        if (empty($component)) {
            show_404('status', false);
        }

        if ($component == 'full-scan' || $component == 'all' || $component == 'download') {
            $this->load->helper('file');
        }

        $this->load->model('campaign_model', 'campaign');

        // Determine current milestone
        $milestones = $this->campaign->milestones_model();
        $milestone = $this->campaign->milestone_filter($selected_milestone, $milestones);

        // If it's the first day of a new milestone, finalize last results from previous milestone
        $yesterday = date("Y-m-d", time() - 60 * 60 * 24);
        if ($milestone->previous == $yesterday) {
            $this->finalize_milestone($milestone->previous);
        }

        // Build query for list of offices to update
        $this->db->select('url, id');

        // Filter for certain offices and don't use the long-running unless explicity called
        if ($id == 'cfo-act') {
            $this->db->where('cfo_act_agency', 'true');
            $this->db->where('long_running', 'false');
        } else if ($id == 'omb-monitored') {
            $this->db->where('omb_monitored', 'true');
            $this->db->where('long_running', 'false');
        } else if ($id == 'long-running') {
            $this->db->where('long_running', 'true');
        }

        if (is_numeric($id)) {
            $this->db->where('id', $id);
        }

        $query = $this->db->get('offices');

        if ($query->num_rows() > 0) {
            $offices = $query->result();

            if (count($offices) > 1) {
                $offices = $this->campaign->prioritize_crawl($offices, $milestone->current);
            }

            $this->status_offices($offices, $component, $selected_milestone, $url_override);

            // Close file connections that are still open
            if (is_resource($this->campaign->validation_log)) {
                fclose($this->campaign->validation_log);
            }
        }

        if (!empty($id) && $this->environment != 'terminal' && $this->environment != 'cron') {
            $this->load->helper('url');
            redirect('/offices/detail/' . $id, 'location');
        }

    }

    private function status_offices($offices, $component, $selected_milestone, $url_override) {

        /*
        * Since we're dealing with externally-hosted content, let's see if we can use sub-processes to
        * guard against anything that might crashes the main process (eg OOM) and halt the crawl.
        */

        if(!function_exists('pcntl_fork')) {

            # The functions from the pcntl extension are unavailable...
            # Do all crawls within this process and hope for the best.
            foreach ($offices as $office) {
                $this->status_single_office($office, $component, $selected_milestone, $url_override);
            }

        } else {

            /*
            * We don't want child processes to have access to the parent's DB connection descriptor
            * or they'll close it down when they exit. So before we start forking, we close it
            * pre-emptively here.
            */
            $this->db->close();

            foreach ($offices as $office) {

                $pid = pcntl_fork();
                if ($pid == -1) {

                    die('could not fork');

                } else if ($pid) {

                    // This logic is only hit in the PARENT process

                    $status = null;
                    log_message('debug', "Waiting on child process ".$pid."\n");
                    pcntl_waitpid($pid, $status);

                    // If the process exited normally, show that status, otherwise just use -1 to indicate failure
                    $status = pcntl_wifexited($status) ? pcntl_wexitstatus($status) : "-1";
                    log_message('debug', "Child process ".$pid." exited with status ".$status.".\n");

                    // We handed off responsibility for this office to the child; skip to the next office!
                    continue;

                } else {

                    // This logic is only hit in the CHILD process

                    // The child process needs its own connection to the database to report in when it finishes
                    $this->load->database();

                    // Process the office
                    $this->status_single_office($office, $component, $selected_milestone, $url_override);

                    // Once the child has reported its status, it can end normally.
                    exit(0);

                }

            }

            // We're done with forking, so get the parent connection to the DB restarted
            $this->load->database();
        }

    }

    private function status_single_office($office, $component, $selected_milestone, $url_override)
    {

        // Set current office id
        $this->campaign->current_office_id = $office->id;
        $this->campaign->validation_pointer = 0;

        // initialize update object
        $update = $this->campaign->datagov_model();
        $update->office_id = $office->id;

        $update->crawl_status = 'in_progress';
        $update->crawl_start = gmdate("Y-m-d H:i:s");

        $url = parse_url($office->url);
        $url = $url['scheme'] . '://' . $url['host'];

        if (!empty($selected_milestone)) {
            $update->milestone = $selected_milestone;
        }

        $force_head_shim = false;

        // See if this is a domain where we can't rely on HTTP HEAD responses
        if ($this->config->item('no_http_head')) {

            if (is_array($this->config->item('no_http_head'))) {
                foreach ($this->config->item('no_http_head') as $head_domain) {
                    if (strpos($url, $head_domain)) {
                        $force_head_shim = true;
                    }
                }
            }

        }

        /*
        ################ datapage ################
        */

        if ($component == 'full-scan' || $component == 'all' || $component == 'datapage') {


            // Get status of html /data page
            $page_status_url = $url . '/data';

            log_message('debug', 'Attempting to request ' . $page_status_url . PHP_EOL);

            $page_status = $this->campaign->uri_header($page_status_url);
            $page_status['expected_url'] = $page_status_url;
            $page_status['last_crawl'] = time();

            $update->datapage_status = (!empty($page_status)) ? json_encode($page_status, JSON_PRETTY_PRINT) : null;

            log_message('debug', 'Attempting to set ' . $update->office_id . ' with ' . $update->datapage_status . PHP_EOL . PHP_EOL);

            if ($component == 'datapage') {
                $update->crawl_status = 'current';
                $update->crawl_end = gmdate("Y-m-d H:i:s");
            }

            $update->status_id = $this->campaign->update_status($update);

        }


        /*
        ################ digitalstrategy ################
        */

        if ($component == 'full-scan' || $component == 'all' || $component == 'digitalstrategy' || $component == 'download') {


            // Get status of html /data page
            $digitalstrategy_status_url = $url . '/digitalstrategy.json';

            log_message('debug', 'Attempting to request ' . $digitalstrategy_status_url . PHP_EOL);

            $page_status = $this->campaign->uri_header($digitalstrategy_status_url);
            $page_status['expected_url'] = $digitalstrategy_status_url;
            $page_status['last_crawl'] = time();

            $update->digitalstrategy_status = (!empty($page_status)) ? json_encode($page_status, JSON_PRETTY_PRINT) : null;

            log_message('debug', 'Attempting to set ' . $update->office_id . ' with ' . $update->digitalstrategy_status . PHP_EOL . PHP_EOL);

            if ($component == 'digitalstrategy') {
                $update->crawl_status = 'current';
                $update->crawl_end = gmdate("Y-m-d H:i:s");
            }

            $update->status_id = $this->campaign->update_status($update);

            // download and version this json file.
            if ($component == 'all' || $component == 'download') {
                $digitalstrategy_archive_status = $this->campaign->archive_file('digitalstrategy', $office->id, $digitalstrategy_status_url);


                // If digitalstrategy.json was downloaded successfully, then it was archived to AWS S3, so let's remove local copy
                if (!$this->config->item('use_local_storage') && $digitalstrategy_archive_status && is_file($digitalstrategy_archive_status)) {
                    unlink($digitalstrategy_archive_status);
                }
            }
        }


        /*
        ################ datajson ################
        */

        if ($component == 'full-scan' || $component == 'all' || $component == 'datajson' || $component == 'download') {

            if (empty($url_override)) {
                $expected_datajson_url = $url . '/data.json';
            } else {
                $expected_datajson_url = urldecode($url_override);
            }

            $expected_datajson_url = filter_remote_url($expected_datajson_url);
            if (!$expected_datajson_url) {
                show_error('Not valid data.json URL.', 400);
                return;
            }

            // attempt to break any caching
            $expected_datajson_url_refresh = $expected_datajson_url . '?refresh=' . time();

            log_message('debug', 'Attempting to request ' . $expected_datajson_url . PHP_EOL);

            // Try to force refresh the cache, follow redirects and get headers
            $json_refresh = true;
            $status = $this->campaign->uri_header($expected_datajson_url_refresh, 0, $force_head_shim);

            if (!$status OR $status['http_code'] != 200) {
                $json_refresh = false;
                $status = $this->campaign->uri_header($expected_datajson_url, 0, $force_head_shim);
            }

            //$status['url']          = $expected_datajson_url;
            $status['expected_url'] = $expected_datajson_url;


            $real_url = ($json_refresh) ? $expected_datajson_url_refresh : $expected_datajson_url;


            /*
            ################ download ################
            */
            if ($component == 'full-scan' || $component == 'all' || $component == 'download') {

                if (!empty($url_override)) {
                    if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                        echo 'Skipping download because custom URL was provided' . PHP_EOL;
                    }
                } else if (!($status['http_code'] == 200)) {

                    if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                        echo 'Resource ' . $real_url . ' not available' . PHP_EOL;
                    }

                    return;

                } else {
                    // download and version this data.json file.
                    $datajson_archive_status = $this->campaign->archive_file('datajson', $office->id, $real_url);
                }

                // If data.json was downloaded successfully, then it was archived to AWS S3, so let's remove local copy
                if (!$this->config->item('use_local_storage') && $datajson_archive_status && is_file($datajson_archive_status)) {
                    unlink($datajson_archive_status);
                }
            }

            /*
            ################ datajson ################
            */
            if ($component == 'full-scan' || $component == 'all' || $component == 'datajson') {

                // Save current update status in case things break during json_status
                $update->datajson_status = (!empty($status)) ? json_encode($status, JSON_PRETTY_PRINT) : null;

                log_message('debug', 'Attempting to set ' . $update->office_id . ' with ' . $update->datajson_status . PHP_EOL . PHP_EOL);

                $update->status_id = $this->campaign->update_status($update);

                // Check JSON status
                $status = $this->json_status($status, $real_url, $component);

                // Set correct URL
                if (!empty($status['url'])) {
                    if (strpos($status['url'], '?refresh=')) {
                        $status['url'] = substr($status['url'], 0, strpos($status['url'], '?refresh='));
                    }
                } else {
                    $status['url'] = $expected_datajson_url;
                }

                $status['expected_url'] = $expected_datajson_url;
                $status['last_crawl'] = time();


                if (array_key_exists('schema_errors', $status) && is_array($status['schema_errors']) && !empty($status['schema_errors'])) {
                    $status['error_count'] = count($status['schema_errors']);
                } else if (array_key_exists('schema_errors', $status) && $status['schema_errors'] === false) {
                    $status['error_count'] = 0;
                } else {
                    $status['error_count'] = null;
                }

                $status['schema_errors'] = (!empty($status['schema_errors'])) ? array_slice($status['schema_errors'], 0, 10, true) : null;

                $update->datajson_status = (!empty($status)) ? json_encode($status, JSON_PRETTY_PRINT) : null;
                //$update->datajson_errors = (!empty($status) && !empty($status['schema_errors'])) ? json_encode(array_slice($status['schema_errors'], 0, 10, true)) : null;
                if (!empty($status) && !empty($status['schema_errors'])) unset($status['schema_errors']);


                log_message('debug', 'Attempting to set ' . $update->office_id . ' with ' . $update->datajson_status . PHP_EOL . PHP_EOL);

                $update->crawl_status = 'current';
                $update->crawl_end = gmdate("Y-m-d H:i:s");

                $this->campaign->update_status($update);
            }

        }

    }



    private function finalize_milestone($milestone)
    {
        $this->load->model('campaign_model', 'campaign');
        return $this->campaign->finalize_milestone($milestone);
    }

    private function json_status($status, $real_url = null, $component = null)
    {

        // if this isn't an array, assume it's a urlencoded URI
        if (is_string($status)) {
            $this->load->model('campaign_model', 'campaign');

            $expected_datajson_url = urldecode($status);

            $status = $this->campaign->uri_header($expected_datajson_url);
            $status['url'] = (!empty($status['url'])) ? $status['url'] : $expected_datajson_url;
        }

        $status['url'] = (!empty($status['url'])) ? $status['url'] : $real_url;

        if ($status['http_code'] == 200) {

            $qa = ($this->environment == 'terminal' OR $this->environment == 'cron') ? 'all' : true;

            $validation = $this->campaign->validate_datajson($status['url'], null, null, 'federal', false, $qa, $component);

            if (!empty($validation)) {
                $status['valid_json'] = $validation['valid_json'];
                $status['valid_schema'] = $validation['valid'];
                $status['total_records'] = (!empty($validation['total_records'])) ? $validation['total_records'] : null;

                $status['schema_version'] = (!empty($validation['schema_version'])) ? $validation['schema_version'] : null;

                if (isset($validation['errors']) && is_array($validation['errors']) && !empty($validation['errors'])) {
                    $status['schema_errors'] = $validation['errors'];
                } else if (isset($validation['errors']) && $validation['errors'] === false) {
                    $status['schema_errors'] = false;
                } else {
                    $status['schema_errors'] = null;
                }

                $status['qa'] = (!empty($validation['qa'])) ? $validation['qa'] : null;

                $status['download_content_length'] = (!empty($status['download_content_length'])) ? $status['download_content_length'] : null;
                $status['download_content_length'] = (!empty($validation['download_content_length'])) ? $validation['download_content_length'] : $status['download_content_length'];

            } else {
                // data.json was not valid json
                $status['valid_json'] = false;
            }

        }

        return $status;
    }

    public function status_review_update()
    {

        // Kick them out if they're not allowed here.
        if ($this->session->userdata('permissions') !== 'admin') {
            $this->load->helper('url');
            redirect('/');
            exit;
        }

        $update = (object)$this->input->post(NULL, TRUE);

        $this->load->model('campaign_model', 'campaign');

        $datagov_model_fields = $this->campaign->datagov_model();
        $tracker_review_model = $this->campaign->tracker_review_model();

        $datagov_model_fields->status_id = (!empty($update->status_id)) ? $update->status_id : null;
        $datagov_model_fields->office_id = $update->office_id;
        $datagov_model_fields->milestone = $update->milestone;

        // Set author name with best data available
        $author_full = $this->session->userdata('name_full');
        $author_name = (!empty($author_full)) ? $author_full : $this->session->userdata('username');

        $tracker_review_model->last_editor = $author_name;
        $tracker_review_model->last_updated = date("F j, Y, g:i a T");

        $tracker_review_model->status = $update->status;
        $tracker_review_model->reviewer_email = $update->reviewer_email;

        $datagov_model_fields->tracker_status = json_encode($tracker_review_model, JSON_PRETTY_PRINT);

        // remove blank fields from update
        foreach ($datagov_model_fields as $field => $data) {
            if (empty($data)) unset($datagov_model_fields->$field);
        }

        $this->campaign->update_status($datagov_model_fields);

        $this->session->set_flashdata('outcome', 'success');
        $this->session->set_flashdata('status', 'Status updated');

        $this->load->helper('url');
        redirect('offices/detail/' . $datagov_model_fields->office_id . '/' . $datagov_model_fields->milestone);


    }

    public function status_update()
    {

        // Kick them out if they're not allowed here.
        if ($this->session->userdata('permissions') !== 'admin') {
            $this->load->helper('url');
            redirect('/');
            exit;
        }


        $this->load->model('campaign_model', 'campaign');

        //$datajson 		= ($this->input->post('datajson', TRU E)) ? $this->input->post('datajson', TRUE) : $datajson;

        $update = (object)$this->input->post(NULL, TRUE);

        $datagov_model_fields = $this->campaign->datagov_model();
        $tracker_model_fields = $this->campaign->tracker_model();
        $tracker_review_model = $this->campaign->tracker_review_model();

        // Set author name with best data available
        $author_full = $this->session->userdata('name_full');
        $author_name = (!empty($author_full)) ? $author_full : $this->session->userdata('username');

        // Update tracker status metadata
        $tracker_review_model->last_editor = $author_name;
        $tracker_review_model->last_updated = date("F j, Y, g:i a T");

        $tracker_review_model->status = (!empty($update->status)) ? $update->status : null;
        $tracker_review_model->reviewer_email = (!empty($update->reviewer_email)) ? $update->reviewer_email : null;

        $datagov_model_fields->tracker_status = json_encode($tracker_review_model, JSON_PRETTY_PRINT);


        // add fake field for general notes
        $tracker_model_fields->office_general = null;

        foreach ($tracker_model_fields as $field => $field_meta) {

            $field_name = "note_$field";

            if (!empty($update->$field_name)) {

                $note_data = array("note" => $update->$field_name, "date" => date("F j, Y, g:i a T"), "author" => $author_name);
                $note_data = array("current" => $note_data, "previous" => null);

                $note_data = json_encode($note_data, JSON_PRETTY_PRINT);

                $note = array('note' => $note_data, 'field_name' => $field, 'office_id' => $update->office_id, 'milestone' => $update->milestone);
                $note = (object)$note;
                $this->campaign->update_note($note);
            }

            unset($update->$field_name);
        }

        if (!empty($update->status_id)) {
            $datagov_model_fields->status_id = $update->status_id;
            unset($update->status_id);
        }

        $datagov_model_fields->office_id = $update->office_id;
        unset($update->office_id);

        $datagov_model_fields->milestone = $update->milestone;
        unset($update->milestone);

        $datagov_model_fields->tracker_fields = json_encode($update, JSON_PRETTY_PRINT);

        // remove blank fields from update
        foreach ($datagov_model_fields as $field => $data) {
            if (empty($data)) unset($datagov_model_fields->$field);
        }

        $this->campaign->update_status($datagov_model_fields);

        $this->session->set_flashdata('outcome', 'success');
        $this->session->set_flashdata('status', 'Status updated');


        $this->load->helper('url');
        redirect('offices/detail/' . $datagov_model_fields->office_id . '/' . $datagov_model_fields->milestone);

    }

    public function validate($datajson_url = null, $datajson = null, $headers = null, $schema = null, $output = 'browser')
    {

        $this->load->model('campaign_model', 'campaign');

        $datajson = ($this->input->post('datajson')) ? $this->input->post('datajson') : $datajson;
        $schema = ($this->input->post_get('schema')) ? $this->input->post_get('schema', TRUE) : $schema;

        $datajson_url = ($this->input->post_get('datajson_url')) ? $this->input->post_get('datajson_url', TRUE) : $datajson_url;
        $datajson_url = filter_var($datajson_url, FILTER_SANITIZE_URL);
        $datajson_url = filter_var($datajson_url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
        $output_type = ($this->input->post_get('output')) ? $this->input->post_get('output', TRUE) : $output;

        if ($this->input->post_get('qa')) {
            $qa = $this->input->post_get('qa');
        } else {
            $qa = false;
        }

        if ($qa == 'true') $qa = true;

        if (!empty($_FILES)) {

            $this->load->library('upload');

            if ($this->do_upload('datajson_upload')) {

                $data = $this->upload->data();

                $datajson = file_get_contents($data['full_path']);
                unlink($data['full_path']);

            } else {

                $errors = array("Could not upload file (it may be larger than PHP or application allows)"); // for more details see $this->upload->display_errors()
                $validation = array(
                    'valid_json' => false,
                    'valid' => false,
                    'fail' => $errors
                );
            }
        }

        $return_source = ($output_type == 'browser') ? true : false;

        if ($datajson OR $datajson_url) {
            $validation = $this->campaign->validate_datajson($datajson_url, $datajson, $headers, $schema, $return_source, $qa);
        }


        if (!empty($validation)) {


            if ($output_type == 'browser' && (!empty($validation['source']) || !empty($validation['fail']))) {

                $validate_response = array(
                    'validation' => $validation,
                    'schema' => $schema,
                    'datajson_url' => $datajson_url
                );

                if ($schema == 'federal-v1.1') {
                    $validate_response['schema_v1_permalinks'] = $this->campaign->schema_v1_permalinks();
                }

                $this->load->view('validate_response', $validate_response);

            } else {

                header('Content-type: application/json');
                print json_encode($validation, JSON_PRETTY_PRINT);
                exit;

            }

        } else {
            $this->load->view('validate');
        }

    }

    public function upgrade_schema($schema = 'federal')
    {

        $this->load->model('campaign_model', 'campaign');

        $schema = ($this->input->post_get('schema', TRUE)) ? $this->input->post_get('schema', TRUE) : $schema;

        if (!empty($_FILES)) {

            $this->load->library('upload');

            if ($this->do_upload('datajson_upload')) {

                $data = $this->upload->data();

                $filename = $data['raw_name'];
                $filename = $filename . '_v1-1_converted.json';

                $datajson = file_get_contents($data['full_path']);
                unlink($data['full_path']);

                if ($datajson = json_decode($datajson)) {
                    $json_schema = $this->campaign->datajson_schema('federal-v1.1'); //
                    $datajson_model = $this->campaign->schema_to_model($json_schema->properties);


                    $convert = array();
                    foreach ($datajson as $dataset) {
                        $model = clone $datajson_model->dataset[0];
                        $convert[] = $this->campaign->datajson_schema_crosswalk($dataset, $model);
                    }

                    $context = '@context';
                    $id = '@id';

                    unset($datajson_model->$id);
                    $datajson_model->$context = 'https://project-open-data.cio.gov/v1.1/schema/catalog.jsonld';
                    $datajson_model->conformsTo = 'https://project-open-data.cio.gov/v1.1/schema';
                    $datajson_model->describedBy = 'https://project-open-data.cio.gov/v1.1/schema/catalog.json';

                    $datajson_model->dataset = $convert;


                    // provide json for download
                    header("Pragma: public");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private", false);
                    header('Content-type: application/json');
                    header("Content-Disposition: attachment; filename=\"$filename\";");
                    header("Content-Transfer-Encoding: binary");

                    print json_encode($datajson_model, JSON_PRETTY_PRINT);
                    exit;
                } else {
                    $data = array();
                    $data['errors'] = 'The file was not valid JSON';
                    $this->load->view('upgrade_schema', $data);
                }


            }
        } else {
            $this->load->view('upgrade_schema');
        }

    }


}
