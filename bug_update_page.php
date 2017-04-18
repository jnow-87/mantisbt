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
 * Display advanced Bug update page
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
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses last_visited_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses version_api.php
 */

$g_allow_browser_cache = 1;

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'last_visited_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'version_api.php' );

require_css( 'status_config.php' );

function table_empty($p_cols){
	echo '<td colspan="', $p_cols, '">&#160;</td>';
}


$f_bug_id = gpc_get_int( 'bug_id' );
$f_reporter_edit = gpc_get_bool( 'reporter_edit' );

$t_bug = bug_get( $f_bug_id, true );

if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( bug_is_readonly( $f_bug_id ) ) {
	error_parameters( $f_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

$t_fields = config_get( 'bug_update_page_fields' );
$t_fields = columns_filter_disabled( $t_fields );

$t_bug_id = $f_bug_id;

$t_action_button_position = config_get( 'action_button_position' );

$t_top_buttons_enabled = $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH;
$t_bottom_buttons_enabled = $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH;

$t_show_id = in_array( 'id', $t_fields );
$t_show_project = in_array( 'project', $t_fields );
$t_show_category = in_array( 'category_id', $t_fields );
$t_show_view_state = in_array( 'view_state', $t_fields );
$t_view_state = $t_show_view_state ? string_display_line( get_enum_element( 'view_state', $t_bug->view_state ) ) : '';
$t_show_date_submitted = in_array( 'date_submitted', $t_fields );
$t_show_last_updated = in_array( 'last_updated', $t_fields );
$t_show_reporter = in_array( 'reporter', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields ) && access_has_bug_level( config_get( 'view_handler_threshold' ), $t_bug_id );
$t_show_priority = in_array( 'priority', $t_fields );
$t_show_severity = in_array( 'severity', $t_fields );
$t_show_profiles = config_get( 'enable_profiles' ) == ON;
$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
$t_show_os_version = $t_show_profiles && in_array( 'os_version', $t_fields );
$t_show_versions = version_should_show_product_version( $t_bug->project_id );
$t_show_product_version = in_array( 'product_version', $t_fields );
$t_show_product_build = in_array( 'product_build', $t_fields );
$t_product_build_attribute = $t_show_product_build ? string_attribute( $t_bug->build ) : '';
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields ) && access_has_bug_level( config_get( 'roadmap_update_threshold' ), $t_bug_id );
$t_show_fixed_in_version = in_array( 'fixed_in_version', $t_fields );
$t_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $t_bug_id );
$t_show_summary = in_array( 'summary', $t_fields );
$t_summary_attribute = $t_show_summary ? string_attribute( $t_bug->summary ) : '';
$t_description_textarea = string_textarea( $t_bug->description );
if( NO_USER == $t_bug->handler_id ) {
	$t_handler_name =  '';
} else {
	$t_handler_name = string_display_line( user_get_name( $t_bug->handler_id ) );
}

$t_can_change_view_state = $t_show_view_state && access_has_project_level( config_get( 'change_view_status_threshold' ) );

if( $t_show_product_version ) {
	$t_product_version_released_mask = VERSION_RELEASED;

	if( access_has_project_level( config_get( 'report_issues_for_unreleased_versions_threshold' ) ) ) {
		$t_product_version_released_mask = VERSION_ALL;
	}
}

$t_formatted_bug_id = $t_show_id ? bug_format_id( $f_bug_id ) : '';
$t_project_name = $t_show_project ? string_display_line( project_get_name( $t_bug->project_id ) ) : '';

layout_page_header( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );

layout_page_begin();

?>
<div class="col-md-6 col-xs-12">
<div id="bug-update" class="form-container">
	<form id="update_bug_form" method="post" action="bug_update.php">
		<?php echo form_security_field( 'bug_update' ); ?>
		<input type="hidden" name="bug_id" value="<?php echo $t_bug_id ?>" />
        <input type="hidden" name="last_updated" value="<?php echo $t_bug->last_updated ?>" />

		<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-comments"></i>
				<?php echo lang_get( 'updating_bug_advanced_title' ) . ': ' . bug_format_id($f_bug_id) ?>
			</h4>
			<div class="widget-toolbar no-border">
				<div class="widget-menu">
					<?php print_small_button( string_get_bug_view_url( $t_bug_id ), lang_get( 'back_to_bug_link' ) ); ?>
				</div>
			</div>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed">
		<thead>

<?php
# Submit Button
if( $t_top_buttons_enabled ) {
?>
	<div class="widget-toolbox padding-8 clearfix">
		<input <?php helper_get_tab_index(); ?>
			type="submit" class="btn btn-primary btn-white btn-sm btn-round"
			value="<?php echo lang_get( 'update_information_button' ); ?>" />
	</div>
<?php
}
?>
			<tbody>
<?php
event_signal( 'EVENT_UPDATE_BUG_FORM_TOP', array( $t_bug_id ) );

