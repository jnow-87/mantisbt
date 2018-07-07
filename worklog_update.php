<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Update a work log entry
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses html_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses worklog_api.php
 */

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('bugnote_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('error_api.php');
require_api('html_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('print_api.php');
require_api('string_api.php');
require_api('worklog_api.php');


####
## input
####

# form inputs
$f_bugnote_id = gpc_get_int('bugnote_id', 0);
$f_worklog_id = gpc_get_int('worklog_id', 0);
$f_time_tracking = gpc_get_string('time_tracking_' . $f_worklog_id, '');
$f_date = strtotime(gpc_get_string('date_' . $f_worklog_id, ''));
$f_cmd = gpc_get_string('cmd', 'undefined');
$f_resp_type = gpc_get_int('resp_type', RESP_NONE);


####
## access validation
####

json_prepare();

form_security_validate('worklog_update');
form_security_purge('worklog_update');

# get bug note
$t_bug_id = bugnote_get_field($f_bugnote_id, 'bug_id');

$t_bug = bug_get($t_bug_id, true);
if($t_bug->project_id != helper_get_current_project()) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

# Check if the bug is readonly
if(bug_is_readonly($t_bug_id))
	report_error('Access denied to readonly issue', '', $f_resp_type);

# Check if the current user is allowed to change the view state of this bugnote
$t_user_id = bugnote_get_field($f_bugnote_id, 'reporter_id');
if($t_user_id == auth_get_current_user_id()) {
	access_ensure_bugnote_level(config_get('bugnote_user_change_view_state_threshold'), $f_bugnote_id);
} else {
	access_ensure_bugnote_level(config_get('update_bugnote_threshold'), $f_bugnote_id);
	access_ensure_bugnote_level(config_get('change_view_status_threshold'), $f_bugnote_id);
}


####
## main
####

# redirect information
$t_redirect_buttons = array(
	array(string_get_worklog_issue_url($f_bugnote_id), lang_get('view_worklog') . ' ' . $f_bugnote_id),
	array(string_get_bug_view_url($t_bug_id), lang_get('view_issue') . ' ' . $t_bug_id ),
);


# perform cmd
switch($f_cmd){
case 'add':
	worklog_add($f_bugnote_id, $f_time_tracking);
	report_success('Worklog entry added', string_get_bug_view_url($t_bug_id), $f_resp_type);
	break;

case 'delete':
	worklog_delete($f_bugnote_id, $f_worklog_id);
	report_success('Worklog deleted', string_get_worklog_issue_url($f_bugnote_id), $f_resp_type);
	break;

case 'update':
	worklog_update($f_bugnote_id, $f_worklog_id, $f_time_tracking, $f_date);
	report_success('Worklog updated', string_get_worklog_issue_url($f_bugnote_id), $f_resp_type);
	break;

case 'undefined':
	break;

default:
	report_error('Invalid command \'' . $f_cmd . '\'', '', $f_resp_type);
	break;
}
