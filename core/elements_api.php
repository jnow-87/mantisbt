<?php

require_api('lang_api.php');
require_api('date_api.php');
require_api('config_api.php');


/**
 *	format a horizontal space
 *
 *	@param	string	$p_space	the amount of space, e.g. in form a pixel value ('5px')
 *
 *	@return	a string containing the html element
 */
function format_hspace($p_space){
	return '<span style="margin-right:' . $p_space . '"></span>';
}

function hspace($p_space){
	echo format_hspace($p_space);
}

function format_strike($p_text){
	return '<strike>' . $p_text . '</strike>';
}

/**
 *	print a vertical space
 *
 *	@param	string	$p_space	the amount of space, e.g. in form a pixel value ('5px')
 *
 *	@return	nothing
 */
function format_vspace($p_space){
	return '<div style="margin-top:' . $p_space . '"></div>';
}

function vspace($p_space){
	echo format_vspace($p_space);
}

/**
 *	return required indicator if the given field is required
 *
 *	@param	string	$p_field_name		field name to check
 *	@param	array	$p_required_fields	array of required fields
 *
 *	@return	a string containing the html element
 */
function format_required_indicator($p_field_name, $p_required_fields){
	if(in_array($p_field_name, $p_required_fields))
		return '<span class="required">*</span>';
	return '';
}

function required_indicator($p_field_name, $p_required_fields){
	echo format_required_indicator($p_field_name, $p_required_fields);
}

/**
 *	format a label
 *
 *	@param	string	$p_name		the label name
 *	@param	string	$p_class	additional label class attributes
 *
 *	@return	a string containing the html element
 */
function format_label($p_name, $p_class = '', $p_style = ''){
	return '<span class="label label-sm ' . $p_class . '" style="' . $p_style . '">' . $p_name . '</span>';
}

function label($p_name, $p_class = '', $p_style = ''){
	echo format_label($p_name, $p_class, $p_style);
}

/**
 *	format a font awesome icon
 *
 *	@param	string	$p_icon			font awesome icon name
 *	@param	string	$p_space_right	horizontal space at the right hand side of the icon
 *
 *	@return	a string containing the html element
 */
function format_icon($p_icon, $p_color = '', $p_space_right = '5px'){
	return '<i class="ace-icon fa ' . $p_icon . ' ' . $p_color . '"></i>' . format_hspace($p_space_right);
}

function icon($p_icon, $p_color = '', $p_space_right = '5px'){
	echo format_icon($p_icon, $p_color, $p_space_right);
}

/**
 *	format an alert based on the given type
 *
 *	@param	string	$p_type		alert type, either of success, warning, danger, info
 *	@param	string	$p_msg		message to display
 *
 *	@return	a string containing the html element
 */
function format_alert($p_type, $p_msg){
	return '<div class="alert alert-' . $p_type . '">' . $p_msg . '</div>';
}

function alert($p_type, $p_msg){
	echo format_alert($p_type, $p_msg);
}


/**
 *	print page containing a single alert
 *
 *	@param	string	$p_type			alert type according to format_alert()
 *	@param	string	$p_msg			msg according to format_alert()
 *	@param	string	$p_page_title	page title
 */
function alert_page($p_type, $p_msg, $p_page_title = ''){
	layout_page_header();
	layout_page_begin();

	if($p_page_title != '')
		page_title($p_page_title);

	echo '<div class="col-md-12-left">';

	alert($p_type, $p_msg);

	echo '</div>';
	layout_page_end();
	exit();
}


/**
 *	format a text input html element
 *
 *	@param	string	$p_id			string used as the elements id properties
 *	@param	string	$p_name			string used as the elements name properties
 *	@param	string	$p_value		html value property
 *	@param	string	$p_placeholder	html placeholder property
 *	@param	string	$p_class		html class property
 *	@param	string	$p_style		css stle
 *	@param	string	$p_prop			any additionally required html properties
 *
 *	@return	a string containing the html element
 */
function format_text($p_id, $p_name, $p_value, $p_placeholder = '', $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	return '<input type="text" id="' . $p_id . '" name="' . $p_name . '" class="' . $p_class . '" value="' . $p_value . '" placeholder="' . $p_placeholder . '" style="' . $p_style . '" ' . $p_prop . '/>';
}

function text($p_id, $p_name, $p_value, $p_placeholder = '', $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	echo format_text($p_id, $p_name, $p_value, $p_placeholder, $p_class, $p_style, $p_prop);
}

/**
 *	format a password input html element
 *
 *	@param	string	$p_id			string used as the elements id properties
 *	@param	string	$p_name			string used as the elements name properties
 *	@param	string	$p_class		html class property
 *	@param	string	$p_style		css stle
 *	@param	string	$p_prop			any additionally required html properties
 *
 *	@return	a string containing the html element
 */