# summary
echo '<tr class="bug-header">';
echo '<th class="category" width="15%">' . lang_get( 'summary' ) . '</th>';
echo '<td colspan="5">', '<input class="input-xs" ', helper_get_tab_index(), ' type="text" id="summary" name="summary" size="100" maxlength="128" value="', $t_summary_attribute, '" />';
echo '</td></tr>';

# description
echo '<tr class="bug-header">';
echo '<th class="bug-description category" colspan=6>' . lang_get( 'description' ) . '</th></tr>';
echo '<tr><td colspan="6">';
echo '<textarea class="form-control input-xs" ', helper_get_tab_index(), ' cols="116" rows="10" id="description" name="description">', $t_description_textarea, '</textarea>';
echo '</td></tr>';


# spacer
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';


## line
echo '<tr class="bug-header">';
	# priority
	echo '<th class="category">' . lang_get( 'priority' ) . '</th>';
	echo '<td width="20%"><select ' . helper_get_tab_index() . ' id="priority" name="priority" class="input-xs">';
	print_enum_string_option_list( 'priority', $t_bug->priority );
	echo '</select></td>';

	# category
	echo '<td class="category" width="12%">', $t_show_category ? '' . lang_get( 'category' ) . '' : '', '</td>';

	echo '<td width="15%">';
	echo '<select ' . helper_get_tab_index() . ' id="category_id" name="category_id" class="input-xs">';
	print_category_option_list( $t_bug->category_id, $t_bug->project_id );
	echo '</select>';
	echo '</td>';

	# due date
	echo '<th class="category" width="12%">' . lang_get( 'due_date' ) . '</th>';

	if( bug_is_overdue( $t_bug_id ) ) {
		echo '<td class="overdue" width="20%">';
	} else {	
		echo '<td width="20%">';
	}

	if( access_has_bug_level( config_get( 'due_date_update_threshold' ), $t_bug_id ) ) {
		$t_date_to_display = '';

		if( !date_is_null( $t_bug->due_date ) ) {
			$t_date_to_display = date( config_get( 'normal_date_format' ), $t_bug->due_date );
		}
		echo '<input ' . helper_get_tab_index() . ' type="text" id="due_date" name="due_date" class="datetimepicker input-xs" size="16" ' .
			'data-picker-locale="' . lang_get_current_datetime_locale() .
			'" data-picker-format="' . convert_date_format_to_momentjs( config_get( 'normal_date_format' ) ) .
			'" maxlength="20" value="' . $t_date_to_display . '" />';
		echo '<i class="fa fa-calendar fa-xlg datetimepicker"></i>';
	} else {
		if( !date_is_null( $t_bug->due_date ) ) {
			echo date( config_get( 'short_date_format' ), $t_bug->due_date );
		}
	}

	echo '</td>';
echo '</tr>';


## line
echo '<tr class="bug-header">';
	# severity
	echo '<th class="category">' . lang_get( 'severity' ) . '</th>';
	echo '<td><select ' . helper_get_tab_index() . ' id="severity" name="severity" class="input-xs">';
	print_enum_string_option_list( 'severity', $t_bug->severity );

	# assignee
	echo '<th class="category">' . lang_get( 'assigned_to' ) . '</th>';
	echo '<td>';
		if(access_has_project_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ) ) ) { 
			echo '<select ' . helper_get_tab_index() . ' id="handler_id" name="handler_id" class="input-xs">';
			echo '<option value="0"></option>';
			print_assign_to_option_list( $t_bug->handler_id, $t_bug->project_id );
			echo '</select>';
		} else {
			echo $t_handler_name;
		}   

	echo '</td>';

	table_empty(2);
echo '</tr>';


# spacer
if($t_show_product_build || $t_show_platform || $t_show_view_state || $t_show_product_version || $t_show_os || $t_show_fixed_in_version || $t_show_os){
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}


## optinal line 
if($t_show_product_build || $t_show_platform || $t_show_view_state){
echo '<tr class="bug-header">';
	# product build
	echo '<th class="category">', $t_show_product_build ? lang_get( 'product_build' ) : '', '</th>';
	echo '<td>';
	if($t_show_product_build){
		echo '<input type="text" id="build" name="build" class="input-xs" size="16" maxlength="32" ' . helper_get_tab_index() . ' value="' . $t_product_build_attribute . '" />';
	}
	echo '</td>';

	# platform
	echo '<th class="category">', $t_show_platform ? lang_get( 'platform' ) : '', '</th>';
	echo '<td>';

	if($t_show_platform){
		if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
			echo '<select ' . helper_get_tab_index() . ' id="platform" name="platform" class="input-xs"><option value=""></option>';
			print_platform_option_list( $t_bug->platform );
			echo '</select>';
		} else {
			echo '<input type="text" id="platform" name="platform" class="typeahead input-xs" autocomplete = "off" size="16" maxlength="32" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $t_bug->platform ) . '" />';
		}
	}

	echo '</td>';

	# view status
	echo '<td class="category">', $t_show_view_state ? lang_get( 'view_status' ) : '', '</td>';
	echo '<td>';

	if( $t_can_change_view_state ) {
		echo '<select ' . helper_get_tab_index() . ' id="view_state" name="view_state" class="input-xs">';
		print_enum_string_option_list( 'view_state', (int)$t_bug->view_state );
		echo '</select>';
	} else if( $t_show_view_state ) {
		echo $t_view_state;
	}
