<?php
require_once('../core.php');
require_api('api_token_api.php');
require_api('string_api.php');
require_api('error_api.php');
require_api('layout_api.php');
require_api('helper_api.php');
require_api('form_api.php');
require_api('authentication_api.php');
require_api('gpc_api.php');
require_api('user_api.php');


json_prepare();

form_security_validate('api_token');
auth_ensure_user_authenticated();
auth_reauthenticate();


$f_cmd = gpc_get_string('cmd', '');
$f_token_name = gpc_get_string('token_name', '');
$f_token_id = gpc_get_int('token_id', -1);

$t_user_id = auth_get_current_user_id();

user_ensure_unprotected($t_user_id);

switch($f_cmd){
case 'create':
	if($f_token_name == '')
		json_error('Missing token name');

	$t_token = api_token_create($f_token_name, $t_user_id);

	layout_inline_page_begin();
	page_title('API Token');

	alert('danger', 'Note that this token will only be displayed once.');
	alert('success', string_display_line($t_token));

	echo '<div class="center">';
	button_link('Proceed', helper_mantis_url('account_page.php'));
	echo '</div>';

	layout_inline_page_end();
	break;

case 'revoke':
	if($f_token_id == -1)
		json_error('Invalid token id');

	api_token_revoke($f_token_id, $t_user_id);
	json_success('Token revoken');
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to ' . basename(__FILE__));
}
