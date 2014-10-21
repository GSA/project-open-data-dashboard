<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Campaign extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->helper('api');
		$this->load->helper('url');

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
	public function index()
	{


	}

	public function convert($orgs = null, $geospatial = null, $harvest = null, $from_export = null) {
		$this->load->model('campaign_model', 'campaign');

		$orgs 			= (!empty($orgs)) ? $orgs : $this->input->get('orgs', TRUE);
		$geospatial 	= (!empty($geospatial)) ? $geospatial : $this->input->get('geospatial', TRUE);
		$harvest 		= (!empty($harvest)) ? $harvest : $this->input->get('harvest', TRUE);
		$from_export 	= (!empty($from_export)) ? $from_export : $this->input->get('from_export', TRUE);


		$row_total = 100;
		$row_count = 0;

		$row_pagesize = 100;
		$raw_data = array();

		while($row_count < $row_total) {
			$result 	= $this->campaign->get_datagov_json($orgs, $geospatial, $row_pagesize, $row_count, true, $harvest);

			if(!empty($result->result)) {

				$row_total = $result->result->count;
				$row_count = $row_count + $row_pagesize;

				$raw_data = array_merge($raw_data, $result->result->results);

				if($from_export == 'true') break;

			} else {
				break;
			}

		}

		if(!empty($raw_data)) {

			$json_schema = $this->campaign->datajson_schema();
			$datajson_model = $this->campaign->schema_to_model($json_schema->properties);

			$convert = array();
			foreach ($raw_data as $ckan_data) {
				$model = clone $datajson_model;
				$convert[] = $this->campaign->datajson_crosswalk($ckan_data, $model);
			}

			if($this->environment == 'terminal') {
				$filepath = 'export.json';

				echo 'Creating file at ' . $filepath . PHP_EOL . PHP_EOL;

				$export_file = fopen($filepath, 'w');
				fwrite($export_file, json_encode($convert));
				fclose($export_file);
			} else {

			    header('Content-type: application/json');
			    print json_encode($convert);
				exit;
			}

		} else {

			if($this->environment == 'terminal') {
				echo 'No results found for ' . $orgs;
			} else {
		    	header('Content-type: application/json');
			    print json_encode(array("error" => "no results"));
				exit;
			}
		}

	}

	public function csv_to_json() {


		$csv_id 		= ($this->input->post('csv_id', TRUE)) ? $this->input->post('csv_id', TRUE) : null;


		// Initial file upload
		if(!empty($_FILES)) {

			$this->load->library('upload');

			if($this->do_upload('csv_upload')) {

				$data = $this->upload->data();


				ini_set("auto_detect_line_endings", true);
				$csv_handle = fopen($data['full_path'], 'r');
				$headings = fgetcsv($csv_handle);

				// Provide mapping between csv headings and POD schema
				$this->load->model('campaign_model', 'campaign');

				$json_schema = $this->campaign->datajson_schema();
				$datajson_model = $this->campaign->schema_to_model($json_schema->properties);

				$output = array();
				$output['headings'] 		= $headings;
				$output['datajson_model'] 	= $datajson_model;
				$output['csv_id'] 			= $data['file_name'];

				$this->load->view('csv_mapping', $output);


			}

		}

		// Apply mapping and convert file to JSON
		else if (!empty($csv_id)) {

			$mapping = ($this->input->post('mapping', TRUE)) ? $this->input->post('mapping', TRUE) : null;

	 		$this->config->load('upload', TRUE);
	 		$upload_config = $this->config->item('upload');

			$full_path = $upload_config['upload_path'] . $csv_id;

			$this->load->helper('csv');
			ini_set("auto_detect_line_endings", true);

			$importer = new CsvImporter($full_path, $parse_header = true, $delimiter = ",");
			$csv = $importer->get();

			$column_headers = array();
			foreach($csv[0] as $key => $this_header) {
				$column_headers[$key] = trim($this_header);
			}

			$json = array();
			foreach ($csv as $row) {

				$count = 0;
				$json_row = array();
				foreach($row as $key => $value) {
					if(!empty($column_headers[$key]) && $mapping[$count] !== 'null') {


						if(is_json($value)){
							$value = json_decode($value);
						} else if ($mapping[$count] == 'keyword' |
								   $mapping[$count] == 'language' |
								   $mapping[$count] == 'references' |
								   $mapping[$count] == 'theme' |
								   $mapping[$count] == 'programCode' |
								   $mapping[$count] == 'bureauCode') {
							$value = str_getcsv($value);
						} else if ($mapping[$count] == 'dataQuality' && !empty($value)) {
							$value = (bool) $value;
						}

						if(is_array($value)) {
							$value = array_map("make_utf8", $value);
							$value = array_map("trim", $value);
							$value = array_filter($value); // removes any empty elements in an array
							$value = array_values($value); // ensures array_filter doesn't create an associative array
						} else if (is_string($value)) {
							$value = trim($value);
							$value = make_utf8($value);
						}

						$value = (!is_bool($value) && empty($value)) ? null : $value;

						$json_row[$mapping[$count]] = $value;
					}

					$count++;
				}

				$json[] = $json_row;

			}


			// delete temporary uploaded csv file
			unlink($full_path);

			// provide json for download
    		header("Pragma: public");
    		header("Expires: 0");
    		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    		header("Cache-Control: private",false);
    		header('Content-type: application/json');
    		header("Content-Disposition: attachment; filename=\"$csv_id.json\";" );
    		header("Content-Transfer-Encoding: binary");

    		print json_encode($json);
    		exit;



		}

		// Show upload form
		else {
			$this->load->view('csv_upload');
		}

	}

	public function do_upload($field_name = null) {

		if (!$this->upload->do_upload($field_name)) {
		    return false;
		} else {
		    return true;
		}
	}


	public function csv($orgs = null) {
		$this->load->model('campaign_model', 'campaign');

		if($orgs == 'all') {
		    $orgs = '*';
		}

		if(empty($orgs)) {
		    $orgs = $this->input->get('orgs', TRUE);
		}

		if(empty($orgs)) {
		    $geospatial = $this->input->get('geospatial', TRUE);
		} else {
		    $geospatial = false;
		}

        // if we didn't get any requests, bail
        if(empty($orgs)) {
    		show_404($orgs, false);
    		exit;
        }

		$row_total = 100;
		$row_count = 0;

		$row_pagesize = 500;
		$raw_data = array();

		while($row_count < $row_total) {
			$result 	= $this->campaign->get_datagov_json($orgs, $geospatial, $row_pagesize, $row_count, true);

			if(!empty($result)) {
				$row_total = $result->result->count;
				$row_count = $row_count + $row_pagesize;

                if ($this->environment == 'terminal' OR $this->environment == 'cron') {
                    echo 'Exporting ' . $row_count . ' of ' . $row_total .  PHP_EOL;
                }

				$raw_data = array_merge($raw_data, $result->result->results);
			} else {
				break;
			}

		}

        // if we didn't get any data, bail
		if(empty($raw_data)) {
		    show_404($orgs, false);
		    exit;
		}



		// use data.json model
		$json_schema = $this->campaign->datajson_schema();
		$datajson_model = $this->campaign->schema_to_model($json_schema->properties);

		$csv_rows = array();
		foreach ($raw_data as $ckan_data) {

            $special_extras = $this->special_extras($ckan_data);

			$model      = clone $datajson_model;
		    $csv_row    = $this->campaign->datajson_crosswalk($ckan_data, $model);

            $csv_row->accessURL  = array();
            $csv_row->format     = array();
            foreach ($csv_row->distribution as $distribution) {
                $csv_row->accessURL[]   = $distribution->accessURL;
                $csv_row->format[]      = $distribution->format;
            }

    		foreach ($csv_row as $key => $value) {

    			if(empty($value) OR is_object($value) == true OR (is_array($value) == true && !empty($value[0]) && is_object($value[0]) == true)) {
    			    $csv_row->$key = null;
    			}

    			if(is_array($value) == true && !empty($value[0]) && is_object($value[0]) == false) {
    			    $csv_row->$key = implode(',', $value);
    			}


    		}

    		$csv_row->_extra_catalog_url                = 'http://catalog.data.gov/dataset/' . $csv_row->identifier;
            $csv_row->_extra_communities                = $special_extras->groups;
            $csv_row->_extra_communities_categories     = $special_extras->group_categories;

			$csv_rows[] = (array) $csv_row;
		}

	   //header('Content-type: application/json');
	   //print json_encode($csv_rows);
	   //exit;


		$headings = array_keys($csv_rows[0]);

		// Open the output stream
        if ($this->environment == 'terminal' OR $this->environment == 'cron') {
            $filepath = realpath('./csv/output.csv');
            $fh = fopen($filepath, 'w');
            echo 'Attempting to save csv to ' . $filepath .  PHP_EOL;
        } else {
            $fh = fopen('php://output', 'w');
        }


		// Start output buffering (to capture stream contents)
		ob_start();
		fputcsv($fh, $headings);

		// Loop over the * to export
		if (!empty($csv_rows)) {
			foreach ($csv_rows as $row) {
				fputcsv($fh, $row);
			}
		}

        if ($this->environment !== 'terminal') {
    		// Get the contents of the output buffer
    		$string = ob_get_clean();
    		$filename = 'csv_' . date('Ymd') .'_' . date('His');
    		// Output CSV-specific headers

    		header("Pragma: public");
    		header("Expires: 0");
    		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    		header("Cache-Control: private",false);
    		header('Content-type: text/csv');
    		header("Content-Disposition: attachment; filename=\"$filename.csv\";" );
    		header("Content-Transfer-Encoding: binary");

    		exit($string);
        } else {
            echo 'Done' . PHP_EOL;
            exit;
        }



	}


    private function special_extras($ckan_data) {

        $special_extras = new stdClass();

	    // communities
	    $groups = array();
	    if(!empty($ckan_data->groups)) {
	        foreach ($ckan_data->groups as $group) {
	            if(!empty($group->title)) {
	                $groups[] = $group->title;
	                $groups_id[] = $group->id;
	            }
	        }
	    }
	    $special_extras->groups = (!empty($groups)) ? implode(',', $groups) : null;

	    // community categories
	    $group_categories = array();
	    if(!empty($groups_id)) {
	        foreach ($groups_id as $group_id) {
	            $group_category_id = '__category_tag_' . $group_id;

	            if(!empty($ckan_data->extras)) {
	                foreach($ckan_data->extras as $extra) {

	                    if ($extra->key == $group_category_id) {
	                        $categories = json_decode($extra->value);
	                        if(is_array($categories)) {
	                            foreach($categories as $category_name) {
	                                $group_categories[$category_name] = true;
	                            }
	                        }

	                    }
	                }

	            }
	        }
	    }
	    if(!empty($group_categories)) {
	        $group_categories = array_keys($group_categories);
	        $special_extras->group_categories = implode(',', $group_categories);
	    } else {
	        $special_extras->group_categories = null;
	    }


	    // formats
        $formats = array();
	    if(!empty($ckan_data->resources)) {
	        foreach ($ckan_data->resources as $resource) {
	            if(!empty($resource->format)) {
	                $formats[] = (string) $resource->format;
                }
	        }
	    }
	    $special_extras->formats = (!empty($formats)) ? implode(',', $formats) : null;


        return $special_extras;
    }



	public function digitalstrategy($id = null) {


		$this->load->model('campaign_model', 'campaign');

		$this->db->select('*');
		$this->db->from('offices');
		$this->db->join('datagov_campaign', 'datagov_campaign.office_id = offices.id', 'left');
		$this->db->where('offices.cfo_act_agency', 'true');
		$this->db->where('offices.no_parent', 'true');

		if(!empty($id) && $id != 'all') {
    		$this->db->where('offices.id', $id);
		}

		$this->db->order_by("offices.name", "asc");
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$view_data['digitalstrategy'] = $query->result();
			$query->free_result();

			$this->load->view('digitalstrategy', $view_data);
		} else {
    		show_404('digitalgov', false);
		}


	}



    /*
    $id can be all, cfo-act, or a specific id
    $component can be datajson, datapage, digitalstrategy, download
    */
	public function status($id = null, $component = null, $selected_milestone = null) {

		// enforce explicit component selection
		if(empty($component)) {
		    show_404('status', false);
		}

		if($component == 'all' || $component == 'download' ) {
			$this->load->helper('file');
		}

		$this->load->model('campaign_model', 'campaign');

		$this->db->select('url, id');

		// Filter for certain offices
		if($id == 'cfo-act') {
			$this->db->where('cfo_act_agency', 'true');
		}

		if (is_numeric($id)) {
			$this->db->where('id', $id);
		}

		// Determine current milestone

		$milestones 			= $this->campaign->milestones_model();	
		$milestone 				= $this->campaign->milestone_filter($selected_milestone, $milestones);


		$query = $this->db->get('offices');

		if ($query->num_rows() > 0) {
		   	$offices = $query->result();

			foreach ($offices as $office) {

				// Set current office id
				$this->campaign->current_office_id = $office->id;
				$this->campaign->validation_pointer = 0;


				// initialize update object
				$update = $this->campaign->datagov_office($office->id, $milestone->selected_milestone);

    			if(!$update){
    				$update = $this->campaign->datagov_model();
					$update->office_id = $office->id;
    			}

				$url =  parse_url($office->url);
				$url = $url['scheme'] . '://' . $url['host'];



                /*
                ################ datajson ################
                */

			    if ($component == 'all' || $component == 'datajson' || $component == 'download') {

    				$expected_datajson_url = $url . '/data.json';

    				// attempt to break any caching
    				$expected_datajson_url_refresh = $expected_datajson_url . '?refresh=' . time();

    				if ($this->environment == 'terminal' OR $this->environment == 'cron') {
    					echo 'Attempting to request ' . $expected_datajson_url . ' and ' . $expected_datajson_url_refresh . PHP_EOL;
    				}

                    // Try to force refresh the cache, follow redirects and get headers
        		    $json_refresh = true;
            		$status = $this->campaign->uri_header($expected_datajson_url_refresh);

            		if(!$status OR $status['http_code'] != 200) {
            		    $json_refresh = false;
            		    $status = $this->campaign->uri_header($expected_datajson_url);
            		}


            		//$status['url']          = $expected_datajson_url;
            		$status['expected_url'] = $expected_datajson_url;



	                $real_url = ($json_refresh) ? $expected_datajson_url_refresh : $expected_datajson_url;


	                /*
	        		################ download ################
	        		*/
	    			if ($component == 'all' || $component == 'download') {

	    				if(!($status['http_code'] == 200)) {

		    				if ($this->environment == 'terminal' OR $this->environment == 'cron') {
		    					echo 'Resource ' . $real_url . ' not available' . PHP_EOL;
		    				}

	    					continue;
	    				}

	    				// download and version this data.json file.
	    				$datajson_archive_status = $this->archive_file('datajson', $office->id, $real_url);

	    			}

	                /*
	        		################ datajson ################
	        		*/
	    			if ($component == 'all' || $component == 'datajson') {

	    				// Save current update status in case things break during json_status
						$update->datajson_status = (!empty($status)) ? json_encode($status) : null;

						if ($this->environment == 'terminal' OR $this->environment == 'cron') {
							echo 'Attempting to set ' . $update->office_id . ' with ' . $update->datajson_status . PHP_EOL . PHP_EOL;
						}

						$this->campaign->update_status($update);

	                	// Check JSON status
	                	$status 				= $this->json_status($status, $real_url);	        		

	        			// Set correct URL
	        			if(!empty($status['url'])) {
	        				if(strpos($status['url'], '?refresh=')) {
	        					$status['url'] = substr($status['url'], 0, strpos($status['url'], '?refresh='));
	        				} 
	        			} else {
	        				$status['url'] = $expected_datajson_url;
	        			}

	        			$status['expected_url'] = $expected_datajson_url;
						$status['last_crawl']	= mktime();

		
						if(is_array($status['schema_errors']) && !empty($status['schema_errors'])) {
							$status['error_count'] = count($status['schema_errors']);
						} else if ($status['schema_errors'] === false) {
							$status['error_count'] = 0;
						} else {
							$status['error_count'] = null;
						}

						$status['schema_errors'] = (!empty($status['schema_errors'])) ? array_slice($status['schema_errors'], 0, 10, true) : null;

						$update->datajson_status = (!empty($status)) ? json_encode($status) : null;
						//$update->datajson_errors = (!empty($status) && !empty($status['schema_errors'])) ? json_encode(array_slice($status['schema_errors'], 0, 10, true)) : null;
						if(!empty($status) && !empty($status['schema_errors'])) unset($status['schema_errors']);


						if ($this->environment == 'terminal' OR $this->environment == 'cron') {
							echo 'Attempting to set ' . $update->office_id . ' with ' . $update->datajson_status . PHP_EOL . PHP_EOL;
						}

	                	$this->campaign->update_status($update);
	    			}

				}


                /*
                ################ datapage ################
                */

               if ($component == 'all' || $component == 'datapage') {


                    // Get status of html /data page
    				$page_status_url = $url . '/data';

    				if ($this->environment == 'terminal' OR $this->environment == 'cron') {
    					echo 'Attempting to request ' . $page_status_url . PHP_EOL;
    				}

            		$page_status = $this->campaign->uri_header($page_status_url);
            		$page_status['expected_url'] = $page_status_url;
            		$page_status['last_crawl']	= mktime();

    				$update->datapage_status = (!empty($page_status)) ? json_encode($page_status) : null;

    				if ($this->environment == 'terminal' OR $this->environment == 'cron') {
    					echo 'Attempting to set ' . $update->office_id . ' with ' . $update->datapage_status . PHP_EOL . PHP_EOL;
    				}

    				$this->campaign->update_status($update);

			    }


                 /*
                 ################ digitalstrategy ################
                 */

                if ($component == 'all' || $component == 'digitalstrategy' || $component == 'download') {


                     // Get status of html /data page
     				$digitalstrategy_status_url = $url . '/digitalstrategy.json';

     				if ($this->environment == 'terminal' OR $this->environment == 'cron') {
     					echo 'Attempting to request ' . $digitalstrategy_status_url . PHP_EOL;
     				}

             		$page_status = $this->campaign->uri_header($digitalstrategy_status_url);
             		$page_status['expected_url'] = $digitalstrategy_status_url;
             		$page_status['last_crawl']	= mktime();

     				$update->digitalstrategy_status = (!empty($page_status)) ? json_encode($page_status) : null;

     				if ($this->environment == 'terminal' OR $this->environment == 'cron') {
     					echo 'Attempting to set ' . $update->office_id . ' with ' . $update->digitalstrategy_status . PHP_EOL . PHP_EOL;
     				}

     				$this->campaign->update_status($update);

     				// download and version this json file.
     				if ($component == 'all' || $component == 'download') {	    				
	    				$digitalstrategy_archive_status = $this->archive_file('digitalstrategy', $office->id, $digitalstrategy_status_url);
					}

 			    }

        		if(!empty($id) && $this->environment != 'terminal' && $this->environment != 'cron') {
        		    $this->load->helper('url');
                    redirect('/offices/detail/' . $id, 'location');
                }

			}

			// Close file connections that are still open 
			if(is_resource($this->campaign->validation_log)) {
				fclose($this->campaign->validation_log);
			}

		}

	}

	public function json_status($status, $real_url = null) {

        // if this isn't an array, assume it's a urlencoded URI
        if(is_string($status)) {
            $this->load->model('campaign_model', 'campaign');

            $expected_datajson_url = urldecode($status);

       		$status = $this->campaign->uri_header($expected_datajson_url);
        	$status['url'] = (!empty($status['url'])) ? $status['url'] : $expected_datajson_url;
        }

        $status['url'] = (!empty($status['url'])) ? $status['url'] : $real_url;
        
		if($status['http_code'] == 200) {

			$qa = ($this->environment == 'terminal' OR $this->environment == 'cron') ? 'all' : true;

			$validation = $this->campaign->validate_datajson($status['url'], null, null, 'federal', false, $qa);

			if(!empty($validation)) {
				$status['valid_json'] = $validation['valid_json'];
				$status['valid_schema'] = $validation['valid'];
				$status['total_records'] = (!empty($validation['total_records'])) ? $validation['total_records'] : null;

				if(isset($validation['errors']) && is_array($validation['errors']) && !empty($validation['errors'])) {
					$status['schema_errors'] = $validation['errors'];
				} else if (isset($validation['errors']) && $validation['errors'] === false) {
					$status['schema_errors'] = false;
				} else {
					$status['schema_errors'] = null;
				}

				$status['qa'] = (!empty($validation['qa'])) ? $validation['qa'] : null;

				$status['download_content_length'] = (!empty($status['download_content_length'])) ? $status['download_content_length'] : null;
				$status['download_content_length'] = (!empty($validation['download_content_length'])) ? $validation['download_content_length'] : $status['download_content_length'];

			} else {
				// data.json was not valid json
				$status['valid_json'] = false;
			}

		}

		return $status;
	}



	public function archive_file($filetype, $office_id, $url) {

		$download_dir = $this->config->item('archive_dir');
		$crawl_date = date("Y-m-d");
		$directory = "$download_dir/$filetype/$crawl_date";
		$filepath = $directory . '/' . $office_id . '.json';

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

		return true;

	}


	public function status_review_update() {

		// Kick them out if they're not allowed here.
		if ($this->session->userdata('permissions') !== 'admin') {
			$this->load->helper('url');
            redirect('/');
            exit;
 		}


        $update = (object) $this->input->post(NULL, TRUE);


        $this->load->model('campaign_model', 'campaign');

		$datagov_model_fields = $this->campaign->datagov_model();
        $tracker_review_model = $this->campaign->tracker_review_model();

		$datagov_model_fields->office_id = $update->office_id;
		$datagov_model_fields->milestone = $update->milestone;
   
        // Set author name with best data available
		$author_full = $this->session->userdata('name_full');
		$author_name = (!empty($author_full)) ? $author_full : $this->session->userdata('username');

		$tracker_review_model->last_editor = $author_name;
		$tracker_review_model->last_updated = date("F j, Y, g:i a T");

		$tracker_review_model->status 				= $update->status;
		$tracker_review_model->reviewer_email 		= $update->reviewer_email;

		$datagov_model_fields->tracker_status = json_encode($tracker_review_model);

		// remove blank fields from update
		foreach ($datagov_model_fields as $field => $data) {
			if(empty($data)) unset($datagov_model_fields->$field);
		}


    	$this->campaign->update_status($datagov_model_fields);

        $this->session->set_flashdata('outcome', 'success');
        $this->session->set_flashdata('status', 'Status updated');

		$this->load->helper('url');
        redirect('offices/detail/' . $datagov_model_fields->office_id . '/' . $datagov_model_fields->milestone);


    }

	public function status_update() {

		// Kick them out if they're not allowed here.
		if ($this->session->userdata('permissions') !== 'admin') {
			$this->load->helper('url');
            redirect('/');
            exit;
 		}


        $this->load->model('campaign_model', 'campaign');

		//$datajson 		= ($this->input->post('datajson', TRU E)) ? $this->input->post('datajson', TRUE) : $datajson;

        $update = (object) $this->input->post(NULL, TRUE);

		$datagov_model_fields = $this->campaign->datagov_model();
		$tracker_model_fields = $this->campaign->tracker_model();
        $tracker_review_model = $this->campaign->tracker_review_model();
   
        // Set author name with best data available
		$author_full = $this->session->userdata('name_full');
		$author_name = (!empty($author_full)) ? $author_full : $this->session->userdata('username');

		// Update tracker status metadata
		$tracker_review_model->last_editor = $author_name;
		$tracker_review_model->last_updated = date("F j, Y, g:i a T");

		$tracker_review_model->status 				= (!empty($update->status)) ? $update->status : null;
		$tracker_review_model->reviewer_email 		= (!empty($update->reviewer_email)) ? $update->reviewer_email : null;

		$datagov_model_fields->tracker_status = json_encode($tracker_review_model);



		// add fake field for general notes
		$tracker_model_fields->office_general = null;

		foreach ($tracker_model_fields as $field => $field_meta) {

			$field_name = "note_$field";

			if(!empty($update->$field_name)) {

				$note_data = array("note" => $update->$field_name, "date" =>  date("F j, Y, g:i a T"), "author" => $author_name);
				$note_data = array("current" => $note_data, "previous" => null);

				$note_data = json_encode($note_data);

				$note = array('note' => $note_data, 'field_name' => $field, 'office_id' => $update->office_id, 'milestone' => $update->milestone);
				$note = (object) $note;
				$this->campaign->update_note($note);
			}

			unset($update->$field_name);
		}

		$datagov_model_fields->office_id = $update->office_id;
		unset($update->office_id);

		$datagov_model_fields->milestone = $update->milestone;
		unset($update->milestone);		

		$datagov_model_fields->tracker_fields = json_encode($update);

		// remove blank fields from update
		foreach ($datagov_model_fields as $field => $data) {
			if(empty($data)) unset($datagov_model_fields->$field);
		}

        $this->campaign->update_status($datagov_model_fields);

        $this->session->set_flashdata('outcome', 'success');
        $this->session->set_flashdata('status', 'Status updated');


		$this->load->helper('url');
        redirect('offices/detail/' . $datagov_model_fields->office_id . '/' . $datagov_model_fields->milestone);

	}



	public function validate($datajson_url = null, $datajson = null, $headers = null, $schema = null, $output = 'browser') {
        $this->load->model('campaign_model', 'campaign');

		$datajson 		= ($this->input->post('datajson', TRUE)) ? $this->input->post('datajson', TRUE) : $datajson;
		$schema 		= ($this->input->get_post('schema', TRUE)) ? $this->input->get_post('schema', TRUE) : $schema;

		$datajson_url 	= ($this->input->get_post('datajson_url')) ? $this->input->get_post('datajson_url') : $datajson_url;
		$output_type 	= ($this->input->get_post('output')) ? $this->input->get_post('output') : $output;

		if ($this->input->get_post('qa')) {
			$qa = $this->input->get_post('qa');
		} else {
			$qa = false;
		}

		if ($qa == 'true') $qa = true;

		if(!empty($_FILES)) {

			$this->load->library('upload');

			if($this->do_upload('datajson_upload')) {

				$data = $this->upload->data();

				$datajson = file_get_contents($data['full_path']);
				unlink($data['full_path']);

			} else {

				$errors = array("Could not upload file (it may be larger than PHP or application allows)"); // for more details see $this->upload->display_errors()
				$validation = array(
								'valid_json' => false, 
								'valid' => false, 
								'fail' => $errors 
								);
			}
		}

		$return_source 	= ($output_type == 'browser') ? true : false;

		if($datajson OR $datajson_url) {
			$validation = $this->campaign->validate_datajson($datajson_url, $datajson, $headers, $schema, $return_source, $qa);
		}

		if(!empty($validation)) {


			if ($output_type == 'browser' && (!empty($validation['source']) || !empty($validation['fail']) )) {

				$validate_response = array(
											'validation' => $validation, 
											'schema'	=> $schema,
											'datajson_url' => $datajson_url
											);

				$this->load->view('validate_response', $validate_response);

			} else {

		     	header('Content-type: application/json');
		        print json_encode($validation);
		        exit;

			}

		} else {
			$this->load->view('validate');
        }

	}


	public function changeset($json_old = null, $datajson_new = null) {


		$json_old 		= ($this->input->get_post('json_old', TRUE)) ? $this->input->get_post('json_old', TRUE) : $json_old;


		if ($this->input->get_post('json_old_select', TRUE)) {
			$selection = $this->input->get_post('json_old_select', TRUE);
			if(!empty($selection)) {
				$json_old  = $selection;
			}
		}

		$datajson_new 	= ($this->input->get_post('datajson_new', TRUE)) ? $this->input->get_post('datajson_new', TRUE) : $datajson_new;

		//$datajson_new = 'http://www.treasury.gov/jsonfiles/data.json';




		if($json_old && $datajson_new) {

				$output = array();
				$output['json_old_request'] = $json_old;

				$json_old = urlencode($json_old);
				$json_old = 'http://catalog.data.gov/api/3/action/package_search?q=' . $json_old . "%20AND%20-type:harvest" . '&rows=200';

				// $json_old = 'http://test.dev/temp/ocsit-gsa-gov.json';

				$datajson_domain 				= parse_url($datajson_new);
     			$output['datajson_domain'] 		= $datajson_domain['host'];
				$output['json_old_url'] 		= $json_old;
				$output['datajson_new_url'] 	= $datajson_new;


				$json_old 	= curl_from_json($json_old, false);
				$datajson_new 	= curl_from_json($datajson_new, false);

				// $object_shim = new stdClass();
				// $object_shim->result 			= new stdClass();
				// $object_shim->result->count 	= count($json_old);
				// $object_shim->result->results 	= $json_old;
				// $json_old = $object_shim;

     			$changeset = 0;
     			$match_count = 0;


				$output['match_count'] 	= $match_count;
				$output['new_count'] 	= count($datajson_new);
				$output['old_count'] 	= $json_old->result->count;
				$output['changeset'] 	= array();

				if ($json_old->result->results) {
					foreach ($json_old->result->results as $old_json) {

						$matches = array();
						$old_json_url = 'http://catalog.data.gov/dataset/' . $old_json->name;

						foreach ($datajson_new as $datajson_entry) {

							// match on id
							if ($datajson_entry->identifier == $old_json->id) {
								$matches[] = 'Match on identifier: '. $datajson_entry->identifier;
							}

							// match on title
							if ($datajson_entry->title == $old_json->title) {
								$matches[] = 'Match on title: '. $datajson_entry->title;
							}							

							// match on URL
							$matched_urls = array();
							foreach ($old_json->resources as $resource) {

								if(empty($datajson_entry->distribution)) {
									$datajson_entry->distribution = array();
								}

								if (!empty($datajson_entry->accessURL)) {
									$distribution = new stdClass();
									$distribution->accessURL = $datajson_entry->accessURL;
									$datajson_entry->distribution[] = $distribution;
								}

								if(!empty($datajson_entry->distribution) && is_array($datajson_entry->distribution)) {

									foreach($datajson_entry->distribution as $distribution) {
										if(!empty($distribution->accessURL)) {
											if ($resource->url == $distribution->accessURL && empty($matched_urls[$distribution->accessURL])) {
												$matches[] = 'Match on URL for data.json id ' . $datajson_entry->identifier . ': '. $distribution->accessURL;
											}
											$matched_urls[$distribution->accessURL] = true;
										}

									}
									if(is_array($datajson_entry->distribution)) {
										reset($datajson_entry->distribution);
									}

								}

							}
							reset($old_json->resources);

						}
						if(is_array($datajson_new)) {
							reset($datajson_new);
						}

						$matchset = array();

	     				if(!empty($matches)) {
	     					$matchset['url'] = $old_json_url;
	     					$matchset['match'] = true;
	     					$matchset['matches'] = $matches;

	     					$output['changeset'][] = $matchset;
	     					$match_count++;
	     				} else {

	     					$matchset['url'] = $old_json_url;
	     					$matchset['match'] = false;

	     					$output['changeset'][] = $matchset;


	     				}

		     			$changeset++;
					}
				}

		}

		if(!empty($changeset)) {

			if(!empty($match_count)) {
				$output['match_count'] = $match_count;
			}

	     	$this->load->view('changeset_result', $output);

		} else {

			$data = array();
			$data['orgs'] = $this->assemble_org_structure();

			$this->load->view('changeset', $data);
        }

	}


	public function assemble_org_structure() {

		$url = 'https://idm.data.gov/fed_agency.json';
		$agency_list = curl_from_json($url, $array=true, $decode=true);

		$taxonomies = $agency_list['taxonomies'];

	    $return = array();
	    // This should be the ONLY loop that go through all taxonomies.
	    foreach ($taxonomies as $taxonomy) {
	        $taxonomy = $taxonomy['taxonomy'];

			//        ignore bad ones
	        if (strlen($taxonomy['unique id']) == 0) {
	            continue;
	        }

			//        ignore 3rd level ones
	        if ($taxonomy['unique id'] != $taxonomy['term']) {
	            continue;
	        }

			//        Make sure we got $return[$sector], ex. $return['Federal Organization']
	        if (!isset($return[$taxonomy['vocabulary']])) {
	            $return[$taxonomy['vocabulary']] = array();
	        }

	        if (strlen($taxonomy['Sub Agency']) != 0) {

				// This is sub-agency
				//  $return['Federal Organization']['National Archives and Records Administration']
	            if (!isset($return[$taxonomy['vocabulary']][$taxonomy['Federal Agency']])) {

					// Make sure we got $return[$sector][$unit]
	                $return[$taxonomy['vocabulary']][$taxonomy['Federal Agency']] = array(
	                    // use [ ] to indicate this is agency with subs. e.g [,sub_id]
	        	            'id' => "[," . $taxonomy['unique id'] . "]",
	                    'is_cfo' => $taxonomy['is_cfo'],
	                    'subs' => array(),
	                );
	            } else {
			//                Add sub id to existing agency entry, e.g. [id,sub_id1,sub_id2] or [,sub_id1,sub_id2]
	                $return[$taxonomy['vocabulary']][$taxonomy['Federal Agency']]['id']
	                    = "[" . trim($return[$taxonomy['vocabulary']][$taxonomy['Federal Agency']]['id'], "[]") . "," . $taxonomy['unique id'] . "]";
	            }

			//            Add term to parent's subs
	            $return[$taxonomy['vocabulary']][$taxonomy['Federal Agency']]['subs'][$taxonomy['Sub Agency']] = array(
	                'id' => $taxonomy['unique id'],
	                'is_cfo' => $taxonomy['is_cfo'],
	            );
	        } else {
			//        ELSE this is ROOT agency
	            if (!isset($return[$taxonomy['vocabulary']][$taxonomy['Federal Agency']])) {
			//                Has not been set by its subunits before
	                $return[$taxonomy['vocabulary']][$taxonomy['Federal Agency']] = array(
	                    'id' => $taxonomy['unique id'], // leave it without [ ] if no subs.
	                    'is_cfo' => $taxonomy['is_cfo'],
	                    'subs' => array(),
	                );
	            } else {
			//                Has been added by subunits before. so let us change it from [,sub_id1,sub_id2] to [id,sub_id1,sub_id2]
	                $return[$taxonomy['vocabulary']][$taxonomy['Federal Agency']]['id'] = "[" . $taxonomy['unique id'] . trim($return[$taxonomy['vocabulary']][$taxonomy['Federal Agency']]['id'], "[]") . "]";
	            }
	        }
	    }

	    $orgs = $return['Federal Organization'];

	    $cfo = array();
	    $non_cfo = array();

	    foreach($orgs as $key => $org) {

			$org['name'] = $key;

			$id = $org['id'];
			$id = str_replace('[,', '(', $id);
			$id = str_replace(',]', ')', $id);
			$id = str_replace(',', ' OR ', $id);
			$id = str_replace('[', '(', $id);
			$id = str_replace(']', ')', $id);

			$org['id'] = $id;

	    	if($org['is_cfo'] == 'Y') {
	    		$cfo[$key] = $org;
	    	} else {
	    		$non_cfo[$key] = $org;
	    	}



	    }

	    ksort($cfo);
	    ksort($non_cfo);

	    $return = array_merge($cfo, $non_cfo);


	    return $return;

	}


	/*
	Crawl each record in a datajson file and save current version + validation results
	*/
	public function version_datajson($office_id = null) {


        $this->load->model('campaign_model', 'campaign');


		// look up last crawl cycle for this office id
        if(!empty($office_id)) {

        	$current_crawl = $this->campaign->datajson_crawl();
        	$current_crawl->office_id = $office_id;

        	if($last_crawl = $this->campaign->get_datajson_crawl($current_crawl->office_id)) {

        		// make sure last crawl completed
        		if ($last_crawl->crawl_status == 'completed' && !empty($last_crawl->crawl_end)) {
        			$current_crawl->crawl_cycle = $last_crawl->crawl_cycle + 1;
        		} else {
        			// abort
        			$current_crawl->crawl_cycle = $last_crawl->crawl_cycle;
        			$current_crawl->crawl_status = 'aborted';

        			// save crawl status
        			$this->campaign->save_datajson_crawl($current_crawl);

        			return $current_crawl;

        		}

        	} else {
        		$last_crawl = false;
        		$current_crawl->crawl_cycle = 1;
        	}


    		$current_crawl->crawl_status = 'started';

			// save crawl status
			$this->campaign->save_datajson_crawl($current_crawl);




        	if ($current_crawl->crawl_status == 'started') {

    			// check to see if datajson status is good enough to parse

    			// ******** missing code here

    			foreach ($metadata_records as $metadata_record) {
    				$this->version_metadata_record($current_crawl);
    			}

    			// save crawl status
    			$this->campaign->save_datajson_crawl($current_crawl);

    			return $current_crawl;

        	}




        }


	}


}