<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class campaign_model extends CI_Model {


	//var $pagination	 		= NULL;
	var $jurisdictions 		= array();


	var $protected_field	= null;


	public function __construct(){
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
						
		//$this->office					= $this->office();

	}
	
	public function datagov_office($office_id) {
	
		$this->db->select('*');		
		$this->db->where('office_id', $office_id);				
		$query = $this->db->get('datagov_campaign');
        
		if ($query->num_rows() > 0) {
		   return $query->row();				
		} else {
		   return false; 
		}		
		
	}
	
	
	public function datagov_model() {
		
		$datagov_model = array(
			'office_id' => null,                
			'contact_name' => null,             
			'contact_email' => null,            
			'datajson_url' => null,   
			'datajson_status' => null,   			          
			'datajson_notes' => null,           
			'feedback_mechanism' => null,       
			'catalog_view' => null,             
			'community_plan' => null,           
			'central_inventory' => null,        
			'inventory_plan' => null			
		);
		
		return $datagov_model;
	}
	
	
	
	public function update_status($update) {		
		
		$this->db->select('datajson_status');		
		$this->db->where('office_id', $update['office_id']);						
		$query = $this->db->get('datagov_campaign');				
		
		if ($query->num_rows() > 0) {
			// update
			
			if ($this->environment == 'terminal') {
				echo 'Updating ' . $update['office_id'] . ' with ' . $update['datajson_status'] . PHP_EOL;
			}	
			
			$current_data = $query->row_array();				
			$update = array_mash($update, $current_data);
			
			$this->db->where('office_id', $update['office_id']);						
			$this->db->update('datagov_campaign', $update);					
			
			
			
		} else {
			// insert
			
			if ($this->environment == 'terminal') {
				echo 'Adding ' . $update['office_id'] . ' with ' . $update['datajson_status'] . PHP_EOL;
			}					
			
			$this->db->insert('datagov_campaign', $update);					
			
		}		
		
	}
	
	

}

?>