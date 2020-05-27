<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_sec extends CI_Migration
{

    public function up()
    {
        // Add the SEC to the set of monitored agencies
        $this->db->query("UPDATE offices SET omb_monitored = 'TRUE' WHERE name IN ('Securities and Exchange Commission')");
    }

    public function down()
    {
        // Add the SEC to the set of monitored agencies
        $this->db->query("UPDATE offices SET omb_monitored = 'FALSE' WHERE name IN ('Securities and Exchange Commission')");
    }

}




