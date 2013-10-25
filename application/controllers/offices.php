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
		$this->db->where('cfo_act_agency', 'true');	
		$this->db->order_by("name", "asc"); 		
		$query = $this->db->get('offices');
        
		if ($query->num_rows() > 0) {
		   $view_data['cfo_offices'] = $query->result();
		   $query->free_result();
		}
		
		$this->db->select('*');		
		$this->db->where('cfo_act_agency', 'false');			
		$this->db->where('reporting_authority_type', 'executive');		
		$this->db->order_by("name", "asc"); 		
		$query = $this->db->get('offices');
        
		if ($query->num_rows() > 0) {
		   $view_data['executive_offices'] = $query->result();
		   $query->free_result();
		}	
		
		$this->db->select('*');		
		$this->db->where('cfo_act_agency', 'false');			
		$this->db->where('reporting_authority_type', 'independent');	
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
		$this->db->select('*');		
		$this->db->where('id', $id);			
		$query = $this->db->get('offices');	
		
		$view_data = array();

		// if successful return ocdid
		if ($query->num_rows() > 0) {
		   $view_data['office'] = $query->row_array();
		}
		
		$this->load->view('office_detail', $view_data);		
			
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */