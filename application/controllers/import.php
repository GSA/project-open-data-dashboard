<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Import extends CI_Controller {

    public $environment = null;

    function __construct() {
        parent::__construct();

        $this->load->helper('api');

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
     */
    public function index() {



        if (!$this->config->item('import_active')) {
            redirect('docs');
        }

        $master_list_url = 'https://www.usa.gov/api/USAGovAPI/contacts.json/contacts/tree?include_descendants=true';

        if ($this->environment == 'terminal') {
            echo 'Loading ' . $master_list_url . PHP_EOL;
        }

        $data = curl_from_json($master_list_url, true);

        if (!empty($data['Contact'])) {
            foreach ($data['Contact'] as $department) {

                //var_dump($department); exit;

                if ($department['Language'] == 'en') {

                    $id = $department['Id'];

                    $parent_list = (!empty($parent_list)) ? $parent_list : array();
                    $parent_list = $this->get_parents($parent_list, $id);

                    if (!empty($department['Contact'])) {
                        foreach ($department['Contact'] as $subdepartment) {
                            $id = $subdepartment['Id'];
                            $parent_list = $this->get_parents($parent_list, $id);
                        }
                    }
                }
            }
        }



        // Now lets seed the database with the top of the hierarchy
        foreach ($parent_list as $type => $parents) {

            foreach ($parents as $id => $office) {

                if ($type == 'executive') {
                    $parent_id = 49743; // white house
                }

                $office_model = $this->prepare_office_data($office, $type, $parent_id = null, $no_parent = 'true');

                if ($this->environment == 'terminal') {
                    echo 'Adding ' . $office_model['name'] . PHP_EOL;
                }

                $this->db->insert('offices', $office_model);
            }
        }
    }

    function get_parents($parent_list, $id) {


        $parent_api_url = 'https://www.usa.gov/api/USAGovAPI/contacts.json/contact/' . $id . '/tree/parent';

        if ($this->environment == 'terminal') {
            echo 'Loading ' . $parent_api_url . PHP_EOL;
        }

        $parent = curl_from_json($parent_api_url, true);

        if (!empty($parent['Contact'][0])) {
            $parent_id = $parent['Id'];

            // this id is the white house
            if ($parent_id == 49743) {
                $parent = $parent['Contact'][0];

                $parent_list['executive'][$parent['Id']] = $parent;
            } else {
                $parent_list['independent'][$parent['Id']] = $parent;
            }
        } else {
            $parent_list['independent'][$parent['Id']] = $parent;
        }

        return $parent_list;
    }

    public function children() {

        $this->db->select('id, reporting_authority_type');
        $this->db->where('no_parent', 'true');
        $query = $this->db->get('offices');

        if ($query->num_rows() > 0) {
            $parent_offices = $query->result();

            foreach ($parent_offices as $office) {

                // 49743 = White House. We should already have all subagencies of the white house in as no_parent = true;
                if ($office->id == 49743) {
                    continue;
                }

                $child_api_url = 'https://www.usa.gov/api/USAGovAPI/contacts.json/contact/' . $office->id . '/tree/descendant';

                if ($this->environment == 'terminal') {
                    echo 'Loading ' . $child_api_url . PHP_EOL;
                }

                $parent = curl_from_json($child_api_url, true);

                if (!empty($parent['Contact'][0])) {

                    $children = $parent['Contact'];
                    $this->process_descendants($children, $office->reporting_authority_type, $office->id);
                }
            }
        }
    }

    function process_descendants($children, $type, $parent_id = null) {

        foreach ($children as $child) {

            $no_parent = (empty($parent_id)) ? 'true' : 'false';
            $office_model = $this->prepare_office_data($child, $type, $parent_id, $no_parent);

            if ($this->environment == 'terminal') {
                echo 'Adding ' . $office_model['name'] . PHP_EOL;
            }

            $this->db->insert('offices', $office_model);

            if (!empty($child['Contact'][0])) {
                $grand_children = $child['Contact'];
                $this->process_descendants($grand_children, $type, $office_model['id']);
            }
        }
    }

    function prepare_office_data($office, $type, $parent_id, $no_parent = 'false') {

        $office_model = $this->office_model();
        $office_model['id'] = $office['Id'];

        $office_model['name'] = $office['Name'];

        if (strpos($office_model['name'], '(') !== false && strpos($office_model['name'], ')') !== false) {
            $abbreviation = get_between($office_model['name'], '(', ')');
            $office_model['name'] = str_replace('(' . $abbreviation . ')', '', $office_model['name']);
            $office_model['name'] = trim($office_model['name']);
        } else {
            $abbreviation = null;
        }

        if (strpos($office_model['name'], 'U.S. Department') !== false) {
            $office_model['name'] = str_replace('U.S. Department', 'Department', $office_model['name']);
            $office_model['name'] = trim($office_model['name']);
        }


        // see if this is a cfo act agency
        $office_model['cfo_act_agency'] = ($this->cfo_act_check($office_model['name'])) ? 'true' : 'false';

        $url = (!empty($office['Web_Url'][0]['Url'])) ? $office['Web_Url'][0]['Url'] : null;

        //if(!empty($url)) {
        //	$url = substr($url, 0, strpos($url, '.gov') + 4);
        //}

        $office_model['abbreviation'] = $abbreviation;
        $office_model['url'] = $url;
        $office_model['parent_office_id'] = $parent_id;
        $office_model['no_parent'] = $no_parent;
        $office_model['reporting_authority_type'] = $type;

        return $office_model;
    }

    function office_model() {

        $office_model = array(
            'id' => null,
            'name' => null,
            'abbreviation' => null,
            'url' => null,
            'notes' => null,
            'parent_office_id' => null,
            'no_parent' => null,
            'reporting_authority_type' => null
        );

        return $office_model;
    }

    function cfo_act_check($agency_name) {

        $cfo_act_agencies = array(
            'U.S. Agency for International Development',
            'General Services Administration',
            'National Science Foundation',
            'Nuclear Regulatory Commission',
            'Office of Personnel Management',
            'Small Business Administration',
            'Department of Agriculture',
            'Department of Commerce',
            'Department of Defense',
            'Department of Education',
            'Department of Energy',
            'Department of Health and Human Services',
            'Department of Homeland Security',
            'Department of Housing and Urban Development',
            'Department of the Interior',
            'Department of Justice',
            'Department of Labor',
            'Department of State',
            'Department of Transportation',
            'Department of the Treasury',
            'Department of Veterans Affairs',
            'Environmental Protection Agency',
            'National Aeronautics and Space Administration',
            'Social Security Administration');

        // iterate through array see if agency_name matches any of them

        if (array_search(strtolower($agency_name), array_map('strtolower', $cfo_act_agencies)) !== false) {
            return true;
        } else {
            return false;
        }
    }

    function tracker() {

        $this->load->helper('csv');
        $this->load->helper('api');
        $this->load->model('campaign_model', 'campaign');


        ini_set("auto_detect_line_endings", true);

        $full_path = $this->config->item('tmp_csv_import');

        $importer = new CsvImporter($full_path, $parse_header = true, $delimiter = ",");
        $csv = $importer->get();

        $model = (array) $this->campaign->datagov_model();

        $note_count = 0;
        $status_count = 0;

        $column_headers = array();
        foreach ($csv as $row) {

            $notes = array();
            foreach ($row as $key => $value) {
                if (substr($key, 0, 5) == 'note_') {
                    $key = substr($key, 5);
                    $notes[$key] = $value;
                }
            }

            reset($row);


            $filtered = array_mash($model, $row);

            $processed = array();
            foreach ($filtered as $key => $value) {
                if (strtolower($value) == 'yes' OR strtolower($value) == 'no') {
                    $value = strtolower($value);
                }

                if (!empty($value)) {
                    $processed[$key] = $value;
                }
            }

            $update = (object) $processed;
            $this->campaign->update_status($update);
            $status_count++;

            foreach ($notes as $field_name => $note_data) {
                $note_data = array("note" => $note_data, "date" => null, "author" => null);
                $note_data = array("current" => $note_data, "previous" => null);

                $note_data = json_encode($note_data);

                $note = array('note' => $note_data, 'field_name' => $field_name, 'office_id' => $update->office_id);
                $note = (object) $note;
                $this->campaign->update_note($note);
                $note_count++;
            }
        }


        echo "Status count: $status_count / Note count: $note_count";
    }

    public function match_agency_slugs() {

        if (php_sapi_name() != 'cli')
            return;

        $agency_slug_api = 'https://idm.data.gov/fed_agency.json';
        $agency_slugs = curl_from_json($agency_slug_api, true);
        $agency_slugs = $agency_slugs["taxonomies"];

        $this->db->select('id, name');
        $this->db->where('no_parent', 'true');
        $query = $this->db->get('offices');

        if ($query->num_rows() > 0) {
            $parent_offices = $query->result();

            foreach ($parent_offices as $office) {

                $this->run_match($agency_slugs, $office);

                // Search for child orgs
                $this->db->select('id, name');
                $this->db->where('parent_office_id', $office->id);
                $child_query = $this->db->get('offices');

                if ($child_query->num_rows() > 0) {
                    $child_offices = $child_query->result();

                    foreach ($child_offices as $child_office) {
                        $this->run_match($agency_slugs, $office, $child_office);
                    }
                }
            }
        }
    }

    public function slug_match($slugs, $parent, $child = null) {

        foreach ($slugs as $slug) {
            $slug = $slug["taxonomy"];
            if ($slug["Federal Agency"] == $parent) {
                if (!empty($child)) {
                    if ($slug["Sub Agency"] == $child) {
                        return substr($slug["term"], 0, strpos($slug["term"], "-gov"));
                    }
                } else {
                    if ($slug["Sub Agency"] == "") {
                        return substr($slug["term"], 0, strpos($slug["term"], "-gov"));
                    }
                }
            }
        }
    }

    public function run_match($agency_slugs, $office, $child_office = null) {

        if (!empty($child_office)) {
            $update_id = $child_office->id;
            $child_office = $child_office->name;
            $office_name = $child_office;
        } else {
            $update_id = $office->id;
            $office_name = $office->name;
        }

        $match = $this->slug_match($agency_slugs, $office->name, $child_office);

        if (!empty($match)) {
            echo "match, $update_id, $office_name, $match" . PHP_EOL;

            $this->db->where('id', $update_id);
            $this->db->update('offices', array("url_slug" => $match));
        } else {
            echo "no-match, $update_id, $office_name, null" . PHP_EOL;
        }
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */