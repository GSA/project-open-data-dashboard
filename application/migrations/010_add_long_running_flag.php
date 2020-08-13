<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_long_running_flag extends CI_Migration
{
    // Add a column to indicate whether the office's crawl takes longer than 23 hours
    public function up()
    {
        // add our column if it doesn't exist
        $this->CI->db->query("CALL add_column_if_not_exists('offices', 'long_running', 'varchar(256)')");
        // test if it exists
        $query = $this->CI->db->query("SELECT column_exists('offices', 'long_running')");
        $row = $query->first_row('array');
        $tableAndColumnExist = (bool) array_shift($row);
        // add values if it exists
        if ($tableAndColumnExist) {
            // set out default
            $this->db->query("UPDATE offices SET long_running = 'FALSE' ");
            // these are the offices that have long running crawls
            $this->db->query("UPDATE offices SET long_running = 'TRUE' WHERE name IN (
                'Department of Commerce',
                'Depart of Health and Human Services',
                'Department of the Interior',
                'Department of Transportation',
                'Environmental Protection Agency',
                'Federal Energy Regulatory Commission',
                'National Aeronautics and Space Administration',
                'U.S. Agency for International Development')
                ");
        }
    }

    public function down()
    {
        // Drops column to indicate whether the office's crawl takes longer than 23 hours
        $this->CI->db->query("CALL drop_column_if_exists('offices', 'long_running')")
    }
}
