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
 * Work log APU
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
 * @uses helper_api.php
 */

require_once('core.php');
require_api( 'access_api.php' );
require_api('authentication_api.php');
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api('config_api.php');
require_api('constant_inc.php');
require_api('error_api.php');
require_api('database_api.php');
require_api('helper_api.php');


# TODO
#	update existing bugs, moving from time_tracking to worklog
#	fix 'private' bugnote in add bugnote and transition screens
#
#	overhaul layout
#		improve layout of worklog in bug view
#		move control panel to the top
#		make use of tabs
#		remove boxes, replacing them with a more open layout
#		streamline toolbars
#		add spaces between buttons
#
#	check for potential caching

class worklog_data{
	public $id;
	public $bugnote_id;
	public $time;
	public $date;
	public $user_id;
}


/**
 * check the validity of the inputs required for the worklog API
 *
 * @param	variable to check for validity
 * @return	nothing, but displays a respective error message
 */
function check_bugnote_id($p_bugnote_id){
	if($p_bugnote_id == 0){
		error_parameters(lang_get('bugnote') . ' ID');
		trigger_error(ERROR_INVALID_MISSING_INPUT, ERROR);
	}
}

function check_time_tracking($p_time_tracking){
	if($p_time_tracking == ''){
		error_parameters(lang_get('time_tracking'));
		trigger_error(ERROR_INVALID_MISSING_INPUT, ERROR);
	}
}

function check_worklog_id($p_worklog_id){
	if($p_worklog_id == 0){
		error_parameters(lang_get('worklog') . ' ID');
		trigger_error(ERROR_INVALID_MISSING_INPUT, ERROR);
	}
}

function check_worklog_user($p_worklog_id){
	check_worklog_id($p_worklog_id);


	$t_query = 'SELECT user_id FROM {worklog} WHERE id=' . db_param();
	$t_result = db_query($t_query, array($p_worklog_id));

	if(db_fetch_array($t_result)['user_id'] != auth_get_current_user_id()){
		unset($t_result);
		trigger_error(ERROR_ACCESS_DENIED, ERROR);
	}
}





/**
 * add a worklog entry to the databse
 *
 * @param	integer	$p_bugnote_id		id of the associated bugnote
 * @param	string	$p_time_tracking	timetracking string, format hh:mm
 *
 * @return	nothing, since database query errors are handled by db_query
 */
function worklog_add($p_bugnote_id, $p_time_tracking){
	check_bugnote_id($p_bugnote_id);
	check_time_tracking($p_time_tracking);

	$t_time_mm = helper_duration_to_minutes($p_time_tracking);

	db_param_push();
	$t_query = 'INSERT INTO {worklog} SET bugnote_id=' . db_param() . ', time = ' . db_param() . ', date = ' . db_param() . ', user_id = ' . db_param();
	db_query($t_query, array($p_bugnote_id, $t_time_mm, db_now(), auth_get_current_user_id()));

	bugnote_date_update($p_bugnote_id);
}

/**
 * remove a worklog entry to the databse
 *
 * @param	integer	$p_bugnote_id		id of the associated bugnote
 * @param	integer	$p_worklog_id		worklog id to be removed
 *
 * @return	nothing, since database query errors are handled by db_query
 */
function worklog_delete($p_bugnote_id, $p_worklog_id){
	check_bugnote_id($p_bugnote_id);
	check_worklog_id($p_worklog_id);
	check_worklog_user($p_worklog_id);

	db_param_push();
	$t_query = 'DELETE FROM {worklog} WHERE id=' . db_param();
	db_query($t_query, array($p_worklog_id));

	bugnote_date_update($p_bugnote_id);
}

/**
 * update/overwrite the time tracking value of a worklog entry already
 * present in the databse
 *
 * @param	integer	$p_bugnote_id		id of the associated bugnote
 * @param	integer	$p_worklog_id		id of the target worklog entry
 * @param	string	$p_time_tracking	timetracking string, format hh:mm
 *
 * @return	result of the database operation
 */
function worklog_update($p_bugnote_id, $p_worklog_id, $p_time_tracking) {
	check_worklog_id($p_worklog_id);
	check_worklog_user($p_worklog_id);
	check_bugnote_id($p_bugnote_id);
	check_time_tracking($p_time_tracking);

	$t_time_mm = helper_duration_to_minutes($p_time_tracking);

	db_param_push();
	$t_query = 'UPDATE {worklog} SET time = ' . db_param() . ', date = ' . db_param() . ' WHERE id=' . db_param();
	db_query($t_query, array($t_time_mm, db_now(), $p_worklog_id));

	bugnote_date_update($p_bugnote_id);
}

/**
 * acquire all worklog entries for a given bugnote
 *
 * @param	integer	$p_bugnote_id		id of the associated bugnote
 *
 * @return	array of worklog_data items
 */
