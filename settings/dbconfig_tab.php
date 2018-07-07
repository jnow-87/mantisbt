<?php

if(!defined('INCLUDE_PERMISSIONS'))
 	return;

require_once('core.php');
require_api('access_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('database_api.php');
require_api('form_api.php');
require_api('project_api.php');
require_api('string_api.php');
require_api('user_api.php');
require_api('elements_api.php');


access_ensure_global_level(config_get('view_configuration_threshold'));


/**
 * returns the configuration type for a given configuration type id
 * @param integer $p_type Configuration type identifier to check.
 * @return string configuration type
 */
function get_config_type($p_type){
	static $t_config_types = array(
		CONFIG_TYPE_DEFAULT => 'default',
		CONFIG_TYPE_INT     => 'integer',
		CONFIG_TYPE_FLOAT   => 'float',
		CONFIG_TYPE_COMPLEX => 'complex',
		CONFIG_TYPE_STRING  => 'string',
	);


	if(array_key_exists($p_type, $t_config_types))
		return $t_config_types[$p_type];
	return $t_config_types[CONFIG_TYPE_DEFAULT];
}

/**
 * Display a given config value appropriately
 * @param integer $p_type        Configuration type id.
 * @param mixed   $p_value       Configuration value.
 * @return void
 */
function get_config_value($p_type, $p_value){
	switch($p_type){
		case CONFIG_TYPE_DEFAULT:
			return '';

		case CONFIG_TYPE_FLOAT:
			return (float)$p_value;

		case CONFIG_TYPE_INT:
			return (integer)$p_value;

		case CONFIG_TYPE_COMPLEX:
			$t_value = @json_decode($p_value, true);

			if($t_value === false)
				return 'Configuration corrupt';
			break;

		case CONFIG_TYPE_STRING:
		default:
			$t_value = config_eval($p_value);
			break;
	}

	return string_nl2br(string_attribute(var_export($t_value, true)));
}

$t_result = db_query('SELECT config_id, user_id, project_id, type, value, access_reqd	FROM {config} ORDER BY user_id, project_id, config_id');

table_begin(array('', 'Configuration Option', 'Value', 'Type', 'User', 'Project', 'Access Level'), 'table-condensed table-hover table-datatable no-border', '', array('width="1px"'));
	while($t_row = db_fetch_array($t_result)){
		extract($t_row, EXTR_PREFIX_ALL, 'v');

		$t_delete_btn = '';

		if(config_can_delete( $v_config_id)){
			$t_delete_btn = format_button_confirm('Delete', 'settings/dbconfig_update.php',
							array('cmd' => 'delete', 'config_id' => $v_config_id, 'user_id' => $v_user_id, 'project_id' => $v_project_id, 'dbconfig_update_token' => form_security_token('dbconfig_update')),
							'Delete database configuration option?', 'danger', format_icon('fa-trash', 'red'));
		}

		table_row(array(
			$t_delete_btn,
			$v_config_id,
			get_config_value($v_type, $v_value),
			get_config_type($v_type),
			($v_user_id == 0) ? 'All Users' : user_format_name($v_user_id),
			format_link(project_get_name($v_project_id, false), 'project_page.php', array('project_id' => $v_project_id)),
			get_enum_element('access_levels', $v_access_reqd)
			)
		);
	}
table_end();
