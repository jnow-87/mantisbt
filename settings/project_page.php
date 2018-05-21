<?php

require_once('../core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('category_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('current_user_api.php');
require_api('custom_field_api.php');
require_api('date_api.php');
require_api('event_api.php');
require_api('file_api.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('project_api.php');
require_api('project_hierarchy_api.php');
require_api('string_api.php');
require_api('user_api.php');
require_api('utility_api.php');
require_api('version_api.php');


function project_update_form_header($p_cmd, $p_reload){
	global $f_project_id;

	return '<form action="project_update.php" method="post" class="form-inline input-hover-form ' . ($p_reload ? 'input-hover-form-reload' : '') . '">'
		 . format_input_hidden('cmd', $p_cmd)
		 . format_input_hidden('project_id', $f_project_id)
		 . form_security_field('project_update');
}

function category_update_form_header($p_cmd){
	global $f_project_id;

	return '<form action="category_update.php" method="post" class="form-inline input-hover-form input-hover-form-reload">'
		 . format_input_hidden('cmd', $p_cmd)
		 . format_input_hidden('project_id', $f_project_id)
		 . form_security_field('category_update');
}


auth_reauthenticate();

form_security_purge('project_update');
form_security_purge('category_update');
form_security_purge('version_update');


$f_project_id = gpc_get_int('project_id');

project_ensure_exists($f_project_id);
access_ensure_project_level(config_get('manage_project_threshold'), $f_project_id);

$t_project = project_get_row($f_project_id);

$t_name = $t_project['name'];
$t_is_private = ($t_project['view_state'] == VS_PRIVATE);

/* user data */
$t_assigned_users = user_list($f_project_id);
$t_all_users = user_list(ALL_PROJECTS);

$t_users_unassigned = array_diff_assoc($t_all_users, $t_assigned_users);

/* category data */
$t_cat_all = category_list(ALL_PROJECTS);
$t_cat_assigned = category_list($f_project_id);
$t_cat_unassigned = array_diff_key($t_cat_all, $t_cat_assigned);

/* custom field data */
$t_custom_fields_all = custom_field_get_ids();
$t_custom_fields_assigned = custom_field_get_linked_ids($f_project_id);
$t_custom_fields_unassigned = array_diff($t_custom_fields_all, $t_custom_fields_assigned);

$t_custom_fields_unassigned_names = array();

foreach($t_custom_fields_unassigned as $t_id)
	$t_custom_fields_unassigned_names[custom_field_get_field($t_id, 'name')] = $t_id;

/* sub-project data */
$t_subproject_ids = current_user_get_accessible_subprojects($f_project_id, true);

$t_all_subprojects = project_hierarchy_get_subprojects($f_project_id, true);
$t_all_subprojects[] = $f_project_id;
$t_subproject_candidates = array();

foreach(project_get_all_rows() as $t_candidate){
	$t_id = $t_candidate['id'];

	if(in_array($t_id, $t_all_subprojects) || in_array($f_project_id, project_hierarchy_get_all_subprojects($t_id)) || !access_has_project_level(config_get('manage_project_threshold'), $t_id))
		continue;

	$t_subproject_candidates[$t_candidate['name']] = $t_id;
}

ksort($t_subproject_candidates);


layout_page_header($t_name);
layout_page_begin();

page_title('Project Settings: ' . $t_name);

/* left column */
echo '<div class="col-md-9">';
	/* details */
	section_begin('Details');
		actionbar_begin();
			echo '<div class="pull-right">';
				/* enabled/disable button */
				button_link(($t_project['enabled'] ? 'Disable' : 'Enable'), 'project_update.php', 
					array('project_id' => $f_project_id, 'cmd' => 'set_details', 'enabled' => !$t_project['enabled'],
					'redirect' => 'settings/project_page.php?project_id=' . $f_project_id, 'project_update_token' => form_security_token('project_update'))
				);

				/* delete button */
				if(access_has_global_level(config_get('delete_project_threshold'))){
					button_confirm('Delete', 'project_update.php',
						array('cmd' => 'delete', 'project_id' => $f_project_id, 'redirect' => 'manage_projects_page.php', 'project_update_token' => form_security_token('project_update')),
						'Delete project?', 'danger', format_icon('fa-trash', 'red')
					);
				}
			echo '</div>';
		actionbar_end();

		echo '<form action="project_update.php" method="post" class="input-hover-form input-hover-form-reload">';
			echo form_security_field('project_update');
			input_hidden('project_id', $f_project_id);
			input_hidden('cmd', 'set_details');
			input_hidden('enabled', $t_project['enabled']);


			echo '<div class="col-md-3">';
			table_begin(array(), 'no-border');

			table_row_bug_info_long('Name:', format_input_hover_text('name', $t_project['name']), '25%');
			table_row_bug_info_long('Status:', format_input_hover_select('status', project_status_list(), get_enum_element('project_status', $t_project['status'])), '25%');
			table_row_bug_info_long('Visibility:', format_input_hover_select('view_state', view_status_list(), get_enum_element('project_view_state', $t_project['view_state'])), '25%');

			$g_project_override = ALL_PROJECTS;

			if(!file_is_uploading_enabled() && DATABASE !== config_get('file_upload_method')){
				$t_file_path = '';
				// Don't reveal the absolute path to non-administrators for security reasons
				if(current_user_is_administrator())
					$t_file_path = config_get('absolute_path_default_upload_folder');

				table_row_bug_info_short('File Path:', format_input_hover_text('file_path', $t_file_path));
			}

			table_end();
			echo '</div>';

			echo '<div class="col-md-9">';
			table_begin(array(), 'no-border');

			table_row_bug_info_long('Description:', format_input_hover_textarea('description', $t_project['description'], '100%', '100px'), '10%');

			table_end();
			echo '</div>';
		echo '</form>';
	section_end();

	/* versions */
	section_begin('Versions');
		actionbar_begin();
			// add version button
			button_link('Create Version', 'version_edit_page.php', array('cmd' => 'create', 'project_id' => $f_project_id), 'inline-page-link');
		actionbar_end();

		$t_versions = version_get_all_rows($f_project_id, null, null);

		table_begin(array('', 'Version', 'Release Date', 'Release State', 'Description'), 'table-condensed table-hover table-sortable no-border', '', array('width="10px"'));

		foreach($t_versions as $t_version){
			$t_edit_btn = format_link(format_icon('fa-pencil'), 'version_edit_page.php',
				array('cmd' => 'edit', 'version_id' => $t_version['id']),
				'inline-page-link', '', 'inline-page-reload'
			);

			$t_release_btn = '';

			if(!$t_version['released'] && !$t_version['obsolete']){
				$t_release_btn = format_link(format_icon('fa-truck'), 'version_update.php',
					array('cmd' => 'set_details', 'version_id' => $t_version['id'], 'obsolete' => $t_version['obsolete'], 'released' => true, 'redirect' => 'settings/project_page.php?project_id=' . $f_project_id, 'version_update_token' => form_security_token('version_update'))
				);
			}

			$t_obsolete_btn = '';

			if(!$t_version['obsolete']){
				$t_obsolete_btn = format_link(format_icon('fa-ban', 'red'), 'version_update.php',
					array('cmd' => 'set_details', 'version_id' => $t_version['id'], 'obsolete' => true, 'released' => $t_version['released'], 'redirect' => 'settings/project_page.php?project_id=' . $f_project_id, 'version_update_token' => form_security_token('version_update'))
				);
			}

			$t_delete_btn = format_button_confirm('Delete', 'version_update.php',
					array('cmd' => 'delete', 'version_id' => $t_version['id'], 'version_update_token' => form_security_token('version_update')),
					'Delete version?', 'danger', format_icon('fa-trash', 'red')
				);

			if(!$t_version['obsolete']){
				if($t_version['released'])	$t_release_state = format_label('Released', 'label-success');
				else						$t_release_state = format_label('Unreleased', 'label-info');
			}
			else
				$t_release_state = format_label('Obsolete', 'label-danger');


			table_row(array(
				$t_edit_btn . $t_release_btn . $t_obsolete_btn . $t_delete_btn,
				version_full_name($t_version['id'], false, $f_project_id),
				date(config_get('short_date_format'), $t_version['date_order']),
				$t_release_state,
				string_display($t_version['description'])
				)
			);
		}

		table_end();
	section_end();

	/* sub-projects */
	section_begin('Sub-Projects');
		actionbar_begin();
			// link subproject
			echo '<form action="project_update.php" method="post" class="form-inline input-hover-form input-hover-form-reload">';
				echo form_security_field('project_update');
				input_hidden('parent_id', $f_project_id);
				input_hidden('cmd', 'subproject_link');

				select('project_id', 'project_id', $t_subproject_candidates, '');
				hspace('2px');
				button('Link', 'link-btn', 'submit');
			echo '</form>';
			
			// create subproject
			button_link('Create', 'project_edit_page.php', array('cmd' => 'create', 'parent_id' => $f_project_id, 'redirect' => 'settings/project_page.php?project_id=' . $f_project_id), 'inline-page-link', false, true, 'inline-page-reload');
		actionbar_end();

		table_begin(array('', 'Project', 'Status', 'Enabled', 'Visibility', 'Description'), 'table-condensed table-hover table-sortable no-border', '', array('width="10px"'));
			foreach($t_subproject_ids as $t_project_id){
				if(!access_has_project_level(config_get('manage_project_threshold'), $t_project_id, $t_user_id))
					continue;

				$t_project = project_get_row($t_project_id);

				$t_project_link = format_link($t_project['name'], 'project_page.php', array('project_id' => $t_project['id']));
				$t_unlink_btn = format_link(format_icon('fa-unlink', 'red'), 'project_update.php',
									array('cmd' => 'subproject_unlink', 'project_id' => $t_project_id, 'parent_id' => $f_project_id, 'project_update_token' => form_security_token('project_update')),
									'inline-page-link', '', 'inline-page-reload'
				);

				$t_delete_btn = format_button_confirm('Delete', 'project_update.php',
								array('cmd' => 'delete', 'project_id' => $t_project_id, 'project_update_token' => form_security_token('project_update')),
								'Delete project?', 'danger', format_icon('fa-trash', 'red')
				);


				table_row(array(
						$t_unlink_btn . $t_delete_btn,
						$t_project_link,
						get_enum_element('project_status', $t_project['status']),
						trans_bool($t_project['enabled']),
						get_enum_element('project_view_state', $t_project['view_state']),
						string_display_links($t_project['description'])
					)
				);
			}
		table_end();
	section_end();
echo '</div>';

/* right column */
echo '<div class="col-md-3">';
	/* users */
	section_begin('Users');
		actionbar_begin();
			// add user button
			echo project_update_form_header('user_add', true);
			select('user_id', 'user_id', $t_users_unassigned, '');
			hspace('2px');
			select('access_level', 'access_level', access_level_list(), '');
			hspace('2px');
			button('Add', 'add-user', 'submit');
			echo '</form>';
		actionbar_end();

		table_begin(array('', 'User Name', 'Access Level'), 'table-condensed table-hover table-sortable no-border', '', array('width="10px"'));

		$t_users_all = project_get_all_user_rows($f_project_id, ANYBODY, true);
		$t_users_local = project_get_all_user_rows($f_project_id, ANYBODY, false);
		$t_users_global = array_diff_key($t_users_all, $t_users_local);

		foreach($t_users_all as $t_user){
			if(in_array($t_user, $t_users_global)){
				$t_unlink_btn = '';
				$t_access = get_enum_element('access_levels', $t_user['access_level']) . ' (global access)';
			}
			else{
				$t_unlink_btn = '';

				if(access_has_project_level(config_get('project_user_threshold'), $f_project_id)){
					$t_unlink_btn = format_link(format_icon('fa-unlink', 'red'), 'project_update.php',
										array('cmd' => 'user_rm',
											  'project_id' => $f_project_id,
											  'user_id' => $t_user['id'],
											  'redirect' => 'settings/project_page.php?project_id=' . $f_project_id,
											  'project_update_token' => form_security_token('project_update')
										)
					);
				}

				$t_access = project_update_form_header('user_update', false)
					. format_input_hidden('user_id', $t_user['id'])
					. format_input_hover_select('access_level_' . $t_user['id'], access_level_list(), get_enum_element('access_levels', $t_user['access_level']))
					. '</form>';
			}

			table_row(array(
				$t_unlink_btn,
				user_format_name($t_user['id']),
				$t_access
			));
		}

		table_end();
	section_end();

	/* issue types */
	section_begin('Issues Types');
		actionbar_begin();
			// add category button
			echo category_update_form_header('create');
			select('id', 'id', $t_cat_unassigned, '');
			hspace('2px');
			button('Add', 'add-cat', 'submit');
			echo '</form>';
		actionbar_end();

		table_begin(array('', 'Type'), 'table-condensed table-hover table-sortable no-border', '', array('width="10px"'));

		foreach($t_cat_assigned as $t_name => $t_id){
			/* Handling inherited categories is only required for backwards compatibility. The current implementation does
			 * not allow to inherit categories, instead copies of the global categories are created for each project to
			 * avoid creating a new database table 
			 */
			$t_inherited = (category_get_field($t_id, 'project_id') != $f_project_id);

			if(!$t_inherited){
				$t_unlink_btn = format_link(format_icon('fa-unlink', 'red'), 'category_update.php',
											array('cmd' => 'delete',
												  'project_id' => $f_project_id,
												  'id' => $t_id,
												  'redirect' => 'settings/project_page.php?project_id=' . $f_project_id,
												  'category_update_token' => form_security_token('category_update')
											)
				);

				$t_inherited_link = '';
			}
			else{
				$t_unlink_btn = '';
				$t_inherited_link = ' (' . format_link('inherited', helper_mantis_url('manage_system_page.php')) . ')';
			}

			table_row(array(
				$t_unlink_btn,
				category_full_name($t_id, false, $f_project_id) . $t_inherited_link
				)
			);
		}

		table_end();
	section_end();

	/* custom fields */
	section_begin('Custom Fields');
		actionbar_begin();
			// add custom field button
			echo project_update_form_header('custom_field_add', true);
			select('custom_field_id', 'custom_field_id', $t_custom_fields_unassigned_names, '');
			hspace('2px');
			button('Add', 'add-cf', 'submit');
			echo '</form>';
		actionbar_end();

		table_begin(array('', 'Custom Field', 'Sequence'), 'table-condensed table-hover table-sortable no-border', '', array('width="10px"'));

		foreach($t_custom_fields_assigned as $t_field_id){
			$t_field = custom_field_get_definition($t_field_id);

			$t_name = format_link(custom_field_get_display_name($t_field['name']), helper_mantis_url('manage_system_page.php#tab_0'), array('field_id' => $t_field_id));
			$t_sequ = project_update_form_header('custom_field_update', false)
					. format_input_hidden('custom_field_id', $t_field_id)
					. format_input_hover_text('custom_field_sequence_' . $t_field_id, custom_field_get_sequence($t_field_id, $f_project_id))
					. '</form>';
			$t_unlink_btn = format_link(format_icon('fa-unlink', 'red'), 'project_update.php',
										array('cmd' => 'custom_field_rm',
											  'project_id' => $f_project_id,
											  'custom_field_id' => $t_field_id,
											  'redirect' => 'settings/project_page.php?project_id=' . $f_project_id,
											  'project_update_token' => form_security_token('project_update')
										)
			);

			table_row(array(
				$t_unlink_btn,
				$t_name,
				$t_sequ
				)
			);
		}

		table_end();
	section_end();
echo '</div>';

event_signal('EVENT_MANAGE_PROJECT_PAGE', array($f_project_id));

layout_page_end();

