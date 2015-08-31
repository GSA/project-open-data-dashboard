<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

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
     * 	- or -
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index($selected_milestone = null, $output = null, $show_all_offices = false, $show_all_fields = false, $show_qa_fields = false) {


        $this->load->model('campaign_model', 'campaign');
        $milestones = $this->campaign->milestones_model();

        $selected_milestone = ($this->input->get_post('milestone', TRUE)) ? $this->input->get_post('milestone', TRUE) : $selected_milestone;
        $selected_milestone = $selected_milestone === null ? $this->get_default_milestone() : $selected_milestone;

        $milestone = $this->campaign->milestone_filter($selected_milestone, $milestones);
        $milestones = $milestone->milestones;

        // Determine selected milestone. Defaults to previous milestone if not specified
        if (empty($selected_milestone)) {
            $milestone->selected_milestone = $milestone->previous;
        }

        if ($milestone->selected_milestone == $milestone->current) {
            $crawl_status_filter = 'current';
        } else {
            $crawl_status_filter = 'final';
        }

        $view_data = array();

        $view_data['cfo_offices'] = $this->get_dashboard_office_list($milestone->selected_milestone, $crawl_status_filter);

        if ($this->config->item('show_all_offices') || $show_all_offices) {

            $this->db->select('*');
            $this->db->from('offices');
            $this->db->join('ciogov_campaign', 'ciogov_campaign.office_id = offices.id', 'left');
            $this->db->where('ciogov_campaign.milestone', $milestone->selected_milestone);
            $this->db->where('offices.cfo_act_agency', 'false');
            $this->db->where('offices.reporting_authority_type', 'executive');
            $this->db->where('offices.no_parent', 'true');
            $this->db->order_by("offices.name", "asc");
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $view_data['executive_offices'] = $query->result();
                $query->free_result();
            }


            $this->db->select('*');
            $this->db->from('offices');
            $this->db->join('ciogov_campaign', 'ciogov_campaign.office_id = offices.id', 'left');
            $this->db->where('ciogov_campaign.milestone', $milestone->selected_milestone);
            $this->db->where('offices.cfo_act_agency', 'false');
            $this->db->where('offices.reporting_authority_type', 'independent');
            $this->db->where('offices.no_parent', 'true');
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
        $view_data['tracker'] = $this->campaign->tracker_model($milestone->selected_milestone);
        $view_data['section_breakdown'] = $this->campaign->tracker_sections_model($milestone->selected_milestone);
        $view_data['subsection_breakdown'] = $this->campaign->tracker_subsections_model($milestone->selected_milestone);

        // pass config variables
        $view_data['max_remote_size'] = $this->config->item('max_remote_size');

        $view_data['show_all_fields'] = $show_all_fields;
        $view_data['show_qa_fields'] = $show_qa_fields;

        // override section model if we're just showing QA
        if ($show_qa_fields)
            $view_data['section_breakdown'] = $this->campaign->qa_sections_model();

        if ($output == 'json') {
            return $view_data;
        }

        $this->load->view('office_list', $view_data);
    }

    /**
     * Code Ignitor doesn't support unions and not sure about
     * stored procedures - there seem to be difficulties with that
     * also. So, run two queries and only include results from 2nd
     * that are not in first.
     *
     * @param <string> $selected_milestone
     * @returns <array>
     */
    public function get_dashboard_office_list($selected_milestone, $crawl_status_filter)
    {
      $cfo_offices = array();
      $cfo_offices2 = array();

      $this->db->select('*');
      $this->db->from('offices');
      $this->db->join('ciogov_campaign', 'ciogov_campaign.office_id = offices.id', 'left');
      $this->db->where("(`ciogov_campaign`.`milestone` IS NULL OR `ciogov_campaign`.`milestone` = '". $selected_milestone ."')");
      $this->db->where('offices.cfo_act_agency', 'true');
      $this->db->where('offices.no_parent', 'true');
      $this->db->where("(ciogov_campaign.crawl_status IS NULL OR ciogov_campaign.crawl_status = '$crawl_status_filter')");
      $this->db->order_by("offices.name", "asc");

      $query = $this->db->get();
      if ($query->num_rows() > 0) {
        $cfo_offices = $query->result();
        $query->free_result();
      }

      // Get all the office ids from above results
      $idList = "";
      $ids = array();
      if(count($cfo_offices)) {
        $ids = array();
        foreach($cfo_offices as $cfo_office) {
          $ids[] = $cfo_office->id;
      }
        $idList = implode(",", $ids);
      }

      $this->db->select('*');
      $this->db->from('offices');
      $this->db->join('ciogov_campaign', 'ciogov_campaign.office_id = offices.id', 'left');
      $this->db->where('offices.cfo_act_agency', 'true');
      $this->db->where('offices.no_parent', 'true');
      if($idList) {
        $this->db->where("offices.id NOT IN ($idList)");
      }
      $this->db->group_by('offices.id');
      $query2 = $this->db->get();
      if ($query2->num_rows() > 0) {
        $cfo_offices2 = $query2->result();
        $query2->free_result();
      }

      /**
       * Have had unexpected results with array_merge so not used here.
       * Only need to resort if we had results
       */
      if(count($cfo_offices2)) {
        foreach($cfo_offices2 as $cfo_office2) {
          $cfo_offices[] = $cfo_office2;
        }

        function cmp($a, $b)
        {
          return strcmp($a->name, $b->name);
        }

         usort($cfo_offices, 'cmp');
      }

      return $cfo_offices;
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

    public function detail($id, $milestone = null, $status = null, $status_id = null) {

        $this->load->helper('api');
        $this->load->model('campaign_model', 'campaign');
        $markdown_extra = new Michelf\MarkdownExtra();

        $milestones = $this->campaign->milestones_model();
        $selected_milestone = ($this->input->get_post('milestone', TRUE)) ? $this->input->get_post('milestone', TRUE) : $milestone;

        $selected_category = ($this->input->get_post('highlight', TRUE)) ? $this->input->get_post('highlight', TRUE) : null;

        $milestone = $this->campaign->milestone_filter($selected_milestone, $milestones);
        $selected_milestone_index = array_search($selected_milestone,array_keys($milestones))+1;
        
        
        $view_data = array();

        // pass milestones data model
        $view_data['milestone'] = $milestone;

        // pass tracker data model
        $view_data['tracker_model'] = $this->campaign->tracker_model($selected_milestone);
        
        // indicate "tracker fields" that are placeholders for tables, not actual fields
        $view_data['tracker_field_tables'] = array('pa_bureau_it_leadership_table', 'pa_cio_governance_board_table', '');

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
                    if (!empty($note_list[$note_field]->current->note)) {

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
            $view_data['office_campaign'] = $this->campaign->ciogov_office($view_data['office']->id, $milestone->selected_milestone, null, $status_id);


            // Get the IDs of daily crawls before and after this date
            $crawls_before = $this->campaign->ciogov_office_crawls($view_data['office']->id, $milestone->selected_milestone, $view_data['office_campaign']->status_id, '<', '5');
            $crawls_after = $this->campaign->ciogov_office_crawls($view_data['office']->id, $milestone->selected_milestone, $view_data['office_campaign']->status_id, '>', '5');

            $view_data['nearby_crawls'] = array_merge(array_reverse($crawls_before), $crawls_after);


            // If we have a blank slate, populate the data model
            if (empty($view_data['office_campaign'])) {
                $view_data['office_campaign'] = $this->campaign->ciogov_model();
            }

            // Make sure tracker data uses full tracker model
            if (isset($view_data['office_campaign']->tracker_fields)) {

                $tracker_fields = ($view_data['office_campaign']->tracker_fields) ? json_decode($view_data['office_campaign']->tracker_fields) : new stdClass();
                
                $clone_tracker_model = clone $view_data['tracker_model'];
                foreach ($view_data['tracker_model'] as $field_name => $value) {
                  //disable tracker field in data+model if not in correct milestone
                  if(isset($value->active) && (intval($selected_milestone_index) < intval($value->active))){
                    unset($tracker_fields->$field_name);
                    unset($clone_tracker_model->$field_name);
                  }
                  else{
                    $tracker_fields->$field_name = (isset($tracker_fields->$field_name)) ? $tracker_fields->$field_name : null;  
                  }
                }
                $view_data['tracker_model'] = $clone_tracker_model;//updates model if any tracker fields were disabled
                
                $view_data['office_campaign']->tracker_fields = json_encode($tracker_fields);
            }

            //$view_data['bureau_governance_detail'] = $this->getBureauGovernanceDetail();
            $view_data = $this->getRecommendationDetail($view_data, $milestone->selected_milestone);

            if ($this->config->item('show_all_offices')) {

                // Get sub offices
                $this->db->select('*');
                $this->db->from('offices');
                $this->db->join('ciogov_campaign', 'ciogov_campaign.office_id = offices.id', 'left');
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
        $view_data['section_breakdown'] = $this->campaign->tracker_sections_model($selected_milestone);

        // pass config variable
        $view_data['config'] = array('max_remote_size' => $this->config->item('max_remote_size'), 'archive_dir' => $this->config->item('archive_dir'));

        //var_dump($view_data) exit;
        $this->load->view('office_detail', $view_data);
    }

    /**
     * Get the table html to display the GAO Recommendations for one office.
     * For the office_detail_automated_tracker view, get the recommendation tracker
     * fields into an object.
     *
     * @param <int> $office_id
     * @param <date> $selected_milestone
     * @return <array>
     */
    public function getRecommendationDetail($view_data, $selected_milestone)
    {
      $office_id = $view_data['office']->id;
      $office = $this->campaign->ciogov_office_recommendations($office_id, $selected_milestone);

      $this->load->model('Recommendation_model', 'recommendation', TRUE);

      $detail = $this->recommendation->get_office_detail($office);
      $view_data['recommendation_detail'] = $detail;

      $status = $this->recommendation->get_office_detail_status($office);
      $view_data['office_campaign']->recommendation_status = $status;

      return $view_data;
    }

    public function getBureauGovernanceDetail() {
      //$office_id = $view_data['office']->id;

    }

    public function routes($route, $parameter1 = null, $parameter2 = null, $parameter3 = null, $parameter4 = null) {

        if ($route == 'all') {
            return $this->index($milestone = null, $output = null, $show_all_offices = true);
        }

        if ($route == 'detail') {
            return $this->detail($parameter1, $parameter2, $parameter3, $parameter4);
        }

        // check if it's a milestone date
        $d = DateTime::createFromFormat('Y-m-d', $route);
        if ($d && $d->format('Y-m-d') == $route) {

            if ($parameter1 == 'all') {
                $show_all_offices = true;
            } else {
                $show_all_offices = false;
            }

            if ($parameter1 == 'full' || $parameter2 == 'full') {
                $show_all_fields = true;
            } else {
                $show_all_fields = false;
            }

            if ($parameter1 == 'qa' || $parameter2 == 'qa') {
                $show_qa_fields = true;
            } else {
                $show_qa_fields = false;
            }


            return $this->index($milestone = $route, $output = null, $show_all_offices, $show_all_fields, $show_qa_fields);
        }
    }

    /**
     * Get default milestone (the first milestone after today's date)
     *
     * @return string - date in YYYY-MM-DD format
     */
    public function get_default_milestone() {
        $this->load->model('campaign_model', 'campaign');
        $milestones = $this->campaign->milestones_model();
        foreach ($milestones as $date => $name) {
            if (date(strtotime($date)) > date('U')) {
                return $date;
            }
        }
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
