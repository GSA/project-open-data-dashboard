<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Office_ids extends CI_Migration {

        public function up() {
            $this->db->query("ALTER TABLE `offices` ADD `url_slug` VARCHAR(256) NULL AFTER `id`;");
        }

}



