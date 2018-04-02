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

$g_allow_browser_cache = 1;

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
	global $t_bug;

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

		$t_value = string_custom_field_value($t_def, $t_id, $f_bug_id);
		table_row_bug_info_long($t_def['name'] . ':', format_input_hover_textarea('custom_field_' . $t_id, $t_value), '10%');
	}

	table_end();
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
		echo '<td class="no-border bug-header" width="30%">' . $p_key . '</td>';

	if($p_value)
		echo '<td class="no-border">' . $p_value . '</td>';

	echo '</tr>';
}

function table_row_bug_info_long($p_key, $p_value, $p_key_width = '50%'){
	echo '<tr>';

	if($p_key)
		echo '<td class="no-border bug-header" width="' . $p_key_width . '">' . $p_key . '</td>';

	if($p_value)
		echo '<td class="no-border">' . $p_value . '</td>';

	echo '</tr>';
}


compress_enable();

/* acquire form input */
$f_bug_id = gpc_get_int('id');


/* format bug data */
bug_ensure_exists($f_bug_id);
access_ensure_bug_level(config_get('view_bug_threshold'), $f_bug_id);

$t_bug = bug_get($f_bug_id, true);
$t_project_id = $t_bug->project_id;
$t_fields = config_get('bug_fields_show')['view'];
$t_date_format = config_get('normal_date_format');

$t_status_icon = '<i class="fa fa-square fa-status-box ' . html_get_status_css_class($t_bug->status) . '"></i> ';
$t_product_build = in_array('build', $t_fields) ? string_display_line($t_bug->build) : '';
$t_platform = in_array('platform', $t_fields) ? string_display_line($t_bug->platform) : '';
$t_bug_due_date = (access_has_bug_level(config_get('due_date_view_threshold'), $f_bug_id) && !date_is_null($t_bug->due_date)) ? date($t_date_format, $t_bug->due_date) : '';

$t_show_tags = in_array('tags', $t_fields) && access_has_global_level(config_get('tag_view_threshold'));
$t_show_history = gpc_get_bool('history', config_get('history_default_visible'));

// get user list
$t_users = project_get_all_user_rows($t_project_id);
$t_user_names = array(
	'[author]' => $t_bug->reporter_id,
	'[me]' => auth_get_current_user_id(),
	'[unassigned]' => 0,
);

foreach($t_users as $t_id => $t_user)
	$t_user_names[$t_user['username']] = $t_id;

ksort($t_user_names);

// get version list
$t_versions = version_get_all_rows((int)$t_project_id);
$t_versions_all = array();
$t_versions_unreleased = array();

foreach($t_versions as $t_version){
	$t_versions_all[$t_version['version']] = $t_version['id'];

	if($t_version['released'] == 0)
		$t_versions_unreleased[$t_version['version']] = $t_version['id'];
}

// get category list
$t_categories = category_get_all_rows($t_project_id);
$t_category_names = array();

foreach($t_categories as $t_category)
	$t_category_names[$t_category['name']] = $t_category['id'];

// get status list
$t_states = get_status_option_list(
	access_get_project_level($t_project_id),
	$t_bug->status,
	false,
	true,
	$t_project_id);

$t_state_names = array();

foreach($t_states as $t_id => $t_name)
	$t_state_names[$t_name] = $t_id;

// get priority list
$t_prios = MantisEnum::getValues(config_get('priority_enum_string'));
$t_prio_names = array();

foreach($t_prios as $t_id)
	$t_prio_names[get_enum_element('priority', $t_id)] = $t_id;

// get severity list
$t_severities = MantisEnum::getValues(config_get('severity_enum_string'));
$t_severity_names = array();

foreach($t_severities as $t_id)
	$t_severity_names[get_enum_element('severity', $t_id)] = $t_id;

// get view state list
$t_view_states = MantisEnum::getValues(config_get('view_state_enum_string'));
$t_view_state_names = array();

foreach($t_view_states as $t_id)
	$t_view_state_names[get_enum_element('view_state', $t_id)] = $t_id;

// generate list of attached tag links
$t_tags_attached = tag_bug_get_attached($f_bug_id);
$t_tag_links = '';

foreach($t_tags_attached as $t_tag){
	$t_sec_token = htmlspecialchars(form_security_param('tag_detach'));
	$t_link = format_link($t_tag['name'], 'tag_view_page.php', array('tag_id' => $t_tag['id']), '', 'margin-right:20px!important');
	$t_buttons = array(array('icon' => 'fa-times', 'href' => format_href('tag_detach.php', array('bug_id' => $f_bug_id, 'tag_id' => $t_tag['id'], $t_sec_token => '')), 'position' => 'right:4px'));

	$t_tag_links .= format_input_hover_element('tag_' . $t_tag['id'], $t_link, $t_buttons);
}

if(count($t_tags_attached) == 0)
	$t_tag_links = 'No tags attached' . format_hspace('20px');

// generate attach tag input
$t_tags_attachable = tag_get_candidates_for_bug($f_bug_id);
$t_tag_names = array('' => 0);

foreach($t_tags_attachable as $t_tag)
	$t_tag_names[$t_tag['name']] = $t_tag['id'];

$t_tag_attach = '<span id="tag_attach_div">';
$t_tag_attach .= form_security_field('tag_attach');
$t_tag_attach .= format_input_hidden('tag_separator', config_get('tag_separator'));
$t_tag_attach .= format_text('tag_string', 'tag_string', '', 'tags separated by \'' . config_get('tag_separator') . '\'');
$t_tag_attach .= format_hspace('5px');
$t_tag_attach .= format_select('tag_select', 'tag_select', $t_tag_names, '');
$t_tag_attach .= format_hspace('10px');
$t_tag_attach .= format_button('Attach', 'tag_attach_div-action-0', 'submit', 'tag_attach.php');
$t_tag_attach .= '</span>';


