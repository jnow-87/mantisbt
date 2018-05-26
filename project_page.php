<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Project Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once('core.php');
require_api('access_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('project_api.php');
require_api('string_api.php');
require_api('utility_api.php');
require_api('elements_api.php');

$f_project_id = gpc_get_int('project_id');
$t_project = project_get_row($f_project_id);

if($f_project_id == ALL_PROJECTS){
	print_header_redirect('set_project.php?project_id=' . $f_project_id . '&ref=filter_page.php');
	exit;
}


/* page content */
layout_page_header($t_project['name']);
layout_page_begin();

page_title('Project: ' . $t_project['name']);


/* left column */
column_begin('9');
section_begin('Description');
	echo $t_project['description'];
section_end();

section_begin('Versions');
	$t_versions = version_get_all_rows($f_project_id, null, null);

	table_begin(array('Version', 'Release State'), 'table-condensed table-hover no-border');

	foreach($t_versions as $t_version){
		$t_release_date = date(config_get('short_date_format'), $t_version_row['date_order']);

		if(!$t_version['obsolete']){
			if($t_version['released'])	$t_release_state = format_label('Released (' . $t_release_date . ')', 'label-success');
			else						$t_release_state = format_label('Planned Release (' . $t_release_date . ')', 'label-info');
		}
		else
			$t_release_state = format_label('Obsolete (' . $t_release_date . ')', 'label-danger');

		table_row(array(
			version_full_name($t_version['id'], false, $f_project_id),
			$t_release_state
			)
		);
	}

	table_end();
section_end();
column_end();

/* right column */
column_begin('3');
section_begin('Development Team');
	$t_users_all = project_get_all_user_rows($f_project_id, ANYBODY, true);

	foreach($t_users_all as $t_user)
		echo user_format_name($t_user['id']) . ' (' . get_enum_element('access_levels', $t_user['access_level']) . ')<br>';
section_end();

section_begin('Links');
	echo format_link('Project Issues', 'set_project.php', array('project_id' => $f_project_id, 'ref' => 'filter_page.php')) . '<br>';
	echo format_link('Releases', 'versions_page.php', array('type' => 'released', 'project_id' => $f_project_id)) . '<br>';
	echo format_link('Roadmap', 'versions_page.php', array('type' => 'unreleased', 'project_id' => $f_project_id)) . '<br>';

	if(config_get('enable_project_documentation') == ON)
		echo format_link('Documentation', 'proj_doc_page.php', array('project_id' => $f_project_id)) . '<br>';

	if(config_get('wiki_enable') == ON)
		echo format_link('Wiki', 'wiki.php?', array('type' => 'project', 'id' => $f_project_id)) . '<br>';

	if(access_has_project_level(config_get('view_summary_threshold'), $f_project_id))
		echo format_link('Summary', 'summary_page.php', array('project_id' => $f_project_id)) . '<br>';

	if(access_has_project_level(config_get('manage_project_threshold'), $f_project_id))
		echo format_link('Settings', 'settings/project_page.php', array('project_id' => $f_project_id)) . '<br>';
section_end();
column_end();

layout_page_end();
