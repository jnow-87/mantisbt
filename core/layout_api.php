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
 * Layout API
 *
 * UI functions to render layout elements in every page. The layout api layer sits above the html api and abstract
 * the lower level html markup into web components
 *
 * Here is the call order for the layout functions
 *
 * layout_page_header
 *      layout_page_header_begin
 *      layout_page_header_end
 * layout_page_begin
 *      ...Page content here...
 * layout_page_end
 *
 *
 *
 * @package CoreAPI
 * @subpackage LayoutAPI
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses utility_api.php
 */


require_api( 'access_api.php' );
require_api( 'utility_api.php' );
require_api('elements_api.php');


/**
 * Print the page header section
 * @param string $p_page_title   Html page title.
 * @param string $p_redirect_url URL to redirect to if necessary.
 * @param string $p_page_id      The page id.
 * @return void
 */
function layout_page_header( $p_page_title = null, $p_redirect_url = null, $p_page_id = null ) {
	layout_page_header_begin( $p_page_title );
	if( $p_redirect_url !== null ) {
		html_meta_redirect( $p_redirect_url );
	}

	layout_page_header_end( $p_page_id );
}

/**
 * Print the part of the page that comes before meta redirect tags should be inserted
 * @param string $p_page_title Page title.
 * @return void
 */
function layout_page_header_begin( $p_page_title = null ) {
	html_begin();
	html_head_begin();
	html_content_type();

	global $g_robots_meta;
	if( !is_blank( $g_robots_meta ) ) {
		echo "\t", '<meta name="robots" content="', $g_robots_meta, '" />', "\n";
	}

	html_title( $p_page_title );
	layout_head_meta();
	html_css();
	layout_head_css();
	html_rss_link();

	$t_favicon_image = config_get( 'favicon_image' );
	if( !is_blank( $t_favicon_image ) ) {
		echo "\t", '<link rel="shortcut icon" href="', helper_mantis_url( $t_favicon_image ), '" type="image/x-icon" />', "\n";
	}

	# Advertise the availability of the browser search plug-ins.
	$t_title = config_get( 'search_title' );
	$t_searches = array( 'text', 'id' );
	foreach( $t_searches as $t_type ) {
		echo "\t",
			'<link rel="search" type="application/opensearchdescription+xml" ',
				'title="' . sprintf( lang_get( "opensearch_{$t_type}_description" ), $t_title ) . '" ',
				'href="' . string_sanitize_url( 'browser_search_plugin.php?type=' . $t_type, true ) .
				'"/>',
			"\n";
	}

	html_head_javascript();
}

/**
 * Print the part of the page that comes after meta tags and before the
 *  actual page content, but without login info or menus.  This is used
 *  directly during the login process and other times when the user may
 *  not be authenticated
 *
 * @param string $p_page_id The id of the page.
 *
 * @return void
 */
function layout_page_header_end( $p_page_id = null) {
	global $g_error_send_page_header;

	event_signal( 'EVENT_LAYOUT_RESOURCES' );
	html_head_end();

	if ( $p_page_id === null ) {
		$t_body_id = '';
	} else {
		$t_body_id = 'id="' . $p_page_id . '" ';
	}

	# Add right-to-left css if needed
	if( layout_is_rtl() ) {
		echo '<body ' . $t_body_id . 'class="skin-3 rtl">', "\n";
	} else {
		echo '<body ' . $t_body_id . 'class="skin-3">', "\n";
	}

	event_signal( 'EVENT_LAYOUT_BODY_BEGIN' );

	$g_error_send_page_header = false;
}

/**
 * Print page common elements including navbar, info bar
 * @return void
 */
function layout_page_begin() {
	layout_navbar();

	if( !db_is_connected() ) {
		return;
	}

	layout_main_container_begin();
	layout_main_content_begin();
	layout_page_content_begin();
	layout_statusbar();

	if( auth_is_user_authenticated() ) {
		if( ON == config_get( 'show_project_menu_bar' ) ) {
			echo '<div class="row">' , "\n";
			print_project_menu_bar();
			echo '</div>' , "\n";
		}
	}
	echo '<div class="row">' , "\n";

	event_signal( 'EVENT_LAYOUT_CONTENT_BEGIN' );
}

/**
 * Print elements at the end of each page
 * @return void
 */
