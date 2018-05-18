<?php

require_once('core.php');
require_api('layout_api.php');
require_api('database_api.php');
require_api('authentication_api.php');
require_api('elements_api.php');


auth_reauthenticate();
access_ensure_global_level(config_get('manage_user_threshold'));

form_security_purge('user_update');

$t_date_format = config_get('normal_date_format');


$t_query = 'SELECT * FROM {user}';
$t_result = db_query($t_query);


layout_page_header(__FILE__);
layout_page_begin();

page_title('User Settings');
	actionbar_begin();
		button_link('Create', 'settings/user_edit_page.php', array('cmd' => 'create'), 'inline-page-link', false, true, 'inline-page-reload');
	actionbar_end();


	$t_update_token = form_security_token('user_update');

	table_begin(array('', 'User Name', 'Real Name', 'eMail', 'Access Level', 'Enabled', 'Protected', 'Date Created', 'Last Visited'), 'table-condensed table-hover table-datatable no-border', '', array('width="40px"'));

	while($t_row = db_fetch_array($t_result)){
		$t_edit_btn = format_link(format_icon('fa-pencil'), 'settings/user_edit_page.php', array('cmd' => 'edit', 'user_id' => $t_row['id']), 'inline-page-link', '', 'inline-page-reload');
		$t_reset_pw_btn = format_link(format_icon('fa-key'), 'settings/user_edit_page.php', array('cmd' => 'reset_pw', 'user_id' => $t_row['id']), 'inline-page-link', '', 'inline-page-reload');
		$t_delete_btn = format_button_confirm('Delete', 'settings/user_update.php', array('cmd' => 'delete', 'user_id' => $t_row['id'], 'redirect' => 'manage_users_page.php', 'user_update_token' => $t_update_token), 'Delete user?', 'danger', format_icon('fa-trash', 'red'));

		table_row(array(
			$t_edit_btn
			. $t_reset_pw_btn
			. $t_delete_btn,
			user_format_name($t_row['id']),
			$t_row['realname'],
			get_email_link($t_row['email'], $t_row['email']),
			get_enum_element('access_levels', $t_row['access_level']),
			trans_bool($t_row['enabled']),
			trans_bool($t_row['protected']),
			date($t_date_format, $t_row['date_created']),
			date($t_date_format, $t_row['last_visit']),
			)
		);
	}

	table_end();

layout_page_end();
