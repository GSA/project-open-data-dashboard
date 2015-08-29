USE `fitara_dashboard`;
DROP PROCEDURE IF EXISTS `fitara_dashboard`.`uspAddMilestone`;
delimiter //
CREATE PROCEDURE `fitara_dashboard`.`uspAddMilestone`(toMilestone date)
    #COMMENT 'Copy records from prior milestone to given milestone - used to simulate a future milestone for testing'
begin

DECLARE fromMilestone date;

set fromMilestone = (select `milestone` from `ciogov_campaign` `c` where `c`.`milestone` < toMilestone 
order by `milestone` desc limit 1);

drop temporary table if exists `tmpCampaign`;
create temporary table `tmpCampaign` as select * from `ciogov_campaign` `c` where `c`.`milestone` = fromMilestone
and `crawl_status` = 'current' group by `office_id` order by `crawl_start`;

update `tmpCampaign` set 
`status_id` = 0,
`milestone` = toMilestone,
`crawl_start` = CURDATE(),
`crawl_end` = CURDATE(),
`crawl_status` = 'current'
where 1=1;


insert into `ciogov_campaign` select * from `tmpCampaign` where 1=1;

drop temporary table if exists `tmpCampaign`;

end
//
delimiter ;