<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2021-06-10 15:13:35 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks.php 128
ERROR - 2021-06-10 15:13:37 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks_state_report.php 181
ERROR - 2021-06-10 15:13:37 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks_state_report.php 181
ERROR - 2021-06-10 15:28:06 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks.php 128
ERROR - 2021-06-10 15:28:07 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks_state_report.php 181
ERROR - 2021-06-10 15:28:07 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks_state_report.php 181
ERROR - 2021-06-10 15:42:11 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks.php 128
ERROR - 2021-06-10 15:42:13 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks_state_report.php 181
ERROR - 2021-06-10 15:42:13 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks_state_report.php 181
ERROR - 2021-06-10 15:59:33 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks.php 128
ERROR - 2021-06-10 15:59:34 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks_state_report.php 181
ERROR - 2021-06-10 15:59:34 --> Severity: Warning --> count(): Parameter must be an array or an object that implements Countable /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/tasks/tasks_state_report.php 181
ERROR - 2021-06-10 16:58:09 --> Query error: Column 'tender_endate' cannot be null - Invalid query: INSERT INTO `tbl_tenders` (`opportunities_id`, `tender_name`, `tender_budget`, `tender_startdate`, `tender_endate`, `tender_eligibility`) VALUES ('1', 'test', '10000', '2021-06-10', NULL, 'test')
ERROR - 2021-06-10 16:59:02 --> Query error: Column 'tender_endate' cannot be null - Invalid query: INSERT INTO `tbl_tenders` (`opportunities_id`, `tender_name`, `tender_budget`, `tender_startdate`, `tender_endate`, `tender_eligibility`) VALUES ('1', 'dad', 'da', '2021-06-10', NULL, 'adadad')
ERROR - 2021-06-10 17:00:21 --> Query error: Column 'tender_endate' cannot be null - Invalid query: INSERT INTO `tbl_tenders` (`opportunities_id`, `tender_name`, `tender_budget`, `tender_startdate`, `tender_endate`, `tender_eligibility`) VALUES ('1', 'ss', 'asa', '2021-06-10', NULL, 'sasas')
ERROR - 2021-06-10 17:03:48 --> Severity: error --> Exception: Call to undefined method Opportunities::save() /home/u910028744/domains/nemmaditech.in/public_html/pm/application/controllers/admin/Opportunities.php 1608
ERROR - 2021-06-10 17:05:33 --> Query error: Column 'tender_endate' cannot be null - Invalid query: INSERT INTO `tbl_tenders` (`opportunities_id`, `tender_name`, `tender_budget`, `tender_startdate`, `tender_endate`, `tender_eligibility`) VALUES ('1', 'f', 'fgj', '2021-06-10', NULL, 'gjgj')
ERROR - 2021-06-10 12:18:31 --> Severity: error --> Exception: syntax error, unexpected 'private' (T_PRIVATE) /home/u910028744/domains/nemmaditech.in/public_html/pm/application/controllers/admin/Opportunities.php 1636
ERROR - 2021-06-10 17:25:35 --> Query error: Table 'u910028744_pm.tbl_tender' doesn't exist - Invalid query: SELECT *
FROM `tbl_tender`
WHERE `opportunities_id` = '1'
ORDER BY `tender_id` DESC
ERROR - 2021-06-10 17:25:35 --> Severity: error --> Exception: Call to a member function result() on boolean /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/opportunities/opportunities_details.php 17
ERROR - 2021-06-10 17:29:17 --> Severity: Warning --> Use of undefined constant tender_startdate - assumed 'tender_startdate' (this will throw an Error in a future version of PHP) /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/opportunities/opportunities_details.php 1339
ERROR - 2021-06-10 17:29:17 --> Severity: Warning --> Use of undefined constant tender_startdate - assumed 'tender_startdate' (this will throw an Error in a future version of PHP) /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/opportunities/opportunities_details.php 1339
ERROR - 2021-06-10 19:01:10 --> Severity: Warning --> Illegal string offset 'data-act' /home/u910028744/domains/nemmaditech.in/public_html/pm/application/helpers/admin_helper.php 2199
ERROR - 2021-06-10 19:01:10 --> Severity: Warning --> Illegal string offset 'data-action-url' /home/u910028744/domains/nemmaditech.in/public_html/pm/application/helpers/admin_helper.php 2200
ERROR - 2021-06-10 19:40:34 --> Query error: Unknown column 'tender_id' in 'order clause' - Invalid query: SELECT *
FROM `tbl_leads_tenders`
WHERE `opportunities_id` IS NULL
AND `deletion_indicator` = 0
ORDER BY `tender_id` DESC
ERROR - 2021-06-10 19:40:34 --> Severity: error --> Exception: Call to a member function result() on boolean /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/leads/leads_details.php 31
ERROR - 2021-06-10 19:42:03 --> Query error: Unknown column 'tender_id' in 'order clause' - Invalid query: SELECT *
FROM `tbl_leads_tenders`
WHERE `leads_id` = '2'
AND `deletion_indicator` = 0
ORDER BY `tender_id` DESC
ERROR - 2021-06-10 19:42:03 --> Severity: error --> Exception: Call to a member function result() on boolean /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/leads/leads_details.php 31
ERROR - 2021-06-10 19:42:04 --> Query error: Unknown column 'tender_id' in 'order clause' - Invalid query: SELECT *
FROM `tbl_leads_tenders`
WHERE `leads_id` = '2'
AND `deletion_indicator` = 0
ORDER BY `tender_id` DESC
ERROR - 2021-06-10 19:42:04 --> Severity: error --> Exception: Call to a member function result() on boolean /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/leads/leads_details.php 31
ERROR - 2021-06-10 19:43:13 --> Query error: Unknown column 'tender_id' in 'order clause' - Invalid query: SELECT *
FROM `tbl_leads_tenders`
WHERE `leads_id` = '2'
AND `deletion_indicator` = 0
ORDER BY `tender_id` DESC
ERROR - 2021-06-10 19:43:13 --> Severity: error --> Exception: Call to a member function result() on boolean /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/leads/leads_details.php 31
ERROR - 2021-06-10 19:43:14 --> Query error: Unknown column 'tender_id' in 'order clause' - Invalid query: SELECT *
FROM `tbl_leads_tenders`
WHERE `leads_id` = '2'
AND `deletion_indicator` = 0
ORDER BY `tender_id` DESC
ERROR - 2021-06-10 19:43:14 --> Severity: error --> Exception: Call to a member function result() on boolean /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/leads/leads_details.php 31
ERROR - 2021-06-10 19:50:03 --> Query error: Table 'u910028744_pm.tbl_tenders' doesn't exist - Invalid query: SELECT *
FROM `tbl_tenders`
WHERE `opportunities_id` IS NULL
AND `deletion_indicator` = 0
ORDER BY `tender_id` DESC
ERROR - 2021-06-10 19:50:03 --> Severity: error --> Exception: Call to a member function result() on boolean /home/u910028744/domains/nemmaditech.in/public_html/pm/application/views/admin/opportunities/opportunities_details.php 17
ERROR - 2021-06-10 19:50:17 --> Query error: Table 'u910028744_pm.tbl_tenders' doesn't exist - Invalid query: SELECT *
FROM `tbl_tenders`
WHERE `tender_id` = '2'
ERROR - 2021-06-10 19:50:17 --> Severity: error --> Exception: Call to a member function row() on boolean /home/u910028744/domains/nemmaditech.in/public_html/pm/application/controllers/admin/Opportunities.php 543
ERROR - 2021-06-10 19:58:50 --> Query error: Table 'u910028744_pm.tbl_tenders' doesn't exist - Invalid query: UPDATE `tbl_tenders` SET `tender_name` = 'test1', `tender_budget` = '200000', `tender_startdate` = '2021-06-11', `tender_enddate` = '2021-06-18', `tender_eligibility` = 'test eligibility2'
WHERE `tender_id` = '2'
