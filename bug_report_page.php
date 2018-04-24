<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file POSTs data to report_bug.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002 Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses collapse_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses profile_api.php
 * @uses project_api.php
 * @uses relationship_api.php
 * @uses string_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

$g_allow_browser_cache = 1;

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('collapse_api.php');
require_api('columns_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('custom_field_api.php');
require_api('date_api.php');
require_api('error_api.php');
require_api('event_api.php');
require_api('file_api.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('profile_api.php');
require_api('project_api.php');
require_api('relationship_api.php');
require_api('string_api.php');
require_api('utility_api.php');
require_api('version_api.php');
require_api('elements_api.php');


$f_resp_type = RESP_JSON;
$t_fields_display = array('project_id', 'description', 'summary', 'category_id');
$t_custom_fields_display = array();

json_prepare();

/* get project id */
$t_project_id = helper_get_current_project();
$t_default_project_id = user_pref_get_pref(auth_get_current_user_id(), 'default_project');

if($t_project_id == ALL_PROJECTS)
	$t_project_id = $t_default_project_id;

if($t_project_id == ALL_PROJECTS)
	report_error('Select a project first', '', $f_resp_type);

if(!project_exists($t_project_id))
	report_error('Current/default project does not exists', '', $f_resp_type);

/* get required fields */
$t_required_fields = config_get('bug_fields_required')['report'];
$t_required_custom_fields = config_get('bug_custom_fields_required')['report'];

// check if any fields are configured required but not displayed a this page
$t_missing_fields = array_merge(
	array_diff($t_required_fields, $t_fields_display),
	array_diff($t_required_custom_fields, $t_custom_fields_display)
);

if(count($t_missing_fields) > 0){
	$t_missing_fields_str = '';

	foreach($t_missing_fields as $t_field)
		$t_missing_fields_str .= '\'' . $t_field . '\' ';

	report_error(
		'The following fields are configured as required fields but not supported when reporting an issue: '
		. $t_missing_fields_str,
		'',
		$f_resp_type
	);
}


/* page content */
// don't index bug report page
html_robots_noindex();

layout_inline_page_begin();
page_title('Report Issue');

echo '<form action="bug_report.php?posted=1" method="post" class="input-hover-form input-hover-form-noreload">';

input_hidden('project_id', $t_project_id);
input_hidden('resp_type', RESP_HTML);

event_signal('EVENT_REPORT_BUG_FORM_TOP', array());

echo form_security_field('bug_report');

echo '<div class="col-md-12">';
	/* actionbar */
	actionbar_begin();
		echo '<div class="pull-right">';
		button('Create', 'create', 'submit');
		echo '</div>';
	actionbar_end();

	echo '<div class="row">';
	table_begin(array(), 'no-border');
	table_row_bug_info_long('Project:', project_get_name($t_project_id), '7%');
	table_row_bug_info_long(format_required_indicator('category_id', $t_required_fields) . 'Type:', format_select('category_id', 'category_id', category_list($t_project_id), ''), '7%');
	table_row_bug_info_long(format_required_indicator('summary', $t_required_fields) . 'Summary:', format_text('summary', 'summary', string_attribute($f_summary), '', 'input-xs', 'width:100%!important'), '7%');
	table_row_bug_info_long(format_required_indicator('description', $t_required_fields) . 'Description:', format_textarea('description', 'description', string_textarea($f_description), 'input-xs', 'width:100% !important;height:100px'), '7%');

	table_row_bug_info_long(' ', '<span class="required pull-right"> * required</span>', '10%');
	table_end();
	echo '</div>';
echo '</div>';

event_signal('EVENT_REPORT_BUG_FORM', array());

echo '</form>';

layout_inline_page_end();
