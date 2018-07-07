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
 * Display Project Roadmap
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('database_api.php');
require_api('error_api.php');
require_api('filter_api.php');
require_api('filter_constants_inc.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('project_api.php');
require_api('string_api.php');
require_api('user_api.php');
require_api('utility_api.php');
require_api('version_api.php');
require_api('bug_list_api.php');
require_api('elements_api.php');


/**
 *	get list of issues that meet the arguments filter
 *
 *	@param	string		$p_project_id		target project id
 *	@param	string		$p_version			version string
 *	@param	string		$p_version_type		defines which versions to query
 *											either 'fixed_in_version' or 'target_version'
 *
 *	@param	reference	$p_issues_resolved	referenced updated with the number of resolved issues
 *
 *	@return	array of issue ids
 */
function db_query_roadmap_info($p_project_id, $p_version, $p_version_type, &$p_issues_resolved){
	$p_issues_resolved = 0;

	$t_can_view_private = access_has_project_level(config_get('private_bug_threshold'), $p_project_id);
	$t_limit_reporters = config_get('limit_reporters');
	$t_user_access_level_is_reporter = (config_get('report_bug_threshold', null, null, $p_project_id) == access_get_project_level($p_project_id));

	$t_query = 'SELECT id,view_state from {bug} WHERE project_id=' . db_param() . ' AND ' . $p_version_type . '=' . db_param();

	$t_result = db_query($t_query, array($p_project_id, $p_version));

	$t_issue_ids = array();

	while($t_row = db_fetch_array($t_result)){
		$t_issue_id = $t_row['id'];

		# hide private bugs if user doesn't have access to view them.
		if(!$t_can_view_private && ($t_row['view_state'] == VS_PRIVATE))
			continue;

		# check limit_Reporter (Issue #4770)
		# reporters can view just issues they reported
		if(ON === $t_limit_reporters && $t_user_access_level_is_reporter && !bug_is_user_reporter($t_issue_id, auth_get_current_user_id()))
			continue;

		if(!helper_call_custom_function('roadmap_include_issue', array($t_issue_id)))
			continue;

		$t_issue_ids[] = $t_issue_id;

		if(bug_is_resolved($t_issue_id))
			$p_issues_resolved++;
	}

	return $t_issue_ids;
}

/**
 *	print the tab for a single project, the respective project is identified through $t_project_ids
 */
function tab_project(){
	global $t_project_ids;
	global $f_version_id;
	global $t_version_type;
	static $i = 0;


	$f_columns = bug_list_columns('bug_list_columns_versions');

	$t_project_id = $t_project_ids[$i];
	$i++;

	/* get all project versions */
	$t_version_rows = version_get_all_rows($t_project_id, true, true);

	if($t_version_type == 'target_version')
		$t_version_rows = array_reverse($t_version_rows);
		
	$t_is_first_version = true;

	/* print sectin per version */
	foreach($t_version_rows as $t_version_row){
		$t_version_name = $t_version_row['version'];
		$t_released = $t_version_row['released'];
		$t_obsolete = $t_version_row['obsolete'];

		/* only show either released or unreleased versions */
		if(($t_version_type == 'target_version' && ($t_released || $t_obsolete)) || ($t_version_type != 'target_version' && !$t_released && !$t_obsolete))
			continue;

		/* Skip all versions except the specified one (if any) */
		if($f_version_id != -1 && $f_version_id != $t_version_row['id'])
			continue;

		/* query issue list */
		$t_issues_resolved = 0;
		$t_issue_ids = db_query_roadmap_info($t_project_id, $t_version_name, $t_version_type, $t_issues_resolved);
		$t_num_issues = count($t_issue_ids);
	
		/* print section */
		section_begin('Version: ' . $t_version_name, !$t_is_first_version);
			/* print version description and progress */
			column_begin('6-left');

			$t_progress = 0;

			if($t_num_issues > 0)
				$t_progress = (int)($t_issues_resolved * 100 / $t_num_issues);

			$t_release_date = date(config_get('short_date_format'), $t_version_row['date_order']);

			if(!$t_obsolete){
				if($t_released)	$t_release_state = format_label('Released (' . $t_release_date . ')', 'label-success');
				else			$t_release_state = format_label('Planned Release (' . $t_release_date . ')', 'label-info');
			}
			else
				$t_release_state = format_label('Obsolete (' . $t_release_date . ')', 'label-danger');


			table_begin(array());
			table_row(
				array(
					$t_release_state,
					format_progressbar($t_progress),
					format_button_link($t_issues_resolved . ' of ' . $t_num_issues . ' issue(s) resolved', 'filter_apply.php', array('type' => 1, 'temporary' => 'y', FILTER_PROPERTY_PROJECT_ID => $t_project_id, FILTER_PROPERTY_TARGET_VERSION => $t_version_name), 'input-xxs')
				),
				'',
				array('', 'width="100%"', '')
			);
			table_end();

			$t_description = $t_version_row['description'];

			if(!is_blank($t_description))
				alert('warning', string_display($t_description));

			column_end();

			/* print issues */
			column_begin('6-right');
			actionbar_begin();
				echo '<div class="pull-right">';
					$t_menu = array(
						array('label' => 'Select Columns', 'data' => array('link' => format_href('columns_select_page.php', column_select_input('bug_list_columns_versions', $f_columns, false, true, basename(__FILE__))), 'class' => 'inline-page-link')),
					);

					dropdown_menu('', $t_menu, '', '', 'dropdown-menu-right');
				echo '</div>';
			actionbar_end();

			bug_list_print($t_issue_ids, $f_columns, 'table-condensed table-hover table-datatable no-border');
			column_end();
		section_end();

		$t_is_first_version = false;
	}

	/* print message if no versions have been shown */
	if($t_is_first_version){
		echo '<p class="lead">';
		echo 'No Roadmap information available.  Issues are included once projects have versions and issues have "target version" set.';
		echo '</p>';
	}
}


/* identify project and version id */
$f_project_id = gpc_get_int('project_id', helper_get_current_project());
$f_version_id = gpc_get_int('version_id', -1);
$f_type = gpc_get_string('type', 'released');

if($f_version_id != -1){
	if(!version_exists($f_version_id)){
		alert_page('danger', 'Unknown version ID ' . $f_version_id, 'Roadmap/Release History');
		exit();
	}

	$f_project_id = version_get_field($f_version_id, 'project_id');

	$f_type = 'unreleased';

	if(version_get_field($f_version_id, 'released'))
		$f_type = 'released';
}

$t_user_id = auth_get_current_user_id();
$t_project_ids = array();

if($f_project_id == ALL_PROJECTS){
	foreach(user_get_all_accessible_projects($t_user_id, ALL_PROJECTS) as $f_project_id){
		if(access_has_project_level(config_get('roadmap_view_threshold', null, null, $f_project_id), $f_project_id))
			$t_project_ids[] = $f_project_id;
	}
}
else{
	access_ensure_project_level(config_get('roadmap_view_threshold'), $f_project_id);
	$t_project_ids = user_get_all_accessible_subprojects($t_user_id, $f_project_id);
	$t_project_ids[] = $f_project_id;
}

/* set page type, either release or changelog */
if($f_type == 'released'){
	$t_page_title = 'Release History';
	$t_version_type = 'fixed_in_version';
}
else{
	$t_page_title = 'Roadmap';
	$t_version_type = 'target_version';
}

version_cache_array_rows( $t_project_ids );


/* page content */
layout_page_header();
layout_page_begin();

page_title($t_page_title);

column_begin('12');

$t_tabs = array();

foreach($t_project_ids as $t_project_id)
	$t_tabs[project_get_field($t_project_id, 'name')] = 'tab_project';

tabs($t_tabs);

column_end();
layout_page_end();
