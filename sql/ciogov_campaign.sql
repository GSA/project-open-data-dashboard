DROP TABLE IF EXISTS `ciogov_campaign`;

CREATE TABLE `ciogov_campaign` (
  `status_id` int(5) NOT NULL AUTO_INCREMENT,
  `office_id` int(10) NOT NULL,
  `milestone` varchar(256) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `crawl_start` datetime DEFAULT NULL,
  `crawl_end` datetime DEFAULT NULL,
  `crawl_status` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `contact_name` text CHARACTER SET latin1,
  `contact_email` text CHARACTER SET latin1,
  `bureaudirectory_status` longtext CHARACTER SET latin1,
  `governanceboard_status` longtext CHARACTER SET latin1,
  `recommendation_status` longtext CHARACTER SET latin1,
  `tracker_fields` longtext CHARACTER SET latin1 NOT NULL,
  `tracker_status` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;