function format_password($p_id, $p_name, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	return '<input type="password" id="' . $p_id . '" name="' . $p_name . '" class="' . $p_class . '" style="' . $p_style . '" ' . $p_prop . '/>';
}

function password($p_id, $p_name, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	echo format_password($p_id, $p_name, $p_class, $p_style, $p_prop);
}


/**
 *	format a textarea input html element
 *
 *	@param	string	$p_id		string used as the elements id properties
 *	@param	string	$p_name		string used as the elements name properties
 *	@param	string	$p_value	html value property
 *	@param	string	$p_class	html class property
 *	@param	string	$p_style	css stle
 *	@param	string	$p_prop		any additionally required html properties
 *
 *	@return	a string containing the html element
 */
function format_textarea($p_id, $p_name, $p_value, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	return '<textarea id="' . $p_id . '" name="' . $p_name . '" class="' . $p_class . '" style="' . $p_style . '" value="' . $p_value . '" ' . $p_prop . '>' . $p_value . '</textarea>';
}

function textarea($p_id, $p_name, $p_value, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	echo format_textarea($p_id, $p_name, $p_value, $p_class, $p_style, $p_prop);
}


/**
 *	format a checkbox input html element
 *
 *	@param	string	$p_id		string used as the elements id properties
 *	@param	string	$p_name		string used as the elements name properties
 *	@param	boolean	$p_checked	inidicate if the box checked
 *	@param	string	$p_class	html class property
 *	@param	string	$p_style	css stle
 *	@param	string	$p_prop		any additionally required html properties
 *
 *	@return	a string containing the html element
 */
function format_checkbox($p_id, $p_name, $p_checked = false, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	return 	'<input type="checkbox" id="' . $p_id . '" name="' . $p_name . '" class="ace ' . $p_class . '" style="' . $p_style . '" ' . $p_prop . ' ' . ($p_checked ? ' checked' : '') . '/>';
}

function checkbox($p_id, $p_name, $p_checked = false, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	echo format_checkbox($p_id, $p_name, $p_checked, $p_class, $p_style, $p_prop);
}

/**
 *	format a select input html element
 *
 *	@param	string	$p_id		string used as the elements id properties
 *	@param	string	$p_name		string used as the elements name properties
 *	@param	array	$p_values	array containing the possible value strings
 *	@param	string	$p_selected	the select value
 *	@param	string	$p_class	html class property
 *	@param	string	$p_style	css stle
 *	@param	string	$p_prop		any additionally required html properties
 *
 *	@return	a string containing the html element
 */
function format_select($p_id, $p_name, $p_values, $p_selected, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	$t_s = '<select id="' . $p_id . '" name="' . $p_name . '" class="' . $p_class . '" style="' . $p_style . '" ' . $p_prop . '>';

	foreach($p_values as $t_key => $t_value)
		$t_s .= '<option value="' . $t_value . '" ' . ($t_key == $p_selected ? 'selected' : '') . '>' . $t_key . '</option>';

	$t_s .= '</select>';

	return $t_s;
}

function select($p_id, $p_name, $p_values, $p_selected, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	echo format_select($p_id, $p_name, $p_values, $p_selected, $p_class, $p_style, $p_prop);
}

/**
 *	format a date input html element
 *
 *	@param	string	$p_id			string used as the elements id property
 *	@param	string	$p_name			string used as the elements name property
 *	@param	array	$p_value		string used as the elements value property
 *	@param	string	$p_width		input width
 *	@param	boolean	$p_fmt_short	if true use the short date format, otherwise
 *									use the normal date format
 *
 *	@return	a string containing the html element
 */
function format_date($p_id, $p_name, $p_value, $p_width = '', $p_fmt_short = false){
	$t_width = ($p_width != '') ? 'width:' . $p_width . ' !important' : '';
	$t_date_prop = 'data-picker-locale="' . lang_get_current_datetime_locale() . '" data-picker-format="' . convert_date_format_to_momentjs($p_fmt_short ? config_get('short_date_format') : config_get('normal_date_format')) . '"';

	return format_text($p_id, $p_name, $p_value, '', 'input-xs inline-page-datetime', $t_width, $t_date_prop);
}

/**
 *	format a href string
 *
 *	@param	string	$p_action	link/action to trigger
 *	@param	array	$p_args		array of arguments containing 'key' => 'value' pairs
 *
 *	@return	a string containing the html element
 */
function format_href($p_action, $p_args = array()){
	$t_href = htmlspecialchars($p_action);

	if(count($p_args) > 0)
		$t_href .= '?';

	foreach($p_args as $t_key => $t_value)
		$t_href .= '&' . $t_key . (($t_value != '') ? '=' : '') . $t_value;

	return $t_href;
}

/**
 *	format a link html element
 *
 *	@param	string	$p_label	the string displayed
 *	@param	string	$p_action	the href property
 *	@param	array	$p_arg		array of arguments to add to the link (name => value)
 *	@param	string	$p_class	html class property
 *	@param	string	$p_style	css stle
 *	@param	string	$p_prop		additional properties
 *
 *	@return	a string containing the html element
 */
