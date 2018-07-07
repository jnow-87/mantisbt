<?php

require_once('../core.php');
require_api('error_api.php');
require_api('gpc_api.php');
require_api('project_api.php');


/* get inputs */
$f_cmd = gpc_get_string('cmd', '');
$f_field_id = gpc_get_int('field_id', -1);

$f_project_id = gpc_get_int('project_id', -1);
$f_sequence = gpc_get_int('sequence', 0);
$f_redirect = gpc_get_string('redirect', '');


json_prepare();

form_security_validate('custom_field_update');

auth_reauthenticate();
access_ensure_global_level(config_get('manage_custom_fields_threshold'));


if($f_project_id != -1)
	project_ensure_exists($f_project_id);

if($f_field_id != -1){
	custom_field_ensure_exists($f_field_id);
	$t_field = custom_field_get_definition($f_field_id);
}
else
	$t_field = custom_field_get_definition_empty();

$t_field['name'] = gpc_get_string('name', $t_field['name']);
$t_field['type'] = gpc_get_int('type', $t_field['type']);
$t_field['possible_values'] = gpc_get_string('possible_values', $t_field['possible_values']);
$t_field['default_value'] = gpc_get_string('default_value', $t_field['default_value']);
$t_field['access_level_r'] = gpc_get_int('access_level_r', $t_field['access_level_r']);
$t_field['access_level_rw'] = gpc_get_int('access_level_rw', $t_field['access_level_rw']);
$t_field['length_min'] = gpc_get_int('length_min', $t_field['length_min']);
$t_field['length_max'] = gpc_get_int('length_max', $t_field['len_max']);
$t_field['filter_by'] = gpc_get_bool('filter_by', false);


/* perform action */
$t_succ_msg = '';

switch($f_cmd){
case 'create':
	if($t_field['name'] == '')
		json_error('Empty custom field name');

	if(!custom_field_is_name_unique($t_field['name']))
		json_error('Custom field with this name already exists');

	$t_field_id = custom_field_create($t_field['name']);
	custom_field_update($t_field_id, $t_field);

	$t_succ_msg = 'Custom field created';
	break;

case 'update':
	if($f_field_id == -1)
		json_error('Invalid custom field ID');

	if(!custom_field_is_name_unique($t_field['name'], $f_field_id))
		json_error('Custom field with this name already exists');

	custom_field_update($f_field_id, $t_field);
	$t_succ_msg = 'Custom field updated';
	break;

case 'delete':
case 'force_delete':
	if($f_field_id == -1)
		json_error('Invalid custom field ID');

	if($f_cmd != 'force_delete' && count(custom_field_get_project_ids($f_field_id)) > 0)
		json_error('Custom field is linked to projects');

	if($f_cmd != 'force_delete' && custom_field_set_in($f_field_id) > 0)
		json_error('Custom field not empty in some issues');

	custom_field_destroy($f_field_id);
	$t_succ_msg = 'Custom field deleted';
	break;

case 'add':
	if($f_field_id == -1)
		json_error('Invalid custom field ID');

	if($f_project_id == -1)
		json_error('Invalid project ID');

	custom_field_link($f_field_id, $f_project_id);
	custom_field_set_sequence($f_field_id, $f_project_id, $f_sequence);
	$t_succ_msg = 'Custom field linked to project';
	break;

case 'rm':
	if($f_field_id == -1)
		json_error('Invalid custom field ID');

	if($f_project_id == -1)
		json_error('Invalid project ID');

	custom_field_unlink($f_field_id, $f_project_id);
	$t_succ_msg = 'Custom field unlinked from project';
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to '. basename(__FILE__));
}


if($f_redirect != '')
	print_header_redirect($f_redirect);


json_success($t_succ_msg);
