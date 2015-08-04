DROP TABLE IF EXISTS `notes`;

CREATE TABLE `notes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `office_id` int(10) NOT NULL,
  `milestone` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `field_name` varchar(256) CHARACTER SET latin1 NOT NULL,
  `note` longtext CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;