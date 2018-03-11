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
require_api('access_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('bugnote_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('error_api.php');
require_api('database_api.php');
require_api('helper_api.php');


# TODO
#	update existing bugs, moving from time_tracking to worklog
#
#	overhaul layout
#		improve layout of worklog in bug view
#		move control panel to the top
#		make use of tabs
#		remove boxes, replacing them with a more open layout
#		streamline toolbars
#		add spaces between buttons
#		which css files are used
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
 *
 * @return array containing arrays for
 *			'users':	an array containing an array for each relevant user id with the following entries
 *						'bugs':		array with an entry for each relevant bug id, listing the minutes spent
 *									by the respective user for this bug
 *						'projects'	array with an entry for each relevant project id, listing the minutes
 *									spent by the respective user for this project
 *
 *			'projects':	an array containing all project ids relevant for the given time period
 '			'bugs':		an array containing all bug ids relevant for the given time period
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

		$t_project_where = ' AND b.project_id = ' . db_param();
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

	$t_query = 'SELECT b.id bug_id, b.project_id project_id, wl.user_id user_id, wl.time time '
			 . 'FROM {bug} b, {bugnote} bn, {worklog} wl '
			 . 'WHERE b.id = bn.bug_id and bn.id = wl.bugnote_id '
			 . $t_project_where . $t_from_where . $t_to_where
			 . ' ORDER BY bn.id';

	$t_result = db_query( $t_query, $t_params );

	$t_access_level_required = config_get( 'time_tracking_view_threshold');

	$t_users = array();
	$t_projects = array();
	$t_bugs = array();

	while( $t_row = db_fetch_array( $t_result ) ) {
		if ( !access_has_bugnote_level( $t_access_level_required, $t_row['bug_id'] ) ) {
			continue;
		}

		$t_bug_id = $t_row['bug_id'];
		$t_project_id = $t_row['project_id'];
		$t_minutes = $t_row['time'];
		$t_user_id = $t_row['user_id'];

		if(!isset($t_users[$t_user_id]))
			$t_users[$t_user_id] = array('id' => $t_user_id, 'projects' => array(), 'bugs' => array());

		if(!isset($t_users[$t_user_id]['projects'][$t_project_id]))
			$t_users[$t_user_id]['projects'][$t_project_id] = 0;

		$t_users[$t_user_id]['projects'][$t_project_id] += $t_minutes;

		if(!isset($t_users[$t_user_id]['bugs'][$t_bug_id]))
			$t_users[$t_user_id]['bugs'][$t_bug_id] = 0;

		$t_users[$t_user_id]['bugs'][$t_bug_id] += $t_minutes;

		if(!in_array($t_project_id, $t_projects))
			$t_projects[] = $t_project_id;

		if(!in_array($t_bug_id, $t_bugs))
			$t_bugs[] = $t_bug_id;
	}

	return array( 'users' => $t_users, 'projects' => $t_projects, 'bugs' => $t_bugs);
}
