<?php


require_once('../core.php');
require_api('error_api.php');
require_api('gpc_api.php');
require_api('filter_api.php');
require_api('form_api.php');
require_api('authentication_api.php');
require_api('helper_api.php');
require_api('elements_api.php');


/**
 *	print inline page to query filter data
 */
function filter_form(){
	global $t_project_id;


	layout_inline_page_begin();
	page_title('Save Filter');

	echo '<form action="settings/filter_update.php" method="post" class="input-hover-form input-hover-form-reload">';
		input_hidden('cmd', 'save');
		echo form_security_field('filter_update');

		actionbar_begin();
		button('Save', 'save-btn', 'submit');
		actionbar_end();

		$t_filter_name = format_text('filter_name', 'filter_name', '', 'Name')
				 . format_hspace('2px')
				 . format_select('filter_select', 'filter_select', filter_get_list(), '');


		table_begin(array(), 'no-border');
		table_row_bug_info_long('Filter Name:', $t_filter_name, '10%');
		table_row_bug_info_long('Make Public:', format_checkbox('public', 'public'), '10%');
		table_row_bug_info_long('All Projects:', format_checkbox('all_projects', 'all_projects', ($t_project_id == ALL_PROJECTS ? true : false)), '10%');
		table_end();
	echo '</form>';

	layout_inline_page_end();
}


json_prepare();


$f_cmd = gpc_get_string('cmd', '');
$f_filter_id = gpc_get_int('filter_id', -1);
$f_filter_name = gpc_get_string('filter_name', '');
$f_public = gpc_get_bool('public', false);
$f_all_projects = gpc_get_bool('all_projects', false);
$f_redirect = gpc_get_string('redirect', '');


form_security_validate('filter_update');
auth_ensure_user_authenticated();
compress_enable();


$t_project_id = ($f_all_projects ? 0 : helper_get_current_project());
$t_succ_msg = '';

switch($f_cmd){
case 'save':
	if($f_filter_name == '')
		filter_form();

	// mantis_filters_table.name has a max length of 64
	if(!filter_name_valid_length($f_filter_name))
		json_error('Filter name too long');

	$t_filter_string = filter_db_get_filter(gpc_get_cookie(config_get('view_all_cookie'), ''));

	// named filters must not reference source query id
	$t_filter = filter_deserialize($t_filter_string);

	if(isset($t_filter['_source_query_id']))
		unset($t_filter['_source_query_id']);

	$t_filter_string = filter_serialize($t_filter);
	$t_filter_id = filter_db_set_for_current_user($t_project_id, $f_public,	$f_filter_name, $t_filter_string);

	if($t_filter_id == -1)
		json_error('Error saving filter');

	$t_succ_msg = 'Filter saved as ' . $f_filter_name;
	break;

case 'save_as':
	filter_form();
	break;

case 'delete':
	if($f_filter_id == -1)
		json_error('Invalid filter ID');

	if(!filter_db_can_delete_filter($f_filter_id))
		json_error('Filter not allowed to be deleted');

	filter_db_delete_filter($f_filter_id);
	$t_succ_msg = 'Filter deleted';
	break;

case 'set_pref':
	if($f_filter_id == -1)
		json_error('Invalid filter ID');

	$f_project_id = gpc_get_int('project_id', filter_get_field($f_filter_id, 'project_id'));
	$f_public = gpc_get_int('public', filter_get_field($f_filter_id, 'is_public'));
	$f_filter_name = gpc_get_string('filter_name', filter_get_field($f_filter_id, 'name'));

	$t_filter_string = filter_db_get_filter($f_filter_id);

	if(!$t_filter_string)
		json_error('Access denied for current user');

	$t_filter = filter_deserialize($t_filter_string);
	
	filter_db_update_filter($f_filter_id, filter_serialize($t_filter), $f_project_id, $f_public, $f_filter_name);
	$t_succ_msg = 'Filter updated';
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to '. basename(__FILE__));
}

if($f_redirect != '')
	print_header_redirect($f_redirect);

json_success($t_succ_msg);
