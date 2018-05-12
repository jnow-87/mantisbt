<?php
# MantisBT - a php based bugtracking system

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

require_once('core.php');
require_api('timeline_api.php');
require_api('elements_api.php');

# Variables that are defined in parent script:
#
# $g_timeline_filter	Filter array to be used to get timeline event
#						If undefined, it's initialized as null.
# $g_timeline_user		User id to limit timeline scope.
#						If undefined, it's initialized as null.


if(!isset($g_timeline_filter))
	$g_timeline_filter = null;

if(!isset($g_timeline_user))
	$g_timeline_user = null;


/* prepare */
$f_days = gpc_get_int('days', 0);

$t_end_time = time() - ($f_days * SECONDS_PER_DAY);
$t_start_time = $t_end_time - (7 * SECONDS_PER_DAY);
$t_events = timeline_events($t_start_time, $t_end_time, 0, $g_timeline_filter, $g_timeline_user);

$t_url_page = $_SERVER["PHP_SELF"];
$t_url_params = $_GET;
if(isset($t_url_params['all']))
	unset($t_url_params['all']);

$t_short_date_format = config_get('short_date_format');
$t_url_params['days'] = $f_days + 7;
$t_next_days = ($f_days - 7) > 0 ? $f_days - 7 : 0;


/* main */
actionbar_begin();
	echo '<div class="pull-right">';
	label(date($t_short_date_format, $t_start_time), 'label-grey');
	echo ' - ';
	label(date($t_short_date_format, $t_end_time), 'label-grey');
	hspace('10px');

	$t_href = $t_url_page . '?' . http_build_query($t_url_params);

	button_link('Prev', $t_href);
	hspace('1px');

	if($t_next_days != $f_days){
		$t_url_params['days'] = $t_next_days;
		$t_href = $t_url_page . '?' . http_build_query($t_url_params);
		button_link('Next', $t_href);
	}
	echo '</div>';
actionbar_end();

table_begin(array(''), 'table-condensed table-searchable no-border', 'style="background:transparent"');

if(empty($t_events))
	echo '<tr><td class="center">No activity within time range</td></tr>';

foreach($t_events as $t_event)
	echo '<tr><td>' . $t_event->html() . '</td></tr>';

table_end();
