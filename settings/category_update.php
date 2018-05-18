<?php
require_once('../core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('category_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('error_api.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('utility_api.php');

form_security_validate('category_update');
auth_reauthenticate();


$f_project_id = gpc_get_int('project_id');
$f_copy_from_project_id = gpc_get_int('copy_from_project_id', -1);
$f_id = gpc_get_int('id', -1);
$f_redirect = gpc_get_string('redirect', '');
$f_name = trim(gpc_get_string('name', gpc_get_string('name_' . $f_id, '')));
$f_cmd = gpc_get_string('cmd');

access_ensure_project_level(config_get('manage_project_threshold'), $f_project_id);

$t_succ_msg = '';

switch($f_cmd){
case 'create':
	if($f_name == ''){
		if($f_id == -1)
			json_error('Empty issue type name');

		$f_name = category_get_name($f_id);
	}

	if(!category_is_unique($f_project_id, $f_name))
		json_error('Issue type already exists');

	category_add($f_project_id, $f_name);
	$t_succ_msg = 'Issue type created';
	break;

case 'delete':
	if($f_id == -1)
		json_error('Invalid issue type ID');

	// Protect the 'default category for moves' from deletion
	category_ensure_can_remove($f_id);

	// Protect the category from deletion which is associted with an issue
	category_ensure_can_delete($f_id);

	category_remove($f_id);
	$t_succ_msg = 'Issue type removed';
	break;

case 'update':
	if($f_id == -1)
		json_error('Invalid issue type ID');

	if($f_name == '')
		json_error('Empty issue type name');

	category_update($f_id, $f_name, ALL_USERS);
	$t_succ_msg = 'Issue type updated';
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to ' . basename(__FILE__));
}


if($f_redirect != '')
	print_header_redirect($f_redirect);

json_success($t_succ_msg);
