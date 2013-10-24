<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import extends CI_Controller {

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
	public function index() {
			
		
		
		if(!$this->config->item('import_active')) {
			redirect('docs');				
		}	
		
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
		


		$this->load->helper('api');

		$master_list_url = 'http://www.usa.gov/api/USAGovAPI/contacts.json/contacts/tree?include_descendants=true';

		if ($this->environment == 'terminal') {
			echo 'Loading ' . $master_list_url . PHP_EOL;
		}

		$data = curl_from_json($master_list_url);

		  if(!empty($data['Contact'])) {
		  	foreach ($data['Contact'] as $department) {

		  		//var_dump($department); exit;

		  		if($department['Language'] == 'en') {

				$id = $department['Id'];

				$parent_list = (!empty($parent_list)) ? $parent_list : array();
				$parent_list = $this->get_parents($parent_list, $id);

				if(!empty($department['Contact'])) {
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
						
				$office_model = $this->office_model();			
				$office_model['id'] = $office['Id'];    
			
				$office_model['name'] = $office['Name'];    
			
				if(strpos($office_model['name'], '(') !== false && strpos($office_model['name'], ')') !== false) {
					$abbreviation = get_between($office_model['name'], '(', ')');
					$office_model['name'] = str_replace('(' . $abbreviation . ')', '', $office_model['name']);
					$office_model['name'] = trim($office_model['name']);
				} else {
					$abbreviation = null;
				}
				
				if(strpos($office_model['name'], 'U.S. Department') !== false) {
					$office_model['name'] = str_replace('U.S. Department', 'Department', $office_model['name']);
					$office_model['name'] = trim($office_model['name']);
				}
				
				
				
				// see if this is a cfo act agency
				$office_model['cfo_act_agency'] = ($this->cfo_act_check($office_model['name'])) ? 'true' : 'false';
				
				$url = (!empty($office['Web_Url'][0]['Url'])) ? $office['Web_Url'][0]['Url'] : null;
			              
				$office_model['abbreviation'] = $abbreviation;            
				$office_model['url'] = $url;                                         
				$office_model['no_parent'] = 'true';                
				$office_model['reporting_authority_type'] =	$type;		
			
				if ($this->environment == 'terminal') {
					echo 'Adding ' . $office_model['name'] . PHP_EOL;
				}			
						
				$this->db->insert('offices', $office_model);						
			
			}
		}
		
		
	}
	
	function get_parents($parent_list, $id) {


		$parent_api_url = 'http://www.usa.gov/api/USAGovAPI/contacts.json/contact/' . $id . '/tree/parent';
		
		if ($this->environment == 'terminal') {
			echo 'Loading ' . $parent_api_url . PHP_EOL;
		}		
		
		$parent = curl_from_json($parent_api_url);

		if(!empty($parent['Contact'][0])) {
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
		'National Aeronautics and Space Administration');		
	
		// iterate through array see if agency_name matches any of them
	
		 if (array_search(strtolower($agency_name),array_map('strtolower',$cfo_act_agencies)) !== false){
			return true;
		} else {
			return false;
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */