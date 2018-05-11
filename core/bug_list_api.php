<?php

require_api('config_api.php');
require_api('gpc_api.php');
require_api('access_api.php');
require_api('database_api.php');
require_api('project_api.php');
require_api('user_api.php');
require_api('helper_api.php');
require_api('columns_api.php');
require_api('custom_field_api.php');
require_api('html_api.php');
require_api('category_api.php');
require_api('worklog_api.php');
require_api('tag_api.php');
require_api('bug_api.php');
require_api('elements_api.php');


function format_content_id($p_bug){
	return format_link(bug_format_id($p_bug->id), 'view.php', array('id' => $p_bug->id, '#tab_0' => ''));
}

function format_content_project_id($p_bug){
	return format_link(project_get_name($p_bug->project_id, false), 'manage_proj_edit_page.php', array('project_id' => $p_bug->project_id));
}

function format_content_reporter_id($p_bug){
	return format_link(user_get_name($p_bug->reporter_id), 'view_user_page.php', array('id' => $t_id), '', 'margin-right:20px!important');
}

function format_content_handler_id($p_bug){
	return format_link(user_get_name($p_bug->handler_id), 'view_user_page.php', array('id' => $t_id), '', 'margin-right:20px!important');
}

function format_content_priority($p_bug){
	return get_enum_element('priority', $p_bug->priority);
}

function format_content_severity($p_bug){
	return get_enum_element('severity', $p_bug->severity);
}

function format_content_status($p_bug){
	$t_status_icon = '<i class="fa fa-square fa-status-box ' . html_get_status_css_class($p_bug->status) . '"></i> ';
	$t_status = get_enum_element('status', $p_bug->status);

	return $t_status_icon . $t_status;
}

function format_content_status_icon($p_bug){
	return '<i class="fa fa-square fa-status-box ' . html_get_status_css_class($p_bug->status) . '"></i> ';
}

function format_content_resolution($p_bug){
	return get_enum_element('resolution', $p_bug->resolution);
}

function format_content_category_id($p_bug){
	return category_get_name($p_bug->category_id);
}

function format_content_date_submitted($p_bug){
	return date(config_get('normal_date_format'), $p_bug->date_submitted);
}

function format_content_last_updated($p_bug){
	return date(config_get('normal_date_format'), $p_bug->last_updated);
}

function format_content_os($p_bug){
	return $p_bug->os;
}

function format_content_os_build($p_bug){
	return $p_bug->os_build;
}

function format_content_platform($p_bug){
	return $p_bug->platform;
}

function format_version($p_version, $p_project_id){
	if($p_version == '')
		return '';

	$t_version_id = version_get_id($p_version, $p_project_id);
	$t_released = version_get_field($t_version_id, 'released');
	$t_obsolete = version_get_field($t_version_id, 'obsolete');

	if($t_obsolete)
		$p_version = format_strike($p_version);

	$p_version = format_label($p_version, ($t_obsolete ? 'label-danger' : ($t_released ? 'label-success' : 'label-info')));

	return format_link($p_version, 'versions_page.php', array('version_id' => $t_version_id));
}

function format_content_version($p_bug){
	return format_version($p_bug->version, $p_bug->project_id);
}

function format_content_fixed_in_version($p_bug){
	return format_version($p_bug->fixed_in_version, $p_bug->project_id);
}

function format_content_target_version($p_bug){
	return format_version($p_bug->target_version, $p_bug->project_id);
}

function format_content_build($p_bug){
	return $p_bug->build;
}

function format_content_view_state($p_bug){
	return get_enum_element('view_state', $p_bug->view_state);
}

function format_content_summary($p_bug){
	return $p_bug->summary;
}

function format_content_due_date($p_bug){
	return date(config_get('normal_date_format'), $p_bug->due_date);
}

function format_content_description($p_bug){
	return $p_bug->description;
}

function format_content_time_tracking($p_bug){
	return db_minutes_to_hhmm(worklog_get_time_bug($p_bug->id));
}

function format_content_tags($p_bug){
	return format_tag_list($p_bug->id, false, false);
}

function format_content_selection($p_bug){
	if(access_has_any_project(config_get('report_bug_threshold', null, null, $p_bug->project_id)) ||
		# !TODO: check if any other projects actually exist for the bug to be moved to
		access_has_project_level(config_get('move_bug_threshold', null, null, $p_bug->project_id), $p_bug->project_id) ||
		# !TODO: factor in $g_auto_set_status_to_assigned == ON
		access_has_project_level(config_get('update_bug_assign_threshold', null, null, $p_bug->project_id), $p_bug->project_id) ||
		access_has_project_level(config_get('update_bug_threshold', null, null, $p_bug->project_id), $p_bug->project_id) ||
		access_has_project_level(config_get('delete_bug_threshold', null, null, $p_bug->project_id), $p_bug->project_id) ||
		# !TODO: check to see if the bug actually has any different selectable workflow states
		access_has_project_level(config_get('update_bug_status_threshold', null, null, $p_bug->project_id), $p_bug->project_id) ||
		access_has_project_level(config_get('change_view_status_threshold', null, null, $p_bug->project_id), $p_bug->project_id) ||
		access_has_project_level(config_get('add_bugnote_threshold', null, null, $p_bug->project_id), $p_bug->project_id) ||
		access_has_project_level(config_get('tag_attach_threshold', null, null, $p_bug->project_id), $p_bug->project_id) ||
		access_has_project_level(config_get('roadmap_update_threshold', null, null, $p_bug->project_id), $p_bug->project_id)) {

		return format_checkbox('select-' . $p_bug->id, 'bug_arr[]', false, '', '', 'value="' . $p_bug->id . '"');
	}
		
	return '';
}

function format_content_edit($p_bug){
	$t_can_edit = !bug_is_readonly($p_bug->id) && access_has_bug_level(config_get('update_bug_threshold'), $p_bug->id);
	return ($t_can_edit ? format_icon('fa-check') : format_icon('fa-times'));
}

function format_content_overdue($p_bug){
	return bug_is_overdue($p_bug->id) ? 'yes' : 'no';
}

function format_content_invalid($p_bug){
	return format_label('invalid', 'label-danger');
}

function format_content_notlinked($p_bug){
	return format_label('field not linked to project', 'label-danger');
}

/**
 *	@return array with all available columns
 */
function bug_list_columns_all(){
	$t_columns = config_get('bug_list_columns_all');

	/* add custom fields */
	$t_project_id = helper_get_current_project();
	$t_related_custom_field_ids = custom_field_get_linked_ids($t_project_id);

	foreach($t_related_custom_field_ids as $t_id){
		if(!custom_field_has_read_access_by_project_id($t_id, $t_project_id)) 
			continue;

		$t_def = custom_field_get_definition($t_id);
		$t_columns[] = 'custom_' . $t_def['name'];
	}

	return $t_columns;
}

/**
 *	return an array with the currently selected columns
 *
 *	@param	string	$p_config_opt		config option name, e.g. bug_list_columns_filter *
 *	@param	boolean	$p_ignore_form_input	ignore column configuration supplied through form
 *											arguments
 *
 *	@return	array containing the columns
 */
function bug_list_columns($p_config_opt, $p_ignore_form_input = false){
	$t_default = array();

	if($p_config_opt != '')
		$t_default = config_get($p_config_opt);

	if($p_ignore_form_input)
		return $t_default;

	$t_col_str = gpc_get_string('columns_str', '');

	if($t_col_str != '')
		return explode('|', $t_col_str);

	return gpc_get_string_array('columns_arr', $t_default);
}

/**
 *	print a table with bugs according to $p_bug_ids and columns according to $p_columns
 *
 *	@param	array	$p_bug_ids		list of bug ids to display
 *	@param	array	$p_columns		list of columns to display, see helper_get_columns_to_view() for valid fields
 *	@param	string	$p_table_class	table classes
 */
function bug_list_print($p_bug_ids, $p_columns, $p_table_class = ''){
	/* cache bugs */
	bug_cache_array_rows($p_bug_ids);

	/* prepare table header */
	$t_header = array();

	for($i=0; $i<count($p_columns); $i++){
		$t_title = column_title($p_columns[$i]);

		if($t_title === false){
			$t_title = '[invalid \'' . $p_columns[$i] . '\']';
			$p_columns[$i] = 'invalid';
		}

		$t_header[] = $t_title;
	}

	/* print table */
	table_begin($t_header, $p_table_class);

	foreach($p_bug_ids as $t_bug_id){
		$t_bug = bug_get($t_bug_id);
		$t_row = array();

		foreach($p_columns as $t_col){
			$t_title = column_title($t_col);
			$t_cf_id = custom_field_get_id_from_name($t_title);

			if($t_cf_id != null){
				if(custom_field_is_linked($t_cf_id, $t_bug->project_id)){
					$t_def = custom_field_get_definition($t_cf_id);
					$t_row[] = string_custom_field_value($t_def, $t_cf_id, $t_bug_id);
				}
				else
					$t_row[] = format_content_notlinked($t_bug);
			}
			else{
				$t_func = 'format_content_' . $t_col;
				$t_row[] = $t_func($t_bug);
			}
		}

		table_row($t_row);
	}

	table_end();
}
?>
