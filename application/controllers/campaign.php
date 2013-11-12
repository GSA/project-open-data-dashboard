<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Campaign extends CI_Controller {

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


	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
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
	
	public function convert() {
		$this->load->model('campaign_model', 'campaign');			
				
		$orgs = $this->input->get('orgs', TRUE);

		$raw_data = $this->campaign->get_datagov_json($orgs);
		
		if(!empty($raw_data)) {
		
			$json_schema = $this->campaign->datajson_schema();
			$datajson_model = $this->campaign->schema_to_model($json_schema->properties);						

			$convert = array();
			foreach ($raw_data as $ckan_data) {
				$model = clone $datajson_model;						
				$convert[] = $this->campaign->datajson_crosswalk($ckan_data, $model);
			}		
			
		    header('Content-type: application/json');
		    print json_encode($convert);		
			exit;			
			
		} else {
			return false;
		}

				

		
	}


	public function csv() {
		$this->load->model('campaign_model', 'campaign');			
				
		$orgs = $this->input->get('orgs', TRUE);

		$row_total = 100;
		$row_count = 0;
		
		$row_pagesize = 100;
		$raw_data = array();
		
		while($row_count < $row_total) {
			$result 	= $this->campaign->get_datagov_json($orgs, $row_pagesize, $row_count, true);
			
			if(!empty($result)) {
				$row_total = $result->result->count;
				$row_count = $row_count + $row_pagesize; 

				$raw_data = array_merge($raw_data, $result->result->results);				
			} else {
				break;
			}
			
		}

		
	   //$json_schema = $this->campaign->datajson_schema();
	   //$datajson_model = $this->campaign->schema_to_model($json_schema->properties);						
				
		// Create a stream opening it with read / write mode
		$stream = fopen('data://text/plain,' . "", 'w+');				
			
		$csv_rows = array();	
		foreach ($raw_data as $ckan_data) {
			$csv_rows[] = (array) $this->campaign->csv_crosswalk($ckan_data);		    
		}
		
	    //header('Content-type: application/json');
	    //print json_encode($csv_rows);		
		//exit;		
		
		
		$headings = array_keys($csv_rows[0]);		
		
		// Open the output stream
		$fh = fopen('php://output', 'w');
		
		// Start output buffering (to capture stream contents)
		ob_start();
		fputcsv($fh, $headings);
		
		// Loop over the * to export
		if (! empty($csv_rows)) {
			foreach ($csv_rows as $row) {
				fputcsv($fh, $row);
			}
		}
		
		// Get the contents of the output buffer
		$string = ob_get_clean();
		$filename = 'csv_' . date('Ymd') .'_' . date('His');
		// Output CSV-specific headers

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header('Content-type: text/csv');		
		header("Content-Disposition: attachment; filename=\"$filename.csv\";" );
		header("Content-Transfer-Encoding: binary");

		exit($string);		

		
	}



	public function status() {
		
		
		$this->load->model('campaign_model', 'campaign');			
		
		
		$this->db->select('url, id');		
		$query = $this->db->get('offices');
		
		if ($query->num_rows() > 0) {
		   	$offices = $query->result();
		
			foreach ($offices as $office) {
				
	
				$url = $office->url;
				
				if(strpos($url, '.org') == true) {
					$tld = '.org';
				} elseif (strpos($url, '.edu') == true) {
					$tld = '.edu';					
				} elseif (strpos($url, '.net') == true) {
					$tld = '.net';					
				} elseif (strpos($url, '.com') == true) {
					$tld = '.com';
				} elseif (strpos($url, '.mil') == true) {
					$tld = '.mil';												
				} elseif (strpos($url, '.gov') == true) {
					$tld = '.gov';					
				}
				
				$url = substr($url, 0, strpos($url, $tld) + 4);
				$expected_datajson_url = $url . '/data.json';

				$status = $this->campaign->uri_header($expected_datajson_url);
				$status['expected_datajson_url'] = $expected_datajson_url;				
				
				if($status['http_code'] == 200) {
					$validation = $this->campaign->validate_datajson($status['url']);

					if(!empty($validation)) {
						$status['valid_json'] = true;
						$status['valid_schema'] = $validation->valid;
						$status['schema_errors'] = $validation->errors;	
					} else {
						// data.json was not valid json
						$status['valid_json'] = false;
					}
					
				}				
				
				
				$update = $this->campaign->datagov_model();
				$update['datajson_status'] = json_encode($status);
				$update['office_id'] = $office->id;
				
				if ($this->environment == 'terminal') {
					echo 'Attempting to set ' . $update['office_id'] . ' with ' . $update['datajson_status'] . PHP_EOL;
				}				
				
				// Instead of hacking together an upsert or preloading existing status data, 
				// let's just be really inefficient and do a lookup for each record
				
				$this->campaign->update_status($update);
								
				
				
			}
		
		
		
		}		
        
	}
	

}