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
 * Manage Filter Page
 *
 * @package MantisBT
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses filter_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses rss_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'filter_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'rss_api.php' );
require_api( 'elements_api.php' );


auth_ensure_user_authenticated();
form_security_purge('filter_update');


$t_project_id = helper_get_current_project();
$t_user_id = auth_get_current_user_id();

$t_rss_enabled = config_get('rss_enabled');

if(!access_has_project_level(config_get('stored_query_use_threshold')))
	access_denied();

if($t_project_id != ALL_PROJECTS){
	$t_filter_ids_available =
			filter_db_get_queries( ALL_PROJECTS, $t_user_id, false ) +
			filter_db_get_queries( ALL_PROJECTS, null, true ) +
			filter_db_get_queries( $t_project_id, $t_user_id, false ) +
			filter_db_get_queries( $t_project_id, null, true )
			;
}
else
	$t_filter_ids_available = filter_db_get_queries();

filter_cache_rows( $t_filter_ids_available );


/* page content */
layout_page_header( lang_get('manage_filter_page_title' ) );
layout_page_begin();

page_title('Filter Settings');

table_begin(array('', 'Filter', 'Project', 'Visibility', 'Owner'), 'table-condensed table-sortable table-hover no-border', '', array('width="40px"'));
	foreach($t_filter_ids_available as $t_id => $t_name){
		if($t_name == '')
			continue;

		/* edit, delete button */
		$t_edit_btn = '';
		$t_delete_btn = '';

		if(filter_db_can_delete_filter($t_id)){
			$t_edit_btn = format_link(format_icon('fa-pencil'), 'settings/filter_edit_page.php', array('filter_id' => $t_id, 'filter_name' => $t_name), 'inline-page-link', '', 'inline-page-reload');
			$t_delete_btn = format_link(format_icon('fa-trash', 'red'), 'settings/filter_update.php',
							array('filter_id' => $t_id, 'cmd' => 'delete', 'filter_update_token' => form_security_token('filter_update')),
							'inline-page-link', '', 'inline-page-reload'
			);
		}

		/* RSS button */
		$t_rss_btn = format_link(format_icon('fa-rss', 'orange'), rss_get_issues_feed_url(null, null, $t_id));

		/* perma link button */
		$t_perma_link_btn = '';

		if(access_has_project_level(config_get('create_permalink_threshold'))){
			$t_filter = filter_deserialize(filter_db_get_filter($t_id));
			$t_perma_link_btn = format_link(format_icon('fa-link', 'grey'), 'permalink_page.php', array('url' => urlencode(filter_get_url($t_filter))));
		}

		/* filter name input */
		$t_name_input = format_link($t_name, 'view_all_set.php', array('type' => 3, 'source_query_id' => $t_id));

		/* project input */
		$t_project_input = project_get_name(filter_get_field($t_id, 'project_id'));

		/* visibility input */
		$t_public_input = filter_get_field($t_id, 'is_public') ? 'public' : 'private';

		/* owner */
		$t_owner = 	format_link(user_get_name(filter_get_field($t_id, 'user_id')), 'view_user_page.php', array('id' => $t_id), '', 'margin-right:20px!important');

		table_row(array(
			$t_edit_btn . $t_rss_btn . $t_perma_link_btn . $t_delete_btn,
			$t_name_input,
			$t_project_input,
			$t_public_input,
			$t_owner
			)
		);
	}
table_end();

layout_page_end();
