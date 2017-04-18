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
 * This file POSTs data to report_bug.php
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

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'collapse_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'profile_api.php' );
require_api( 'project_api.php' );
require_api( 'relationship_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );


function table_empty($p_cols){
	echo '<td colspan="', $p_cols, '">&#160;</td>';
}


$f_master_bug_id = gpc_get_int( 'm_id', 0 );

if( $f_master_bug_id > 0 ) {
	# master bug exists...
	bug_ensure_exists( $f_master_bug_id );

	# master bug is not read-only...
	if( bug_is_readonly( $f_master_bug_id ) ) {
		error_parameters( $f_master_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	$t_bug = bug_get( $f_master_bug_id, true );

	#@@@ (thraxisp) Note that the master bug is cloned into the same project as the master, independent of
	#       what the current project is set to.
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
		$t_changed_project = true;
	} else {
		$t_changed_project = false;
	}

	access_ensure_project_level( config_get( 'report_bug_threshold' ) );

	$f_build				= $t_bug->build;
	$f_platform				= $t_bug->platform;
	$f_os					= $t_bug->os;
	$f_os_build				= $t_bug->os_build;
	$f_product_version		= $t_bug->version;
	$f_target_version		= $t_bug->target_version;
	$f_profile_id			= 0;
	$f_handler_id			= $t_bug->handler_id;

	$f_category_id			= $t_bug->category_id;
	$f_severity				= $t_bug->severity;
	$f_priority				= $t_bug->priority;
	$f_summary				= $t_bug->summary;
	$f_description			= $t_bug->description;
	$f_view_state			= (int)$t_bug->view_state;
	$f_due_date				= $t_bug->due_date;

	$t_project_id			= $t_bug->project_id;
} else {
	# Get Project Id and set it as current
	$t_current_project = helper_get_current_project();
	$t_project_id = gpc_get_int( 'project_id', $t_current_project );

	# If all projects, use default project if set
	$t_default_project = user_pref_get_pref( auth_get_current_user_id(), 'default_project' );
	if( ALL_PROJECTS == $t_project_id && ALL_PROJECTS != $t_default_project ) {
		$t_project_id = $t_default_project;
	}

	if( ( ALL_PROJECTS == $t_project_id || project_exists( $t_project_id ) )
		&& $t_project_id != $t_current_project
		&& project_enabled( $t_project_id ) ) {
		helper_set_current_project( $t_project_id );
		# Reloading the page is required so that the project browser
		# reflects the new current project
		print_header_redirect( $_SERVER['REQUEST_URI'], true, false, true );
	}

	# New issues cannot be reported for the 'All Project' selection
	if( ALL_PROJECTS == $t_current_project ) {
		print_header_redirect( 'login_select_proj_page.php?ref=bug_report_page.php' );
	}

	# Check for bug report threshold
	if( !access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
		# If can't report on current project, show project selector if there is any other allowed project
		access_ensure_any_project_level( 'report_bug_threshold' );
		print_header_redirect( 'login_select_proj_page.php?ref=bug_report_page.php' );
	}
	access_ensure_project_level( config_get( 'report_bug_threshold' ) );

	$f_build				= gpc_get_string( 'build', '' );
	$f_platform				= gpc_get_string( 'platform', '' );
	$f_os					= gpc_get_string( 'os', '' );
	$f_os_build				= gpc_get_string( 'os_build', '' );
	$f_product_version		= gpc_get_string( 'product_version', '' );
	$f_target_version		= gpc_get_string( 'target_version', '' );
	$f_profile_id			= gpc_get_int( 'profile_id', 0 );
	$f_handler_id			= gpc_get_int( 'handler_id', 0 );

	$f_category_id			= gpc_get_int( 'category_id', 0 );
	$f_severity				= gpc_get_int( 'severity', (int)config_get( 'default_bug_severity' ) );
	$f_priority				= gpc_get_int( 'priority', (int)config_get( 'default_bug_priority' ) );
	$f_summary				= gpc_get_string( 'summary', '' );
	$f_description			= gpc_get_string( 'description', '' );
	$f_view_state			= gpc_get_int( 'view_state', (int)config_get( 'default_bug_view_status' ) );
	$f_due_date				= gpc_get_string( 'due_date', date_strtotime( config_get( 'due_date_default' ) ) );

	if( $f_due_date == '' ) {
		$f_due_date = date_get_null();
	}

	$t_changed_project		= false;
}

$f_copy_notes_from_parent         = gpc_get_bool( 'copy_notes_from_parent', false );
$f_copy_attachments_from_parent   = gpc_get_bool( 'copy_attachments_from_parent', false );

$t_fields = config_get( 'bug_report_page_fields' );
$t_fields = columns_filter_disabled( $t_fields );

$t_show_category = in_array( 'category_id', $t_fields );
$t_show_severity = in_array( 'severity', $t_fields );
$t_show_priority = in_array( 'priority', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields ) && access_has_project_level( config_get( 'update_bug_assign_threshold' ) );
$t_show_profiles = config_get( 'enable_profiles' );
$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
$t_show_os_version = $t_show_profiles && in_array( 'os_version', $t_fields );
$t_show_resolution = in_array( 'resolution', $t_fields );
$t_show_status = in_array( 'status', $t_fields );
$t_show_tags =
	in_array( 'tags', $t_fields ) &&
	access_has_project_level(
		config_get( 'tag_attach_threshold', /* default */ null, /* user */ null, $t_project_id ),
		$t_project_id );

$t_show_versions = version_should_show_product_version( $t_project_id );
$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields ) && config_get( 'enable_product_build' ) == ON;
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields ) && access_has_project_level( config_get( 'roadmap_update_threshold' ) );
$t_show_due_date = in_array( 'due_date', $t_fields ) && access_has_project_level( config_get( 'due_date_update_threshold' ), helper_get_current_project(), auth_get_current_user_id() );
$t_show_attachments = in_array( 'attachments', $t_fields ) && file_allow_bug_upload();
$t_show_view_state = in_array( 'view_state', $t_fields ) && access_has_project_level( config_get( 'set_view_status_threshold' ) );

# don't index bug report page
html_robots_noindex();

layout_page_header( lang_get( 'report_bug_link' ) );

layout_page_begin( __FILE__ );

$t_form_encoding = '';
if( $t_show_attachments ) {
	$t_form_encoding = 'enctype="multipart/form-data"';
}
?>
<div class="col-md-6 col-xs-12">
<form id="report_bug_form"
	method="post" <?php echo $t_form_encoding; ?>
	action="bug_report.php?posted=1"
>
<?php echo form_security_field( 'bug_report' ) ?>
<input type="hidden" name="m_id" value="<?php echo $f_master_bug_id ?>" />
<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-edit"></i>
				<?php echo lang_get( 'enter_report_details_title' ) ?>
		</h4>
	</div>
<div class="widget-body dz-clickable">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed">
<?php
	event_signal( 'EVENT_REPORT_BUG_FORM_TOP', array( $t_project_id ) );
?>


<?php # summary ?>
<tr>
	<th class="category" width="15%"><span class="required">*</span><?php print_documentation_link( 'summary' ) ?></th>
	<td colspan=5>
		<input class="input-xs" <?php echo helper_get_tab_index() ?> type="text" id="summary" name="summary" size="80" maxlength="128" value="<?php echo string_attribute( $f_summary ) ?>" />
	</td>
</tr>


<?php # description ?>
<tr>
	<th class="category" colspan=6><span class="required">*</span><?php print_documentation_link( 'description' ) ?></th></tr><tr>
	<td colspan=6>
		<textarea class="form-control" <?php echo helper_get_tab_index() ?> id="description" name="description" cols="80" rows="10"><?php echo string_textarea( $f_description ) ?></textarea>
	</td>
</tr>


<?php # relationship (in case of cloned bug creation...)
	# spacer
	if( $f_master_bug_id > 0 ) {
		echo '<tr class="spacer"><td colspan="6"></td></tr>';
		echo '<tr class="hidden"></tr>';
?>
	<tr>
		<th class="category">	<?php echo lang_get( 'relationship_with_parent' ) . ' ' . bug_format_id( $f_master_bug_id ) ?></th>
		<td colspan="5"><?php relationship_list_box( config_get( 'default_bug_relationship_clone' ), "rel_type", false, true ) ?></td>
	</tr>
<?php
	}
