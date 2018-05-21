<?php

require_once('../core.php');
require_api('database_api.php');
require_api('gpc_api.php');
require_api('user_api.php');
require_api('helper_api.php');
require_api('custom_field_api.php');
require_api('elements_api.php');


json_prepare();

$f_cmd = gpc_get_string('cmd', '');
$f_field_id = gpc_get_int('field_id', -1);

if($f_field_id != -1){
	custom_field_ensure_exists($f_field_id);
	$t_field = custom_field_get_definition($f_field_id);
	$t_project_ids = custom_field_get_project_ids($f_field_id);
}
else
	$t_field = custom_field_get_definition_empty();


auth_reauthenticate();

switch($f_cmd){
case 'create':
	access_ensure_global_level(config_get('manage_custom_fields_threshold'));
	
	$t_page_title = 'Create Custom Field';

	$t_cmd = 'create';
	$t_btn_text = 'Create';
	break;

case 'edit':
	access_ensure_global_level(config_get('manage_custom_fields_threshold'));

	$t_page_title = 'Edit Custom Field: ' . $t_field['name'];
	$t_cmd = 'update';
	$t_btn_text = 'Update';
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to ' . basename(__FILE__));
}


layout_inline_page_begin();
page_title($t_page_title);

actionbar_begin();
	echo '<div class="pull-left">';
		/* link project form */
		echo '<form method="post" action="settings/custom_field_update.php" class="form-inline input-hover-form">';
			input_hidden('custom_field_update_token', form_security_token('custom_field_update'));
			input_hidden('field_id', $f_field_id);
			input_hidden('cmd', 'add');
			input_hidden('redirect', format_href('settings/custom_field_edit_page.php', array('cmd' => $f_cmd, 'field_id' => $f_field_id)));

			select('project_id', 'project_id', project_list(false, $t_project_ids), '');
			hspace('2px');
			text('sequence', 'sequence', '0', 'Sequence', 'input-xs', '', 'size="2"');
			hspace('2px');
			button('Link Project', 'submit', 'submit');
		echo '</form>';
	echo '</div>';

	echo '<div class="pull-right">';
		/* update button */
		button($t_btn_text, 'submit-btn', 'submit', '', 'form-remote-trigger', false, 'data-form-id="custom_field_update_form"');
	echo '</div>';
actionbar_end();

/* field details */
echo '<form id="custom_field_update_form" action="settings/custom_field_update.php" method="post" class="input-hover-form input-hover-form-reload">';
	input_hidden('field_id', $f_field_id);
	input_hidden('cmd', $t_cmd);
	button('hidden-submit', 'submit-btn', 'submit', '', '', false, 'style="visibility:hidden"');
	echo form_security_field('custom_field_update');

	echo '<div class="col-md-6-left">';
	table_begin(array(), 'no-border');

	table_row_bug_info_short('Name:', format_text('name', 'name', string_attribute($t_field['name'])));
	table_row_bug_info_short('Type:', format_select('type', 'type', custom_field_type_list(), get_enum_element('custom_field_type', $t_field['type'])));
	table_row_bug_info_short('Possible Values:', format_text('possible_values', 'possible_values', string_attribute($t_field['possible_values']), 'separate by \'|\''));
	table_row_bug_info_short('Default Value:', format_textarea('default_value', 'default_value', string_attribute($t_field['default_value']), 'input-xs', 'width:100%!important;height:100px'));

	table_end();
	echo '</div>';

	echo '<div class="col-md-6-right">';
	table_begin(array(), 'no-border');

	table_row_bug_info_short('Read Access:', format_select('access_level_r', 'access_level_r', access_level_list(), get_enum_element('access_levels', $t_field['access_level_r'])));
	table_row_bug_info_short('Write Access:', format_select('access_level_rw', 'access_level_rw', access_level_list(), get_enum_element('access_levels', $t_field['access_level_rw'])));
	table_row_bug_info_short('Min. Length:', format_text('length_min', 'length_min', $t_field['length_min']));
	table_row_bug_info_short('Max. Length:', format_text('length_max', 'length_max', $t_field['length_max']));
	table_row_bug_info_short('Add to Filter:', format_checkbox('filter_by', 'filter_by', $t_field['filter_by']));

	table_end();
	echo '</div>';
echo '</form>';

/* linked projects */
echo '<div class="col-md-12">';
table_begin(array('', 'Project', 'Sequence'), 'table-condensed table-hover table-datatable no-border', '', array('width="1px"'));

foreach($t_project_ids as $t_project_id){
	$t_unlink_btn = '<form method="post" action="settings/custom_field_update.php" class="form-inline input-hover-form">'
				  . format_input_hidden('custom_field_update_token', form_security_token('custom_field_update'))
				  . format_input_hidden('field_id', $f_field_id)
				  . format_input_hidden('project_id', $t_project_id)
				  . format_input_hidden('cmd', 'rm')
				  . format_input_hidden('redirect', format_href('settings/custom_field_edit_page.php', array('cmd' => $f_cmd, 'field_id' => $f_field_id)))
				  . format_button('<i class="fa fa-unlink red"></i>', 'delete_' . $t_project_id, 'submit', '', 'btn-icon', true)
				  . '</form>';


	$t_linked_field_ids = custom_field_get_linked_ids($t_project_id);
	$t_sequence = '';

	foreach($t_linked_field_ids as $t_id){
		$t_sequence .= custom_field_get_field($t_id, 'name')
					. ' (' . custom_field_get_sequence($t_id, $t_project_id) . ')<br>';
	}

	table_row(array(
		$t_unlink_btn,
		project_get_name($t_project_id),
		$t_sequence
		)
	);
}

table_end();
echo '</div>';

layout_inline_page_end();
