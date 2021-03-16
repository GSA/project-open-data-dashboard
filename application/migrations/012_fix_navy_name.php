<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_fix_nasa_url extends CI_Migration
{

    public function up()
    {
        // Update Navy's name
        $this->db->query('UPDATE offices SET name = "U.S. Navy" WHERE id = "49561"');
    }

    public function down()
    {
      $this->db->query('UPDATE offices SET name = "Navy" WHERE id = "49561"');
    }
}