?>


<?php # spacer ?>
<tr class="spacer"><td colspan="6"></td></tr>
<tr class="hidden"></tr>


<?php # line ?>
<tr>
	<?php # priority ?>
	<th class="category"><?php print_documentation_link( 'priority' ) ?></th>
	<td width="15%">
		<select <?php echo helper_get_tab_index() ?> id="priority" name="priority" class="input-xs"><?php print_enum_string_option_list( 'priority', $f_priority ) ?></select>
	</td>

	<?php # category ?>
	<th class="category" width="15%"><?php echo config_get( 'allow_no_category' ) ? '' : '<span class="required">*</span> ';	print_documentation_link( 'category' );	?></th>
	<td width="15%">
		<?php if( $t_changed_project ) {
			echo '[' . project_get_field( $t_bug->project_id, 'name' ) . '] ';
		} ?>
		<select <?php echo helper_get_tab_index() ?> id="category_id" name="category_id" class="autofocus input-xs">
			<?php
				print_category_option_list( $f_category_id );
			?>
		</select>
	</td>

	<?php # due date ?>
	<?php
	$t_date_to_display = '';

	if( !date_is_null( $f_due_date ) ) {
		$t_date_to_display = date( config_get( 'normal_date_format' ), $f_due_date );
	}
	?>
		<th class="category" width="17%"><?php print_documentation_link( 'due_date' ) ?></th>
		<td width="100%">
			<?php echo '<input ' . helper_get_tab_index() . ' type="text" id="due_date" name="due_date" class="datetimepicker" ' .
				'data-picker-locale="' . lang_get_current_datetime_locale() .
				'" data-picker-format="' . convert_date_format_to_momentjs( config_get( 'normal_date_format' ) ) . '" ' .
				'size="16" maxlength="20" value="' . $t_date_to_display . '" />' ?>
			<i class="fa fa-calendar fa-xlg datetimepicker"></i>
		</td>
</tr>


<?php # line ?>
<tr>
	<?php # severity ?>
	<th class="category"><?php print_documentation_link( 'severity' ) ?></th>
	<td>
		<select <?php echo helper_get_tab_index() ?> id="severity" name="severity" class="input-xs">
			<?php print_enum_string_option_list( 'severity', $f_severity ) ?>
		</select>
	</td>

	<?php # assignee ?>
	<th class="category"><?php echo lang_get( 'email_handler' ) ?></th>
	<td>
		<select <?php echo helper_get_tab_index() ?> id="handler_id" name="handler_id" class="input-xs">
			<option value="0" selected="selected"></option>
			<?php print_assign_to_option_list( $f_handler_id ) ?>
		</select>
	</td>

	<?php table_empty(2); ?>
</tr>


<?php # spacer ?>
<?php if($t_show_product_build || $t_show_platform || $t_show_view_state || $t_show_product_version || $t_show_os || $t_show_os || $t_show_target_version || $t_show_tags){ ?>
<tr class="spacer"><td colspan="6"></td></tr>
<tr class="hidden"></tr>
<?php } ?>


<?php # optional line ?>
<?php if($t_show_product_build || $t_show_platform || $t_show_view_state){ ?>
<tr>
	<?php # product build ?>
	<th class="category"><?php echo lang_get( 'product_build' ) ?></th>
	<td><input class="input-xs" <?php echo helper_get_tab_index() ?> type="text" id="build" name="build" size="16" maxlength="32" value="<?php echo string_attribute( $f_build ) ?>" /></td>
	
	<?php # platform ?>
	<th class="category"><?php echo lang_get( 'platform' ) ?></th>
	<td>
		<?php if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) { ?>
		<select id="platform" name="platform" class="input-xs">
			<option value=""></option>
			<?php print_platform_option_list( $f_platform ); ?>
		</select>
		<?php
			} else {
				echo '<input class="typeahead input-xs" type="text" id="platform" name="platform" autocomplete = "off" size="16" maxlength="32" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $f_platform ) . '" />';
			}
		?>
	</td>

	<?php # view status ?>
	<th class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</th>
	<td>
		<?php
		echo '<select ' . helper_get_tab_index() . ' id="view_state" name="view_state" class="input-xs">';
		print_enum_string_option_list( 'view_state' );
		echo '</select>';
		?>

	</td>
</tr>

<?php } ?>

<?php # optional line ?>
<?php if($t_show_product_version || $t_show_os){ ?>
<tr>
	<?php # product version?>
	<th class="category"><?php echo lang_get( 'product_version' ) ?></th>
	<td>
		<select <?php echo helper_get_tab_index() ?> id="product_version" name="product_version" class="input-xs">
			<?php print_version_option_list( $f_product_version, $t_project_id, $t_product_version_released_mask ) ?>
		</select>
	</td>

	<?php # operating system?>
	<th class="category"><?php echo lang_get( 'os' ) ?></th>
	<td>
		<?php if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) { ?>
		<select id="os" name="os" class="input-xs">
			<option value=""></option>
			<?php print_os_option_list( $f_os ); ?>
		</select>
		<?php
			} else {
				echo '<input class="typeahead input-xs" type="text" id="os" name="os" autocomplete = "off" size="16" maxlength="32" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $f_os ) . '" />';
			}
		?>
	</td>

	<?php table_empty(2); ?>
</tr>
<?php } ?>


<?php # optional line ?>
<?php if($t_show_os || $t_show_target_version){ ?>
<tr>
	<?php # target version?>
	<th class="category"><?php echo lang_get( 'target_version' ) ?></th>
	<td>
		<select <?php echo helper_get_tab_index() ?> id="target_version" name="target_version" class="input-xs">
			<?php print_version_option_list( '', null, VERSION_FUTURE ) ?>
		</select>
	</td>

	<?php # os version?>
	<th class="category"><?php echo lang_get( 'os_version' ) ?></th>
	<td>
		<?php
			if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
		?>
		<select id="os_build" name="os_build" class="input-xs">
			<option value=""></option>
				<?php print_os_build_option_list( $f_os_build ); ?>
			</select>
		<?php
			} else {
				echo '<input class="typeahead input-xs" type="text" id="os_build" name="os_build" autocomplete = "off" size="16" maxlength="16" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $f_os_build ) . '" />';
			}
		?>
	</td>

	<?php table_empty(2); ?>
</tr>
<?php } ?>


<?php event_signal( 'EVENT_REPORT_BUG_FORM', array( $t_project_id ) ) ?>

<?php # optional line ?>
<?php if( $t_show_tags ) { ?>
	<tr>
		<?php # tags ?>
		<th class="category"><?php echo lang_get( 'tag_attach_long' ) ?></th>
		<td colspan="5"><?php print_tag_input( '' ); ?></td>
	</tr>
<?php } ?>


<?php ## custom fields ?>
<?php
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

	# spacer
	if($t_related_custom_field_ids){
		echo '<tr class="spacer"><td colspan="6"></td></tr>';
		echo '<tr class="hidden"></tr>';
	}

	$i=0;
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( ( $t_def['display_report'] || $t_def['require_report']) && custom_field_has_write_access_to_project( $t_id, $t_project_id ) ) {

			$t_required_class = $t_def['require_report'] ? 'class="required" ' : '';

			if($i == 0){
				echo '<tr>';
			}
	?>
			<th class="category">
				<?php if( $t_def['require_report'] ) {?><span class="required">*</span><?php } ?>
				<?php if( $t_def['type'] != CUSTOM_FIELD_TYPE_RADIO && $t_def['type'] != CUSTOM_FIELD_TYPE_CHECKBOX ) { ?>
						<?php echo string_display( lang_get_defaulted( $t_def['name'] ) ) ?>
				<?php } else { echo string_display( lang_get_defaulted( $t_def['name'] ) ); } ?>
			</th>
			<td>
				<?php print_custom_field_input( $t_def, ( $f_master_bug_id === 0 ) ? null : $f_master_bug_id ) ?>
			</td>
<?php
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
?>

</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<span class="required pull-right"> * <?php echo lang_get( 'required' ) ?></span>
	<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'submit_report_button' ) ?>" />
</div>
</div>
</div>
</form>
</div>
<?php
layout_page_end();
