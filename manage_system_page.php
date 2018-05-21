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
}

function tab_permissions(){
	section_begin('Issues');
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

		perm_table('Filters', array(
			'Save' => 'stored_query_create_threshold',
			'Save as shared' => 'stored_query_create_shared_threshold',
			'Use' => 'stored_query_use_threshold'
		));
	section_end();

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

	section_begin('Miscellaneous');
		$t_misc = array(
			'View summary' => 'view_summary_threshold',
			'View user mail address' => 'show_user_email_threshold',
			'Send reminders' => 'bug_reminder_threshold',
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


layout_page_header(__FILE__);
layout_page_begin();

page_title('Mantis Settings');

tabs(array(
		'Issues' => 'tab_issues',
		'Plugins' => 'tab_plugins',
		'Workflows' => 'tab_workflows',
		'Permission Report' => 'tab_permissions',
		'Configuration Report' => 'tab_config',
	)
);

layout_page_end();