echo '</tr>';
}


## optional line
if($t_show_product_version || $t_show_os){
echo '<tr class="bug-header">';
	# product version
	echo '<th class="category">', $t_show_product_version ? lang_get( 'product_version' ) : '', '</th>';
	echo '<td>';
	
	if($t_show_product_version){
		echo '<select ', helper_get_tab_index(), ' id="version" name="version" class="input-xs">';
		print_version_option_list( $t_bug->version, $t_bug->project_id, $t_product_version_released_mask );
		echo '</select>';
	}

	echo '</td>';

	# operating system
	echo '<th class="category">', $t_show_os ? lang_get( 'os' ) : '', '</th>';
	echo '<td>';

	if($t_show_os){
		if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
			echo '<select ' . helper_get_tab_index() . ' id="os" name="os" class="input-xs"><option value=""></option>';
			print_os_option_list( $t_bug->os );
			echo '</select>';
		} else {
			echo '<input type="text" id="os" name="os" class="typeahead input-xs" autocomplete = "off" size="16" maxlength="32" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $t_bug->os ) . '" />';
		}
	}

	echo '</td>';

	table_empty(2);
echo '</tr>';
}


## optional line
if($t_show_fixed_in_version || $t_show_os){
echo '<tr class="bug-header">';
	# fixed in
	echo '<th class="category">', $t_show_fixed_in_version ? lang_get( 'fixed_in_version' ) : '', '</th>';
	echo '<td>';

	if($t_show_fixed_in_version){
		echo '<select ' . helper_get_tab_index() . ' id="fixed_in_version" name="fixed_in_version" class="input-xs">';
		print_version_option_list( $t_bug->fixed_in_version, $t_bug->project_id, VERSION_ALL );
		echo '</select>';
	}

	echo '</td>';

	# OS version
	echo '<th class="category">', $t_show_os ? lang_get( 'os_version' ) : '', '</th>';
	echo '<td>';

	if($t_show_os){
		if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
			echo '<select ' . helper_get_tab_index() . ' id="os_build" name="os_build" class="input-xs"><option value=""></option>';
			print_os_build_option_list( $t_bug->os_build );
			echo '</select>';
		} else {
			echo '<input type="text" id="os_build" name="os_build" class="typeahead input-xs" autocomplete = "off" size="16" maxlength="16" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $t_bug->os_build ) . '" />';
		}
	}

	echo '</td>';

	table_empty(2);
echo '</tr>';
}

event_signal( 'EVENT_UPDATE_BUG_FORM', array( $t_bug_id ) );

# Custom Fields
$t_custom_fields_found = false;
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );

# spacer
if($t_related_custom_field_ids){
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}


$i = 0;
foreach ( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );
	if( ( $t_def['display_update'] || $t_def['require_update'] ) && custom_field_has_write_access( $t_id, $t_bug_id ) ) {
		$t_custom_fields_found = true;

		$t_required_class = $t_def['require_update'] ? ' class="required" ' : '';

		if( $t_def['type'] != CUSTOM_FIELD_TYPE_RADIO && $t_def['type'] != CUSTOM_FIELD_TYPE_CHECKBOX ) {
			$t_label_for = ' for="custom_field_' . string_attribute( $t_def['id'] ) . '" ';
		} else {
			$t_label_for = '';
		}

		if($i == 0){
			echo '<tr>';
		}

		echo '<td class="category">';
		echo '<span>', string_display( lang_get_defaulted( $t_def['name'] ) ), '</span>';
		echo '</td><td>';
		print_custom_field_input( $t_def, $t_bug_id );
		echo '</td>';

		if($i == 2){
			echo '</tr>';
			$i = 0;
		}
		else{
			$i = $i + 1;
		}
	}
} # foreach( $t_related_custom_field_ids as $t_id )

if($i != 0){
	table_empty(2 * (3 - $i));
	echo '</tr>';
}



event_signal( 'EVENT_BUGNOTE_ADD_FORM', array( $t_bug_id ) );

echo '</table>';
echo '</div>';
echo '</div>';
echo '</div>';

# Submit Button
if( $t_bottom_buttons_enabled ) {
?>
	<div class="widget-toolbox padding-8 clearfix">
		<input <?php helper_get_tab_index(); ?>
			type="submit" class="btn btn-primary btn-white btn-sm  btn-round"
			value="<?php echo lang_get( 'update_information_button' ); ?>" />
	</div>
<?php
}

echo '</div>';
echo '</form>';
echo '</div>';
echo '</div>';


####
## bug history
####
define( 'HISTORY_INC_ALLOW', true );
include( $t_mantis_dir . 'history_inc.php' );


layout_page_end();

last_visited_issue( $t_bug_id );