function format_link($p_label, $p_action, $p_arg = array(), $p_class = '', $p_style = '', $p_prop = ''){
	return $t_link = '<a class="' . $p_class . '" style="' . $p_style . '" href="' . format_href($p_action, $p_arg) . '" ' . $p_prop . '>' . $p_label . '</a>';
}

/**
 *	print the page title
 *
 *	@param	string	$p_title	the page title
 *
 *	@return	nothing
 */
function page_title($p_title){
	echo '<hr class="hr-page-title" data-content="' . $p_title . '"></hr>';
}

function column_begin($p_md, $p_xs = '12'){
	echo '<div class="col-md-' . $p_md . ' col-xs-' . $p_xs . '">';
}

function column_end(){
	echo '</div>';
}

/**
 *	print a collapsable section header
 *
 *	@param	string	$p_heading		the section name
 *	@param	bool	$p_collapsed	flag indicating the inital collapse state of the heading
 *
 *	@return	nothing
 */
$g_section_label_cnt = 0;

function section_begin($p_heading, $p_collapsed = false){
	global $g_section_label_cnt;

	$t_label = 'section_' . $g_section_label_cnt;
	$g_section_label_cnt++;

	echo '<hr id="' . $t_label . '" class="hr-text ' . ($p_collapsed ? 'collapsed' : '') . '" data-content="' . $p_heading . '" data-toggle="collapse" data-target="#' . $t_label . '_target">';
	echo '<div id="' . $t_label . '_target" class="section collapse ' . (!$p_collapsed ? 'in' : '') . '">';
	echo '<div class="row">';
}

/**
 *	mark the end of section created with section_begin()
 *
 *	@return	nothing
 */
function section_end(){
	echo '</div>';
	echo '</div>';
}

/**
 *	print the actionbar header
 *
 *	@return	nothing
 */
function actionbar_begin(){
	echo '<div class="actionbar">';
	echo '<table class="actionbar">';
	echo '<thead>';
	echo '<tr><td class="actionbar">';
}

/**
 *	print the actionbar footer
 *
 *	@return	nothing
 */
function actionbar_end(){
	echo '</td></tr>';
	echo '</thead>';
	echo '</table>';
	echo '</div>';
}

/**
 *	format a html link with a button look
 *
 *	@param	string	$p_button_text		text displayed as the button
 *	@param	string  $p_action			page URL.
 *	@param	array   $p_arg				array of <key> <value> pairs that are passed on
 *										through the link
 *
 *	@param	string  $p_class			additional class attributes
 *	@param	boolean	$p_class_overwrite	if true, overwrite the internal class attributes
 *										entirely by $p_class, otherwise $p_class is
 *										appended to the internal attributes
 *
 *	@param	boolean	$p_visible			if true the button is shown, otherwise the button
 *										present, i.e. occupies space, but not visible
 *
 *	@param	string	$p_prop				additional properties
 *
 *	@return nothing
 */
function format_button_link($p_button_text, $p_action, $p_arg = array(), $p_class = '', $p_class_overwrite = false, $p_visible = true, $p_prop = ''){
	$t_class = 'btn btn-primary btn-white btn-xs btn-round ' . $p_class;

	if($p_class_overwrite == true)
		$t_class = $p_class;

	 if(!$p_visible)
	 	$t_class .= ' invisible';

	return format_link($p_button_text, $p_action, $p_arg, $t_class, '', $p_prop) . format_hspace('2px');
}

function button_link($p_button_text, $p_action, $p_arg = array(), $p_class = '', $p_class_overwrite = false, $p_visible = true, $p_prop = ''){
	echo format_button_link($p_button_text, $p_action, $p_arg, $p_class, $p_class_overwrite, $p_visible, $p_prop);
}

/**
 *	format a submit button
 *
 *	@param	string	$p_text				text displayed as the button
 *	@param	string	$p_id				the label used as the button name
 *	@param	string	$p_type				html button type, e.g. button, submit
 *	@param	string	$p_class			additional button class attributes
 *	@param	boolean	$p_class_overwrite	if true, overwrite the internal class attributes
 *										entirely by $p_class, otherwise $p_class is
 *										appended to the internal attributes
 *
 *	@param	string	$p_prop				additional properties
 *	@param	boolean	$p_force_input		force the use of an html input element instead of a button element
 *										this is somehow required when submitting forms that contain file
 *										input elements
 *
 *	@return	a string containing the html element
 */
