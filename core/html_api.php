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
 * HTML API
 *
 * These functions control the HTML output of each page.
 *
 *
 * @package CoreAPI
 * @subpackage HTMLAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses php_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses rss_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses layout_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'news_api.php' );
require_api( 'php_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'rss_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'layout_api.php' );

$g_rss_feed_url = null;

$g_robots_meta = '';

# flag for error handler to skip header menus
$g_error_send_page_header = true;

$g_stylesheets_included = array();
$g_scripts_included = array();

/**
 * Sets the url for the rss link associated with the current page.
 * null: means no feed (default).
 * @param string $p_rss_feed_url RSS feed URL.
 * @return void
 */
function html_set_rss_link( $p_rss_feed_url ) {
	if( OFF != config_get( 'rss_enabled' ) ) {
		global $g_rss_feed_url;
		$g_rss_feed_url = $p_rss_feed_url;
	}
}

/**
 * This method marks the page as not for indexing by search engines
 * @return void
 */
function html_robots_noindex() {
	global $g_robots_meta;
	$g_robots_meta = 'noindex,follow';
}

/**
 * Prints the link that allows auto-detection of the associated feed.
 * @return void
 */
function html_rss_link() {
	global $g_rss_feed_url;

	if( $g_rss_feed_url !== null ) {
		echo '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . string_attribute( $g_rss_feed_url ) . '" />' . "\n";
	}
}

/**
 * Prints a <script> tag to include a JavaScript file.
 * @param string $p_filename Name of JavaScript file (with extension) to include.
 * @return void
 */
function html_javascript_link( $p_filename ) {
	echo "\t", '<script type="text/javascript" src="', helper_mantis_url( 'js/' . $p_filename ), '"></script>', "\n";
}

/**
 * Prints a <script> tag to include a JavaScript file.
 * @param string $p_url fully qualified domain name for the cdn js file
 * @param string $p_hash resource hash to perform subresource integrity check
 * @return void
 */
function html_javascript_cdn_link( $p_url, $p_hash = '' ) {
	$t_integrity = '';
	if( $p_hash !== '' ) {
		$t_integrity = 'integrity="' . $p_hash . '" ';
	}
	echo "\t", '<script type="text/javascript" src="', $p_url, '" ', $t_integrity, 'crossorigin="anonymous"></script>', "\n";
}

/**
 * Print the document type and the opening <html> tag
 * @return void
 */
function html_begin() {
	echo '<!DOCTYPE html>', "\n";
	echo '<html>', "\n";
}

/**
 * Begin the <head> section
 * @return void
 */
function html_head_begin() {
	echo '<head>', "\n";
}

/**
 * Print the content-type
 * @return void
 */
function html_content_type() {
	echo "\t", '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />', "\n";
}

/**
 * Print the window title
 * @param string $p_page_title Window title.
 * @return void
 */
function html_title( $p_page_title = null ) {
	$t_page_title = string_html_specialchars( $p_page_title );
	$t_title = string_html_specialchars( config_get( 'window_title' ) );
	echo "\t", '<title>';
	if( empty( $t_page_title ) ) {
		echo $t_title;
	} else {
		if( empty( $t_title ) ) {
			echo $t_page_title;
		} else {
			echo $t_page_title . ' - ' . $t_title;
		}
	}
	echo '</title>', "\n";
}

/**
 * Require a CSS file to be in html page headers
 * @param string $p_stylesheet_path Path to CSS style sheet.
 * @return void
 */
function require_css( $p_stylesheet_path ) {
	global $g_stylesheets_included;
	$g_stylesheets_included[$p_stylesheet_path] = $p_stylesheet_path;
}

/**
 * Print the link to include the CSS file
 * @return void
 */
function html_css() {
	global $g_stylesheets_included;
	html_css_link( config_get( 'css_include_file' ) );
	html_css_link( 'common_config.php' );
	# Add right-to-left css if needed
	if( lang_get( 'directionality' ) == 'rtl' ) {
		html_css_link( config_get( 'css_rtl_include_file' ) );
	}
	foreach( $g_stylesheets_included as $t_stylesheet_path ) {
		html_css_link( $t_stylesheet_path );
	}

	# dropzone css
	if ( config_get_global( 'cdn_enabled' ) == ON ) {
		html_css_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/' . DROPZONE_VERSION . '/min/dropzone.min.css' );
	} else {
		html_css_link( 'dropzone-' . DROPZONE_VERSION . '.min.css' );
	}
}

