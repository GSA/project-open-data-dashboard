<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_alter_session_table extends CI_Migration
{

    public function up()
    {
        // See Step 6 in https://codeigniter.com/userguide3/installation/upgrade_300.html
        $this->db->query("ALTER TABLE ci_sessions CHANGE session_id id varchar(40) DEFAULT '0' NOT NULL;");
        $this->db->query("ALTER TABLE ci_sessions DROP COLUMN user_agent CASCADE;");
        $this->db->query("ALTER TABLE ci_sessions CHANGE user_data data text NOT NULL;");
        $this->db->query("ALTER TABLE ci_sessions CHANGE last_activity timestamp int(10) unsigned DEFAULT 0 NOT NULL;");

        // See https://codeigniter.com/userguide3/libraries/sessions.html#database-driver
        // No need to adjust the primary key as described there; sess_match_ip is FALSE.
    }
}



