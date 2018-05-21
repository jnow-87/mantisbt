<?php

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('category_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('icon_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('project_api.php');
require_api('project_hierarchy_api.php');
require_api('string_api.php');
require_api('user_api.php');
require_api('utility_api.php');


function project_row($p_project_id, $p_is_subproject){
	$t_project = project_get_row($p_project_id);

	$t_project_link = format_link($t_project['name'], 'settings/project_page.php', array('project_id' => $t_project['id']));

	if($p_is_subproject)
		$t_project_link = format_icon('fa-angle-double-right') . $t_project_link;

	$t_edit_btn = format_link(format_icon('fa-pencil'), 'settings/project_edit_page.php', array('cmd' => 'edit', 'project_id' => $p_project_id), 'inline-page-link', '', 'inline-page-reload');
	$t_delete_btn = format_button_confirm('Delete', 'settings/project_update.php',
					array('cmd' => 'delete', 'project_id' => $p_project_id, 'project_update_token' => form_security_token('project_update')),
					'Delete project?', 'danger', format_icon('fa-trash', 'red')
	);

	table_row(array(
			$t_edit_btn . $t_delete_btn,
			$t_project_link,
			get_enum_element('project_status', $t_project['status']),
			trans_bool($t_project['enabled']),
			get_enum_element('project_view_state', $t_project['view_state']),
			string_display_links($t_project['description'])
		)
	);

	$t_subprojects = project_hierarchy_get_subprojects($p_project_id, true);

	foreach($t_subprojects as $t_project_id)
		project_row($t_project_id, true);
}


auth_reauthenticate();

$t_user_id = auth_get_current_user_id();
$t_projects = user_get_accessible_projects($t_user_id, true);


layout_page_header('Projects');
layout_page_begin();

page_title('Project Settings');

actionbar_begin();
	button_link('Create', 'settings/project_edit_page.php', array('cmd' => 'create'), 'inline-page-link', false, true, 'inline-page-reload');
actionbar_end();

table_begin(array('', 'Project', 'Status', 'Enabled', 'Visibility', 'Description'), 'table-condensed table-hover table-datatable no-border', '', array('width="10px"'));
	foreach($t_projects as $t_project_id){
		if(!access_has_project_level(config_get('manage_project_threshold'), $t_project_id, $t_user_id))
			continue;

		project_row($t_project_id, false);
	}
table_end();

layout_page_end();
