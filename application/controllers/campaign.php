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
		
		
	}


	public function status() {
		
		
		$this->load->model('campaign_model', 'campaign');			
		
		
		$this->db->select('url, id');		
		$query = $this->db->get('offices');
		
		if ($query->num_rows() > 0) {
		   	$offices = $query->result();
		
			foreach ($offices as $office) {
				$url = $office->url;
				$url = substr($url, 0, strpos($url, '.gov') + 4);
				$expected_datajson_url = $url . '/data.json';

				$status = curl_header($expected_datajson_url);	
				$status = $status['info'];	//content_type and http_code	
				
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