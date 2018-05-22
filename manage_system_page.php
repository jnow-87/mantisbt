<?php

require_once('core.php');
require_api('layout_api.php');
require_api('helper_api.php');
require_api('elements_api.php');


function tab_issues(){
	define('INCLUDE_ISSUES', 1);
	include('settings/issues_tab.php');
}

function tab_plugins(){
	define('INCLUDE_PLUGIN', 1);
	include('settings/plugin_tab.php');
}

function tab_workflows(){
	define('INCLUDE_WORKFLOW', 1);
	include('settings/workflow_tab.php');
}

function tab_permissions(){
	define('INCLUDE_PERMISSIONS', 1);
	include('settings/permissions_tab.php');
}

function tab_config(){
}


/* page content */
layout_page_header(__FILE__);
layout_page_begin();

page_title('Mantis Settings');

tabs(array(
		'Issues' => 'tab_issues',
		'Plugins' => 'tab_plugins',
		'Workflow' => 'tab_workflows',
		'Permissions' => 'tab_permissions',
		'Configuration' => 'tab_config',
	)
);

layout_page_end();
