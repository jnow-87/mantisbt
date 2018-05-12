<?php

require_once('../core.php');
require_api('layout_api.php');
require_api('project_api.php');
require_api('elements_api.php');


$f_filter_id = gpc_get_int('filter_id');
$f_filter_name = gpc_get_string('filter_name');


layout_inline_page_begin();
page_title('Edit Filter: ' . $f_filter_name);

echo '<form action="settings/filter_update.php" method="post" class="">';
	input_hidden('cmd', 'set_pref');
	input_hidden('filter_id', $f_filter_id);
	input_hidden('redirect', 'manage_filters_page.php');
	echo form_security_field('filter_update');

	actionbar_begin();
		button('Update', 'update-btn', 'submit');
	actionbar_end();

	table_begin(array(), 'no-border');

		table_row_bug_info_long('Name:', format_text('filter_name', 'filter_name', $f_filter_name), '10%');
		table_row_bug_info_long('Project:', format_select('project_id', 'project_id', project_list(true), project_get_name(filter_get_field($f_filter_id, 'project_id'))), '10%');
		table_row_bug_info_long('Visibility:', format_select('public', 'public', array('public' => 1, 'private' => 0), (filter_get_field($f_filter_id, 'is_public') ? 'public' : 'private')), '10%');

	table_end();
echo '</form>';

layout_inline_page_end();
