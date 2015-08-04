<?php

/**
 * Load GAO Recommendation data from csv spreadsheet and insert the
 * records into the gaoRecommendation table.
 *
 * $config['base_url']/recommendation
 *
 * http://www.codeigniter.com/user_guide/general/controllers.html#what-is-a-controller
 */
class Recommendation extends CI_Controller {

  static $csvFile = 'gao rec.csv';
  static $archive_dir = 'recommendation';
  public $log = "";
  static $currentMilestone = "2015-08-15";
  public $outputDir = "";

  function __construct() {
    parent::__construct();

    $this->load->model('Recommendation_model', 'recommendation', TRUE);
    $this->outputDir = $this->getArchiveDir();

    $data = $this->getUploadFile();

    $recommendations = $this->csv_to_array($data);

    $recommendations = $this->getOfficeFromAgencyName($recommendations);

    $groupedRecommendations = $this->groupByOfficeID($recommendations);

    $this->save_json_to_file($data, $groupedRecommendations);

    $this->save_campaign_records($groupedRecommendations);

    echo $this->log;
  }

  public function index()
  {
  }

   /**
    * Given a row from the csv in array format, convert to an object where each property
    * is a field in the table.
    *
    * @param <array> $mapping
    * @param <array> $records
    * @return <object>
    */
   public function csv_row_to_objects($mapping, $records) {

     foreach($records as $record) {
       $item = $this->recommendation->objectify($mapping, $record);
       $recommendations[] = $item;
     }

     return $recommendations;
   }

   /**
    * Read in the csv file and save as one json file for each agency
    *
    * http://www.codeigniter.com/user_guide/libraries/file_uploading.html?highlight=upload#CI_Upload::initialize
    *
    * @param <array> $data
    */
   public function csv_to_array($data)
   {
     $this->load->helper('csv');
     $this->load->helper('api');
     ini_set("auto_detect_line_endings", true);

     $column_headers = $this->getColumnHeaders($data);

     // Provide mapping between csv headings and POD schema
     $json_schema = $this->recommendation->datajson_schema();
     $datajson_model = $this->recommendation->schema_to_model($json_schema->properties);

     $mapping = $this->recommendation->get_mapping($json_schema->properties);
     $importer = new CsvImporter($data['full_path'], $parse_header = true, $delimiter = ",");
     $csv = $importer->get();

     $recommendations = $this->csv_row_to_objects($mapping, $csv);

     return $recommendations;
    }

   /**
    * Group the recommendation records by the office id
    * e.g.,
    * Array([49229] => Array(...
    *
    * Exclude any records that don't have an office_id
    *
    * @param <array> $list
    * @return <array>
    */
   public function groupByOfficeID($list)
   {
      $result = array();
      $field = "office_id";

      foreach ($list as $elt) {
        if (is_array($elt)) {
    	  foreach ($elt as $e) {
    		$result[$e->$field][] = $e;
    	  }
        } else {
    	  $result[$elt->$field][] = $elt;
        }
       }
      return $result;
   }

   /**
    * Retrieve the file to be uploaded as if it were given in
    * a browse input form.
    *
    * TO DO: contruct an upload form to select file to be uploaded
    */
   function getUploadFile()
   {
     $this->load->library('upload');
     $upload_path = $this->upload->upload_path;

     $data = array(
         'file_name'     => static::$csvFile,
         'file_type'     => "csv",
         'file_path'     => $upload_path,
         'full_path'     => $upload_path . static::$csvFile,
         'file_ext'      => ".csv"
     );

     return $data;
   }

   /**
    * Get the column headers from the CSV File
    *
    * @param <array> $data
    * @return <array>
    */
   function getColumnHeaders($data)
   {
     $this->log("Reading csv file ". $data['full_path']);

     $csv_handle = fopen($data['full_path'], 'r');

     $headings = fgetcsv($csv_handle);

     // Sanitize input
     $headings = $this->security->xss_clean($headings);

     $column_headers = $headings;

     return $column_headers;
   }

   /**
    * Given a recommendation record, get the office id and url
    * from the office table.
    *
    * @param <array> $recommendations
    */
   public function getOfficeFromAgencyName($recommendations)
   {
     $offices = $this->getOffices();

     $out = array();
     foreach($recommendations as $recommendation) {
        $aliases = $this->getAgencyAliases($recommendation->agencyName);
        foreach($offices as $office) {
          if(array_search($office->name, $aliases) !== FALSE) {
            $recommendation->office_id = $office->id;
            $recommendation->url = preg_replace("/\/$/", "", $office->url);
            $recommendation->path_to_json = $this->getPathToJSON($office->id);

            $out[] = $recommendation;
            break;
          }
        }
        if(!$recommendation->office_id) {
          $this->log("ERROR: No office_id found for '{$recommendation->agencyName}' in office table");
        }
       }

     return $out;
   }

