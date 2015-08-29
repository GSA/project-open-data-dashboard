-- for IEEUIT-1897 started but on hold - not in db_deployment.csv

use fitara_dashboard;

DROP TABLE IF EXISTS `milestone`;

CREATE TABLE `milestone` (
  `milestoneID` int(11) NOT NULL AUTO_INCREMENT,
  `milestoneEndDate` date NOT NULL,
  `milestoneTitle` varchar(45) NOT NULL,
  PRIMARY KEY (`milestoneID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;