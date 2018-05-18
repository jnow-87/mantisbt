<?php

require_once('../core.php');
require_api('database_api.php');
require_api('config_api.php');
require_api('gpc_api.php');
require_api('user_api.php');
require_api('version_api.php');
require_api('elements_api.php');


json_prepare();

$f_cmd = gpc_get_string('cmd', '');
$f_version_id = gpc_get_int('version_id', -1);
$f_project_id = gpc_get_int('project_id', -1);

if($f_version_id != -1){
	version_ensure_exists($f_version_id);
	$t_version = version_get($f_version_id);
}
else{
	$t_version = version_get_empty();
	$t_version->date_order = time();
}


switch($f_cmd){
case 'create':
	$t_page_title = 'Create Version';
	$t_form_action = 'version_update.php?cmd=create';
	$t_btn_text = 'Create';
	break;

case 'edit':
	$t_page_title = 'Edit Version: ' . $t_version->version;
	$t_form_action = 'version_update.php?cmd=set_details';
	$t_btn_text = 'Update';
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to ' . basename(__FILE__));
}


layout_inline_page_begin();
page_title($t_page_title);

echo '<form action="' . $t_form_action . '" method="post" class="input-hover-form input-hover-form-reload">';
	echo form_security_field('version_update');
	input_hidden('project_id', $f_project_id);
	input_hidden('version_id', $f_version_id);

	actionbar_begin();
		button($t_btn_text, 'submit-btn', 'submit');
	actionbar_end();

	echo '<div class="col-md-4">';
	table_begin(array(), 'no-border');

	table_row_bug_info_short('Version Name:', format_text('name', 'name', $t_version->version));
	table_row_bug_info_short('Release Date:', format_date('date', 'date', date(config_get('short_date_format'), $t_version->date_order), '', true));
	table_row_bug_info_short('Released:', format_checkbox('released', 'released', $t_version->released));
	table_row_bug_info_short('Obsolete:', format_checkbox('obsolete', 'obsolete', $t_version->obsolete));

	table_end();
	echo '</div>';

	echo '<div class="col-md-8">';
	table_begin(array(), 'no-border');

	table_row_bug_info_long('Description:', format_textarea('description', 'description', $t_version->description, 'input-xs', 'width:100%!important;height:300px'), '15%');

	table_end();

	echo '</div>';
echo '</form>';

layout_inline_page_end();
