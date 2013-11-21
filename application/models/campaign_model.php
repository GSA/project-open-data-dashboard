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
			'datapage_url' => null,  
			'datapage_status' => null,  						         
			'feedback_mechanism' => null,       
			'catalog_view' => null,             
			'community_plan' => null,           
			'central_inventory' => null,        
			'inventory_plan' => null			
		);
		
		return $datagov_model;
	}
	
	
	public function uri_header($url, $redirect_count = 0) {
		
		$status = curl_header($url);	
		$status = $status['info'];	//content_type and http_code		
		
		if($status['redirect_count'] == 0 && !(empty($redirect_count))) $status['redirect_count'] = 1;		
		$status['redirect_count'] = $status['redirect_count'] + $redirect_count;

		if(!empty($status['redirect_url'])) {
			if($status['redirect_count'] == 0 && $redirect_count == 0) $status['redirect_count'] = 1;
			
			if ($status['redirect_count'] > 5) return $status;
			$status = $this->uri_header($status['redirect_url'], $status['redirect_count']);
		}		
		
		if(!empty($status)) {
			return $status;
		} else {
			return false; 
		}
	}
		
	
	public function validate_datajson($uri) {
		
		$this->load->helper('jsonschema');					

		$schema = json_decode(file_get_contents(realpath('./schema/catalog.json')));		

		if($data = @file_get_contents($uri)) {
    		$data = json_decode($data);

    		if(empty($data)) {
    			return Jsv4::validate($data, $schema);
    		} else {
    			return false;
    		}		    
		} else {
		    return false;
		}
		

		
	}

	
	public function update_status($update) {		
		
		$this->db->select('datajson_status');		
		$this->db->where('office_id', $update['office_id']);						
		$query = $this->db->get('datagov_campaign');				
		
		if ($query->num_rows() > 0) {
			// update
			
			if ($this->environment == 'terminal') {
				echo 'Updating ' . $update['office_id'] . PHP_EOL . PHP_EOL;
			}	
			
			$current_data = $query->row_array();				
			$update = array_mash($update, $current_data);
			
			$this->db->where('office_id', $update['office_id']);						
			$this->db->update('datagov_campaign', $update);					
			
			
			
		} else {
			// insert
			
			if ($this->environment == 'terminal') {
				echo 'Adding ' . $update['office_id'] . PHP_EOL . PHP_EOL;
			}					
			
			$this->db->insert('datagov_campaign', $update);					
			
		}		
		
	}
	
	
	
	
	public function datajson_schema() {
		
		$schema = json_decode(file_get_contents(realpath('./schema/catalog.json')));

		if (!empty($schema->items->{'$ref'})) {
			
			$schema = json_decode(file_get_contents(realpath('./schema/' . $schema->items->{'$ref'})));

		}		
		return $schema;
		
	}
	
	
	public function schema_to_model($schema) {
		
		$model = new stdClass();
		
		foreach ($schema as $key => $value) {
			
			if(!empty($value->items) && $value->type == 'array') {
				 $model->$key = array();								
			} else {
				$model->$key = null;				
			}
			
		}
		
		return $model;
		
	}
	
	public function get_datagov_json($orgs, $rows = 100, $offset = 0, $raw = false) {
		
		$orgs = rawurlencode($orgs);
		$query = "-harvest_source_id:[''%20TO%20*]%20AND%20-type:harvest%20AND%20organization:(" . $orgs . ")&rows=" . $rows . '&start=' . $offset;
		$uri = 'http://catalog.data.gov/api/3/action/package_search?q=' . $query;
		$datagov_json = curl_from_json($uri, false);
						
		if(empty($datagov_json)) return false;
				
		if($raw == true) {			
			return $datagov_json;
		} else {			
			return $datagov_json->result->results;
		}
		
	}
	
	public function datajson_crosswalk($raw_data, $datajson_model) {
	
		$distributions = array();
		foreach($raw_data->resources as $resource) {
			$distribution = new stdClass();
			
			$distribution->accessURL 	= $resource->url;
			$distribution->format		= $resource->format;
			
			$distributions[] = $distribution;			
		}
	
		if(!empty($raw_data->tags)) {
			$tags = array();
			foreach ($raw_data->tags as $tag) {
				$tags[] = $tag->name;				
			}
		} else {
			$tags = null;
		}
		
		if(!empty($raw_data->extras)) {
		    
		    foreach($raw_data->extras as $extra) {
		        
		        if ($extra->key == 'tags') {
		            $extra_tags = $extra->value;
		            $datajson_model->keyword = (!empty($extra_tags)) ? array_map('trim',explode(",",$extra_tags)) : null;
		        }
		        
		        if ($extra->key == 'data-dictiionary' OR $extra->key == 'data-dictionary') {
		            $datajson_model->dataDictionary = $extra->value;
		        }

		        if ($extra->key == 'person') {
		            $datajson_model->contactPoint = $extra->value;
		        }
		        
		        if ($extra->key == 'contact-email') {
		            $datajson_model->mbox = $extra->value;
		        }	
		        
		        if ($extra->key == 'frequency-of-update') {
		            $datajson_model->accrualPeriodicity = $extra->value;
		        }	        		        
		        
		        if ($extra->key == 'issued') {
		            $datajson_model->issued = date(DATE_ISO8601, strtotime($extra->value));
		        }		        
		        
		        if ($extra->key == 'theme') {
		            $datajson_model->theme[0] = $extra->value;
		        }		        
		        
		        if ($extra->key == 'access-level') {
		            $datajson_model->accessLevel = $extra->value;
		        }
		        
		        if ($extra->key == 'license' OR $extra->key == 'licence') {
		            $license = trim($extra->value);
		            
		            if(!empty($license)) {
		                $datajson_model->license = $license;
		            }
		            
		        }		        		        
		        
		        
		        
		    }
		    
		    
        }	
        
	
		
		
	    $datajson_model->accessURL                          = null; 
//		$datajson_model->accessLevel                        = $datajson_model->accessLevel;
		$datajson_model->accessLevelComment                 = null;
//		$datajson_model->accrualPeriodicity                 = $datajson_model->accrualPeriodicity;
		$datajson_model->bureauCode                         = null;
		$datajson_model->contactPoint                       = (!empty($datajson_model->contactPoint)) ? $datajson_model->contactPoint : $raw_data->maintainer;
//		$datajson_model->dataDictionary                     = $datajson_model->dataDictionary;
		$datajson_model->dataQuality                        = null;
		$datajson_model->description                        = $raw_data->notes;
		$datajson_model->distribution                       = $distributions;
	    $datajson_model->format                             = null;		
		$datajson_model->identifier                         = $raw_data->id;
//		$datajson_model->issued                             = $datajson_model->issued;
		$datajson_model->keyword                            = (!empty($datajson_model->keyword)) ? $datajson_model->keyword : $tags;
		$datajson_model->landingPage                        = null;
		$datajson_model->language                           = null;
//		$datajson_model->license                            = $datajson_model->license;
		$datajson_model->mbox                               = (!($datajson_model->mbox)) ? $datajson_model->mbox : $raw_data->maintainer_email;
		$datajson_model->modified                           = date(DATE_ISO8601, strtotime($raw_data->metadata_modified));
		$datajson_model->PrimaryITInvestmentUII             = null;
		$datajson_model->programCode                        = null;
		$datajson_model->publisher                          = $raw_data->organization->title;
		$datajson_model->references                         = null;
		$datajson_model->spatial                            = null;
		$datajson_model->systemOfRecords                    = null;
		$datajson_model->temporal                           = null;
//		$datajson_model->theme                              = $datajson_model->theme;
		$datajson_model->title                              = $raw_data->title;
		$datajson_model->webService                         = null;
	
		return $datajson_model;
	}	
	
	
	

}

?>