/* page header */
layout_page_header(bug_format_summary($f_bug_id, SUMMARY_CAPTION), null, 'view-issue-page');
layout_page_begin('view_all_bug_page.php');

page_title(bug_format_id($f_bug_id) . ' - ' . bug_format_summary($f_bug_id, SUMMARY_CAPTION));


echo '<form action="test_post.php" class="input-hover-form">';

input_hidden('bug_id', $f_bug_id);
input_hidden('last_updated', $t_bug->last_updated);

/* left column */
echo '<div class="col-md-9">';
	/* bug data */
	section_begin('Description');
		/* actionbar */
		actionbar_begin();
//		html_buttons_view_bug_page($f_bug_id);

		echo '<div class="pull-right">';
		button('Edit', 'input-hover-show-all');
		button('Reset', 'input-hover-reset-all');
		button('Update', 'input-hover-submit-all', 'submit');
		echo '</div>';

		actionbar_end();

		/* bug info */
		echo '<div class="row">';

		echo '<div class="col-md-3 no-padding">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Type:', format_input_hover_select('category_id', $t_category_names, category_get_name($t_bug->category_id)));
		table_row_bug_info_short('Status:', '<span>' . $t_status_icon . format_hspace('10px') . format_input_hover_select('status', $t_state_names, get_enum_element('status', $t_bug->status)) . '</span>');
		table_row_bug_info_short('Resolution:', get_enum_element('resolution', $t_bug->resolution));
		table_end();
		echo '</div>';

		echo '<div class="col-md-3 no-padding">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Priority:', format_input_hover_select('priority', $t_prio_names, get_enum_element('priority', $t_bug->priority)));
		table_row_bug_info_short('Severity:', format_input_hover_select('severity', $t_severity_names, get_enum_element('severity', $t_bug->severity)));
		table_end();
		echo '</div>';

		echo '<div class="col-md-3 no-padding">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Visibility:', format_input_hover_select('view_state', $t_view_state_names, get_enum_element('view_state', $t_bug->view_state)));
		table_end();
		echo '</div>';

		echo '</div>';
		echo '<hr>';

		/* description */
		echo '<div class="row">';
		table_begin('', 'no-border');
		table_row_bug_info_long('Description:', format_input_hover_text('summary', $t_bug->summary, '100%'), '7%');
		table_end();

		table_begin('', 'no-border');
		table_row_bug_info_long(' ', format_input_hover_textarea('description', $t_bug->description, '100%', '100px'), '1%');
		table_end();

		echo '</div>';
		echo '<hr>';

		/* tags */
		if($t_show_tags){
			echo '<div class="row">';
			table_begin('', 'no-border');
			table_row_bug_info_long('Tags:', $t_tag_links . $t_tag_attach, '5%');
			table_end();
			echo '</div>';
		}

		echo '<hr>';

		/* custom fields and tags */
		$t_tabs = array();
		$t_tabs['Custom Fields'] = 'tab_custom_fields';
		$t_tabs['Links'] = 'tab_links';
		$t_tabs['Monitored by'] = 'tab_monitored';

		tabs($t_tabs);

		## Bug Details Event Signal
		event_signal('EVENT_VIEW_BUG_DETAILS', array($f_bug_id));
	section_end();

	/* activities */
	section_begin('Activities');
		$t_tabs = array();
		$t_tabs['Bugnotes'] = 'tab_bugnote';

		if($t_show_history)
			$t_tabs['History'] = 'tab_history';

		tabs($t_tabs);
	section_end();
echo '</div>';


/* right column */
echo '<div class="col-md-3">';
	/* people */
	section_begin('People');
		echo '<div class="row">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Assignee:', format_input_hover_select('handler_id', $t_user_names, user_get_name($t_bug->handler_id)));
		table_row_bug_info_short('Author:', user_get_name($t_bug->reporter_id));
		table_end();
		echo '</div>';
	section_end();

	/* date and time */
	section_begin('Date and Time');
		echo '<div class="row">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Due Date:', format_input_hover_date('due_date', $t_bug_due_date));
		table_row_bug_info_short('Last Updated:', date($t_date_format, $t_bug->last_updated));
		table_row_bug_info_short('Date Submitted:', date($t_date_format, $t_bug->date_submitted));
		table_end();
		echo '</div>';
	section_end();

	/* version info */
	section_begin('Version Info');
		echo '<div class="row">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Fixed in Version:', format_input_hover_select('fixed_in_version', $t_versions_unreleased, $t_bug->fixed_in_version));
		table_row_bug_info_short('Target Version:', format_input_hover_select('target_version', $t_versions_unreleased, $t_bug->target_version));
		table_row_bug_info_short('Affected Version:', format_input_hover_select('version', $t_versions_all, $t_bug->version));
		table_row_bug_info_short(' ', '');
		table_row_bug_info_short(' ', '');
		table_row_bug_info_short('Product Build:', format_input_hover_text('build', $t_product_build));
		table_row_bug_info_short('Platform:', format_input_hover_text('platform', $t_platform));
		table_row_bug_info_short('OS:', format_input_hover_text('os', $t_bug->os));
		table_row_bug_info_short('OS Version:', format_input_hover_text('os_build', $t_bug->os_build));
		table_end();
		echo '</div>';
	section_end();
echo '</div>';
echo '</form>';


/* page footer */
layout_page_end();
