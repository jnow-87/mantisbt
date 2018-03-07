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
 * This include file prints out the bug bugnote_stats
 * $f_bug_id must already be defined
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses bugnote_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

if( !defined( 'BILLING_INC_ALLOW' ) ) {
	return;
}

require_api( 'bugnote_api.php' );
require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

?>
<?php


$t_date_submitted = $_POST[FILTER_PROPERTY_START_DATE_SUBMITTED]; 

if($t_date_submitted == ''){
	$t_date_submitted = date(config_get( 'short_date_format' ));
}

$t_today = $_POST[FILTER_PROPERTY_END_DATE_SUBMITTED];

if($t_today == ''){
	$t_today = date(config_get( 'short_date_format' ));
}


$f_get_bugnote_stats_button = gpc_get_string( 'get_bugnote_stats_button', '' );

$f_project_id = helper_get_current_project();

$t_collapse_block = is_collapsed( 'time_tracking_stats' );
$t_block_css = $t_collapse_block ? 'collapsed' : '';
$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';


# Retrieve time tracking information
if( !is_blank( $f_get_bugnote_stats_button ) )
	$t_bugnote_stats = billing_get_summaries( $f_project_id, $f_date_from, $f_date_to );

# Time tracking date range input form
# CSRF protection not required here - form does not result in modifications
?>

<div class="col-md-6-left col-xs-12">
	<div id="time_tracking_stats" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-clock-o"></i>
				<?php echo lang_get( 'time_tracking' ) ?>
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<i class="1 ace-icon <?php echo $t_block_icon ?> fa bigger-125"></i>
				</a>
			</div>
		</div>

		<div class="widget-body">
			<form method="post" action="">
			<div class="widget-main">
				<input type="hidden" name="id" value="<?php echo isset( $f_bug_id ) ? $f_bug_id : 0 ?>" />
				<?php
					$t_filter = array();
					$t_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] = 'on';
					$t_filter[FILTER_PROPERTY_START_DATE_SUBMITTED] = $t_date_submitted;
					$t_filter[FILTER_PROPERTY_END_DATE_SUBMITTED] = $t_today;
					filter_init( $t_filter );
					print_filter_do_filter_by_date( true );
				?>

			<div class="widget-toolbox padding-8 clearfix">
				<input name="get_bugnote_stats_button" class="btn btn-primary btn-sm btn-white btn-round"
					   value="<?php echo lang_get( 'time_tracking_get_info_button' ) ?>" type="submit">
			</div>
			</form>
		</div>
	</div>

<?php
	if( !is_blank( $f_get_bugnote_stats_button ) ) {
		# Retrieve time tracking information
		$t_from = $t_date_submitted;
		$t_to = $t_today;
		$t_bugnote_stats = billing_get_summaries( $f_project_id, $t_from, $t_to, $f_bugnote_cost );

		# Sort the array by bug_id, user/real name
		if( ON == config_get( 'show_realname' ) ) {
			$t_name_field = 'realname';
		} else {
			$t_name_field = 'username';
		}

		if( is_blank( $f_bugnote_cost ) || ( (double)$f_bugnote_cost == 0 ) ) {
			$t_cost_col = false;
		}

		echo '<br />';

		$t_exports = array(
			'csv_export' => 'billing_export_to_csv.php',
		);

		foreach( $t_exports as $t_export_label => $t_export_page ) {
			echo '<a class="btn btn-primary btn-sm btn-white btn-round" ';
			echo 'href="' . $t_export_page . '?';
			echo 'from=' . $t_from . '&amp;to=' . $t_to;
			echo '&amp;cost=' . $f_bugnote_cost;
			echo '&amp;project_id=' . $f_project_id;
			echo '">' . lang_get( $t_export_label ) . '</a> ';
		}

		echo '<br />';

?>
<div class="space-10"></div>
</div>

<div class="col-md-6-left col-xs-12">

<!-- time per user -->
<?php
if($t_bugnote_stats['users']){
?>
	<div class="widget-box widget-color-blue2 table-responsive">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php echo lang_get( 'time_tracking' ) . ' per ' . lang_get($t_name_field) ?>
			</h4>
		</div>

		<table class="table table-bordered table-condensed table-striped">
		<tr>
			<td class="small-caption bold">
				<?php echo lang_get( $t_name_field ) ?>
			</td>
			<td class="small-caption bold">
				<?php echo lang_get( 'time_tracking' ) ?>
			</td>
		</tr>

	<?php
		foreach ( $t_bugnote_stats['users'] as $t_username => $t_user_info ) {
	?>
		<tr>
			<td class="small-caption">
				<?php echo $t_username; ?>
			</td>
			<td class="small-caption">
				<?php echo db_minutes_to_hhmm( $t_user_info['minutes'] ); ?>
			</td>
		</tr>
	<?php	} ?>
		<tr class="row-category2">
			<td class="small-caption bold">
				<?php echo lang_get( 'total_time' ); ?>
			</td>
			<td class="small-caption bold">
				<?php echo db_minutes_to_hhmm( $t_bugnote_stats['total']['minutes'] ); ?>
			</td>
		</tr>
		</table>

	<?php
	?>
	</div>
<?php
}
?>

<!-- time per issue -->
<?php
if($t_bugnote_stats['issues']){
?>
	<div class="widget-box widget-color-blue2 table-responsive">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php echo lang_get( 'time_tracking' ) . ' per ' . lang_get('bug') ?>
			</h4>
		</div>

		<table class="table table-bordered table-condensed table-striped">
		<tr>
			<td class="small-caption bold">
				<?php echo lang_get( 'summary' ) ?>
			</td>
			<td class="small-caption bold">
				<?php echo lang_get( 'time_tracking' ) ?>
			</td>
		</tr>
	<?php
			foreach ( $t_bugnote_stats['issues'] as $t_issue_id => $t_issue ) {
				$t_project_info = ( !isset( $f_bug_id ) && $f_project_id == ALL_PROJECTS ) ? '[' . project_get_name( $t_issue['project_id'] ) . ']' . lang_get( 'word_separator' ) : '';
				$t_link = sprintf( lang_get( 'label' ), string_get_bug_view_link( $t_issue_id ) ) . lang_get( 'word_separator' ) . $t_project_info . string_display( $t_issue['summary'] );
	?>

				<?php
				$t_bug_time_total = 0;

				foreach( $t_issue['users'] as $t_username => $t_user_info ) {
					$t_bug_time_total += $t_user_info['minutes'];
				}
				?>
				<tr class="row-category-history">
					<td class="small-caption"> <?php echo $t_link ?></td>
					<td class="small-caption"> <?php echo db_minutes_to_hhmm($t_bug_time_total) ?></td>
				</tr>
	<?php
			} # end for issues loop ?>

		<tr>
			<td class="small-caption bold">
				<?php echo lang_get( 'total_time' ); ?>
			</td>
			<td class="small-caption bold">
				<?php echo db_minutes_to_hhmm( $t_bugnote_stats['total']['minutes'] ); ?>
			</td>
		</tr>
		</table>
	</div>
	</div>
<?php
}
?>

<!-- time per issue and user -->
<?php
if($t_bugnote_stats['issues']){
?>
	<div class="col-md-6-right col-xs-12">
	<div class="widget-box widget-color-blue2 table-responsive">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php echo lang_get( 'time_tracking' ) . ' per ' . lang_get('bug') . '/' . lang_get($t_name_field) ?>
			</h4>
		</div>

		<table class="table table-bordered table-condensed table-striped">
		<tr>
			<td class="small-caption bold">
				<?php echo lang_get('summary') . '/' . lang_get( $t_name_field ) ?>
			</td>
			<td class="small-caption bold">
				<?php echo lang_get( 'time_tracking' ) ?>
			</td>
		</tr>
	<?php
			foreach ( $t_bugnote_stats['issues'] as $t_issue_id => $t_issue ) {
				$t_project_info = ( !isset( $f_bug_id ) && $f_project_id == ALL_PROJECTS ) ? '[' . project_get_name( $t_issue['project_id'] ) . ']' . lang_get( 'word_separator' ) : '';
				$t_link = sprintf( lang_get( 'label' ), string_get_bug_view_link( $t_issue_id ) ) . lang_get( 'word_separator' ) . $t_project_info . string_display( $t_issue['summary'] );
				echo '<tr class="row-category-history"><td colspan="4">' . $t_link . '</td></tr>';

				foreach( $t_issue['users'] as $t_username => $t_user_info ) {
	?>
		<tr>
			<td class="small-caption">
				<?php echo $t_username ?>
			</td>
			<td class="small-caption">
				<?php echo db_minutes_to_hhmm( $t_user_info['minutes'] ) ?>
			</td>
		</tr>

	<?php
				} # end of users within issues loop
			} # end for issues loop ?>

		<tr>
			<td class="small-caption bold">
				<?php echo lang_get( 'total_time' ); ?>
			</td>
			<td class="small-caption bold">
				<?php echo db_minutes_to_hhmm( $t_bugnote_stats['total']['minutes'] ); ?>
			</td>
		</tr>
		</table>
	</div>
	</div>
<?php
}
?>

<?php
