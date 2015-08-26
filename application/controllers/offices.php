<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Offices extends CI_Controller {




	function __construct() {
		parent::__construct();

		$this->load->helper('url');
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
	public function index($selected_milestone = null, $output=null, $show_all_offices = false, $show_all_fields = false, $show_qa_fields = false)
	{


		$this->load->model('campaign_model', 'campaign');
		$milestones = $this->campaign->milestones_model();	

		$selected_milestone	= ($this->input->get_post('milestone', TRUE)) ? $this->input->get_post('milestone', TRUE) : $selected_milestone;

		$milestone 			= $this->campaign->milestone_filter($selected_milestone, $milestones);
		$milestones 		= $milestone->milestones;

		// Determine selected milestone. Defaults to previous milestone if not specified
		if(empty($selected_milestone)) {
			$milestone->selected_milestone	= $milestone->previous;
		} 

		if ($milestone->selected_milestone == $milestone->current) {
			$crawl_status_filter = 'current';
		} else {
			$crawl_status_filter = 'final';
		}

		$view_data = array();

		$this->db->select('*');
		$this->db->from('offices');
		$this->db->join('datagov_campaign', 'datagov_campaign.office_id = offices.id', 'left');	
		$this->db->where('datagov_campaign.milestone', $milestone->selected_milestone);
		$this->db->where('offices.cfo_act_agency', 'true');
		$this->db->where('offices.no_parent', 'true');
		$this->db->where("(datagov_campaign.crawl_status IS NULL OR datagov_campaign.crawl_status = '$crawl_status_filter')");
		$this->db->order_by("offices.name", "asc");
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$view_data['cfo_offices'] = $query->result();
			$query->free_result();
		}

		if ($this->config->item('show_all_offices') || $show_all_offices) {

			$this->db->select('*');
			$this->db->from('offices');
			$this->db->join('datagov_campaign', 'datagov_campaign.office_id = offices.id', 'left');
			$this->db->where('datagov_campaign.milestone', $milestone->selected_milestone);
			$this->db->where('offices.cfo_act_agency', 'false');
			$this->db->where('offices.reporting_authority_type', 'executive');
			$this->db->where('offices.no_parent', 'true');
			$this->db->where("(datagov_campaign.crawl_status IS NULL OR datagov_campaign.crawl_status = '$crawl_status_filter')");
			$this->db->order_by("offices.name", "asc");
			$query = $this->db->get();

			if ($query->num_rows() > 0) {
			   $view_data['executive_offices'] = $query->result();
			   $query->free_result();
			}

			$this->db->select('*');
			$this->db->from('offices');
			$this->db->join('datagov_campaign', 'datagov_campaign.office_id = offices.id', 'left');
			$this->db->where('datagov_campaign.milestone', $milestone->selected_milestone);
			$this->db->where('offices.cfo_act_agency', 'false');
			$this->db->where('offices.reporting_authority_type', 'independent');
			$this->db->where('offices.no_parent', 'true');
			$this->db->where("(datagov_campaign.crawl_status IS NULL OR datagov_campaign.crawl_status = '$crawl_status_filter')");
			$this->db->order_by("offices.name", "asc");
			$query = $this->db->get();


			// if successful return ocdid
			if ($query->num_rows() > 0) {
			   $view_data['independent_offices'] = $query->result();
			   $query->free_result();
			}

		}

		// pass milestones and tracker data model
		$view_data['milestone'] = $milestone;
		$view_data['tracker'] = $this->campaign->tracker_model();
		$view_data['section_breakdown'] = $this->campaign->tracker_sections_model();

		// pass config variables
		$view_data['max_remote_size'] = $this->config->item('max_remote_size');

		$view_data['show_all_fields'] = $show_all_fields;
		$view_data['show_qa_fields']  = $show_qa_fields;	

		// override section model if we're just showing QA
		if($show_qa_fields) $view_data['section_breakdown'] = $this->campaign->qa_sections_model();	

		if ($output == 'json') {
			return $view_data;
		}

		$this->load->view('office_list', $view_data);

	}


	public function export() {
		$listing = $this->index('json');

		$output = array();

		foreach ($listing as $group) {

			foreach ($group as $office) {
				$output[] = array("key" => $office->id, "name" => $office->name);
			}

		}

		header('Content-type: application/json');
		print json_encode($output);
		exit;

	}


	public function detail($id, $milestone=null, $status = null, $status_id = null) {

		$this->load->helper('api');
		$this->load->model('campaign_model', 'campaign');
		$markdown_extra = new Michelf\MarkdownExtra();

		$milestones = $this->campaign->milestones_model();	
		$selected_milestone	= ($this->input->get_post('milestone', TRUE)) ? $this->input->get_post('milestone', TRUE) : $milestone;

		$selected_category	= ($this->input->get_post('highlight', TRUE)) ? $this->input->get_post('highlight', TRUE) : null;
	
		$milestone 				= $this->campaign->milestone_filter($selected_milestone, $milestones);


		$view_data = array();

		// pass milestones data model
		$view_data['milestone'] = $milestone;

		// pass tracker data model
		$view_data['tracker_model'] = $this->campaign->tracker_model();


		$this->db->select('*');
		$this->db->where('id', $id);
		$query = $this->db->get('offices');

		if ($query->num_rows() > 0) {
		   $view_data['office'] = $query->row();


			// Get note data
			$notes = $this->campaign->get_notes($view_data['office']->id, $milestone->selected_milestone);
			$view_data['note_model'] = $this->campaign->note_model();

			if ($notes->num_rows() > 0) {

				$note_list = array();
				foreach ($notes->result() as $note) {
					$note_field = 'note_' . $note->field_name;
					$note_list[$note_field] = json_decode($note->note);
					if(!empty($note_list[$note_field]->current->note)) {

						$note_html = $note_list[$note_field]->current->note;
						
						$note_html = $markdown_extra->transform($note_html);						
						$note_html = linkToAnchor($note_html);

						$note_list[$note_field]->current->note_html = $note_html;
					} else {
						$note_list[$note_field]->current->note_html = null;
					}
				}

				$view_data['notes'] = $note_list;
			}

			// Get crawler data
			$view_data['office_campaign'] = $this->campaign->datagov_office($view_data['office']->id, $milestone->selected_milestone, null, $status_id);


			// Get the IDs of daily crawls before and after this date
			$crawls_before = $this->campaign->datagov_office_crawls($view_data['office']->id, $milestone->selected_milestone, $view_data['office_campaign']->status_id, '<', '5');
			$crawls_after  = $this->campaign->datagov_office_crawls($view_data['office']->id, $milestone->selected_milestone, $view_data['office_campaign']->status_id, '>', '5');

			$view_data['nearby_crawls'] = array_merge(array_reverse($crawls_before), $crawls_after);
			

			// If we have a blank slate, populate the data model
			if(empty($view_data['office_campaign'])) {
				$view_data['office_campaign'] = $this->campaign->datagov_model();
			}

			// Make sure tracker data uses full tracker model
			if(isset($view_data['office_campaign']->tracker_fields)) {

				$tracker_fields = ($view_data['office_campaign']->tracker_fields) ? json_decode($view_data['office_campaign']->tracker_fields) : new stdClass();

				foreach ($view_data['tracker_model'] as $field_name => $value) {
					$tracker_fields->$field_name = (isset($tracker_fields->$field_name)) ? $tracker_fields->$field_name : null;
				}

				$view_data['office_campaign']->tracker_fields = json_encode($tracker_fields);

			} 


			if(!empty($view_data['office_campaign']->datajson_status)) {
				$view_data['office_campaign']->expected_datajson_url = (!empty($view_data['office_campaign']->datajson_status['url'])) ? $view_data['office_campaign']->datajson_status['url'] : '';
				$view_data['office_campaign']->expected_datajson_status = (object) json_decode($view_data['office_campaign']->datajson_status);
			}

			if ($this->config->item('show_all_offices')) {

				// Get sub offices
				$this->db->select('*');
				$this->db->from('offices');
				$this->db->join('datagov_campaign', 'datagov_campaign.office_id = offices.id', 'left');
				$this->db->where('offices.parent_office_id', $view_data['office']->id);
				$this->db->order_by("offices.name", "asc");
				$query = $this->db->get();

				if ($query->num_rows() > 0) {
				   $view_data['child_offices'] = $query->result();
				}

			}



		}



		// selected tab
		$view_data['selected_category'] = $selected_category;

		// pass tracker section breakdown model
		$view_data['section_breakdown'] = $this->campaign->tracker_sections_model();

		// pass config variable
		$view_data['config'] = array('max_remote_size' => $this->config->item('max_remote_size'), 'archive_dir' => $this->config->item('archive_dir'));

		//var_dump($view_data['office_campaign']); exit;

		$this->load->view('office_detail', $view_data);

	}

	public function routes($route, $parameter1 = null, $parameter2 = null, $parameter3 = null, $parameter4 = null) {

		if($route == 'all') {
			return $this->index($milestone=null, $output=null, $show_all_offices = true);	
		}

		if($route == 'detail') {
			return $this->detail($parameter1, $parameter2, $parameter3, $parameter4);	
		}

		// check if it's a milestone date
    	$d = DateTime::createFromFormat('Y-m-d', $route);
    	if ($d && $d->format('Y-m-d') == $route) {

    		if ($parameter1 == 'all'){
    			$show_all_offices = true;
    		} else {
    			$show_all_offices = false;
    		}

    		if ($parameter1 == 'full' || $parameter2 == 'full'){
    			$show_all_fields = true;
    		} else {
    			$show_all_fields = false;
    		}  

    		if ($parameter1 == 'qa' || $parameter2 == 'qa'){
    			$show_qa_fields = true;
    		} else {
    			$show_qa_fields = false;
    		} 


    		return $this->index($milestone=$route, $output=null, $show_all_offices, $show_all_fields, $show_qa_fields);	
    	}


		
	}



}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */