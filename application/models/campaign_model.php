<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class campaign_model extends CI_Model {

    var $jurisdictions = array();
    var $protected_field = null;
    var $validation_counts = null;
    var $current_office_id = null;
    var $validation_pointer = null;
    var $validation_log = null;
    var $schema = null;

    public function __construct() {
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

    public function ciogov_office($office_id, $milestone = null, $crawl_status = null, $status_id = null) {

        $this->db->select('*');
        $this->db->where('office_id', $office_id);

        // If we got a status_id, query specifically for that
        if (!empty($status_id)) {
            $this->db->where('status_id', $status_id);
        } else {
            // otherwise see if we need to filter by crawl status
            if (!empty($crawl_status)) {
                $this->db->where('crawl_status', $crawl_status);
            } else {
                $this->db->where("(crawl_status IS NULL OR crawl_status='current' OR crawl_status='final')");
            }
        }


        if ($milestone)
            $this->db->where('milestone', $milestone);
        $this->db->limit(1);

        $query = $this->db->get('ciogov_campaign');

        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
          // Create a template record if not in db
          $campaign = new stdClass();
          $campaign->office_id = $office_id;
          $campaign->milestone = $milestone;
          $campaign->status_id = 0;
          $campaign->crawl_start = "";
          $campaign->crawl_end = "";
          $campaign->crawl_status = "current";
          $campaign->bureaudirectory_status = "";
          $campaign->governanceboard_status = "";
          $campaign->recommendation_status = "";
          $campaign->policyarchive_status = "";
          $campaign->tracker_fields = "";
          return $campaign;
        }
    }

    public function ciogov_office_crawls($office_id, $milestone = null, $status_id, $direction, $limit) {

        $status_id = intval($status_id);

        $this->db->select('status_id, crawl_start, crawl_end');
        $this->db->where('office_id', $office_id);
        $this->db->where('milestone', $milestone);
        $this->db->where('status_id ' . $direction, $status_id);

        if ($direction == '<')
            $order_dir = 'DESC';
        if ($direction == '>')
            $order_dir = 'ASC';

        $this->db->order_by('status_id', $order_dir);

        $query = $this->db->get('ciogov_campaign', $limit);

        return $query->result_array();
    }

    public function ciogov_model() {

        $model = new stdClass();

        $model->status_id = null;
        $model->office_id = null;
        $model->milestone = null;
        $model->crawl_start = null;
        $model->crawl_end = null;
        $model->crawl_status = null;
        $model->contact_name = null;
        $model->contact_email = null;
        $model->bureaudirectory_status = null;
        $model->governanceboard_status = null;
        $model->recommendation_status = null;
        $model->policyarchive_status = null;
        $model->tracker_fields = '';
        $model->tracker_status = null;

        return $model;
    }

    public function tracker_model($milestone = null) {

        $milestones = $this->campaign->milestones_model();
        $milestone_index = intval(array_search($milestone, array_keys($milestones))) + 1;

        $model = new stdClass();
        $field = new stdClass();

        $field->type = null; // field types include: string, textarea, integer, url, select (yes/no), traffic, status (submission status), placeholder
        $field->value = null;
        $field->label = null;
        $field->placeholder = null;
        $field->due_date = null;

        // Common Baseline

        $model->cb_self_assessment = clone $field;
        $model->cb_self_assessment->dashboard = true;
        $model->cb_self_assessment->label = "Self-Assessment";
        $model->cb_self_assessment->type = $milestone_index === 3 ? "approval" : "select";
        $model->cb_self_assessment->due_date = ($milestone_index < 3) ? '2015-08-15' : "";

        $model->cb_self_assessment_url = clone $field;
        $model->cb_self_assessment_url->dashboard = false;
        $model->cb_self_assessment_url->label = "Self-Assessment Plan URL (Optional)";
        $model->cb_self_assessment_url->type = "url";
        $model->cb_self_assessment_url->indent = 1;
        $model->cb_self_assessment_url->active = "3";//active for milestone 3 and forward


        $model->cb_implementation_plan = clone $field;
        $model->cb_implementation_plan->dashboard = true;
        $model->cb_implementation_plan->label = "Implementation Plan";
        $model->cb_implementation_plan->type = $milestone_index === 3 ? "approval" : "select";
        $model->cb_implementation_plan->due_date = ($milestone_index < 3) ? '2015-08-15' : "";


        $model->cb_implementation_plan_url = clone $field;
        $model->cb_implementation_plan_url->dashboard = false;
        $model->cb_implementation_plan_url->label = "Implementation Plan URL";
        $model->cb_implementation_plan_url->type = "url";
        $model->cb_implementation_plan_url->indent = 1;
        $model->cb_implementation_plan_url->active = "3";//active for milestone 3 and forward

        $model->cb_date_of_omb_approval_of_implementation_plan = clone $field;
        $model->cb_date_of_omb_approval_of_implementation_plan->dashboard = false;
        $model->cb_date_of_omb_approval_of_implementation_plan->label = "Date of OMB Approval of Implementation Plan ";
        $model->cb_date_of_omb_approval_of_implementation_plan->type = "date";
        $model->cb_date_of_omb_approval_of_implementation_plan->indent = 1;
        $model->cb_date_of_omb_approval_of_implementation_plan->active = "3";//active for milestone 3 and forward

        $model->cb_cio_assignment_plan = clone $field;
        $model->cb_cio_assignment_plan->dashboard = true;
        $model->cb_cio_assignment_plan->label = "CIO Assignment Plan (If Applicable)";
        $model->cb_cio_assignment_plan->type = $milestone_index === 3 ? "approval" : "select";
        $model->cb_cio_assignment_plan->due_date = ($milestone_index < 3) ? '2015-08-15' : "";

        $model->cb_cio_assign_plan_url = clone $field;
        $model->cb_cio_assign_plan_url->dashboard = false;
        $model->cb_cio_assign_plan_url->label = "CIO Assignment Plan URL (If Applicable)";
        $model->cb_cio_assign_plan_url->type = "url";
        $model->cb_cio_assign_plan_url->indent = 1;
        $model->cb_cio_assign_plan_url->active = "3";//active for milestone 3 and forward

        // Published Artifacts

        $model->pa_bureau_it_leadership = clone $field;
        $model->pa_bureau_it_leadership->dashboard = true;
        $model->pa_bureau_it_leadership->label = "Bureau IT Leadership";
        $model->pa_bureau_it_leadership->description = "Bureau IT Leadership file exists and conforms to schema?";
        $model->pa_bureau_it_leadership->type = "select";
        $model->pa_bureau_it_leadership->due_date = ($milestone_index <= 3) ? '2015-08-15' : "";

        $model->pa_bureau_it_leadership_table = clone $field;
        $model->pa_bureau_it_leadership_table->indent = 1;
        $model->pa_bureau_it_leadership_table->label = "Bureau IT Leadership Directory";
        $model->pa_bureau_it_leadership_table->type = "table";

        $model->pa_cio_governance_board = clone $field;
        $model->pa_cio_governance_board->dashboard = true;
        $model->pa_cio_governance_board->label = "CIO Governance Board List";
        $model->pa_cio_governance_board->description = "CIO Governance Board file exists and conforms to schema?";
        $model->pa_cio_governance_board->type = "select";
        $model->pa_cio_governance_board->due_date = ($milestone_index <= 3) ? '2015-08-31' : "";

        $model->pa_cio_governance_board_table = clone $field;
        $model->pa_cio_governance_board_table->indent = 1;
        $model->pa_cio_governance_board_table->label = "CIO Governance Board Membership List";
        $model->pa_cio_governance_board_table->type = "table";

        $model->pa_it_policy_archive = clone $field;
        $model->pa_it_policy_archive->dashboard = true;
        $model->pa_it_policy_archive->label = "IT Policy Archive";
        $model->pa_it_policy_archive->description = "IT Policy Archive file exists with expected file extension?";
        $model->pa_it_policy_archive->type = "select";
        $model->pa_it_policy_archive->due_date = ($milestone_index <= 3) ? '2015-08-31' : "";

        // GAO Recommendations
        /*
        $model->gr_open_gao_recommendations = clone $field;
        $model->gr_open_gao_recommendations->dashboard = true;
        $model->gr_open_gao_recommendations->label = "GAO Recommendations";
        $model->gr_open_gao_recommendations->description = "# Open GAO Recommendations";
        $model->gr_open_gao_recommendations->type = "integer";
        */

        if ($milestone_index >= 3) {

            /*
            $model->ci_listserv_members = clone $field;
            $model->ci_listserv_members->dashboard = true;
            $model->ci_listserv_members->label = "# of Listserv Members";
            $model->ci_listserv_members->type = "integer";
             */

        }

        return $model;
    }

    /**
     * Returns array of section breakdown for the dashboard office table headers.
     *
     * @param <date> $milestone
     * @return <array>
     */
     public function tracker_sections_model($milestone = null) {

       $milestones = $this->campaign->milestones_model();
       $milestone_index = intval(array_search($milestone, array_keys($milestones))) + 1;
       $cb = $milestone_index === 3 ?  'Common Baseline: OMB Approval' : 'Common Baseline Submission Status';

       $section_breakdown = array(
            'cb' => $cb,
            'pa' => 'Published Artifacts Submission Status',
            //'gr' => 'GAO Recommendations'
        );

        if ($milestone_index >= 3) {
           // $section_breakdown['ci'] = 'Community Involvement';
        }

        return $section_breakdown;
    }

    /**
     * Returns an array of subsections for the office table subheader.
     * Returns the tracker_model objects that have a dashboard setting
     * of true.
     *
     * @param <date> $milestone
     * @return <array>
     */
    public function tracker_subsections_model($milestone = null) {

      $section_breakdown = $this->tracker_sections_model($milestone);

      $sections = array_keys($section_breakdown);

      $tracker = $this->tracker_model($milestone);

      foreach ($tracker as $key => $item) {
        $section = substr($key, 0, 2);
        if (isset($item->dashboard) && $item->dashboard === true) {
          if(!property_exists($item, "due_date") || !isset($item->due_date) || !strtotime($item->due_date)) {
            $item->due_date = "";
          }
          if(strtotime($item->due_date)) {
             $date = new DateTime($item->due_date);
             $item->due_date = $date->format("m/d/Y");
          }
          $subsection_breakdown[$section][] = $item;
        }
      }

      return $subsection_breakdown;
    }

    public function tracker_review_model() {

        $model = new stdClass();

        $model->status = null;
        $model->reviewer_name = null;
        $model->reviewer_email = null;
        $model->last_updated = null;
        $model->last_editor = null;

        return $model;
    }

    public function milestones_model() {

        $milestones = array(
            "2015-08-17" => "Milestone 1",
            "2015-08-31" => "Milestone 2",
            "2015-09-30" => "Milestone 3",
            "2015-11-30" => "Milestone 4",
            "2016-02-29" => "Milestone 5",
            "2016-04-30" => "Milestone 6"
        );

        return $milestones;
    }

    public function milestone_filter($selected_milestone, $milestones) {

        // Sets the first milestone in the future as the current and last available milestone
        foreach ($milestones as $milestone_date => $milestone) {
            if (strtotime($milestone_date) >= strtotime(date('Y-m-d'))) {

                if (empty($current_milestone)) {
                    $current_milestone = $milestone_date;
                } else if (!config_item('show_all_milestones')) {
                    unset($milestones[$milestone_date]);
                }
            }
        }

        // if we didn't explicitly select a milestone, use the current one
        if (empty($selected_milestone) || !array_key_exists($selected_milestone, $milestones)) {
            $selected_milestone = $current_milestone;
            $specified = "false";
        } else {
            $specified = "true";
        }

        reset($milestones);

        // determine previous milestone
        while (key($milestones) !== $current_milestone)
            next($milestones);
        prev($milestones);
        $previous_milestone = key($milestones);

        reset($milestones);

        $response = new stdClass();

        $response->selected_milestone = $selected_milestone;
        $response->current = $current_milestone;
        $response->previous = $previous_milestone;
        $response->specified = $specified;

        $response->milestones = $milestones;

        return $response;
    }

    public function note_model() {

        $model = new stdClass();

        $model->date = null;
        $model->author = null;
        $model->note = null;
        $model->note_html = null;

        $note = new stdClass();

        $note->current = $model;

        return $note;
    }

    public function json_crawl() {

        $model = new stdClass();

        $model->id = null;
        $model->office_id = null;
        $model->url = null;
        $model->crawl_cycle = null;
        $model->crawl_status = null;
        $model->start = null;
        $model->end = null;

        return $model;
    }

    public function metadata_record() {

        $model = new stdClass();

        $model->id = null;
        $model->office_id = null;
        $model->url = null;
        $model->identifier = null;
        $model->json_body = null;
        $model->schema_valid = null;
        $model->validation_errors = null;
        $model->last_modified_header = null;
        $model->last_modified_body = null;
        $model->last_crawled = null;
        $model->crawl_cycle = null;

        return $model;
    }

    public function metadata_resource() {

        $model = new stdClass();

        $model->id = null;
        $model->metadata_record_id = null;
        $model->metadata_record_identifier = null;
        $model->url = null;

        return $model;
    }

    public function uri_header($url, $redirect_count = 0) {

        $tmp_dir = $tmp_dir = $this->config->item('archive_dir');

        $status = curl_header($url, true, $tmp_dir);
        $status = $status['info']; //content_type and http_code

        if ($status['redirect_count'] == 0 && !(empty($redirect_count)))
            $status['redirect_count'] = 1;
        $status['redirect_count'] = $status['redirect_count'] + $redirect_count;

        if (!empty($status['redirect_url'])) {
            if ($status['redirect_count'] == 0 && $redirect_count == 0)
                $status['redirect_count'] = 1;

            if ($status['redirect_count'] > 5)
                return $status;
            $status = $this->uri_header($status['redirect_url'], $status['redirect_count']);
        }

        if (!empty($status)) {
            return $status;
        } else {
            return false;
        }
    }

    public function validate_bureaudirectory($url = null, $json = null, $headers = null, $schema = null, $return_source = false, $component = null) {
        return $this->validate_json($url, $json, $headers, 'bureaudirectory', $return_source, $component);
    }

    public function validate_governanceboard($url = null, $json = null, $headers = null, $schema = null, $return_source = false, $component = null) {
        return $this->validate_json($url, $json, $headers, 'governanceboard', $return_source, $component);
    }

    public function validate_json($url = null, $json = null, $headers = null, $schema = null, $return_source = false, $component = null) {


        if ($url) {

            $json_header = ($headers) ? $headers : $this->campaign->uri_header($url);

            $errors = array();

            // Max file size
            $max_remote_size = $this->config->item('max_remote_size');


            // Only download the data.json if we need to
            if (empty($json_header['download_content_length']) ||
                    $json_header['download_content_length'] < 0 ||
                    (!empty($json_header['download_content_length']) &&
                    $json_header['download_content_length'] > 0 &&
                    $json_header['download_content_length'] < $max_remote_size)) {

                if (config_item('proxy_host') && config_item('proxy_port')) {
                    $ch = curl_init ($url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    $proxy = config_item('proxy_host') .":" .config_item('proxy_port');
                    curl_setopt($ch, CURLOPT_PROXY, $proxy);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; CIO.gov Digital Strategy JSON crawler)');
                    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
                    $json=curl_exec($ch);
                } else {
                    $ch = curl_init ($url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; CIO.gov Digital Strategy JSON crawler)');
                    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
                    $json=curl_exec($ch);
                }

                if ($json == false) {
                    $json = curl_from_json($url, false, false);
                    if (!$json) {
                        $errors[] = "File not found or couldn't be downloaded";
                    }
                }
            }


            if (!empty($json) && (empty($json_header['download_content_length']) || $json_header['download_content_length'] < 0)) {
                $json_header['download_content_length'] = strlen($json);
            }

            // See if it exceeds max size
            if ($json_header['download_content_length'] > $max_remote_size) {

                //$filesize = human_filesize($json_header['download_content_length']);
                //$errors[] = "The data.json file is " . $filesize . " which is currently too large to parse with this tool. Sorry.";
                // Increase the timeout limit
                @set_time_limit(6000);

                $this->load->helper('file');

                if ($rawfile = $this->archive_file('json-lines', $this->current_office_id, $json_url)) {

                    $outfile = $rawfile . '.lines.json';

                    $stream = fopen($rawfile, 'r');
                    $out_stream = fopen($outfile, 'w+');

                    //$listener = new DataJsonParser();
                    $listener = new JsonStreamingParser_Parser_Listener();
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
                    $json_lines_count = $listener->_array_count;

                    // Delete temporary raw source file
                    unlink($rawfile);

                    $out_stream = fopen($outfile, 'r+');

                    $chunk_cycle = 0;
                    $chunk_size = 200;
                    $chunk_count = intval(ceil($json_lines_count / $chunk_size));
                    $buffer = '';

                    $response = array();
                    $response['errors'] = array();

                    if ($quality !== false) {
                        $response['qa'] = array();
                    }

                    echo "Analyzing $json_lines_count lines in $chunk_count chunks of $chunk_size lines each" . PHP_EOL;

                    while ($chunk_cycle < $chunk_count) {

                        $buffer = '';
                        $counter = 0;

                        if ($chunk_cycle > 0) {
                            $key_offset = $chunk_size * $chunk_cycle;
                        } else {
                            $key_offset = 0;
                        }

                        $next_offset = $key_offset + $chunk_size;
                        //echo "Analyzing chunk $chunk_cycle of $chunk_count ($key_offset to $next_offset of $json_lines_count)" . PHP_EOL;


                        if ($chunk_cycle == 0) {
                            $json_header = fgets($out_stream);
                        }

                        while (($buffer .= fgets($out_stream)) && $counter < $chunk_size) {
                            $counter++;
                        }

                        $buffer = $json_header . $buffer;
                        $buffer = substr($buffer, 0, strlen($buffer) - 2) . ']}';

                        $validator = $this->campaign->jsonschema_validator($buffer, 'federal-v1.1');

                        if (!empty($validator['errors'])) {

                            $response['errors'] = array_merge($response['errors'], $this->process_validation_errors($validator['errors'], $key_offset));
                        }

                        $chunk_cycle++;
                    }

                    // Delete json lines file
                    unlink($outfile);

                    $response['valid'] = (empty($response['errors'])) ? true : false;
                    $response['valid_json'] = true;

                    $response['total_records'] = $json_lines_count;

                    if (!empty($json_header['download_content_length'])) {
                        $response['download_content_length'] = $json_header['download_content_length'];
                    }

                    if (empty($response['errors'])) {
                        $response['errors'] = false;
                    }

                    return $response;

                } else {
                    $errors[] = "File not found or couldn't be downloaded";
                }
            }



            // See if it's valid JSON
            if (!empty($json) && $json_header['download_content_length'] < $max_remote_size) {

                // See if raw file is valid
                $raw_valid_json = is_json($json);

                // See if we can clean up the file to make it valid
                if (!$raw_valid_json) {
                    $json_processed = json_text_filter($json);
                    $valid_json = is_json($json_processed);
                } else {
                    $valid_json = true;
                }

                if ($valid_json !== true) {
                    $errors[] = 'The validator was unable to determine if this was valid JSON';
                }
            }

            if (!empty($errors)) {

                $valid_json = (isset($valid_json)) ? $valid_json : null;
                $raw_valid_json = (isset($raw_valid_json)) ? $raw_valid_json : null;

                $response = array(
                    'raw_valid_json' => $raw_valid_json,
                    'valid_json' => $valid_json,
                    'valid' => false,
                    'fail' => $errors,
                    'download_content_length' => $json_header['download_content_length']
                );


                if ($valid_json && $return_source === false) {
                    $catalog = json_decode($json_processed);

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
        if ($json && empty($json_processed)) {
            $json_processed = json_text_filter($json);
        }


        // verify it's valid json
        if ($json_processed) {
            if (!isset($valid_json)) {
                $valid_json = is_json($json_processed);
            }
        }


        if ($json_processed && $valid_json) {

            $json_decode = json_decode($json_processed);

            if (!empty($json_decode->conformsTo) && $json_decode->conformsTo == 'https://project-open-data.cio.gov/v1.1/schema') {


                if ($schema !== 'federal-v1.1' && $schema !== 'non-federal-v1.1') {

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

            if ($schema == 'federal-v1.1' && empty($json_decode->dataset)) {
                $errors[] = "This file does not appear to be using the federal-v1.1 schema";
                $response = array(
                    'raw_valid_json' => $raw_valid_json,
                    'valid_json' => $valid_json,
                    'valid' => false,
                    'fail' => $errors
                );
                return $response;
            }


            if ($schema !== 'federal-v1.1' && $schema !== 'non-federal-v1.1') {
                $chunk_size = 500;
                $json_chunks = array_chunk((array) $json_decode, $chunk_size);
            } else {
                $json_chunks = array((array) $json_decode);
            }


            $response = array();
            $response['errors'] = array();

            // save detected schema version to output
            $response['schema_version'] = $schema;

            foreach ($json_chunks as $chunk_count => $chunk) {

                $chunk = json_encode($chunk);
                $validator = $this->campaign->jsonschema_validator($chunk, $schema);

                if (!empty($validator['errors'])) {

                    if ($chunk_count) {
                        $key_offset = $chunk_size * $chunk_count;
                        $key_offset = $key_offset;
                    } else {
                        $key_offset = 0;
                    }

                    $response['errors'] = $response['errors'] + $this->process_validation_errors($validator['errors'], $key_offset);
                }

            }


            $valid_json = (isset($raw_valid_json)) ? $raw_valid_json : $valid_json;

            $response['valid'] = (empty($response['errors'])) ? true : false;
            $response['valid_json'] = $valid_json;


            if ($schema == 'federal-v1.1' OR $schema == 'non-federal-v1.1') {
                $response['total_records'] = count($json_decode->dataset);
            } else {
                $response['total_records'] = count($json_decode);
            }


            if (!empty($json_header['download_content_length'])) {
                $response['download_content_length'] = $json_header['download_content_length'];
            }

            if (empty($response['errors'])) {
                $response['errors'] = false;
            }

            if ($return_source) {
                $dataset_array = ($schema == 'federal-v1.1' OR $schema == 'non-federal-v1.1') ? true : false;
                $json_decode = filter_json($json_decode, $dataset_array);
                $response['source'] = $json_decode;
            }

            return $response;
        } else {
            $errors[] = "This does not appear to be valid JSON";
            $response = array(
                'valid_json' => false,
                'valid' => false,
                'fail' => $errors
            );
            if (!empty($json_header['download_content_length'])) {
                $response['download_content_length'] = $json_header['download_content_length'];
            }

            return $response;
        }
    }

    public function jsonschema_validator($data, $schema = null, $chunked = null) {

        if ($data) {

            $schema_variant = (!empty($schema)) ? "$schema" : "";

            $schema_module = ($schema == 'federal-v1.1' && $chunked == true) ? 'dataset.json' : 'catalog.json';

            $path = './schema/' . $schema_variant;
            if (file_exists(realpath($path . '.json'))) {
                $path .= '.json';
            }
            else {
                $path .= "/" . $schema_module;
            }

            //echo $path; exit;
            // Get the schema and data as objects
            $retriever = new JsonSchema\Uri\UriRetriever;
            $schema = $retriever->retrieve('file://' . realpath($path));


            //header('Content-type: application/json');
            //print $data;
            //exit;

            $data = json_decode($data);

            if (!empty($data)) {
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
                    $errors = $validator->getErrors();

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

            if (!is_numeric($error['property']) AND ( $error['property'] === '') OR ( $error['property'] === '@context') OR ( $error['property'] === '@type') OR ( $error['property'] === '@id') OR ( $error['property'] === 'describedBy') OR ( $error['property'] === 'conformsTo')) {
                $error['property'] = 'catalog.' . $error['property'];
            }

            if (is_numeric($error['property']) OR strpos($error['property'], '.') === false OR $error['property'] === 'catalog.') {
                $key = ($error['property'] === 'catalog.') ? 'catalog' : $error['property'];
                $field = 'ALL';
            } else {

                if (strpos($error['property'], 'dataset[') !== false) {
                    $dataset_key = substr($error['property'], 0, strpos($error['property'], '.'));
                    $key = get_between($dataset_key, '[', ']');
                    $full_field = substr($error['property'], strpos($error['property'], '.') + 1);
                } else {
                    $key = substr($error['property'], 0, strpos($error['property'], '.'));
                    $full_field = substr($error['property'], strpos($error['property'], '.') + 1);
                }


                if (strpos($full_field, '[')) {
                    $field = substr($full_field, 0, strpos($full_field, '['));
                    $subfield = 'child-' . get_between($full_field, '[', ']');
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

    public function validation_check($id, $title, $url, $format = null) {

        $tmp_dir = $this->config->item('archive_dir');

        $header = curl_header($url, false, $tmp_dir);
        $good_link = false;
        $good_format = true;

        if (!empty($header['info']['http_code']) && preg_match('/[5]\d{2}\z/', $header['info']['http_code'])) {
            $this->validation_counts['http_5xx'] ++;
        }

        if (!empty($header['info']['http_code']) && preg_match('/[4]\d{2}\z/', $header['info']['http_code'])) {
            $this->validation_counts['http_4xx'] ++;
        }

        if (!empty($header['info']['http_code']) && preg_match('/[3]\d{2}\z/', $header['info']['http_code'])) {
            $this->validation_counts['http_3xx'] ++;
        }

        if (!empty($header['info']['http_code']) && preg_match('/[2]\d{2}\z/', $header['info']['http_code'])) {
            $this->validation_counts['http_2xx'] ++;
            $good_link = true;
        }

        if (empty($header['info']['http_code'])) {
            $this->validation_counts['http_0'] ++;
        }

        if ($good_link && !empty($format) && !empty($header['info']['content_type']) && stripos($header['info']['content_type'], $format) === false) {
            $this->validation_counts['format_mismatch'] ++;
            $good_format = false;
        }

        if ($good_link && !empty($header['info']['content_type']) && stripos($header['info']['content_type'], 'application/pdf') !== false) {
            $this->validation_counts['pdf'] ++;
        }

        if ($good_link && !empty($format) && !empty($header['info']['content_type']) && stripos($header['info']['content_type'], 'text/html') !== false) {
            $this->validation_counts['html'] ++;
        }

        if ($good_link === false OR $good_format === false) {
            $error_report = $this->error_report_model();
            $error_report['id'] = $id;
            $error_report['title'] = $title;
            $error_report['error_type'] = (!$good_link) ? 'broken_link' : 'format_mismatch';
            $error_report['url'] = $url;
            $error_report['http_status'] = $header['info']['http_code'];
            $error_report['format_served'] = $header['info']['content_type'];
            $error_report['format_json'] = $format;
            $error_report['crawl_date'] = date(DATE_W3C);

            // ######## Log this to a CSV ##########
            // if this is the first record to log, prepare the file
            if ($this->validation_pointer == 0) {

                $download_dir = $this->config->item('archive_dir');
                $directory = "$download_dir/error_log";

                // create error log directory if needed
                if (!file_exists($directory)) {
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
            'format_json' => null,
            'crawl_date' => null
        );

        return $error;
    }

    public function archive_file($filetype, $office_id, $url) {

        $download_dir = $this->config->item('archive_dir');

        if ($filetype == 'json-lines') {
            $directory = "$download_dir/json-lines";
            $filepath = $directory . '/' . $office_id . '.raw';
        } else if ($filetype == 'policyarchive') {
            $crawl_date = date("Y-m-d");
            $directory = "$download_dir/$filetype/$crawl_date";
            $extension = "zip";
            if (substr($url, -6) == 'tar.gz') {
                $extension = 'tar.gz';
            }
            else {
                $ext_pos = strrpos($url, '.') + 1;
                if ($ext_pos !== false) {
                    $extension = substr($url, $ext_pos);
                }
            }
            $filepath = $directory . '/' . $office_id . '.' . $extension;
        } else {
            $crawl_date = date("Y-m-d");
            $directory = "$download_dir/$filetype/$crawl_date";
            $filepath = $directory . '/' . $office_id . '.json';
        }


        if (!is_dir($directory)) {

            if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                echo 'Creating directory ' . $directory . PHP_EOL;
            }

            mkdir($directory);
        }

        // Attempt to get JSON, via URL in normal mode or locally if in simulation mode
        if (config_item('simulate_office_data') && in_array($filetype, array('bureaudirectory', 'governanceboard'))) {

            $path = str_replace('system', 'archive/' . $filetype, BASEPATH);
            $file = 'example' . (rand(1, 6)) . '.json';
            $url = $path . $file;
            echo "Simulating $filetype data for office $office_id with $file" . PHP_EOL;
            $copy = @fopen($url, 'rb');

	    // If we can't read from this file, skip
	    if ($copy === false) {

	      if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                echo 'Could not read from ' . $url . PHP_EOL;
	      }
	    }

	    $paste = @fopen($filepath, 'wb');

	    // If we can't write to this file, skip
	    if ($paste === false) {

	      if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                echo 'Could not open ' . $filepath . PHP_EOL;
	      }
	    }

	    if ($copy !== false && $paste !== false) {
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


        } else {

            if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                echo 'Attempting to download ' . $url . ' to ' . $filepath . PHP_EOL;
            }

	    if (config_item('proxy_host') && config_item('proxy_port')) {
	      $ch = curl_init ($url);
	      curl_setopt($ch, CURLOPT_HEADER, 0);
	      $proxy = config_item('proxy_host') .":" .config_item('proxy_port');
	      curl_setopt($ch, CURLOPT_PROXY, $proxy);
          curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; CrawlBot/1.0.0)');
	      curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
	      $copy=curl_exec($ch);
	    } else {
	      //$copy = file_get_contents($url, false);
          $ch = curl_init ($url);
	      curl_setopt($ch, CURLOPT_HEADER, 0);
          curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; CrawlBot/1.0.0)');
	      curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
	      $copy=curl_exec($ch);

	    }

	    // If we can't read from this file, skip
	    if ($copy === false || $copy === "") {
	      if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                echo 'Could not read from ' . $url . PHP_EOL;
	      }
	      $copy == false;
	    }

      	    if ($copy !== false) {
	      if (! file_put_contents($filepath, $copy) ) {
                echo 'Could not write to ' . $filepath . PHP_EOL;
	      }
	    }
	}

        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
            echo 'Done' . PHP_EOL . PHP_EOL;
        }

        return $filepath;
    }

    public function update_status($update) {

        $existing_status = array();
        $tracker_update = false;

        // Determine current milestone
        $selected_milestone = (!empty($update->milestone)) ? $update->milestone : null;
        $milestones = $this->milestones_model();
        $milestone = $this->milestone_filter($selected_milestone, $milestones);

        $update->milestone = $milestone->selected_milestone;

        // Check if this is to update tracker fields
        if (!empty($update->status_id)) {
            $existing_status['status_id'] = $update->status_id;
            $this->db->where('status_id', $update->status_id);

            if (empty($update->crawl_status)) {
                $tracker_update = true;
            }
        }

        $this->db->select('status_id, crawl_status');
        $this->db->where('office_id', $update->office_id);
        $this->db->where('milestone', $update->milestone);
        $this->db->where("(crawl_status IS NULL OR crawl_status = 'final')");
        $this->db->limit(1);

        $query = $this->db->get('ciogov_campaign');

        if ($query->num_rows() > 0) {
            $row = $query->row();
            $existing_status['status_id'] = $row->status_id;

            if (!empty($row->crawl_status)) {
                $existing_status['crawl_status'] = $row->crawl_status;
                $update->crawl_status = $row->crawl_status;
            }
        }

        // if this is to update tracker fields (crawl_status would be empty)
        if (!empty($existing_status)) {

            // if this is to update tracker fields
            if (empty($update->crawl_status)) {

                $this->db->where('status_id', $existing_status['status_id']);
                $this->db->where('office_id', $update->office_id);
                $this->db->where('milestone', $update->milestone);

                $this->db->update('ciogov_campaign', $update);
            }

            // if this is just an old record, change the crawl_status
            if (empty($existing_status['crawl_status'])) {

                if (!empty($update->crawl_status) && $update->crawl_status == 'in_progress') {
                    $old_status = 'current';
                }

                if (!empty($update->crawl_status) && $update->crawl_status == 'current') {
                    $old_status = 'archive';
                }

                if (!empty($old_status)) {
                    $reset = array('crawl_status' => $old_status);

                    $this->db->where('status_id', $existing_status['status_id']);
                    $this->db->update('ciogov_campaign', $reset);
                }
            }
        }


        // Check if this is an in-progress crawl to update or a mid-quarter tracker update
        if ($tracker_update OR ( isset($update->status_id) && !empty($update->crawl_status))) {

            $this->db->where('status_id', $update->status_id);
            $this->db->update('ciogov_campaign', $update);

            $status_id = $update->status_id;

            // Otherwise this is an insert
        } else {


            if (isset($update->status_id)) {
                unset($update->status_id);
            }

            if ($this->environment == 'terminal') {
                echo 'Adding ' . $update->office_id . PHP_EOL . PHP_EOL;
            }

            // Copy tracker data and GAO recommendations over from the current
            // record for this milestone
            $this->db->select('tracker_fields, tracker_status, recommendation_status');
            $this->db->where('office_id', $update->office_id);
            $this->db->where('milestone', $update->milestone);
            $this->db->where('crawl_status', 'current');
            $this->db->order_by('status_id', 'desc');
            $this->db->limit(1);
            $query = $this->db->get('ciogov_campaign');
            if ($query->num_rows() > 0) {
                error_log('Found current record');
                $row = $query->row();
                if (config_item('simulate_office_data') && empty($row->tracker_fields)) {
                    $update->tracker_fields = $this->simulate_tracker_fields();
                } else {
                    $update->tracker_fields = $row->tracker_fields;
                }
                $update->tracker_status = $row->tracker_status;
                $update->recommendation_status = $row->recommendation_status;
            } else {
                if (config_item('simulate_office_data')) {
                    $update->tracker_fields = $this->simulate_tracker_fields();
                }
                else {
                    $update->tracker_fields = $this->seed_first_tracker_fields();
                }
            }

            $this->db->insert('ciogov_campaign', $update);
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

            $query = $this->db->get('ciogov_campaign');

            if ($query->num_rows() > 0) {

                $row = $query->row();
                $reset = array('crawl_status' => 'archive');

                $this->db->where('status_id', $row->status_id);
                $this->db->update('ciogov_campaign', $reset);
            }
        }

        return $status_id;
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

    public function json_schema($version = '') {

        $version_path = (!empty($version)) ? $version . '/' : '';

        $path = './schema/' . $version_path . 'catalog.json';

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


            if (!empty($value->type) && $value->type == 'object') {

                // This is just hard coded to prevent recursion, but should be replaced with proper recursion detection
                if ($key == 'subOrganizationOf') {
                    $model->$key = null;
                } else {
                    $model->$key = $this->schema_to_model($value->properties);
                }
            } else if (!empty($value->items) && $value->type == 'array') {

                $model->$key = array();

                if (!empty($value->items->properties)) {
                    $model->$key = array($this->schema_to_model($value->items->properties));
                }
            } else if (!empty($value->anyOf)) {

                foreach ($value->anyOf as $anyOptions) {

                    if (!empty($anyOptions->type) && $anyOptions->type == 'array') {

                        $model->$key = array();

                        if (!empty($anyOptions->items) && !empty($anyOptions->items->type) && $anyOptions->items->type == 'object') {
                            $model->$key = array($this->schema_to_model($anyOptions->items->properties));
                        }
                    }
                }

                if (!isset($model->$key)) {
                    $model->$key = null;
                }
            } else {

                if ($key == '@type' && !empty($value->enum)) {
                    $model->$key = $value->enum[0];
                } else {
                    $model->$key = null;
                }
            }
        }

        return $model;
    }

    /**
     * Return the GAO Recommendations record for the given office and milestone.
     *
     * @param <int> $office_id
     * @param <date> $milestone
     * @return <object|false>
     */
    public function ciogov_office_recommendations($office_id, $milestone = null)
    {
      $this->db->select('*');
      $this->db->where('office_id', $office_id);
      $this->db->where('recommendation_status is not NULL');
      $this->db->order_by("crawl_end", "desc");

      if ($milestone)
        $this->db->where('milestone', $milestone);
      $this->db->limit(1);

      $query = $this->db->get('ciogov_campaign');

      if ($query->num_rows() > 0) {
        return $query->row();
      } else {
        $office = new stdClass();
        $office->recommendation_status = "";
        return $office;
      }
    }

    /**
     * TO DO - when agencies provide a valid url, we should validate that before
     * the download.
     *
     * Open the archived file that has been downloaded in campaign status method and
     * validate it against the schema
     *
     * @param <array> $status
     * @param <string> $file_path
     * @param <string> $component
     * @param <string> $real_url
     */
    public function validate_archive_file_with_schema($status, $file_path, $component, $real_url)
    {
      $fp = fopen($file_path, 'r');

      if(!$fp) {
        $status['errors'][] = "Unable to open archived json file";
      }

      $status['total_records'] = 0;
      $status['download_content_length'] = 0;
      $status['content_type'] = "application/json";
      $status['schema_version'] = "1.0";
      $json = file_get_contents($file_path);

      if(empty($json)) {
        $status['errors'][] = 'Archived json file is empty';
        $status['valid_json'] = false;
       }
      else if(!is_json($json)) {
        $json = json_text_filter($json);
      }

      if(!empty($json) && !is_json($json)) {
        $status['errors'][] = 'Invalid archived json file';
        $status['valid_json'] = false;
      }
      else {
        $status['download_content_length'] = strlen($json);
        $data = json_decode($json);
        $status['total_records'] = count($data);
        $status['valid_json'] = true;
     }

     $schema = $this->datajson_schema($component);

      if (!empty($data)) {
        $validator = new JsonSchema\Validator();
        $validator->check($data, $schema);

        if (!$validator->isValid()) {
          $errors = $validator->getErrors();
          $status['schema_errors'] = $errors;
         }
       }

      return $status;
    }

    /**
     * Get the Recommendation schema definition
     *
     * @param string $component
     * @return <array>
     */
    public function datajson_schema($component) {

      $path = './schema/' . $component . '.json';

      // Get the schema and data as objects
      $retriever = new JsonSchema\Uri\UriRetriever;
      $schema = $retriever->retrieve('file://' . realpath($path));

      return $schema;
    }

    /**
     * Simulate tracker data
     *
     * @return string
     */
    public function simulate_tracker_fields() {

        if (!config_item('simulate_office_data')) {
            return '';
        }

        $tracker_fields = new stdClass();

        foreach ($this->tracker_model() as $key => $field) {
            if ($field->type === 'integer' || $field->type === 'percent') {
                $tracker_fields->$key = rand(0, 99);
            } elseif ($field->type === 'select') {
                $tracker_fields->$key = rand(0, 1) === 1 ? 'yes' : '';
            } elseif ($field->type === 'url') {
                $url = 'http://example.gov/' . substr(md5(rand(0, 9)), 0, rand(10, 20));
                $tracker_fields->$key = '<a href="' . $url . '" target="_blank">' . $url . '</a>';
            }
        }

        return json_encode($tracker_fields);
    }

    /**
     * Seed initial tracker data
     *
     * @return string
     */
    public function seed_first_tracker_fields() {

        $tracker_fields = new stdClass();

        foreach ($this->tracker_model() as $key => $field) {
            $tracker_fields->$key = '';
        }

        return json_encode($tracker_fields);
    }

}

?>
