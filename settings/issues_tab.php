<?php

if(!defined('INCLUDE_ISSUES'))
 	return;

require_once('core.php');
require_api('category_api.php');
require_api('form_api.php');
require_api('elements_api.php');


function category_form_header($p_category_id, $p_cmd, $p_input_hover){
	return '<form action="settings/category_update.php" method="post" class="' . ($p_input_hover ? 'input-hover-form' : '') . '">'
		  . format_input_hidden('cmd', $p_cmd)
		  . format_input_hidden('id', $p_category_id)
		  . format_input_hidden('project_id', ALL_PROJECTS)
		  . form_security_field('category_update');
}


form_security_purge('category_update');


$t_categories = category_get_all_rows(ALL_PROJECTS);
$t_can_update_global_cat = access_has_global_level(config_get('manage_site_threshold'));

/* issue types */
section_begin('Issue Types');
	actionbar_begin();
		echo category_form_header(-1, 'create', false);
		input_hidden('redirect', 'manage_system_page.php');

		text('name', 'name', '');
		hspace('2px');
		button('Create', 'create-btn', 'submit');
		echo '</form>';
	actionbar_end();

	table_begin(array('', 'Name'), 'table-condensed table-hover no-border', '', array('width="1px"'));

	foreach($t_categories as $t_category){
		$t_id = $t_category['id'];
		$t_delete_btn = format_button_confirm('Delete', 'settings/category_update.php',
						array('cmd' => 'delete', 'id' => $t_id, 'project_id' => ALL_PROJECTS, 'name' => '-', 'category_update_token' => form_security_token('category_update')),
						'Delete issue type?',	'danger', format_icon('fa-trash', 'red')
		);

		$t_name_input = category_form_header($t_id, 'update', true)
					  .	format_input_hover_text('name_' . $t_id, category_full_name($t_id, false))
					  . '</form>';

		table_row(array($t_delete_btn, $t_name_input));
	}

	table_end();
section_end();

/* custom fields */
section_begin('Custom Fields');
	actionbar_begin();
		button_link('Create', 'settings/custom_field_edit_page.php', array('cmd' => 'create'), 'inline-page-link', false, true, 'inline-page-reload');
	actionbar_end();


	$t_custom_fields = custom_field_get_ids();

	table_begin(array('', 'Name', 'Type', 'Valid Values', 'Default Value', 'Read Access', 'Write Access', 'Filter Enabled', 'Project Count', 'Issue Count'), 'table-condensed table-hover no-border', '', array('width="1px"'));

	foreach($t_custom_fields as $t_field_id){
		$t_field = custom_field_get_definition($t_field_id);
		$t_name = custom_field_get_display_name($t_field['name']);
		$t_n_proj = count(custom_field_get_project_ids($t_field_id));
		$t_n_issues = custom_field_set_in($t_field_id);


		$t_edit_btn = format_link(format_icon('fa-pencil'), 'settings/custom_field_edit_page.php',
						array('cmd' => 'edit', 'field_id' => $t_field_id),
						'inline-page-link', '', 'inline-page-reload'
		);

		$t_delete_btn = format_button_confirm('Delete', 'settings/custom_field_update.php',
						array('cmd' => 'delete', 'field_id' => $t_field_id, 'custom_field_update_token' => form_security_token('custom_field_update')),
						'Delete custom field?',	'danger', format_icon('fa-trash', 'red')
		);

		$t_force_delete_btn = '';

		if($t_n_proj > 0 || $t_n_issues > 0){
			$t_force_delete_btn = format_button_confirm('Delete', 'settings/custom_field_update.php',
							array('cmd' => 'force_delete', 'field_id' => $t_field_id, 'custom_field_update_token' => form_security_token('custom_field_update')),
							'Force custom field deletion?<br>Custom field is linked to ' . $t_n_proj . ' project(s) and is set for ' . $t_n_issues . ' issue(s).',
							'danger', format_icon('fa-exclamation', 'red')
			);
		}

		table_row(array(
			$t_edit_btn . $t_delete_btn . $t_force_delete_btn,
			$t_name,
			get_enum_element('custom_field_type', $t_field['type']),
			str_replace('|', ' | ', string_display($t_field['possible_values'])),
			string_display($t_field['default_value']),
			get_enum_element('access_levels', $t_field['access_level_r']),
			get_enum_element('access_levels', $t_field['access_level_rw']),
			trans_bool($t_field['filter_by']),
			$t_n_proj,
			$t_n_issues
			)
		);
	}

	table_end();
section_end();
