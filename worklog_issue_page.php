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
 * View worklog entries for the given bugnote
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
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('print_api.php');
require_api('string_api.php');
require_api('worklog_api.php');


####
## helper functions
####
function form_header($p_cmd, $p_bugnote_id, $p_worklog_id = 0, $p_from = '', $p_to = ''){
	$t_r = '<form method="post" action="worklog_issue_page.php" class="form-inline input-hover-form">'
		 . format_input_hidden('worklog_update_token', form_security_token('worklog_update'))
		 . format_input_hidden('bugnote_id', $p_bugnote_id)
		 . format_input_hidden('cmd', $p_cmd);

	if($p_worklog_id != 0)
		$t_r .= format_input_hidden('worklog_id', $p_worklog_id);

	if($p_from != '')
		$t_r .= format_input_hidden('date_from', $p_from);

	if($p_to != '')
		$t_r .= format_input_hidden('date_to', $p_to);

	return $t_r;
}


####
## input
####

# config variables
$t_date_format = config_get('normal_date_format');

# form inputs
$f_bugnote_id = gpc_get_int('bugnote_id', 0);
$f_date_from = gpc_get_string('date_from', '');
$f_date_to = gpc_get_string('date_to', '');


####
## access validation
#####

json_prepare();

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
	json_error('Access denied to readonly issue');

# Check if the current user is allowed to change the view state of this bugnote
$t_user_id = bugnote_get_field($f_bugnote_id, 'reporter_id');

if($t_user_id == auth_get_current_user_id()) {
	access_ensure_bugnote_level(config_get('bugnote_user_change_view_state_threshold'), $f_bugnote_id);
} else {
	access_ensure_bugnote_level(config_get('update_bugnote_threshold'), $f_bugnote_id);
	access_ensure_bugnote_level(config_get('change_view_status_threshold'), $f_bugnote_id);
}


####
## handle worklog update
####

# XXX including worklog_update.php rather than using it as the form action is required
#	  in order to be able to display this page and its updates as inline page
include('worklog_update.php');


####
## main form
####

$t_from = 0;
$t_to = 0;

if($f_date_from != '')
	$t_from = strtotime($f_date_from);

if($f_date_to != '')
	$t_to = strtotime($f_date_to) + SECONDS_PER_DAY - 1;

$t_work_log = worklog_get($f_bugnote_id, $t_from, $t_to);

# get earliest and latest worklog date
$t_from = 0;
$t_to = 0;

foreach($t_work_log as $t_entry){
	if($t_entry->date < $t_from || $t_from == 0)
		$t_from = $t_entry->date;

	if($t_entry->date > $t_to || $t_to == 0)
		$t_to = $t_entry->date;
}

if($f_date_from == '')
	$f_date_from = date(config_get('short_date_format'), $t_from);

if($f_date_to == '')
	$f_date_to = date(config_get('short_date_format'), $t_to);


layout_inline_page_begin();
page_title(bug_format_summary($t_bug_id, SUMMARY_CAPTION));


report_warning('Invalid command \'' . 'pp' . '\'', false);

actionbar_begin();
	echo '<div class="pull-left">';
	
	# log work
	echo form_header('add', $f_bugnote_id);
	echo '<span id="log_work_div">';
	text('time_tracking_' . $t_id, 'time_tracking_0', '', 'hh:mm', 'input-xs', '','size=6');
	hspace('2px');
	button('Log Work', 'log_work_div-action-0', 'submit', '', '');
	echo '</span>';
	echo '</form>';
	echo '</div>';

	echo '<div class="pull-right">';

	# period select
	echo form_header('undefined', $f_bugnote_id);
	echo format_label('From: ') . format_hspace('2px');
	echo format_date('date_from', 'date_from', $f_date_from, '7em', true);
	echo format_label('To: ') . format_hspace('2px');
	echo format_date('date_to', 'date_to', $f_date_to, '7em', true);
	button('Apply', 'get_bugnote_stats_button','submit');
	echo '</form>';

	# show all
	echo form_header('undefined', $f_bugnote_id);
	button('Reset', 'get_bugnote_stats_button','submit');
	echo '</form>';
	echo '</div>';
actionbar_end();

table_begin(array(), 'table-condensed no-border');
	$t_total = 0;

	foreach ($t_work_log as $t_entry){
		$t_worklog_id = $t_entry->id;
		$t_date = date($t_date_format, $t_entry->date);
		$t_user = prepare_user_name($t_entry->user_id);
		$t_time = db_minutes_to_hhmm($t_entry->time);

		$t_total += $t_entry->time;

		table_row(
			array(
				# delete button
				form_header('delete', $f_bugnote_id, $t_worklog_id, $f_date_from, $f_date_to)
				. format_button('<i class="fa fa-trash red"></i>', 'delete_' . $t_worklog_id, 'submit', '', 'btn-icon', true)
				. '</form>',

				# user name
				$t_user,

				# date
				form_header('update', $f_bugnote_id, $t_worklog_id, $f_date_from, $f_date_to)
				. format_input_hidden('time_tracking_' . $t_worklog_id, $t_time)
				. format_input_hover_date('date_' . $t_worklog_id, $t_date)
				. '</form>',

				# time spent
				form_header('update', $f_bugnote_id, $t_worklog_id, $f_date_from, $f_date_to)
				. format_input_hover_text('time_tracking_' . $t_worklog_id, $t_time)
				. '</form>'
			),
			'',
			array(
				'',
				'',
				'width="35%"',
				''
			)
		);
	}
table_end();

echo '<div class="time-tracking-total pull-right">' . format_icon('fa-clock-o', 'red') . 'Time Spent During Period: ' . db_minutes_to_hhmm($t_total) . '</div>';


# free worklog data
unset($t_work_log);

layout_inline_page_end();
