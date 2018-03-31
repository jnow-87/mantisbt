<?php

/**
 *	print a horizontal space
 *
 *	@param	string	$p_space	the amount of space, e.g. in form a pixel value ('5px')
 *
 *	@return	nothing
 */
function hspace($p_space){
	echo '<span style="margin-right:' . $p_space . '"></span>';
}

/**
 *	print a vertical space
 *
 *	@param	string	$p_space	the amount of space, e.g. in form a pixel value ('5px')
 *
 *	@return	nothing
 */
function vspace($p_space){
	echo '<span style="margin-top:' . $p_space . '"></span>';
}

/**
 *	print a label
 *
 *	@param	string	$p_name		the label name
 *	@param	string	$p_class	additional label class attributes
 *
 *	@return	nothing
 */
function label($p_name, $p_class = ''){
	echo '<span class="label label-default ' . $p_class . '">' . $p_name . '</span>';
}

/**
 *	format a text input html element
 *
 *	@param	string	$p_id		string used as the elements id name properties
 *	@param	string	$p_value	html value property
 *	@param	string	$p_class	html class property
 *	@param	string	$p_style	css stle
 *	@param	string	$p_prop		any additionally required html properties
 *
 *	@return	a string containing the html element
 */
function format_text($p_id, $p_value, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	return '<input type="text" id="' . $p_id . '" name="' . $p_id . '" class="' . $p_class . '" value="' . $p_value . '" style="' . $p_style . '" ' . $p_prop . '/>';
}

/**
 *	format a textarea input html element
 *
 *	@param	string	$p_id		string used as the elements id name properties
 *	@param	string	$p_value	html value property
 *	@param	string	$p_class	html class property
 *	@param	string	$p_style	css stle
 *	@param	string	$p_prop		any additionally required html properties
 *
 *	@return	a string containing the html element
 */
function format_textarea($p_id, $p_value, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	return '<textarea id="' . $p_id . '" name="' . $p_id . '" class="' . $p_class . '" style="' . $p_style . '" value="' . $p_value . '" ' . $p_prop . '>' . $p_value . '</textarea>';
}

/**
 *	format a checkbox input html element
 *
 *	@param	string	$p_id		string used as the elements id name properties
 *	@param	boolean	$p_checked	inidicate if the box checked
 *	@param	string	$p_class	html class property
 *	@param	string	$p_style	css stle
 *	@param	string	$p_prop		any additionally required html properties
 *
 *	@return	a string containing the html element
 */
function format_checkbox($p_id, $p_checked = false, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	return 	'<input type="checkbox" id="' . $p_id . '" name="' . $p_id . '" class="' . $p_class . '" style="' . $p_style . '" ' . $p_prop . ' ' . ($p_checked ? ' checked' : '') . '/>';
}

/**
 *	format a select input html element
 *
 *	@param	string	$p_id		string used as the elements id name properties
 *	@param	array	$p_values	array containing the possible value strings
 *	@param	string	$p_selected	the select value
 *	@param	string	$p_class	html class property
 *	@param	string	$p_style	css stle
 *	@param	string	$p_prop		any additionally required html properties
 *
 *	@return	a string containing the html element
 */
function format_select($p_id, $p_values, $p_selected, $p_class = 'input-xs', $p_style = '', $p_prop = ''){
	$t_s = '<select id="' . $p_id . '" name="' . $p_id . '" class="' . $p_class . '" style="' . $p_style . '" ' . $p_prop . '>';

	foreach($p_values as $t_value)
		$t_s .= '<option value="' . $t_value . '" ' . ($t_value == $p_selected ? 'selected' : '') . '>' . $t_value . '</option>';

	$t_s .= '</select>';

	return $t_s;
}

/**
 *	format a link html element
 *
 *	@param	string	$p_label	the string displayed
 *	@param	string	$p_action	the href property
 *	@param	array	$p_arg		array of arguments to add to the link (name => value)
 *	@param	string	$p_class	html class property
 *	@param	string	$p_style	css stle
 *
 *	@return	a string containing the html element
 */
function format_link($p_label, $p_action, $p_arg = array(), $p_class = '', $p_style = ''){
	$t_link = '<a class="' . $p_class . '" style="' . $p_style . '" href="' . htmlspecialchars($p_action);

	# arguments
	if(count($p_arg) > 0)
		$t_link .= '?';

	foreach($p_arg as $t_arg_name => $t_arg_value)
		$t_link .= '&' . $t_arg_name . '=' . $t_arg_value;
	
	$t_link .= '">' . $p_label . '</a>';

	return $t_link;
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
}