function worklog_get($p_bugnote_id, $p_from = 0, $p_to = 0) {
	check_bugnote_id($p_bugnote_id);

	$t_worklog = array();
	$t_params = array($p_bugnote_id);
	$t_from = '';
	$t_to = '';

	if($p_from != 0){
		$t_from = ' AND date >= ' . db_param();
		$t_params[] = $p_from;
	}

	if($p_to != 0){
		$t_to = ' AND date <= ' . db_param();
		$t_params[] = $p_to;
	}


	db_param_push();
	$t_query = 'SELECT * FROM {worklog} WHERE bugnote_id=' . db_param() . $t_from . ' ' . $t_to;
	$t_result = db_query($t_query, $t_params);

	if($t_result == false)
		return array();

	while($t_row = db_fetch_array($t_result)){
		$t_entry = new worklog_data;

		$t_entry->id = $t_row['id'];
		$t_entry->bugnote_id = $t_row['bugnote_id'];
		$t_entry->time = $t_row['time'];
		$t_entry->date = $t_row['date'];
		$t_entry->user_id = $t_row['user_id'];

		$t_worklog[] = $t_entry;
	}

	return $t_worklog;
}

/**
 * get total time spent for a given bugnote in a given time period
 *
 * @param	integer	$p_bugnote_id		id of the associated bugnote
 * @param	integer	$p_from				unix timestamp for start date, ignored if 0
 * @param	integer $p_to				unix timestamp for end date, ignored if 0
 *
 * @return	number of minutes spent
 */
function worklog_get_time($p_bugnote_id, $p_from = 0, $p_to = 0) {
	check_bugnote_id($p_bugnote_id);

	$t_params = array($p_bugnote_id);
	$t_from = '';
	$t_to = '';

	if($p_from != 0){
		$t_from = ' AND date >= ' . db_param();
		$t_params[] = $p_from;
	}

	if($p_to != 0){
		$t_to = ' AND date <= ' . db_param();
		$t_params[] = $p_to;
	}


	db_param_push();
	$t_query = 'SELECT * FROM {worklog} WHERE bugnote_id=' . db_param() . $t_from . $t_to;
	$t_result = db_query($t_query, $t_params);

	if($t_result == false)
		return 0;

	$t_time = 0;

	while($t_row = db_fetch_array($t_result)){
		$t_time += $t_row['time'];
	}

	return $t_time;
}

/**
 * Ensure that the specified user has time tracking reporting access to the specified project.
 *
 * @param integer $p_project_id The project id or null for current project.
 * @param integer $p_user_id The user id or null for logged in user.
 */
function worklog_ensure_reporting_access( $p_project_id = null, $p_user_id = null ) {
	if( config_get( 'time_tracking_enabled' ) == OFF ) {
		trigger_error( ERROR_ACCESS_DENIED, ERROR );
	}

	access_ensure_project_level( config_get( 'time_tracking_reporting_threshold' ), $p_project_id, $p_user_id );
}

/**
 * Gets the worklog information for the specified project during the specified date range.
 * 
 * @param integer $p_project_id    A project identifier or ALL_PROJECTS.
 * @param string  $p_from          Starting date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param string  $p_to            Ending date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @return array array of bugnotes
 * @access public
 */
function worklog_get_for_project( $p_project_id, $p_from, $p_to ) {
	$t_params = array();
	$c_to = strtotime( $p_to ) + SECONDS_PER_DAY - 1;
	$c_from = strtotime( $p_from );

	if( $c_to === false || $c_from === false ) {
		error_parameters( array( $p_from, $p_to ) );
		trigger_error( ERROR_GENERIC, ERROR );
	}

	db_param_push();

	if( ALL_PROJECTS != $p_project_id ) {
		access_ensure_project_level( config_get( 'view_bug_threshold' ), $p_project_id );

		$t_project_where = ' AND b.project_id = ' . db_param() . ' AND bn.bug_id = b.id ';
		$t_params[] = $p_project_id;
	} else {
		$t_project_ids = user_get_all_accessible_projects();
		$t_project_where = ' AND b.project_id in (' . implode( ', ', $t_project_ids ). ')';
	}

	if( !is_blank( $c_from ) ) {
		$t_from_where = ' AND bn.last_modified >= ' . db_param();
		$t_params[] = $c_from;
	} else {
		$t_from_where = '';
	}

	if( !is_blank( $c_to ) ) {
		$t_to_where = ' AND bn.last_modified <= ' . db_param();
		$t_params[] = $c_to;
	} else {
		$t_to_where = '';
	}

	$t_results = array();

	$t_query = 'SELECT bn.id id, bn.date_submitted as date_submitted, bnt.note note,
			u.realname realname, b.project_id project_id, c.name bug_category, b.summary bug_summary, bn.bug_id bug_id, bn.reporter_id reporter_id
			FROM {user} u, {bugnote} bn, {bug} b, {bugnote_text} bnt, {category} c
			WHERE u.id = bn.reporter_id AND bn.bug_id = b.id AND bnt.id = bn.bugnote_text_id AND c.id=b.category_id
			' . $t_project_where . $t_from_where . $t_to_where . '
			ORDER BY bn.id';
	$t_result = db_query( $t_query, $t_params );

	$t_access_level_required = config_get( 'time_tracking_view_threshold');

	while( $t_row = db_fetch_array( $t_result ) ) {
		if ( !access_has_bugnote_level( $t_access_level_required, $t_row['id'] ) ) {
			continue;
		}

		$t_row['minutes'] = worklog_get_time($t_row['id'], $c_from, $c_to);

		$t_results[] = $t_row;
	}

	$t_rows = worklog_rows_to_array( $t_results );
	return $t_rows;
}

