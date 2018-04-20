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
 * View all bugs include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses category_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses event_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

if(!defined('VIEW_ALL_INC_ALLOW'))
	return;

require_api('category_api.php');
require_api('columns_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('current_user_api.php');
require_api('event_api.php');
require_api('filter_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('elements_api.php');
require_api('bug_group_action_api.php');

$t_filter = current_user_get_bug_filter();
$f_hide_filter = gpc_get_bool('hide_filter', false);

filter_init($t_filter);
list($t_sort,) = explode(',', $g_filter['sort']);
list($t_dir,) = explode(',', $g_filter['dir']);


/* Improve performance by caching category data in one pass */
if(helper_get_current_project() > 0)
	category_get_all_rows(helper_get_current_project());

$g_columns = helper_get_columns_to_view(COLUMNS_TARGET_VIEW_PAGE);
bug_cache_columns_data($t_rows, $g_columns);

/* bulk edit actions */
$t_cmds = bug_group_action_get_commands(null);
$t_cmd_list = array();

while(list($t_action_id, $t_action_label) = each($t_cmds))
	$t_cmd_list[$t_action_label] = $t_action_id;


/* left column */
echo '<div class="col-md-9">';
echo '<form id="bug_action" method="post" action="bug_actiongroup_page.php">';
	input_hidden('filter_num_total', count($t_rows));

	actionbar_begin();
		echo '<div class="pull-left">';
			/* select all button */
			button('Select All', 'bug_arr_all', 'button', '', 'check-all');

			/* bulk edit action */
			select('action', 'action', $t_cmd_list, '');
			hspace('2px');
			button('Apply', 'apply_bulk', 'submit');
		echo '</div>';

		echo '<div class="pull-right">';
			/* print and export buttons */
			button_link('Print Report', 'print_all_bug_page.php');
			button_link('CSV Export', 'csv_export.php');

			/* plugin handling */
			$t_event_menu_options = event_signal('EVENT_MENU_FILTER');

			foreach ($t_event_menu_options as $t_plugin => $t_plugin_menu_options){
				foreach ($t_plugin_menu_options as $t_callback => $t_callback_menu_options){
					if (!is_array($t_callback_menu_options))
						$t_callback_menu_options = array($t_callback_menu_options);

					foreach ($t_callback_menu_options as $t_menu_option){
						if ($t_menu_option)
							echo $t_menu_option;
					}
				}
			}
		echo '</div>';
	actionbar_end();

	/* prepare table head */
	$t_thead = array();
	foreach($g_columns as $t_column)
		$t_thead[] = lang_get($t_column);

	table_begin($t_thead, 'table-bordered table-condensed table-striped table-hover table-sortable'); 

	/* issue list */
	foreach($t_rows as $t_row){
		$t_trow = array();

		foreach($g_columns as $t_column){
			/* get formated cell (including 'td' tags) */
			ob_start();
			helper_call_custom_function('print_column_value', array($t_column, $t_row));
			$t_cell = ob_get_contents();
			ob_end_clean();

			/* strip 'td' tags */
			$t_cell = preg_replace('#<td[^>]*>#', '', $t_cell);
			$t_cell = preg_replace('#</td[^>]*>#', '', $t_cell);

			$t_trow[] = $t_cell;
		}

		table_row($t_trow);
	}

	table_end();

echo '</form>';
echo '</div>';


/* right column */
echo '<div class="col-md-3">';

if(!$f_hide_filter){
	/* filter */
	filter_draw_selection_area($f_page_number);
}

echo '</div>';