function format_button($p_text, $p_id, $p_type = 'button', $p_action = '',  $p_class = '', $p_class_overwrite = false, $p_prop = '', $p_force_input = false){
	$t_btn = '';

	$t_class = 'btn btn-primary btn-white btn-xs btn-round ' . $p_class;

	if($p_type == '')
		$p_type = 'button';

	if($p_class_overwrite)
		$t_class = $p_class;

	if($p_action != '')
		$p_type = 'submit';

	if($p_force_input == true)
		$t_btn .= '<input ';
	else
		$t_btn .= '<button ';

	$t_btn .= 'name="' . $p_id . '" id="' . $p_id . '" class="' . $t_class . '" type="' . $p_type . '" ' . $p_prop . ' ';

	if($p_action != '')
		$t_btn .= 'formaction="' . $p_action . '" ';
	
	if($p_force_input == true)
		$t_btn .= ' value="' . $p_text . '"/>';
	else
		$t_btn .= '>' . $p_text . '</button>';

	$t_btn .= format_hspace('2px');

	return $t_btn;
}

function button($p_text, $p_id, $p_type = 'button', $p_action = '',  $p_class = '', $p_class_overwrite = false, $p_prop = '', $p_force_input = false){
	echo format_button($p_text, $p_id, $p_type, $p_action,  $p_class, $p_class_overwrite, $p_prop, $p_force_input);
}

/**
 *	format a button that triggers a confirm inline-page
 *
 *	@param	string	$p_text			text displayed as the button
 *	@param	string	$p_action		action to trigger if it is confirmed
 *	@param	array	$p_args			arguments to pass when triggering the button
 *	@param	string	$p_msg			message to display at the inline page
 *	@param	string	$p_msg_class	class to be used for message div
 *
 *	@return	a string containing the html element
 */
function format_button_confirm($p_text, $p_action, $p_args, $p_msg, $p_msg_class = '', $p_icon = ''){
	$t_r = '';

	$t_args['confirm_btn'] = $p_text;
	$t_args['confirm_redirect'] = $p_action;
	$t_args['confirm_arg_keys'] = implode('|', array_keys($p_args));
	$t_args['confirm_arg_values'] = implode('|', array_values($p_args));
	$t_args['confirm_msg'] = $p_msg;
	$t_args['confirm_msg_class'] = $p_msg_class;

	if($p_icon != '')
		$t_r .= format_link($p_icon, helper_mantis_url('confirm.php'), $t_args, 'inline-page-link');
	else
		$t_r .= format_button_link($p_text, helper_mantis_url('confirm.php'), $t_args, 'inline-page-link');

	return $t_r;
}

function button_confirm($p_text, $p_action, $p_args, $p_msg, $p_msg_class = ''){
	echo format_button_confirm($p_text, $p_action, $p_args, $p_msg, $p_msg_class);
}

/**
 *	format an icon that triggers a confirm inline-page
 *
 *	@param	string	$p_icon			font-awsome icon
 *	@param	string	$p_text			text displayed as the button in the confirm page
 *	@param	string	$p_id			button id
 *	@param	string	$p_action		action to trigger if it is confirmed
 *	@param	string	$p_msg			message to display at the inline page
 *	@param	string	$p_msg_class	class to be used for message div
 *	@param	string	$p_icon_class	class to be used for icon
 *	@param	boolean	$p_in_form		if true the button is assumed to be part of form and thus no
 *									new one is created
 *									if false the button is embedded into a form
 *
 *	@return	a string containing the html element
 */
function format_icon_confirm($p_icon, $p_text, $p_id, $p_action, $p_msg, $p_msg_class = '', $p_icon_class, $p_in_form = true){
	$t_r = '';

	if(!$p_in_form)
		$t_r .= '<form method="post" class="input-hover-form">';

	$t_r .= format_input_hidden('confirm_btn', $p_text);
	$t_r .= format_input_hidden('confirm_redirect', $p_action);
	$t_r .= format_input_hidden('confirm_msg', $p_msg);
	$t_r .= format_input_hidden('confirm_msg_class', $p_msg_class);

	$t_r .= format_button('<i class="fa ' . $p_icon .'"></i>', $p_id, 'submit', 'confirm.php',  'btn-icon ' . $p_icon_class, true);

	if(!$p_in_form)
		$t_r .= '</form>';

	return $t_r;
}

function icon_confirm($p_icon, $p_text, $p_id, $p_action, $p_msg, $p_msg_class = '', $p_in_form = true){
	echo format_icon_confirm($p_icon, $p_text, $p_id, $p_action, $p_msg, $p_msg_class, $p_in_form);
}

/**
 *	print a list of tabs and their respective content
 *
 *	@param	array	$p_tabs		array in the form of array('tab-name' => 'callback-name'), whereas
 *								tab-name is tab name and
 *								callback-name is the name of the function that renders the tab content
 *
 *	@return nothing
 */
$g_tab_label_cnt = 0;