function layout_page_end() {
	if( !db_is_connected() ) {
		return;
	}

	event_signal( 'EVENT_LAYOUT_CONTENT_END' );

	echo '</div>' , "\n";

	layout_page_content_end();
	layout_main_content_end();

	layout_footer();
	layout_scroll_up_button();

	layout_main_container_end();
	layout_body_javascript();

	html_body_end();
	html_end();
}

/**
 * print elements at the begin of inline page
 */
function layout_inline_page_begin(){
	echo '<div class="inline-page">';

	echo '<div class="row">';
	echo '<div class="pull-right">';
	button('<i class="fa fa-times"></i>', 'close_inline_page', 'button', '',  'inline-page-close', true);
	echo '</div>';
	echo '</div>';

	echo '<div class="row">';
}

/**
 * print elements at the end of inline page
 */
function layout_inline_page_end(){
	echo '</div>';
	echo '</div>';

	html_body_end();
	html_end();
}

/**
 * Print common elements for admin pages
 * @return void
 */
function layout_admin_page_begin() {
	layout_navbar();

	layout_main_container_begin();
}

/**
 * Print elements at the end of each admin page
 * @return void
 */
function layout_admin_page_end() {
	layout_footer();
	layout_scroll_up_button();

	layout_main_container_end();
	layout_body_javascript();

	html_body_end();
    html_end();
}



/**
 * Check if the layout is setup for right to left languages
 * @return bool
 */
function layout_is_rtl() {
	if( lang_get( 'directionality' ) == 'rtl' ) {
		return true;
	}
	return false;
}

/**
 * Print meta tags for the page head
 * @return null
 */
function layout_head_meta() {
	# use the following meta to force IE use its most up to date rendering engine
	echo '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />' . "\n";

	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />' . "\n";
}

/**
 * Print css link directives for the head section of the page
 * @return null
 */
function layout_head_css() {
	# bootstrap & fontawesome
	if ( config_get_global( 'cdn_enabled' ) == ON ) {
		html_css_cdn_link( 'https://maxcdn.bootstrapcdn.com/bootstrap/' . BOOTSTRAP_VERSION . '/css/bootstrap.min.css' );
		html_css_cdn_link( 'https://maxcdn.bootstrapcdn.com/font-awesome/' . FONT_AWESOME_VERSION . '/css/font-awesome.min.css' );

		# theme text fonts
		html_css_cdn_link( 'https://fonts.googleapis.com/css?family=Open+Sans:300,400' );

		# datetimepicker
		html_css_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/' . DATETIME_PICKER_VERSION . '/css/bootstrap-datetimepicker.min.css' );
	} else {
		html_css_link( 'bootstrap-' . BOOTSTRAP_VERSION . '.min.css' );
		html_css_link( 'font-awesome-' . FONT_AWESOME_VERSION . '.min.css' );

		# theme text fonts
		html_css_link( 'open-sans.css' );

		# datetimepicker
		html_css_link( 'bootstrap-datetimepicker-' . DATETIME_PICKER_VERSION . '.min.css' );
	}

	# theme styles
	html_css_link( 'ace.min.css' );
	html_css_link( 'ace-mantis.css' );
	html_css_link( 'dataTables.bootstrap.min.css');
	html_css_link( 'jquery.dataTables.min.css');

	# mantis specific, including
	# 	changes to bootstrap, ace
	#	additional elements
	html_css_link( 'mantis-hr.css' );
	html_css_link( 'mantis-elements.css' );
	html_css_link( 'mantis-bootstrap.css' );
	html_css_link( 'mantis-table.css' );
	html_css_link( 'mantis-input.css' );

	# handle IE separately
	echo '<!--[if lte IE 9]>';
	html_css_link( 'ace-part2.min.css' );
	echo '<![endif]-->';
	html_css_link( 'ace-skins.min.css' );

	if( layout_is_rtl() ) {
		html_css_link( 'ace-rtl.min.css' );
	}

	echo '<!--[if lte IE 9]>';
	html_css_link( 'ace-ie.min.css' );
	echo '<![endif]-->';
	echo "\n";
}


/**
 * Print javascript directives for the head section of the page
 * @return null
 */
