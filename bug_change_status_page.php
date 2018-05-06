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
 * Handling of Bug Status change
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses relationship_api.php
 * @uses version_api.php
 */

$g_allow_browser_cache = 1;

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('custom_field_api.php');
require_api('date_api.php');
require_api('event_api.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('relationship_api.php');
require_api('version_api.php');
require_api('elements_api.php');


$f_bug_id = gpc_get_int('id');
$t_bug = bug_get($f_bug_id);

$t_file = __FILE__;
$t_mantis_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$t_show_page_header = false;
$t_force_readonly = true;

if($t_bug->project_id != helper_get_current_project()) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

$f_new_status = gpc_get_int('new_status');
$f_old_status = $t_bug->status;
$f_change_type = gpc_get_string('change_type', BUG_UPDATE_TYPE_CHANGE_STATUS);

$t_reopen = config_get('bug_reopen_status', null, null, $t_bug->project_id);
$t_resolved = config_get('bug_resolved_status_threshold', null, null, $t_bug->project_id);
$t_closed = config_get('bug_closed_status_threshold', null, null, $t_bug->project_id);
$t_resolution_fixed = config_get('bug_resolution_fixed_threshold', null, null, $t_bug->project_id);
$t_project_id = $t_bug->project_id;
$t_current_user_id = auth_get_current_user_id();
$t_versions_unreleased = version_list($t_project_id, false);

# Ensure user has proper access level before proceeding
if($f_new_status == $t_reopen && $f_change_type == BUG_UPDATE_TYPE_REOPEN) {
	access_ensure_can_reopen_bug($t_bug, $t_current_user_id);
} else if($f_new_status == $t_closed) {
	access_ensure_can_close_bug($t_bug, $t_current_user_id);
} else if(bug_is_readonly($f_bug_id)
	|| !access_has_bug_level(access_get_status_threshold($f_new_status, $t_bug->project_id), $f_bug_id, $t_current_user_id)) {
	access_denied();
}

$t_can_update_due_date = access_has_bug_level(config_get('due_date_update_threshold'), $f_bug_id);

# get new issue handler if set, otherwise default to original handler
$f_handler_id = gpc_get_int('handler_id', $t_bug->handler_id);

if(config_get('bug_assigned_status') == $f_new_status) {
	if($f_handler_id != NO_USER) {
		if(!access_has_bug_level(config_get('handle_bug_threshold'), $f_bug_id, $f_handler_id)) {
			trigger_error(ERROR_HANDLER_ACCESS_TOO_LOW, ERROR);
		}
	}
}

$t_status_label = str_replace(' ', '_', MantisEnum::getLabel(config_get('status_enum_string'), $f_new_status));


if($f_old_status == $f_new_status){
	json_prepare();
	json_warning('No target status selected');
	json_commit();
	return;
}

if($f_new_status >= $t_resolved && !relationship_can_resolve_bug($f_bug_id))
	json_error('Not all children of this issue are yet resolved or closed');


####
## check state transition
####

$state_transition = $f_old_status . '_to_' . $f_new_status;

$t_fields = config_get('bug_fields_show')[$state_transition];

$t_required_fields = config_get('bug_fields_required')[$state_transition];


$t_show_priority = in_array('priority', $t_fields) || in_array('priority', $t_required_fields);
$t_show_severity = in_array('severity', $t_fields) || in_array('severity', $t_required_fields);
$t_show_notes = in_array('notes', $t_fields) || in_array('notes', $t_required_fields);
$t_show_time_tracking = in_array('time_tracking', $t_fields) || in_array('time_tracking', $t_required_fields);


$t_show_assignee = in_array('handler_id', $t_fields) || in_array('handler_id', $t_required_fields);
$t_show_due_date = in_array('due_date', $t_fields) || in_array('due_date', $t_required_fields);
$t_show_resolution = in_array('resolution', $t_fields) || in_array('resolution', $t_required_fields);
$t_show_fixed_in_version = in_array('fixed_in_version', $t_fields) || in_array('fixed_in_version', $t_required_fields);
$t_show_target_version = in_array('target_version', $t_fields) || in_array('target_version', $t_required_fields);

$t_reset_assignee = 0;
$t_reset_resolution = 0;

if($f_old_status == STATUS_INREVIEW && $f_new_status == STATUS_INDEVELOPMENT){
	$t_reset_resolution = 1;
}
else if($f_new_status == STATUS_OPEN){
	$t_reset_resolution = 1;
	$t_reset_assignee = 1;
}


####
## generate form
####

layout_inline_page_begin();
page_title(lang_get($t_status_label . '_bug_title'));

echo '<div class="col-md-12">';
echo '<form method="post" action="bug_update.php" class="input-hover-form inline-page-form input-hover-form-reload">';
	echo form_security_field('bug_update');

	input_hidden('status_change_enabled', '1');
	input_hidden('bug_id', $f_bug_id);
	input_hidden('new_status', $f_new_status);
	input_hidden('old_status', $f_old_status);
	input_hidden('reset_resolution', $t_reset_resolution);
	input_hidden('reset_assignee', $t_reset_assignee);
	input_hidden('status', $f_new_status);
	input_hidden('last_updated', $t_bug->last_updated);
	input_hidden('action_type', string_attribute($f_change_type));

	actionbar_begin();
		echo '<div class="pull-left">';
		button(lang_get($t_status_label . '_bug_button'), 'state-change-submit', 'submit');
		echo '</div>';
	actionbar_end();

	table_begin(array(), 'no-border');
		// priority
		if($t_show_priority){
			$t_required = format_required_indicator('priority', $t_required_fields);
			table_row_bug_info_long($t_required . 'Priority:', format_select('priority', 'priority', priority_list(), get_enum_element('priority', $t_bug->priority)), '10%');
		}

		// severity
		if($t_show_severity){
			$t_required = format_required_indicator('severity', $t_required_fields);
			table_row_bug_info_long($t_required . 'Severity:', format_select('severity', 'severity', severity_list(), get_enum_element('severity', $t_bug->severity)), '10%');
		}

		// assignee
		if($t_show_assignee && access_has_bug_level(config_get('update_bug_assign_threshold', config_get('update_bug_threshold')), $f_bug_id)){
			$t_required = format_required_indicator('handler_id', $t_required_fields);
			$t_suggested_handler_id = $t_bug->handler_id;

			if($t_suggested_handler_id == NO_USER && access_has_bug_level(config_get('handle_bug_threshold'), $f_bug_id))
				$t_suggested_handler_id = $t_current_user_id;

			table_row_bug_info_long($t_required . 'Assignee:', format_select('handler_id', 'handler_id', user_list($t_project_id, $t_bug->reporter_id, true), user_get_name($t_bug->handler_id)), '10%');
		}

		// target version
		if($t_show_target_version){
			$t_required = format_required_indicator('target_version', $t_required_fields);
			table_row_bug_info_long($t_required . 'Target Version:', format_select('target_version', 'target_version', $t_versions_unreleased, $t_bug->target_version), '10%');
		}

		// fixed version
		if($t_show_fixed_in_version){
			$t_required = format_required_indicator('fixed_in_version', $t_required_fields);
			table_row_bug_info_long($t_require . 'Fixed in Version:', format_select('fixed_in_version', 'fixed_in_version', $t_versions_unreleased, $t_bug->fixed_in_version), '10%');
		}

		// due date
		if($t_show_due_date && $t_can_update_due_date){
			$t_required = format_required_indicator('due_date', $t_required_fields);
			$t_date_to_display = '';

			if(!date_is_null($t_bug->due_date))
				$t_date_to_display = date(config_get('normal_date_format'), $t_bug->due_date);

			table_row_bug_info_long($t_required . 'Due Date:', format_date('due_date', 'due_date', $t_date_to_display), '10%');
		}

		// resolution
		if($t_show_resolution){
			$t_required = format_required_indicator('resolution', $t_required_fields);

			$t_resolution = ($t_bug->resolution >= $t_resolution_fixed)? $t_bug->resolution : $t_resolution_fixed;
			$t_relationships = relationship_get_all_src($f_bug_id);
			foreach($t_relationships as $t_relationship) {
				if($t_relationship->type == BUG_DUPLICATE) {
					$t_resolution = config_get('bug_duplicate_resolution');
					break;
				}
			}

			table_row_bug_info_long($t_required . 'Resolution:', format_select('resolution', 'resolution', resolution_list(), get_enum_element('resolution', $t_resolution)), '10%');
		}

		// time tracking
		if($t_show_time_tracking && config_get('time_tracking_enabled') && access_has_bug_level(config_get('time_tracking_edit_threshold'), $f_bug_id)){
			$t_required = format_required_indicator('time_tracking', $t_required_fields);
			table_row_bug_info_long($t_required . 'Work Log:', format_text('time_tracking', 'time_tracking', '', 'hh:mm', 'input-xs', '', 'size=5'), '10%');
		}

		// custom fields
		$t_related_custom_field_ids = custom_field_get_linked_ids($t_bug->project_id);
		$custom_fields_show = config_get('bug_custom_fields_show')[$state_transition];
		$custom_fields_required = config_get('bug_custom_fields_required')[$state_transition];

		foreach($t_related_custom_field_ids as $t_id){
			// check if the field is required for the current state transition
			$field_name = custom_field_get_field($t_id, 'name');

			if(!in_array($field_name, $custom_fields_show) && !in_array($field_name, $custom_fields_required))
				continue;

			// display field
			$t_def = custom_field_get_definition($t_id);
			$t_required = format_required_indicator($t_def['name'], $custom_fields_required);
			$t_value = custom_field_get_value($t_def, $f_bug_id);

			// TODO use print_custom_field_input() for displaying custom fields
			table_row_bug_info_long($t_required . lang_get_defaulted($t_def['name']), format_textarea('custom_field_' . $t_id, 'custom_field_' . $t_id, $t_value, 'input-xs', 'width:100% !important;height:100px;'), '10%');
			input_hidden('custom_field_' . $t_id . '_presence', 0);
		}

		// notes
		if($t_show_notes){
			$t_required = format_required_indicator('notes', $t_required_fields);
			table_row_bug_info_long($t_required . 'Note:', format_textarea('bugnote_text', 'bugnote_text', '', 'input-xs', 'width:100% !important;height:100px;'), '10%');
		}

		// required inidicator
		table_row_bug_info_long(' ', '<span class="required pull-right"> * required</span>', '10%');

	table_end();

	event_signal('EVENT_UPDATE_BUG_STATUS_FORM', array($f_bug_id, $f_new_status));
	event_signal('EVENT_BUGNOTE_ADD_FORM', array($f_bug_id));
echo '</form>';
echo '</div>';


layout_inline_page_end();

last_visited_issue($f_bug_id);
