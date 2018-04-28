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
 * My View Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses category_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('category_api.php');
require_api('compress_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('current_user_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('print_api.php');
require_api('user_api.php');
require_api('layout_api.php');
require_api('elements_api.php');
require_api('bug_api.php');
require_api('file_api.php');
require_api('filter_api.php');
require_api('filter_constants_inc.php');
require_api('icon_api.php');
require_api('project_api.php');
require_api('string_api.php');
require_api('bug_list_api.php');

require_css('status_config.php');

auth_ensure_user_authenticated();

$t_current_user_id = auth_get_current_user_id();

# Improve performance by caching category data in one pass
category_get_all_rows(helper_get_current_project());

compress_enable();

# don't index my view page
html_robots_noindex();

layout_page_header_begin();

if(current_user_get_pref('refresh_delay') > 0)
	html_meta_redirect('my_view_page.php?refresh=true', current_user_get_pref('refresh_delay') * 60);

layout_page_header_end();

layout_page_begin(__FILE__);

$t_secion_titles = array(
	'unassigned' => 'Unassigned',
	'recent_mod' => 'Recently Modified',
	'reported' => 'Reported by Me',
	'assigned' => 'Assigned to Me (Unresolved)',
	'resolved' => 'Resolved',
	'monitored' => 'Monitored by Me',
	'feedback' => 'Awaiting Feedback from Me',
	'verify' => 'Awaiting Confirmation of Resolution from Me',
	'my_comments' => 'Issues I Have Commented On',
);


$t_boxes = config_get('my_view_boxes');
asort($t_boxes);
reset($t_boxes);
#print_r ($t_boxes);

$t_project_id = helper_get_current_project();
$t_timeline_view_threshold_access = access_has_project_level(config_get('timeline_view_threshold'));


$t_filter = current_user_get_bug_filter();

if($t_filter === false)
	$t_filter = filter_get_default();

$t_sort = $t_filter['sort'];
$t_dir = $t_filter['dir'];

$t_bug_resolved_status_threshold = config_get('bug_resolved_status_threshold');
$t_hide_status_default = config_get('hide_status_default');
$t_default_show_changed = config_get('default_show_changed');

$c_filter['assigned'] = filter_create_assigned_to_unresolved(helper_get_current_project(), $t_current_user_id);
$t_url_link_parameters['assigned'] = FILTER_PROPERTY_HANDLER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_bug_resolved_status_threshold;

# @TODO cproensa: make this value configurable
$t_recent_days = 30;
$c_filter['recent_mod'] = filter_create_recently_modified($t_recent_days);
$t_url_link_parameters['recent_mod'] = FILTER_PROPERTY_HIDE_STATUS . '=none'
		. '&' . FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE . '=' . $c_filter['recent_mod'][FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE]
		. '&' . FILTER_PROPERTY_LAST_UPDATED_END_DATE . '=' . $c_filter['recent_mod'][FILTER_PROPERTY_LAST_UPDATED_END_DATE]
		. '&' . FILTER_PROPERTY_LAST_UPDATED_START_DATE . '=' . $c_filter['recent_mod'][FILTER_PROPERTY_LAST_UPDATED_START_DATE];

$c_filter['reported'] = filter_create_reported_by(helper_get_current_project(), $t_current_user_id);
$t_url_link_parameters['reported'] = FILTER_PROPERTY_REPORTER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;

$c_filter['resolved'] = array(
	FILTER_PROPERTY_CATEGORY_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_SEVERITY => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_STATUS => array('0' => $t_bug_resolved_status_threshold),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_HANDLER_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_RESOLUTION => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_BUILD => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_VERSION => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_HIDE_STATUS => array('0' => $t_hide_status_default),
	FILTER_PROPERTY_MONITOR_USER_ID => array('0' => META_FILTER_ANY),
);
$t_url_link_parameters['resolved'] = FILTER_PROPERTY_STATUS . '=' . $t_bug_resolved_status_threshold . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;


$c_filter['unassigned'] = filter_create_assigned_to_unresolved(helper_get_current_project(), 0);
$t_url_link_parameters['unassigned'] = FILTER_PROPERTY_HANDLER_ID . '=[none]' . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;

# TODO: check. handler value looks wrong

$c_filter['monitored'] = filter_create_monitored_by(helper_get_current_project(), $t_current_user_id);
$t_url_link_parameters['monitored'] = FILTER_PROPERTY_MONITOR_USER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;

$c_filter['feedback'] = array(
	FILTER_PROPERTY_CATEGORY_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_SEVERITY => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_STATUS => array('0' => config_get('bug_feedback_status')),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => array('0' => $t_current_user_id),
	FILTER_PROPERTY_HANDLER_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_RESOLUTION => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_BUILD => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_VERSION => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_HIDE_STATUS => array('0' => $t_hide_status_default),
	FILTER_PROPERTY_MONITOR_USER_ID => array('0' => META_FILTER_ANY),
);
$t_url_link_parameters['feedback'] = FILTER_PROPERTY_REPORTER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_STATUS . '=' . config_get('bug_feedback_status') . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;

$c_filter['verify'] = array(
	FILTER_PROPERTY_CATEGORY_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_SEVERITY => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_STATUS => array('0' => $t_bug_resolved_status_threshold),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => array('0' => $t_current_user_id),
	FILTER_PROPERTY_HANDLER_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_RESOLUTION => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_BUILD => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_VERSION => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_HIDE_STATUS => array('0' => $t_hide_status_default),
	FILTER_PROPERTY_MONITOR_USER_ID => array('0' => META_FILTER_ANY),
);
$t_url_link_parameters['verify'] = FILTER_PROPERTY_REPORTER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_STATUS . '=' . $t_bug_resolved_status_threshold;

$c_filter['my_comments'] = array(
	FILTER_PROPERTY_CATEGORY_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_SEVERITY => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_STATUS => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_HANDLER_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_RESOLUTION => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_BUILD => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_VERSION => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_HIDE_STATUS => array('0' => $t_hide_status_default),
	FILTER_PROPERTY_MONITOR_USER_ID => array('0' => META_FILTER_ANY),
	FILTER_PROPERTY_NOTE_USER_ID=> array('0' => META_FILTER_MYSELF),
);

$t_url_link_parameters['my_comments'] = FILTER_PROPERTY_NOTE_USER_ID. '=' . META_FILTER_MYSELF . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;



/* page content */
page_title('Dashboard');

echo '<div class="col-md-7">';

/* filter */
foreach($t_boxes as $t_box_title => $t_box_display){
	if(!(
		// don't display bugs that are set as 0
		($t_box_display == 0)
		// don't display "Assigned to Me" bugs to users that bugs can't be assigned to
	 || ($t_box_title == 'assigned' &&  (current_user_is_anonymous() || !access_has_project_level(config_get('handle_bug_threshold'), $t_project_id, $t_current_user_id)))
		// don't display "Monitored by Me" bugs to users that can't monitor bugs
	 || ($t_box_title == 'monitored' && (current_user_is_anonymous() OR !access_has_project_level(config_get('monitor_bug_threshold'), $t_project_id, $t_current_user_id)))
		// don't display "Reported by Me" bugs to users that can't report bugs
	 || (in_array($t_box_title, array('reported', 'feedback', 'verify')) &&	(current_user_is_anonymous() OR !access_has_project_level(config_get('report_bug_threshold'), $t_project_id, $t_current_user_id)))
	)){
		$t_per_page = -1;
		$f_page_number = 1;
		$t_page_count = null;
		$t_bug_count = null;

		$t_rows = filter_get_bug_rows($f_page_number, $t_per_page, $t_page_count, $t_bug_count, $c_filter[$t_box_title]);

		$t_bug_ids = array();

		foreach($t_rows as $t_row)
			$t_bug_ids[] = $t_row->id;

		section_begin($t_secion_titles[$t_box_title]);

		/* filter content */
		bug_list_print($t_bug_ids, array('id', 'summary', 'category_id', 'status', 'tags'), 'table-condensed table-hover table-sortable no-border');

		unset($t_rows);

		section_end();
	}
}

echo '</div>';

/* timeline */
if($t_timeline_view_threshold_access){
	echo '<div class="col-md-5">';
	section_begin('Timeline');

	# Build a simple filter that gets all bugs for current project
	$g_timeline_filter = array();
	$g_timeline_filter[FILTER_PROPERTY_HIDE_STATUS] = array(META_FILTER_NONE);
	$g_timeline_filter = filter_ensure_valid_filter($g_timeline_filter);
	include($g_core_path . 'timeline_inc.php');

	section_end();
	echo '</div>';
}

layout_page_end();