/**
 * Prints a CSS link
 * @param string $p_filename Filename.
 * @return void
 */
function html_css_link( $p_filename ) {
	# If no path is specified, look for CSS files in default directory
	if( $p_filename == basename( $p_filename ) ) {
		$p_filename = 'css/' . $p_filename;
	}
	echo "\t", '<link rel="stylesheet" type="text/css" href="', string_sanitize_url( helper_mantis_url( $p_filename ), true ), '" />', "\n";
}

/**
 * Prints a CSS link for CDN
 * @param string $p_url fully qualified domain name to the js file name
 * @return void
 */
function html_css_cdn_link( $p_url ) {
	echo "\t", '<link rel="stylesheet" type="text/css" href="', $p_url, '" crossorigin="anonymous" />', "\n";
}

/**
 * Print an HTML meta tag to redirect to another page
 * This function is optional and may be called by pages that need a redirect.
 * $p_time is the number of seconds to wait before redirecting.
 * If we have handled any errors on this page return false and don't redirect.
 *
 * @param string  $p_url      The page to redirect: has to be a relative path.
 * @param integer $p_time     Seconds to wait for before redirecting.
 * @param boolean $p_sanitize Apply string_sanitize_url to passed URL.
 * @return boolean
 */
function html_meta_redirect( $p_url, $p_time = null, $p_sanitize = true ) {
	if( ON == config_get_global( 'stop_on_errors' ) && error_handled() ) {
		return false;
	}

	if( null === $p_time ) {
		$p_time = current_user_get_pref( 'redirect_delay' );
	}

	$t_url = config_get( 'path' );
	if( $p_sanitize ) {
		$t_url .= string_sanitize_url( $p_url );
	} else {
		$t_url .= $p_url;
	}

	$t_url = htmlspecialchars( $t_url );

	echo "\t" . '<meta http-equiv="Refresh" content="' . $p_time . '; URL=' . $t_url . '" />' . "\n";

	return true;
}

/**
 * Require a javascript file to be in html page headers
 * @param string $p_script_path Path to javascript file.
 * @return void
 */
function require_js( $p_script_path ) {
	global $g_scripts_included;
	$g_scripts_included[$p_script_path] = $p_script_path;
}

/**
 * Javascript...
 * @return void
 */
function html_head_javascript() {
	global $g_scripts_included;
	echo "\t" . '<script type="text/javascript" src="' . helper_mantis_url( 'javascript_config.php' ) . '"></script>' . "\n";
	echo "\t" . '<script type="text/javascript" src="' . helper_mantis_url( 'javascript_translations.php' ) . '"></script>' . "\n";

	if ( config_get_global( 'cdn_enabled' ) == ON ) {
		# JQuery
		html_javascript_cdn_link( 'https://ajax.googleapis.com/ajax/libs/jquery/' . JQUERY_VERSION . '/jquery.min.js', JQUERY_HASH );

		# Dropzone
		html_javascript_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/' . DROPZONE_VERSION . '/min/dropzone.min.js', DROPZONE_HASH );
	} else {
		# JQuery
		html_javascript_link( 'jquery-' . JQUERY_VERSION . '.min.js' );

		# Dropzone
		html_javascript_link( 'dropzone-' . DROPZONE_VERSION . '.min.js' );
	}

	html_javascript_link( 'common.js' );
	foreach ( $g_scripts_included as $t_script_path ) {
		html_javascript_link( $t_script_path );
	}
}

/**
 * End the <head> section
 * @return void
 */
function html_head_end() {
	echo '</head>', "\n";
}

/**
 * Prints the logo with an URL link.
 * @param string $p_logo Path to the logo image. If not specified, will get it
 *                       from $g_logo_image
 * @return void
 */
