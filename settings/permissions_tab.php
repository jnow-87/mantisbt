<?php

if(!defined('INCLUDE_PERMISSIONS'))
 	return;

require_once('core.php');
require_api('email_api.php');
require_api('elements_api.php');


/**
 * array_merge_recursive2()
 *
 * Similar to array_merge_recursive but keyed-valued are always overwritten.
 * Priority goes to the 2nd array.
 *
 * @public yes
 * @param array|string|integer $p_array1 Array.
 * @param array|string|integer $p_array2 Array.
 * @return array
 */
function array_merge_recursive2($p_array1, $p_array2){
	if(!is_array($p_array1) || !is_array($p_array2))
		return $p_array2;

	$t_merged_array = $p_array1;

	foreach($p_array2 as $t_key2 => $t_value2){
		if(array_key_exists($t_key2, $t_merged_array) && is_array($t_value2)){
			$t_merged_array[$t_key2] = array_merge_recursive2($t_merged_array[$t_key2], $t_value2);
		}
		else{
			$t_merged_array[$t_key2] = $t_value2;
		}
	}
	return $t_merged_array;
}

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
		$t_header[] = MantisEnum::getLabel(config_get('access_levels_enum_string'), $t_lvl);
		$t_td_attr[] = 'class="center"';
	}

	table_begin($t_header, 'table-condensed table-hover no-border', '', $t_td_attr);

	foreach($p_rows as $t_title => $t_cfg)
		perm_row($t_title, $t_cfg, $t_td_attr);

	table_end();
}

function perm_row_mail($p_caption, $p_perm_name, $p_td_attr){
	static $t_access_lvls = null;
	static $t_actions = null;
	static $t_default_notify_flags = null;
	static $t_notify_flags = null;

	/* one time static variable initialisation */
	if($t_access_lvls == null){
		$t_access_lvls = MantisEnum::getValues(config_get('access_levels_enum_string'));
		$t_actions = email_get_actions();
		$t_default_notify_flags = config_get('default_notify_flags');
		$t_notify_flags = array();

		foreach($t_default_notify_flags as $t_flag => $t_value){
			foreach($t_actions as $t_action)
				$t_notify_flags[$t_action][$t_flag] = $t_value;
		}

		$t_notify_flags = array_merge_recursive2($t_notify_flags, config_get('notify_flags'));
	}

	$func_mail_perm = function($p_flag) use ($p_perm_name, $t_notify_flags, $t_default_notify_flags){
		if(isset($t_notify_flags[$p_perm_name][$p_flag]))
			return $t_notify_flags[$p_perm_name][$p_flag];

		if(isset($t_default_notify_flags[$p_flag]))
			return $t_default_notify_flags[$p_flag];

		return false;
	};

	$t_row = array($p_caption);

	$t_row[] = ($func_mail_perm('reporter') ? format_icon('fa-times') : '');
	$t_row[] = ($func_mail_perm('handler') ? format_icon('fa-times') : '');
	$t_row[] = ($func_mail_perm('monitor') ? format_icon('fa-times') : '');
	$t_row[] = ($func_mail_perm('bugnotes') ? format_icon('fa-times') : '');
	$t_row[] = ($func_mail_perm('category') ? format_icon('fa-times') : '');

	foreach($t_access_lvls as $t_lvl)
		$t_row[] = ($t_lvl >= $func_mail_perm('threshold_min') && $t_lvl <= $func_mail_perm('threshold_max') ? format_icon('fa-times') : '');

	table_row($t_row, '', $p_td_attr);
}


section_begin('Project ');
	perm_table('Projects', array(
		'Create' => 'create_project_threshold',
		'Delete' => 'delete_project_threshold',
		'Manage' => 'manage_project_threshold',
		'User Access' => 'project_user_threshold',
		'Assign to Private Projects' => 'private_project_threshold',
	));

	if(config_get('enable_project_documentation') == ON){
		perm_table('Project Documents', array(
			'View' => 'view_proj_doc_threshold',
			'Upload' => 'upload_project_file_threshold'
		));
	}
