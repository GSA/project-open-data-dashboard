<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_omb_monitored_flag extends CI_Migration
{

    public function up()
    {
        // Add a column to indicate whether the office's crawl takes longer than 23 hours
        $this->db->query("ALTER TABLE `offices` ADD `long_running` varchar(256) CHARACTER SET latin1 DEFAULT 'FALSE' AFTER `omb_monitored`;");

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
