<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_fcc_to_omb_monitored_agencies extends CI_Migration
{
    public function up()
    {
        // OMB also wants to monitor the FCC, already in the DB
        $this->db->query("UPDATE offices SET omb_monitored = 'TRUE' WHERE name IN ('Federal Communications Commission')");

    }
}