function html_print_logo( $p_logo = null ) {
	if( !$p_logo ) {
		$p_logo = config_get( 'logo_image' );
	}

	if( !is_blank( $p_logo ) ) {
		$t_logo_url = config_get( 'logo_url' );
		$t_show_url = !is_blank( $t_logo_url );

		if( $t_show_url ) {
			echo '<a id="logo-link" href="', config_get( 'logo_url' ), '">';
		}
		$t_alternate_text = string_html_specialchars( config_get( 'window_title' ) );
		echo '<img id="logo-image" alt="', $t_alternate_text, '" style="max-height: 80px;" src="' . helper_mantis_url( $p_logo ) . '" />';
		if( $t_show_url ) {
			echo '</a>';
		}
	}
}



/**
 * Print a user-defined banner at the top of the page if there is one.
 * @return void
 */
function html_top_banner() {
	$t_page = config_get( 'top_include_page' );
	$t_logo_image = config_get( 'logo_image' );

	if( !is_blank( $t_page ) && file_exists( $t_page ) && !is_dir( $t_page ) ) {
		include( $t_page );
	} else if( !is_blank( $t_logo_image ) ) {
		echo '<div id="banner">';
		html_print_logo( $t_logo_image );
		echo '</div>';
	}

	event_signal( 'EVENT_LAYOUT_PAGE_HEADER' );
}

/**
 * Outputs a message to confirm an operation's result.
 * @param array   $p_buttons     Array of (URL, label) pairs used to generate
 *                               the buttons; if label is null or unspecified,
 *                               the default 'proceed' text will be displayed.
 * @param string  $p_message     Message to display to the user. If none is
 *                               provided, a default message will be printed
 * @param integer $p_type        One of the constants CONFIRMATION_TYPE_SUCCESS,
 *                               CONFIRMATION_TYPE_WARNING, CONFIRMATION_TYPE_FAILURE
 * @return void
 */
function html_operation_confirmation( array $p_buttons, $p_message = '', $p_type = CONFIRMATION_TYPE_SUCCESS ) {
	switch( $p_type ) {
		case CONFIRMATION_TYPE_FAILURE:
			$t_alert_css = 'alert-danger';
			$t_message = 'operation_failed';
			break;
		case CONFIRMATION_TYPE_WARNING:
			$t_alert_css = 'alert-warning';
			$t_message = 'operation_warnings';
			break;
		case CONFIRMATION_TYPE_SUCCESS:
		default:
			$t_alert_css = 'alert-success';
			$t_message = 'operation_successful';
			break;
	}

	echo '<div class="container-fluid">';
	echo '<div class="col-md-6-left col-xs-12">';
	echo '<div class="space-0"></div>';
	echo '<div class="alert ' . $t_alert_css . ' center">';

	# Print message
	if( is_blank( $p_message ) ) {
		$t_message = lang_get( $t_message );
	} else {
		$t_message = $p_message;
	}
	echo '<p class="bold bigger-110">' . $t_message  . '</p><br />';

	# Print buttons
	echo '<div class="btn-group">';
	foreach( $p_buttons as $t_button ) {
		if($t_button[0] == '')
			continue;

		$t_url = string_sanitize_url( $t_button[0] );
		$t_label = isset( $t_button[1] ) ? $t_button[1] : lang_get( 'proceed' );

		print_link_button( $t_url, $t_label );
	}
	echo '</div>';

	echo '</div></div></div>', PHP_EOL;
}

/**
 * Outputs an operation successful message with a single redirect link.
 * @param string $p_redirect_url The url to redirect to.
 * @param string $p_message      Message to display to the user.
 * @return void
 */
function html_operation_successful( $p_redirect_url, $p_message = '' ) {
	html_operation_confirmation( array( array( $p_redirect_url ) ), $p_message );
}

/**
 * Outputs a warning message with a single redirect link.
 * @param string $p_redirect_url The url to redirect to.
 * @param string $p_message      Message to display to the user.
 * @return void
 */
function html_operation_warning( $p_redirect_url, $p_message = '' ) {
	html_operation_confirmation(
		array( array( $p_redirect_url ) ),
		$p_message,
		CONFIRMATION_TYPE_WARNING
	);
}

/**
 * Outputs an error message with a single redirect link.
 * @param string $p_redirect_url The url to redirect to.
 * @param string $p_message      Message to display to the user.
 * @return void
 */
function html_operation_failure( $p_redirect_url, $p_message = '' ) {
	html_operation_confirmation(
		array( array( $p_redirect_url ) ),
		$p_message,
		CONFIRMATION_TYPE_FAILURE
	);
}

/**
 * End the <body> section
 * @return void
 */
