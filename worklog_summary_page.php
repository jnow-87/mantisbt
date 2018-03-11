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
 * Display Mantis Billing Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses string_api.php
 * @uses utility_api.php
 * @uses print_api.php
 * @uses access_api.php
 * @uses worklog_api.php
 * @uses html_api.php
 */

require_once('core.php');
require_api('collapse_api.php');
require_api('config_api.php');
require_api('database_api.php');
require_api('filter_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('lang_api.php');
require_api('string_api.php');
require_api('utility_api.php');
require_api('print_api.php');
require_api('access_api.php');
require_api('worklog_api.php');
require_api('html_api.php');


/**
 *	print a table per user and the specified row data
 *
 *	@param	array	$p_users			array containing an entry per user, with each user entry
 *										containing an array with the number of minutes spent for
 *										$p_row_label
 *	@param	string	$p_row_label		string identifying the data in $p_users to be printed
 *	@param	array	$p_rows				array of valid row indices, each index has to have an entry
 *										in $p_users[][$p_row_label]
 *	@param	string	$p_row_name_func	name of function used to format the name of each row index
 *										within $p_rows
 *
 *	@return	none
 */
function print_worklog_table($p_users, $p_row_label, $p_rows, $p_row_name_func){
?>
	<table class="table table-bordered table-condensed table-striped">
		<!-- table head -->
		<tr>
			<th>
			</th>

			<?php foreach($p_users as $t_user){ ?>
				<th class="small-caption">
					<?php echo user_get_name($t_user['id']); ?>
				</th>
			<?php } ?>

			<th class="small-caption">
				<?php echo lang_get('total_time'); ?>
			</th>
		</tr>

		<!-- row list -->
		<?php
		$t_col_total = array();

		foreach ($p_rows as $t_row) { ?>
		<tr>
			<th class="small-caption category">
				<?php echo $p_row_name_func($t_row); ?>
			</th>

			<?php
			$t_row_total = 0;

			foreach($p_users as $t_user){
				if(!isset($t_col_total[$t_user['id']]))
					$t_col_total[$t_user['id']] = 0;

				$t_col_total[$t_user['id']] += $t_user[$p_row_label][$t_row];
				$t_row_total += $t_user[$p_row_label][$t_row];
			?>
				<td class="small-caption">
					<?php
						$t_minutes = $t_user[$p_row_label][$t_row];
						echo $t_minutes == 0 ? '-' : db_minutes_to_hhmm($t_minutes);
					?>
				</td>
			<?php } ?>

			<td class="small-caption">
				<?php echo db_minutes_to_hhmm($t_row_total); ?>
			</td>
		</tr>
		<?php
		} ?>

		<!-- spacer -->
		<tr class="spacer"><td colspan="6"></td></tr>
		<tr class="hidden"></tr>

		<!-- summary row -->
		<tr>
			<th class="small-caption category">
				<?php echo lang_get('total_time'); ?>
			</th>

			<?php
			$t_total = 0;

			foreach($p_users as $t_user){
				$t_total += $t_col_total[$t_user['id']];
			?>
				<td class="small-caption">
					<?php echo db_minutes_to_hhmm($t_col_total[$t_user['id']]); ?>
				</td>
			<?php } ?>

			<td class="small-caption">
				<?php echo db_minutes_to_hhmm($t_total); ?>
			</td>
		</tr>
	</table>
<?php
}



worklog_ensure_reporting_access();

layout_page_header(lang_get('worklog_link'));
layout_page_begin();

$f_date_from = gpc_get_string(FILTER_PROPERTY_START_DATE_SUBMITTED, '');
$f_date_to = gpc_get_string(FILTER_PROPERTY_END_DATE_SUBMITTED, '');

if($f_date_from == '')
	$f_date_from = date(config_get('short_date_format'));

if($f_date_to == '')
	$f_date_to = date(config_get('short_date_format'));


$f_get_bugnote_stats_button = gpc_get_string('get_bugnote_stats_button', '');

$f_project_id = helper_get_current_project();

$t_collapse_block = is_collapsed('time_tracking_stats');
$t_block_css = $t_collapse_block ? 'collapsed' : '';
$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';


# retrieve worklog stats
if(!is_blank($f_get_bugnote_stats_button))
	$t_stats = worklog_get_for_project($f_project_id, $f_date_from, $f_date_to);
?>

<!-- Time tracking date range input form -->
<!-- CSRF protection not required here - form does not result in modifications -->
<div class="col-md-6-left col-xs-12">
	<div id="time_tracking_stats" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-clock-o"></i>
				<?php echo lang_get('time_tracking') ?>
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<i class="1 ace-icon <?php echo $t_block_icon ?> fa bigger-125"></i>
				</a>
			</div>
		</div>

		<div class="widget-body">
			<form method="post" action="">
			<div class="widget-toolbox">
				<input type="hidden" name="id" value="<?php echo isset($f_bug_id) ? $f_bug_id : 0 ?>" />
				<?php
					$t_filter = array();
					$t_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] = 'on';
					$t_filter[FILTER_PROPERTY_START_DATE_SUBMITTED] = $f_date_from;
					$t_filter[FILTER_PROPERTY_END_DATE_SUBMITTED] = $f_date_to;
					filter_init($t_filter);
					print_filter_do_filter_by_date();
				?>

				<input name="get_bugnote_stats_button" class="btn btn-primary btn-xs btn-white btn-round pull-right "
					   value="<?php echo lang_get('time_tracking_get_info_button') ?>" type="submit">

				<div class="space-2"></div>
			</div>
			</form>
		</div>
	</div>

	<div class="space-10"></div>
</div>

<div class="col-md-6-left col-xs-12">

<?php
if($t_stats['users']){
?>
	<!-- time per project and user -->
	<div class="widget-box widget-color-blue2 table-responsive">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php echo lang_get('time_tracking') . ' per ' . lang_get('project') ?>
			</h4>
		</div>

		<?php print_worklog_table($t_stats['users'], 'projects', $t_stats['projects'], 'project_get_name'); ?>
	</div>

	<!-- time per issue and user -->
	<div class="widget-box widget-color-blue2 table-responsive">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php echo lang_get('time_tracking') . ' per ' . lang_get('bug') ?>
			</h4>
		</div>

		<?php print_worklog_table($t_stats['users'], 'bugs', $t_stats['bugs'], 'string_get_bug_view_link'); ?>
	</div>
<?php
}
?>

</div>

<?php
layout_page_end();
