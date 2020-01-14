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

		$output = $this->input->post_get('output', TRUE);

		$this->load->model('campaign_model', 'campaign');
		$milestones = $this->campaign->milestones_model();

		$selected_milestone	= ($this->input->post_get('milestone', TRUE)) ? $this->input->post_get('milestone', TRUE) : $selected_milestone;

		$milestone 			= $this->campaign->milestone_filter($selected_milestone, $milestones);
		$milestones 		= $milestone->milestones;

		// Determine selected milestone. Defaults to current milestone if not specified
		if(empty($selected_milestone)) {
			$milestone->selected_milestone	= $milestone->current;
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

		if(!empty($view_data['cfo_offices'])) {
			$view_data['office_totals'] = $this->calculate_totals($view_data['cfo_offices']);
		}



		// pass config variables
		$view_data['max_remote_size'] = $this->config->item('max_remote_size');

		$view_data['show_all_fields'] = $show_all_fields;
		$view_data['show_qa_fields']  = $show_qa_fields;

		// override section model if we're just showing QA
		if($show_qa_fields) $view_data['section_breakdown'] = $this->campaign->qa_sections_model();


		// Calculate collective review status
		$review_status = "";
		$complete_count = 0;
		if(!empty($view_data["cfo_offices"])) {
			foreach ($view_data["cfo_offices"] as $office_data) {
				if (!empty($office_data->tracker_status)) {
					$tracker_status = json_decode($office_data->tracker_status);
					if (!empty($tracker_status->status) && $tracker_status->status == "in-progress") {
						$review_status = "in-progress";
						break;
					}
					if (!empty($tracker_status->status) && $tracker_status->status == "complete") {
						$complete_count++;
					}
				}
			}

			$view_data['review_status'] = $review_status;

			if($complete_count == count($view_data["cfo_offices"])) {
				$view_data['review_status'] = "complete";
			}

		}


		// For raw JSON output
		if ($output == 'json') {

			$json_fields = array("datajson_status", "datapage_status", "digitalstrategy_status", "tracker_fields", "tracker_status");



			if(!empty($view_data["cfo_offices"])) {
				$converted_data = array();
				foreach ($view_data["cfo_offices"] as $office_data) {

					foreach($json_fields as $json_field) {
						if(!empty($office_data->$json_field)) {
							$office_data->$json_field = json_decode($office_data->$json_field);
						}
					}
					$converted_data[] = $office_data;
				}
				$view_data["cfo_offices"] = $converted_data;

			}


			header('Content-type: application/json');
			print json_encode($view_data, JSON_PRETTY_PRINT);
			exit;
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
		print json_encode($output, JSON_PRETTY_PRINT);
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

		if ($query->num_rows() < 1) {
			show_error('Office '.html_escape($id).' is unknown', 404, 'Can\'t help you there.');
		} else {
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

			// If we have a blank slate, populate the data model
			if(empty($view_data['office_campaign'])) {
				$view_data['office_campaign'] = $this->campaign->datagov_model();
			}

			// Get the IDs of daily crawls before and after this date
			$crawls_before = $this->campaign->datagov_office_crawls($view_data['office']->id, $milestone->selected_milestone, $view_data['office_campaign']->status_id, '<', '5');
			$crawls_after  = $this->campaign->datagov_office_crawls($view_data['office']->id, $milestone->selected_milestone, $view_data['office_campaign']->status_id, '>', '5');

			$view_data['nearby_crawls'] = array_merge(array_reverse($crawls_before), $crawls_after);



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

		$milestone_trends = $this->get_trends($id);
		if ($milestone_trends) {
			$periods = array();
			foreach ($milestone_trends as $milestone_trend) {
				$tracker_fields = json_decode($milestone_trend->tracker_fields);

				$edi_access_public 		= (!empty($tracker_fields->edi_access_public)) ? $tracker_fields->edi_access_public : null;
				$edi_access_restricted 	= (!empty($tracker_fields->edi_access_restricted)) ? $tracker_fields->edi_access_restricted : null;
				$edi_access_nonpublic 	= (!empty($tracker_fields->edi_access_nonpublic)) ? $tracker_fields->edi_access_nonpublic : null;

				$period = array('milestone' => $milestone_trend->milestone,
								'edi_access_public' => 		$edi_access_public,
								'edi_access_restricted' =>  $edi_access_restricted,
								'edi_access_nonpublic' => 	$edi_access_nonpublic);
				$periods[] = $period;
			}
			$view_data['trends'] = $periods;
		} else {
			$view_data['trends'] = null;
		}


		// selected tab
		$view_data['selected_category'] = $selected_category;

		// pass tracker section breakdown model
		$view_data['section_breakdown'] = $this->campaign->tracker_sections_model();

		// pass config variable
		$view_data['config'] = array(
		    'max_remote_size' => $this->config->item('max_remote_size'),
            'archive_dir' => $this->config->item('archive_dir'),
            's3_bucket' => $this->config->item('s3_bucket'),
            's3_prefix' => $this->config->item('s3_prefix')
        );

		$this->load->view('office_detail', $view_data);

	}

	public function routes($route, $parameter1 = null, $parameter2 = null, $parameter3 = null, $parameter4 = null) {

		if($route == 'all') {
			return $this->index($milestone=null, $output=null, $show_all_offices = true);
		}

		if($route == 'detail') {
			return $this->detail($parameter1, $parameter2, $parameter3, $parameter4);
		}

		if($route == 'qa') {

			$this->load->model('campaign_model', 'campaign');
			$milestones = $this->campaign->milestones_model();
			$milestone 	= $this->campaign->milestone_filter(null, $milestones);

			return $this->index($milestone=$milestone->current, $output=null, $show_all_offices=true, $show_all_fields=false, $show_qa_fields=true);
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

	public function calculate_totals($offices) {
		$totals = array();
		$valid_metadata = array();
		foreach ($offices as $office) {
			if (!empty($office->tracker_fields)) {
				$tracker = json_decode($office->tracker_fields);
				$valid_metadata[] = $tracker->pdl_valid_metadata;
				foreach ($tracker as $field => $value) {
					if (!isset($totals[$field])) {
						$totals[$field] = array('office_count' => 0, 'total' => 0, 'average' => 0, 'type' => null, 'errors' => '');
					}

					if (is_numeric($value) OR strpos($value, '%') !== false) {

						if (strpos($value, '%') !== false) {
							if(empty($totals[$field]['type'])) $totals[$field]['type'] = 'percent';
							$value = $value * 0.01;

							if($totals[$field]['type'] == 'integer') {
								$totals[$field]['errors'] .= 'Inconsistent data type found on ' . $office->id . ' ';
							}

						} else {
							if(empty($totals[$field]['type'])) $totals[$field]['type'] = 'integer';
							if($totals[$field]['type'] == 'percent') {
								$totals[$field]['errors'] .= 'Inconsistent data type found on ' . $office->id . ' ';
							}
						}

						$totals[$field]['office_count']++;
						$totals[$field]['total'] = $totals[$field]['total'] + $value;
					}
				}

			}

		}

		foreach ($totals as $field => $total) {
			if ($total["office_count"] == 0) {
				unset($totals[$field]);
			} else {
				$totals[$field]["average"] = round(($total["total"] / $total["office_count"]), 2);
			}
		}

		return $totals;

	}



	public function get_trends($office_id) {

		$this->db->select('tracker_fields');
		$this->db->select('milestone');
		$this->db->from('datagov_campaign');
		$this->db->where('datagov_campaign.office_id', $office_id);
		$this->db->where("(datagov_campaign.crawl_status IS NULL OR datagov_campaign.crawl_status = 'final')");
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}

	}







}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