function html_body_end() {
	# Should code need to be added to this function in the future, it should be
	# placed *above* this event, which needs to be the last thing to occur
	# before the actual body ends (see #20084)
	event_signal( 'EVENT_LAYOUT_BODY_END' );

	echo '</body>', "\n";
}

/**
 * Print the closing <html> tag
 * @return void
 */
function html_end() {
	echo '</html>', "\n";

	if( function_exists( 'fastcgi_finish_request' ) ) {
		fastcgi_finish_request();
	}
}

/**
 * Print the menu bar with a list of projects to which the user has access
 * @return void
 */
function print_project_menu_bar() {
	$t_project_ids = current_user_get_accessible_projects();
	$t_current_project_id = helper_get_current_project();

	echo '<div class="col-md-6-left col-xs-12">' . "\n";
	echo '<div class="btn-group">' . "\n";

	$t_active = ALL_PROJECTS == $t_current_project_id ? 'active' : '';
	echo '<a class="btn btn-xs btn-white btn-info ' . $t_active .
		'" href="' . helper_mantis_url( 'set_project.php?project_id=' . ALL_PROJECTS ) . '">', lang_get( 'all_projects' ), '</a>' . "\n";

	foreach( $t_project_ids as $t_id ) {
		$t_active = $t_id == $t_current_project_id ? 'active' : '';
		echo '<a class="btn btn-xs btn-white btn-info ' . $t_active .
			'" href="' . helper_mantis_url( 'set_project.php?project_id=' . $t_id ) . '">', string_html_specialchars( project_get_field( $t_id, 'name' ) ), '</a>' . "\n";
		print_subproject_menu_bar( $t_current_project_id, $t_id, $t_id . ';' );
	}

	echo '</div>' . "\n";
	echo '<div class="space-4"></div>' . "\n";
	echo '</div>' . "\n";
}

/**
 * Print the menu bar with a list of projects to which the user has access
 * @todo check parents param - set_project.php?project_id=' . $p_parents . $t_subproject
 * @param integer $p_current_project_id Selected project id.
 * @param integer $p_parent_project_id Parent project id.
 * @param string  $p_parents    Parent project identifiers.
 * @return void
 */
function print_subproject_menu_bar( $p_current_project_id, $p_parent_project_id, $p_parents = '' ) {
	$t_subprojects = current_user_get_accessible_subprojects( $p_parent_project_id );

	foreach( $t_subprojects as $t_subproject_id ) {
		$t_active = $p_current_project_id == $t_subproject_id ? 'active' : '';
		echo '<a class="btn btn-xs btn-white btn-default ' . $t_active .
			'" href="' . helper_mantis_url( 'set_project.php?project_id=' . $p_parents . $t_subproject_id ) .
			'"><i class="ace-icon fa fa-angle-double-right"></i> ' .
			string_html_specialchars( project_get_field( $t_subproject_id, 'name' ) ) . '</a>';

		# Render this subproject's subprojects ... passing current project id to highlight selected project
		print_subproject_menu_bar( $p_current_project_id, $t_subproject_id, $p_parents . $t_subproject_id . ';' );
	}
}

/**
 * Print the menu for the graph summary section
 * @return void
 */
function print_summary_submenu() {
	# Plugin / Event added options
	$t_event_menu_options = event_signal( 'EVENT_SUBMENU_SUMMARY' );
	$t_menu_options = array();
	foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
		foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
			if( is_array( $t_callback_menu_options ) ) {
				$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
			} else {
				if( !is_null( $t_callback_menu_options ) ) {
					$t_menu_options[] = $t_callback_menu_options;
				}
			}
		}
	}

	if( count($t_menu_options) > 0 ) {
		echo '<div class="space-10"></div>';
		echo '<div class="center">';
		echo '<div class="btn-toolbar inline">';
		echo '<div class="btn-group">';

		# Plugins menu items - these are cooked links
		foreach ($t_menu_options as $t_menu_item) {
			echo $t_menu_item;
		}
		echo '</div></div></div>';
	}
}

/**
 * Print the menu for the manage section
 *
 * @param string $p_page Specifies the current page name so it's link can be disabled.
 * @return void
 */