/**
 * Gets the worklog summary for the specified project and the date range.
 *
 * @param integer $p_project_id    A project identifier or ALL_PROJECTS.
 * @param string  $p_from          Starting date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param string  $p_to            Ending date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @return array The contains worklog data grouped by issues, users, and total information.
 * @access public
 */
function worklog_get_summaries( $p_project_id, $p_from, $p_to ) {
	$t_notes = worklog_get_for_project( $p_project_id, $p_from, $p_to );

	$t_issues = array();
	$t_users = array();

	foreach ( $t_notes as $t_note ) {
		extract( $t_note, EXTR_PREFIX_ALL, 'v' );

		$t_username = user_get_name( $v_reporter_id );

		# Create users in collection of users if not already exists
		if( !isset( $t_users[$t_username] ) ) {
			$t_users[$t_username] = array();
			$t_users[$t_username]['minutes'] = 0;
		}

		# Update user total minutes
		$t_users[$t_username]['minutes'] += $v_minutes;

		# Create issue if it doesn't exist yet.
		if( !isset( $t_issues[$v_bug_id] ) ) {
			$t_issues[$v_bug_id]['issue_id'] = $v_bug_id;
			$t_issues[$v_bug_id]['project_id'] = $v_project_id;
			$t_issues[$v_bug_id]['project_name'] = $v_project_name;
			$t_issues[$v_bug_id]['summary'] = $v_bug_summary;
			$t_issues[$v_bug_id]['users'] = array();
			$t_issues[$v_bug_id]['minutes'] = 0;
		}

		# Create user within issue if they don't exist yet
		if( !isset( $t_issues[$v_bug_id]['users'][$t_username] ) ) {
			$t_issues[$v_bug_id]['users'][$t_username] = array();
			$t_issues[$v_bug_id]['users'][$t_username]['minutes'] = 0;
		}

		# Update total minutes for user within the issue
		$t_issues[$v_bug_id]['users'][$t_username]['minutes'] += $v_minutes;

		# Update total minutes for issue
		$t_issues[$v_bug_id]['minutes'] += $v_minutes;
	}

	$t_total = array(
		'minutes' => 0,
	);

	# Calculate total minutes across all issues
	foreach( $t_issues as $t_issue_id => $t_issue_info ) {
		$t_issues[$t_issue_id]['duration'] = db_minutes_to_hhmm( $t_issue_info['minutes'] );
		$t_total['minutes'] += $t_issue_info['minutes'];

		ksort( $t_issues[$t_issue_id]['users'] );
	}

	ksort( $t_users );
	ksort( $t_issues );

	return array(
		'issues' => $t_issues,
		'users' => $t_users,
		'total' => $t_total );
}

/**
 * Converts an array of bugnotes
 *
 * @param array $p_bugnotes  Array of bugnotes
 * @return array             output rows
 * @access private
 */
function worklog_rows_to_array( $p_bugnotes ) {
	$t_rows = array();

	foreach( $p_bugnotes as $t_note ) {
		$t_row = array();
		$t_row['id'] = $t_note['id'];
		$t_row['minutes'] = $t_note['minutes'];
		$t_row['duration'] = db_minutes_to_hhmm( $t_note['minutes'] );
		$t_row['note'] = $t_note['note'];
		$t_row['reporter_id'] = $t_note['reporter_id'];
		$t_row['reporter_username'] = user_get_name( $t_note['reporter_id'] );
		$t_row['reporter_realname'] = user_get_realname( $t_note['reporter_id'] );
		$t_row['date_submitted'] = $t_note['date_submitted'];

		if ( is_blank( $t_row['reporter_realname'] ) ) {
			$t_row['reporter_realname'] = $t_row['reporter_username'];
		}

		$t_row['bug_id'] = $t_note['bug_id'];
		$t_row['project_id'] = $t_note['project_id'];
		$t_row['project_name'] = project_get_name( $t_note['project_id'] );
		$t_row['bug_summary'] = $t_note['bug_summary'];
		$t_row['bug_category'] = $t_note['bug_category'];

		$t_rows[] = $t_row;
	}

	return $t_rows;
}
