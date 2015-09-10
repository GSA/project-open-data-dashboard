<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Campaign extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->helper('api');
        $this->load->helper('url');
        $this->load->helper('file');

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
     * 		http://example.com/index.php/welcome
     * 	- or -
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index() {

    }

    public function csv_field_mapper($headings, $json_model, $inception = false) {

        $matched = array();
        $match = false;
        $count = 0;
        $selected = '';

        ob_start();
        foreach ($headings as $field) {
            ?>
            <div class="form-group">
                <label class="col-sm-2" for="<?php echo $field; ?>"><?php echo $field; ?></label>
                <div class="col-sm-3">
                    <select id="<?php echo $field; ?>" type="text" name="mapping[<?php echo $count; ?>]">
                        <option value="null">Select a corresponding field</option>
            <?php //var_dump($json_model);  ?>
            <?php foreach ($json_model as $pod_field => $pod_value): ?>
                <?php
                if (is_object($pod_value) OR ( is_array($pod_value) && count($pod_value) > 0)) {
                    if (is_array($pod_value)) {

                        foreach ($pod_value as $pod_value_child) {

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
                        if (!($match OR ( isset($matched[$field]) && isset($matched[$field]))))
                            echo '<span class="text-danger">No match found</span>';
                        $match = false;
                        $count++;
                        ?>
                </div>
            </div>
                        <?php
                        reset($json_model);
                    }

                    return ob_get_clean();
                }

    public function do_upload($field_name = null) {

        if (!$this->upload->do_upload($field_name)) {
            return false;
        } else {
            return true;
        }
    }

    public function csv($orgs = null) {
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
            $result = $this->campaign->get_ciogov_json($orgs, $geospatial, $row_pagesize, $row_count, true);

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
        $json_schema = $this->campaign->json_schema();
        $json_model = $this->campaign->schema_to_model($json_schema->items->properties);

        $csv_rows = array();
        foreach ($raw_data as $ckan_data) {

            $special_extras = $this->special_extras($ckan_data);

            $model = clone $json_model;
            $csv_row = $this->campaign->json_crosswalk($ckan_data, $model);

            $csv_row->accessURL = array();
            $csv_row->format = array();
            foreach ($csv_row->distribution as $distribution) {
                $csv_row->accessURL[] = $distribution->accessURL;
                $csv_row->format[] = $distribution->format;
            }

            foreach ($csv_row as $key => $value) {

                if (empty($value) OR is_object($value) == true OR ( is_array($value) == true && !empty($value[0]) && is_object($value[0]) == true)) {
                    $csv_row->$key = null;
                }

                if (is_array($value) == true && !empty($value[0]) && is_object($value[0]) == false) {
                    $csv_row->$key = implode(',', $value);
                }
            }

            $csv_row->_extra_catalog_url = 'http://catalog.data.gov/dataset/' . $csv_row->identifier;
            $csv_row->_extra_communities = $special_extras->groups;
            $csv_row->_extra_communities_categories = $special_extras->group_categories;

            $csv_rows[] = (array) $csv_row;
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

    private function special_extras($ckan_data) {

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
                    $formats[] = (string) $resource->format;
                }
            }
        }
        $special_extras->formats = (!empty($formats)) ? implode(',', $formats) : null;


        return $special_extras;
    }

    /**
     * $id can be all, cfo-act, or a specific id
     * $component can be full-scan, all, directory, govboards, download
     *
     * crawl_status values:
     * in_progress
     * current
     * final
     * aborted
     * started
     * archive
     *
     * @param string $id
     * @param string $component
     * @param string $selected_milestone
     */
    public function status($id = null, $component = null, $selected_milestone = null) {

        // enforce explicit component selection
        if (empty($component)) {
            show_404('status', false);
        }

        if ($component == 'full-scan' || $component == 'all' || $component == 'download') {
            $this->load->helper('file');
        }

        $this->load->model('campaign_model', 'campaign');

        $this->db->select('url, id');

        // Filter for certain offices
        if ($id == 'cfo-act') {
            $this->db->where('cfo_act_agency', 'true');
        }

        if (is_numeric($id)) {
            $this->db->where('id', $id);
        }

        // Determine current milestone

        $milestones = $this->campaign->milestones_model();
        $milestone = $this->campaign->milestone_filter($selected_milestone, $milestones);


        $query = $this->db->get('offices');

        if ($query->num_rows() > 0) {
            $offices = $query->result();
            
            // make sure to update any defunct, older in_progress records to aborted
            $this->db->where('crawl_status', 'in_progress');
            $this->db->where("(`ciogov_campaign`.`crawl_start` < NOW() - INTERVAL 20 MINUTE)");
            $this->db->update('ciogov_campaign', array('crawl_status' => 'aborted'));

            // check if a crawl is currently underway, if so, return false
            $this->db->select('status_id');
            $this->db->where("(`ciogov_campaign`.`crawl_start` >= NOW() - INTERVAL 20 MINUTE)");
            $this->db->where('crawl_status', 'in_progress');
            $query = $this->db->get('ciogov_campaign');
            if ($query->num_rows() > 0) {
                // abort crawl
                error_log('FOUND ' . $query->num_rows() . ' recent ciogov_campiagn record(s) that are in_progress.  ABORTING CRAWL.');
                return false;
            }

            $this->finalize_prior_milestone($offices, $milestone);

            foreach ($offices as $office) {

                // Set current office id
                $this->campaign->current_office_id = $office->id;
                $this->campaign->validation_pointer = 0;

                // initialize update object
                $update = $this->campaign->ciogov_model();
                $update->office_id = $office->id;

                $update->crawl_status = 'in_progress';
                $update->crawl_start = date("Y-m-d H:i:s");

                $url = parse_url($office->url);
                $url = $url['scheme'] . '://' . $url['host'];

                foreach (glob(config_item('archive_dir') . "/*/" . date("Y-m-d") . "/" . $office->id . "*") as $stale_file) {
                    error_log('unlinking stale crawl file: ' . $stale_file);
                    unlink($stale_file);
                }

                $bureaudirectory_error = $governanceboard_error = $policyarchive_error = false;
                $bureaudirectory_status = $governanceboard_status = $policyarchive_status = array();
                /*
                  ################ bureaudirectory ################
                 */

                if ($component == 'full-scan' || $component == 'all' || $component == 'bureaudirectory' || $component == 'download') {
                    // Get status
                    $expected_url = $url . '/digitalstrategy/bureaudirectory.json';

                    // attempt to break any caching
                    $expected_url_refresh = $expected_url . '?refresh=' . time();

                    if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                        echo 'Attempting to request ' . $expected_url . ' and ' . $expected_url_refresh . PHP_EOL;
                    }

                    // Try to force refresh the cache, follow redirects and get headers
                    $json_refresh = true;
                    $bureaudirectory_status = $this->campaign->uri_header($expected_url_refresh);

                    if (!$bureaudirectory_status OR $bureaudirectory_status['http_code'] != 200) {
                        $json_refresh = false;
                        $bureaudirectory_status = $this->campaign->uri_header($expected_url);
                    }

                    $bureaudirectory_status['expected_url'] = $expected_url;
                    $bureaudirectory_status['last_crawl'] = mktime();

                    $real_url = ($json_refresh) ? $expected_url_refresh : $expected_url;

                    /*
                      ################ download ################
                     */
                    if ($component == 'full-scan' || $component == 'all' || $component == 'download') {

                        if (!($bureaudirectory_status['http_code'] == 200) && !config_item('simulate_office_data')) {
                            if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                                echo 'Resource ' . $real_url . ' not available' . PHP_EOL;
                            }
                            $bureaudirectory_error = true;
                            $bureaudirectory_status['valid_json'] = false;
                            $bureaudirectory_status['tracker_fields'] = $this->track_bureaudirectory(false, $expected_url);
                            $update->bureaudirectory_status = (!empty($bureaudirectory_status)) ? json_encode($bureaudirectory_status) : null;
                            $update->status_id = $this->campaign->update_status($update);
                        }
                        else {
                            // download and version this json file.
                            $bd_archive_status = $this->campaign->archive_file('bureaudirectory', $office->id, $real_url);
                            $bureaudirectory_status = $this->campaign->validate_archive_file_with_schema($bureaudirectory_status, $bd_archive_status, 'bureaudirectory', $real_url);
                            $bureaudirectory_status['tracker_fields'] = $this->track_bureaudirectory($bd_archive_status, $expected_url);
                            $update->bureaudirectory_status = (!empty($bureaudirectory_status)) ? json_encode($bureaudirectory_status) : null;
                            $update->status_id = $this->campaign->update_status($update);
                        }
                    }

                    /*
                      ################ bureaudirectory ################
                     */
                    if (!$bureaudirectory_error && ($component == 'full-scan' || $component == 'all' || $component == 'bureaudirectory') ) {

                        $update->bureaudirectory_status = (!empty($bureaudirectory_status)) ? json_encode($bureaudirectory_status) : null;

                        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                            echo 'Attempting to set ' . $update->office_id . ' with ' . $update->bureaudirectory_status . PHP_EOL . PHP_EOL;
                        }

                        $update->status_id = $this->campaign->update_status($update);

                        // Set correct URL
                        if (!empty($bureaudirectory_status['url'])) {
                            if (strpos($bureaudirectory_status['url'], '?refresh=')) {
                                $bureaudirectory_status['url'] = substr($bureaudirectory_status['url'], 0, strpos($bureaudirectory_status['url'], '?refresh='));
                            }
                        } else {
                            $bureaudirectory_status['url'] = $expected_url;
                        }

                        $bureaudirectory_status['expected_url'] = $expected_url;
                        if (!isset($bureaudirectory_status['last_crawl'])) {
                            $bureaudirectory_status['last_crawl'] = mktime();
                        }


                        if (isset($bureaudirectory_status['schema_errors']) && is_array($bureaudirectory_status['schema_errors']) && !empty($bureaudirectory_status['schema_errors'])) {
                            $bureaudirectory_status['error_count'] = count($bureaudirectory_status['schema_errors']);
                        } else if (isset($bureaudirectory_status['schema_errors']) && $bureaudirectory_status['schema_errors'] === false) {
                            $bureaudirectory_status['error_count'] = 0;
                        } else {
                            $bureaudirectory_status['error_count'] = null;
                        }

                        $bureaudirectory_status['schema_errors'] = (!empty($bureaudirectory_status['schema_errors'])) ? array_slice($bureaudirectory_status['schema_errors'], 0, 10, true) : null;

                        $update->bureaudirectory_status = (!empty($bureaudirectory_status)) ? json_encode($bureaudirectory_status) : null;
                        if (!empty($bureaudirectory_status) && !empty($bureaudirectory_status['schema_errors'])) {
                            unset($bureaudirectory_status['schema_errors']);
                        }

                        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                            echo 'Attempting to set ' . $update->office_id . ' with ' . $update->bureaudirectory_status . PHP_EOL . PHP_EOL;
                        }

                        $update->status_id = $this->campaign->update_status($update);
                    }
                }


                /*
                  ################ governanceboard ################
                 */

                if ($component == 'full-scan' || $component == 'all' || $component == 'governanceboard' || $component == 'download') {

                    // Get status
                    $expected_url = $url . '/digitalstrategy/governanceboards.json';

                    // attempt to break any caching
                    $expected_url_refresh = $expected_url . '?refresh=' . time();

                    if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                        echo 'Attempting to request ' . $expected_url . ' and ' . $expected_url_refresh . PHP_EOL;
                    }

                    // Try to force refresh the cache, follow redirects and get headers
                    $json_refresh = true;
                    $governanceboard_status = $this->campaign->uri_header($expected_url_refresh);

                    if (!$governanceboard_status OR $governanceboard_status['http_code'] != 200) {
                        $json_refresh = false;
                        $governanceboard_status = $this->campaign->uri_header($expected_url);
                    }

                    $governanceboard_status['expected_url'] = $expected_url;

                    $real_url = ($json_refresh) ? $expected_url_refresh : $expected_url;

                    /*
                      ################ download ################
                     */
                    if ($component == 'full-scan' || $component == 'all' || $component == 'download') {

                        if (!($governanceboard_status['http_code'] == 200) && !config_item('simulate_office_data')) {
                            if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                                echo 'Resource ' . $real_url . ' not available' . PHP_EOL;
                            }
                            $governanceboard_error = true;
                            $governanceboard_status['valid_json'] = false;
                            $governanceboard_status['tracker_fields'] = $this->track_governanceboard(false, $expected_url);
                            $update->governanceboard_status = (!empty($governanceboard_status)) ? json_encode($governanceboard_status) : null;
                            $update->status_id = $this->campaign->update_status($update);
                        }
                        else {

                            // download and version this json file.
                            $gb_archive_status = $this->campaign->archive_file('governanceboard', $office->id, $real_url);
                            $governanceboard_status = $this->campaign->validate_archive_file_with_schema($governanceboard_status, $gb_archive_status, 'governanceboard', $real_url);
                            $governanceboard_status['tracker_fields'] = $this->track_governanceboard($gb_archive_status, $expected_url);

                            $update->governanceboard_status = (!empty($governanceboard_status)) ? json_encode($governanceboard_status) : null;
                            $update->status_id = $this->campaign->update_status($update);
                        }
                    }

                    /*
                      ################ governanceboard ################
                     */
                    if (!$governanceboard_error && ($component == 'full-scan' || $component == 'all' || $component == 'governanceboard') ) {

                        $update->governanceboard_status = (!empty($governanceboard_status)) ? json_encode($governanceboard_status) : null;

                        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                            echo 'Attempting to set ' . $update->office_id . ' with ' . $update->governanceboard_status . PHP_EOL . PHP_EOL;
                        }

                        $update->status_id = $this->campaign->update_status($update);

                        // Set correct URL
                        if (!empty($governanceboard_status['url'])) {
                            if (strpos($governanceboard_status['url'], '?refresh=')) {
                                $governanceboard_status['url'] = substr($governanceboard_status['url'], 0, strpos($governanceboard_status['url'], '?refresh='));
                            }
                        } else {
                            $governanceboard_status['url'] = $expected_url;
                        }

                        $governanceboard_status['expected_url'] = $expected_url;
                        if (!isset($governanceboard_status['last_crawl'])) {
                            $governanceboard_status['last_crawl'] = mktime();
                        }


                        if (isset($governanceboard_status['schema_errors']) && is_array($governanceboard_status['schema_errors']) && !empty($governanceboard_status['schema_errors'])) {
                            $governanceboard_status['error_count'] = count($governanceboard_status['schema_errors']);
                        } else if (isset($governanceboard_status['schema_errors']) && $governanceboard_status['schema_errors'] === false) {
                            $governanceboard_status['error_count'] = 0;
                        } else {
                            $governanceboard_status['error_count'] = null;
                        }

                        $governanceboard_status['schema_errors'] = (!empty($governanceboard_status['schema_errors'])) ? array_slice($governanceboard_status['schema_errors'], 0, 10, true) : null;

                        $update->governanceboard_status = (!empty($governanceboard_status)) ? json_encode($governanceboard_status) : null;
                        if (!empty($governanceboard_status) && !empty($governanceboard_status['schema_errors'])) {
                            unset($governanceboard_status['schema_errors']);
                        }

                        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                            echo 'Attempting to set ' . $update->office_id . ' with ' . $update->governanceboard_status . PHP_EOL . PHP_EOL;
                        }

                    }
                }


                /*
                  ################ policyarchive ################
                 */

                if ($component == 'full-scan' || $component == 'all' || $component == 'policyarchive' || $component == 'download') {

                    // Get status
                    $expected_url = $url . '/digitalstrategy/policyarchive.zip';
                    $expected_url_array = array(
                        $url . '/digitalstrategy/policyarchive.zip',
                        $url . '/digitalstrategy/policyarchive.tar',
                        $url . '/digitalstrategy/policyarchive.tar.gz',
                        $url . '/digitalstrategy/policyarchive.tgz'
                    );

                    $real_url = $expected_url_array[0];
                    for ($x = 0; $x < count($expected_url_array); $x++) {
                        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                            echo 'Attempting to request ' . $expected_url_array[$x] . PHP_EOL;
                        }

                        $json_refresh = false;
                        $policyarchive_status = $this->campaign->uri_header($expected_url_array[$x]);

                        $policyarchive_status['expected_url'] = $expected_url_array[$x];

                        if ($policyarchive_status['http_code'] == 200) {
                            $real_url = $expected_url_array[$x];
                            break;
                        }
                    }

                    /*
                      ################ download ################
                     */
                    if ($component == 'full-scan' || $component == 'all' || $component == 'download') {

                        if (!($policyarchive_status['http_code'] == 200) && !config_item('simulate_office_data')) {
                            if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                                echo 'Resource ' . $real_url . ' not available' . PHP_EOL;
                            }
                            $policyarchive_error = true;
                            $policyarchive_status['valid_json'] = false;
                            $policyarchive_status['tracker_fields'] = $this->track_policyarchive(false, $policyarchive_status['expected_url']);
                            $update->policyarchive_status = (!empty($policyarchive_status)) ? json_encode($policyarchive_status) : null;
                            $update->status_id = $this->campaign->update_status($update);
                        }
                        else {

                            // download and version this json file.
                            $pa_archive_status = $this->campaign->archive_file('policyarchive', $office->id, $real_url);
                            $policyarchive_status['tracker_fields'] = $this->track_policyarchive($pa_archive_status, $policyarchive_status['expected_url']);
                            $update->policyarchive_status = (!empty($policyarchive_status)) ? json_encode($policyarchive_status) : null;
                            $update->status_id = $this->campaign->update_status($update);
                        }
                    }

                    /*
                      ################ policyarchive ################
                     */
                    if (!$policyarchive_error && ($component == 'full-scan' || $component == 'all' || $component == 'policyarchive') ) {

                        $update->policyarchive_status = (!empty($policyarchive_status)) ? json_encode($policyarchive_status) : null;

                        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                            echo 'Attempting to set ' . $update->office_id . ' with ' . $update->policyarchive_status . PHP_EOL . PHP_EOL;
                        }

                        $update->status_id = $this->campaign->update_status($update);

                        $pa_archive_status = $this->campaign->archive_file('policyarchive', $office->id, $real_url);
                        $policyarchive_status['tracker_fields'] = $this->track_policyarchive($pa_archive_status, $policyarchive_status['expected_url']);

                        $policyarchive_status['url'] = $real_url;
                        if (!isset($policyarchive_status['last_crawl'])) {
                            $policyarchive_status['last_crawl'] = mktime();
                        }

                        $policyarchive_status['error_count'] = 0;
                        $policyarchive_status['schema_errors'] = null;

                        $update->policyarchive_status = (!empty($policyarchive_status)) ? json_encode($policyarchive_status) : null;
                        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                            echo 'Attempting to set ' . $update->office_id . ' with ' . $update->policyarchive_status . PHP_EOL . PHP_EOL;
                        }

                    }
                }

                $update->crawl_status = 'current';
                $update->crawl_end = date("Y-m-d H:i:s");

                $update->status_id = $this->campaign->update_status($update);


                if (!empty($id) && $this->environment != 'terminal' && $this->environment != 'cron') {
                    $this->load->helper('url');
                    redirect('/offices/detail/' . $id, 'location');
                }
            }

            // Close file connections that are still open
            if (is_resource($this->campaign->validation_log)) {
                fclose($this->campaign->validation_log);
            }

         } // end if $offices

    }


    /**
     * Check if this is the first time we are crawling in the current
     * milestone. If so, set the crawl_status of the last crawl in the
     * prior milestone to 'final';
     *
     * If the last crawl of the prior milestone was already set to final,
     * then nothing to do.
     *
     * We check each office's campaign records individually in case any
     * of the records are in an inconsistent state.
     *
     * TO DO - for efficiency, it would be more efficient to work this status
     * update into the status method processing. This was added as a separate
     * step to minimize the risk of creating bugs in the status method.
     *
     * @param <object> $milestone
     */
    public function finalize_prior_milestone($offices, $milestone)
    {
       if(!$milestone->previous) {
         return;
       }

       foreach($offices as $office) {

         $update = null;
         $update = $this->get_campaign_for_milestone($office, $milestone->previous, 'final');

         if($update) {
           $this->output("finalize_prior_milestone: office {$office->id} ciogov_campaign record already finalized for milestone " . $milestone->previous);
           continue;
         }

         // There should be a record with crawl_status of 'current'
         $update = $this->get_campaign_for_milestone($office, $milestone->previous, 'current');

         if(!$update) {
          // Check for any valid record in the prior milestone
          $update = $this->get_campaign_for_milestone($office, $milestone->previous, 'archive');
         }

         // If we didn't find any valid record in the prior milestone, create one.
         if(!$update) {
           $now = date('Y-m-d H:i:s');
           $update->tracker_fields = $this->campaign->seed_first_tracker_fields();
           $update->office_id = $office->id;
           $update->milestone = $milestone->previous;
           $update->crawl_status = 'final';
           $update->crawl_start = $now;
           $update->crawl_end = $now;
           $this->db->insert('ciogov_campaign', $update);
           $this->output("finalize_prior_milestone: office {$office->id} missing valid ciogov_campaign record for milestone " . $milestone->previous . " finalized record inserted");
         }
         else {
           $this->db->where('status_id', $update->status_id);
           $this->db->update('ciogov_campaign', array('crawl_status' => 'final'));
           $this->output("finalize_prior_milestone: office {$office->id} ciogov_campaign record {$update->status_id} finalized for milestone " . $milestone->previous);
         }
      }
    }


    /**
     * Find the most recent campaign record for the given office, milestone,
     * and status.
     *
     * @param <object> $office
     * @param <date> $milestoneDate
     * @param <string> $status
     * @returns <object>
     */
    public function get_campaign_for_milestone($office, $milestoneDate, $status)
    {
      $this->db->select('status_id, milestone, crawl_status, crawl_start');
      $this->db->where('milestone', $milestoneDate);
      $this->db->where('office_id', $office->id);
      $this->db->where('crawl_status', $status);
      $this->db->order_by('crawl_start', 'desc');
      $this->db->limit(1);
      $query = $this->db->get('ciogov_campaign');

      $campaign = null;
      if ($query->num_rows() > 0) {
        $campaigns = $query->result();
        $campaign = $campaigns[0];
      }

      return $campaign;
    }

    public function json_status($status, $real_url = null, $component = null) {
        error_log('DEFUNCT? json_status');

        // if this isn't an array, assume it's a urlencoded URI
        if (is_string($status)) {
            $this->load->model('campaign_model', 'campaign');

            $expected_url = urldecode($status);

            $status = $this->campaign->uri_header($expected_url);
            $status['url'] = (!empty($status['url'])) ? $status['url'] : $expected_url;
        }

        $status['url'] = (!empty($status['url'])) ? $status['url'] : $real_url;

        if ($status['http_code'] == 200) {

            $qa = ($this->environment == 'terminal' OR $this->environment == 'cron') ? 'all' : true;

            $validate_component = 'validate_' . $component;
            /**
             * TO DO - call this when we have real agency data at the expected url
             *
             * This will not be executed until the agencies provide actual real, valid urls.
             * Since the urls are not valid, the http_code is 404 and this block is not executed.
             *
             * This calls validate_bureaudirectory or validate_governanceboard methods in campaign_model
             * We can't call this until agencies provide valid data at expected urls.
             * For now, we are going to validate the example data after the download is complete.
             */
            $validation = $this->campaign->$validate_component($status['url'], null, null, 'federal', false, $qa, $component);

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
                // data was not valid json
                $status['valid_json'] = false;
            }
        }

        return $status;
    }

    public function status_review_update() {

        // Kick them out if they're not allowed here.
        if ($this->session->userdata('permissions') !== 'admin') {
            $this->load->helper('url');
            redirect('/');
            exit;
        }

        $update = (object) $this->input->post(NULL, TRUE);

        $this->load->model('campaign_model', 'campaign');

        $ciogov_model_fields = $this->campaign->ciogov_model();
        $tracker_review_model = $this->campaign->tracker_review_model();

        $ciogov_model_fields->status_id = (!empty($update->status_id)) ? $update->status_id : null;
        $ciogov_model_fields->office_id = $update->office_id;
        $ciogov_model_fields->milestone = $update->milestone;

        // Set author name with best data available
        $author_full = $this->session->userdata('name_full');
        $author_name = (!empty($author_full)) ? $author_full : $this->session->userdata('username');

        $tracker_review_model->last_editor = $author_name;
        $tracker_review_model->last_updated = date("F j, Y, g:i a T");

        $tracker_review_model->status = $update->status;
        $tracker_review_model->reviewer_email = $update->reviewer_email;

        $ciogov_model_fields->tracker_status = json_encode($tracker_review_model);

        // remove blank fields from update
        foreach ($ciogov_model_fields as $field => $data) {
            if (empty($data))
                unset($ciogov_model_fields->$field);
        }

        $this->campaign->update_status($ciogov_model_fields);

        $this->session->set_flashdata('outcome', 'success');
        $this->session->set_flashdata('status', 'Status updated');

        $this->load->helper('url');
        redirect('offices/detail/' . $ciogov_model_fields->office_id . '/' . $ciogov_model_fields->milestone);
    }

    public function status_update() {

        // Kick them out if they're not allowed here.
        if ($this->session->userdata('permissions') !== 'admin') {
            $this->load->helper('url');
            redirect('/');
            exit;
        }


        $this->load->model('campaign_model', 'campaign');

        $update = (object) $this->input->post(NULL, TRUE);

        $ciogov_model_fields = $this->campaign->ciogov_model();
        $tracker_model_fields = $this->campaign->tracker_model($update->milestone);
        $tracker_review_model = $this->campaign->tracker_review_model();

        // Set author name with best data available
        $author_full = $this->session->userdata('name_full');
        $author_name = (!empty($author_full)) ? $author_full : $this->session->userdata('username');

        // Update tracker status metadata
        $tracker_review_model->last_editor = $author_name;
        $tracker_review_model->last_updated = date("F j, Y, g:i a T");

        $tracker_review_model->status = (!empty($update->status)) ? $update->status : null;
        $tracker_review_model->reviewer_email = (!empty($update->reviewer_email)) ? $update->reviewer_email : null;

        $ciogov_model_fields->tracker_status = json_encode($tracker_review_model);



        // add fake field for general notes
        $tracker_model_fields->office_general = null;

        foreach ($tracker_model_fields as $field => $field_meta) {

            $field_name = "note_$field";

            if (!empty($update->$field_name)) {

                $note_data = array("note" => $update->$field_name, "date" => date("F j, Y, g:i a T"), "author" => $author_name);
                $note_data = array("current" => $note_data, "previous" => null);

                $note_data = json_encode($note_data);

                $note = array('note' => $note_data, 'field_name' => $field, 'office_id' => $update->office_id, 'milestone' => $update->milestone);
                $note = (object) $note;
                $this->campaign->update_note($note);
            }

            unset($update->$field_name);
        }

        if (!empty($update->status_id)) {
            $ciogov_model_fields->status_id = $update->status_id;
            unset($update->status_id);
        }

        $ciogov_model_fields->office_id = $update->office_id;
        unset($update->office_id);

        $ciogov_model_fields->milestone = $update->milestone;
        unset($update->milestone);

        $ciogov_model_fields->tracker_fields = json_encode($update);

        // remove blank fields from update
        foreach ($ciogov_model_fields as $field => $data) {
            if (empty($data))
                unset($ciogov_model_fields->$field);
        }

        $this->campaign->update_status($ciogov_model_fields);

        $this->session->set_flashdata('outcome', 'success');
        $this->session->set_flashdata('status', 'Status updated');


        $this->load->helper('url');
        redirect('offices/detail/' . $ciogov_model_fields->office_id . '/' . $ciogov_model_fields->milestone);
    }

    public function validate($url = null, $json = null, $headers = null, $schema = null, $output = 'browser') {

        $this->load->model('campaign_model', 'campaign');

        $json = htmlspecialchars(($this->input->post('json')) ? $this->input->post('json') : $json);
        $schema = htmlspecialchars(($this->input->get_post('schema')) ? $this->input->get_post('schema', TRUE) : $schema);

        $url = htmlspecialchars(($this->input->get_post('url')) ? $this->input->get_post('url', TRUE) : $url);
        $output_type = htmlspecialchars(($this->input->get_post('output')) ? $this->input->get_post('output', TRUE) : $output);

        if (!empty($_FILES)) {

            $this->load->library('upload');

            if ($this->do_upload('json_upload')) {

                $data = $this->upload->data();

                $json = file_get_contents($data['full_path']);
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

        if ($json OR $url) {
            $validation = $this->campaign->validate_json($url, $json, $headers, $schema, $return_source);
        }



        if (!empty($validation)) {

            if ($output_type == 'browser' && (!empty($validation['source']) || !empty($validation['fail']) )) {

                $validate_response = array(
                    'validation' => $validation,
                    'schema' => $schema,
                    'url' => $url
                );

                $this->load->view('validate_response', $validate_response);
            } else {

                header('Content-type: application/json');
                print json_encode($validation);
                exit;
            }
        } else {
            $this->load->view('validate');
        }
    }

    /*
      Crawl each record in a json file and save current version + validation results
     */

    public function version_json($office_id = null) {
        error_log('DEFUNCT? version_json');


        $this->load->model('campaign_model', 'campaign');


        // look up last crawl cycle for this office id
        if (!empty($office_id)) {

            $current_crawl = $this->campaign->json_crawl();
            $current_crawl->office_id = $office_id;

            if ($last_crawl = $this->campaign->get_json_crawl($current_crawl->office_id)) {

                // make sure last crawl completed
                if ($last_crawl->crawl_status == 'completed' && !empty($last_crawl->crawl_end)) {
                    $current_crawl->crawl_cycle = $last_crawl->crawl_cycle + 1;
                } else {
                    // abort
                    $current_crawl->crawl_cycle = $last_crawl->crawl_cycle;
                    $current_crawl->crawl_status = 'aborted';

                    // save crawl status
                    $this->campaign->save_json_crawl($current_crawl);

                    return $current_crawl;
                }
            } else {
                $last_crawl = false;
                $current_crawl->crawl_cycle = 1;
            }


            $current_crawl->crawl_status = 'started';

            // save crawl status
            $this->campaign->save_json_crawl($current_crawl);




            if ($current_crawl->crawl_status == 'started') {

                // check to see if json status is good enough to parse
                // ******** missing code here

                foreach ($metadata_records as $metadata_record) {
                    $this->version_metadata_record($current_crawl);
                }

                // save crawl status
                $this->campaign->save_json_crawl($current_crawl);

                return $current_crawl;
            }
        }
    }

    public function track_bureaudirectory($archive, $url) {

        $tracker_fields = new stdClass();

        $tracker_fields->pa_bureau_it_leadership = false;
        $tracker_fields->pa_bureau_it_leaders = 'Cannot be evaluated';
        $tracker_fields->pa_key_bureau_it_leaders = 'Cannot be evaluated';
        $tracker_fields->pa_political_appointees = 'Cannot be evaluated';
        $tracker_fields->pa_bureau_it_leadership_link = str_replace('.json', '.html', $url);

        if ($archive) {

            // TODO: Validate against schema
            $tracker_fields->pa_bureau_it_leadership = true; // this should be based on the result of the schema check

            if ($tracker_fields->pa_bureau_it_leadership) {

                $tracker_fields->pa_bureau_it_leaders = 0;
                $tracker_fields->pa_key_bureau_it_leaders = 0;
                $tracker_fields->pa_political_appointees = 0;

                $data = json_decode(file_get_contents($archive));
                if ($data) {
                    if (!isset($data->leaders)) {
                        $tracker_fields->pa_bureau_it_leadership = false;
                    }
                    else {
                        foreach ($data->leaders as $leader) {
                            $tracker_fields->pa_bureau_it_leaders++;
                            if (!empty($leader->keyBureauCIO) && $leader->keyBureauCIO === 'Yes') {
                                $tracker_fields->pa_key_bureau_it_leaders++;
                            }
                            if (!empty($leader->typeOfAppointment) && $leader->typeOfAppointment === 'political') {
                                $tracker_fields->pa_political_appointees++;
                            }
                        }
                    }
                }
            }
        }

        return $tracker_fields;
    }

    public function track_governanceboard($archive, $url) {

        $tracker_fields = new stdClass();

        $tracker_fields->pa_cio_governance_board = false;
        $tracker_fields->pa_mapped_to_program_inventory = 'Cannot be evaluated';
        $tracker_fields->pa_ref_program_inventory = 'Cannot be evaluated';
        $tracker_fields->pa_cio_governance_board_link = str_replace('.json', '.html', $url);

        // Get number of records in agency's Federal Program Inventory
        $url = parse_url($url);
        $url = $url['scheme'] . '://' . $url['host'];
        $query = $this->db->query("SELECT * FROM offices WHERE url = ? LIMIT 1", array($url));
        $result = $query->result();
        if (count($result) > 0) {
            $query = $this->db->query("SELECT * FROM refFPIcode WHERE agencyCode = ?", array($result[0]->agencyCode));
            $tracker_fields->pa_ref_program_inventory = count($query->result());
        }

        if ($archive) {

            // TODO: Validate against schema
            $tracker_fields->pa_cio_governance_board = true; // this should be based on the result of the schema check

            if ($tracker_fields->pa_cio_governance_board) {

                $tracker_fields->pa_mapped_to_program_inventory = 0;

                $data = json_decode(file_get_contents($archive));
                if ($data) {
                    if (empty($data->boards)) {
                        $tracker_fields->pa_cio_governance_board = false;
                    }
                    else {
                        foreach ($data->boards as $board) {
                            if (isset($board->programCodeFPI)) {
                                $tracker_fields->pa_mapped_to_program_inventory++;
                            }
                        }
                    }
                }
            }
        }

        return $tracker_fields;

    }

public function track_policyarchive($archive, $url) {

        $tracker_fields = new stdClass();

        $tracker_fields->pa_it_policy_archive = false;
        $tracker_fields->pa_it_policy_archive_files = 'Cannot be evaluated';
        $tracker_fields->pa_it_policy_archive_filenames = 'Cannot be evaluated';
        $tracker_fields->pa_it_policy_archive_link = $url;

       if ($archive) {

            // TODO: Validate against schema
            $tracker_fields->pa_it_policy_archive = true; // this should be based on the result of the schema check

            if ($tracker_fields->pa_it_policy_archive) {

                $tracker_fields->pa_it_policy_archive_files = 0;
                $tracker_fields->pa_it_policy_archive_filenames = "";

                // download and extract file names here
                // save in tracker fields
                if (substr($archive, -3) == "zip") {
                    $za = new ZipArchive();
                    $za->open($archive);

                    $tracker_fields->pa_it_policy_archive_files = $za->numFiles;
                    for( $i = 0; $i < $za->numFiles; $i++ ){
                        $stat = $za->statIndex( $i );
                        $tracker_fields->pa_it_policy_archive_filenames .= ( basename( $stat['name'] ) . PHP_EOL );
                    }
                }
                else if (substr($archive, -3) == "tgz" || substr($archive, -6) == "tar.gz") {
                    $cmd = "tar -ztf $archive";
                    $files = explode(PHP_EOL, trim(shell_exec(escapeshellcmd($cmd))));
                    $tracker_fields->pa_it_policy_archive_files = count($files);
                    $tracker_fields->pa_it_policy_archive_filenames = implode(PHP_EOL,$files);
                }
                else if (substr($archive, -3) == "tar") {
                    $cmd = "tar -tf $archive";
                    $files = explode(PHP_EOL, trim(shell_exec(escapeshellcmd($cmd))));
                    $tracker_fields->pa_it_policy_archive_files = count($files);
                    $tracker_fields->pa_it_policy_archive_filenames = implode(PHP_EOL,$files);
                }

                // MAX environment may not permit opening of compressed files
                if(!$tracker_fields->pa_it_policy_archive_filenames) {
                    $tracker_fields->pa_it_policy_archive_filenames = "Unable to open archive";
                }
            }
        } // end if archive file is found

        return $tracker_fields;
    }

    /**
     * If running in terminal or cron mode, then provide information
     * on the job.
     *
     * @param <string> $text
     */
    public function output($text)
    {
      if ($this->environment == 'terminal' OR $this->environment == 'cron') {
        echo $text . PHP_EOL;
      }
    }

    /**
     * Save GAO Recommendations status
     */
    public function status_update_gao() {

        // Kick them out if they're not allowed here.
        if ($this->session->userdata('permissions') !== 'admin') {
            $this->load->helper('url');
            redirect('/');
            exit;
        }
        
        $this->load->model('campaign_model', 'campaign');
        
        $update = (object) $this->input->post(NULL, TRUE);
        
        $milestone = isset($update->milestone) && !empty($update->milestone) ? $update->milestone : "";
        $baseline = isset($update->baseline) && !empty($update->baseline) ? $update->baseline : "";
        $closed = isset($update->closed) && !empty($update->closed) ? $update->closed : "";

        $note_model_fields = $this->campaign->note_model();
        $note_model_fields->office_id = 0;
        $note_model_fields->milestone = $milestone;
        $note_model_fields->field_name = 'gao_recommendations';
        $note_model_fields->note = json_encode(array('baseline' => $baseline, 'closed' => $closed));
        
        $this->campaign->update_note($note_model_fields);

        $this->session->set_flashdata('outcome_gao', 'success');
        $this->session->set_flashdata('status_gao', 'GAO recommendation status updated');

        $this->load->helper('url');
        redirect('offices/' . $note_model_fields->milestone . '#gao_recs');
        
    }

}