function print_manage_menu( $p_page = '' ) {
	$t_pages = array();

	# Plugin / Event added options
	$t_event_menu_options = event_signal( 'EVENT_MENU_MANAGE' );
	$t_menu_options = array();
	foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
		foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
			if( is_array( $t_callback_menu_options ) ) {
				$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
			} else {
				if( !is_null( $t_callback_menu_options ) ) {
					$t_menu_options[] = $t_callback_menu_options;
				}
			}
		}
	}

	echo '<ul class="nav nav-tabs padding-18">' . "\n";
	foreach( $t_pages AS $t_page ) {
		$t_active =  $t_page['url'] == $p_page ? 'active' : '';
		echo '<li class="' . $t_active .  '">' . "\n";
		if( $t_page['label'] == '' ) {
			echo '<a href="'. lang_get_defaulted( $t_page['url'] ) .'"><i class="blue ace-icon fa fa-info-circle"></i> </a>';
		} else {
			echo '<a href="'. helper_mantis_url( $t_page['url'] ) .'">' . lang_get_defaulted( $t_page['label'] ) . '</a>';
		}
		echo '</li>' . "\n";
	}

	# Plugins menu items - these are cooked links
	foreach( $t_menu_options as $t_menu_item ) {
		echo '<li>', $t_menu_item, '</li>';
	}

	echo '</ul>' . "\n";
}

/**
 * Print the menu for the documentation section
 * @param string $p_page Specifies the current page name so it's link can be disabled.
 * @return void
 */
function print_doc_menu( $p_page = '' ) {
	# User Documentation
	$t_doc_url = config_get( 'manual_url' );
	if( is_null( parse_url( $t_doc_url, PHP_URL_SCHEME ) ) ) {
		# URL has no scheme, so it is relative to MantisBT root
		if( is_blank( $t_doc_url ) ||
			!file_exists( config_get_global( 'absolute_path' ) . $t_doc_url )
		) {
			# Local documentation not available, use online docs
			$t_doc_url = 'http://www.mantisbt.org/documentation.php';
		} else {
			$t_doc_url = helper_mantis_url( $t_doc_url );
		}
	}

	$t_pages[$t_doc_url] = array(
		'url'   => $t_doc_url,
		'label' => 'user_documentation'
	);

	# Project Documentation
	$t_pages['proj_doc_page.php'] = array(
		'url'   => helper_mantis_url( 'proj_doc_page.php' ),
		'label' => 'project_documentation'
	);

	# Add File
	if( file_allow_project_upload() ) {
		$t_pages['proj_doc_add_page.php'] = array(
			'url'   => helper_mantis_url( 'proj_doc_add_page.php' ),
			'label' => 'add_file'
		);
	}

	echo '<ul class="nav nav-tabs padding-18">' . "\n";

	foreach ( $t_pages as $key => $t_page ) {
		$t_active =  $key == $p_page ? 'active' : '';
		echo '<li class="' . $t_active . '">' . "\n";
		echo '<a href="' . $t_page['url'] . '">' . "\n";
		echo lang_get($t_page['label']);

		echo '</a>' . "\n";
		echo '</li>' . "\n";
	}

	echo '</ul>' . "\n";
}

/**
 * Print the menu for the summary section
 * @param string $p_page Specifies the current page name so it's link can be disabled.
 * @return void
 */
function print_summary_menu( $p_page = '' ) {
	# Plugin / Event added options
	$t_event_menu_options = event_signal( 'EVENT_MENU_SUMMARY' );
	$t_menu_options = array();
	foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
		foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
			if( is_array( $t_callback_menu_options ) ) {
				$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
			} else {
				if( !is_null( $t_callback_menu_options ) ) {
					$t_menu_options[] = $t_callback_menu_options;
				}
			}
		}
	}

	$t_pages['summary_page.php'] = array( 'url'=>'summary_page.php', 'label'=>'summary_link' );

	echo '<ul class="nav nav-tabs padding-18">' . "\n";

	foreach ( $t_pages as $t_page ) {
		$t_active =  $t_page['url'] == $p_page ? 'active' : '';
		echo '<li class="' . $t_active . '">' . "\n";
		echo '<a href="'. helper_mantis_url( $t_page['url'] ) .'">' . "\n";
		echo lang_get( $t_page['label'] );
		echo '</a>' . "\n";
		echo '</li>' . "\n";
	}

	# Plugins menu items - these are cooked links
	foreach ( $t_menu_options as $t_menu_item ) {
		echo '<li>' . $t_menu_item . '</li>';
	}

	echo '</ul>' . "\n";
}

