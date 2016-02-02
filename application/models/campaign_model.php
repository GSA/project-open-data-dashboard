<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class campaign_model extends CI_Model {

	var $jurisdictions 		= array();
	var $protected_field	= null;
	var $validation_counts  = null;
	var $current_office_id  = null;
	var $validation_pointer = null;
	var $validation_log 	= null;
	var $schema 			= null;


	public function __construct(){
		parent::__construct();

		$this->load->helper('api');
        $this->load->library('DataJsonParser');

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

	public function datagov_office($office_id, $milestone = null, $crawl_status = null, $status_id = null) {

		$this->db->select('*');
		$this->db->where('office_id', $office_id);

		// If we got a status_id, query specifically for that
		if(!empty($status_id)){
			$this->db->where('status_id', $status_id);
		} else {
		// otherwise see if we need to filter by crawl status	
			if(!empty($crawl_status)) {
				$this->db->where('crawl_status', $crawl_status);
			} else {
				$this->db->where("(crawl_status IS NULL OR crawl_status='current' OR crawl_status='final')");	
			}
		}


		if($milestone) $this->db->where('milestone', $milestone);
		$this->db->limit(1);

		$query = $this->db->get('datagov_campaign');

		if ($query->num_rows() > 0) {
		   return $query->row();
		} else {
		   return false;
		}

	}

	public function datagov_office_crawls($office_id, $milestone = null, $status_id, $direction, $limit) {

		$this->db->select('status_id, crawl_start, crawl_end');
		$this->db->where('office_id', $office_id);
		$this->db->where('milestone', $milestone);
		$this->db->where('status_id ' . $direction, $status_id);	

		if ($direction == '<') $order_dir = 'DESC';
		if ($direction == '>') $order_dir = 'ASC';

		$this->db->order_by('status_id', $order_dir);

		$query = $this->db->get('datagov_campaign', $limit);

		return $query->result_array();

	}	


	public function datagov_model() {

		$model = new stdClass();

		$model->status_id						= null;
		$model->office_id						= null;
		$model->milestone						= null;
		$model->crawl_start						= null;
		$model->crawl_end						= null;
		$model->crawl_status					= null;
		$model->contact_name					= null;
		$model->contact_email					= null;
		$model->datajson_status					= null;
		$model->datapage_status					= null;
		$model->digitalstrategy_status			= null;

		$model->tracker_fields					= '';
		$model->tracker_status					= null;

		return $model;
	}


	public function qa_sections_model() {


		$model = new stdClass();
		$field = new stdClass();

		$field->label 							= null;
		$field->total_field						= null;
		$field->success_basis					= null;
		$field->success_weight					= null;
		$field->value							= null;

		$model->total_records 							=   clone $field;
		$model->total_records->label 					=   'Public Datasets';
		
		$model->valid_count 							=   clone $field;
		$model->valid_count->label 						=   'Valid Metadata';
		$model->valid_count->total_field 				=   'total_records';
		$model->valid_count->success_basis				= 	'high';
		$model->valid_count->success_weight				=	70;
			
		$model->programs 								=   clone $field;
		$model->programs->label 						=   'Programs';
		
		$model->bureaus 								=   clone $field;
		$model->bureaus->label 							=   'Bureaus';

		$model->accessLevel_public 						=   clone $field;
		$model->accessLevel_public->label 				=   'Public Datasets';
		$model->accessLevel_public->total_field 		=   'total_records';

		$model->accessLevel_nonpublic 					=   clone $field;
		$model->accessLevel_nonpublic->label 			=   'Restricted Datasets';
		$model->accessLevel_nonpublic->total_field 		=   'total_records';

		$model->accessLevel_restricted 					=   clone $field;
		$model->accessLevel_restricted->label 			=   'Non-public Datasets';
		$model->accessLevel_restricted->total_field 	=   'total_records';

		$model->accessURL_present 						=   clone $field;
		$model->accessURL_present->label 				=   'Datasets with downloads';
		$model->accessURL_present->total_field 			=   'total_records';
	
		$model->accessURL_total 						=   clone $field;
		$model->accessURL_total->label 					=   'Total Download URLs';
	
		$model->accessURL_working 						=   clone $field;
		$model->accessURL_working->label 				=   'Working Download URLs';
		$model->accessURL_working->total_field 			=   'accessURL_total';
		$model->accessURL_working->success_basis		= 	'high';
		$model->accessURL_working->success_weight		=	30;
	
		$model->accessURL_format 						=   clone $field;
		$model->accessURL_format->label 				=   'Correct Format';
		$model->accessURL_format->total_field 			=   'accessURL_working';
		$model->accessURL_format->success_basis			= 	'high';
		$model->accessURL_format->success_weight		=	20;
		
		$model->accessURL_html 							=   clone $field;
		$model->accessURL_html->label 					=   'HTML Downloads';
		$model->accessURL_html->total_field 			=   'accessURL_working';
		$model->accessURL_html->success_basis			= 	'low';
		$model->accessURL_html->success_weight			=	.35;

		$model->accessURL_pdf 							=   clone $field;
		$model->accessURL_pdf->label 					=   'PDF Downloads';
		$model->accessURL_pdf->total_field 				=   'accessURL_working';
		$model->accessURL_pdf->success_basis			= 	'low';
		$model->accessURL_pdf->success_weight			=	.15;

		return $model;

	}



	public function tracker_model() {

		$model = new stdClass();
		$field = new stdClass();

		$field->type 								= null;
		$field->value								= null;
		$field->label								= null;
		$field->placeholder							= null;


		// Enterprise Data Inventory

		$model->edi_aggregate_score					= clone $field;
		$model->edi_aggregate_score->label 			= "Overall Progress this Milestone";
		$model->edi_aggregate_score->type 			= "traffic";

		$model->edi_selected_best_practice			= clone $field;
		$model->edi_selected_best_practice->label	= "Selected to highlight a best practice";
		$model->edi_selected_best_practice->type 	= "select";

		$model->edi_updated							= clone $field;
		$model->edi_updated->label 					= "Inventory Updated this Quarter";
		$model->edi_updated->type 					= "select";

		$model->edi_datasets						= clone $field;
		$model->edi_datasets->label 				= "Number of Datasets";
		$model->edi_datasets->type 					= "string";

        $model->edi_apis                            = clone $field;
        $model->edi_apis->label                     = "Number of APIs";
        $model->edi_apis->type                      = "string";		

		$model->edi_schedule_delivered				= clone $field;
		$model->edi_schedule_delivered->label 		= "Schedule Delivered";
		$model->edi_schedule_delivered->type 		= "select";

		$model->edi_bureaus							= clone $field;
		$model->edi_bureaus->label 					= "Bureaus represented";
		$model->edi_bureaus->type 					= "string";

		$model->edi_programs						= clone $field;
		$model->edi_programs->label 				= "Programs represented";
		$model->edi_programs->type 					= "string";

		$model->edi_access_public					= clone $field;
		$model->edi_access_public->label 			= "Access Level = Public";
		$model->edi_access_public->type 			= "string";

		$model->edi_access_restricted				= clone $field;
		$model->edi_access_restricted->label 		= "Access Level = Restricted";
		$model->edi_access_restricted->type 		= "string";

		$model->edi_access_nonpublic				= clone $field;
		$model->edi_access_nonpublic->label 		= "Access Level = Non-Public";
		$model->edi_access_nonpublic->type 			= "string";

		$model->edi_superset						= clone $field;
		$model->edi_superset->label 				= "Inventory > Public listing";
		$model->edi_superset->type 					= "select";

		$model->edi_progress_evaluation				= clone $field;
		$model->edi_progress_evaluation->label 		= "Percentage growth in records since last quarter";
		$model->edi_progress_evaluation->type 		= "string";

		$model->edi_schedule_risk					= clone $field;
		$model->edi_schedule_risk->label 			= "Schedule Risk for Nov 30, 2014";
		$model->edi_schedule_risk->type 			= "traffic";

        $model->edi_quality_check                   = clone $field;
        $model->edi_quality_check->label            = "Spot Check - datasets listed by search engine";
        $model->edi_quality_check->type             = "string";

        $model->edi_public_release                  = clone $field;
        $model->edi_public_release->label           = "PDL includes non-public datasets and no redactions";
        $model->edi_public_release->type            = "select";

        $model->edi_license_present                 = clone $field;
        $model->edi_license_present->label          = "License specified";
        $model->edi_license_present->type           = "string";


		// Public Data Listing

		$model->pdl_aggregate_score					= clone $field;
		$model->pdl_aggregate_score->label 			= "Overall Progress this Milestone";
		$model->pdl_aggregate_score->type 			= "traffic";

		$model->pdl_selected_best_practice			= clone $field;
		$model->pdl_selected_best_practice->label	= "Selected to highlight a best practice";
		$model->pdl_selected_best_practice->type 	= "select";

		$model->pdl_datasets						= clone $field;
		$model->pdl_datasets->label 				= "Number of Public Datasets";
		$model->pdl_datasets->type 					= "string";

		$model->pdl_downloadable					= clone $field;
		$model->pdl_downloadable->label 			= "Number of Public Datasets with File Downloads";
		$model->pdl_downloadable->type 				= "string";

        $model->pdl_apis                            = clone $field;
        $model->pdl_apis->label                     = "Number of Public APIs";
        $model->pdl_apis->type                      = "string";	

		$model->pdl_collections						= clone $field;
		$model->pdl_collections->label	 			= "Number of Public Collections";
		$model->pdl_collections->type 				= "string";
		
		$model->pdl_link_total						= clone $field;
		$model->pdl_link_total->label 				= "Total number of Public access and download links (Note: There can be more than 1 per dataset.)";
		$model->pdl_link_total->type 				= "string";

		$model->pdl_link_check						= clone $field;
		$model->pdl_link_check->label 				= "Quality Check: Links are sufficiently working";
		$model->pdl_link_check->type 				= "traffic";	

		$model->pdl_link_2xx						= clone $field;
		$model->pdl_link_2xx->label 				= "Quality Check: Accessible links";
		$model->pdl_link_2xx->type 					= "string";

		$model->pdl_link_3xx						= clone $field;
		$model->pdl_link_3xx->label 				= "Quality Check: Redirected links";
		$model->pdl_link_3xx->type 					= "string";		

		$model->pdl_link_5xx						= clone $field;
		$model->pdl_link_5xx->label 				= "Quality Check: Error links";
		$model->pdl_link_5xx->type 					= "string";

		$model->pdl_link_4xx						= clone $field;
		$model->pdl_link_4xx->label 				= "Quality Check: Broken links";
		$model->pdl_link_4xx->type 					= "string";

		$model->pdl_growth							= clone $field;
		$model->pdl_growth->label 					= "Percentage growth in records since last quarter";
		$model->pdl_growth->type 					= "string";

		$model->pdl_valid_metadata					= clone $field;
		$model->pdl_valid_metadata->label 			= "Valid Metadata";
		$model->pdl_valid_metadata->type 			= "string";

		$model->pdl_slashdata						= clone $field;
		$model->pdl_slashdata->label 				= "/data";
		$model->pdl_slashdata->type 				= "select";

		$model->pdl_datajson						= clone $field;
		$model->pdl_datajson->label 				= "/data.json";
		$model->pdl_datajson->type 					= "select";

		$model->pdl_datagov_harvested				= clone $field;
		$model->pdl_datagov_harvested->label 		= "Harvested by data.gov";
		$model->pdl_datagov_harvested->type 		= "select";

		$model->pdl_datagov_view_count				= clone $field;
		$model->pdl_datagov_view_count->label 		= "Views on data.gov for the quarter";
		$model->pdl_datagov_view_count->type 		= "string";

		// Public Engagement

		$model->pe_aggregate_score					= clone $field;
		$model->pe_aggregate_score->label 			= "Overall Progress this Milestone";
		$model->pe_aggregate_score->type 			= "traffic";

		$model->pe_selected_best_practice			= clone $field;
		$model->pe_selected_best_practice->label	= "Selected to highlight a best practice";
		$model->pe_selected_best_practice->type 	= "select";

		$model->pe_feedback_specified				= clone $field;
		$model->pe_feedback_specified->label 		= "Description of feedback mechanism delivered";
		$model->pe_feedback_specified->type 		= "select";

		$model->pe_prioritization					= clone $field;
		$model->pe_prioritization->label 			= "Data release is prioritized through public engagement";
		$model->pe_prioritization->type 			= "traffic";

		$model->pe_dialogue							= clone $field;
		$model->pe_dialogue->label 					= "Feedback loop is closed, 2 way communication";
		$model->pe_dialogue->type 					= "traffic";

		$model->pe_reference						= clone $field;
		$model->pe_reference->label 				= "Link to or description of Feedback Mechanism";
		$model->pe_reference->type 					= "string";


		// Privacy & Security

		$model->ps_aggregate_score					= clone $field;
		$model->ps_aggregate_score->label 			= "Overall Progress this Milestone";
		$model->ps_aggregate_score->type 			= "traffic";

		$model->ps_selected_best_practice			= clone $field;
		$model->ps_selected_best_practice->label	= "Selected to highlight a best practice";
		$model->ps_selected_best_practice->type 	= "select";

		$model->ps_publication_process				= clone $field;
		$model->ps_publication_process->label 		= "Data Publication Process Delivered";
		$model->ps_publication_process->type 		= "traffic";

		$model->ps_publication_process_qa			= clone $field;
		$model->ps_publication_process_qa->label 	= "Information that should not to be made public is documented with agency's OGC";
		$model->ps_publication_process_qa->type 	= "traffic";


		// Human Capital

		$model->hc_aggregate_score					= clone $field;
		$model->hc_aggregate_score->label 			= "Overall Progress this Milestone";
		$model->hc_aggregate_score->type 			= "traffic";

		$model->hc_selected_best_practice			= clone $field;
		$model->hc_selected_best_practice->label	= "Selected to highlight a best practice";
		$model->hc_selected_best_practice->type 	= "select";		

		$model->hc_lead								= clone $field;
		$model->hc_lead->label 						= "Open Data Primary Point of Contact";
		$model->hc_lead->type 						= "string";

		$model->hc_contacts							= clone $field;
		$model->hc_contacts->label 					= "POCs identified for required responsibilities";
		$model->hc_contacts->type 					= "traffic";

		// Use & Impact

		$model->ui_aggregate_score					= clone $field;
		$model->ui_aggregate_score->label 			= "Overall Progress this Milestone";
		$model->ui_aggregate_score->type 			= "traffic";

		$model->ui_selected_best_practice			= clone $field;
		$model->ui_selected_best_practice->label	= "Selected to highlight a best practice";
		$model->ui_selected_best_practice->type 	= "select";

		$model->ui_identified_users					= clone $field;
		$model->ui_identified_users->label 			= "Identified 5 data improvements this quarter";
		$model->ui_identified_users->type 			= "select";

		$model->ui_primary_uses						= clone $field;
		$model->ui_primary_uses->label 				= "Primary Uses";
		$model->ui_primary_uses->type 				= "string";

		$model->ui_value_impact						= clone $field;
		$model->ui_value_impact->label 				= "Value or impact of data";
		$model->ui_value_impact->type 				= "string";

		$model->ui_primary_discovery				= clone $field;
		$model->ui_primary_discovery->label 		= "Primary data discovery channels";
		$model->ui_primary_discovery->type 			= "string";

		$model->ui_user_suggest_usability			= clone $field;
		$model->ui_user_suggest_usability->label 	= "User suggestions on improving data usability";
		$model->ui_user_suggest_usability->type 	= "string";

		$model->ui_user_suggest_releases			= clone $field;
		$model->ui_user_suggest_releases->label 	= "User suggestions on additional data releases";
		$model->ui_user_suggest_releases->type 		= "string";

        $model->ui_dap_tracking                     = clone $field;
        $model->ui_dap_tracking->label              = "Digital Analytics Program on /data";
        $model->ui_dap_tracking->type               = "select"; 		

		return $model;
}



	public function tracker_sections_model() {

        $section_breakdown = array(
                                    "edi" => "Enterprise Data Inventory", 
                                    "pdl" => "Public Data Listing", 
                                    "pe" => "Public Engagement", 
                                    "ps" => "Privacy &amp; Security", 
                                    "hc" => "Human Capital",
                                    "ui" => "Use &amp; Impact"
                                );  

        return $section_breakdown;

	}


	public function tracker_review_model() {

		$model = new stdClass();

		$model->status 						= null;
		$model->reviewer_name				= null;
		$model->reviewer_email				= null;
		$model->last_updated				= null;
		$model->last_editor					= null;

		return $model;

	}


	public function milestones_model() {

		$milestones = array(
							"2013-11-30" => "Milestone 1",
							"2014-02-28" => "Milestone 2",
							"2014-05-31" => "Milestone 3",
							"2014-08-31" => "Milestone 4",
							"2014-11-30" => "Milestone 5",
							"2015-02-28" => "Milestone 6",
							"2015-05-31" => "Milestone 7",
							"2015-08-31" => "Milestone 8",
							"2015-11-30" => "Milestone 9",
							"2016-02-28" => "Milestone 10",
							"2016-05-31" => "Milestone 11",
							"2016-08-31" => "Milestone 12",
							"2016-11-30" => "Milestone 13"
							);

		return $milestones;
	}


	public function milestone_filter($selected_milestone, $milestones) {

		// Sets the first milestone in the future as the current and last available milestone
	    foreach ($milestones as $milestone_date => $milestone) {
	        if (strtotime($milestone_date) >= strtotime(date('Y-m-d'))) {
	            
	        	if(empty($current_milestone)) {
	        		$current_milestone = $milestone_date;	
	        	} else {
	        		unset($milestones[$milestone_date]);
	        	}	            
	        } 
	    }

	    // if we didn't explicitly select a milestone, use the current one
		if(empty($selected_milestone)) {
			$selected_milestone = $current_milestone;
			$specified = "false";			
		} else {
			$specified = "true";
		}

		reset($milestones);

		// determine previous milestone
		while (key($milestones) !== $current_milestone) next($milestones);
		prev($milestones);
		$previous_milestone = key($milestones);

		reset($milestones);

		$response = new stdClass();

		$response->selected_milestone 	= $selected_milestone;
		$response->current 				= $current_milestone;
		$response->previous 			= $previous_milestone;
		$response->specified			= $specified;

		$response->milestones 			= $milestones;

		return $response;

	}



	public function note_model() {

		$model = new stdClass();

		$model->date							= null;
		$model->author							= null;
		$model->note							= null;
		$model->note_html						= null;

		$note = new stdClass();

		$note->current							= $model;

		return $note;
	}








	public function datajson_crawl() {

		$model = new stdClass();

		$model->id 					= null;
		$model->office_id 			= null;
		$model->datajson_url 		= null;
		$model->crawl_cycle 		= null;
		$model->crawl_status 		= null;
		$model->start 				= null;
		$model->end 				= null;

		return $model;
	}


	public function metadata_record() {

		$model = new stdClass();

		$model->id 						= null;
		$model->office_id 				= null;
		$model->datajson_url 			= null;
		$model->identifier 				= null;
		$model->json_body 				= null;
		$model->schema_valid 			= null;
		$model->validation_errors 		= null;
		$model->last_modified_header 	= null;
		$model->last_modified_body 		= null;
		$model->last_crawled 			= null;
		$model->crawl_cycle 			= null;

		return $model;
	}



	public function metadata_resource() {

		$model = new stdClass();

		$model->id                         = null;
 		$model->metadata_record_id         = null;
 		$model->metadata_record_identifier = null;
 		$model->url                        = null;

		return $model;
	}








	public function uri_header($url, $redirect_count = 0) {

		$tmp_dir = $tmp_dir = $this->config->item('archive_dir');

		$status = curl_header($url, true, $tmp_dir);
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



	public function validate_datajson($datajson_url = null, $datajson = null, $headers = null, $schema = null, $return_source = false, $quality = false, $component = null) {


		if ($datajson_url) {

			$datajson_header = ($headers) ? $headers : $this->campaign->uri_header($datajson_url);

			$errors = array();

			// Max file size
			$max_remote_size = $this->config->item('max_remote_size');


			// Only download the data.json if we need to
			if(empty($datajson_header['download_content_length']) || 
				$datajson_header['download_content_length'] < 0 || 
				(!empty($datajson_header['download_content_length']) && 
				$datajson_header['download_content_length'] > 0 && 
				$datajson_header['download_content_length'] < $max_remote_size)) {

				// Load the JSON
				$opts = array(
							  'http'=>array(
							    'method'=>"GET",
							    'user_agent'=>"Data.gov data.json crawler"
							  )
							);

				$context = stream_context_create($opts);
				
				$datajson = @file_get_contents($datajson_url, false, $context, -1, $max_remote_size+1);

				if ($datajson == false) {

					$datajson = curl_from_json($datajson_url, false, false);

					if(!$datajson) {
						$errors[] = "File not found or couldn't be downloaded";	
					}
					
				} 

			}


			if(!empty($datajson) && (empty($datajson_header['download_content_length']) || $datajson_header['download_content_length'] < 0)) {
				$datajson_header['download_content_length'] = strlen($datajson);
			}

			// See if it exceeds max size
			if($datajson_header['download_content_length'] > $max_remote_size) {

				//$filesize = human_filesize($datajson_header['download_content_length']);
				//$errors[] = "The data.json file is " . $filesize . " which is currently too large to parse with this tool. Sorry.";				

				// Increase the timeout limit
			    @set_time_limit(6000);	
			
				$this->load->helper('file');

				if ($rawfile = $this->archive_file('datajson-lines', $this->current_office_id, $datajson_url)) {	

			        $outfile = $rawfile . '.lines.json';

			        $stream = fopen($rawfile, 'r'); 
			        $out_stream = fopen($outfile, 'w+');

			        $listener = new DataJsonParser();
			        $listener->out_file = $out_stream;

					if ($this->environment == 'terminal' OR $this->environment == 'cron') {
						echo 'Attempting to convert to JSON lines' . PHP_EOL;
					}

			        try {
			            $parser = new JsonStreamingParser_Parser($stream, $listener);
			            $parser->parse();
			        } catch (Exception $e) {
			            fclose($stream);
			            throw $e;
			        }

			        // Get the dataset count
			        $datajson_lines_count = $listener->_array_count;

			        // Delete temporary raw source file
			        unlink($rawfile);

			        $out_stream = fopen($outfile, 'r+');	

					$chunk_cycle = 0;
					$chunk_size = 200;					
					$chunk_count = intval(ceil($datajson_lines_count/$chunk_size));
					$buffer = '';
		
					$response = array();
					$response['errors'] = array();		

					if($quality !== false) {
						$response['qa'] = array();
					}			

					echo "Analyzing $datajson_lines_count lines in $chunk_count chunks of $chunk_size lines each" . PHP_EOL;

					while($chunk_cycle < $chunk_count) {
					
						$buffer = '';
						$datajson_qa = null;
						$counter = 0; 				

						if ($chunk_cycle > 0) {
							$key_offset = $chunk_size * $chunk_cycle;
						} else {
							$key_offset = 0;
						}

						$next_offset = $key_offset + $chunk_size;
						//echo "Analyzing chunk $chunk_cycle of $chunk_count ($key_offset to $next_offset of $datajson_lines_count)" . PHP_EOL;


						if ($chunk_cycle == 0) {
							$json_header = fgets($out_stream);
						}

						while (($buffer .= fgets($out_stream)) && $counter < $chunk_size) {
					        $counter++;
					    }		

					    $buffer = $json_header . $buffer;
					    $buffer = substr($buffer, 0, strlen($buffer) - 2) . ']}';

						$validator = $this->campaign->jsonschema_validator($buffer, 'federal-v1.1');				

						if(!empty($validator['errors']) ) {

							$response['errors'] = array_merge($response['errors'], $this->process_validation_errors($validator['errors'], $key_offset));

						}

						if($quality !== false) {
							$datajson_qa = $this->campaign->datajson_qa($buffer, 'federal-v1.1', $quality, $component);	

							if(!empty($datajson_qa)) {
								$response['qa'] = array_merge_recursive($response['qa'], $datajson_qa);	
							}	

						}						

					    $chunk_cycle++;				
					}

			        // Delete json lines file
			        unlink($outfile);					

					// ###################################################################
					// Needs to be refactored into separate function
					// ###################################################################


						// Sum QA counts 
						if(!empty($response['qa'])) {


							if(!empty($response['qa']['bureauCodes'])) {
								$response['qa']['bureauCodes'] = array_keys($response['qa']['bureauCodes']);
							}

							if(!empty($response['qa']['programCodes'])) {
								$response['qa']['programCodes'] = array_keys($response['qa']['programCodes']);
							}

							$sum_array_fields = array('API_total', 
													  'collections_total',
													  'downloadURL_present', 
													  'downloadURL_total', 
													  'accessURL_present', 
													  'accessURL_total', 
													  'accessLevel_public', 
													  'accessLevel_restricted', 
													  'accessLevel_nonpublic',
													  'license_present',
													  'redaction_present',
													  'redaction_no_explanation');

							foreach ($sum_array_fields as $array_field) {
								if(!empty($response['qa'][$array_field]) && is_array($response['qa'][$array_field])) {					
									$response['qa'][$array_field] = array_sum($response['qa'][$array_field]);					 
								}	
							}

							// Sum validation counts
							if (!empty($response['qa']['validation_counts']) && is_array($response['qa']['validation_counts'])) {
								foreach ($response['qa']['validation_counts'] as $validation_key => $validation_count) {

									if(is_array($response['qa']['validation_counts'][$validation_key])) {
										$response['qa']['validation_counts'][$validation_key] = array_sum($response['qa']['validation_counts'][$validation_key]);
									}

								}
							}

						}
						

						$response['valid'] = (empty($response['errors'])) ? true : false;
						$response['valid_json'] = true;

						$response['total_records'] = $datajson_lines_count;		

						if(!empty($datajson_header['download_content_length'])) {
							$response['download_content_length'] = $datajson_header['download_content_length'];
						}

						if(empty($response['errors'])) {
							$response['errors'] = false;
						}
						
						return $response;


			// ###################################################################



				} else {
					$errors[] = "File not found or couldn't be downloaded";	
				}				
		
			}



			// See if it's valid JSON 
			if(!empty($datajson) && $datajson_header['download_content_length'] < $max_remote_size) {

				// See if raw file is valid
				$raw_valid_json = is_json($datajson);

				// See if we can clean up the file to make it valid
				if(!$raw_valid_json) {
					$datajson_processed = json_text_filter($datajson);
					$valid_json 		= is_json($datajson_processed);
				} else {
					$valid_json = true;
				}

				if ($valid_json !== true) {
					$errors[] = 'The validator was unable to determine if this was valid JSON';
				}				
			}

			if(!empty($errors)) {

				$valid_json 	= (isset($valid_json)) ? $valid_json : null;
				$raw_valid_json = (isset($raw_valid_json)) ? $raw_valid_json : null;

				$response = array(
								'raw_valid_json' => $raw_valid_json,
								'valid_json' => $valid_json, 
								'valid' => false, 
								'fail' => $errors, 
								'download_content_length' => $datajson_header['download_content_length']
								);


				if($valid_json && $return_source === false) {
					$catalog = json_decode($datajson_processed);

					if ($schema == 'federal-v1.1' OR $schema == 'non-federal-v1.1') {
						$response['total_records'] = count($catalog->dataset);
					} else {
						$response['total_records'] = count($catalog);
					}

					
				}
								
				return $response;
			}

		}


		// filter string for json conversion if we haven't already
		if ($datajson && empty($datajson_processed)) {
			$datajson_processed = json_text_filter($datajson);
		}


		// verify it's valid json
		if($datajson_processed) {
			if(!isset($valid_json)) {
				$valid_json = is_json($datajson_processed);
			}
		}


		if ($datajson_processed && $valid_json) {

			$datajson_decode = json_decode($datajson_processed);

			if(!empty($datajson_decode->conformsTo) 
				&& $datajson_decode->conformsTo == 'https://project-open-data.cio.gov/v1.1/schema') {


				if($schema !== 'federal-v1.1' && $schema !== 'non-federal-v1.1' ) {

					if ($schema == 'federal') {
						$schema = 'federal-v1.1';						
					} else if ($schema == 'non-federal') {
						$schema = 'non-federal-v1.1';
					} else {
						$schema = 'federal-v1.1';
					}

				}

				$this->schema = $schema;

			}

			if($schema == 'federal-v1.1' && empty($datajson_decode->dataset)) {				
				$errors[] = "This file does not appear to be using the federal-v1.1 schema";	
				$response = array(
								'raw_valid_json' => $raw_valid_json,
								'valid_json' => $valid_json, 
								'valid' => false, 
								'fail' => $errors
								);
				return $response;				
			}


			if($schema !== 'federal-v1.1' && $schema !== 'non-federal-v1.1' ) {
				$chunk_size = 500;				
				$datajson_chunks = array_chunk($datajson_decode, $chunk_size);
			} else {
				$datajson_chunks = array($datajson_decode);
			}
			

			$response = array();
			$response['errors'] = array();

			if($quality !== false) {
				$response['qa'] = array();
			}

			// save detected schema version to output
			$response['schema_version'] = $schema;			

			foreach ($datajson_chunks as $chunk_count => $chunk) {

				$chunk = json_encode($chunk);
				$validator = $this->campaign->jsonschema_validator($chunk, $schema);
			
				if(!empty($validator['errors']) ) {

					if ($chunk_count) {
						$key_offset = $chunk_size * $chunk_count;
						$key_offset = $key_offset;
					} else {
						$key_offset = 0;
					}

					$response['errors'] = $response['errors'] + $this->process_validation_errors($validator['errors'], $key_offset);
					
				}

				if($quality !== false) {
					$datajson_qa = $this->campaign->datajson_qa($chunk, $schema, $quality, $component);	

					if(!empty($datajson_qa)) {
						$response['qa'] = array_merge_recursive($response['qa'], $datajson_qa);	
					}	

				}
								
			}


			// Sum QA counts 
			if(!empty($response['qa'])) {


				if(!empty($response['qa']['bureauCodes'])) {
					$response['qa']['bureauCodes'] = array_keys($response['qa']['bureauCodes']);
				}

				if(!empty($response['qa']['programCodes'])) {
					$response['qa']['programCodes'] = array_keys($response['qa']['programCodes']);
				}

				$sum_array_fields = array('accessURL_present', 'accessURL_total', 'accessLevel_public', 'accessLevel_restricted', 'accessLevel_nonpublic');

				foreach ($sum_array_fields as $array_field) {
					if(!empty($response['qa'][$array_field]) && is_array($response['qa'][$array_field])) {					
						$response['qa'][$array_field] = array_sum($response['qa'][$array_field]);					 
					}	
				}

				// Sum validation counts
				if (!empty($response['qa']['validation_counts']) && is_array($response['qa']['validation_counts'])) {
					foreach ($response['qa']['validation_counts'] as $validation_key => $validation_count) {

						if(is_array($response['qa']['validation_counts'][$validation_key])) {
							$response['qa']['validation_counts'][$validation_key] = array_sum($response['qa']['validation_counts'][$validation_key]);
						}

					}
				}

			}

			$valid_json = (isset($raw_valid_json)) ? $raw_valid_json : $valid_json;

			$response['valid'] = (empty($response['errors'])) ? true : false;
			$response['valid_json'] = $valid_json;


			if ($schema == 'federal-v1.1' OR $schema == 'non-federal-v1.1') {
				$response['total_records'] = count($datajson_decode->dataset);
			} else {
				$response['total_records'] = count($datajson_decode);
			}			


			if(!empty($datajson_header['download_content_length'])) {
				$response['download_content_length'] = $datajson_header['download_content_length'];
			}

			if(empty($response['errors'])) {
				$response['errors'] = false;
			}

			if ($return_source) {	
				$dataset_array = ($schema == 'federal-v1.1' OR $schema == 'non-federal-v1.1') ? true : false;
				$datajson_decode = filter_json($datajson_decode, $dataset_array);			
				$response['source'] = $datajson_decode;
			}
			
			return $response;

		} else {			
			$errors[] = "This does not appear to be valid JSON";
			$response = array(
							'valid_json' => false, 
							'valid' => false, 
							'fail' => $errors 
							);
			if(!empty($datajson_header['download_content_length'])) {
				$response['download_content_length'] = $datajson_header['download_content_length'];
			}

			return $response;
		}



	}

	public function jsonschema_validator($data, $schema = null, $chunked = null) {


		if($data) {

			$schema_variant = (!empty($schema)) ? "$schema/" : "";

			$schema_module = ($schema == 'federal-v1.1' && $chunked == true) ? 'dataset.json' : 'catalog.json'; 

			$path = './schema/' . $schema_variant . $schema_module;		

			//echo $path; exit;

			// Get the schema and data as objects
	        $retriever = new JsonSchema\Uri\UriRetriever;
	        $schema = $retriever->retrieve('file://' . realpath($path));


        	 //header('Content-type: application/json');
        	 //print $data;
        	 //exit;

    		$data = json_decode($data);

		    if(!empty($data)) {
                // If you use $ref or if you are unsure, resolve those references here
                // This modifies the $schema object
                $refResolver = new JsonSchema\RefResolver($retriever);
                $refResolver->resolve($schema, 'file://' . __DIR__ . '/../../schema/' . $schema_variant);

                // Validate
                $validator = new JsonSchema\Validator();
                $validator->check($data, $schema);

                if ($validator->isValid()) {
                    $results = array('valid' => true, 'errors' => false);
                } else {
                    $errors =  $validator->getErrors();

                    $results = array('valid' => false, 'errors' => $errors);
                }

          	   //header('Content-type: application/json');
          	   //print json_encode($results);
          	   //exit;

                return $results;
            } else {
                return false;
            }

    	}



	}



	public function process_validation_errors($errors, $offset = null) {

		$output = array();

		foreach ($errors as $error) {

            if ( !is_numeric($error['property']) AND  
                ($error['property'] === '') OR 
                 ($error['property'] === '@context') OR 
                 ($error['property'] === '@type') OR 
                 ($error['property'] === '@id') OR 
                 ($error['property'] === 'describedBy') OR 
                 ($error['property'] === 'conformsTo')) {
                $error['property'] = 'catalog.' . $error['property'];
            }

			if(is_numeric($error['property']) OR strpos($error['property'], '.') === false OR $error['property'] === 'catalog.') {
				$key = ($error['property'] === 'catalog.') ? 'catalog' : $error['property'];
				$field = 'ALL';
			} 
            else {

            	if (strpos($error['property'], 'dataset[') !== false) {
            		$dataset_key 	= substr($error['property'], 0, strpos($error['property'], '.'));
					$key 			= get_between($dataset_key, '[', ']');
					$full_field 	= substr($error['property'], strpos($error['property'], '.') + 1);
            	} else {
					$key = substr($error['property'], 0, strpos($error['property'], '.'));
					$full_field = substr($error['property'], strpos($error['property'], '.') + 1);            		
            	}


				if (strpos($full_field, '[')) {
					$field 		= substr($full_field, 0, strpos($full_field, '[') );
					$subfield 	= 'child-' . get_between($full_field, '[', ']');
				} else {
					$field = $full_field;
				}

			}

			if ($offset) {
				$key = $key + $offset;
			}

			if (isset($subfield)) {
				$output[$key][$field]['sub_fields'][$subfield][] = $error['message'];
			} else {
				$output[$key][$field]['errors'][] = $error['message'];
			}

			unset($subfield);



		}

		return $output;

	}





	public function datajson_qa($json, $schema = null, $quality = true, $component = null) {

		$programCode = array();
		$bureauCode = array();
		$collections_list = array();

		$this->validation_counts = $this->validation_count_model();

		$accessLevel_public			= 0;
		$accessLevel_restricted		= 0;
		$accessLevel_nonpublic		= 0;

		$accessURL_total			= 0;
		$API_total					= 0;
		$collections_total			= 0;
		$downloadURL_total			= 0;		
		$accessURL_present 			= 0;
		$downloadURL_present		= 0;
		$license_present			= 0;
		$redaction_present			= 0;	
		$redaction_no_explanation	= 0;	

		$json = json_decode($json);

		if ($schema == 'federal-v1.1' OR $schema == 'non-federal-v1.1') {
			$json = $json->dataset;
		}

		foreach ($json as $dataset) {

			if(!empty($dataset->accessLevel)) {


				if ($dataset->accessLevel == 'public') {
					$accessLevel_public++;
				} else if ($dataset->accessLevel == 'restricted public') {
					$accessLevel_restricted++;
				} else if ($dataset->accessLevel == 'non-public') {
					$accessLevel_nonpublic++;
				}

			} 

			if($schema == 'federal' OR $schema == 'federal-v1.1') {				


				if(!empty($dataset->programCode) && is_array($dataset->programCode)) {

					foreach ($dataset->programCode as $program) {
						$programCode[$program] = true;	
					}
					
				}

				if(!empty($dataset->bureauCode) && is_array($dataset->bureauCode)) {

					foreach ($dataset->bureauCode as $bureau) {
						$bureauCode[$bureau] = true;	
					}
				}				
			}
		
			if (!empty($dataset->isPartOf)) {
				$collections_list[$dataset->isPartOf] = true;
			}


			$has_accessURL = false;
			$has_downloadURL = false;

			if( ($schema == 'federal' OR $schema == 'non-federal')
				&& !empty($dataset->accessURL) 
				&& filter_var($dataset->accessURL, FILTER_VALIDATE_URL)) {

				$accessURL_total++;
				$has_accessURL = true;
				$dataset_format = (!empty($dataset->format)) ? $dataset->format : null;

				if($component === 'full-scan') $this->validation_check($dataset->identifier, $dataset->title, $dataset->accessURL, $dataset_format);

			}

			if( ($schema == 'federal' OR $schema == 'non-federal') 
				&& !empty($dataset->webService) 
				&& filter_var($dataset->webService, FILTER_VALIDATE_URL)) {

				$accessURL_total++;
				$API_total++;
				$has_accessURL = true;

				if($component === 'full-scan') $this->validation_check($dataset->identifier, $dataset->title, $dataset->webService);

			}			

			if(!empty($dataset->distribution) && is_array($dataset->distribution)) {
				
				foreach ($dataset->distribution as $distribution) {

					if ($schema == 'federal-v1.1' OR $schema == 'non-federal-v1.1') {
						$media_type = (!empty($distribution->mediaType)) ? $distribution->mediaType : null;
					} else {
						$media_type = (!empty($distribution->format)) ? $distribution->format : null;
					}

				   if(!empty($distribution->accessURL) && filter_var($distribution->accessURL, FILTER_VALIDATE_URL)) {
				   		
				   		if (($schema == 'federal-v1.1' OR $schema == 'non-federal-v1.1') 
				   			&& !empty($distribution->format) 
				   			&& strtolower($distribution->format) == 'api' ) {
				   			$API_total++;

				   		}

				   		if($component === 'full-scan') $this->validation_check($dataset->identifier, $dataset->title, $distribution->accessURL, $media_type);
						$accessURL_total++;
						$has_accessURL = true;					   		
				   }

				   if(!empty($distribution->downloadURL) && filter_var($distribution->downloadURL, FILTER_VALIDATE_URL)) {
				   		if($component === 'full-scan') $this->validation_check($dataset->identifier, $dataset->title, $distribution->downloadURL, $media_type);
						$accessURL_total++;
						$downloadURL_total++;
						$has_accessURL = true;	
						$has_downloadURL = true;	
				   }	
			
				}

			}

			// Track presence of redactions and rights info
			$json_text = json_encode($dataset);
			if (strpos($json_text, '[[REDACTED-EX') !== false) {
				$redaction_present++;

				if(empty($dataset->rights)) {
					$redaction_no_explanation++;	
				}
				
			}
			unset($json_text);

			// Track presence of license info
			if(!empty($dataset->license) && filter_var($dataset->license, FILTER_VALIDATE_URL)) {
				$license_present++;
			}

			if($has_accessURL) $accessURL_present++;
			if($has_downloadURL) $downloadURL_present++;


		}

		$qa = array();

		if($schema == 'federal' OR $schema == 'federal-v1.1') {

			$qa['programCodes'] 				= $programCode;
			$qa['bureauCodes'] 					= $bureauCode;

		}

		$qa['accessLevel_public']			= $accessLevel_public;
		$qa['accessLevel_restricted']		= $accessLevel_restricted;
		$qa['accessLevel_nonpublic']		= $accessLevel_nonpublic;

		$qa['accessURL_present'] 			= $accessURL_present;
		$qa['accessURL_total'] 				= $accessURL_total;
		$qa['API_total'] 					= $API_total;	
		$qa['collections_total']			= count($collections_list);	
		$qa['validation_counts']			= $this->validation_counts;
		$qa['license_present'] 				= $license_present;
		$qa['redaction_present'] 			= $redaction_present;
		$qa['redaction_no_explanation'] 	= $redaction_no_explanation;

		if ($schema == 'federal-v1.1' OR $schema == 'non-federal-v1.1') {
			$qa['downloadURL_present'] 	= $downloadURL_present;	
			$qa['downloadURL_total'] 	= $downloadURL_total;				
		}


		return $qa;

	}

	public function validation_check($id, $title, $url, $format = null) {

		$tmp_dir = $this->config->item('archive_dir');

		$header = curl_header($url, false, $tmp_dir);
		$good_link = false;
		$good_format = true;

		if(!empty($header['info']['http_code']) && preg_match('/[5]\d{2}\z/', $header['info']['http_code']) ){
			$this->validation_counts['http_5xx']++;
		}	

		if(!empty($header['info']['http_code']) && preg_match('/[4]\d{2}\z/', $header['info']['http_code']) ){
			$this->validation_counts['http_4xx']++;
		}	

		if(!empty($header['info']['http_code']) && preg_match('/[3]\d{2}\z/', $header['info']['http_code']) ){
			$this->validation_counts['http_3xx']++;
		}

		if(!empty($header['info']['http_code']) && preg_match('/[2]\d{2}\z/', $header['info']['http_code']) ){
			$this->validation_counts['http_2xx']++;
			$good_link = true;
		}	

		if(empty($header['info']['http_code'])){
			$this->validation_counts['http_0']++;
		}			

		if($good_link && !empty($format) && !empty($header['info']['content_type']) && stripos($header['info']['content_type'], $format) === false){
			$this->validation_counts['format_mismatch']++;
			$good_format = false;
		}		

		if($good_link && !empty($header['info']['content_type']) && stripos($header['info']['content_type'], 'application/pdf') !== false){
			$this->validation_counts['pdf']++;
		}

		if($good_link && !empty($format) && !empty($header['info']['content_type']) && stripos($header['info']['content_type'], 'text/html') !== false){
			$this->validation_counts['html']++;
		}	

		if($good_link === false OR $good_format === false) {
			$error_report = $this->error_report_model();
			$error_report['id'] = $id;
			$error_report['title'] = $title;
			$error_report['error_type'] = (!$good_link) ? 'broken_link' : 'format_mismatch' ;
			$error_report['url'] = $url;
			$error_report['http_status'] = $header['info']['http_code'];
			$error_report['format_served'] = $header['info']['content_type'];
			$error_report['format_datajson'] = $format;
			$error_report['crawl_date'] = date(DATE_W3C);

			// ######## Log this to a CSV ##########

			// if this is the first record to log, prepare the file
			if($this->validation_pointer == 0) {

				$download_dir = $this->config->item('archive_dir');
				$directory = "$download_dir/error_log";

				// create error log directory if needed
				if(!file_exists($directory)) {
					mkdir($directory);
				}

				$backup_path = $directory . '/' . $this->current_office_id . '_backup.csv';
				$filepath = $directory . '/' . $this->current_office_id . '.csv';

				// check to see if there's already a file
				if (file_exists($filepath)) {
					rename($filepath, $backup_path);
				}

				// Open new file
				$this->validation_log = fopen($filepath, 'w');

				if ($this->environment == 'terminal' OR $this->environment == 'cron') {
					echo 'Creating new file at ' . $filepath . PHP_EOL;
				}

				// Set file headings
				$headings = array_keys($error_report);
				fputcsv($this->validation_log, $headings);

				// Write first row of data to log
				fputcsv($this->validation_log, $error_report);

			} else {

				// open existing file pointer
				fputcsv($this->validation_log, $error_report);

			}

			$this->validation_pointer++;

		} else {
			return true;
		}

	}


	public function validation_count_model() {

		$count = array(
			'http_5xx' => 0,
			'http_4xx' => 0,
			'http_3xx' => 0,
			'http_2xx' => 0,
			'http_0' => 0,			
			'pdf' => 0,
			'html' => 0,
			'format_mismatch' => 0
			);

		return $count;

	}

	public function error_report_model() {

		$error = array(
			'error_type' => null,
			'id' => null,
			'title' => null,
			'url' => null,
			'http_status' => null,
			'format_served' => null,
			'format_datajson' => null,
			'crawl_date' => null
			);

		return $error;

	}	


	public function archive_file($filetype, $office_id, $url) {

		$download_dir = $this->config->item('archive_dir');

		if($filetype == 'datajson-lines') {
			$directory = "$download_dir/datajson-lines";
			$filepath = $directory . '/' . $office_id . '.raw';			
		} else {
			$crawl_date = date("Y-m-d");
			$directory = "$download_dir/$filetype/$crawl_date";
			$filepath = $directory . '/' . $office_id . '.json';			
		}


		if(!get_dir_file_info($directory)) {

			if ($this->environment == 'terminal' OR $this->environment == 'cron') {
				echo 'Creating directory ' . $directory . PHP_EOL;
			}

			mkdir($directory);
		}


		if ($this->environment == 'terminal' OR $this->environment == 'cron') {
			echo 'Attempting to download ' . $url . ' to ' . $filepath . PHP_EOL;
		}


		$opts = array(
		  'http'=>array(
		    'method'=>"GET",
		    'user_agent'=>"Data.gov data.json crawler"
		  )
		);

		$context = stream_context_create($opts);

		$copy = @fopen($url, 'rb', false, $context);
		$paste = @fopen($filepath, 'wb');


		// If we can't read from this file, skip
		if ($copy===false) {

			if ($this->environment == 'terminal' OR $this->environment == 'cron') {
				echo 'Could not read from ' . $url . PHP_EOL;
			}

			
		}

		// If we can't write to this file, skip
		if ($paste===false) {

			if ($this->environment == 'terminal' OR $this->environment == 'cron') {
				echo 'Could not open ' . $filepath . PHP_EOL;
			}

		}

		if($copy !== false && $paste !== false) {
			while (!feof($copy)) {
			    if (fwrite($paste, fread($copy, 1024)) === FALSE) {

			    		if ($this->environment == 'terminal' OR $this->environment == 'cron') {
							echo 'Download error: Cannot write to file ' . $filepath . PHP_EOL;
						}

			       }
			}			
		} else {

			return false;
		}

		fclose($copy);
		fclose($paste);

		if ($this->environment == 'terminal' OR $this->environment == 'cron') {
			echo 'Done' . PHP_EOL . PHP_EOL;
		}

		return $filepath;

	}



	public function update_status($update) {

		$existing_status 	= array();
		$tracker_update 	= false;

		// Determine current milestone
		$selected_milestone	= (!empty($update->milestone)) ? $update->milestone : null;
		$milestones 			= $this->milestones_model();	
		$milestone 				= $this->milestone_filter($selected_milestone, $milestones);

		$update->milestone 		= $milestone->selected_milestone;

		// Check if this is to update tracker fields
		if (!empty($update->status_id)) {
			$existing_status['status_id'] = $update->status_id;
			$this->db->where('status_id', $update->status_id);			

			if (empty($update->crawl_status)){
				$tracker_update = true;
			}
		}

		$this->db->select('status_id, crawl_status');
		$this->db->where('office_id', $update->office_id);
		$this->db->where('milestone', $update->milestone);
		$this->db->where("(crawl_status IS NULL OR crawl_status = 'final')");
		$this->db->limit(1);

		$query = $this->db->get('datagov_campaign');

		if ($query->num_rows() > 0) {
			$row = $query->row();
			$existing_status['status_id'] = $row->status_id;

			if (!empty($row->crawl_status)) {
				$existing_status['crawl_status'] =  $row->crawl_status;
				$update->crawl_status		 	 = 	$row->crawl_status;
			}

		} 

		// if this is to update tracker fields (crawl_status would be empty)
		if (!empty($existing_status)) {			
			
			// if this is to update tracker fields
			if (empty($update->crawl_status)) {
				
				$this->db->where('status_id', $existing_status['status_id']);
				$this->db->where('office_id', $update->office_id);
				$this->db->where('milestone', $update->milestone);

				$this->db->update('datagov_campaign', $update);				
			}

			// if this is just an old record, change the crawl_status
			if (empty($existing_status['crawl_status'])) {

				if(!empty($update->crawl_status) && $update->crawl_status == 'in_progress') {
					$old_status = 'current';
				}

				if(!empty($update->crawl_status) && $update->crawl_status == 'current') {
					$old_status = 'archive';
				}

				if(!empty($old_status)) {
					$reset = array( 'crawl_status' => $old_status );

					$this->db->where('status_id', $existing_status['status_id']);			
					$this->db->update('datagov_campaign', $reset); 
				}

			}

		} 


		// Check if this is an in-progress crawl to update or a mid-quarter tracker update
		if ($tracker_update OR (isset($update->status_id) && !empty($update->crawl_status))) {

			$this->db->where('status_id', $update->status_id);
			$this->db->update('datagov_campaign', $update);

			$status_id = $update->status_id;

		// Otherwise this is an insert
		} else {
			

			if(isset($update->status_id)) {
				unset($update->status_id);	
			}
			
			if ($this->environment == 'terminal') {
				echo 'Adding ' . $update->office_id . PHP_EOL . PHP_EOL;
			}

			$this->db->insert('datagov_campaign', $update);
			$status_id = $this->db->insert_id();
		}

		// reset previous "current" crawl
		if (!empty($update->crawl_status) && $update->crawl_status == 'current') {
			
			$this->db->select('status_id');
			$this->db->where('office_id', $update->office_id);
			$this->db->where('milestone', $update->milestone);
			$this->db->where('crawl_status', 'current');
			$this->db->where("(crawl_end IS NULL OR crawl_end < '$update->crawl_end')");
			$this->db->limit(1);

			$query = $this->db->get('datagov_campaign');

			if ($query->num_rows() > 0) {

				$row = $query->row();
				$reset = array( 'crawl_status' => 'archive' );

				$this->db->where('status_id', $row->status_id);			
				$this->db->update('datagov_campaign', $reset); 

			}

		}

		return $status_id;

	}

	public function finalize_milestone($milestone) {
		
		$this->db->where('milestone', $milestone);
		$this->db->where('crawl_status', 'current');
		
		$finalize = array( 'crawl_status' => 'final' );
		$this->db->update('datagov_campaign', $finalize); 

		return;
	}


	public function update_note($update) {

		$this->db->select('note');
		$this->db->where('office_id', $update->office_id);
		$this->db->where('milestone', $update->milestone);
		$this->db->where('field_name', $update->field_name);
		
		$query = $this->db->get('notes');

		if ($query->num_rows() > 0) {
			// update

			if ($this->environment == 'terminal') {
				echo 'Updating ' . $update->office_id . PHP_EOL . PHP_EOL;
			}

			//$current_data = $query->row_array();
			//$update = array_mash($update, $current_data);

			$this->db->where('office_id', $update->office_id);
			$this->db->where('milestone', $update->milestone);
			$this->db->where('field_name', $update->field_name);

			$this->db->update('notes', $update);



		} else {
			// insert

			if ($this->environment == 'terminal') {
				echo 'Adding ' . $update->office_id . PHP_EOL . PHP_EOL;
			}

			$this->db->insert('notes', $update);

		}

	}

	public function get_notes($office_id, $milestone) {

		$query = $this->db->get_where('notes', array('office_id' => $office_id, 'milestone' => $milestone));

		return $query;

	}





	public function datajson_schema($version = '') {

		$prefix = 'fitara';

		if (!empty($version)) {			
			if (substr($version, 0, strlen($prefix)) == $prefix) {
				$version_path = $prefix . '/' . substr($version, strlen($prefix)+1) . '.json';
			} else {
				$version_path = $version . '/catalog.json';
			}
		} else {
			$version_path = 'catalog.json';
		}

		$path = './schema/' . $version_path; 

		// Get the schema and data as objects
        $retriever = new JsonSchema\Uri\UriRetriever;
        $schema = $retriever->retrieve('file://' . realpath($path));	

        $refResolver = new JsonSchema\RefResolver($retriever);
        $refResolver->resolve($schema, 'file://' . __DIR__ . '/../../schema/' . $version_path);

		return $schema;


	}


	public function schema_to_model($schema) {

		$model = new stdClass();

		
		foreach ($schema as $key => $value) {


			if(!empty($value->type) && $value->type == 'object') {
	
				// This is just hard coded to prevent recursion, but should be replaced with proper recursion detection
				if($key == 'subOrganizationOf') {
					$model->$key = null;
				} else {
					$model->$key = $this->schema_to_model($value->properties);	
				}

			} else if(!empty($value->items) && $value->type == 'array') {

				 $model->$key = array();
	
				if (!empty($value->items->properties)) {
					$model->$key = array($this->schema_to_model($value->items->properties));
				}


			} else if(!empty($value->anyOf)) {

				foreach ($value->anyOf as $anyOptions) {

					if (!empty($anyOptions->type) && $anyOptions->type == 'array') {

						$model->$key = array();

						if (!empty($anyOptions->items) && !empty($anyOptions->items->type) && $anyOptions->items->type == 'object') {							
							$model->$key = array($this->schema_to_model($anyOptions->items->properties));

						}
					}
				}

				if(!isset($model->$key)) {
					$model->$key = null;
				}

			} else {
				
				if($key == '@type' && !empty($value->enum)) {
					$model->$key = $value->enum[0];
				} else {
					$model->$key = null;
				}

			}

		}

		return $model;

	}

	public function get_datagov_json($orgs, $geospatial = false, $rows = 100, $offset = 0, $raw = false, $allow_harvest_sources = 'true') {

		$allow_harvest_sources = (empty($allow_harvest_sources)) ? 'true' : $allow_harvest_sources;

		if ($geospatial == 'both') {
		    $filter = "%20";
		} else if ($geospatial == 'true') {
		    $filter = 'metadata_type:geospatial%20AND%20';
		} else {
			$filter = '-metadata_type:geospatial%20AND%20';
		}

		if ($allow_harvest_sources !== 'true') {
			$filter .= "AND%20-harvest_source_id:[''%20TO%20*]";
		}

		if(strpos($orgs, 'http://') !== false) {

			$uri = $orgs;
			$from_export = true;

		} else {

			$orgs = rawurlencode($orgs);
			$query = $filter . "-type:harvest%20AND%20organization:(" . $orgs . ")&rows=" . $rows . '&start=' . $offset;
			$uri = 'http://catalog.data.gov/api/3/action/package_search?q=' . $query;
			$from_export = false;
		}

		$datagov_json = curl_from_json($uri, false);

		if($from_export) {

			$object_shim = new stdClass();
			$object_shim->result 			= new stdClass();
			$object_shim->result->count 	= count($datagov_json);
			$object_shim->result->results 	= $datagov_json;

			$datagov_json = $object_shim;
		}

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
		            $datajson_model->theme = $extra->value;
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
		$datajson_model->mbox                               = (!empty($datajson_model->mbox)) ? $datajson_model->mbox : $raw_data->maintainer_email;
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


public function datajson_schema_crosswalk($raw_data, $datajson_model) {

		$distributions = array();

		// Add any accessURL and format to a distribution
		if(!empty($raw_data->accessURL)) {
			$distribution = clone $datajson_model->distribution[0];

			$distribution->downloadURL 		= $raw_data->accessURL;
			$distribution->mediaType		= (!empty($raw_data->format)) ? $raw_data->format : null;

			$distribution = $this->unset_nulls($distribution);

			$distributions[] = $distribution;
		}

		// Convert distributions
		if(!empty($raw_data->distribution) && is_array($raw_data->distribution)) {

			foreach($raw_data->distribution as $resource) {
				$distribution = clone $datajson_model->distribution[0];

				$distribution->downloadURL 		= $resource->accessURL;
				$distribution->mediaType		= $resource->format;

				$distribution = $this->unset_nulls($distribution);

				$distributions[] = $distribution;
			}		

		}

		// Convert webService to a distribution
		if(!empty($raw_data->webService)) {
			$distribution = clone $datajson_model->distribution[0];

			$distribution->accessURL 		= $raw_data->webService;
			$distribution->format			= 'API';	

			$distribution = $this->unset_nulls($distribution);

			$distributions[] = $distribution;		

		}

		// Convert license to a URL
		if(!empty($raw_data->license)) {

			if(!filter_var($raw_data->license, FILTER_VALIDATE_URL)) {
				$license = urlencode($raw_data->license); 
				$license = 'https://project-open-data.cio.gov/unknown-license/#v1-legacy/' . $license;				
			} else {
				$license = $raw_data->license;
			}

		}

		// Convert accrualPeriodicity to a date
		if(!empty($raw_data->accrualPeriodicity)) {

			switch ($raw_data->accrualPeriodicity) {
			    case "Decennial":
			        $accrualPeriodicity = 'R/P10Y';
			        break;
			    case "Quadrennial":
			        $accrualPeriodicity = 'R/P4Y';
			        break;			        
			    case "Annual":
			        $accrualPeriodicity = 'R/P1Y';
			        break;
			    case "Bimonthly":
			        $accrualPeriodicity = 'R/P2M';
			        break;
			    case "Semiweekly":
			        $accrualPeriodicity = 'R/P3.5D';
			        break;
			    case "Daily":
			        $accrualPeriodicity = 'R/P1D';
			        break;
			    case "Biweekly":
			        $accrualPeriodicity = 'R/P2W';
			        break;
			    case "Semiannual":
			        $accrualPeriodicity = 'R/P6M';
			        break;
			    case "Biennial":
			        $accrualPeriodicity = 'R/P2Y';
			        break;
			    case "Triennial":
			        $accrualPeriodicity = 'R/P3Y';
			        break;
			    case "Three times a week":
			        $accrualPeriodicity = 'R/P0.33W';
			        break;
			    case "Three times a month":
			        $accrualPeriodicity = 'R/P0.33M';
			        break;	
			    case "Continuously updated":
			        $accrualPeriodicity = 'R/PT1S';
			        break;
			    case "Monthly":
			        $accrualPeriodicity = 'R/P1M';
			        break;			
			    case "Quarterly":
			        $accrualPeriodicity = 'R/P3M';
			        break;
			    case "Semimonthly":
			        $accrualPeriodicity = 'R/P0.5M';
			        break;
			    case "Three times a year":
			        $accrualPeriodicity = 'R/P4M';
			        break;
			    case "Weekly":
			        $accrualPeriodicity = 'R/P1W';
			        break;     
			    case "Completely irregular":
			        $accrualPeriodicity = 'irregular';
			        break; 		
			    default: $accrualPeriodicity = $raw_data->accrualPeriodicity;
			}			

		} else {
			$accrualPeriodicity = null;
		}

		// reset other objects
		$datajson_model->contactPoint 	= clone $datajson_model->contactPoint;
		$datajson_model->publisher 		= clone $datajson_model->publisher;

		// Set email address, but check for redactions before prepending mailto:
		if (!empty($raw_data->mbox)) {
			if(strpos($raw_data->mbox, '[[REDACTED-EX') === 0) {
				$hasEmail = $raw_data->mbox;
			} else {
				$hasEmail = 'mailto:' . $raw_data->mbox;
			}
		} else {
			$hasEmail = null;
		}

		$datajson_model->contactPoint->hasEmail 			= $hasEmail;
		$datajson_model->contactPoint->fn                   = (!empty($raw_data->contactPoint)) ? $raw_data->contactPoint : null;
	    
		$datajson_model->accessLevel                        = (!empty($raw_data->accessLevel)) ? $raw_data->accessLevel : null;
		$datajson_model->rights 			                = (!empty($raw_data->accessLevelComment)) ? $raw_data->accessLevelComment : null;
		$datajson_model->accrualPeriodicity                 = $accrualPeriodicity;
		$datajson_model->bureauCode                         = (!empty($raw_data->bureauCode)) ? $raw_data->bureauCode : null;	
		$datajson_model->describedBy                     	= (!empty($raw_data->dataDictionary)) ? $raw_data->dataDictionary : null;
		$datajson_model->dataQuality                        = (!empty($raw_data->dataQuality)) ? $raw_data->dataQuality : null;
		$datajson_model->description                        = (!empty($raw_data->description)) ? $raw_data->description : null;
		
		$datajson_model->distribution                       = (!empty($distributions)) ? $distributions : null;
		
		$datajson_model->identifier                         = (!empty($raw_data->identifier)) ? $raw_data->identifier : null;
		$datajson_model->issued                             = (!empty($raw_data->issued)) ? $raw_data->issued : null;
		$datajson_model->keyword                            = (!empty($raw_data->keyword)) ? $raw_data->keyword : null;
		$datajson_model->landingPage                        = (!empty($raw_data->landingPage)) ? $raw_data->landingPage : null;
		$datajson_model->language                           = (!empty($raw_data->language)) ? $raw_data->language : null;	
		
		$datajson_model->license                            = (!empty($license)) ? $license : null;

		$datajson_model->modified                           = (!empty($raw_data->modified)) ? $raw_data->modified : null;
		$datajson_model->primaryITInvestmentUII             = (!empty($raw_data->PrimaryITInvestmentUII)) ? $raw_data->PrimaryITInvestmentUII : null;
		$datajson_model->programCode                        = (!empty($raw_data->programCode)) ? $raw_data->programCode : null;
		$datajson_model->publisher->name                    = (!empty($raw_data->publisher)) ? $raw_data->publisher : null;
		$datajson_model->references                         = (!empty($raw_data->references)) ? $raw_data->references : null;
		$datajson_model->spatial                            = (!empty($raw_data->spatial)) ? $raw_data->spatial : null;
		$datajson_model->systemOfRecords                    = (!empty($raw_data->systemOfRecords)) ? $raw_data->systemOfRecords : null;
		$datajson_model->temporal                           = (!empty($raw_data->temporal)) ? $raw_data->temporal : null;
		$datajson_model->theme                              = (!empty($raw_data->theme)) ? $raw_data->theme : null;
		$datajson_model->title                              = (!empty($raw_data->title)) ? $raw_data->title : null;	

		$datajson_model = $this->unset_nulls($datajson_model);

		return $datajson_model;
	}

	function unset_nulls($object) {

		foreach($object as $key => $property) {
			
			if (is_null($property)) {
				unset($object->$key);
			}

			if(is_object($property)) {
				$object->$key = $this->unset_nulls($property);
			}

			if(is_array($property)) {

				if(empty($property)) {
					unset($object->$key);
				} else {
					foreach ($property as $row => $value) {
						if(is_object($value) OR is_array($value)) {
							$property[$row] = $this->unset_nulls($value);	
						}						
					}
				}

			}			
			
		}

		return $object;

	}



	function schema_v1_permalinks() {

		$permalink = array();

        $permalink['@context']						        = 'context';
        $permalink['@id']							        = 'id';
        $permalink['@type']							        = 'type';
        $permalink['conformsTo']					        = 'conformsTo';
        $permalink['describedBy']					        = 'describedBy';
        $permalink['dataset']						        = 'dataset';
        $permalink['dataset.@type']							= 'dataset-type';
        $permalink['dataset.accessLevel']					= 'accessLevel';
        $permalink['dataset.accrualPeriodicity']			= 'accrualPeriodicity';
        $permalink['dataset.bureauCode']					= 'bureauCode';
        $permalink['dataset.conformsTo']					= 'dataset-conformsTo';
        $permalink['dataset.contactPoint']					= 'contactPoint';
        $permalink['dataset.contactPoint.@type']			= 'dataset-contactPoint-type';
        $permalink['dataset.contactPoint.fn']				= 'contactPoint-fn';
        $permalink['dataset.contactPoint.hasEmail']			= 'contactPoint-hasEmail';
        $permalink['dataset.dataQuality']					= 'dataQuality';
        $permalink['dataset.describedBy']					= 'dataset-describedBy';
        $permalink['dataset.describedByType']				= 'dataset-describedByType';
        $permalink['dataset.description']					= 'description';
        $permalink['dataset.distribution']					= 'distribution';
        $permalink['dataset.distribution.@type']			= 'distribution-type';
        $permalink['dataset.distribution.accessURL']		= 'distribution-accessURL';
        $permalink['dataset.distribution.conformsTo']		= 'distribution-conformsTo';
        $permalink['dataset.distribution.downloadURL']		= 'distribution-downloadURL';
        $permalink['dataset.distribution.describedBy']		= 'distribution-describedBy';
        $permalink['dataset.distribution.describedByType']	= 'distribution-describedByType';
        $permalink['dataset.distribution.description']		= 'distribution-description';
        $permalink['dataset.distribution.format']			= 'distribution-format';
        $permalink['dataset.distribution.mediaType']		= 'distribution-mediaType';
        $permalink['dataset.distribution.title']			= 'distribution-title';
        $permalink['dataset.identifier']					= 'identifier';
        $permalink['dataset.isPartOf']						= 'isPartOf';
        $permalink['dataset.issued']						= 'issued';
        $permalink['dataset.keyword']						= 'keyword';
        $permalink['dataset.landingPage']					= 'landingPage';
        $permalink['dataset.language']						= 'language';
        $permalink['dataset.license']						= 'license';
        $permalink['dataset.modified']						= 'modified';
        $permalink['dataset.primaryITInvestmentUII']		= 'primaryITInvestmentUII';
        $permalink['dataset.programCode']					= 'programCode';
        $permalink['dataset.publisher']						= 'publisher';
        $permalink['dataset.publisher.@type']				= 'publisher-type';
        $permalink['dataset.publisher.name']				= 'publisher-name';
        $permalink['dataset.publisher.subOrganizationOf']	= 'publisher-subOrganizationOf';
        $permalink['dataset.rights']						= 'rights';
        $permalink['dataset.spatial']						= 'spatial';
        $permalink['dataset.systemOfRecords']				= 'systemOfRecords';
        $permalink['dataset.temporal']						= 'temporal';
        $permalink['dataset.theme']							= 'theme';
        $permalink['dataset.title']							= 'title';

        return $permalink;

	}


}

?>
