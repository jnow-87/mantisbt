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


json_prepare();

form_security_validate('user_update');


$f_user_id = gpc_get_int('user_id', -1);
$f_cmd = gpc_get_string('cmd', '');
$f_redirect = gpc_get_string('redirect', '');

if($f_user_id == -1)
	json_error('Missing user ID');

$f_email = gpc_get_string('email', user_get_email($f_user_id));

$f_pw_current = gpc_get_string('pw_current', '');
$f_pw_new0 = gpc_get_string('pw_new0', '');
$f_pw_new1 = gpc_get_string('pw_new1', '');

$t_pref = user_pref_get($f_user_id);
$t_pref_new = user_pref_get($f_user_id);

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

$f_email_full_issue = gpc_get_bool('email_full_issue');


auth_ensure_user_authenticated();
current_user_ensure_unprotected();


switch($f_cmd){
case 'set_details':
	user_set_email($f_user_id, $f_email);
	json_success('Details updated');
	break;

case 'set_prefs':
	user_pref_set($f_user_id, $t_pref_new);
	config_set('email_notifications_verbose', $f_email_full_issue, $f_user_id, ALL_PROJECTS);

	json_success('Preferences updated');
	break;

case 'reset_prefs':
	user_pref_delete($f_user_id);

	layout_inline_page_begin();
	page_title('Status');
	html_operation_successful($f_redirect, 'Preferences reset');
	layout_inline_page_end();
	break;

case 'set_pw':
	if(!auth_does_password_match($f_user_id, $f_pw_current))
		json_error('Invalid password');

	if($f_pw_new0 == '')
		json_error('Empty password');

	if($f_pw_new0 != $f_pw_new1)
		json_error('Passwords do not match');

	user_set_password($f_user_id, $f_pw_new0);

	json_success('Password updated');
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\'');
}
