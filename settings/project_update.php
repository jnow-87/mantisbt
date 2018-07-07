<?php

require_once('../core.php');
require_api('project_api.php');
require_api('error_api.php');


json_prepare();

form_security_validate('project_update');


$f_cmd = gpc_get_string('cmd', '');
$f_redirect = gpc_get_string('redirect', '');
$f_project_id = gpc_get_int('project_id', -1);
$f_parent_id = gpc_get_int('parent_id', -1);
$f_copy_from_id = gpc_get_int('copy_from_id', -1);
$f_user_id = gpc_get_int('user_id', -1);
$f_access_level = gpc_get_int('access_level_' . $f_user_id, DEFAULT_ACCESS_LEVEL);
$f_access_level = gpc_get_int('access_level', $f_access_level);
$f_custom_field_id = gpc_get_int('custom_field_id', -1);
$f_custom_field_sequence = gpc_get_int('custom_field_sequence_' . $f_custom_field_id, 0);

if($f_project_id != -1){
	project_ensure_exists($f_project_id);
	$t_project = project_get_row($f_project_id);
}
else
	$t_project = project_get_row_empty();

$f_name = gpc_get_string('name', $t_project['name']);
$f_status = gpc_get_int('status', $t_project['status']);
$f_view_state = gpc_get_int('view_state', $t_project['view_state']);
$f_enabled = gpc_get_bool('enabled', false);
$f_file_path = gpc_get_string('file_path', '');
$f_description = gpc_get_string('description', $t_project['description']);


auth_reauthenticate();
access_ensure_project_level(config_get('manage_project_threshold'), $f_project_id);


$t_succ_msg = '';

switch($f_cmd){
case 'create':
	access_ensure_global_level(config_get('create_project_threshold'));

	if($f_parent_id != -1)
		project_ensure_exists($f_parent_id);

	$t_project_id = project_create(strip_tags($f_name), $f_description, $f_status, $f_view_state, $f_file_path, $f_enabled, false);
	
	if(($f_view_state == VS_PRIVATE) && (false === current_user_is_administrator())){
		$t_access_level = access_get_global_level();
		$t_current_user_id = auth_get_current_user_id();

		project_add_user($t_project_id, $t_current_user_id, $t_access_level);
	}

	if($f_parent_id != -1)
		project_hierarchy_add($t_project_id, $f_parent_id, false);

	event_signal('EVENT_MANAGE_PROJECT_CREATE', array($t_project_id));
	$t_succ_msg = 'Project created';
	break;

case 'delete':
	if($f_project_id == -1)
		json_error('Invalid project ID');

	access_ensure_project_level(config_get('delete_project_threshold'), $f_project_id);
	project_delete($f_project_id);

	$t_succ_msg = 'Project deleted';
	break;

case 'set_details':
	if($f_project_id == -1)
		json_error('Invalid project ID');

	project_update($f_project_id, $f_name, $f_description, $f_status, $f_view_state, $f_file_path, $f_enabled, false);
	event_signal('EVENT_MANAGE_PROJECT_UPDATE', array($f_project_id));
	$t_succ_msg = 'Project details updated';
	break;

case 'user_add':
	if($f_project_id == -1)
		json_error('Invalid project ID');

	if($f_user_id == -1)
		json_error('Invalid user ID');

	access_ensure_project_level(config_get('project_user_threshold'), $f_project_id);

	project_add_user($f_project_id, $f_user_id, $f_access_level);
	$t_succ_msg = 'User added';
	break;

case 'user_rm':
	if($f_project_id == -1)
		json_error('Invalid project ID');

	if($f_user_id == -1)
		json_error('Invalid user ID');

	access_ensure_project_level(config_get('project_user_threshold'), $f_project_id);

	// Don't allow removal of users from the project who have a higher access level than the current user
	access_ensure_project_level(access_get_project_level($f_project_id, $f_user_id), $f_project_id);
	
	project_remove_user($f_project_id, $f_user_id);
	$t_succ_msg = 'User removed';
	break;

case 'user_update':
	if($f_project_id == -1)
		json_error('Invalid project ID');

	if($f_user_id == -1)
		json_error('Invalid user ID');

	access_ensure_project_level(config_get('project_user_threshold'), $f_project_id);

	// Don't allow update of users from the project who have a higher access level than the current user
	access_ensure_project_level(access_get_project_level($f_project_id, $f_user_id), $f_project_id);
	
	project_set_user_access($f_project_id, $f_user_id, $f_access_level);
	$t_succ_msg = 'User access level updated';
	break;

case 'custom_field_add':
	if($f_project_id == -1)
		json_error('Invalid project ID');

	if($f_custom_field_id == -1)
		json_error('Invalid custom field ID');

	access_ensure_project_level(config_get('custom_field_link_threshold'), $f_project_id);

	custom_field_link($f_custom_field_id, $f_project_id);
	$t_succ_msg = 'Custom field added';
	break;

case 'custom_field_rm':
	if($f_project_id == -1)
		json_error('Invalid project ID');

	if($f_custom_field_id == -1)
		json_error('Invalid custom fieldID');

	access_ensure_project_level(config_get('custom_field_link_threshold'), $f_project_id);

	custom_field_unlink($f_custom_field_id, $f_project_id);
	$t_succ_msg = 'Custom field removed';
	break;

case 'custom_field_update':
	if($f_project_id == -1)
		json_error('Invalid project ID');

	if($f_custom_field_id == -1)
		json_error('Invalid custom fieldID');

	access_ensure_project_level(config_get('custom_field_link_threshold'), $f_project_id);

	custom_field_set_sequence($f_custom_field_id, $f_project_id, $f_custom_field_sequence);
	$t_succ_msg = 'Custom field updated';
	break;

case 'subproject_link':
	if(config_get('subprojects_enabled') == OFF)
		access_denied();

	if($f_project_id == -1)
		json_error('Invalid project ID');

	if($f_parent_id == -1)
		json_error('Invalid parent project ID');

	project_ensure_exists($f_parent_id);

	if($f_project_id == $f_parent_id)
		trigger_error(ERROR_GENERIC, ERROR);

	project_hierarchy_add($f_project_id, $f_parent_id);
	$t_succ_msg = 'Subproject linked';
	break;

case 'subproject_unlink':
	if(config_get( 'subprojects_enabled' ) == OFF)
		access_denied();

	if($f_project_id == -1)
		json_error('Invalid project ID');

	if($f_parent_id == -1)
		json_error('Invalid parent project ID');

	project_ensure_exists($f_parent_id);

	project_hierarchy_remove($f_project_id, $f_parent_id);
	$t_succ_msg = 'Subproject unlinked';
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to ' . basename(__FILE__));
}

if($f_redirect != ''){
	form_security_purge('project_update');
	print_header_redirect($f_redirect);
}

json_success($t_succ_msg);
