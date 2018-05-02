<?php

require_once('core.php');
require_api('authentication_api.php');
require_api('gpc_api.php');
require_api('layout_api.php');
require_api('bug_list_api.php');
require_api('elements_api.php');

define('COLUMN_SELECT_PAGE', 'columns_select_page.php');


/**
 *	format a dragable item
 */
function format_item($t_column_name, $p_selected){
	return '<label for="' . $t_column_name. '_sel">'
		   . bug_list_column_title($t_column_name, false)
		   . '</label>'
		   . format_checkbox($t_column_name . '_sel', 'columns_arr[]', $p_selected, '', '', 'value="' . $t_column_name . '"');
}


/* form input */
$f_page = $_SERVER['HTTP_REFERER'];

$f_ignore_gpc = gpc_get_bool('reset', false);
$f_select_all = gpc_get_bool('select_all', false);
$f_select_none = gpc_get_bool('select_none', false);
$f_hide_apply = gpc_get_bool('hide_apply', false);
$f_usage = gpc_get_string('usage');
$f_cmd = gpc_get_string('cmd', '');

/* acquire column lists */
$t_columns_all = bug_list_columns_all();
$t_columns_cur = bug_list_columns($f_usage, $f_ignore_gpc);

/* process commands */
switch($f_cmd){
case 'update_config':
	// update config and reload page
	config_set('bug_list_columns_' . $f_usage, $t_columns_cur, auth_get_current_user_id());
	print_header_redirect($f_page, true, false, true);
	break;
}


/* page content */
layout_inline_page_begin();
page_title('Columns Selection');

echo '<div class="col-md-12">';
echo '<form method="post" action="" class="">';
	input_hidden('usage', $f_usage);

	actionbar_begin();
		echo '<div class="pull-left">';
		button_link('Default', COLUMN_SELECT_PAGE, array('reset' => 1, 'usage' => $f_usage), 'inline-page-link');
		button_link('Select All', COLUMN_SELECT_PAGE, array('select_all' => 1, 'usage' => $f_usage), 'inline-page-link');
		button_link('Select None', COLUMN_SELECT_PAGE, array('select_none' => 1, 'usage' => $f_usage), 'inline-page-link');
		echo '</div>';

		echo '<div class="pull-right">';
		if(!$f_hide_apply)
			button('Apply', 'apply-btn', 'submit');

		button('Apply and Save', 'apply-btn', 'submit', COLUMN_SELECT_PAGE . '?cmd=update_config');
		echo '</div>';
	actionbar_end();

	/* generate dragable item list */
	$t_items = array();

	// selecteed items
	foreach($t_columns_cur as $t_col)
		$t_items[] = format_item($t_col, ($f_select_none ? false : true));

	// not selected items
	$t_diff = array_diff($t_columns_all, $t_columns_cur);
	$t_titles = array();

	foreach($t_diff as $t_el)
		$t_titles[bug_list_column_title($t_el, false)] = $t_el;

	ksort($t_titles);

	foreach($t_titles as $t_col)
		$t_items[] = format_item($t_col, ($f_select_all ? true : false));

	/* print dragable items */
	dragable($t_items);
echo '</form>';
echo '</div>';

layout_inline_page_end();