function layout_head_javascript() {
	# HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries
	echo '<!--[if lte IE 8]>';
	html_javascript_link( 'html5shiv.min.js' );
	html_javascript_link( 'respond.min.js' );
	echo '<![endif]-->';
	echo "\n";
}


/**
 * Print javascript directives before the closing of the page body element
 * @return null
 */
function layout_body_javascript() {
	if ( config_get_global( 'cdn_enabled' ) == ON ) {
		# bootstrap
		html_javascript_cdn_link( 'https://maxcdn.bootstrapcdn.com/bootstrap/' . BOOTSTRAP_VERSION . '/js/bootstrap.min.js', BOOTSTRAP_HASH );

		# moment & datetimepicker
		html_javascript_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/' . MOMENT_VERSION . '/moment-with-locales.min.js', MOMENT_HASH );
		html_javascript_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/' . DATETIME_PICKER_VERSION . '/js/bootstrap-datetimepicker.min.js', DATETIME_PICKER_HASH );

		# typeahead.js
		html_javascript_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/' . TYPEAHEAD_VERSION . '/typeahead.jquery.min.js', TYPEAHEAD_HASH );

		# listjs
		html_javascript_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/list.js/' . LISTJS_VERSION . '/list.min.js', LISTJS_HASH );
	} else {
		# bootstrap
		html_javascript_link( 'bootstrap-' . BOOTSTRAP_VERSION . '.min.js' );

		# moment & datetimepicker
		html_javascript_link( 'moment-with-locales-' . MOMENT_VERSION . '.min.js' );
		html_javascript_link( 'bootstrap-datetimepicker-' . DATETIME_PICKER_VERSION . '.min.js' );

		# typeahead.js
		html_javascript_link( 'typeahead.jquery-' . TYPEAHEAD_VERSION . '.min.js' );

		# listjs
		html_javascript_link( 'list-' . LISTJS_VERSION . '.min.js' );
	}

	# ace theme scripts
	html_javascript_link( 'ace.min.js' );

	html_javascript_link('mantis_inc.js');
	html_javascript_link('mantis_inline_page.js');
	html_javascript_link('mantis_table.js');
	html_javascript_link('mantis_hr.js');
	html_javascript_link('mantis_input.js');
	html_javascript_link('dataTables.bootstrap.min.js');
	html_javascript_link('jquery.dataTables.min.js');
}


/**
 * Print opening markup for login/signup/register pages
 * @return null
 */
function layout_login_page_begin() {
	html_begin();
	html_head_begin();
	html_content_type();

	global $g_robots_meta;
	if( !is_blank( $g_robots_meta ) ) {
		echo "\t", '<meta name="robots" content="', $g_robots_meta, '" />', "\n";
	}

	html_title();
	layout_head_meta();
	html_css();
	layout_head_css();
	html_rss_link();

	$t_favicon_image = config_get( 'favicon_image' );
	if( !is_blank( $t_favicon_image ) ) {
		echo "\t", '<link rel="shortcut icon" href="', helper_mantis_url( $t_favicon_image ), '" type="image/x-icon" />', "\n";
	}

	# Advertise the availability of the browser search plug-ins.
	echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Text Search" href="' . string_sanitize_url( 'browser_search_plugin.php?type=text', true) . '" />' . "\n";
	echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Issue Id" href="' . string_sanitize_url( 'browser_search_plugin.php?type=id', true) . '" />' . "\n";
	
	html_head_javascript();
	
	event_signal( 'EVENT_LAYOUT_RESOURCES' );
	html_head_end();

	echo '<body class="login-layout light-login">';
	layout_main_container_begin();
	layout_main_content_begin();
	echo '<div class="row">';
}

/**
 * Print closing markup for login/signup/register pages
 * @return null
 */
function layout_login_page_end() {
	echo '</div>';
	layout_main_content_end();
	layout_main_container_end();
	layout_body_javascript();

	echo '</body>', "\n";
}

/**
 * Render navbar at the top of the page
 * @return null
 */