/**
 * Print the admin tab bar
 * @param string $p_page Specifies the current page name so it set to active.
 * @return void
 */
function print_admin_menu_bar( $p_page ) {
	global $g_upgrade;
	echo '<div class="space-10"></div>';
	echo '<ul class="nav nav-tabs padding-18">' . "\n";

	$t_active = 'index.php' == $p_page ? 'active' : '';
	echo '<li class="green ' . $t_active . '">' . "\n";
	echo '<a href="index.php"><i class="blue ace-icon fa fa-info-circle"></i> </a>';
	echo '</li>' . "\n";

	if( count( $g_upgrade ) - 1 != config_get( 'database_version' ) ) {
		echo '<li class="bold">' . "\n";
		echo '<a class="green" href="install.php">Upgrade your installation</a>';
		echo '</li>' . "\n";
	}

	$t_active = 'system_utils.php' == $p_page ? 'active' : '';
	echo '<li class="' . $t_active . '">' . "\n";
	echo '<a href="system_utils.php">System Utilities</a>' . "\n";
	echo '</li>' . "\n";

	$t_active = 'test_langs.php' == $p_page ? 'active' : '';
	echo '<li class="' . $t_active . '">' . "\n";
	echo '<a href="test_langs.php">Test Langs</a>' . "\n";
	echo '</li>' . "\n";

	$t_active = 'email_queue.php' == $p_page ? 'active' : '';
	echo '<li class="' . $t_active . '">' . "\n";
	echo '<a href="email_queue.php">Email Queue</a>' . "\n";
	echo '</li>' . "\n";

	echo '</ul>' . "\n";
}

/**
 * Print an html button inside a form
 * @param string $p_action      Form Action.
 * @param string $p_button_text Button Text.
 * @param array  $p_fields      An array of hidden fields to include on the form.
 * @param string $p_method      Form submit method - default post.
 * @return void
 */
function html_button( $p_action, $p_button_text, array $p_fields = array(), $p_method = 'post' ) {
	$t_form_name = explode( '.php', $p_action, 2 );
	$p_action = urlencode( $p_action );
	$p_button_text = string_attribute( $p_button_text );

	if( strtolower( $p_method ) == 'get' ) {
		$t_method = 'get';
	} else {
		$t_method = 'post';
	}

	echo '<form method="' . $t_method . '" action="' . $p_action . '" class="form-inline">' . "\n";
	echo "\t" . '<fieldset>';
	# Add a CSRF token only when the form is being sent via the POST method
	if( $t_method == 'post' ) {
		echo form_security_field( $t_form_name[0] );
	}

	foreach( $p_fields as $t_key => $t_val ) {
		$t_key = string_attribute( $t_key );
		$t_val = string_attribute( $t_val );

		echo "\t\t" . '<input type="hidden" name="' . $t_key . '" value="' . $t_val . '" />' . "\n";
	}

	echo "\t\t" . '<input type="submit" class="btn btn-primary btn-xs btn-white btn-round" value="' . $p_button_text . '" />' . "\n";
	echo "\t" . '</fieldset>';
	echo '</form>' . "\n";
}

/**
 * Print a button to create a wiki page
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_button_wiki( $p_bug_id ) {
	if( config_get_global( 'wiki_enable' ) == ON ) {
		if( access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
			html_button( 'wiki.php', lang_get_defaulted( 'Wiki' ), array( 'id' => $p_bug_id, 'type' => 'issue' ), 'get' );
		}
	}
}

/**
 * get the css class name for the given status, user and project
 * @param integer $p_status  An enumeration value.
 * @param integer $p_user    A valid user identifier.
 * @param integer $p_project A valid project identifier.
 * @return string
 *
 * @todo This does not work properly when displaying issues from a project other
 * than then current one, if the other project has custom status or colors.
 * This is due to the dynamic css for color coding (css/status_config.php).
 * Build CSS including project or even user-specific colors ?
 */
