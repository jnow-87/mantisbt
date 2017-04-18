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
 * This include file prints out the bug information
 * $f_bug_id MUST be specified before the file is included
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses columns_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses event_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses last_visited_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

if( !defined( 'BUG_VIEW_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'columns_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'event_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'last_visited_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

require_css( 'status_config.php' );

function table_empty($p_cols){
	echo '<td colspan="', $p_cols, '">&#160;</td>';
}

$f_bug_id = gpc_get_int( 'id' );

bug_ensure_exists( $f_bug_id );

$t_bug = bug_get( $f_bug_id, true );

# In case the current project is not the same project of the bug we are
# viewing, override the current project. This ensures all config_get and other
# per-project function calls use the project ID of this bug.
$g_project_override = $t_bug->project_id;

access_ensure_bug_level( config_get( 'view_bug_threshold' ), $f_bug_id );

$f_history = gpc_get_bool( 'history', config_get( 'history_default_visible' ) );

$t_fields = config_get( $t_fields_config_option );
$t_fields = columns_filter_disabled( $t_fields );

compress_enable();

if( $t_show_page_header ) {
	layout_page_header( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ), null, 'view-issue-page' );
	layout_page_begin( 'view_all_bug_page.php' );
}

$t_action_button_position = config_get( 'action_button_position' );

$t_bugslist = gpc_get_cookie( config_get( 'bug_list_cookie' ), false );

$t_show_versions = version_should_show_product_version( $t_bug->project_id );
$t_show_product_version = in_array( 'product_version', $t_fields );
$t_show_fixed_in_version = in_array( 'fixed_in_version', $t_fields );
$t_show_product_build = in_array( 'product_build', $t_fields );
$t_product_build = $t_show_product_build ? string_display_line( $t_bug->build ) : '';
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields )
	&& access_has_bug_level( config_get( 'roadmap_view_threshold' ), $f_bug_id );

$t_product_version_string  = '';
$t_target_version_string   = '';
$t_fixed_in_version_string = '';

if( $t_show_product_version || $t_show_fixed_in_version || $t_show_target_version ) {
	$t_version_rows = version_get_all_rows( $t_bug->project_id );

	if( $t_show_product_version ) {
		$t_product_version_string  = prepare_version_string( $t_bug->project_id, version_get_id( $t_bug->version, $t_bug->project_id ) );
	}

	if( $t_show_target_version ) {
		$t_target_version_string   = prepare_version_string( $t_bug->project_id, version_get_id( $t_bug->target_version, $t_bug->project_id ) );
	}

	if( $t_show_fixed_in_version ) {
		$t_fixed_in_version_string = prepare_version_string( $t_bug->project_id, version_get_id( $t_bug->fixed_in_version, $t_bug->project_id ) );
	}
}

$t_product_version_string = string_display_line( $t_product_version_string );
$t_target_version_string = string_display_line( $t_target_version_string );
$t_fixed_in_version_string = string_display_line( $t_fixed_in_version_string );

$t_bug_id = $f_bug_id;
$t_form_title = bug_format_id($f_bug_id) . " [" . string_display_line( category_full_name( $t_bug->category_id ) ) . "]: " . bug_format_summary( $f_bug_id, SUMMARY_CAPTION );
$t_wiki_link = config_get_global( 'wiki_enable' ) == ON ? 'wiki.php?id=' . $f_bug_id : '';

if( access_has_bug_level( config_get( 'view_history_threshold' ), $f_bug_id ) ) {
	$t_history_link = 'view.php?id=' . $f_bug_id . '&history=1#history';
} else {
	$t_history_link = '';
}

$t_show_reminder_link = !current_user_is_anonymous() && !bug_is_readonly( $f_bug_id ) &&
	  access_has_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );
$t_bug_reminder_link = 'bug_reminder_page.php?bug_id=' . $f_bug_id;

$t_top_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH );
$t_bottom_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH );

$t_show_project = in_array( 'project', $t_fields );
$t_show_id = in_array( 'id', $t_fields );

$t_show_date_submitted = in_array( 'date_submitted', $t_fields );
$t_date_submitted = $t_show_date_submitted ? date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) : '';

$t_show_last_updated = in_array( 'last_updated', $t_fields );
$t_last_updated = $t_show_last_updated ? date( config_get( 'normal_date_format' ), $t_bug->last_updated ) : '';

$t_show_tags = in_array( 'tags', $t_fields ) && access_has_global_level( config_get( 'tag_view_threshold' ) );

$t_bug_overdue = bug_is_overdue( $f_bug_id );