function layout_navbar() {
	echo '<div id="navbar" class="navbar navbar-default navbar-collapse navbar-fixed-top noprint">';

	/* mantis logo */
	echo '<div class="pull-left">';
	echo '<a href="' . config_get('logo_url') . '">' .
		 '<img src="' . helper_mantis_url( 'images/mantis_logo_title.png' ) . '"height="30" style="margin-right:20px"/>' .
		 '</a>';
	echo '</div>';

	/* menus */
	// issues
	$t_menu = array(
		array('label' => 'Report Issue', 'data' => array('link' => 'bug_report_page.php', 'icon' => 'fa-edit')),
		array('label' => 'divider', 'data' => ''),
		array('label' => 'My View', 'data' => array('link' => 'my_view_page.php', 'icon' => 'fa-dashboard')),
		array('label' => 'View Issues', 'data' => array('link' => 'view_all_bug_page.php', 'icon' => 'fa-tasks'))
	);

	dropdown_menu('Issues', $t_menu, 'grey', 'fa-bug');

	// projects
	$t_project_search_hdr =
		'<div id="projects-list">' . 
		'<input class="search form-control" placeholder="search" />' . 
		'<ul class="list dropdown-green no-margin">';

	$t_project_search_ftr = '</ul></div>';

	$t_menu = array();
	$t_menu[] = array('label' => 'bare', 'data' => $t_project_search_hdr);

	$t_project_ids = user_get_accessible_projects(auth_get_current_user_id());
	project_cache_array_rows( $t_project_ids );

	foreach( $t_project_ids as $t_id ) {
		$t_link = helper_mantis_url( 'set_project.php' ) . '?project_id=' . $t_id;
		$t_name = string_attribute( project_get_field( $t_id, 'name' ) );

		$t_menu[] = array('label' => $t_name, 'data' => array('link' => $t_link, 'class' => 'project-link'));
	}

	$t_menu[] = array('label' => 'bare', 'data' => $t_project_search_ftr);
	$t_menu[] = array('label' => 'divider', 'data' => '');
	$t_menu[] = array('label' => 'Any Project', 'data' => array('link' => helper_mantis_url( 'set_project.php' ) . '?project_id=' . ALL_PROJECTS, 'icon' => ''));

	dropdown_menu('Projects', $t_menu, 'grey', 'fa-book');

	// reports
	$t_menu = array(
		array('label' => 'Work Log', 'data' => array('link' => 'worklog_summary_page.php', 'icon' => 'fa-clock-o')),
		array('label' => 'Roadmap', 'data' => array('link' => 'roadmap_page.php', 'icon' => 'fa-road')),
		array('label' => 'Change Log', 'data' => array('link' => 'changelog_page.php', 'icon' => 'fa-retweet')),
		array('label' => 'divider', 'data' => ''),
		array('label' => 'Summary', 'data' => array('link' => 'summary_page.php', 'icon' => 'fa-bar-chart-o'))
	);

	dropdown_menu('Reports', $t_menu, 'grey', 'fa-heartbeat');

	// settings
	$t_menu = array(
		array('label' => 'My Account', 'data' => array('link' => 'account_page.php', 'icon' => 'fa-user')),
		array('label' => 'Manage', 'data' => array('link' => 'manage_overview_page.php', 'icon' => 'fa-gears')),
		array('label' => 'divider', 'data' => ''),
		array('label' => 'Logout', 'data' => array('link' => 'logout_page.php', 'icon' => 'fa-sign-out')),
	);

	dropdown_menu('Settings', $t_menu, 'grey', 'fa-sliders');

	/* issue search */
	echo '<div class="input-xs">';
	echo '<form method="post" action="' . helper_mantis_url( 'jump_to_bug.php' ) . '">';
	echo '<input type="text" name="bug_id" class="input-xs" size="7" placeholder="Issue ID">';
	echo '</form>';
	echo '</div>';

	echo '<div class="pull-right" style="padding-top:7px;padding-right:10px;">';

	/* recently visited */
	if( last_visited_enabled() ) {
		$t_issues = '';
		$t_comma = '';
		$t_ids = last_visited_get_array();

		if( count( $t_ids ) > 0 ) {
			foreach( $t_ids as $t_id ) {
				$t_bug_string = string_get_bug_view_link( $t_id, true, false, 'color:white;font-size:x-small' );

				if($t_bug_string == '')
					continue;

				$t_issues .= $t_comma . ' ' . $t_bug_string;

				if($t_comma == '')
					$t_comma = ',';
			}
		}

		echo format_label('Recently Visited:') . format_hspace('5px') . $t_issues . format_hspace('5px');
	}

	/* user info */
	$t_user = string_html_specialchars(current_user_get_field( 'username' ));

	echo format_label('User:') . format_hspace('5px') . $t_user . format_hspace('5px');

	/* project info */
	$t_current_project_id = helper_get_current_project();
	$t_project = string_attribute(($t_current_project_id == ALL_PROJECTS ? lang_get( 'all_projects' ) : project_get_field( $t_current_project_id, 'name' )));

	echo format_label('Project:') . format_hspace('5px') . $t_project;

	echo '</div>';
	echo '</div>';
}

