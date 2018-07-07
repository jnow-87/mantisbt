<?php

require_once('../core.php');
require_api('database_api.php');
require_api('gpc_api.php');
require_api('user_api.php');
require_api('helper_api.php');
require_api('elements_api.php');


define('PROJECT_UPDATE', helper_mantis_url('settings/project_update.php'));


json_prepare();

$f_cmd = gpc_get_string('cmd', '');
$f_project_id = gpc_get_int('project_id', -1);
$f_parent_id = gpc_get_int('parent_id', -1);
$f_redirect = gpc_get_string('redirect', '');

if($f_project_id != -1){
	project_ensure_exists($f_project_id);
	$t_project = project_get_row($f_project_id);
}
else
	$t_project = project_get_row_empty();


auth_reauthenticate();

switch($f_cmd){
case 'create':
	access_ensure_global_level(config_get('create_project_threshold'));
	
	$t_page_title = 'Create Project';

	if($f_parent_id != -1)
		$t_page_title = 'Create Sub-Project';

	$t_form_action = PROJECT_UPDATE . '?cmd=create';
	$t_btn_text = 'Create';
	break;

case 'edit':
	access_ensure_project_level(config_get('manage_project_threshold'), $f_project_id);

	$t_page_title = 'Edit Project: ' . $t_project['name'];
	$t_form_action = PROJECT_UPDATE . '?cmd=set_details';
	$t_btn_text = 'Update';
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to ' . basename(__FILE__));
}


layout_inline_page_begin();
page_title($t_page_title);

echo '<form action="' . $t_form_action . '" method="post" class="input-hover-form input-hover-form-reload">';
	echo form_security_field('project_update');
	input_hidden('project_id', $f_project_id);
	input_hidden('parent_id', $f_parent_id);

	actionbar_begin();
		button($t_btn_text, 'submit-btn', 'submit');
	actionbar_end();

	column_begin('4');
	table_begin(array(), 'no-border');

	table_row_bug_info_short('Name:', format_text('name', 'name', $t_project['name']));
	table_row_bug_info_short('Status:', format_select('status', 'status', project_status_list(), get_enum_element('project_status', $t_project['status'])));
	table_row_bug_info_short('Visibility:', format_select('view_state', 'view_state', view_status_list(), get_enum_element('project_view_state', $t_project['view_state'])));
	table_row_bug_info_short('Enabled:', format_checkbox('enabled', 'enabled', $t_project['enabled']));

	$g_project_override = ALL_PROJECTS;

	if(!file_is_uploading_enabled() && DATABASE !== config_get('file_upload_method')){
		$t_file_path = '';
		// Don't reveal the absolute path to non-administrators for security reasons
		if(current_user_is_administrator())
			$t_file_path = config_get('absolute_path_default_upload_folder');

		table_row_bug_info_short('File Path:', format_text('file_path', 'file_path', $t_file_path));
	}

	table_end();
	column_end();

	column_begin('8');
	table_begin(array(), 'no-border');

	table_row_bug_info_long('Description:', format_textarea('description', 'description', $t_project['description'], 'input-xs', 'width:100%!important;height:100px'), '15%');

	table_end();
	column_end();
echo '</form>';

event_signal('EVENT_MANAGE_PROJECT_CREATE_FORM');

layout_inline_page_end();
