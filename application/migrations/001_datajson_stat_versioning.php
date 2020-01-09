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

		/*
		Populate offices table

		The agency hierarchy in the offices table was originally populated from
		the USA.gov Federal Agency Directory API. That API is no longer
		available, so we've captured the data in this CSV file.

		Note that the IDs, which originally came from the API, are exposed in
		URLs that people may have bookmarked; use caution if you change them!
 		*/
		if (($handle = fopen(APPPATH."migrations/offices.csv", "r")) === FALSE) {

			echo "Couldn't open the CSV file";

		} else {

			$this->db->query('BEGIN;');

			// Read in the header row
			if (($keys = fgetcsv($handle, 1000, ",")) === FALSE) {

				echo "Couldn't find a header row";

			} else {

				// For each remaining row...
				while (($values = fgetcsv($handle, 1000, ",")) !== FALSE) {
					// Make an INSERT query and execute it
					$record = array_combine($keys, $values);
					$query = $this->db->insert_string('offices', $record);
					$result = $this->db->simple_query($query);
					if(!$result) {
						$this->db->simple_query('ROLLBACK;');
					}
				}

			}

			$this->db->query('COMMIT;');
			fclose($handle);

		}

	}

}