function layout_statusbar(){
	echo '<div class="statusbar">';
	echo '<input type="text" id="statusbar-err" class="statusbar statusbar-err" readonly/>';
	echo '<input type="text" id="statusbar-ok" class="statusbar statusbar-ok" readonly/>';
	echo '<input type="text" id="statusbar-warn" class="statusbar statusbar-warn" readonly/>';
	echo '</div>';
}

/**
 * Render opening markup for main container
 * @return null
 */
function layout_main_container_begin() {
	echo '<div class="main-container" id="main-container">', "\n";
}

/**
 * Render closing markup for main container
 * @return null
 */
function layout_main_container_end() {
	echo '</div>' , "\n";
}

/**
 * Render opening markup for main content
 * @return null
 */
function layout_main_content_begin() {
	echo '<div class="main-content">' , "\n";
}

/**
 * Render closing markup for main content
 * @return null
 */
function layout_main_content_end() {
	echo '</div>' , "\n";
}

/**
 * Render opening markup for main page content
 * @return null
 */
function layout_page_content_begin() {
	echo '  <div class="page-content">' , "\n";
}

/**
 * Render closing markup for main page content
 * @return null
 */
function layout_page_content_end() {
	echo '</div>' , "\n";
}

/**
 * Print the page footer information
 * @return void
 */
