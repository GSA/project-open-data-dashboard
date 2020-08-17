<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_long_running_flag extends CI_Migration
{
    // Add a column to indicate whether the office's crawl takes longer than 23 hours
    public function up()
    {
        $this->db->query("CALL add_column_if_not_exists('offices', 'long_running', 'varchar(256) CHARACTER SET latin1 DEFAULT \'FALSE\' AFTER `cfo_act_agency`')");
        // these are the offices that have long running crawls
        $this->db->query("UPDATE offices SET long_running = 'TRUE' WHERE name IN (
            'Department of Commerce',
            'Department of Health and Human Services',
            'Department of the Interior',
            'Department of Transportation',
            'Environmental Protection Agency',
            'Federal Energy Regulatory Commission',
            'National Aeronautics and Space Administration',
            'U.S. Agency for International Development')
            ");
    }

    public function down()
    {
      $this->db->query("CALL drop_column_if_exists('offices', 'long_running')");
    }
}
