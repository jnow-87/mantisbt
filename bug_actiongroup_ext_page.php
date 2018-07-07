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
 * Bug action group additional actions
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses bug_group_action_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

if(!defined('BUG_ACTIONGROUP_INC_ALLOW')){
	return;
}

require_api('authentication_api.php');
require_api('bug_group_action_api.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('print_api.php');
require_api('string_api.php');
require_api('utility_api.php');
require_api('bug_list_api.php');
require_api('elements_api.php');

$f_filter_num_total = gpc_get_int('filter_num_total', 0);
$f_action = gpc_get_string('bulk_action', '');

$t_cmds = bug_group_action_get_commands(null);
$t_action_name = $t_cmds[$f_action];

$t_external_action = utf8_strtolower(utf8_substr($f_action, utf8_strlen('EXT_')));
$t_form_name = 'bug_actiongroup_' . $t_external_action;

bug_group_action_init($t_external_action);

layout_page_header();
layout_inline_page_begin();

page_title('Bulk Operation: ' . $t_action_name);

column_begin('12');

/* print alerts */
// hint on the number of issues compared to the total number of the filter result
if(count($f_bug_arr) < $f_filter_num_total)
	alert('info', 'Performing action on ' . count($f_bug_arr) . ' out of  ' . $f_filter_num_total) . ' issues';

/* main form */
echo '<form method="post" action="bug_actiongroup_ext.php">';
	actionbar_begin();
		echo '<div class="pull-right">';
		button('Start Action', 'bulk-submit', 'submit');
		echo '</div>';
	actionbar_end();

	/* hidden inputs */
	echo form_security_field($t_form_name);
	input_hidden('bulk_action', string_attribute($t_external_action));
	bug_group_action_print_hidden_fields($f_bug_arr);

	/* additionally required data based on the given action */
	table_begin(array(), 'table-condensed no-border');
		bug_group_action_print_action_fields($t_external_action);
	table_end();
echo '</form>';

/* list of bugs to apply action on */
bug_list_print($f_bug_arr, bug_list_columns('bug_list_columns_bulk', true), 'table-condensed table-hover no-border');

column_end();
layout_inline_page_end();
