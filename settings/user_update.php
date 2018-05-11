<?php
require_once('../core.php');
require_api('email_api.php');
require_api('form_api.php');
require_api('html_api.php');
require_api('user_api.php');
require_api('gpc_api.php');
require_api('error_api.php');
require_api('authentication_api.php');
require_api('current_user_api.php');
require_api('layout_api.php');


function get_details($p_user_id){
	if($p_user_id == -1)
		$t_user = user_get_row_empty();
	else
		$t_user = user_get_row($p_user_id);

	$t_user['username'] = gpc_get_string('username', $t_user['username']);
	$t_user['realname'] = gpc_get_string('realname', $t_user['realname']);
	$t_user['email'] = gpc_get_string('email', $t_user['email']);
	$t_user['enabled'] = gpc_get_bool('enabled', false);
	$t_user['protected'] = gpc_get_bool('protected', false);
	$t_user['access_level'] = gpc_get_int('access_level', $t_user['access_level']);

	user_ensure_name_valid($t_user['username']);
	user_ensure_realname_unique($t_user['username'], $t_user['realname']);

	return $t_user;
}

function get_pref($p_user_id){
	$t_pref = user_pref_get($p_user_id);
	$t_pref_new = user_pref_get($p_user_id);

	$t_pref_new->timezone = gpc_get_string('timezone', $t_pref->timezone);
	$t_pref_new->language = gpc_get_string('language', $t_pref->language);
	$t_pref_new->refresh_delay = gpc_get_int('refresh_delay', $t_pref->refresh_delay);
	$t_pref_new->redirect_delay = gpc_get_int('redirect_delay', $t_pref->redirect_delay);
	$t_pref_new->default_project = gpc_get_int('default_project', $t_pref->default_project);
	$t_pref_new->bugnote_order = gpc_get_string('bugnote_order', $t_pref->bugnote_order);
	$t_pref_new->email_bugnote_limit = gpc_get_int('email_notes_limit', $t_pref->email_bugnote_limit);

	foreach(email_events() as $t_type){
		$t_check_id = 'email_on_' . $t_type;
		$t_sev_id = 'email_on_' . $t_type . '_min_severity';

		$t_pref_new->$t_check_id = gpc_get_bool($t_check_id);
		$t_pref_new->$t_sev_id = gpc_get_int($t_sev_id, $t_pref->$t_sev_id);
	}

	return $t_pref_new;
}

function validate_password($p_user_id){
	$f_pw_current = gpc_get_string('pw_current', '');
	$f_pw_new0 = gpc_get_string('pw_new0', '');
	$f_pw_new1 = gpc_get_string('pw_new1', '');

	if($p_user_id != -1 && !auth_does_password_match($p_user_id, $f_pw_current))
		json_error('Invalid password');

	if($f_pw_new0 == '')
		json_error('Empty password');

	if($f_pw_new0 != $f_pw_new1)
		json_error('Passwords do not match');

	return $f_pw_new0;
}


json_prepare();

$t_manage_user_threshold = config_get('manage_user_threshold');


form_security_validate('user_update');
auth_ensure_user_authenticated();
current_user_ensure_unprotected();
auth_reauthenticate();
access_ensure_global_level($t_manage_user_threshold);


$f_user_id = gpc_get_int('user_id', -1);
$f_cmd = gpc_get_string('cmd', '');
$f_redirect = gpc_get_string('redirect', '');

if($f_user_id != -1)
	user_ensure_exists($f_user_id);


/* perform command */
$t_succ_msg = '';

switch($f_cmd){
case 'create':
	$t_pw = validate_password(-1);
	$t_user = get_details(-1);

	user_ensure_name_valid($t_user['username']);
	user_ensure_realname_unique($t_user['username'], $t_user['realname']);

	lang_push(config_get('default_language'));
	user_create($t_user['username'], $t_pw, $t_user['email'], $t_user['access_level'], $t_user['protected'], $t_user['enabled'], $t_user['realname']);
	lang_pop();

	form_security_purge('user_update');
	break;

case 'delete':
	if($f_user_id == -1)
		json_error('Missing user ID');

	// Ensure that the account to be deleted is of equal or lower access to the current user.
	access_ensure_global_level(user_get_field($f_user_id, 'access_level'));

	// Check that we are not deleting the last administrator account
	if(user_is_administrator($f_user_id) &&	user_count_level(config_get_global('admin_site_threshold'), true) <= 1)
		trigger_error(ERROR_USER_CHANGE_LAST_ADMIN, ERROR);

	user_delete($f_user_id);
	form_security_purge('user_update');

	$t_succ_msg = 'User deleted';
	break;

case 'set_email':
	if($f_user_id == -1)
		json_error('Missing user ID');

	user_set_email($f_user_id, gpc_get_string('email'));
	$t_succ_msg = 'eMail updated';
	break;

case 'set_full_details':
	if($f_user_id == -1)
		json_error('Missing user ID');

	$t_user = get_details($f_user_id);
	user_ensure_name_valid($t_user['username']);
	user_ensure_realname_unique($t_user['username'], $t_user['realname']);

	// Ensure that the account to be updated is of equal or lower access to the current user.
	access_ensure_global_level($t_user['access_level']);

	user_set_fields($f_user_id, $t_user);
	event_signal('EVENT_MANAGE_USER_UPDATE', array($f_user_id));

	$t_succ_msg = 'User updated';
	break;

case 'set_prefs':
	if($f_user_id == -1)
		json_error('Missing user ID');

	user_pref_set($f_user_id, get_pref($f_user_id));
	config_set('email_notifications_verbose', gpc_get_bool('email_full_issue'), $f_user_id, ALL_PROJECTS);

	$t_succ_msg = 'Preferences updated';
	break;

case 'reset_prefs':
	user_pref_delete($f_user_id);

	layout_inline_page_begin();
	page_title('Status');
	html_operation_successful($f_redirect, 'Preferences reset');
	layout_inline_page_end();
	return;

case 'set_pw':
	$t_pw = validate_password($f_user_id);
	user_set_password($f_user_id, $t_pw);

	$t_succ_msg = 'Password updated';
	break;

case 'reset_pw':
	if($f_user_id == -1)
		json_error('Missing user ID');

	$t_pw = validate_password(-1);
	user_set_password($f_user_id, $t_pw);

	$t_succ_msg = 'Password reset';
	break;

case 'assign_project':
	if($f_user_id == -1)
		json_error('Missing user ID');

	$f_project_id = gpc_get_int('project_id');
	$f_access_level = gpc_get_string('access_level', '');

	if($f_access_level == '')
		$f_access_level = gpc_get_string('access_level_' . $f_project_id);

	if(!access_has_project_level($t_manage_user_threshold, $f_project_id) || !access_has_project_level($f_access_level, $f_project_id))
		json_error('Access denied for current user');

	project_remove_user($f_project_id, $f_user_id);
	project_add_user($f_project_id, $f_user_id, $f_access_level);
	break;

case 'unassign_project':
	if($f_user_id == -1)
		json_error('Missing user ID');

	$f_project_id = gpc_get_int('project_id');

	$t_user = get_details($f_user_id);

	access_ensure_project_level(config_get('project_user_threshold'), $f_project_id);
	access_ensure_project_level($t_user['access_level'], $f_project_id);

	project_remove_user($f_project_id, $f_user_id);
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to '. basename(__FILE__));
}


if($f_redirect != '')
	print_header_redirect($f_redirect);

json_success($t_succ_msg);
