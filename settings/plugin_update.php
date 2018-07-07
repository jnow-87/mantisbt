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
 * @uses database_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 */

/** @ignore */
define( 'PLUGINS_DISABLED', true );

require_once('../core.php');
require_api('access_api.php');
require_api('authentication_api.php');
require_api('config_api.php');
require_api('database_api.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('print_api.php');
require_api('error_api.php');
require_api('plugin_api.php');


json_prepare();

form_security_validate('plugin_update');

auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));

$f_plugin_name = gpc_get_string('plugin');
$f_cmd = gpc_get_string('cmd');
$f_redirect = gpc_get_string('redirect', '');


$t_succ_msg = '';

switch($f_cmd){
case 'set_priority':
	$f_priority = gpc_get_int('priority_' . $f_plugin_name);

	$t_query = 'UPDATE {plugin} SET priority=' . db_param() . ' WHERE basename=' . db_param();
	db_query($t_query, array($f_priority, $f_plugin_name));

	$t_succ_msg = 'Priority updated';
	break;

case 'set_protection':
	$f_protected = gpc_get_bool('protected_' . $f_plugin_name, false);

	$t_query = 'UPDATE {plugin} SET protected=' . db_param() . ' WHERE basename=' . db_param();
	db_query($t_query, array($f_protected, $f_plugin_name));

	$t_succ_msg = 'Protection updated';
	break;

case 'install':
	$t_plugin = plugin_register($f_plugin_name, true);

	if(!is_null($t_plugin))
		plugin_install($t_plugin);
	break;

case 'uninstall':
	// register plugins and metadata without initializing
	plugin_register_installed();
	$t_plugin = plugin_register($f_plugin_name, true);

	if(!is_null($t_plugin))
		plugin_uninstall($t_plugin);
	break;

case 'upgrade':
	$t_plugin = plugin_register($f_plugin_name, true);

	if(!is_null($t_plugin))
		plugin_upgrade($t_plugin);
	break;

default:
	json_error('Invalid cmd \'' . $f_cmd . '\' to '. basename(__FILE__));
}

if($f_redirect != '')
	print_header_redirect($f_redirect);

json_success($t_basename . ': ' . $t_succ_msg);
