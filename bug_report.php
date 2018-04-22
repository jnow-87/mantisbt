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
 * This page stores the reported bug
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
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses last_visited_api.php
 * @uses print_api.php
 * @uses profile_api.php
 * @uses relationship_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('custom_field_api.php');
require_api('date_api.php');
require_api('email_api.php');
require_api('error_api.php');
require_api('event_api.php');
require_api('file_api.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('history_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('last_visited_api.php');
require_api('print_api.php');
require_api('profile_api.php');
require_api('relationship_api.php');
require_api('string_api.php');
require_api('user_api.php');
require_api('utility_api.php');


form_security_validate('bug_report');

/* prepare response */
$f_resp_type = gpc_get_int('resp_type', RESP_JSON);

json_prepare();

if($f_resp_type == RESP_HTML){
	layout_inline_page_begin();
	page_title('Create Issue Result');
}

/* get project id */
$t_project_id = null;

$f_master_bug_id = gpc_get_int('id', 0);

if($f_master_bug_id > 0){
	bug_ensure_exists($f_master_bug_id);

	if(bug_is_readonly($f_master_bug_id))
		report_error('Access denied to readonly issue', '', $f_resp_type);

	$t_master_bug = bug_get($f_master_bug_id, true);
	$t_project_id = $t_master_bug->project_id;
}
else
	$t_project_id = gpc_get_int('project_id');

project_ensure_exists($t_project_id);

if($t_project_id != helper_get_current_project())
	$g_project_override = $t_project_id;

access_ensure_project_level(config_get('report_bug_threshold'));

if(isset($_GET['posted']) && empty($_FILE) && empty($_POST)){
	report_error(
		'File upload failed. This is likely because the filesize was '
		. 'larger than is currently allowed by this PHP installation',
		'',
		$f_resp_type
	);
}

/* create bug instance -- either clone from master or create new one */
$t_bug = new BugData;

if($t_bug == null)
	report_error('null pointer', '', $f_resp_type);

if($f_master_bug_id > 0){
	$t_bug->status = config_get('bug_submit_status');
	$t_bug->resolution = config_get('default_bug_resolution');

	$t_bug->project_id = $t_master_bug->project_id;
	$t_bug->reporter_id = $t_master_bug->reporter_id;
	$t_bug->build = $t_master_bug->build;
	$t_bug->platform = $t_master_bug->platform;
	$t_bug->os = $t_master_bug->os;
	$t_bug->os_build = $t_master_bug->os_build;
	$t_bug->version = $t_master_bug->version;
	$t_bug->profile_id = $t_master_bug->profile_id;
	$t_bug->handler_id = $t_master_bug->handler_id;
	$t_bug->view_state = $t_master_bug->view_state;
	$t_bug->category_id = $t_master_bug->category_id;
	$t_bug->severity = $t_master_bug->severity;
	$t_bug->priority = $t_master_bug->priority;
	$t_bug->summary = 'Clone of ' .  bug_format_id($f_master_bug_id) . ': ' . $t_master_bug->summary;
	$t_bug->description = $t_master_bug->description;
	$t_bug->due_date = $t_master_bug->due_date;

	$f_tag_select = 0;
	$f_tag_string = tag_bug_get_all($f_master_bug_id);
}
else{
	$t_bug->status = config_get('bug_submit_status');
	$t_bug->resolution = config_get('default_bug_resolution');
	$t_bug->project_id = $t_project_id;
	$t_bug->reporter_id = auth_get_current_user_id();

	$t_bug->build = gpc_get_string('build', '');
	$t_bug->platform = gpc_get_string('platform', '');
	$t_bug->os = gpc_get_string('os', '');
	$t_bug->os_build = gpc_get_string('os_build', '');
	$t_bug->version = gpc_get_string('product_version', '');
	$t_bug->profile_id = gpc_get_int('profile_id', 0);
	$t_bug->handler_id = gpc_get_int('handler_id', 0);
	$t_bug->view_state = gpc_get_int('view_state', config_get('default_bug_view_status'));
	$t_bug->category_id = gpc_get_int('category_id', 0);
	$t_bug->severity = gpc_get_int('severity', config_get('default_bug_severity'));
	$t_bug->priority = gpc_get_int('priority', config_get('default_bug_priority'));
	$t_bug->summary = gpc_get_string('summary');
	$t_bug->description = gpc_get_string('description');
	$t_bug->due_date = gpc_get_string('due_date', date_strtotime(config_get('due_date_default')));

	$f_tag_select = gpc_get_int('tag_select', 0);
	$f_tag_string = gpc_get_string('tag_string', '');

	if(access_has_project_level(config_get('roadmap_update_threshold'), $t_bug->project_id))
		$t_bug->target_version = gpc_get_string('target_version', '');

	// if a profile was selected then let's use that information
	if($t_bug->profile_id != 0){
		if(profile_is_global($t_bug->profile_id))
			$t_row = user_get_profile_row(ALL_USERS, $t_bug->profile_id);
		else
			$t_row = user_get_profile_row($t_bug->reporter_id, $t_bug->profile_id);

		if(is_blank($t_bug->platform))
			$t_bug->platform = $t_row['platform'];

		if(is_blank($t_bug->os))
			$t_bug->os = $t_row['os'];

		if(is_blank($t_bug->os_build))
			$t_bug->os_build = $t_row['os_build'];
	}
}

/* bug data validation */
// Prevent unauthorized users setting handler when reporting issue
if($t_bug->handler_id > 0)
	access_ensure_project_level(config_get('update_bug_assign_threshold'));

helper_call_custom_function('issue_create_validate', array($t_bug));

// check required fields
$t_res = $t_bug->check_fields_builtin('report');

if($t_res != '')
	report_error($t_res, '', $f_resp_type);

$t_res = $t_bug->check_fields_custom('report');

if($t_res != '')
	report_error($t_res, '', $f_resp_type);


/* Validate custom fields before adding the bug */
$t_related_custom_field_ids = custom_field_get_linked_ids($t_bug->project_id);

foreach($t_related_custom_field_ids as $t_id){
	$t_def = custom_field_get_definition($t_id);

	// check if requried fields are present
	if(!gpc_isset_custom_field($t_id, $t_def['type']) && $t_def['require_report'])
		report_error('Required field \'' . lang_get_defaulted(custom_field_get_field($t_id, 'name')) . '\' is empty', '', $f_resp_type);

	if(!custom_field_validate($t_id, gpc_get_custom_field('custom_field_' . $t_id, $t_def['type'], null)))
		report_error('Invalid value for \'' . lang_get_defaulted(custom_field_get_field($t_id, 'name')) . '\'', '', $f_resp_type);
}

/* Create the bug */
// allow plugins to pre-process bug data
$t_bug = event_signal('EVENT_REPORT_BUG_DATA', $t_bug);

// create bug
if($t_bug == null)
	report_error('2\'nd null pointer', '', $f_resp_type);

$t_bug_id = $t_bug->create();
$t_bug->process_mentions();

// process tags
if(!is_blank($f_tag_string) || $f_tag_select != 0){
	$t_tags_failed = tag_attach_many($t_bug_id, $f_tag_string, $f_tag_select);

	if($t_tags_failed !== true){
		$t_msg = '';

		foreach($p_tags_failed as $t_tag_row){
			$t_tag_name = string_html_specialchars($t_tag_row['name']);

			if($t_tag_row['id'] == -1 )
				$t_error = 'access denied';
			else if($t_tag_row['id'] == -2)
				$t_error = 'invalid tag name';
			else
				$t_error = 'unknown';

			$t_msg .= $t_tag_name . ' (' . $t_error . ') ';
		}

		report_warning('Some tags have not been attached: ' . $t_msg, '', $f_resp_type);
	}
}

// handle custom field submission
foreach($t_related_custom_field_ids as $t_id){
	// Do not set custom field value if user has no write access
	if(!custom_field_has_write_access($t_id, $t_bug_id))
		continue;

	$t_def = custom_field_get_definition($t_id);

	if(!custom_field_set_value($t_id, $t_bug_id, gpc_get_custom_field('custom_field_' . $t_id, $t_def['type'], $t_def['default_value']), false))
		report_error('Error setting custom field value for \'' . lang_get_defaulted(custom_field_get_field($t_id, 'name')) . '\'', '', $f_resp_type);
}

// add relation to master bug
if($f_master_bug_id > 0){
	relationship_add($t_bug_id, $f_master_bug_id, BUG_BLOCKS, false);

	// update master bug last updated
	bug_update_date($f_master_bug_id);

	// add log line to record the cloning action
	history_log_event_special($t_bug_id, BUG_CREATED_FROM, '', $f_master_bug_id);
	history_log_event_special($f_master_bug_id, BUG_CLONED_TO, '', $t_bug_id);
}

// make added issue appear in the last visited list
last_visited_issue($t_bug_id);

// call helper
helper_call_custom_function('issue_create_notify', array($t_bug_id));

// allow plugins to post-process bug data with the new bug ID
event_signal('EVENT_REPORT_BUG', array($t_bug, $t_bug_id));

email_bug_added($t_bug_id);


/* response */
if($f_resp_type != RESP_JSON)
	form_security_purge('bug_report');

$t_bug_link = format_link(bug_format_id($t_bug_id), 'view.php', array('id' => $t_bug_id));
$t_reload_link = ($f_master_bug_id > 0 ? format_link('Reload', 'view.php', array('id' => $f_master_bug_id)) : '');

report_success('Created issue ' . $t_bug_link	. format_hspace('15px') . $t_reload_link, '', $f_resp_type);

if($f_resp_type == RESP_HTML)
	layout_inline_page_end();
