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
						array('cmd' => 'delete', 'id' => $t_id, 'project_id' => ALL_PROJECTS, 'name' => '-', 'redirect' => 'manage_system_page.php', 'category_update_token' => form_security_token('category_update')),
						'Delete category?',	'danger', format_icon('fa-trash', 'red')
		);

		$t_name_input = category_form_header($t_id, 'update', true)
					  .	format_input_hover_text('name_' . $t_id, category_full_name($t_id, false))
					  . '</form>';

		table_row(array($t_delete_btn, $t_name_input));
	}

	table_end();
section_end();

section_begin('Custom Fields');
section_end();