function tabs($p_tabs){
	global $g_tab_label_cnt;

	# tab bar
	echo '<ul class="nav nav-tabs" style="margin:0 12px 0 12px">';

	$t_active = 'class="active"';
	foreach($p_tabs as $t_name => $t_content){
		$t_label = 'tab_' . $g_tab_label_cnt;
		$g_tab_label_cnt++;

		echo '<li ' . $t_active . '><a id="' . $t_label . '" data-toggle="tab" href="#' . $t_label . '_ref">' . $t_name . '</a></li>';
		$t_active = '';
	}

	echo '</ul>';

	# tab content
	echo '<div class="tab-content" style="margin:0 12px 0 12px">';

	$t_active = 'in active';
	$t_ntabs = count($p_tabs);

	foreach($p_tabs as $t_name => $t_content){
		$t_label = 'tab_' . ($g_tab_label_cnt - $t_ntabs);
		$t_ntabs--;

		echo '<div id="' . $t_label . '_ref" class="tab-pane fade ' . $t_active . '">';
		$t_active = '';
		echo $t_content();
		echo '</div>';
	}

	echo '</div>';
}

/**
 *	print a dropdown menu
 *
 *	@param	string	$p_title	dropdown menu name
 *	@param	array	$p_items	dropdown menu entries, containing the following elements
 *									'label' => 'item-name'
 *									'data' => label dependent type defined as follows
 *										label == 'header'
 *											data => string
 *											this item is rendered a heading within the menu,
 *											whereas data is the headeing name
 *
 *										label == 'divider'
 *											data => ignored
 *											this item is rendered as horizontal line within
 *											the menu
 *
 *										label == 'bare'
 *											data => string
 *											data is echoed as is, this can be used to add any
 *											kind of item to the menu
 *
 *										label == any other string
 *											data => array
 *											this item is rendered as menu entry which triggers
 *											a link, hence data is required to contain at least
 *											an entry 'link'.
 *											data can also contain entries for 'class' and 'icon'
 *											if present they influence the link class attributes
 *											and the icon displayed in front of the item
 *
 *	@param	string	$p_color	dropdown menu color class
 *	@param	string	$p_icon		an optional item shown in front of the menu name
 *	@param	string	$p_class	dropdown menu class additions
 *
 *	@return	nothing
 */
function dropdown_menu($p_title, $p_items, $p_color = '', $p_icon = '', $p_class = ''){
	$t_padding_button = '0px';
	$t_padding_icon_left = '0px';
	$t_padding_icon_right = '10px';

	if($p_color == ''){
		$t_padding_button = '10px';
		$t_padding_icon_left = '5px';
		$t_padding_icon_right = '5px';
	}

	# button group and navigation bar header
	echo '<div class="btn-group">';
	echo '<ul class="nav ace-nav" style="padding-left:'. $t_padding_button . ';padding-right:' . $t_padding_button . '">';

	# color
	if($p_color != '')
		echo '<li class="' . $p_color . '">';

	echo '<a data-toggle="dropdown" href="#" class="dropdown-toggle">';

	# title icon
	if($p_icon != '')
		echo '<i class="ace-icon fa ' . $p_icon . '" style="padding-right:' . $t_padding_icon_right . '"></i>';

	# title
	echo $p_title . '<i class="ace-icon ' . $p_color . ' fa fa-angle-down" style="padding-left:' . $t_padding_icon_left . '"></i>';
	echo '</a>';

	# menu items
	echo '<ul class="dropdown-menu scrollable-menu ' . $p_class . '">';

	foreach($p_items as $t_item){
		$t_label = $t_item['label'];
		$t_data = $t_item['data'];

		if($t_label == 'header'){
			echo '<li class="dropdown-header">' . $t_data . '</li>';
		}
		else if($t_label == 'divider'){
			echo '<li class="divider"/>';
		}
		else if($t_label == 'bare'){
			echo $t_data;
		}
		else{
			$t_class = '';

			if(isset($t_data['class']))
				$t_class = 'class="' . $t_data['class'] . '"';

			echo '<li><a ' . $t_class . ' href="' . $t_data['link'] . '">';

			if(isset($t_data['icon']) && $t_data['icon'] != '')
				echo '<i class="ace-icon fa ' . $t_data['icon'] . '" style="padding-right:5px"></i>';
				
			echo $t_label;
			echo '</a></li>';
		}
	}

	echo '</ul>';

	if($p_color)
		echo '</li>';

	echo '</ul>';
	echo '</div>';
}

/**
 *	format a hidden input field
 *
 *	@param	string	$p_id		element name
 *	@param	string	$p_value	element value
 *
 *	@return	a string containing the html element
 */
function format_input_hidden($p_id, $p_value){
	return '<input type="hidden" id="' . $p_id . '" name="' . $p_id . '" value="' . $p_value . '"/>';
}

function input_hidden($p_id, $p_value){
	echo format_input_hidden($p_id, $p_value);
}

/**
 *	format the header of input-hover elements
 *
 *	@param	string	$p_id	the id of the master element
 *
 *	@return	formated string
 */
