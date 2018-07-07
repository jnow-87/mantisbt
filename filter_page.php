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
$f_project_id = gpc_get_int('project_id', helper_get_current_project());
$f_columns = bug_list_columns('bug_list_columns_filter');

auth_ensure_user_authenticated();
compress_enable();
filter_init(current_user_get_bug_filter());
form_security_purge('filter_update');


/* Get Project Id and set it as current */
if((ALL_PROJECTS == $f_project_id || project_exists($f_project_id)) && $f_project_id != helper_get_current_project()){
	helper_set_current_project($f_project_id);
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
	print_header_redirect('filter_apply.php?type=0');

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

layout_page_header_begin('Search Issues');

if(current_user_get_pref('refresh_delay') > 0)
	html_meta_redirect('filter_page.php?refresh=true', current_user_get_pref('refresh_delay') * 60);

layout_page_header_end();

layout_page_begin();
page_title('Filter Issues');

/* filter result */
column_begin('9');
echo '<form id="bug_action" method="post" action="bug_actiongroup_page.php" class="input-hover-form">';
	input_hidden('filter_num_total', count($t_rows));

	actionbar_begin();
		echo '<div class="pull-left">';
			/* bulk operations */
			if(in_array('selection', $f_columns)){
				// select all button
				button('Select All', 'bug_arr_all', 'button', '', 'check-all');

				// operation selection
				select('bulk_action', 'bulk_action', $t_cmd_list, '');
				hspace('2px');
				button('Apply', 'apply_bulk', 'submit');
			}
			else
				// hint if 'selection' column is disabled
				echo 'enable bulk operations by enabling the \'selection\' ' . format_link('column', 'columns_select_page.php', column_select_input('bug_list_columns_filter', $f_columns, true, false, basename(__FILE__)), 'inline-page-link');
		echo '</div>';

		echo '<div class="pull-right">';
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

			/* dropdown: export, print, column selection */
			$t_menu = array(
				array('label' => 'Export csv', 'data' => array('link' => 'csv_export.php')),
				array('label' => 'Print', 'data' => array('link' => 'print_all_bug_page.php')),
				array('label' => 'divider', 'data' => ''),
				array('label' => 'Select Filter Columns', 'data' => array('link' => format_href('columns_select_page.php', column_select_input('bug_list_columns_filter', $f_columns, false, true, basename(__FILE__))), 'class' => 'inline-page-link')),
				array('label' => 'Select Bulk Columns', 'data' => array('link' => format_href('columns_select_page.php', column_select_input('bug_list_columns_bulk', array(), true, true, basename(__FILE__))), 'class' => 'inline-page-link')),
				array('label' => 'Select Print/Export Columns', 'data' => array('link' => format_href('columns_select_page.php', column_select_input('bug_list_columns_export', array(), true, true, basename(__FILE__))), 'class' => 'inline-page-link')),
			);

			dropdown_menu('', $t_menu, '', '', 'dropdown-menu-right');
		echo '</div>';
	actionbar_end();

	bug_list_print($t_bug_ids, $f_columns, 'table-condensed table-hover table-datatable no-border');
echo '</form>';
column_end();


/* filter */
column_begin('3');

if(!$f_hide_filter){
	/* get current filter data */
	if($t_filter == null)
		$t_filter = current_user_get_bug_filter();

	$t_filter = filter_ensure_valid_filter($t_filter === false ? array() : $t_filter);
	$t_view_type = $t_filter['_view_type'];
	$t_view_filters = config_get('view_filters');

	/* get available filters */
	$t_filter_names = filter_get_list();
	$t_filter_selected = gpc_get_string('filter_selected', '');

	if(isset($t_filter['_source_query_id']))
		$t_filter_selected = filter_get_field($t_filter['_source_query_id'], 'name');

	/* actionbar */
	actionbar_begin();
		echo '<div class="pull-left">';

		/* apply button */
		// NOTE triggers the form with id 'filters_form_open' through javascript
		button('Apply', 'filter-apply-btn', 'button');

		/* reset button */
		echo '<form class="form-inline" method="get" name="reset_query" action="filter_apply.php">';
		input_hidden('type', 3);
		input_hidden('source_query_id', -1);
		button('Reset Filter', 'reset_query_button', 'submit');
		echo '</form>';

		/* available filter selection */
		if(count($t_filter_names) > 0){
			echo '<form id="filter-queries-form" class="form-inline"  method="get" name="list_queries_open" action="filter_apply.php">';
			input_hidden('type', 3);
			select('source_query_id', 'source_query_id', $t_filter_names, $t_filter_selected);
			echo '</form>';
		}

		echo '</div>';

		echo '<div class="pull-right">';

		/* dropdown: export, print, column selection */
		$t_menu = array();

		if(access_has_project_level(config_get('stored_query_create_threshold'))){
			$t_menu[] = array('label' => 'Save', 'data' => array('link' => format_href('settings/filter_update.php', array('cmd' => 'save', 'filter_name' => $t_filter_selected, 'filter_update_token' => form_security_token('filter_update'))), 'class' => 'inline-page-link'));
			$t_menu[] = array('label' => 'Save as', 'data' => array('link' => format_href('settings/filter_update.php', array('cmd' => 'save_as', 'filter_update_token' => form_security_token('filter_update'))), 'class' => 'inline-page-link'));
			$t_menu[] = array('label' => 'divider', 'data' => '');
		}

		if($t_view_filters != SIMPLE_ONLY && $t_view_filters != ADVANCED_ONLY){
			$t_action = config_get('use_dynamic_filters') ? 'filter_apply.php' : 'view_manage_filters_page.php';

			if($t_view_type == FILTER_VIEW_TYPE_SIMPLE)	$t_menu[] = array('label' => 'Advanced Filter', 'data' => array('link' => format_href($t_action, array('type' => 6, 'view_type' => FILTER_VIEW_TYPE_ADVANCED))));
			else										$t_menu[] = array('label' => 'Simple Filter', 'data' => array('link' => format_href($t_action, array('type' => 6, 'view_type' => FILTER_VIEW_TYPE_SIMPLE))));
		}

		dropdown_menu('', $t_menu, '', '', 'dropdown-menu-right');

		echo '</div>';

	actionbar_end();

	/* filter form */
	echo '<form method="post" name="filters_open" id="filters_form_open" action="filter_apply.php?f=3">';
		input_hidden('type', 1);
		input_hidden('view_type', $t_view_type);
		input_hidden('filter_selected', $t_filter_selected);

		text('filter-search-txt', FILTER_PROPERTY_SEARCH, string_attribute($t_filter[FILTER_PROPERTY_SEARCH]), 'Search', 'input-xs', 'width:100%!important');
		vspace('5px');
		filter_form_draw_inputs_column($t_filter);
	echo '</form>';
}

column_end();

layout_page_end();
