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
 * This page allows actions to be performed on an array of bugs
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
 * @uses bug_group_action_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('bug_group_action_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('custom_field_api.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('string_api.php');
require_api('utility_api.php');
require_api('version_api.php');


json_prepare();

auth_ensure_user_authenticated();

$f_action = gpc_get_string('bulk_action', '');
$f_bug_arr = gpc_get_int_array('bug_arr', array());
$f_filter_num_total = gpc_get_int('filter_num_total', 0);

$t_cmds = bug_group_action_get_commands(null);
$t_action_name = $t_cmds[$f_action];

# redirects to all_bug_page if nothing is selected
if(is_blank($f_action) || (0 == count($f_bug_arr)))
	json_error('No issues selected');

# run through the issues to see if they are all from one project
$t_project_id = ALL_PROJECTS;
$t_multiple_projects = false;
$t_projects = array();

bug_cache_array_rows($f_bug_arr);

foreach($f_bug_arr as $t_bug_id){
	$t_bug = bug_get($t_bug_id);

	if($t_project_id != $t_bug->project_id){
		if(($t_project_id != ALL_PROJECTS) && !$t_multiple_projects){
			$t_multiple_projects = true;
		}
		else{
			$t_project_id = $t_bug->project_id;
			$t_projects[$t_project_id] = $t_project_id;
		}
	}
}

if($t_multiple_projects){
	$t_project_id = ALL_PROJECTS;
	$t_projects[ALL_PROJECTS] = ALL_PROJECTS;
}

# override the project if necessary
#	in case the current project is not the same project of the bug we are viewing...
#	... override the current project. This to avoid problems with categories and handlers lists etc.
if($t_project_id != helper_get_current_project())
	$g_project_override = $t_project_id;

define('BUG_ACTIONGROUP_INC_ALLOW', true);


if(strpos($f_action, 'EXT_') === 0){
	$t_form_page = 'bug_actiongroup_ext_page.php';
	require_once($t_form_page);
	exit;
}

$t_custom_group_actions = config_get('custom_group_actions');

foreach($t_custom_group_actions as $t_custom_group_action){
	if($f_action == $t_custom_group_action['action']){
		require_once($t_custom_group_action['form_page']);
		exit;
	}
}

# Check if user selected to update a custom field.
$t_custom_fields_prefix = 'custom_field_';

if(strpos($f_action, $t_custom_fields_prefix) === 0){
	$t_custom_field_id = (int)substr($f_action, utf8_strlen($t_custom_fields_prefix));
	$f_action = 'CUSTOM';
}

# Form name
$t_form_name = 'bug_actiongroup_' . $f_action;


/* check if operation is valid for issues of multiple projects */
$t_require_project_id = array('UP_PRODUCT_VERSION', 'UP_FIXED_IN_VERSION', 'UP_TARGET_VERSION', 'RESOLVE', 'ASSIGN', 'UP_CATEGORY', 'UP_STATUS', 'CLOSE');

if($t_multiple_projects && in_array($f_action, $t_require_project_id))
	json_error('Set version not supported for issues of multiple projects');




$t_bugnote = false;
$t_selects = array();
$t_dates = array();
$t_custom_fields = array();

switch($f_action){
	case 'CLOSE':
		$t_bugnote = true;
		break;

	case 'DELETE':
		break;

	case 'MOVE':
		$t_bugnote = true;
		$t_selects[] = array('label' => 'Project', 'id' => 'project_id', 'value' => project_list());
		break;

	case 'ASSIGN':
		$t_selects[] = array('label' => 'Assignee', 'id' => 'assign', 'value' => user_list($t_project_id));
		break;

	case 'RESOLVE':
		$t_bugnote = true;
		$t_selects[] = array('label' => 'Resolution', 'id' => 'resolution', 'value' => resolution_list());
		$t_selects[] = array('label' => 'Fixed in Version', 'id' => 'fixed_in_version', 'value' => version_list($t_project_id, true));
		break;

	case 'UP_PRIOR':
		$t_selects[] = array('label' => 'Priority', 'id' => 'priority', 'value' => priority_list());
		break;

	case 'UP_STATUS':
		$t_bugnote = true;
		$t_selects[] = array('label' => 'Status', 'id' => 'status', 'value' => status_list());
		break;

	case 'UP_CATEGORY':
		$t_selects[] = array('label' => 'Category', 'id' => 'category', 'value' => category_list($t_project_id));
		break;

	case 'VIEW_STATUS':
		$t_selects[] = array('label' => 'Visibility', 'id' => 'view_status', 'value' => view_status_list());
		break;

	case 'UP_PRODUCT_VERSION':
		$t_selects[] = array('label' => 'Affected Version', 'id' => 'product_version', 'value' => version_list($t_project_id, true));
		break;

	case 'UP_FIXED_IN_VERSION':
		$t_selects[] = array('label' => 'Fixed in Version', 'id' => 'fixed_in_version', 'value' => version_list($t_project_id, true));
		break;

	case 'UP_TARGET_VERSION':
		$t_selects[] = array('label' => 'Target Version', 'id' => 'target_version', 'value' => version_list($t_project_id, false));
		break;

	case 'UP_DUE_DATE':
		$t_dates[] = array('label' => 'Due Date', 'id' => 'due_date', 'value' => '');
		break;

	case 'CUSTOM':
		$t_bugnote = true;
		$t_custom_field_def = custom_field_get_definition($t_custom_field_id);
		$t_custom_fields[] = array('label' =>$t_custom_field_def['name'], 'id' => $t_custom_field_id);
		break;

	default:
		json_error('Invalid action \'' . $f_action . '\'');
}

layout_page_header();
layout_inline_page_begin();

page_title('Bulk Operation: ' . $t_action_name);


echo '<div class="col-md-12">';

/* print alerts */
// alert if issues of multiple projects are selected
if($t_multiple_projects)
	alert('warning', lang_get('multiple_projects'));

// hint on the number of issues compared to the total number of the filter result
if(count($f_bug_arr) < $f_filter_num_total)
	alert('info', 'Performing action on ' . count($f_bug_arr) . ' out of  ' . $f_filter_num_total) . ' issues';

/* main form */
echo '<form method="post" action="bug_actiongroup.php">';
	actionbar_begin();
		echo '<div class="pull-right">';
		button('Start Action', 'bulk-submit', 'submit');
		echo '</div>';
	actionbar_end();

	/* hidden inputs */
	echo form_security_field($t_form_name);
	input_hidden('bulk_action', string_attribute($f_action));
	bug_group_action_print_hidden_fields($f_bug_arr);

	if($f_action === 'CUSTOM')
		input_hidden('custom_field_id', $t_custom_field_id);

	/* additionally required data based on the given action */
	table_begin(array(), 'table-condensed no-border');

	// print selects
	foreach($t_selects as $t_entry)
		table_row_bug_info_long($t_entry['label'] . ':', format_select($t_entry['id'], $t_entry['id'], $t_entry['value'], ''), '10%');

	// print date input
	foreach($t_dates as $t_entry)
		table_row_bug_info_long($t_entry['label'] . ':', format_date($t_entry['id'], $t_entry['id'], $t_entry['value']), '10%');

	// print custom fields
	foreach($t_custom_fields as $t_entry)
		table_row_bug_info_long($t_entry['label'] . ':', format_textarea('custom_field_' . $t_entry['id'], 'custom_field_' . $t_entry['id'], '', 'input-xs', 'width:100%!important;height:150px;'), '10%');

	// print note
	if($t_bugnote){
		if(access_has_project_level(config_get('private_bugnote_threshold'), $t_project_id)
			&& access_has_project_level(config_get('set_view_status_threshold'), $t_project_id)
		){
			$t_private = format_label('Private:') . format_hspace('2px') . format_checkbox('private', 'private');
		}

		table_row_bug_info_long('Note:<br>' . $t_private, format_textarea('bugnote_text', 'bugnote_text', '', 'input-xs', 'width:100%!important;height:150px;'), '10%');
	}

	table_end();
echo '</form>';

/* list of bugs to apply action on */
bug_group_action_print_bug_list($f_bug_arr);

echo '</div>';

layout_inline_page_end();