function html_get_status_css_class( $p_status, $p_user = null, $p_project = null ) {
	$t_status_enum = config_get( 'status_enum_string', null, $p_user, $p_project );
	if( MantisEnum::hasValue( $t_status_enum, $p_status ) ) {
		return 'status-' . $p_status . '-color';
	} else {
		return '';
	}
}

/**
 * Class that provides managed generation of an HTML table content, consisting of <tr> and <td> elements
 * which are arranged sequentially on a grid.
 * Items consist of "header" and "content", which are rendered to separate table cells.
 * An option is provided to arrange the header and content in vertical or horizontal orientation.
 * Vertical orientation places header on top of content, while horizontal orientation places the header
 * to the left of content cell.
 * Each item can have a different colspan, which is used to arrange the items efficiently. When the
 * arrangement is made, an item with higher colspan than current free space may be placed in next row,
 * but still fill the current row with next items if they fit. This may cause a variation in expected
 * order, but allows for a more compact fill for rows.
 */
class TableGridLayout {
	const ORIENTATION_VERTICAL = 0;
	const ORIENTATION_HORIZONTAL = 1;

	protected $cols;
	private $_max_colspan;

	public $items = array();
	public $item_orientation;

	/**
	 * Set this variable to add a class attribute for each <tr>
	 * @var string
	 */
	public $tr_class = null;

	/**
	 * Constructor.
	 * $p_orientation may be one of this class constants:
	 * ORIENTATION_VERTICAL, ORIENTATION_HORIZONTAL
	 * @param integer $p_cols	Number of columns for the table
	 * @param integer $p_orientation	Orientation for header and content cells
	 */
	public function __construct( $p_cols, $p_orientation = null ) {
		# sanitize values
		switch( $p_orientation ) {
			case self::ORIENTATION_HORIZONTAL:
				if( $p_cols < 2 ) {
					$p_cols = 2;
				}
				$this->_max_colspan = $p_cols-1;
				break;
			case self::ORIENTATION_VERTICAL:
			default:
				$p_orientation = self::ORIENTATION_VERTICAL;
				if( $p_cols < 1 ) {
					$p_cols = 1;
				}
				$this->_max_colspan = $p_cols;
		}

		$this->cols = $p_cols;
		$this->item_orientation = $p_orientation;
	}

	/**
	 * Adds a item to the collection
	 * @param TableFieldsItem $p_item An item
	 */
	public function add_item( TableFieldsItem $p_item ) {
		if( $p_item->colspan > $this->_max_colspan ) {
			$p_item->colspan = $this->_max_colspan;
		}
		$this->items[] = $p_item;
	}

	/**
	 * Prints the HTMl for the generated table cells, for all items contained
	 */
	public function render() {
		$t_rows_items = array();
		$t_rows_freespace = array();
		$t_used_rows = 0;

		# Arrange the items in rows accounting for their actual cell space
		foreach( $this->items as $t_item ) {
			# Get the actual table colums needed to render the item
			$t_item_cols = ( $this->item_orientation == self::ORIENTATION_VERTICAL ) ? $t_item->colspan : $t_item->colspan + 1;
			# Search for a row with enough space to fit the item
			$t_found = false;
			for( $t_ix = 0; $t_ix < $t_used_rows; $t_ix++ ) {
				if( $t_rows_freespace[$t_ix] >= $t_item_cols ) {
					# Found a row with available space. Add the item here
					$t_found = true;
					$t_rows_freespace[$t_ix] -= $t_item_cols;
					$t_rows_items[$t_ix][] = $t_item;
					break;
				}
			}
			# If no suitable row was found, create new one and add the item here
			if( !$t_found ) {
				$t_rows_items[] = array( $t_item );
				$t_used_rows++;
				$t_rows_freespace[] = $this->cols - $t_item_cols;
			}
		}

		# Render the arranged items
		if( $this->tr_class ) {
			$p_tr_attr_class = ' class="' . $this->tr_class . '"';
		} else {
			$p_tr_attr_class = '';
		}
		foreach( $t_rows_items as $t_row ) {
			switch( $this->item_orientation ) {

				case self::ORIENTATION_HORIZONTAL:
					$t_cols_left = $this->cols;
					echo '<tr' . $p_tr_attr_class . '>';
					foreach( $t_row as $t_item ) {
						$this->render_td_item_header( $t_item, 1 );
						$this->render_td_item_content( $t_item, $t_item->colspan );
						$t_cols_left -= ( $t_item->colspan + 1 );
					}
					if( $t_cols_left > 0 ) {
						$this->render_td_empty($t_cols_left);
					}
					echo '</tr>';
					break;

				# default is vertical orientation
				default:
					# row for headers
					$t_cols_left = $this->cols;
					echo '<tr' . $p_tr_attr_class . '>';
					foreach( $t_row as $t_item ) {
						$this->render_td_item_header( $t_item, $t_item->colspan );
						$t_cols_left -= $t_item->colspan;
					}
					if( $t_cols_left > 0 ) {
						$this->render_td_empty_header( $t_cols_left );
					}
					echo '</tr>';
					# row for contents
					$t_cols_left = $this->cols;
					echo '<tr' . $p_tr_attr_class . '>';
					foreach( $t_row as $t_item ) {
						$this->render_td_item_content( $t_item, $t_item->colspan );
						$t_cols_left -= $t_item->colspan;
					}
					if( $t_cols_left > 0 ) {
						$this->render_td_empty($t_cols_left);
					}
					echo '</tr>';
			}
		}
	}

