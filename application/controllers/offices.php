<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Offices extends CI_Controller {

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
		
		$view_data = array();
	
		$this->db->select('*');	
		$this->db->from('offices');			
		$this->db->join('datagov_campaign', 'datagov_campaign.office_id = offices.id');	
		$this->db->where('offices.cfo_act_agency', 'true');	
		$this->db->where('offices.no_parent', 'true');			
		$this->db->order_by("offices.name", "asc"); 			
		$query = $this->db->get();
        
		if ($query->num_rows() > 0) {		
			$view_data['cfo_offices'] = $query->result();
			$query->free_result();
		}
		
		$this->db->select('*');		
		$this->db->where('cfo_act_agency', 'false');			
		$this->db->where('reporting_authority_type', 'executive');	
		$this->db->where('no_parent', 'true');				
		$this->db->order_by("name", "asc"); 		
		$query = $this->db->get('offices');
        
		if ($query->num_rows() > 0) {
		   $view_data['executive_offices'] = $query->result();
		   $query->free_result();
		}	
		
		$this->db->select('*');		
		$this->db->where('cfo_act_agency', 'false');			
		$this->db->where('reporting_authority_type', 'independent');	
		$this->db->where('no_parent', 'true');	
		$this->db->where('id !=', 49743);	// exclude the white house			
		$this->db->order_by("name", "asc"); 						
		$query = $this->db->get('offices');
        
		// if successful return ocdid
		if ($query->num_rows() > 0) {
		   $view_data['independent_offices'] = $query->result();
		   $query->free_result();		
		}			
		
		
		
		$this->load->view('office_list', $view_data);
	}
	
	
	public function detail($id) {
		
		$this->load->helper('api');		
		$this->load->model('campaign_model', 'campaign');			
		
		$this->db->select('*');		
		$this->db->where('id', $id);			
		$query = $this->db->get('offices');			
		
		$view_data = array();

		if ($query->num_rows() > 0) {
		   $view_data['office'] = $query->row();
		

		
			$view_data['office_campaign'] = $this->campaign->datagov_office($view_data['office']->id);
		
			if(empty($view_data['office_campaign'])) {
				$view_data['office_campaign'] = (object) $this->campaign->datagov_model();
			}
		
			 $url = $view_data['office']->url;
			 $url = substr($url, 0, strpos($url, '.gov') + 4);
			 
			 
			
			 $view_data['office_campaign']->expected_datajson_url = $url . '/data.json';
			
			
			 $status = curl_header($view_data['office_campaign']->expected_datajson_url);	
			 $status = $status['info'];	//content_type and http_code
			 $view_data['office_campaign']->expected_datajson_status = $status; 
		
			$this->db->select('*');		
			$this->db->where('parent_office_id', $view_data['office']->id);		
			$this->db->order_by("name", "asc"); 						
				
			$query = $this->db->get('offices');		
		
			if ($query->num_rows() > 0) {
			   $view_data['child_offices'] = $query->result();				
			}
		
		}
		
		$this->load->view('office_detail', $view_data);		
			
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */