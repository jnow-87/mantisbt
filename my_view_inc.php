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
 * My View include file
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses file_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses helper_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 */

if( !defined( 'MY_VIEW_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'file_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'helper_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );

$t_filter = current_user_get_bug_filter();
if( $t_filter === false ) {
	$t_filter = filter_get_default();
}
$t_sort = $t_filter['sort'];
$t_dir = $t_filter['dir'];

$t_update_bug_threshold = config_get( 'update_bug_threshold' );
$t_bug_resolved_status_threshold = config_get( 'bug_resolved_status_threshold' );
$t_hide_status_default = config_get( 'hide_status_default' );
$t_default_show_changed = config_get( 'default_show_changed' );

$c_filter['assigned'] = filter_create_assigned_to_unresolved( helper_get_current_project(), $t_current_user_id );
$t_url_link_parameters['assigned'] = FILTER_PROPERTY_HANDLER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_bug_resolved_status_threshold;

# @TODO cproensa: make this value configurable
$t_recent_days = 30;
$c_filter['recent_mod'] = filter_create_recently_modified( $t_recent_days );
$t_url_link_parameters['recent_mod'] = FILTER_PROPERTY_HIDE_STATUS . '=none'
		. '&' . FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE . '=' . $c_filter['recent_mod'][FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE]
		. '&' . FILTER_PROPERTY_LAST_UPDATED_END_DATE . '=' . $c_filter['recent_mod'][FILTER_PROPERTY_LAST_UPDATED_END_DATE]
		. '&' . FILTER_PROPERTY_LAST_UPDATED_START_DATE . '=' . $c_filter['recent_mod'][FILTER_PROPERTY_LAST_UPDATED_START_DATE];

$c_filter['reported'] = filter_create_reported_by( helper_get_current_project(), $t_current_user_id );
$t_url_link_parameters['reported'] = FILTER_PROPERTY_REPORTER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;

$c_filter['resolved'] = array(
	FILTER_PROPERTY_CATEGORY_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS => array(
		'0' => $t_bug_resolved_status_threshold,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HANDLER_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_RESOLUTION => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_BUILD => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_VERSION => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS => array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => array(
		'0' => META_FILTER_ANY,
	),
);
$t_url_link_parameters['resolved'] = FILTER_PROPERTY_STATUS . '=' . $t_bug_resolved_status_threshold . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;


$c_filter['unassigned'] = filter_create_assigned_to_unresolved( helper_get_current_project(), 0 );
$t_url_link_parameters['unassigned'] = FILTER_PROPERTY_HANDLER_ID . '=[none]' . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;

# TODO: check. handler value looks wrong

$c_filter['monitored'] = filter_create_monitored_by( helper_get_current_project(), $t_current_user_id );
$t_url_link_parameters['monitored'] = FILTER_PROPERTY_MONITOR_USER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;

$c_filter['feedback'] = array(
	FILTER_PROPERTY_CATEGORY_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS => array(
		'0' => config_get( 'bug_feedback_status' ),
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => array(
		'0' => $t_current_user_id,
	),
	FILTER_PROPERTY_HANDLER_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_RESOLUTION => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_BUILD => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_VERSION => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS => array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => array(
		'0' => META_FILTER_ANY,
	),
);
$t_url_link_parameters['feedback'] = FILTER_PROPERTY_REPORTER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_STATUS . '=' . config_get( 'bug_feedback_status' ) . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;

$c_filter['verify'] = array(
	FILTER_PROPERTY_CATEGORY_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS => array(
		'0' => $t_bug_resolved_status_threshold,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => array(
		'0' => $t_current_user_id,
	),
	FILTER_PROPERTY_HANDLER_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_RESOLUTION => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_BUILD => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_VERSION => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS => array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => array(
		'0' => META_FILTER_ANY,
	),
);
$t_url_link_parameters['verify'] = FILTER_PROPERTY_REPORTER_ID . '=' . $t_current_user_id . '&' . FILTER_PROPERTY_STATUS . '=' . $t_bug_resolved_status_threshold;

$c_filter['my_comments'] = array(
	FILTER_PROPERTY_CATEGORY_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HANDLER_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_RESOLUTION => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_BUILD => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_VERSION => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS => array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_NOTE_USER_ID=> array(
		'0' => META_FILTER_MYSELF,
	),
);

$t_url_link_parameters['my_comments'] = FILTER_PROPERTY_NOTE_USER_ID. '=' . META_FILTER_MYSELF . '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . $t_hide_status_default;
$t_rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $c_filter[$t_box_title] );

# Improve performance by caching category data in one pass
if( helper_get_current_project() == 0 ) {
	$t_categories = array();
	foreach( $t_rows as $t_row ) {
		$t_categories[] = $t_row->category_id;
	}

	category_cache_array_rows( array_unique( $t_categories ) );
}

bug_cache_columns_data( $t_rows , array( 'attachment_count' ) );

$t_filter = array_merge( $c_filter[$t_box_title], $t_filter );

$t_box_title_label = lang_get( 'my_view_title_' . $t_box_title );
if( $t_box_title == 'recent_mod' ) {
	$t_box_title_label .= ' (' . $t_recent_days . ' ' . lang_get( 'days' ) . ')';
}

$t_collapse_block = is_collapsed( $t_box_title );
$t_block_css = $t_collapse_block ? 'collapsed' : '';
$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';

$t_bug_string = $t_bug_count == 1 ? 'bug' : 'bugs';

# -- ====================== BUG LIST ========================= --
?>
<div id="<?php echo $t_box_title ?>" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-list-alt"></i>
<?php
#-- Box title
$t_box_url = html_entity_decode( config_get( 'bug_count_hyperlink_prefix' ) ).'&' . $t_url_link_parameters[$t_box_title];
print_link( $t_box_url, $t_box_title_label, false, 'white' );

# -- Viewing range info
if( count( $t_rows ) > 0 ) {
	$v_start = $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] * ( $f_page_number - 1 ) + 1;
	$v_end = $v_start + count( $t_rows ) - 1;
} else {
	$v_start = 0;
	$v_end = 0;
}
echo '<span class="badge"> ' . " $v_start - $v_end / $t_bug_count " . ' </span>';
?>
		</h4>
		<div class="widget-toolbar">
			<a data-action="collapse" href="#">
				<i class="1 ace-icon fa <?php echo $t_block_icon ?> bigger-125"></i>
			</a>
		</div>
		<div class="widget-toolbar no-border hidden-xs">
			<div class="widget-menu">
				<?php print_small_button( $t_box_url, lang_get( 'view_bugs_link' ) ); ?>
			</div>
		</div>
	</div>

	<div class="widget-body">
		<div class="widget-main no-padding">
			<div class="table-responsive">
				<table class="table table-bordered table-condensed table-striped table-hover">
<tbody>

<tr>
	<th width="10%"><?php echo lang_get('email_bug') ?></th>
	<th width="10%"><?php echo lang_get('category') ?></th>
	<th width="100%"><?php echo lang_get('summary') ?></th>
</tr>


<?php
# -- Loop over bug rows and create $v_* variables --
$t_count = count( $t_rows );

for( $i = 0;$i < $t_count; $i++ ) {
	$t_bug = $t_rows[$i];
	?>

<tr>
	<td><?php print_bug_link( $t_bug->id, false ); ?></td>
	<td><?php echo string_display_line( category_full_name( $t_bug->category_id ) ); ?></td>
	<td><?php echo bug_format_summary( $t_bug->id, SUMMARY_CAPTION ); ?></td>
</tr>
<?php
}
?>
</tbody>
</table>
</div>
</div>
</div>
</div>
<?php
# Free the memory allocated for the rows in this box since it is not longer needed.
unset( $t_rows );

