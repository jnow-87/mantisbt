<?php
require_once('core.php');
require_api('api_token_api.php');
require_api('authentication_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('current_user_api.php');
require_api('form_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('print_api.php');
require_api('string_api.php');
require_api('user_api.php');
require_api('utility_api.php');
require_api('timezone_api.php');
require_api('bug_list_api.php');
require_api('project_api.php');
require_api('layout_api.php');
require_api('elements_api.php');


auth_ensure_user_authenticated();
current_user_ensure_unprotected();


$t_user_id = gpc_get_int('id', auth_get_current_user_id());
$t_user_name = user_get_name($t_user_id);
$t_pref = user_pref_get($t_user_id);
$t_email_full_issue = (int)config_get('email_notifications_verbose', null, $t_user_id, ALL_PROJECTS);
$t_date_format = config_get('normal_date_format');

form_security_purge('user_update');


layout_page_header(__FILE__);
layout_page_begin();

page_title('User Info: ' . $t_user_name);

/* left column */
column_begin('7');

/* user details */
section_begin('Details');
	echo '<form action="settings/user_update.php" method="post" class="input-hover-form">';
	input_hidden('user_id', $t_user_id);
	input_hidden('cmd', 'set_email');
	echo form_security_field('user_update');

	$t_email = user_get_email($t_user_id);
	$t_email = get_email_link($t_email, $t_email);

	column_begin('3');
	table_begin(array(), 'no-border');
	table_row_bug_info_short('Username:', $t_user_name);
	table_row_bug_info_short('Realname:', user_get_realname($t_user_id));
	table_row_bug_info_short('eMail:', $t_email);
	table_end();
	column_end();

	echo '</form>';
section_end();

column_end();

/* right column */
if(access_has_project_level(config_get('timeline_view_threshold'))){
	column_begin('5');
	section_begin('Timeline');

	# Build a simple filter that gets all bugs for current project
	$g_timeline_filter = array();
	$g_timeline_filter[FILTER_PROPERTY_HIDE_STATUS] = array(META_FILTER_NONE);
	$g_timeline_filter = filter_ensure_valid_filter($g_timeline_filter);
	include($g_core_path . 'timeline_inc.php');

	section_end();
	column_end();
}

layout_page_end();
