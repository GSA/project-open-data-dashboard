<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Datajson_stat_versioning extends CI_Migration {

	public function up() {

		$this->db->query("
		CREATE TABLE IF NOT EXISTS `datagov_campaign` (
			`office_id` int(10) NOT NULL,
			`milestone` varchar(256) CHARACTER SET latin1 NOT NULL DEFAULT '',
			`contact_name` text CHARACTER SET latin1,
			`contact_email` text CHARACTER SET latin1,
			`datajson_status` longtext CHARACTER SET latin1,
			`datapage_status` longtext CHARACTER SET latin1,
			`digitalstrategy_status` longtext CHARACTER SET latin1,
			`tracker_fields` longtext CHARACTER SET latin1 NOT NULL,
			PRIMARY KEY (`office_id`,`milestone`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS `notes` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`office_id` int(10) NOT NULL,
			`milestone` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
			`field_name` varchar(256) CHARACTER SET latin1 NOT NULL,
			`note` longtext CHARACTER SET latin1 NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1021 ;
			");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS `offices` (
			`id` int(10) NOT NULL,
			`name` varchar(256) CHARACTER SET latin1 NOT NULL,
			`abbreviation` text CHARACTER SET latin1,
			`url` text CHARACTER SET latin1,
			`notes` text CHARACTER SET latin1,
			`parent_office_id` int(10) DEFAULT NULL,
			`no_parent` varchar(256) CHARACTER SET latin1 NOT NULL,
			`reporting_authority_type` varchar(256) CHARACTER SET latin1 NOT NULL,
			`cfo_act_agency` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS `users_auth` (
			`user_id` int(8) NOT NULL,
			`username` varchar(255) CHARACTER SET latin1 NOT NULL,
			`username_url` varchar(255) CHARACTER SET latin1 NOT NULL,
			`name_full` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
			`provider_url` text CHARACTER SET latin1 NOT NULL,
			`provider_user_id` int(12) NOT NULL,
			`token` text CHARACTER SET latin1 NOT NULL,
			`provider` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT 'github',
			`permissions` varchar(256) CHARACTER SET latin1 DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");

		$this->db->query("ALTER TABLE `datagov_campaign` ADD `crawl_start` DATETIME NULL AFTER `milestone`;");
		$this->db->query("ALTER TABLE `datagov_campaign` ADD `crawl_end` DATETIME NULL AFTER `crawl_start`;");
		$this->db->query("ALTER TABLE `datagov_campaign` ADD `crawl_status` VARCHAR(256) NULL AFTER `crawl_end`;");
		$this->db->query("ALTER TABLE `datagov_campaign` DROP PRIMARY KEY; ");
		$this->db->query("ALTER TABLE `datagov_campaign` ADD `status_id` INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;");

	}

}