$t_show_view_state = in_array( 'view_state', $t_fields );
$t_bug_view_state_enum = $t_show_view_state ? string_display_line( get_enum_element( 'view_state', $t_bug->view_state ) ) : '';

$t_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $f_bug_id );

if( $t_show_due_date ) {
	if( !date_is_null( $t_bug->due_date ) ) {
		$t_bug_due_date = date( config_get( 'normal_date_format' ), $t_bug->due_date );
	} else {
		$t_bug_due_date = '';
	}
}

$t_show_reporter = in_array( 'reporter', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields ) && access_has_bug_level( config_get( 'view_handler_threshold' ), $f_bug_id );
$t_show_monitor_box = !$t_force_readonly;
$t_show_relationships_box = !$t_force_readonly;
$t_show_history = $f_history;
$t_show_profiles = config_get( 'enable_profiles' );
$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
$t_platform = $t_show_platform ? string_display_line( $t_bug->platform ) : '';
$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
$t_os = $t_show_os ? string_display_line( $t_bug->os ) : '';
$t_show_os_version = $t_show_profiles && in_array( 'os_version', $t_fields );
$t_os_version = $t_show_os_version ? string_display_line( $t_bug->os_build ) : '';
$t_can_attach_tag = $t_show_tags && !$t_force_readonly && access_has_bug_level( config_get( 'tag_attach_threshold' ), $f_bug_id );
$t_show_priority = in_array( 'priority', $t_fields );
$t_priority = $t_show_priority ? string_display_line( get_enum_element( 'priority', $t_bug->priority ) ) : '';
$t_show_severity = in_array( 'severity', $t_fields );
$t_severity = $t_show_severity ? string_display_line( get_enum_element( 'severity', $t_bug->severity ) ) : '';
$t_show_status = in_array( 'status', $t_fields );
$t_status = $t_show_status ? string_display_line( get_enum_element( 'status', $t_bug->status ) ) : '';
$t_show_resolution = in_array( 'resolution', $t_fields );
$t_resolution = $t_show_resolution ? string_display_line( get_enum_element( 'resolution', $t_bug->resolution ) ) : '';
$t_show_description = in_array( 'description', $t_fields );

$t_description = $t_show_description ? string_display_links( $t_bug->description ) : '';

$t_links = event_signal( 'EVENT_MENU_ISSUE', $f_bug_id );

#
# Start of Template
#

echo '<div class="col-md-6 col-xs-12">';
echo '<div class="widget-box widget-color-blue2">';
echo '<div class="widget-header widget-header-small">';
echo '<h4 class="widget-title lighter">';
echo '<i class="ace-icon fa fa-bars"></i>';
echo $t_form_title;
echo '</h4>';
echo '</div>';

echo '<div class="widget-body">';

####
## action buttons
####
if(config_get('view_issue_button_notes') | config_get('view_issue_button_sendmail') | config_get('view_issue_button_history') |  config_get('view_issue_button_next')){
	echo '<div class="widget-toolbox padding-8 clearfix noprint">';
	echo '<div class="btn-group pull-left">';

	# Jump to Bugnotes
	if(config_get('view_issue_button_notes')){
		print_small_button( '#bugnotes', lang_get( 'jump_to_bugnotes' ) );
	}

	# Send Bug Reminder
	if(config_get('view_issue_button_sendmail')){
		if( $t_show_reminder_link ) {
			print_small_button( $t_bug_reminder_link, lang_get( 'bug_reminder' ) );
		}

		if( !is_blank( $t_wiki_link ) ) {
			print_small_button( $t_wiki_link, lang_get( 'wiki' ) );
		}

		foreach ( $t_links as $t_plugin => $t_hooks ) {
			foreach( $t_hooks as $t_hook ) {
				if( is_array( $t_hook ) ) {
					foreach( $t_hook as $t_label => $t_href ) {
						if( is_numeric( $t_label ) ) {
							print_bracket_link_prepared( $t_href );
						} else {
							print_small_button( $t_href, $t_label );
						}
					}
				} elseif( !empty( $t_hook ) ) {
					print_bracket_link_prepared( $t_hook );
				}
			}
		}
	}

	# Links
	if(config_get('view_issue_button_history')){
		if( !is_blank( $t_history_link ) ) {
			# History
			print_small_button( $t_history_link, lang_get( 'bug_history' ) );
		}
	}

	echo '</div>';

	# prev/next links
	echo '<div class="btn-group pull-right">';

	if(config_get('view_issue_button_next')){
		if( $t_bugslist ) {
			$t_bugslist = explode( ',', $t_bugslist );
			$t_index = array_search( $f_bug_id, $t_bugslist );
			if( false !== $t_index ) {
				if( isset( $t_bugslist[$t_index-1] ) ) {
					print_small_button( 'view.php?id='.$t_bugslist[$t_index-1], '&lt;&lt;' );
				}

				if( isset( $t_bugslist[$t_index+1] ) ) {
					print_small_button( 'view.php?id='.$t_bugslist[$t_index+1], '&gt;&gt;' );
				}
			}
		}
	}

	echo '</div>';
	echo '</div>';
}

