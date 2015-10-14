<?php

/**
 * Model the gaoRecommendation table.
 */
class Recommendation_model extends CI_Model {

  static $products_url = "http://www.gao.gov/products/";
  public $emptyMessage = "No GAO Recommendations provided.";

  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Given an array where the order matches the fieldNames,
   * create an object with propertie and values.
   *
   * @param <array> $fieldNames
   * @param <array> $record
   * @return stdClass
   */
  public function objectify($fieldNames, $record) {

    $obj = new stdClass();
    foreach($fieldNames as $field => $title) {
      if(isset($record[$title])) {
        $value = trim($record[$title]);
        $value = make_utf8($value);
        $value = (!is_bool($value) && empty($value)) ? null : $value;

        $obj->$field = $value;
      }
    }

    return $obj;
  }

  /**
   * Get the Recommendation schema definition
   *
   * @param string $version
   * @return <array>
   */
  public function datajson_schema($version = '') {

    $version_path = (!empty($version)) ? $version . '/' : '';

    $path = './schema/' . $version_path . '/recommendation/single_entry.json';

    // Get the schema and data as objects
    $retriever = new JsonSchema\Uri\UriRetriever;
    $schema = $retriever->retrieve('file://' . realpath($path));

    return $schema;
  }

  /**
   * Convert the schema into a model
   *
   * @param <array> $schema
   * @return stdClass
   */
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
   * Get an array from the schema where the field is the key and the
   * title is the value.
   *
   * @param <array> $schema
   * @return <array>
   */
  public function get_mapping($schema) {

    $mapping = array();

    foreach ($schema as $key => $value) {

      $mapping[$key] = $value->title;
    }
    return $mapping;
  }

  /**
   * Retrieve the GAO Recommendations from the json file
   * and format as a table for recommendation_detail view.
   *
   * Steps:
   *
   * 1. Open office record with given id and get the expected
   * url for the json from the recommendation_status field
   *
   * 2. Open the json file and parse it so that we have an
   * array of recommendation objects
   *
   * 3. Create a table from the array.
   *
   * TO DO: Move the inline css to main.css. Need to revise the styles so that
   * they don't override the bare-bone styles taken from bootstrap.min.css that
   * are being applied to the other tables on the page drawn by office_details.php view.
   *
   * @param <object> $office
   * @return <html>
   */
  public function get_office_detail($office)
  {
     $json_schema = $this->datajson_schema();
     $properties = $this->getOfficeDetailProperties($json_schema->properties);

     $json_file = $this->get_json_path($office->recommendation_status);
     $recommendations = $this->json_to_array($json_file);

     $html = "";
     if(count($recommendations)) {
       $html = '<table id="recommendations" class="table" style="border-collapse: collapse">';
       $html .= $this->buildTableHeader($properties);
       $html .= $this->buildTableBody($recommendations, $properties);
       $html .= "</table>\n";
     }
     else {
       $html = "<p>". $this->emptyMessage . "</p>";
     }

     return $html;
  }

  /**
   * Construct the header for the GAO Recommendations table
   * for one office.
   *
   * @param <array> $properties
   * @return string
   */
  public function buildTableHeader($properties)
  {
    $html = '<thead><tr class="table-header" style="border: 1px solid #000000; background-color: #e6e6e6">';
    foreach($properties as $field => $property) {
         $html .= '<th scope="col" style="border: 1px solid #000000">' . $property->title . "</th>\n";
    }

    $html .= "</tr></thead>\n";

    return $html;
  }

  /**
   * Filter out any properties that are not relevant to
   * office details, as specified in the schema.
   *
   * @param <array> $properties
   * @return <array>
   */
  public function getOfficeDetailProperties($properties)
  {
    $filtered = array();
    foreach($properties as $field => $property) {
      if(property_exists($property, "office_detail") && $property->office_detail == true) {
        $filtered[$field] = $property;
      }
    }

    return $filtered;
  }

  /**
   * Construct the body of the table with the recommendations retrieved
   * from the json file for one office.
   *
   * @param <array> $recommendations
   * @param <array> $properties
   * @return <html>
   */
  public function buildTableBody($recommendations, $properties)
  {
     $html = "<tbody>\n";
     foreach($recommendations as $recommendation) {
       $html .= '<tr style="border: 1px solid #000000; border-top: 1px solid #000000">';
       foreach($properties as $field => $property) {
         $value = $recommendation->$field;
         if($field == "productNumber") {
           $value = '<a target="_blank" href="'. static::$products_url . $value . '">' . $value . "</a>";
         }
          $html .= '<td style="border: 1px solid #000000">' . $value . "</td>";
       }
       $html .= "</tr>\n";
     }

     $html .= "</tbody>\n";
     return $html;
  }

  /**
   * Get the path to the json file.
   *
   * @param <object> $status
   * @return <string>
   */
  public function get_json_path($status)
  {
      $url = null;
      if(empty($status)) {
        return $url;
      }
      $status = json_decode($status);
      if(!is_object($status) || !property_exists($status, "expected_url")) {
        return $url;
      }

      $url = $status->expected_url;
      if(!file_exists($url)) {
        $this->emptyMessage = "Invalid path for GAO Recommendation details json file ". $url;
        $url = null;
      }

      return $url;
  }

  /**
   * Get the contents of the json file and return an
   * array of the recommendation objects.
   *
   * @param <string> $json_file
   * @return <array>
   */
  public function json_to_array($json_file)
  {
    $recommendations = array();

    if(!$json_file) {
      return $recommendations;
    }

    $fp = fopen($json_file, 'r');

    if(!$fp) {
      $this->emptyMessage = "Unable to open GAO Recommendation json file ". $json_file;
    }

    $json = file_get_contents($json_file);

    if($json) {
      $obj = json_decode($json);
    }

    if(property_exists($obj, "recommendations") && isset($obj->recommendations)) {
      $recommendations = $obj->recommendations;
    }
    else {
      $this->emptyMessage = "Unexpected format given in GAO Recommendation json file ". $json_file;
    }

    if(!count($recommendations)) {
      $this->emptyMessage = "No GAO Recommendations provided in json file ". $json_file;
    }

    return $recommendations;
  }

  /**
   * Get the tracker fields from the office record tracker_fields column.
   *
   * @param <object> $office
   * @returns <object>
   */
  public function get_office_detail_status($office)
  {
    $status = new stdClass();
    $status->empty = false;
    $json = $office->recommendation_status;
    if(!empty($json)) {
      $status = json_decode($json);
    }
    else {
      $status->emptyMessage = $this->emptyMessage;
      $status->empty = true;
    }

    if(property_exists($office, "crawl_end")) {
      $date = new DateTime($office->crawl_end);
      $status->last_crawl = $date->format("l, d-M-Y H:i:s T");
    }

    return $status;
  }

}