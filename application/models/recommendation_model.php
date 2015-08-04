<?php

/**
 * Model the gaoRecommendation table.
 */
class Recommendation_model extends CI_Model {

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

  public function get_mapping($schema) {

    $mapping = array();

    foreach ($schema as $key => $value) {

      $mapping[$key] = $value->title;
    }
    return $mapping;
  }

}