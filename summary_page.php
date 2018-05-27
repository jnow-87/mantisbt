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
 * Display summary page of Statistics
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses print_api.php
 * @uses summary_api.php
 * @uses user_api.php
 */

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('database_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('print_api.php');
require_api('summary_api.php');
require_api('user_api.php');
require_api('elements_api.php');


function tab_by_user(){
	global $t_orcttab;


	section_begin('Developer');
?>
	<!-- DEVELOPER STATS -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th>Status</th>
			<?php echo $t_orcttab ?>
		</tr>
	</thead>
	<?php summary_print_by_developer() ?>
	</table>

	<!-- DEVELOPER BY RESOLUTION -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-15">Resolution</th>
			<?php
				$t_resolutions = MantisEnum::getValues(config_get('resolution_enum_string'));

				foreach ($t_resolutions as $t_resolution){
					echo '<th class="align-right">', get_enum_element('resolution', $t_resolution), "</th>\n";
				}

				echo '<th class="align-right">', '% Fixed', "</th>\n";
			?>
		</tr>
	</thead>
	<?php summary_print_developer_resolution(config_get('resolution_enum_string')) ?>
	</table>

<?php
	section_end();

	section_begin('Reporter');
?>

	<!-- REPORTER STATS -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-35">Status</th>
			<?php echo $t_orcttab ?>
		</tr>
	</thead>
	<?php summary_print_by_reporter() ?>
	</table>

	<!-- REPORTER BY RESOLUTION -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-15">Resolution</th>
			<?php
				$t_resolutions = MantisEnum::getValues(config_get('resolution_enum_string'));

				foreach ($t_resolutions as $t_resolution){
					echo '<th class="align-right">', get_enum_element('resolution', $t_resolution), "</th>\n";
				}

				echo '<th class="align-right">', '% False', "</th>\n";
			?>
		</tr>
	</thead>
	<?php summary_print_reporter_resolution(config_get('resolution_enum_string')) ?>
	</table>

	<!-- REPORTER EFFECTIVENESS -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-35">Effectiveness</th>
			<th class="align-right">Severity</th>
			<th class="align-right">False</th>
			<th class="align-right">Total</th>
		</tr>
	</thead>
	<?php summary_print_reporter_effectiveness(config_get('severity_enum_string'), config_get('resolution_enum_string')) ?>
	</table>

<?php
	section_end();
}

function tab_by_project(){
	global $t_orcttab;
?>
	<!-- BY PROJECT -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-35">Project</th>
			<?php echo $t_orcttab ?>
		</tr>
	</thead>
	<?php summary_print_by_project(); ?>
	</table>
<?php
}

function tab_by_time(){
	$t_specific_where = helper_project_specific_where($f_project_id, auth_get_current_user_id());

	$t_resolved = config_get('bug_resolved_status_threshold');
	# the issue may have passed through the status we consider resolved
	#  (e.g., bug is CLOSED, not RESOLVED). The linkage to the history field
	#  will look up the most recent 'resolved' status change and return it as well
	$t_query = 'SELECT b.id, b.date_submitted, b.last_updated, MAX(h.date_modified) as hist_update, b.status
		FROM {bug} b LEFT JOIN {bug_history} h
			ON b.id = h.bug_id AND h.type=0 AND h.field_name=\'status\' AND h.new_value=' . db_param() . '
			WHERE b.status >=' . db_param() . ' AND ' . $t_specific_where . '
			GROUP BY b.id, b.status, b.date_submitted, b.last_updated
			ORDER BY b.id ASC';

	$t_result = db_query($t_query, array($t_resolved, $t_resolved));

	$t_bug_count = 0;
	$t_bug_id = 0;
	$t_largest_diff = 0;
	$t_total_time = 0;

	while($t_row = db_fetch_array($t_result)){
		$t_bug_count++;
		$t_date_submitted = $t_row['date_submitted'];
		$t_id = $t_row['id'];
		$t_status = $t_row['status'];
		if($t_row['hist_update'] !== null){
			$t_last_updated = $t_row['hist_update'];
		} else{
			$t_last_updated = $t_row['last_updated'];
		}

		if($t_last_updated < $t_date_submitted){
			$t_last_updated = 0;
			$t_date_submitted = 0;
		}

		$t_diff = $t_last_updated - $t_date_submitted;
		$t_total_time = $t_total_time + $t_diff;
		if($t_diff > $t_largest_diff){
			$t_largest_diff = $t_diff;
			$t_bug_id = $t_row['id'];
		}
	}

	if($t_bug_count < 1)
		$t_bug_count = 1;

	$t_average_time = $t_total_time / $t_bug_count;

	$t_largest_diff = number_format($t_largest_diff / SECONDS_PER_DAY, 2);
	$t_total_time = number_format($t_total_time / SECONDS_PER_DAY, 2);
	$t_average_time = number_format($t_average_time / SECONDS_PER_DAY, 2);
?>
	<!-- BY DATE -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-35">Days</th>
			<th class="align-right">Opened</th>
			<th class="align-right">Resolved</th>
			<th class="align-right">Balance</th>
		</tr>
	</thead>
	<?php summary_print_by_date(config_get('date_partitions')) ?>
	</table>

	<!-- MOST ACTIVE -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-85">Most Active</th>
			<th class="align-right">Score</th>
		</tr>
	</thead>
	<?php summary_print_by_activity() ?>
	</table>

	<!-- LONGEST OPEN -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-85">Longest Open</th>
			<th class="align-right">Days</th>
		</tr>
	</thead>
	<?php summary_print_by_age() ?>
	</table>

	<!-- TIME STATS -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th colspan="2">Stats for Resolved Issues (days)</th>
		</tr>
	</thead>
	<tr>
		<td>Longest open issue</td>
		<td class="align-right"><?php
			if($t_bug_id > 0){
				print_bug_link($t_bug_id);
			}
		?></td>
	</tr>
	<tr>
		<td>Longest open</td>
		<td class="align-right"><?php echo $t_largest_diff ?></td>
	</tr>
	<tr>
		<td>Average time</td>
		<td class="align-right"><?php echo $t_average_time ?></td>
	</tr>
	<tr>
		<td>Total time</td>
		<td class="align-right"><?php echo $t_total_time ?></td>
	</tr>
	</table>
<?php
}

function tab_by_issue_field(){
	global $t_orcttab;
?>
	<!-- BY SEVERITY -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-35">Severity</th>
			<?php echo $t_orcttab ?>
		</tr>
	</thead>
	<?php summary_print_by_enum('severity') ?>
	</table>

	<!-- BY CATEGORY -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-35">Type</th>
			<?php echo $t_orcttab ?>
		</tr>
	</thead>
	<?php summary_print_by_category() ?>
	</table>

	<!-- BY RESOLUTION -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-35">Resolution</th>
			<?php echo $t_orcttab ?>
		</tr>
	</thead>
	<?php summary_print_by_enum('resolution') ?>
	</table>

	<!-- BY PRIORITY -->
	<table class="table table-hover table-bordered table-condensed ">
	<thead>
		<tr>
			<th class="width-35">Priority</th>
			<?php echo $t_orcttab ?>
		</tr>
	</thead>
	<?php summary_print_by_enum('priority') ?>
	</table>
<?php
}

$f_project_id = gpc_get_int('project_id', helper_get_current_project());

# Override the current page to make sure we get the appropriate project-specific configuration
$g_project_override = $f_project_id;
access_ensure_project_level(config_get('view_summary_threshold'));

$t_orct_arr = preg_split('/[\)\/\(]/', '(open/resolved/closed/total)', -1, PREG_SPLIT_NO_EMPTY);

$t_orcttab = '';
foreach ($t_orct_arr as $t_orct_s){
	$t_orcttab .= '<th class="align-right">';
	$t_orcttab .= $t_orct_s;
	$t_orcttab .= '</th>';
}

layout_page_header('Statistics');
layout_page_begin();

page_title('Statistics');

column_begin('12');

tabs(array(
	'Project' => 'tab_by_project',
	'User' => 'tab_by_user',
	'Issue' => 'tab_by_issue_field',
	'Time' => 'tab_by_time',
));

column_end();

layout_page_end();