	/**
	 * Prints HTML code for an empty TD cell
	 * @param integer $p_colspan Colspan attribute for cell
	 */
	protected function render_td_empty( $p_colspan ) {
		echo '<td';
		if( $p_colspan > 1) {
			echo ' colspan="' . $p_colspan . '"';
		}
		echo '>';
		echo '&nbsp;';
		echo '</td>';
	}

	/**
	 * Prints HTML code for an empty TD cell, of header type
	 * @param integer $p_colspan Colspan attribute for cell
	 */
	protected function render_td_empty_header( $p_colspan ) {
		$this->render_td_empty( $p_colspan );
	}

	/**
	 * Prints HTML code for TD cell representing the Item header
	 * @abstract
	 * @param TableFieldsItem $p_item Item to display
	 * @param integer $p_colspan Colspan attribute for cell
	 */
	protected function render_td_item_header( TableFieldsItem $p_item, $p_colspan ) {
		echo '<th';
		if( $p_item->attr_class ) {
			echo 'class="' . $p_item->attr_class . '"';
		}
		if( $p_colspan > 1) {
			echo ' colspan="' . $p_colspan . '"';
		}
		if( $p_item->header_attr_id ) {
			echo ' id="' . $p_item->header_attr_id . '"';
		}
		echo '>';
		echo $p_item->header;
		echo '</th>';
	}

	/**
	 * Prints HTML code for TD cell representing the Item content
	 * @abstract
	 * @param TableFieldsItem $p_item Item to display
	 * @param integer $p_colspan Colspan attribute for cell
	 */
	protected function render_td_item_content( TableFieldsItem $p_item, $p_colspan  ) {
		echo '<td';
		if( $p_item->attr_class ) {
			echo 'class="' . $p_item->attr_class . '"';
		}
		if( $p_colspan > 1) {
			echo ' colspan="' . $p_colspan . '"';
		}
		if( $p_item->content_attr_id ) {
			echo ' id="' . $p_item->content_attr_id . '"';
		}
		echo '>';
		echo $p_item->header;
		echo '</td>';
	}
}

/**
 * Class that represent Items to use with TableGridLayout
 */
class TableFieldsItem {
	public $header;
	public $content;
	public $colspan;
	public $attr_class = null;
	public $content_attr_id = null;
	public $header_attr_id = null;

	/**
	 * Constructor
	 * @param string $p_header		HTMl to be used in header cell
	 * @param string $p_content		HTMl to be used in content cell
	 * @param integer $p_colspan	Colspan for the content cell
	 * @param string $p_class		Class to be added to the cells
	 * @param string $p_content_id	Id attribute to use for content cell
	 * @param string $p_header_id	Id attribute to use for header cell
	 */
	public function __construct( $p_header, $p_content, $p_colspan = 1, $p_class = null, $p_content_id = null, $p_header_id = null ) {
		$this->header = $p_header;
		$this->content = $p_content;
		if( $p_colspan < 1 ) {
			$p_colspan = 1;
		}
		$this->colspan = $p_colspan;
		$this->atr_class = $p_class;
		$this->content_attr_id = $p_content_id;
		$this->header_attr_id = $p_header_id;
	}
}

