<?php

if(!defined('INCLUDE_PERMISSIONS'))
 	return;

require_once('core.php');
require_api('elements_api.php');


/**
 *	print table row for permissions
 *
 *	@param	string	$p_caption		row caption (column 0)
 *	@param	string	$p_perm_name	name of the config option containing the permissions
 *	@param	array	$p_td_attr		column attributes
 */
function perm_row($p_caption, $p_perm_name, $p_td_attr){
	$t_access_lvls = MantisEnum::getValues(config_get('access_levels_enum_string'));
	$t_cfg = config_get($p_perm_name);

	if(!is_array($t_cfg)){
		$t_arr = array();

		foreach($t_access_lvls as $t_lvl){
			if($t_lvl >= $t_cfg)
				$t_arr[] = $t_lvl;
		}

		$t_cfg = $t_arr;
	}

	$t_row = array($p_caption);

	foreach($t_access_lvls as $t_lvl)
		$t_row[] = (in_array($t_lvl, $t_cfg) ? format_icon('fa-times') : '');

	table_row($t_row, '', $p_td_attr);
}

/**
 *	print table for a set of permissions
 *
 *	@param	string	$p_title	table title
 *	@param	array	$p_rows		array of permission caption and config option name
 */
function perm_table($p_title, $p_rows){
	/* get access levels */
	$t_access_lvls = MantisEnum::getValues(config_get('access_levels_enum_string'));
	$t_td_attr = array('class="thead" width="10%"');
	$t_header = array($p_title);

	foreach($t_access_lvls as $t_lvl){
		$t_header[] = MantisEnum::getLabel(lang_get('access_levels_enum_string'), $t_lvl);
		$t_td_attr[] = 'class="center"';
	}

	table_begin($t_header, 'table-condensed table-hover no-border', '', $t_td_attr);

	foreach($p_rows as $t_title => $t_cfg)
		perm_row($t_title, $t_cfg, $t_td_attr);

	table_end();
}


section_begin('Project Permissions');
	perm_table('Projects', array(
		'Create' => 'create_project_threshold',
		'Delete' => 'delete_project_threshold',
		'Manage' => 'manage_project_threshold',
		'User Access' => 'project_user_threshold',
		'Assign to private projects' => 'private_project_threshold',
	));

	if(config_get('enable_project_documentation') == ON){
		perm_table('Project Documents', array(
			'View' => 'view_proj_doc_threshold',
			'Upload' => 'upload_project_file_threshold'
		));
	}
section_end();

section_begin('Issue Permissions');
	perm_table('View', array(
		'Summary' => 'view_summary_threshold',
		'Change log' => 'view_changelog_threshold',
		'Assignee' => 'view_handler_threshold',
		'History' => 'view_history_threshold'
	));

	if(config_get('allow_file_upload') == ON){
		perm_table('Attachments', array(
			'View' => 'view_attachments_threshold',
			'Download' => 'download_attachments_threshold',
			'Delete' => 'delete_attachments_threshold',
			'Upload' => 'upload_bug_file_threshold'
		));
	}

	perm_table('Custom Fields', array(
		'Manage' => 'manage_custom_fields_threshold',
		'Link to project' => 'custom_field_link_threshold'
	));

	perm_table('Notes', array(
		'Add' => 'add_bugnote_threshold',
		'Edit' => 'bugnote_user_edit_threshold',
		'Edit others\'' => 'update_bugnote_threshold',
		'Change visibility' => 'bugnote_user_change_view_state_threshold',
		'Delete' => 'bugnote_user_delete_threshold',
		'Delete others\'' => 'delete_bugnote_threshold',
		'View private' => 'private_bugnote_threshold'
	));

	perm_table('Filters', array(
		'Save' => 'stored_query_create_threshold',
		'Save as shared' => 'stored_query_create_shared_threshold',
		'Use' => 'stored_query_use_threshold'
	));
section_end();

section_begin('Workflow Permissions');
	perm_table('Issue', array(
		'Report' => 'report_bug_threshold',
		'Update' => 'update_bug_threshold',
		'Monitor' => 'monitor_bug_threshold',
		'Handle' => 'handle_bug_threshold',
		'Assign' => 'update_bug_assign_threshold',
		'Move' => 'move_bug_threshold',
		'Delete' => 'delete_bug_threshold',
		'Reopen' => 'reopen_bug_threshold',
		'Update readonly' => 'update_readonly_bug_threshold',
		'Update status' => 'update_bug_status_threshold',
		'Set initial visibility' => 'set_view_status_threshold',
		'Update visibility' => 'change_view_status_threshold',
		'View monitoring users' => 'show_monitor_list_threshold'
	));
section_end();

section_begin('Miscellaneous Permissions');
	$t_misc = array(
		'View user mail address' => 'show_user_email_threshold',
		'Send reminders' => 'bug_reminder_threshold',
		'Receive reminders' => 'reminder_receive_threshold',
		'Manage users' => 'manage_user_threshold',
		'Notify for new users' => 'notify_new_user_created_threshold_min'
	);


	if(config_get('news_enabled') == ON){
		$t_misc['View private news'] = 'private_news_threshold';
		$t_misc['Manage news'] = 'manage_news_threshold';
	}

	perm_table('Miscellaneous', $t_misc);
section_end();

