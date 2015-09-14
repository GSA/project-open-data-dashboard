<?php

/**
 * Model for milestone_settings
 *
 * Allows for configurable or custom sections, subsections, and
 * metrics for each milestone.
 */
class milestone_setting_model extends CI_Model {

  public $table = 'milestone_setting';

  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Retrieve the tracker model from milestone_setting
   * and return those that are flagged for the dashboard.
   * These are used for the dashboard office table subheadings.
   *
   * @param <date> $milestone
   * @return <array>
   */
  public function get_subsections($milestone)
  {
     $milestone_number = $this->get_milestone_number($milestone);

     $subsections = $this->get_setting($milestone_number, 'tracker_model');

     if(!$subsections) {
       $this->copy_settings_to_milestone($milestone_number);
       $subsections = $this->get_setting($milestone_number, 'tracker_model');
     }

     $subsection_breakdown = array();
     foreach ($subsections as $key => $item) {
       $section = substr($key, 0, 2);
       if (isset($item->dashboard) && $item->dashboard == true) {
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

  /**
   * Get the number associated with the given milestone date
   *
   * @param <date> $milestone
   * @return number
   */
  public function get_milestone_number($milestone)
  {
    $this->load->model('campaign_model', 'campaign');

    $milestone_number = 1;
    $milestones = $this->campaign->milestones_model();
    if(array_key_exists($milestone, $milestones)) {
      $number = $milestones[$milestone];
      $milestone_number = preg_replace("/Milestone\s+/", '', $number);
    }

    return $milestone_number;
  }

  /**
   * Retrieve the section breakdown for this milestone.
   * Called from campaign_model function tracker_sections_model
   *
   * @param <date> $milestone
   * @return <array>
   */
  public function get_sections($milestone)
  {
    $milestone_number = $this->get_milestone_number($milestone);

    $sections = $this->get_setting($milestone_number, 'sections');
    if(!$sections) {
      $this->copy_settings_to_milestone($milestone_number);
      $subsections = $this->get_setting($milestone_number, 'sections');
    }

    $section_breakdown = array();
    foreach($sections as $section) {
      $section_breakdown[$section->code] = $section->label;
    }

    return $section_breakdown;
  }

  /**
   * Retrieve the setting for the given milestone number and field name
   *
   * @param <int> $milestone_number
   * @param <string> $field_name
   * @return <array>
   */
  public function get_setting($milestone_number, $field_name)
  {
    $settings = array();
    $setting = '';
    $this->db->select('setting');
    $this->db->where('milestone_number', $milestone_number);
    $this->db->where('field_name', $field_name);
    $query = $this->db->get($this->table);

    if ($query->num_rows() > 0) {
      $settings = $query->result();
      $setting = json_decode($settings[0]->setting);
    }

    return $setting;
  }

  /**
   * If no section and tracker model settings are defined for this milestone,
   * copy the settings from the prior milestone.
   *
   * @param <int> $milestone_number
   */
  public function copy_settings_to_milestone($milestone_number)
  {
    $stored_procedure = "CALL uspCopyMilestoneSetting(?)";
    $result = $this->db->query($stored_procedure, array('toMilestoneNumber' => $milestone_number));
  }

}