function format_input_hover_begin($p_id){
	return '<span id="' . $p_id . '" class="input-hover-master">';
}

function input_hover_begin($p_id){
	echo format_input_hover_begin($p_id);
}


/**
 *	format the footer of input-hover elements
 *
 *	@return	formated string
 */
function format_input_hover_end(){
	return '</span>';
}

function input_hover_end(){
	echo format_input_hover_end();
}

/**
 *	format an input-hover button
 *
 *	@param	string	$p_id		button id
 *	@param	string	$p_icon		button icon
 *	@param	string	$p_type		button type, which is either of
 *									'button'	for generic buttons, $p_action is required to be a formaction
 *									'submit'	for submit buttons, $p_action is required to be a formaction
 *									'link'		for links, $p_action is interpreted as href
 *
 *	@param	string	$p_action	the action to take when clicking the button
 *									if $p_type is 'button' or 'submit' $p_action is required to be a formaction
 *									if $p_type is 'link' $p_action is interpreted as href
 *
 *	@param	string	$p_position	css position
 *
 *	@return	formated string
 */
function format_input_hover_button($p_id, $p_icon, $p_type = 'button', $p_action = '', $p_class = '', $p_position = '', $p_prop = ''){
	$t_s = '';

	if($p_type == 'link'){
		$t_s .= '<a id="' . $p_id . '" class="input-hover-button ' . $p_class . '" href="' . $p_action . '" ';
	}
	else{
		if($p_action != '')
			$p_type = 'submit';

		$t_s .= '<button type="' . $p_type . '" id="' . $p_id . '" class="input-hover-button ' . $p_class . '" ';

		if($p_action != '')
			$t_s .= 'formaction="' . $p_action . '" ';
	}

	if($p_position != '')
		$t_s .= 'style="position:absolute;' . $p_position . '" ';

	$t_s .= $p_prop;

	$t_s .= '><i class="fa ' . $p_icon . '"></i>';

	if($p_type == 'link')
		$t_s .= '</a>';
	else
		$t_s .= '</button>';

	return $t_s;
}

function input_hover_button($p_id, $p_icon, $p_type = 'button', $p_action = '', $p_class = '', $p_position = '', $p_prop = ''){
	echo format_input_hover_button($p_id, $p_icon, $p_type, $p_action, $p_class, $p_position, $p_prop);
}

/**
 *	format an input-hover element
 *
 *	@param	string	$p_id		the id of the element
 *	@param	string	$p_element	html element
 *	@param	array	$p_buttons	an array of buttons to allocate to the element
 *								a single button contains the following fields
 *									'icon' => string containing an icon
 *									'href' => string with the link that shall be
 *											  triggered when clicking the button
 *									'class' => string with additional button class
 *									'position' => string with css positions
 *									'properties' => string with further html properties
 *
 *	@return	formated string
 */
function format_input_hover_element($p_id, $p_element, $p_buttons){
	$t_s = '';

	$t_s .= format_input_hover_begin($p_id);
	$t_s .= $p_element;

	$t_i = 0;

	foreach($p_buttons as $t_button){
		$t_s .= format_input_hover_button($p_id . '-action-' . $t_i, $t_button['icon'], 'link', $t_button['href'], $t_button['class'], $t_button['position'], $t_button['properties']);
		$t_i++;
	}

	$t_s .= format_input_hover_end();

	return $t_s;
}

function input_hover_element($p_id, $p_element, $p_buttons){
	echo format_input_hover_element($p_id, $p_element, $p_buttons);
}

/**
 *	format an input-hover element with submit and reset button
 *
 *	@param	string	$p_id				the id the element
 *	@param	string	$p_input			the html input element to display while the user hovers
 *										over the element or it has been focused
 *
 *	@param	string	$p_overlay			the html element that shall be display while the input-hover
 *										element is not hovered over or focused 
 *
 *	@param	string	$p_commit_pos		css position for the commit button
 *	@param	string	$p_reset_pos		css position for the reset button
 *	@param	string	$p_submit_action	alternate submit action, default is specified through the
 *										form the button belongs to
 *
 *	@return	formated string
 */
function format_input_hover_submit_reset($p_id, $p_input, $p_overlay, $p_commit_pos = '', $p_reset_pos = '', $p_submit_action = ''){
	$t_s = '';

	$t_s .= format_input_hover_begin($p_id);

	$t_s .= $p_input;
	$t_s .= $p_overlay;

	$t_s .= format_input_hover_button($p_id . '-action-0', 'fa-check', 'submit', $p_submit_action, '', $p_commit_pos);
	$t_s .= format_input_hover_button($p_id . '-reset', 'fa-times', 'button', '', '', $p_reset_pos);

	$t_s .= format_input_hover_end();

	return $t_s;
}

