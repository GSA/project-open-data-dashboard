<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_fix_nasa_url extends CI_Migration
{

    public function up()
    {
        // Update NASA's URL for finding their data.json; see mail "Open Data scraper questions (labs.data.gov)" on 3/3/2020
        $this->db->query('UPDATE offices SET url = "https://data.nasa.gov" WHERE id = "49476"');
    }
}



