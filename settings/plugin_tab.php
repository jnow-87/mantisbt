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
 * Plugin Configuration
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses plugin_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

 if(!defined('INCLUDE_PLUGIN'))
 	return;

require_once('core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('config_api.php');
require_api('form_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('plugin_api.php');
require_api('print_api.php');
require_api('string_api.php');
require_api('utility_api.php');
require_api('elements_api.php');


/**
 *	form header for plugin_update.php
 */
function form_header($p_plugin, $p_cmd){
	return '<form action="settings/plugin_update.php" method="post" class="input-hover-form input-hover-form-reload">'
		  . form_security_field('plugin_update')
		  . format_input_hidden('plugin', $p_plugin)
		  . format_input_hidden('cmd', $p_cmd);
}

/**
 *	return info on dependency
 */
function dependency_string($p_type){
	switch($p_type){
	case 'met':
		$t_string = '<span class="dependency_met">plugin ready</span>';
		break;

	case 'unmet':
		$t_string = '<span class="dependency_unmet">unmet dependencies</span>';
		break;

	case 'dated':
		$t_string = '<span class="dependency_dated">outdated Dependencies</span>';
		break;

	case 'upgrade':
		$t_string = '<span class="dependency_upgrade">upgrade nedded</span>';
		break;

	default:
		$t_string = 'invalid state';
		break;
	}

	return ' (' . $t_string . ')';
}


auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));

form_security_purge('plugin_update');


/* get plugin data */
$t_plugins = plugin_find_all();
uasort($t_plugins,
	function ($p_p1, $p_p2){
		return strcasecmp($p_p1->name, $p_p2->name);
	}
);

$t_plugins_installed = array();
$t_plugins_available = array();

foreach($t_plugins as $t_basename => $t_plugin){
	if(plugin_is_registered($t_basename))
		$t_plugins_installed[$t_basename] = $t_plugin;
	else
		$t_plugins_available[$t_basename] = $t_plugin;
}

/* installed plugins */
section_begin('Installed Plugins');
	table_begin(array('', 'Plugin', 'Description', 'Dependencies', 'Priority', 'Protected'), 'table-condensed table-hover no-border', '', array('width="10px"'));

	foreach($t_plugins_installed as $t_basename => $t_plugin){
		$t_description = string_display_line_links($t_plugin->description);
		$t_author = $t_plugin->author;
		$t_contact = $t_plugin->contact;
		$t_page = $t_plugin->page;
		$t_url = $t_plugin->url;
		$t_requires = $t_plugin->requires;
		$t_depends = array();
		$t_priority = plugin_priority($t_basename);
		$t_protected = plugin_protected($t_basename);

		$t_name = string_display_line($t_plugin->name . ' ' . $t_plugin->version);

		if(!is_blank($t_page))
			$t_name = '<a href="' . string_attribute(plugin_page($t_page, false, $t_basename)) . '">' . $t_name . '</a>';

		if(!empty($t_author)){
			if(is_array($t_author))
				$t_author = implode($t_author, ', ');

			if(!is_blank($t_contact)){
				$t_author = '<br />' . sprintf(lang_get('plugin_author'),
					'<a href="mailto:' . string_attribute($t_contact) . '">' . string_display_line($t_author) . '</a>');
			}
			else
				$t_author = '<br />' . string_display_line(sprintf(lang_get('plugin_author'), $t_author));
		}

		if(!is_blank($t_url))
			$t_url = '<br />' . lang_get('plugin_url') . lang_get('word_separator') . '<a href="' . $t_url . '">' . $t_url . '</a>';

		$t_upgrade = plugin_needs_upgrade($t_plugin);

		if(is_array($t_requires)){
			foreach($t_requires as $t_plugin => $t_version){
				$t_dependency = plugin_dependency($t_plugin, $t_version);
				if(1 == $t_dependency){
					if(is_blank($t_upgrade))
						$t_depends[] = string_display_line($t_plugins[$t_plugin]->name . ' ' . $t_version) . dependency_string('met');
					else
						$t_depends[] = string_display_line($t_plugins[$t_plugin]->name . ' ' . $t_version) . dependency_string('upgrade');
				}
				else if(-1 == $t_dependency)
					$t_depends[] = string_display_line($t_plugins[$t_plugin]->name . ' ' . $t_version) . dependency_string('dated');
				else
					$t_depends[] = string_display_line($t_plugin . ' ' . $t_version) . dependency_string('unmet');
			}
		}

		if(count($t_depends) > 0)
			$t_depends = implode($t_depends, '<br />');
		else
			$t_depends = 'No dependencies';

		$t_prio_input = '';

		if('MantisCore' != $t_basename){
			if(!$t_protected){
				$t_prio_input = form_header($t_basename, 'set_priority')
								. format_input_hover_select('priority_' . $t_basename, plugin_priority_list(), $t_priority)
								. '</form>';
			}
			else
				$t_prio_input = $t_priority;
		}

		$t_protected_input = '';

		if('MantisCore' != $t_basename){
			$t_protected_input = form_header($t_basename, 'set_protection')
								 . format_input_hover_checkbox('protected_' . $t_basename, $t_protected)
								 . '</form>';
		}

		$t_upgrade_btn = '';

		if($t_upgrade){
			$t_upgrade_btn =
				format_button_confirm(
					'Upgrade', 'settings/plugin_update.php',
					array('plugin' => $t_basename, 'cmd' => 'upgrade', 'plugin_update_token' => form_security_token('plugin_update')),
					'Upgrade ' . $t_basename . '?', 'warning',
					format_icon('fa-angle-double-up')
				);
		}

		$t_uninstall_btn = '';

		if(!$t_protected){
			$t_uninstall_btn =
				format_button_confirm(
					'Uninstall', 'settings/plugin_update.php',
					array('plugin' => $t_basename, 'cmd' => 'uninstall', 'plugin_update_token' => form_security_token('plugin_update')),
					'Uninstall ' . $t_basename . '?', 'danger',
					format_icon('fa-trash', 'red')
				);
		}

		table_row(array(
				$t_upgrade_btn . $t_uninstall_btn,
				$t_name,
				$t_description . $t_author . $t_url,
				$t_depends,
				$t_prio_input,
				$t_protected_input
			)
		);
	}

	table_end();

	echo '</form>';
