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
 * View Bug
 *
 * @package MantisBT
 * @copyright Copyright 2018 Jan Nowotsch jan.nowotsch@gmail.com
 */

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('category_api.php');
require_api('columns_api.php');
require_api('compress_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('current_user_api.php');
require_api('custom_field_api.php');
require_api('date_api.php');
require_api('event_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('last_visited_api.php');
require_api('prepare_api.php');
require_api('print_api.php');
require_api('project_api.php');
require_api('string_api.php');
require_api('tag_api.php');
require_api('utility_api.php');
require_api('version_api.php');
require_api('elements_api.php');

require_css('status_config.php');


/* callback to render custom fields */
function tab_custom_fields(){
	global $f_bug_id;

	$t_related_custom_field_ids = custom_field_get_linked_ids($t_bug->project_id);
	$custom_fields_show = config_get('bug_custom_fields_show')['view'];

	custom_field_cache_values(array($t_bug->id) , $t_related_custom_field_ids);

	table_begin('', 'no-border');

	foreach($t_related_custom_field_ids as $t_id) {
		if(!custom_field_has_read_access($t_id, $f_bug_id)) {
			continue;
		}

		$t_def = custom_field_get_definition($t_id);

		# ignore field if it shall not be shown
		if(!in_array($t_def['name'], $custom_fields_show)){
			continue;
		}

		table_row_bug_info_long(string_display($t_def['name']) . ':', string_custom_field_value($t_def, $t_id, $f_bug_id));
	}

	table_end();
}

/* callback to render tags */
function tab_tags(){
	global $f_bug_id;
	tag_display_attached($f_bug_id);
}

/* callback to render relationships */
function tab_links(){
	global $t_bug;
	relationship_view_box($t_bug->id);
}

/* callback to render monitoring users */
function tab_monitored(){
	global $f_bug_id;

	define('BUG_MONITOR_LIST_VIEW_INC_ALLOW', true);
	include('bug_monitor_list_view_inc.php');
}

/* callback to render bugnotes */
function tab_bugnote(){
	global $f_bug_id;

	define('BUGNOTE_ADD_INC_ALLOW', true);
	include('bugnote_add_inc.php');

	define('BUGNOTE_VIEW_INC_ALLOW', true);
	include('bugnote_view_inc.php');

	/* allow plugins to display stuff after notes */
	event_signal('EVENT_VIEW_BUG_EXTRA', array($f_bug_id));
}

/* callback to render history */
function tab_history(){
	global $f_bug_id;

	define('HISTORY_INC_ALLOW', true);
	include('history_inc.php');
}

/**
 *	print a table row formated for bug information, that is with
 *	heading and data and defined cell sizes
 *
 *	@param	string	$p_key		the heading cell content
 *	@param	string	$p_value	the value cell content
 *
 *	@return	nothing
 */
function table_row_bug_info_short($p_key, $p_value){
	echo '<tr>';
	if($p_key)
		echo '<td class="no-border bug-header" width="45%">' . $p_key . '</td>';

	if($p_value)
		echo '<td class="no-border" width="55%">' . $p_value . '</td>';

	echo '</tr>';
}

function table_row_bug_info_long($p_key, $p_value){
	echo '<tr>';

	if($p_key)
		echo '<td class="no-border bug-header">' . $p_key . '</td>';

	if($p_value)
		echo '<td class="no-border">' . $p_value . '</td>';

	echo '</tr>';
}



compress_enable();

/* acquire config values */
$t_date_format = config_get('normal_date_format');


/* acquire form input */
$f_bug_id = gpc_get_int('id');


/* prepare bug data */
bug_ensure_exists($f_bug_id);
access_ensure_bug_level(config_get('view_bug_threshold'), $f_bug_id);

$t_bug = bug_get($f_bug_id, true);
$t_fields = config_get('bug_fields_show')['view'];

$t_status_icon = '<i class="fa fa-square fa-status-box ' . html_get_status_css_class($t_bug->status) . '"></i> ';
$t_status_string = string_display_line(get_enum_element('status', $t_bug->status));
$t_product_version = in_array('version', $t_fields) ? string_display_line(prepare_version_string($t_bug->project_id, version_get_id($t_bug->version, $t_bug->project_id))) : '';
$t_fixed_in_version = in_array('fixed_in_version', $t_fields) ? string_display_line(prepare_version_string($t_bug->project_id, version_get_id($t_bug->fixed_in_version, $t_bug->project_id))) : '';
$t_product_build = in_array('build', $t_fields) ? string_display_line($t_bug->build) : '';
$t_target_version_string = in_array('target_version', $t_fields) && access_has_bug_level(config_get('roadmap_view_threshold'), $f_bug_id) ? string_display_line(prepare_version_string($t_bug->project_id, version_get_id($t_bug->target_version, $t_bug->project_id))) : '';
$t_platform = in_array('platform', $t_fields) ? string_display_line($t_bug->platform) : '';
$t_os = in_array('os', $t_fields) ? string_display_line($t_bug->os) : '';
$t_os_version = in_array('os', $t_fields) ? string_display_line($t_bug->os_build) : '';
$t_bug_view_state_enum = in_array('view_state', $t_fields) ? string_display_line(get_enum_element('view_state', $t_bug->view_state)) : '';
$t_bug_due_date = (access_has_bug_level(config_get('due_date_view_threshold'), $f_bug_id) && !date_is_null($t_bug->due_date)) ? date($t_date_format, $t_bug->due_date) : '';

$t_show_tags = in_array('tags', $t_fields) && access_has_global_level(config_get('tag_view_threshold'));
$t_show_history = gpc_get_bool('history', config_get('history_default_visible'));



/* page header */
layout_page_header(bug_format_summary($f_bug_id, SUMMARY_CAPTION), null, 'view-issue-page');
layout_page_begin('view_all_bug_page.php');

page_title(bug_format_id($f_bug_id) . ' - ' . bug_format_summary($f_bug_id, SUMMARY_CAPTION));


/* left column */
echo '<div class="col-md-10">';
	/* bug data */
	section_begin('Description');
		/* actionbar */
		actionbar_begin();
		html_buttons_view_bug_page($f_bug_id);
		print_tag_attach_form($f_bug_id);
		actionbar_end();

		/* bug info */
		echo '<div class="row subsection">';

		echo '<div class="col-md-2 no-padding">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Type:', string_display_line(category_full_name($t_bug->category_id)));

		table_row_bug_info_short('Status:', $t_status_icon . ' ' . $t_status_string);
		table_row_bug_info_short('Resolution:', string_display_line(get_enum_element('resolution', $t_bug->resolution)));
		table_end();
		echo '</div>';

		echo '<div class="col-md-2 no-padding">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Priority:', string_display_line(get_enum_element('priority', $t_bug->priority)));
		table_row_bug_info_short('Severity:', string_display_line(get_enum_element('severity', $t_bug->severity)));
		table_end();
		echo '</div>';

		echo '<div class="col-md-2 no-padding">';
		table_begin('', 'no-border');
		table_row_bug_info_short('View Status:', $t_bug_view_state_enum);
		table_end();
		echo '</div>';

		echo '</div>';

		/* description */
		echo '<div class="row subsection">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Description:', '');
		table_row_bug_info_short('', bug_format_summary($f_bug_id, SUMMARY_CAPTION));
		table_row_bug_info_short('', string_display_links($t_bug->description));
		table_end();
		echo '</div>';

		/* tags */
		if($t_show_tags){
			ob_start();
			tag_display_attached($f_bug_id);

			$t_tags = ob_get_contents();
			ob_end_clean();

			echo '<div class="row subsection">';
			table_begin('', 'no-border');
			table_row_bug_info_short('Tags:', '');
			table_row_bug_info_short('', $t_tags);
			table_end();
			echo '</div>';
		}

		/* custom fields and tags */
		$t_tabs = array();
		$t_tabs['Custom Fields'] = 'tab_custom_fields';
		$t_tabs['Links'] = 'tab_links';
		$t_tabs['Monitored by'] = 'tab_monitored';

		tabs($t_tabs);

		## Bug Details Event Signal
		event_signal('EVENT_VIEW_BUG_DETAILS', array($f_bug_id));
	section_end();

	/* bugnotes */
	section_begin('Activities');
		$t_tabs = array();
		$t_tabs['Bugnotes'] = 'tab_bugnote';

		if($t_show_history)
			$t_tabs['History'] = 'tab_history';

		tabs($t_tabs);
	section_end();
