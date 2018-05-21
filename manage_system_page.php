<?php

require_once('core.php');
require_api('layout_api.php');
require_api('elements_api.php');


/**
 *	print table row for permissions
 *
 *	@param	string	$p_caption		row caption (column 0)
 *	@param	string	$p_perm_name	name of the config option containing the permissions
 */
function perm_row($p_caption, $p_perm_name){
	$t_access_lvls = MantisEnum::getValues(config_get('access_levels_enum_string'));
	$t_cfg = config_get($p_perm_name);

	$t_row = array($p_caption);

	foreach($t_access_lvls as $t_lvl)
		$t_row[] = ($t_lvl >= (int)$t_cfg ? format_icon('fa-check') : '');

	table_row($t_row);
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
	$t_td_attr = array('width="10%"');
	$t_header = array($p_title);

	foreach($t_access_lvls as $t_lvl)
		$t_header[] = MantisEnum::getLabel(lang_get('access_levels_enum_string'), $t_lvl);

	table_begin($t_header, 'table-condensed table-hover no-border', '', $t_td_attr);

	foreach($p_rows as $t_title => $t_cfg)
		perm_row($t_title, $t_cfg);

	table_end();
}

function option_row($p_title, $p_opt_name, $p_type){
	$t_str_val = '';
	$t_opt_val = config_get($p_opt_name);

	if($p_type == 'bool'){
		$t_str_val = trans_bool($t_opt_val);
	}
	else{
		// assume enum
		$t_str_val = get_enum_element($p_type, $t_opt_val);
	}
	
	table_row_bug_info_short($p_title . ':', $t_str_val);
}

/**
 *	print workflow permissions
 */
function workflow_perm_table(){
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
}


/**
 *	one function per system management tab
 */
function tab_issues(){
	define('INCLUDE_ISSUES', 1);
	include('settings/issues_tab.php');
}

function tab_plugins(){
	define('INCLUDE_PLUGIN', 1);
	include('settings/plugin_tab.php');
}

function tab_workflows(){
	section_begin('Transistions');
	section_end();

	section_begin('Configuration');
		echo '<div class="col-md-3">';
			table_begin(array(), 'no-border');
			option_row('New issue status', 'bug_submit_status', 'status');
			option_row('Reopen issue status', 'bug_reopen_status', 'status');
			option_row('Reopen resolution', 'bug_reopen_resolution', 'resolution');
			option_row('Resolve issue status', 'bug_resolved_status_threshold', 'status');
			option_row('Readonly issue status', 'bug_readonly_status_threshold', 'status');
			option_row('Assigned issue status', 'bug_assigned_status', 'status');
			table_end();
		echo '</div>';

		echo '<div class="col-md-3">';
			table_begin(array(), 'no-border');
			option_row('Report can close', 'allow_reporter_close', 'bool');
			option_row('Report can re-open', 'allow_reporter_reopen', 'bool');
			option_row('Update status when assigning', 'auto_set_status_to_assigned', 'bool');
			option_row('Limit report access to own issues', 'limit_reporters', 'bool');
			table_end();
		echo '</div>';
	section_end();

	section_begin('Permissions');
		workflow_perm_table();
	section_end();
}

function tab_permissions(){
	section_begin('Projects');
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

	section_begin('Issues');
		perm_table('View', array(
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

	section_begin('Workflow');
		workflow_perm_table();
	section_end();

	section_begin('Miscellaneous');
		$t_misc = array(
			'View summary' => 'view_summary_threshold',
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
}

function tab_config(){
}


/* page content */
layout_page_header(__FILE__);
layout_page_begin();

page_title('Mantis Settings');

tabs(array(
		'Issues' => 'tab_issues',
		'Plugins' => 'tab_plugins',
		'Workflow' => 'tab_workflows',
		'Permission Report' => 'tab_permissions',
		'Configuration Report' => 'tab_config',
	)
);

layout_page_end();
