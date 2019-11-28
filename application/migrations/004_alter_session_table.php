<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_alter_session_table extends CI_Migration
{

    public function up()
    {
        // See Step 6 in https://codeigniter.com/userguide3/installation/upgrade_300.html
        $this->db->query("ALTER TABLE ci_sessions RENAME COLUMN session_id TO id;");
        $this->db->query("ALTER TABLE ci_sessions DROP COLUMN user_agent CASCADE;");
        $this->db->query("ALTER TABLE ci_sessions RENAME COLUMN user_data TO data;");
        $this->db->query("ALTER TABLE ci_sessions RENAME COLUMN last_activity TO timestamp;");

        // See https://codeigniter.com/userguide3/libraries/sessions.html#database-driver
        // No need to adjust the primary key as described there; sess_match_ip is FALSE.
        $this->db->query("CREATE INDEX ci_sessions_timestamp ON ci_sessions (`timestamp`);");
    }
}