echo '<div class="widget-main no-padding">';
echo '<div class="table-responsive">';
echo '<table class="table table-bordered table-condensed">';

if( $t_top_buttons_enabled ) {
	echo '<thead><tr class="bug-nav">';
	echo '<tr class="top-buttons noprint">';
	echo '<td colspan="6">';
	html_buttons_view_bug_page( $t_bug_id );
	echo '</td>';
	echo '</tr>';
	echo '</thead>';
}

if( $t_bottom_buttons_enabled ) {
	echo '<tfoot>';
	echo '<tr class="noprint"><td colspan="6">';
	html_buttons_view_bug_page( $t_bug_id );
	echo '</td></tr>';
	echo '</tfoot>';
}

echo '<tbody>';

####
## issue data
####

## description
echo '<tr class="bug-header">';
echo '<th class="bug-description category" colspan="6">', lang_get( 'description' ), '</th>';
echo '</tr>';

echo '<tr class="bug-header-data">';
echo '<td class="bug-description" colspan="6">', $t_description, '</td>';
echo '</tr>';

## tags
if( $t_show_tags ) {
	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';

	echo '<tr class="noprint">';
	echo '<th class="bug-tags category">', lang_get( 'tags' ), '</th>';

	if( $t_can_attach_tag ) {
		echo '<td class="bug-attach-tags" colspan="5">';
		print_tag_attach_form( $t_bug_id );
		echo '</td>';
	}
	
	echo '</tr>';

	echo '<tr>';
	echo '<td class="bug-tags" colspan="5">';
	tag_display_attached( $t_bug_id );
	echo '</td></tr>';
}


# spacer
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';


## line
echo '<tr>';
	# status
	echo '<th class="bug-status category" width="15%">', lang_get( 'status' ), '</th>';

	$t_status_label = html_get_status_css_class( $t_bug->status );

	echo '<td class="bug-status" width=15%>';
	echo '<i class="fa fa-square fa-status-box ' . $t_status_label . '"></i> ';
	echo $t_status, '</td>';

	# reporter
	echo '<th class="bug-reporter category" width="15%">', lang_get( 'reporter' ), '</th>';
	echo '<td class="bug-reporter" width=15%>';
	print_user_with_subject( $t_bug->reporter_id, $t_bug_id );
	echo '</td>';

	# date submitted
	echo '<th class="bug-date-submitted category" width="15%">', $t_show_date_submitted ? lang_get( 'date_submitted' ) : '', '</th>';
	echo '<td class="bug-date-submitted" width=100%>', $t_date_submitted, '</td>';
echo '</tr>';


## line
echo '<tr>';
	# priority
	echo '<th class="bug-priority category">', lang_get( 'priority' ), '</th>';
	echo '<td class="bug-priority">', $t_priority, '</td>';

	# assignee
	echo '<th class="bug-assigned-to category">', lang_get( 'assigned_to' ), '</th>';
	echo '<td class="bug-assigned-to">';
	print_user_with_subject( $t_bug->handler_id, $t_bug_id );
	echo '</td>';

	# due date
	echo '<th class="bug-due-date category">', lang_get( 'due_date' ), '</th>';

	if( $t_bug_overdue ) {
		echo '<td class="bug-due-date overdue">', $t_bug_due_date, '</td>';
	} else {
		echo '<td class="bug-due-date">', $t_bug_due_date, '</td>';
	}

echo '</tr>';


## line
echo '<tr>';
	# resolution
	echo '<th class="bug-resolution category">', lang_get( 'resolution' ), '</th>';
	echo '<td class="bug-resolution">', $t_resolution, '</td>';

	# empty
	table_empty(2);

	# date updated
	echo '<th class="bug-last-modified category">', $t_show_last_updated ? lang_get( 'last_update' ) : '','</th>';
	echo '<td class="bug-last-modified">', $t_last_updated, '</td>';
echo '</tr>';


## line
echo '<tr>';
	# severity
	echo '<th class="bug-severity category">', lang_get( 'severity' ), '</th>';
	echo '<td class="bug-severity">', $t_severity, '</td>';

	# empty
	table_empty(4);