   /**
    * Return the agency name with the colon and following removed. Also supply
    * possible aliases.
    *
    * @param <string> $inName
    * @returns <array>
    */
   public function getAgencyAliases($inName) {

     $aliases = array();
     $aliases[] = preg_replace("/(.*?):(.*)/", "$1", $inName);

     if(preg_match("/U.S./", $inName)) {
       $aliases[] = preg_replace("/U.S./", "United States", $inName);
     }
     if(preg_match("/United States/", $inName)) {
       $aliases[] = preg_replace("/United States/", "U.S.", $inName);
     }

     return $aliases;
   }

   /**
    * Retrieve the offices from the db.
    *
    * @return <array>
    */
   public function getOffices()
   {
     $this->load->helper('api');
     $this->load->model('campaign_model', 'campaign');
     $this->db->select('id, name, url');
     $query = $this->db->get('offices');

     $offices = array();
     if ($query->num_rows() > 0) {
       $offices = $query->result();
     }

     return $offices;
   }

   /**
    * Save the json to a file
    *
    * @param <array> $data
    * @param <array> $groupedRecommendations
    */
   function save_json_to_file($data, $groupedRecommendations)
   {
     $this->log("Saving GAO Recommendations to ". $this->outputDir);
     foreach($groupedRecommendations as $office_id => $agencyRecommendations) {
       if(!is_array($agencyRecommendations)) {
         $agencyRecommendations = array($agencyRecommendations);
       }
       $fp = fopen($agencyRecommendations[0]->path_to_json, 'w');
       $content = '{"recommendations" : '. json_encode($agencyRecommendations) . '}';
       fputs($fp, $content);
       fclose($fp);
     }
   }

   /**
    * Store the json files in recommendation/today date/office id.json
    * e.g., recommendation/2015-07-01/92601.json
    */
   function getArchiveDir()
   {
     $dir = $this->config->item('archive_dir') . "/". static::$archive_dir;
     $this->checkDir($dir);
     $dir = $dir . "/". date('Y-m-d');
     $this->checkDir($dir);

     return $dir;
   }

   /**
    * Get the path to the json file.
    *
    * @param <int> $office_id
    * @returns <string>
    */
   public function getPathToJSON($office_id)
   {
     return $this->outputDir . "/". $office_id . ".json";
   }

   /**
    * If directory does not exist, create it.
    *
    * @param <string> $dir
    */
   public function checkDir($dir)
   {
     if (!file_exists($dir)) {
       mkdir($dir, 0775);
     }
   }

   /**
    * collect output
    *
    * @param <string> $msg
    */
   function log ($msg)
   {
     $this->log .= $msg . "<br>";
   }

   /**
    * Save a record to the ciogov_campaign table indicating that
    * the GAO Recommendations have been read and stored.
    *
    * @param <array> $groupedRecommendations
    */
   public function save_campaign_records($groupedRecommendations)
   {
     $this->log("Saving campaign records for GAO Recommendations upload");

     $this->load->model('campaign_model', 'campaign');

     foreach($groupedRecommendations as $office_id => $agencyRecommendations) {
       if(!is_array($agencyRecommendations)) {
         $agencyRecommendations = array($agencyRecommendations);
       }
        $campaign = $this->setOneCampaignRecord($agencyRecommendations[0]);
        $this->db->insert('ciogov_campaign', $campaign);
       }
   }

   /**
    * Set the values into one model instance for a campaign insert.
    *
    * @param <int> $office_id
    * @return <object>
    */
   public function setOneCampaignRecord($recommendation)
   {
     $now = date('Y/m/d H:i:s');

     $status['url'] = $recommendation->url;
     $status['expected_url'] = $recommendation->path_to_json;
     $status['schema_errors'] = 0;

     $campaign = $this->campaign->ciogov_model();
     $campaign->office_id = $recommendation->office_id;
     $campaign->milestone = static::$currentMilestone;
     $campaign->crawl_start = $now;
     $campaign->crawl_end = $now;
     $campaign->recommendation_status = json_encode($status);

     return $campaign;
   }

}