function layout_footer() {
	global $g_queries_array, $g_request_time;

	# If a user is logged in, update their last visit time.
	# We do this at the end of the page so that:
	#  1) we can display the user's last visit time on a page before updating it
	#  2) we don't invalidate the user cache immediately after fetching it
	#  3) don't do this on the password verification or update page, as it causes the
	#    verification comparison to fail
	if( auth_is_user_authenticated() && !current_user_is_anonymous() && !( is_page_name( 'verify.php' ) || is_page_name( 'account_update.php' ) ) ) {
		$t_user_id = auth_get_current_user_id();
		user_update_last_visit( $t_user_id );
	}

	layout_footer_begin();

	# Show MantisBT version and copyright statement
	$t_version_suffix = '';
	$t_copyright_years = ' 2000 - ' . date( 'Y' );
	if( config_get( 'show_version' ) == ON ) {
		$t_version_suffix = ' ' . htmlentities( MANTIS_VERSION . config_get_global( 'version_suffix' ) );
	}
	echo '<div class="col-md-6-left col-xs-12 no-padding">' . "\n";
	echo '<address>' . "\n";
	echo '<strong>Powered by <a href="https://www.mantisbt.org" title="bug tracking software">MantisBT ' . $t_version_suffix . '</a></strong> <br>' . "\n";
	echo "<small>Copyright &copy;$t_copyright_years MantisBT Team</small>" . '<br>';

	# Show optional user-specified custom copyright statement
	$t_copyright_statement = config_get( 'copyright_statement' );
	if( $t_copyright_statement ) {
		echo '<small>' . $t_copyright_statement . '</small>' . "\n";
	}

	# Show contact information
	if( !is_page_name( 'login_page' ) ) {
		$t_webmaster_contact_information = sprintf( lang_get( 'webmaster_contact_information' ), string_html_specialchars( config_get( 'webmaster_email' ) ) );
		echo '<small>' . $t_webmaster_contact_information . '</small>' . '<br>' . "\n";
	}

	echo '</address>' . "\n";
	echo '</div>' . "\n";


	# We don't have a button anymore, so for now we will only show the resized
	# version of the logo when not on login page.
	if( !is_page_name( 'login_page' ) ) {
		echo '<div class="col-md-6-left col-xs-12">' . "\n";
		echo '<div class="pull-right" id="powered-by-mantisbt-logo">' . "\n";
		$t_mantisbt_logo_url = helper_mantis_url( 'images/mantis_logo.png' );
		echo '<a href="https://www.mantisbt.org" '.
			'title="Mantis Bug Tracker: a free and open source web based bug tracking system.">' .
			'<img src="' . $t_mantisbt_logo_url . '" width="102" height="35" ' .
			'alt="Powered by Mantis Bug Tracker: a free and open source web based bug tracking system." />' .
			'</a>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
	}

	event_signal( 'EVENT_LAYOUT_PAGE_FOOTER' );

	if( config_get( 'show_timer' ) || config_get( 'show_memory_usage' ) || config_get( 'show_queries_count' ) ) {
		echo '<div class="col-xs-12 no-padding grey">' . "\n";
		echo '<address class="no-margin pull-right">' . "\n";
	}


	# Print the page execution time
	if( config_get( 'show_timer' ) ) {
		$t_page_execution_time = sprintf( lang_get( 'page_execution_time' ), number_format( microtime( true ) - $g_request_time, 4 ) );
		echo '<small><i class="fa fa-clock-o"></i> ' . $t_page_execution_time . '</small>&#160;&#160;&#160;&#160;' . "\n";
	}

	# Print the page memory usage
	if( config_get( 'show_memory_usage' ) ) {
		$t_page_memory_usage = sprintf( lang_get( 'memory_usage_in_kb' ), number_format( memory_get_peak_usage() / 1024 ) );
		echo '<small><i class="fa fa-bolt"></i> ' . $t_page_memory_usage . '</small>&#160;&#160;&#160;&#160;' . "\n";
	}

	# Determine number of unique queries executed
	if( config_get( 'show_queries_count' ) ) {
		$t_total_queries_count = count( $g_queries_array );
		$t_unique_queries_count = 0;
		$t_total_query_execution_time = 0;
		$t_unique_queries = array();
		for ( $i = 0; $i < $t_total_queries_count; $i++ ) {
			if( !in_array( $g_queries_array[$i][0], $t_unique_queries ) ) {
				$t_unique_queries_count++;
				$g_queries_array[$i][3] = false;
				array_push( $t_unique_queries, $g_queries_array[$i][0] );
			} else {
				$g_queries_array[$i][3] = true;
			}
			$t_total_query_execution_time += $g_queries_array[$i][1];
		}

		$t_total_queries_executed = sprintf( lang_get( 'total_queries_executed' ), $t_total_queries_count );
		echo '<small><i class="fa fa-database"></i> ' . $t_total_queries_executed . '</small>&#160;&#160;&#160;&#160;' . "\n";
		if( config_get_global( 'db_log_queries' ) ) {
			$t_unique_queries_executed = sprintf( lang_get( 'unique_queries_executed' ), $t_unique_queries_count );
			echo '<small><i class="fa fa-database"></i> ' . $t_unique_queries_executed . '</small>&#160;&#160;&#160;&#160;' . "\n";
		}
		$t_total_query_time = sprintf( lang_get( 'total_query_execution_time' ), $t_total_query_execution_time );
		echo '<small><i class="fa fa-clock-o"></i> ' . $t_total_query_time . '</small>&#160;&#160;&#160;&#160;' . "\n";
	}

	if( config_get( 'show_timer' ) || config_get( 'show_memory_usage' ) || config_get( 'show_queries_count' ) ) {
		echo '</address>' . "\n";
		echo '</div>' . "\n";
	}

	# Print table of log events
	log_print_to_page();

	layout_footer_end();
}

/**
 * Render opening markup for footer section
 * @return null
 */
function layout_footer_begin() {
	echo '<div class="clearfix"></div>' . "\n";
	echo '<div class="space-20"></div>' . "\n";
	echo '<div class="footer noprint">' . "\n";
	echo '<div class="footer-inner">' . "\n";
	echo '<div class="footer-content">' . "\n";
}

/**
 * Render closing markup for footer section
 * @return null
 */
function layout_footer_end() {
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
}

/**
 * Render scroll up link to go at the bottom of the page
 * @return null
 */
function layout_scroll_up_button() {
	echo '<a class="btn-scroll-up btn btn-xs btn-inverse display" id="btn-scroll-up" href="#">' . "\n";
	echo '<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>' . "\n";
	echo '</a>' . "\n";
}
