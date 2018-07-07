<?php
require_once('../core.php');
require_api('access_api.php');
require_api('config_api.php');
require_api('gpc_api.php');


form_security_validate('dbconfig_update');
access_ensure_global_level(config_get('set_configuration_threshold'));

$f_config_id = gpc_get_string('config_id');
$f_user_id = gpc_get_int('user_id');
$f_project_id = gpc_get_int('project_id');
$f_redirect = gpc_get_string('redirect', '');
$f_cmd = gpc_get_string('cmd', '');


switch($f_cmd){
case 'delete':
	config_delete($f_config_id, $f_user_id, $f_project_id);
	$t_succ_msg = 'Option deleted';
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to '. basename(__FILE__));
}


if($f_redirect != ''){
	form_security_purge('dbconfig_update');
	print_header_redirect($f_redirect);
}

json_success($t_succ_msg);
