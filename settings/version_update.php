<?php

require_once('../core.php');
require_api('gpc_api.php');
require_api('version_api.php');
require_api('error_api.php');


json_prepare();

form_security_validate('version_update');


$f_cmd = gpc_get_string('cmd', '');
$f_redirect = gpc_get_string('redirect', '');
$f_project_id = gpc_get_int('project_id', -1);
$f_version_id = gpc_get_int('version_id', -1);

if($f_version_id != -1){
	version_ensure_exists($f_version_id);
	$t_version_old = version_get($f_version_id);
}
else
	$t_version_old = version_get_empty();

$t_version = version_get_empty();
$t_version->id = $f_version_id;
$t_version->version = gpc_get_string('name', $t_version_old->version);
$t_version->date_order = gpc_get_string('date', $t_version_old->date_order);
$t_version->description = gpc_get_string('description', $t_version_old->description);
$t_version->obsolete = gpc_get_bool('obsolete');
$t_version->released = gpc_get_bool('released');


auth_reauthenticate();

if($f_project_id != -1)
	access_ensure_project_level(config_get('manage_project_threshold'), $f_project_id);

if($f_version_id != -1)
	access_ensure_project_level(config_get('manage_project_threshold'), $t_version->project_id);



$t_succ_msg = '';

switch($f_cmd){
case 'create':
	if($f_project_id == -1)
		json_error('Invalid project ID');

	if(!version_is_unique($t_version->version, $f_project_id))
		json_error('Version already exists');

	$t_version->id = version_add($f_project_id, $t_version->version);
	version_update($t_version);

	$t_succ_msg = 'Version created';
	break;

case 'delete':
	if($f_version_id == -1)
		json_error('Invalid version ID');

	version_remove($f_version_id);
	$t_succ_msg = 'Version deleted';
	break;

case 'set_details':
	if($f_version_id == -1)
		json_error('Invalid version ID');

	if($t_version->released && !$t_version_old->released)
		$t_version->date_order = time();

	if($t_version->obsolete && !$t_version_old->obsolete)
		$t_version->date_order = time();

	version_update($t_version);
	event_signal('EVENT_MANAGE_VERSION_UPDATE', array($f_version_id));
	$t_succ_msg = 'Version details updated';
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to ' . basename(__FILE__));
}

if($f_redirect != ''){
	form_security_purge('version_update');
	print_header_redirect($f_redirect);
}

json_success($t_succ_msg);
