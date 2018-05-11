<?php

require_once('../core.php');
require_api('database_api.php');
require_api('gpc_api.php');
require_api('user_api.php');
require_api('elements_api.php');


function form_header($p_cmd, $p_project_id = -1, $p_access_level = -1){
	global $f_user_id;

	return '<form method="post" action="settings/user_update.php" class="form-inline input-hover-form">'
		 . format_input_hidden('user_update_token', form_security_token('user_update'))
		 . format_input_hidden('user_id', $f_user_id)
		 . format_input_hidden('redirect', 'settings/user_edit_page.php?cmd=edit&user_id=' . $f_user_id)
		 . ($p_project_id != -1 ? format_input_hidden('project_id', $p_project_id) : '')
		 . ($p_access_level != -1 ? format_input_hidden('access_level', $p_access_level) : '')
		 . format_input_hidden('cmd', $p_cmd);
}



json_prepare();

$f_cmd = gpc_get_string('cmd', '');

switch($f_cmd){
case 'create':
	$t_btn_action = 'settings/user_update.php?cmd=create';
	$t_btn_text = 'Create';
	$f_user_id = -1;
	$t_user = user_get_row_empty();

	$t_show_user_input = true;
	$t_show_assigned_projects = false;
	$t_show_pw_input = true;
	break;

case 'edit':
	$f_user_id = gpc_get_string('user_id');
	$t_user = user_get_row($f_user_id);

	$t_btn_action = 'settings/user_update.php?cmd=set_full_details';
	$t_btn_text = 'Update';

	$t_show_user_input = true;
	$t_show_assigned_projects = true;
	$t_show_pw_input = false;
	break;

case 'reset_pw':
	$f_user_id = gpc_get_string('user_id');
	$t_user = user_get_row($f_user_id);

	$t_btn_action = 'settings/user_update.php?cmd=reset_pw';
	$t_btn_text = 'Set Password';

	$t_show_user_input = false;
	$t_show_assigned_projects = false;
	$t_show_pw_input = true;
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to ' . basename(__FILE__));
}


$t_date_format = config_get('normal_date_format');


layout_inline_page_begin();
page_title('Edit User: ' . $t_user['username']);

echo '<form action="" method="post" class="">';
	echo form_security_field('user_update');
	input_hidden('user_id', $f_user_id);
	input_hidden('redirect', 'users_page.php');

	actionbar_begin();
		button($t_btn_text, 'submit-btn', 'submit', $t_btn_action);
	actionbar_end();

	/* account details */
	section_begin('Details');
		echo '<div class="col-md-6-left">';

		table_begin(array(), 'no-border');

		if($t_show_user_input){
			table_row_bug_info_short('User Name:', format_text('username', 'username', $t_user['username']));
			table_row_bug_info_short('Real Name:', format_text('realname', 'realname', $t_user['realname']));
			table_row_bug_info_short('eMail:', format_text('email', 'email', $t_user['email']));
		}

		if($t_show_pw_input){
			table_row_bug_info_short('Password:', format_password('pw_new0', 'pw_new0'));
			table_row_bug_info_short('Confirm Password:', format_password('pw_new1', 'pw_new1'));
		}

		table_end();

		echo '</div>';

		echo '<div class="col-md-6-right">';

		table_begin(array(), 'no-border');

		if($t_show_user_input){
			table_row_bug_info_short('Access Level:', format_select('access_level', 'access_level', access_level_list(), get_enum_element('access_levels', $t_user['access_level'])));
			table_row_bug_info_short('Enabled:', format_checkbox('enabled', 'enabled', $t_user['enabled']));
			table_row_bug_info_short('Protected:', format_checkbox('protected', 'protected', $t_user['protected']));
		}

		table_end();

		echo '</div>';
	section_end();
echo '</form>';

/* assigned projects */
if($t_show_assigned_projects){
	section_begin('Assigned Projects');
		actionbar_begin();
			$t_projects = user_get_unassigned_projects($f_user_id);
			$t_project_names = array();

			foreach($t_projects as $t_project)
				$t_project_names[$t_project['name']] = $t_project['id'];

			echo form_header('assign_project');
			select('project_id', 'project_id', $t_project_names, '');
			select('access_level', 'access_level', access_level_list(), '');
			button('Assign', 'assign', 'submit');
			echo '</form>';
		actionbar_end();

		if(user_get_access_level($f_user_id) == ACC_ADMIN)
			$t_projects = project_list();
		else
			$t_projects = user_get_assigned_projects($f_user_id, true);

		table_begin(array('', 'Project', 'Access Level', 'Visibility', 'Description'), 'table-condensed table-hover no-border');

		foreach($t_projects as $t_id){
			$t_unassign_btn = form_header('unassign_project', $t_id)
				. format_button('<i class="fa fa-trash red"></i>', 'unassign_' . $t_id, 'submit', '', 'btn-icon', true)
				. '</form>';

			table_row(array(
					$t_unassign_btn,
					format_link(project_get_name($t_id, false), helper_mantis_url('manage_proj_edit_page.php'), array('project_id' => $t_id)),
					form_header('assign_project', $t_id)
					. format_input_hover_select('access_level_' . $t_id, access_level_list(), get_enum_element('access_levels', user_get_access_level($f_user_id, $t_id)))
					. '</form>',
					get_enum_element('project_view_state', project_get_field($t_id, 'view_state')),
					project_get_field($t_id, 'description')
				)
			);
		}

		table_end();
	section_end();
}

layout_inline_page_end();
