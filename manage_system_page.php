<?php

require_once('core.php');
require_api('layout_api.php');
require_api('elements_api.php');


function tab_custom_fields(){
}

function tab_plugins(){
	define('INCLUDE_PLUGIN', 1);
	include('settings/plugin_tab.php');
}

function tab_permissions(){
}

function tab_config(){
}

function tab_workflow(){
}


layout_page_header(__FILE__);
layout_page_begin();

page_title('Mantis Settings');

tabs(array(
		'Custom Fields' => 'tab_custom_fields',
		'Plugins' => 'tab_plugins',
		'Permission Report' => 'tab_permissions',
		'Configuration Report' => 'tab_config',
		'Workflow Report' => 'tab_workflow',
	)
);

layout_page_end();