section_end();

/* not installed plugins */
section_begin('Available Plugins');
	table_begin(array('', 'Plugin', 'Description', 'Dependencies'), 'table-condensed table-hover no-border', '', array('width="10px"'));

	foreach($t_plugins_available as $t_basename => $t_plugin){
		$t_description = string_display_line_links($t_plugin->description);
		$t_author = $t_plugin->author;
		$t_contact = $t_plugin->contact;
		$t_url = $t_plugin->url ;
		$t_requires = $t_plugin->requires;
		$t_depends = array();

		$t_name = string_display_line($t_plugin->name . ' ' . $t_plugin->version);

		if(!empty($t_author)){
			if(is_array($t_author)){
				$t_author = implode($t_author, ', ');
			}
			if(!is_blank($t_contact)){
				$t_author = '<br />' . sprintf(lang_get('plugin_author'),
					'<a href="mailto:' . string_display_line($t_contact) . '">' . string_display_line($t_author) . '</a>');
			} else{
				$t_author = '<br />' . string_display_line(sprintf(lang_get('plugin_author'), $t_author));
			}
		}

		if(!is_blank($t_url))
			$t_url = '<br />' . lang_get('plugin_url') . lang_get('word_separator') . '<a href="' . $t_url . '">' . $t_url . '</a>';

		$t_ready = true;
		if(is_array($t_requires)){
			foreach($t_requires as $t_plugin => $t_version){
				$t_dependency = plugin_dependency($t_plugin, $t_version);
				if(1 == $t_dependency){
					$t_depends[] = string_display_line($t_plugins[$t_plugin]->name . ' ' . $t_version) . dependency_string('met');
				} else if(-1 == $t_dependency){
					$t_ready = false;
					$t_depends[] = string_display_line($t_plugins[$t_plugin]->name . ' ' . $t_version) . dependency_string('dated');
				} else{
					$t_ready = false;
					$t_depends[] = string_display_line($t_plugin . ' ' . $t_version) . dependency_string('unmet');
				}
			}
		}

		if(0 < count($t_depends))
			$t_depends = implode($t_depends, '<br />');
		else
			$t_depends = '<span class="small dependency_met">' . lang_get('plugin_no_depends') . '</span>';

		$t_install_btn = '';

		if($t_ready)
			$t_install_btn = format_link(format_icon('fa-wrench'), 'settings/plugin_update.php', array('plugin' => $t_basename, 'cmd' => 'install', 'plugin_update_token' => form_security_token('plugin_update')));

		table_row(array(
				$t_install_btn,
				$t_name,
				$t_description . $t_author . $t_url,
				$t_depends
			)
		);
	}

	table_end();
section_end();
