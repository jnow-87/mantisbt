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
 * Bugnote action group add include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

if( !defined( 'BUG_ACTIONGROUP_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );
require_api('elements_api.php');

/**
 * Prints the field within the custom action form.  This has an entry for
 * every field the user need to supply + the submit button.  The fields are
 * added as rows in a table that is already created by the calling code.
 * A row has two columns.
 * @return void
 */
function action_add_note_print_fields() {
	if(access_has_project_level(config_get('private_bugnote_threshold'))
		&& access_has_project_level(config_get('set_view_status_threshold'))
	){
		$t_private = format_label('Private:') . format_hspace('2px') . format_checkbox('private', 'private');
	}

	table_row_bug_info_long('Note:<br>' . $t_private, format_textarea('bugnote_text', 'bugnote_text', '', 'input-xs', 'width:100%!important;height:150px;'), '10%');
}

/**
 * Validates the action on the specified bug id.
 *
 * @param integer $p_bug_id A bug identifier.
 * @return string|null On failure: the reason why the action could not be validated. On success: null.
 */
function action_add_note_validate( $p_bug_id ) {
	$f_bugnote_text = gpc_get_string( 'bugnote_text' );

	if( is_blank( $f_bugnote_text ) ) {
		error_parameters( lang_get( 'bugnote' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_add_bugnote_threshold = config_get( 'add_bugnote_threshold' );
	$t_bug_id = $p_bug_id;

	if( bug_is_readonly( $t_bug_id ) ) {
		return lang_get( 'actiongroup_error_issue_is_readonly' );
	}

	if( !access_has_bug_level( $t_add_bugnote_threshold, $t_bug_id ) ) {
		return lang_get( 'access_denied' );
	}

	return null;
}

/**
 * Executes the custom action on the specified bug id.
 *
 * @param integer $p_bug_id The bug id to execute the custom action on.
 * @return null Previous validation ensures that this function doesn't fail. Therefore we can always return null to indicate no errors occurred.
 */
function action_add_note_process( $p_bug_id ) {
	$f_bugnote_text = gpc_get_string( 'bugnote_text' );
	$f_private = gpc_get_bool( 'private' );
	$t_bugnote_id = bugnote_add( $p_bug_id, $f_bugnote_text, '0:00', $f_view_state != VS_PUBLIC );
	bugnote_process_mentions( $p_bug_id, $t_bugnote_id, $f_bugnote_text );
	return null;
}
