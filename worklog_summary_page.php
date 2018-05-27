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
 * Display Mantis Billing Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses string_api.php
 * @uses utility_api.php
 * @uses print_api.php
 * @uses access_api.php
 * @uses worklog_api.php
 * @uses html_api.php
 */

require_once('core.php');
require_api('collapse_api.php');
require_api('config_api.php');
require_api('database_api.php');
require_api('filter_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('string_api.php');
require_api('utility_api.php');
require_api('print_api.php');
require_api('access_api.php');
require_api('worklog_api.php');
require_api('html_api.php');
require_api('project_api.php');
require_api('elements_api.php');


/**
 *	echo a link to the project management page for the given id
 *
 *	@param	integer		$p_project_id	project id to format
 *
 *	@return	none
 */
function format_project_link($p_project_id){
	return format_link(project_get_name($p_project_id), 'project_page.php', array('project_id' => $p_project_id));
}


/**
 *	print a table per user and the specified row data
 *
 *	@param	array	$p_users			array containing an entry per user, with each user entry
 *										containing an array with the number of minutes spent for
 *										$p_row_label
 *	@param	string	$p_row_label		string identifying the data in $p_users to be printed
 *	@param	array	$p_rows				array of valid row indices, each index has to have an entry
 *										in $p_users[][$p_row_label]
 *	@param	string	$p_row_name_func	name of function used to format the name of each row index
 *										within $p_rows
 *	@param	string	$p_row_link_func	name of function used to create a link to the target row item
 *
 *	@return	none
 */
function worklog_table($p_users, $p_row_label, $p_rows, $p_row_name_func, $p_row_link_func){
	$t_row_names = array();


	# create array with row name and id
	foreach ($p_rows as $t_row)
		$t_row_names[$p_row_name_func($t_row)] = $t_row;

	# sort array
	ksort($t_row_names);

	$t_users = array('');

	foreach($p_users as $t_user)
			$t_users[] = user_get_name($t_user['id']);
	
	$t_users[] = 'Total';

	table_begin($t_users, 'table-bordered table-condensed table-hover table-datatable');
		$t_col_total = array();

		foreach ($t_row_names as $t_row_id){
			$t_row = array();
			$t_row_attr = array();
			$t_row_total = 0;

			$t_row[] = $p_row_link_func($t_row_id);
			$t_row_attr[] = 'class="category"';

			foreach($p_users as $t_user){
				if(!isset($t_col_total[$t_user['id']]))
					$t_col_total[$t_user['id']] = 0;

				$t_col_total[$t_user['id']] += $t_user[$p_row_label][$t_row_id];
				$t_row_total += $t_user[$p_row_label][$t_row_id];

				$t_minutes = $t_user[$p_row_label][$t_row_id];
				$t_row[] = $t_minutes == 0 ? '-' : db_minutes_to_hhmm($t_minutes);
				$t_row_attr[] = '';
			}

			$t_row[] = db_minutes_to_hhmm($t_row_total);
			$t_row_attr[] = 'width="100%"';

			table_row($t_row, '', $t_row_attr);
		}


		$t_row = array(format_icon('fa-clock-o', 'red') . 'Total');
		$t_row_attr[] = 'class="category"';
		$t_total = 0;

		foreach($p_users as $t_user){
			$t_total += $t_col_total[$t_user['id']];

			$t_row[] = db_minutes_to_hhmm($t_col_total[$t_user['id']]);
			$t_row_attr[] = '';
		}

		$t_row[] = db_minutes_to_hhmm($t_total);
		$t_row_attr[] = 'width="100%"';


		echo '<tfoot>';
		table_row($t_row, '', $t_row_attr);
		echo '</tfoor>';
	table_end();
}

function tab_per_project(){
	global $t_stats;
	worklog_table($t_stats['users'], 'projects', $t_stats['projects'], 'project_get_name', 'format_project_link');
}

function tab_per_issue(){
	global $t_stats;
	worklog_table($t_stats['users'], 'bugs', $t_stats['bugs'], 'bug_format_id', 'string_get_bug_view_link');
}


$f_project_id = helper_get_current_project();
$f_date_from = gpc_get_string('date_from', date(config_get('normal_date_format'), 0));
$f_date_to = gpc_get_string('date_to', date(config_get('normal_date_format'), time()));


layout_page_header('Work Log');
layout_page_begin();

page_title('Work Log Summary');

worklog_ensure_reporting_access();


# retrieve worklog stats
$t_stats = worklog_get_for_project($f_project_id, $f_date_from, $f_date_to);


column_begin('12');
	actionbar_begin();
		echo '<form method="post" action="">';
			input_hidden('id', 0);
			echo format_date('date_from', 'date_from', $f_date_from, '', true) . format_hspace('2px');
			echo format_date('date_to', 'date_to', $f_date_to, '', true) . format_hspace('2px');

			button('Get Work Log', 'submit-btn', 'submit');
			button('Reset', 'reset-btn', 'reset');
		echo '</form>';
	actionbar_end();


if(isset($t_stats['users']))
	tabs(array('Projects' => 'tab_per_project', 'Issues' => 'tab_per_issue'));

column_end();

layout_page_end();
