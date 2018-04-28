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
 * View all bug page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses current_user_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses user_api.php
 */

require_once('core.php');
require_api('authentication_api.php');
require_api('compress_api.php');
require_api('config_api.php');
require_api('current_user_api.php');
require_api('filter_api.php');
require_api('gpc_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('project_api.php');
require_api('user_api.php');
require_api('category_api.php');
require_api('columns_api.php');
require_api('constant_inc.php');
require_api('event_api.php');
require_api('helper_api.php');
require_api('elements_api.php');
require_api('bug_group_action_api.php');
require_api('bug_list_api.php');

require_js('bugFilter.js');
require_css('status_config.php');


$f_hide_filter = gpc_get_bool('hide_filter', false);
$t_project_id = gpc_get_int('project_id', helper_get_current_project());
$f_columns = helper_get_columns_to_view(COLUMNS_TARGET_VIEW_PAGE);
$f_columns[] = 'tags';


auth_ensure_user_authenticated();
compress_enable();
filter_init(current_user_get_bug_filter());


/* Get Project Id and set it as current */
if((ALL_PROJECTS == $t_project_id || project_exists($t_project_id)) && $t_project_id != helper_get_current_project()){
	helper_set_current_project($t_project_id);
	# Reloading the page is required so that the project browser
	# reflects the new current project
	print_header_redirect($_SERVER['REQUEST_URI'], true, false, true);
}

/* get bug ids */
$t_page = null;
$t_per_page = null;
$t_bug_count = null;
$t_page_count = null;

$t_filter = filter_gpc_get(null);

if(count(array_diff($t_filter, filter_get_default_array())) == 0)
	$t_filter = null;

$t_rows = filter_get_bug_rows($t_page, $t_per_page, $t_page_count, $t_bug_count, $t_filter, null, null);
	

if($t_rows === false)
	print_header_redirect('view_all_set.php?type=0');

$t_bug_ids = array();

foreach($t_rows as $t_row)
	$t_bug_ids[] = (int)$t_row->id;

/* bulk edit actions */
$t_cmds = bug_group_action_get_commands(null);
$t_cmd_list = array();

foreach($t_cmds as $t_action_id => $t_action_label)
	$t_cmd_list[$t_action_label] = $t_action_id;


/* page content */
# don't index view issues pages
html_robots_noindex();

layout_page_header_begin(lang_get('view_bugs_link'));

if(current_user_get_pref('refresh_delay') > 0)
	html_meta_redirect('view_all_bug_page.php?refresh=true', current_user_get_pref('refresh_delay') * 60);

layout_page_header_end();

layout_page_begin(__FILE__);
page_title('Filter Issues');

/* filter result */
echo '<div class="col-md-9">';
echo '<form id="bug_action" method="post" action="bug_actiongroup_page.php" class="input-hover-form">';
	input_hidden('filter_num_total', count($t_rows));

	actionbar_begin();
		echo '<div class="pull-left">';
			/* select all button */
			button('Select All', 'bug_arr_all', 'button', '', 'check-all');

			/* bulk edit action */
			select('bulk_action', 'bulk_action', $t_cmd_list, '');
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

	bug_list_print($t_bug_ids, $f_columns, 'table-condensed table-hover table-sortable no-border');
echo '</form>';
echo '</div>';


/* filter */
echo '<div class="col-md-3">';

if(!$f_hide_filter)
	filter_draw_selection_area(1, $t_filter);

echo '</div>';

layout_page_end();
