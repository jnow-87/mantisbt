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
 * View worklog entries for the given bugnote
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
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses worklog_api.php
 */

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('bugnote_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('error_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('print_api.php');
require_api('string_api.php');
require_api('worklog_api.php');


####
## input
####

# config variables
$t_normal_date_format = config_get('normal_date_format');

# form inputs
$f_bugnote_id = gpc_get_int('bugnote_id', 0);


####
## access validation
#####

# get bug note
$t_bug_id = bugnote_get_field($f_bugnote_id, 'bug_id');

$t_bug = bug_get($t_bug_id, true);
if($t_bug->project_id != helper_get_current_project()) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

# Check if the bug is readonly
if(bug_is_readonly($t_bug_id)) {
	error_parameters($t_bug_id);
	trigger_error(ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR);
}

# Check if the current user is allowed to change the view state of this bugnote
$t_user_id = bugnote_get_field($f_bugnote_id, 'reporter_id');

if($t_user_id == auth_get_current_user_id()) {
	access_ensure_bugnote_level(config_get('bugnote_user_change_view_state_threshold'), $f_bugnote_id);
} else {
	access_ensure_bugnote_level(config_get('update_bugnote_threshold'), $f_bugnote_id);
	access_ensure_bugnote_level(config_get('change_view_status_threshold'), $f_bugnote_id);
}


####
## main form
####

$t_work_log = bugnote_worklog_get($f_bugnote_id);

layout_page_header(bug_format_summary($t_bug_id, SUMMARY_CAPTION));
layout_page_begin();

?>

<div class="col-md-6-left col-xs-12">
<div class="widget-box widget-color-blue2">
	<!-- header -->
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-history"></i>
			<?php echo lang_get('view_worklog') . ' ' . lang_get('for') . ' ' . lang_get('bugnote') . ': ' . $f_bugnote_id ?>
		</h4>
	</div>

	<!-- body -->
	<div class="widget-body">
		<!-- back to issue button -->
		<div class="widget-toolbox">
			<div class="btn-toolbar">
				<div class="btn-group pull-right">
					<?php print_small_button('view.php?id=' . $t_bug_id, lang_get('back_to_issue')); ?>
				</div>
			</div>
		</div>

		<!-- list of worklog entries -->
		<div class="widget-main no-padding">
			<div class="table-responsive">
				<table class="table table-bordered table-condensed">

				<?php
					$t_total = 0;

				foreach ($t_work_log as $t_entry){
					$t_worklog_id = $t_entry['id'];
					$t_date = date($t_normal_date_format, $t_entry['date']);
					$t_user = prepare_user_name($t_entry['user_id']);
					$t_time = db_minutes_to_hhmm($t_entry['time']);

					$t_total += $t_entry['time'];
				?>
					<tr>
						<!-- info column -->
						<th class="category" width="10%"><?php echo $t_date . ' ' . lang_get('by') . ' ' . $t_user ?></th>

						<!-- update column -->
						<td width="10%">
							<form id="bugnote_worklog" method="post" action="bugnote_worklog_update.php">
								<input type="hidden" name="bugnote_id" value="<?php echo $f_bugnote_id ?>"/>
								<input type="hidden" name="worklog_id" value="<?php echo $t_worklog_id ?>"/>
								<input type="text" name="time_tracking" class="input-xs pull-left" size="5" value="<?php echo $t_time ?>" />
								<input type="hidden" name="action" value="update"/>

								<input type="submit" class="btn btn-primary btn-xs btn-white btn-round" value="<?php echo lang_get('update') ?>" />
							</form>
						</td>

						<!-- delete column -->
						<td width="5%">
							<?php print_small_button('bugnote_worklog_update.php?bugnote_id=' . $f_bugnote_id . '&action=delete&worklog_id=' . $t_worklog_id, lang_get('delete_link'));	?>
						</td>
					</tr>
				<?php
				} ?>

					<!-- worklog total -->
					<tr class="spacer"><td colspan=6></td></tr>
					<tr>
						<th class="category"> <?php echo lang_get('total_time_for_issue') ?></th>
						<td><?php echo db_minutes_to_hhmm($t_total) ?></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
</div>

<?php

# free worklog data
unset($t_work_log);

layout_page_end();