/**
 *	mark the end of section created with section_begin()
 *
 *	@return	nothing
 */
function section_end(){
	echo '</div>';
}

/**
 *	print the actionbar header
 *
 *	@return	nothing
 */
function actionbar_begin(){
	echo '<table class="table actionbar">';
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
}

/**
 *	print a html link with a button look
 *
 *	@param	string	$p_button_text	text displayed as the button
 *	@param	string  $p_link			page URL.
 *	@param	string  $p_class		additional class attributes
 *	@param	array   $p_arg			array of <key> <value> pairs that are passed on
 *									through the link
 *
 *	@return nothing
 */
function button_link($p_button_text, $p_link, $p_arg = array(), $p_class = 'btn-xs btn-round') {
	echo format_link($p_button_text, $p_link, $p_arg, 'btn btn-primary btn-white ' . $p_class);
}

/**
 *	print a submit button
 *
 *	@param	string	$p_text		text displayed as the button
 *	@param	string	$p_label	the label used as the button name
 *	@param	string	$p_type		html button type, e.g. button, submit
 *	@param	string	$p_class	additional button class attributes
 *
 *	@return	nothing
 */
function button($p_text, $p_label, $p_type = 'button', $p_class = 'btn-xs btn-round'){
	echo '<input name="' . $p_label . '" id="' . $p_label . '" class="btn btn-primary btn-white ' . $p_class . '" value="' . $p_text .'" type="' . $p_type . '"/>';
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

		echo '<li ' . $t_active . '><a data-toggle="tab" href="#' . $t_label . '">' . $t_name . '</a></li>';
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

		echo '<div id="' . $t_label . '" class="tab-pane fade ' . $t_active . '">';
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
 *
 *	@return	nothing
 */
function dropdown_menu($p_title, $p_items, $p_color = '', $p_icon = ''){
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
	echo '<ul class="dropdown-menu scrollable-menu">';

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
 *	print the header of input-hover elements
 *
 *	@param	string	$p_id	the id of the master element
 */
function input_hover_begin($p_id){
	echo '<span id="' . $p_id . '" class="input-hover-master">';
}

/**
 *	print the footer of input-hover elements
 */
function input_hover_end(){
	echo '</span>';
}

/**
 *	print an input-hover element
 *
 *	@param	string	$p_id		the id of the element
 *	@param	string	$p_element	html element
 *	@param	array	$p_buttons	an array of buttons to allocate to the element
 *								a single button contains the following fields
 *									'icon' => string containing an icon
 *									'link' => string with the link that shall be
 *											  triggered when clicking the button
 *									'position' => string with css positions
 */
function input_hover_element($p_id, $p_element, $p_buttons){
	input_hover_begin($p_id);

	echo $p_element;

	$t_i = 0;

	foreach($p_buttons as $t_button){
		input_hover_button($p_id . '-action-' . $t_i, $t_button['icon'], 'link', $t_button['link'], $t_button['position']);
		$t_i++;
	}

	input_hover_end();
}

/**
 *	print an input-hover element with submit and reset button
 *
 *	@param	string	$p_id			the id the element
 *	@param	string	$p_input		the html input element to display while the user hovers
 *									over the element or it has been focused
 *
 *	@param	string	$p_overlay		the html element that shall be display while the input-hover
 *									element is not hovered over or focused 
 *
 *	@param	string	$p_commit_pos	css position for the commit button
 *	@param	string	$p_reset_pos	css position for the reset button
 */
function input_hover_submit_reset($p_id, $p_input, $p_overlay, $p_commit_pos = '', $p_reset_pos = ''){
	input_hover_begin($p_id);

	echo $p_input;
	echo $p_overlay;

	input_hover_button($p_id . '-action-0', 'fa-check', 'submit', '', $p_commit_pos);
	input_hover_button($p_id . '-reset', 'fa-times', 'button', '', $p_reset_pos);

	input_hover_end();
}

/**
 *	print an input-hover button
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
 */
function input_hover_button($p_id, $p_icon, $p_type = 'button', $p_action = '', $p_position = ''){
	if($p_type == 'link'){
		echo '<a id="' . $p_id . '" class="input-hover-button" href="' . $p_action . '" ';
	}
	else{
		if($p_action != '')
			$p_type = 'submit';

		echo '<button type="' . $p_type . '" id="' . $p_id . '" class="input-hover-button" ';

		if($p_action != '')
			echo 'formaction="' . $p_action . '" ';
	}

	if($p_position != '')
		echo 'style="position:absolute;' . $p_position . '" ';

	echo '><i class="fa ' . $p_icon . '"></i>';

	if($p_type == 'link')
		echo '</a>';
	else
		echo '</button>';
}

/**
 *	print an input-hover text element
 *
 *	@param	string	$p_id		the id of the master element
 *								the value has to be accessed through $p_id . '-input'
 *
 *	@param	string	$p_value	the input value
 */
function input_hover_text($p_id, $p_value){
	$t_input = format_text($p_id . '-input', $p_value, 'input-hover-input');
	$t_overlay = format_text($p_id . '-overlay', $p_value, 'input-hover-overlay', '', 'readonly');

	input_hover_submit_reset($p_id, $t_input, $t_overlay, 'right:17px', 'right:4px');
}

/**
 *	print an input-hover textarea element
 *
 *	@param	string	$p_id		the id of the master element
 *								the value has to be accessed through $p_id . '-input'
 *
 *	@param	string	$p_value	the input value
 *	@param	string	$p_width	width
 *	@param	string	$p_height	height
 */
function input_hover_textarea($p_id, $p_value, $p_width = '100%', $p_height = '50px'){
	$t_style = 'width:' . $p_width . ';height:' . $p_height . ';';

	$t_input = format_textarea($p_id . '-input', $p_value, 'input-hover-input', $t_style);
	$t_overlay = format_textarea($p_id . '-overlay', $p_value, 'input-hover-overlay', $t_style, 'readonly');

	input_hover_submit_reset($p_id, $t_input, $t_overlay, 'right:17px', 'right:4px');
}

/**
 *	print an input-hover checkbox element
 *
 *	@param	string	$p_id		the id of the master element
 *								the value has to be accessed through $p_id . '-input'
 *
 *	@param	boolean	$p_checked	state of the checkbox (true or false)
 */
function input_hover_checkbox($p_id, $p_checked = false){
	$t_input = format_checkbox($p_id . '-input', $p_checked, 'input-hover-input', 'width:75px');
	$t_overlay = format_text($p_id . '-overlay', $p_checked ? 'true' : 'false', 'input-hover-overlay', 'width:75px', 'readonly');

	input_hover_submit_reset($p_id, $t_input, $t_overlay, 'right:17px', 'right:4px');
}

/**
 *	print an input-hover select element
 *
 *	@param	string	$p_id		the id of the master element
 *								the value has to be accessed through $p_id . '-input'
 *
 *	@param	array	$p_values	array containing the possible values
 *	@param	string	$p_selected	the currently selected value
 */
function input_hover_select($p_id, $p_values, $p_selected){
	$t_input = format_select($p_id . '-input', $p_values, $p_selected, 'input-hover-input', 'margin-right:35px');
	$t_overlay = format_text($p_id . '-overlay', $p_selected, 'input-hover-overlay', 'width:130px', 'readonly');

	input_hover_submit_reset($p_id, $t_input, $t_overlay, 'right:13px', 'right:0px');
}

/**
 *	print a table header
 *
 *	@param	array	$p_headrow	simple array with one entry per column
 *	@param	string	$p_class	additional table class attributes
 *	@param	string	$p_tr_attr	tr attributes
 *	@param	string	$p_th_attr	th attributes
 *
 *	@return nothing
 */
function table_begin($p_headrow, $p_class = '', $p_tr_attr = '', $p_th_attr = ''){
	echo '<table class="table ' . $p_class . '">';
	echo '<thead>';
	echo '<tr ' . $p_tr_attr . '>';
	
	foreach($p_headrow as $t_td)
		echo '<th ' . $p_th_attr . '>' . $t_td . '</th>';

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
function table_row($p_data, $p_tr_attr = '', $p_td_attr = ''){
	echo '<tr ' . $p_tr_attr . '>';

	foreach($p_data as $t_el)
		echo '<td ' . $p_td_attr . '>' . $t_el . '</td>';

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

?>
