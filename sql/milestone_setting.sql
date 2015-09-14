use `fitara_dashboard`;

DROP TABLE IF EXISTS `milestone_setting`;

CREATE TABLE `milestone_setting` (
  `milestone_setting_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `milestone_number` tinyint(4) NOT NULL,
  `field_name` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `setting` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`milestone_setting_id`),
  KEY `fitara_dashboard_milestone_setting_milestone_number_idx` (`milestone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


truncate table `milestone_setting`;

-- ----------------------------------------------------------------------
-- 
-- Sections
-- 
-- ----------------------------------------------------------------------

insert into `milestone_setting` (`milestone_number`, `field_name`, `setting`) 
values
('1', 'sections', '[
{"code": "cb", "label": "Common Baseline Submission Status"},
{"code": "pa", "label": "Published Artifacts Submission Status"}
]'),
('2', 'sections', '[
{"code": "cb", "label": "Common Baseline Submission Status"},
{"code": "pa", "label": "Published Artifacts Submission Status"}
]'),
('3', 'sections', '[
{"code": "cb", "label": "Common Baseline: OMB Approval"},
{"code": "pa", "label": "Published Artifacts Submission Status"},
{"code": "ci", "label": "Community Involvement"}
]'),
('4', 'sections', '[
{"code": "cb", "label": "Common Baseline Submission Status"},
{"code": "pa", "label": "Published Artifacts Submission Status"},
{"code": "ci", "label": "Community Involvement"}
]'),
('5', 'sections', '[
{"code": "cb", "label": "Common Baseline Submission Status"},
{"code": "pa", "label": "Published Artifacts Submission Status"},
{"code": "ci", "label": "Community Involvement"}
]'),
('6', 'sections', '[
{"code": "cb", "label": "Common Baseline Submission Status"},
{"code": "pa", "label": "Published Artifacts Submission Status"},
{"code": "ci", "label": "Community Involvement"}
]');


-- ----------------------------------------------------------------------
-- 
-- Tracker Model
-- IEEUIT-1913 - completed settings for subsection breakdown but
-- settings that are nondashboard that would be needed to replace
-- tracker_model for IEEUIT-1914 are incomplete.
-- 
-- ----------------------------------------------------------------------
insert into `milestone_setting` (`milestone_number`, `field_name`, `setting`) 
values
('1', 'tracker_model', '{
"cb_self_assessment":
	{"dashboard":"true","label":"Self-Assessment", "type": "select", "due_date": "2015-08-15"}, 
"cb_implementation_plan": 
	{"dashboard": "true", "label":"Implementation Plan", "due_date":"2015-08-15"},
"cb_cio_assignment_plan":
	{"dashboard": "true", "label":"CIO Assignment Plan (If Applicable)", "due_date":"2015-08-15"},
"pa_bureau_it_leadership":
	{"dashboard": "true", "label":"Bureau IT Leadership", "due_date":"2015-08-15"},
"pa_cio_governance_board":
	{"dashboard": "true", "label":"CIO Governance Board List", "due_date":"2015-08-31"},
"pa_it_policy_archive":
	{"dashboard": "true", "label":"IT Policy Archive", "due_date":"2015-08-31"}
}'),
('2', 'tracker_model', '{
"cb_self_assessment":
	{"dashboard":"true","label":"Self-Assessment", "type": "select", "due_date": "2015-08-15"}, 
"cb_implementation_plan": 
	{"dashboard": "true", "label":"Implementation Plan", "due_date":"2015-08-15"},
"cb_cio_assignment_plan":
	{"dashboard": "true", "label":"CIO Assignment Plan (If Applicable)", "due_date":"2015-08-15"},
"pa_bureau_it_leadership":
	{"dashboard": "true", "label":"Bureau IT Leadership", "due_date":"2015-08-15"},
"pa_cio_governance_board":
	{"dashboard": "true", "label":"CIO Governance Board List", "due_date":"2015-08-31"},
"pa_it_policy_archive":
	{"dashboard": "true", "label":"IT Policy Archive", "due_date":"2015-08-31"}
}'),
('3', 'tracker_model', '{
"cb_self_assessment":
	{"dashboard":"true","label":"Self-Assessment", "type": "approval"}, 
"cb_self_assessment_url":
	{"dashboard":"false","label":"Self-Assessment Plan URL (Optional)", "type": "url", "indent": "1"},
"cb_implementation_plan": 
	{"dashboard": "true", "label": "Implementation Plan"},
"cb_date_of_omb_approval_of_implementation_plan":
	{"dashboard":"false","label":"Date of OMB Approval of Implementation Plan", "type": "date", "indent": "1"},
"cb_cio_assignment_plan":
	{"dashboard": "true", "label":"CIO Assignment Plan (If Applicable)"},
"pa_bureau_it_leadership":
	{"dashboard": "true", "label":"Bureau IT Leadership", "due_date":"2015-08-15"},
"pa_cio_governance_board":
	{"dashboard": "true", "label":"CIO Governance Board List", "due_date":"2015-08-31"},
"pa_it_policy_archive":
	{"dashboard": "true", "label":"IT Policy Archive", "due_date":"2015-08-31"},
"ci_listserv_members":
	{"dashboard": "true", "label":"# of Listserv Members"}
}'),
-- No need (yet) to define after 4 which sets self-assessment back to 'select'
('4', 'tracker_model', '{
"cb_self_assessment":
	{"dashboard":"true","label":"Self-Assessment", "type": "select"}, 
"cb_self_assessment_url":
	{"dashboard":"false","label":"Self-Assessment Plan URL (Optional)", "type": "url", "indent": "1"},
"cb_implementation_plan": 
	{"dashboard": "true", "label":"Implementation Plan"},
"cb_date_of_omb_approval_of_implementation_plan":
	{"dashboard":"false","label":"Date of OMB Approval of Implementation Plan", "type": "date", "indent": "1"},	
"cb_cio_assignment_plan":
	{"dashboard": "true", "label":"CIO Assignment Plan (If Applicable)"},
"pa_bureau_it_leadership":
	{"dashboard": "true", "label":"Bureau IT Leadership"},
"pa_cio_governance_board":
	{"dashboard": "true", "label":"IT Policy Archive"},
"ci_listserv_members":
	{"dashboard": "true", "label":"# of Listserv Members"}
}');