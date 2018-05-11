<?php
require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('current_user_api.php');
require_api('event_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('elements_api.php');


auth_reauthenticate();
access_ensure_global_level(config_get('manage_site_threshold'));

layout_page_header(__FILE__);
layout_page_begin();

page_title('About Mantis');


section_begin('Details');

echo '<div class="col-md-3">';

table_begin(array(), 'no-border');

table_row_bug_info_short('Mantis Version:', MANTIS_VERSION . config_get_global('version_suffix'));
table_row_bug_info_short('Database Scheme:', config_get('database_version'));

table_end();
echo '</div>';


if(current_user_is_administrator()){
	echo '<div class="col-md-3">';

	table_begin(array(), 'no-border');

		table_row_bug_info_short('Site Path:', config_get('absolute_path'));
		table_row_bug_info_short('Core Path:', config_get('core_path'));
		table_row_bug_info_short('Plugin Path:', config_get('plugin_path'));

	table_end();

	echo '</div>';
}

section_end();

layout_page_end();
?>