echo '</tr>';

# spacer
if($t_show_product_build || $t_show_platform || $t_show_view_state || $t_show_product_version || $t_show_os || $t_show_fixed_in_version){
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';
}


## optional line
if($t_show_product_build || $t_show_platform || $t_show_view_state){
echo '<tr>';
	# product build
	echo '<th class="bug-product-build category">', $t_show_product_build ? lang_get( 'product_build' ) : '', '</th>';
	echo '<td class="bug-product-build">', $t_product_build, '</td>';

	# platform
	echo '<th class="bug-platform category">', $t_show_platform ? lang_get( 'platform' ) : '', '</th>';
	echo '<td class="bug-platform">', $t_platform, '</td>';

	# view status
	echo '<th class="bug-view-status category">', $t_show_view_state ? lang_get( 'view_status' ) : '', '</th>';
	echo '<td class="bug-view-status">', $t_bug_view_state_enum, '</td>';
echo '</tr>';
}

## optional line
if($t_show_product_version || $t_show_os){
echo '<tr>';
	# product version
	echo '<th class="bug-product-version category">', $t_show_product_version ? lang_get( 'product_version' ) : '', '</th>';
	echo '<td class="bug-product-version">', $t_product_version_string, '</td>';

	# operating system
	echo '<th class="bug-os category">', $t_show_os ? lang_get( 'os_version') : '', '</th>';
	echo '<td class="bug-os">', $t_os, $t_os ? ' / ' : '' , $t_os_version  , '</td>';

	# empty
	table_empty(2);
echo '</tr>';
}

## optional line
if($t_show_fixed_in_version){
echo '<tr>';
	# fixed in version
	echo '<th class="bug-fixed-in-version category">', $t_show_fixed_in_version ?  lang_get( 'fixed_in_version' ) : '', '</th>';
	echo '<td class="bug-fixed-in-version">', $t_fixed_in_version_string, '</td>';

	# empty
	table_empty(4);
echo '</tr>';
}


## Bug Details Event Signal
event_signal( 'EVENT_VIEW_BUG_DETAILS', array( $t_bug_id ) );

## custom fields
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );
custom_field_cache_values( array( $t_bug->id ) , $t_related_custom_field_ids );

if($t_related_custom_field_ids){
	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}


$i = 0;
foreach( $t_related_custom_field_ids as $t_id ) {
	if( !custom_field_has_read_access( $t_id, $f_bug_id ) ) {
		continue;
	} # has read access

	$t_def = custom_field_get_definition( $t_id );

	if($i == 0){
		echo '<tr>';
	}

	echo '<th class="bug-custom-field category">', string_display( lang_get_defaulted( $t_def['name'] ) ), '</th>';
	echo '<td class="bug-custom-field">';
	print_custom_field_value( $t_def, $t_id, $f_bug_id );
	echo '</td>';
	
	if($i == 2){
		echo '</tr>';
		$i = 0;
	}
	else{
		$i = $i + 1;
	}
}

if($i != 0){
	table_empty(2 * (3 - $i));
	echo '</tr>';
}

echo '</tbody></table>';
echo '</div></div></div></div></div>';


####
## bug notes and "Add Note" box
####
if( 'ASC' == current_user_get_pref( 'bugnote_order' ) ) {
	define( 'BUGNOTE_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );

	if( !$t_force_readonly ) {
		define( 'BUGNOTE_ADD_INC_ALLOW', true );
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
	}
} else {
	if( !$t_force_readonly ) {
		define( 'BUGNOTE_ADD_INC_ALLOW', true );
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
	}

	define( 'BUGNOTE_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );
}


####
## relationships
####
if( $t_show_relationships_box ) {
	relationship_view_box( $t_bug->id );
}


####
## user list monitoring the bug
####
if( $t_show_monitor_box ) {
	define( 'BUG_MONITOR_LIST_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bug_monitor_list_view_inc.php' );
}


####
## allow plugins to display stuff after notes
####
event_signal( 'EVENT_VIEW_BUG_EXTRA', array( $f_bug_id ) );


####
## bug history
####
if( $t_show_history ) {
	define( 'HISTORY_INC_ALLOW', true );
	include( $t_mantis_dir . 'history_inc.php' );
}


####
## time tracking statistics
####
if( config_get( 'time_tracking_enabled' ) &&
	access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
	define( 'BUGNOTE_STATS_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_stats_inc.php' );
}



layout_page_end();

last_visited_issue( $t_bug_id );
