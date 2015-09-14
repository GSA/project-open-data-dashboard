USE `fitara_dashboard`;

DROP PROCEDURE IF EXISTS `fitara_dashboard`.`uspCopyMilestoneSetting`;
delimiter //
CREATE PROCEDURE `fitara_dashboard`.`uspCopyMilestoneSetting`(toMilestoneNumber tinyint(4))
    #COMMENT 'Copy settings for sections and tracker_model from prior milestone_setting to given milestone'
begin

DECLARE fromMilestoneNumber tinyint(4);

set fromMilestoneNumber = (select max(`milestone_number`) from `milestone_setting` 
where `milestone_number` < `toMilestoneNumber`);

insert into `milestone_setting` 
(`milestone_number`,
`field_name`,
`setting`)

select 
toMilestoneNumber as `milestone_number`,
`field_name`,
`setting`
 from `milestone_setting` `m` where `milestone_number` = fromMilestoneNumber and
 toMilestoneNumber not in (select `milestone_number` from `milestone_setting` `m2` where
 `m`.`field_name` = `m2`.`field_name`);

end
//
delimiter ;

-- example call:
-- CALL `uspCopyMilestoneSetting`(4);