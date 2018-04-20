<?php
# Mantis - a php based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * View Bug Revisions
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses bug_revision_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

require_once('core.php');
require_api('access_api.php');
require_api('bug_api.php');
require_api('bugnote_api.php');
require_api('bug_revision_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('print_api.php');
require_api('string_api.php');
require_api('user_api.php');


####
## input
####

$f_bug_id = gpc_get_int('bug_id', 0);
$f_bugnote_id = gpc_get_int('bugnote_id', 0);
$f_rev_id = gpc_get_int('rev_id', 0);
$f_drop_bug_id = gpc_get_int('drop_bug_id', 0);
$f_drop_rev_id = gpc_get_int('drop_rev_id', 0);

json_prepare();


####
## handle form actions
####

# check if a revision shall be dropped
if($f_drop_bug_id != 0 && $f_drop_rev_id != 0){
	form_security_validate('bug_revision_drop');
	access_ensure_bug_level(config_get('bug_revision_drop_threshold'), $f_drop_bug_id);

	bug_revision_drop($f_drop_rev_id);
	form_security_purge('bug_revision_drop');
}


####
## get revision list
####

if($f_bug_id)			$t_bug_revisions = array_reverse(bug_revision_list($f_bug_id), true);
else if($f_bugnote_id)	$t_bug_revisions = bug_revision_list(bugnote_get_field($f_bugnote_id, 'bug_id'), REV_ANY, $f_bugnote_id);
else if($f_rev_id)		$t_bug_revisions = bug_revision_like($f_rev_id);
else					json_error('Undefined error while processing the issue revision page');


####
## page content
####

layout_inline_page_begin();
page_title('Bugnote Revisions');

table_begin(array(), 'table-condensed no-border');

	if(count($t_bug_revisions) < 1)
		table_row(array('There are no revisions for this note'), '', array('class="center"'));

	$t_can_drop = null;
	$t_user_access = null;

	foreach($t_bug_revisions as $t_rev){
		if(is_null($t_can_drop))
			$t_can_drop = access_has_bug_level(config_get('bug_revision_drop_threshold'), $t_rev['bug_id']);

		switch($t_rev['type']){
			case REV_DESCRIPTION:
				$t_label = lang_get('description');
				break;

			case REV_BUGNOTE:
				if(is_null($t_user_access))
					$t_user_access = access_has_bug_level(config_get('private_bugnote_threshold'), $t_rev['bug_id']);

				if(!$t_user_access)
					continue;

				$t_label = lang_get('bugnote');
				break;

			default:
				$t_label = '';
		}

		$t_drop_btn = '';

		if($t_can_drop){
			$t_drop_btn = 
				'<form method="post" action="bug_revision_view_page.php" class="form-inline input-hover-form">'
				. format_input_hidden('bug_revision_drop_token', form_security_token('bug_revision_drop'))
				. format_input_hidden('bug_id', $f_bug_id)
				. format_input_hidden('bugnote_id', $f_bugnote_id)
				. format_input_hidden('rev_id', $f_rev_id)
				. format_input_hidden('drop_rev_id', $t_rev['id'])
				. format_input_hidden('drop_bug_id', $t_rev['bug_id'])
				. format_button('<i class="fa fa-trash red"></i>', 'drop_' . $t_rev['id'], 'submit', '', 'btn-icon', true)
				. '</form>';
		}

		$t_date = string_display_line(date(config_get('normal_date_format'), $t_rev['timestamp']));
		$t_user = prepare_user_name($t_rev['user_id']);

		table_row(array($t_drop_btn, $t_user, $t_date, $t_label, string_display_links($t_rev['value'])), '', array('', '', '', '', 'width="60%"'));
	}

table_end();

layout_inline_page_end();
