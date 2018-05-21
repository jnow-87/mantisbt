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
require_api('lang_api.php');
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


$t_user_id = auth_get_current_user_id();
$t_user_name = user_get_name($t_user_id);
$t_pref = user_pref_get($t_user_id);
$t_email_full_issue = (int)config_get('email_notifications_verbose', null, $t_user_id, ALL_PROJECTS);
$t_date_format = config_get('normal_date_format');

form_security_purge('user_update');


layout_page_header(__FILE__);
layout_page_begin();

page_title('User Settings: ' . $t_user_name);

/* actionbar */
echo '<div class="col-md-12">';
actionbar_begin();
	echo '<div class="pull-left">';
		button_link('Change Password', 'settings/set_password.php', array('user_id' => $t_user_id), 'inline-page-link');
		button_link('Reset Preferences', 'settings/user_update.php',
			array('user_id' => $t_user_id, 'cmd' => 'reset_prefs', 'redirect' => 'manage_account_page.php', 'user_update_token' => form_security_token('user_update')),
			'inline-page-link',
			false,
			true,
			'inline-page-reload'
		);
	echo '</div>';
actionbar_end();
echo '</div>';

/* left column */
echo '<div class="col-md-9">';

/* user details */
section_begin('Details');
	echo '<form action="settings/user_update.php" method="post" class="input-hover-form">';
	input_hidden('user_id', $t_user_id);
	input_hidden('cmd', 'set_email');
	echo form_security_field('user_update');

	echo '<div class="col-md-3 no-padding">';
	table_begin(array(), 'no-border');
	table_row_bug_info_short('Username:', user_format_name($t_user_id));
	table_row_bug_info_short('Realname:', user_get_realname($t_user_id));
	table_row_bug_info_short('eMail:', format_input_hover_text('email', user_get_email($t_user_id)));
	table_end();
	echo '</div>';

	echo '</form>';
section_end();

/* access levels */
section_begin('Access Rights');
	echo '<div class="col-md-3 no-padding">';
	table_begin(array(), 'no-border');
	table_row_bug_info_short('General Access Level:', get_enum_element('access_levels', user_get_access_level($t_user_id)));
	table_end();
	echo '</div>';

	if(user_get_access_level($t_user_id) == ACC_ADMIN)
		$t_projects = project_list();
	else
		$t_projects = user_get_assigned_projects($t_user_id, true);

	echo '<div class="col-md-12 no-padding">';
	table_begin(array('Project', 'Access Level', 'Visibility', 'Description'), 'table-condensed table-hover no-border');

	foreach($t_projects as $t_id){
		table_row(array(
				format_link(project_get_name($t_id, false), helper_mantis_url('settings/project_page.php'), array('project_id' => $t_id)),
				get_enum_element('access_levels', user_get_access_level($t_user_id, $t_id)),
				get_enum_element('project_view_state', project_get_field($t_id, 'view_state')),
				project_get_field($t_id, 'description')
			)
		);
	}

	table_end();
	echo '</div>';
section_end();

/* API tokens */
section_begin('API Tokens');
	actionbar_begin();
		echo '<form action="settings/api_token.php" method="post" class="form-inline input-hover-form input-hover-form-reload">';
		echo form_security_field('api_token');
		input_hidden('cmd', 'create');

		text('token_name', 'token_name', '', 'Token Name', 'input-xs', '', 'maxlength="' . DB_FIELD_SIZE_API_TOKEN_NAME . '"');
		hspace('5px');
		button('Create', 'create-btn', 'submit');
		echo '</form>';
	actionbar_end();

	table_begin(array('', 'Token', 'Date Created', 'Last Used'), 'table-condensed table-hover table-datatable no-border');

	$t_tokens = api_token_get_all($t_user_id);

	/* access level per assigned project */
	foreach($t_tokens as $t_token){
		extract($t_token, EXTR_PREFIX_ALL, 'u');

		$t_btn_delete = format_link(format_icon('fa-trash', 'red'), 'settings/api_token.php', array('cmd' => 'revoke', 'token_id' => $u_id, 'token_name' => $u_name, 'api_token_token' => form_security_token('api_token')), 'inline-page-link', '', 'inline-page-reload');
		$t_date_created = date($t_date_format, $u_date_created);

		$t_date_used = 'Never used';

		if(api_token_is_used($t_token))
			$t_date_used = date($t_date_format, $u_date_used);

		table_row(array($t_btn_delete, $u_name, $t_date_created, $t_date_used), '', array('width="20px"'));
	}
	table_end();