echo '</div>';


/* right column */
echo '<div class="col-md-2">';
	/* people */
	section_begin('People');
		table_begin('', 'no-border');
		table_row_bug_info_short('Assignee:', user_get_name($t_bug->handler_id));
		table_row_bug_info_short('Author:', user_get_name($t_bug->reporter_id));
		table_end();
	section_end();

	/* date and time */
	section_begin('Date and Time');
		table_begin('', 'no-border');
		table_row_bug_info_short('Due Date:', $t_bug_due_date);
		table_row_bug_info_short('Last Updated:', date($t_date_format, $t_bug->last_updated));
		table_row_bug_info_short('Date Submitted:', date($t_date_format, $t_bug->date_submitted));
		table_end();
	section_end();

	/* version info */
	section_begin('Version Info');
		table_begin('', 'no-border');
		table_row_bug_info_short('Fixed in Version:', $t_fixed_in_version);
		table_row_bug_info_short('Target Version:', $t_target_version_string);
		table_row_bug_info_short(' ', '');
		table_row_bug_info_short(' ', '');
		table_row_bug_info_short('Product Build:', $t_product_build);
		table_row_bug_info_short('Product Version:', $t_product_version);
		table_row_bug_info_short('Platform:', $t_platform);
		table_row_bug_info_short('OS Version:', $t_os  . ' ' . ($t_os ? ' / ' . $t_os_version : ''));

		table_end();
	section_end();
echo '</div>';


/* page footer */
layout_page_end();
