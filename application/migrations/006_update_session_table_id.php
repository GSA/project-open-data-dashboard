<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_update_session_table_id extends CI_Migration
{

    public function up()
    {
        // See https://codeigniter.com/userguide3/installation/upgrade_312.html#step-2-update-your-ci-sessions-database-table
        $this->db->query("ALTER TABLE ci_sessions CHANGE id id varchar(128) NOT NULL;");
    }
}



