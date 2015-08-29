-- for IEEUIT-1897 started but on hold - not in db_deployment.csv

use fitara_dashboard;

DROP TABLE IF EXISTS recommendation`;

CREATE TABLE `recommendation` (
  `recommendationID` int(11) NOT NULL AUTO_INCREMENT,
  `milestoneID` int(11) NOT NULL,
  `baselineCount` int(10) unsigned DEFAULT NULL,
  `closedCount` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`recommendationID`),
  KEY `fk_recommendation_milestone_idx` (`milestoneID`),
  CONSTRAINT `fk_recommendation_milestone` FOREIGN KEY (`milestoneID`) REFERENCES `milestone` (`milestoneID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
