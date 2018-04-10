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
require_api('user_api.php');

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
		if(!custom_field_has_read_access($t_id, $f_bug_id))
			continue;

		$t_def = custom_field_get_definition($t_id);

		# ignore field if it shall not be shown
		if(!in_array($t_def['name'], $custom_fields_show))
			continue;

		$t_value = custom_field_get_value($t_def, $f_bug_id);
		// TODO use print_custom_field_input() for displaying custom fields
		table_row_bug_info_long($t_def['name'] . ':', format_input_hover_textarea('custom_field_' . $t_id, $t_value), '10%');
		input_hidden('custom_field_' . $t_id . '_presence', 0);
	}

	table_end();
}

/* callback to render relationships */
function tab_links(){
	global $t_bug;
//	relationship_view_box($t_bug->id);
}

/* callback to render monitoring users */
function tab_monitored(){
	global $f_bug_id;

	define('BUG_MONITOR_LIST_VIEW_INC_ALLOW', true);
//	include('bug_monitor_list_view_inc.php');
}

/* callback to render bugnotes */
function tab_bugnote(){
	global $f_bug_id;

	define('BUGNOTE_ADD_INC_ALLOW', true);
//	include('bugnote_add_inc.php');

	define('BUGNOTE_VIEW_INC_ALLOW', true);
//	include('bugnote_view_inc.php');

	/* allow plugins to display stuff after notes */
	event_signal('EVENT_VIEW_BUG_EXTRA', array($f_bug_id));
}

/* callback to render history */
function tab_history(){
	global $f_bug_id;

	define('HISTORY_INC_ALLOW', true);
//	include('history_inc.php');
}


compress_enable();

/* acquire form input */
$f_bug_id = gpc_get_int('id');
$t_show_history = gpc_get_bool('history', config_get('history_default_visible'));


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
$t_versions_unreleased = version_list($t_project_id, false);
$t_versions_all = version_list($t_project_id, true);

$t_show_tags = in_array('tags', $t_fields) && access_has_global_level(config_get('tag_view_threshold'));

/* generate list of attached tag links */
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

/* generate attach tag input */
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


echo '<form action="bug_update.php" method="post" class="input-hover-form input-hover-form-noreload">';

input_hidden('id', $f_bug_id);
input_hidden('bug_id', $f_bug_id);
input_hidden('last_updated', $t_bug->last_updated);

form_security_purge('bug_update');
echo form_security_field('bug_update');

/* left column */
echo '<div class="col-md-9">';
	/* bug data */
	section_begin('Description');
		/* actionbar */
		actionbar_begin();
			echo '<div class="pull-left">';

			// edit button
			if(access_has_bug_level(config_get('update_bug_threshold'), $f_bug_id)){
				button('Edit', 'input-hover-show-all'); 
				button('Reset', 'input-hover-reset-all');
				button('Update', 'input-hover-submit-all', 'submit');
			}

			// clone button
			if(access_has_bug_level(config_get('report_bug_threshold'), $f_bug_id))
				button_link('Clone', 'bug_report_page.php', array('id' => $f_bug_id));

			// move button
			if(!bug_is_readonly($f_bug_id) && config_get('view_issue_button_move'))
				button_link('Move', 'bug_actiongroup_page.php', array('action' => 'MOVE', 'bug_arr[]' => $f_bug_id, 'bug_actiongroup_page_token' => form_security_token('bug_actiongroup_page')));

			// monitor buttons
			if(!current_user_is_anonymous() && access_has_bug_level( config_get('monitor_bug_threshold'), $f_bug_id)){
				if(user_is_monitoring_bug(auth_get_current_user_id(), $f_bug_id))
					button_link('Unmonitor', 'bug_monitor_delete.php', array('bug_id' => $f_bug_id, 'bug_monitor_delete_token' => form_security_token('bug_monitor_delete')));
				else
					button_link('Monitor', 'bug_monitor_add.php', array('bug_id' => $f_bug_id, 'bug_monitor_add_token' => form_security_token('bug_monitor_add')));
			}

			echo '</div>';
			echo '<div class="pull-right">';

			// delete button
			if(!bug_is_readonly($f_bug_id) && config_get('view_issue_button_delete'))
				button_link('Delete', 'bug_actiongroup_page.php', array('action' => 'DELETE', 'bug_arr[]' => $f_bug_id, 'bug_actiongroup_page_token' => form_security_token('bug_actiongroup_page')));

			echo '</div>';
		actionbar_end();

		/* bug info */
		echo '<div class="row">';

		echo '<div class="col-md-3 no-padding">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Type:', format_input_hover_select('category_id', category_list($t_project_id), category_get_name($t_bug->category_id)));
		table_row_bug_info_short('Status:', '<span>' . $t_status_icon . format_hspace('10px') . format_input_hover_select('new_status', bug_status_list($t_project_id, $t_bug->status), get_enum_element('status', $t_bug->status), 'bug_change_status_page.php') . '</span>');
		table_row_bug_info_short('Resolution:', format_input_hover_select('resolution', resolution_list(), get_enum_element('resolution', $t_bug->resolution)));
		table_end();
		echo '</div>';

		echo '<div class="col-md-3 no-padding">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Priority:', format_input_hover_select('priority', priority_list(), get_enum_element('priority', $t_bug->priority)));
		table_row_bug_info_short('Severity:', format_input_hover_select('severity', severity_list(), get_enum_element('severity', $t_bug->severity)));
		table_end();
		echo '</div>';

		echo '<div class="col-md-3 no-padding">';
		table_begin('', 'no-border');
		table_row_bug_info_short('Visibility:', format_input_hover_select('view_state', view_status_list(), get_enum_element('view_state', $t_bug->view_state)));
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
		table_row_bug_info_short('Assignee:', format_input_hover_select('handler_id', user_list($t_project_id, $t_bug->reporter_id), user_get_name($t_bug->handler_id)));
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

last_visited_issue($f_bug_id);