function input_hover_submit_reset($p_id, $p_input, $p_overlay, $p_commit_pos = '', $p_reset_pos = '', $p_submit_action = ''){
	echo format_input_hover_submit_reset($p_id, $p_input, $p_overlay, $p_commit_pos, $p_reset_pos, $p_submit_action);
}

/**
 *	format an input-hover text element
 *
 *	@param	string	$p_id				the id of the master element
 *										the value has to be accessed through $p_id . '-input'
 *
 *	@param	string	$p_value			the input value
 *	@param	string	$p_width			input width
 *	@param	string	$p_submit_action	alternate submit action, default is specified through the
 *										form the button belongs to
 *
 *	@return	formated string
 */
function format_input_hover_text($p_id, $p_value, $p_width = '', $p_submit_action = ''){
	$t_width = ($p_width != '') ? 'width:' . $p_width : '';

	$t_input = format_text($p_id . '-input', $p_id, $p_value, '', 'input-hover-input', $t_width);
	$t_overlay = format_text($p_id . '-overlay', $p_id . '-overlay', $p_value, '', 'input-hover-overlay', $t_width, 'readonly');

	return format_input_hover_submit_reset($p_id, $t_input, $t_overlay, 'right:17px', 'right:4px', $p_submit_action);
}

function input_hover_text($p_id, $p_value, $p_width = '', $p_submit_action = ''){
	echo format_input_hover_text($p_id, $p_value, $p_width, $p_submit_action);
}

/**
 *	format an input-hover textarea element
 *
 *	@param	string	$p_id				the id of the master element the value has to be accessed
 *										through $p_id . '-input'
 *
 *	@param	string	$p_value			the input value
 *	@param	string	$p_width			width
 *	@param	string	$p_height			height
 *	@param	string	$p_submit_action	alternate submit action, default is specified through the
 *										form the button belongs to
 *
 *	@return	formated string
 */
function format_input_hover_textarea($p_id, $p_value, $p_width = '100%', $p_height = '50px', $p_submit_action = ''){
	$t_style = 'width:' . $p_width . ';height:' . $p_height . ';';

	$t_input = format_textarea($p_id . '-input', $p_id, $p_value, 'input-hover-input', $t_style);
	$t_overlay = format_textarea($p_id . '-overlay', $p_id . '-overlay', $p_value, 'input-hover-overlay', $t_style, 'readonly');

	return format_input_hover_submit_reset($p_id, $t_input, $t_overlay, 'right:17px', 'right:4px', $p_submit_action);
}

function input_hover_textarea($p_id, $p_value, $p_width = '100%', $p_height = '50px', $p_submit_action = ''){
	echo format_input_hover_textarea($p_id, $p_value, $p_width, $p_height, $p_submit_action);
}

/**
 *	format an input-hover checkbox element
 *
 *	@param	string	$p_id		the id of the master element
 *								the value has to be accessed through $p_id . '-input'
 *
 *	@param	boolean	$p_checked	state of the checkbox (true or false)
 *	@param	string	$p_submit_action	alternate submit action, default is specified through the
 *										form the button belongs to
 *
 *	@return	formated string
 */
function format_input_hover_checkbox($p_id, $p_checked = false, $p_submit_action = ''){
	$t_input = format_checkbox($p_id . '-input', $p_id, $p_checked, 'input-hover-input', 'width:75px');
	$t_overlay = format_text($p_id . '-overlay', $p_id . '-overlay', $p_checked ? 'true' : 'false', '', 'input-hover-overlay', 'width:75px', 'readonly');

	return format_input_hover_submit_reset($p_id, $t_input, $t_overlay, 'right:17px', 'right:4px', $p_submit_action);
}

function input_hover_checkbox($p_id, $p_checked = false, $p_submit_action = ''){
	echo format_input_hover_checkbox($p_id, $p_checked, $p_submit_action);
}

/**
 *	format an input-hover select element
 *
 *	@param	string	$p_id		the id of the master element
 *								the value has to be accessed through $p_id . '-input'
 *
 *	@param	array	$p_values	array containing the possible values
 *	@param	string	$p_selected	the currently selected value
 *	@param	string	$p_submit_action	alternate submit action, default is specified through the
 *										form the button belongs to
 *
 *	@return	formated string
 */
function format_input_hover_select($p_id, $p_values, $p_selected, $p_submit_action = ''){
	$t_input = format_select($p_id . '-input', $p_id, $p_values, $p_selected, 'input-hover-input', 'margin-right:35px');
	$t_overlay = format_text($p_id . '-overlay', $p_id . '-overlay', $p_selected, '', 'input-hover-overlay', 'width:130px', 'readonly');

	return format_input_hover_submit_reset($p_id, $t_input, $t_overlay, 'right:13px', 'right:0px', $p_submit_action);
}

function input_hover_select($p_id, $p_values, $p_selected, $p_submit_action = ''){
	echo format_input_hover_select($p_id, $p_values, $p_selected, $p_submit_action);
}