section_end();

section_begin('Issue ');
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
		'Link to Project' => 'custom_field_link_threshold'
	));

	perm_table('Notes', array(
		'Add' => 'add_bugnote_threshold',
		'Edit' => 'bugnote_user_edit_threshold',
		'Edit Others\'' => 'update_bugnote_threshold',
		'Change Visibility' => 'bugnote_user_change_view_state_threshold',
		'Delete' => 'bugnote_user_delete_threshold',
		'Delete Others\'' => 'delete_bugnote_threshold',
		'View Private' => 'private_bugnote_threshold'
	));

	perm_table('Filters', array(
		'Save' => 'stored_query_create_threshold',
		'Save as Shared' => 'stored_query_create_shared_threshold',
		'Use' => 'stored_query_use_threshold'
	));
section_end();

section_begin('Mail');
	$t_misc = array('View User Mail Address' => 'show_user_email_threshold');

	if(config_get('enable_email_notification')){
		$t_misc['Send Reminders'] = 'bug_reminder_threshold';
		$t_misc['Receive Reminders'] = 'reminder_receive_threshold';
		$t_misc['Notify for New Users'] = 'notify_new_user_created_threshold_min';
	}

	perm_table('Miscellaneous', $t_misc);

	if(config_get('enable_email_notification')){
		$t_header = array(
			'Notification',
			'Author',
			'Assignee',
			'Monitoring User',
			'User who Added Notes',
			'Issue Type Owner'
		);

		$t_td_attr = array(
			'class="thead" width="10%"',
			'class="center"',
			'class="center"',
			'class="center"',
			'class="center"',
			'class="center"',
		);

		$t_access_lvls = MantisEnum::getValues(config_get('access_levels_enum_string'));

		foreach($t_access_lvls as $t_lvl){
			$t_header[] = MantisEnum::getLabel(config_get('access_levels_enum_string'), $t_lvl);
			$t_td_attr[] = 'class="center"';
		}

		table_begin($t_header, 'table-condensed table-hover no-border', '', $t_td_attr);
			perm_row_mail('on Update', 'updated', $t_td_attr);
			perm_row_mail('on Assignee update', 'owner', $t_td_attr);
			perm_row_mail('on Reopened', 'reopened', $t_td_attr);
			perm_row_mail('on Delete', 'deleted', $t_td_attr);
			perm_row_mail('on Bugnote create', 'bugnote', $t_td_attr);
			perm_row_mail('on Link update', 'relation', $t_td_attr);

			$t_states = MantisEnum::getAssocArrayIndexedByValues(config_get( 'status_enum_string'));

			foreach($t_states as $t_status_id => $t_label)
				perm_row_mail('on Tansition to \'' . get_enum_element('status', $t_status_id) . '\'', $t_label, $t_td_attr);
		table_end();
	}
section_end();

section_begin('Workflow ');
	perm_table('Issue', array(
		'Report' => 'report_bug_threshold',
		'Update' => 'update_bug_threshold',
		'Monitor' => 'monitor_bug_threshold',
		'Handle' => 'handle_bug_threshold',
		'Assign' => 'update_bug_assign_threshold',
		'Move' => 'move_bug_threshold',
		'Delete' => 'delete_bug_threshold',
		'Reopen' => 'reopen_bug_threshold',
		'Update Readonly' => 'update_readonly_bug_threshold',
		'Update Status' => 'update_bug_status_threshold',
		'Set Initial Visibility' => 'set_view_status_threshold',
		'Update Visibility' => 'change_view_status_threshold',
		'View Monitoring Users' => 'show_monitor_list_threshold'
	));
section_end();

section_begin('Miscellaneous ');
	$t_misc = array(
		'Manage Users' => 'manage_user_threshold',
	);

	if(config_get('news_enabled')){
		$t_misc['View Private News'] = 'private_news_threshold';
		$t_misc['Manage News'] = 'manage_news_threshold';
	}

	perm_table('Miscellaneous', $t_misc);
section_end();