section_end();

echo '</div>';

/* right column */
echo '<div class="col-md-3">';
echo '<form action="settings/user_update.php" method="post" class="input-hover-form">';

input_hidden('user_id', $t_user_id);
input_hidden('cmd', 'set_prefs');
echo form_security_field('user_update');

/* interface settings */
section_begin('Interface Settings');
	table_begin(array(), 'no-border');
	table_row_bug_info_short('Time Zone:', format_input_hover_select('timezone', timezone_list(), $t_pref->timezone));
	table_row_bug_info_short('Language:', format_input_hover_select('language', language_list(), $t_pref->language));
	table_row_bug_info_short('Default Project:', format_input_hover_select('default_project', project_list(true), project_get_name($t_pref->default_project)));
	table_row_bug_info_short('Redirect Delay:', format_input_hover_text('redirect_delay', $t_pref->redirect_delay, '50px') . ' seconds');
	table_row_bug_info_short('Refresh Delay:', format_input_hover_text('refresh_delay', $t_pref->refresh_delay, '50px') . ' minutes');
	table_row_bug_info_short('Bug Note Order:', format_input_hover_select('bugnote_order', array('Ascending' => 'ASC', 'Descending' => 'DESC'), helper_ordering_name($t_pref->bugnote_order)));
	table_end();
section_end();

/* column config */
section_begin('Columns');
	$t_config_opt = array(
		'Filter' => 'bug_list_columns_filter',
		'Dashboard' => 'bug_list_columns_dashboard',
		'Versions' => 'bug_list_columns_versions',
		'Bulk Edit' => 'bug_list_columns_bulk',
		'Print/Export' => 'bug_list_columns_export',
	);

	table_begin(array(), 'no-border');

	foreach($t_config_opt as $t_name => $t_value){
		$t_columns = config_get($t_value, $t_user_id);
		$t_column_names = array();

		foreach($t_columns as $t_col)
			$t_column_names[] = column_title($t_col, false);

		$t_buttons = array(
			array(
				'icon' => 'fa-pencil',
				'href' => format_href(helper_mantis_url('columns_select_page.php'), column_select_input($t_value, $t_columns, true, true, basename(__FILE__))),
				'position' => 'right:4px',
				'class' => 'inline-page-link',
				'properties' => 'inline-page-reload'
			)
		);

		$t_key_name = '<span style="margin-right:20px!important">' . $t_name . ':</span>';
		$t_column_key = format_input_hover_element('id_' . $t_value, $t_key_name, $t_buttons);

		table_row(array($t_column_key, implode('<br>', $t_column_names)), '', array('class="no-border bug-header" width="30%" valign="top"', 'class="no-border"'));
	}

	table_end();
section_end();

/* email notification settings */
if(config_get('enable_email_notification') == ON){
	section_begin('eMail Settings');
		table_begin(array(), 'no-border');

		table_row_bug_info_short('Notes Limit:', format_input_hover_text('email_notes_limit', $t_pref->email_bugnote_limit));
		table_row_bug_info_short('Full Issue Details:', format_input_hover_checkbox('email_full_issue', $t_email_full_issue));

		foreach(email_events() as $t_type){
			$t_check_id = 'email_on_' . $t_type;
			$t_sev_id = 'email_on_' . $t_type . '_min_severity';

			table_row_bug_info_short('eMail on ' . $t_type . ':', format_input_hover_checkbox($t_check_id, $t_pref->$t_check_id));
			table_row_bug_info_short('', 'min severity:' . format_hspace('15px') . format_input_hover_select($t_sev_id, severity_list(), get_enum_element('severity', $t_pref->$t_sev_id)));
		}

		table_end();
	section_end();
}

echo '</form>';
echo '</div>';

layout_page_end();
