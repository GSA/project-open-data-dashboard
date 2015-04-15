<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Datajson_stat_versioning extends CI_Migration {

        public function up() {

            $this->db->query("ALTER TABLE `datagov_campaign` ADD `crawl_start` DATETIME NULL AFTER `milestone`;");
            $this->db->query("ALTER TABLE `datagov_campaign` ADD `crawl_end` DATETIME NULL AFTER `crawl_start`;");
            $this->db->query("ALTER TABLE `datagov_campaign` ADD `crawl_status` VARCHAR(256) NULL AFTER `crawl_end`;");
            $this->db->query("ALTER TABLE `datagov_campaign` DROP PRIMARY KEY; ");
            $this->db->query("ALTER TABLE `datagov_campaign` ADD `status_id` INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;");

        }

}