/**
 *	format an input-hover date picker element
 *
 *	@param	string	$p_id				the id of the master element
 *										the value has to be accessed through $p_id . '-input'
 *
 *	@param	string	$p_value			the input value
 *	@param	string	$p_width			input width
 *	@param	string	$p_submit_action	alternate submit action, default is specified through the
 *										form the button belongs to
 *
 *	@return	formated string
 */
function format_input_hover_date($p_id, $p_value, $p_width = '', $p_submit_action = ''){
	$t_width = ($p_width != '') ? 'width:' . $p_width : '';
	$t_date_prop = 'data-picker-locale="' . lang_get_current_datetime_locale() . '" data-picker-format="' . convert_date_format_to_momentjs(config_get('normal_date_format')) . '"';

	$t_input = format_text($p_id . '-input', $p_id, $p_value, '', 'input-hover-input datetimepicker', $t_width, $t_date_prop);
	$t_overlay = format_text($p_id . '-overlay', $p_id . '-overlay', $p_value, '', 'input-hover-overlay', $t_width, 'readonly');

	return format_input_hover_submit_reset($p_id, $t_input, $t_overlay, 'right:22px', 'right:9px', $p_submit_action);
}

function input_hover_date($p_id, $p_value, $p_width = '', $p_submit_action = ''){
	echo format_input_hover_date($p_id, $p_value, $p_width, $p_submit_action);
}

function format_dragable($p_list){
	echo '<ul class="dragable">';

	foreach($p_list as $t_el){
  		echo '<li class="ui-state-default">';
		icon('fa-sort');
		echo $t_el;
		echo '</li>';
	}

	echo '</ul>';
}

function dragable($p_list){
	echo format_dragable($p_list);
}


/**
 *	print a table header
 *
 *	@param	array	$p_headrow	simple array with one entry per column
 *	@param	string	$p_class	additional table class attributes
 *	@param	string	$p_tr_attr	tr attributes
 *	@param	array	$p_th_attr	th attributes per column
 *
 *	@return nothing
 */
function table_begin($p_headrow, $p_class = '', $p_tr_attr = '', $p_th_attr = array()){
	echo '<table class="table ' . $p_class . '">';
	echo '<thead>';
	echo '<tr ' . $p_tr_attr . '>';
	
	for($t_i=0; $t_i<count($p_headrow); $t_i++)
		echo '<th ' . (isset($p_th_attr[$t_i]) ? $p_th_attr[$t_i] : '') . '>' . $p_headrow[$t_i] . '</th>';

	echo '</tr>';
	echo '</thead>';
}

/**
 *	print a table row
 *
 *	@param	array	$p_data		simple array with one entry per column
 *	@param	string	$p_tr_attr	tr attributes
 *	@param	string	$p_td_attr	td attributes
 *
 *	@return nothing
 */
function table_row($p_data, $p_tr_attr = '', $p_td_attr = array()){
	echo '<tr ' . $p_tr_attr . '>';

	for($t_i=0; $t_i<count($p_data); $t_i++){
		$t_td = (isset($p_td_attr[$t_i]) ? $p_td_attr[$t_i] : '');
		echo '<td ' . $t_td . '>' . $p_data[$t_i] . '</td>';
	}

	echo '</tr>';
}

/**
 *	print a table footer
 *
 *	@return nothing
 */
function table_end(){
	echo '</table>';
}

/**
 *	print a table row formated for bug information, that is with
 *	heading and data and defined cell sizes
 *
 *	@param	string	$p_key		the heading cell content
 *	@param	string	$p_value	the value cell content
 *
 *	@return	nothing
 */
function table_row_bug_info_short($p_key, $p_value){
	table_row(array($p_key, $p_value), '', array('class="no-border bug-header" width="30%"', 'class="no-border"'));
}

function table_row_bug_info_long($p_key, $p_value, $p_key_width = '50%'){
	table_row(array($p_key, $p_value), '', array('class="no-border bug-header" width="' . $p_key_width . '"', 'class="no-border"'));
}

/**
 *	format a progressbar
 *
 *	@param	$p_percentage	string	fill state of the bar
 *	@param	$p_label		string	text to show, if empty $p_percentage is shown
 *	@param	$p_class		string	progessbar class
 *
 *	@return	a string containing the html element
 */
function format_progressbar($p_percentage, $p_label = '', $p_class = 'progress-bar progress-bar-success'){
	if($p_label == '')
		$p_label = $p_percentage . '%';

	return '<div class="progress"><div class="' . $p_class . '" role="progressbar" style="width:' . $p_percentage . '%" >' . $p_label . '</div></div>';
}

function progressbar($p_percentage, $p_label = '', $p_class = 'progress-bar progress-bar-success'){
	echo format_progressbar($p_percentage, $p_label, $p_class);
}

?>
