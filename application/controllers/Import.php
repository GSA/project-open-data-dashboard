<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use APIHelper\APIHelper;

class Import extends CI_Controller {

	public $environment = null;

	function __construct()
	{
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


	public function index() {
		redirect('docs');
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
		foreach($csv as $row) {

			$notes = array();
			foreach ($row as $key => $value) {
				if(substr($key, 0, 5) == 'note_') {
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

				if(!empty($value)) {
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
        $api_helper = new APIHelper();

		if (php_sapi_name() != 'cli') return;

		$agency_slug_api = 'https://www.data.gov/app/themes/roots-nextdatagov/assets/Json/fed_agency.json';
		$agency_slugs = $api_helper->curl_from_json($agency_slug_api, true);
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

	private function slug_match($slugs, $parent, $child = null) {

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

	private function run_match($agency_slugs, $office, $child_office = null) {

		if(!empty($child_office)){
			$update_id = $child_office->id;
			$child_office = $child_office->name;
			$office_name = $child_office;
		} else {
			$update_id = $office->id;
			$office_name = $office->name;
		}

		$match = $this->slug_match($agency_slugs, $office->name, $child_office);

		if(!empty($match)) {
			echo "match, $update_id, $office_name, $match" . PHP_EOL;

			$this->db->where('id', $update_id);
			$this->db->update('offices', array("url_slug"=>$match));

		} else {
			echo "no-match, $update_id, $office_name, null" . PHP_EOL;
		}
	}

	public function match_bureaus () {

		$this->load->helper('csv');

		$bureaus_url = 'http://project-open-data.cio.gov/data/omb_bureau_codes.csv';

		$importer = new CsvImporter($bureaus_url, $parse_header = true, $delimiter = ",");
		$csv = $importer->get();

		$parent_offices = array();

		foreach($csv as $row) {
			if ($row["Bureau Code"] == "00") {

				// Search for org
				$this->db->select('id, name');
				$this->db->where('name', $row["Bureau Name"]);
				$office_query = $this->db->get('offices');

				if ($office_query->num_rows() > 0) {
				   $office_matches = $office_query->result();

					foreach ($office_matches as $office_match) {
						$parent_offices[$row["Agency Code"]] = $office_match->id;
					}
				}
			}
		}

		reset($csv);
		$bureaus_mapped = array();

		foreach($csv as $row) {

			$bureau_mapped = array('agency_name' => '',
								   'bureau_name' => '',
								   'agency_code' => '',
								   'bureau_code' => '',
								   'treasury_code' => '',
								   'cgac_code' => '',
								   'usagov_directory_id' => '',
								   'parent_match' => '');

			// Search for org
			$this->db->select('id, name');
			$this->db->where('name', $row["Bureau Name"]);

			if(!empty($parent_offices[$row["Agency Code"]])) {
				$where = "(parent_office_id='" . $parent_offices[$row["Agency Code"]] . "' OR no_parent = 'true')";
				$this->db->where($where);

				$bureau_mapped['parent_match'] = 'true';
			} else {
				$bureau_mapped['parent_match'] = 'false';
			}

			$office_query = $this->db->get('offices');

			if ($office_query->num_rows() > 0) {
			   $office_matches = $office_query->result();

				foreach ($office_matches as $office_match) {
					$bureau_mapped['usagov_directory_id'] = $office_match->id;
				}

			}

			$bureau_mapped['agency_name'] = $row["Agency Name"];
			$bureau_mapped['bureau_name'] = $row['Bureau Name'];
			$bureau_mapped['agency_code'] = $row['Agency Code'];
			$bureau_mapped['bureau_code'] = $row['Bureau Code'];
			$bureau_mapped['treasury_code'] = $row['Treasury Code'];
			$bureau_mapped['cgac_code'] = $row['CGAC Code'];

			$bureaus_mapped[] = $bureau_mapped;

		}


		$headings = array_keys($bureaus_mapped[0]);

		// Open the output stream
        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
            $filepath = realpath('./downloads/bureaus_mapped.csv');
            $fh = fopen($filepath, 'w');
            echo 'Attempting to save csv to ' . $filepath .  PHP_EOL;
        } else {
            $fh = fopen('php://output', 'w');
        }


		// Start output buffering (to capture stream contents)
		ob_start();
		fputcsv($fh, $headings);

		// Loop over the * to export
		if (!empty($bureaus_mapped)) {
			foreach ($bureaus_mapped as $row) {
				fputcsv($fh, $row);
			}
		}

        if ($this->environment !== 'terminal') {
    		// Get the contents of the output buffer
    		$string = ob_get_clean();
    		$filename = 'bureaus_mapped_' . date('Ymd') .'_' . date('His');
    		// Output CSV-specific headers

    		header("Pragma: public");
    		header("Expires: 0");
    		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    		header("Cache-Control: private",false);
    		header('Content-type: text/csv');
    		header("Content-Disposition: attachment; filename=\"$filename.csv\";" );
    		header("Content-Transfer-Encoding: binary");

    		exit($string);
        } else {
            echo 'Done' . PHP_EOL;
            exit;
        }


	}




}
