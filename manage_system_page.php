<?php

require_once('core.php');
require_api('layout_api.php');
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
}

function tab_permissions(){
}

function tab_config(){
}


layout_page_header(__FILE__);
layout_page_begin();

page_title('Mantis Settings');

tabs(array(
		'Issues' => 'tab_issues',
		'Plugins' => 'tab_plugins',
		'Workflows' => 'tab_workflows',
		'Permission Report' => 'tab_permissions',
		'Configuration Report' => 'tab_config',
	)
);

layout_page_end();
