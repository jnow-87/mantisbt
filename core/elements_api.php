<?php

/**
 *	print a horizontal space
 *
 *	@param	string	$p_space	the amount of space, e.g. in form a pixel value ('5px')
 *
 *	@return	nothing
 */
function hspace($p_space){
	echo '<span style="padding-right:' . $p_space . '"></span>';
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

	echo '<hr class="hr-text ' . ($p_collapsed ? 'collapsed' : '') . '" data-content="' . $p_heading . '" data-toggle="collapse" data-target="#' . $t_label . '_target">';
	echo '<div id="' . $t_label . '_target" class="collapse ' . (!$p_collapsed ? 'in' : '') . '">';
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
	# button start
	echo '<a class="btn btn-primary btn-white ' . $p_class . '" href="' . htmlspecialchars( $p_link );

	# arguments
	if($p_arg)
		echo '?';

	foreach ($p_arg as $t_arg_name => $t_arg_value)
		echo '&' . $t_arg_name . '=' . $t_arg_value;
	
	# button end
	echo '">' . $p_button_text . '</a>';
}

/**
 *	print a submit button
 *
 *	@param	string	$p_text		text displayed as the button
 *	@param	string	$p_label	the label used as the button name
 *	@param	string	$p_class	additional button class attributes
 *
 *	@return	nothing
 */
function button_submit($p_text, $p_label, $p_class = 'btn-xs btn-round'){
	echo '<input name="' . $p_label . '" class="btn btn-primary btn-white ' . $p_class . '" value="' . $p_text .'" type="submit"/>';
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
	echo '<ul class="nav nav-tabs">';

	$t_active = 'class="active"';
	foreach($p_tabs as $t_name => $t_content){
		$t_label = 'tab_' . $g_tab_label_cnt;
		$g_tab_label_cnt++;

		echo '<li ' . $t_active . '><a data-toggle="tab" href="#' . $t_label . '">' . $t_name . '</a></li>';
		$t_active = '';
	}

	echo '</ul>';

	# tab content
	echo '<div class="tab-content">';

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
 *	Print a toggleable text input, that is, by default the content of the field is
 *	shown but is not editable. Once clicked, the content is shown as text input and
 *	is made editable
 *
 *	@param	$p_label	the label of the input field
 *	@param	$p_value	the current value of the field
 *	@param	$p_class	input field class attributes
 *
 *	@return nothing
 */
function text_input_toggle($p_label, $p_value, $p_class){
	/*readonly input field, visible by default */
	echo '<div id="' . $p_label . '" class="input-toggle" style="display:block">';
	echo '<input type="text" class="' . $p_class . '" value="' . $p_value . '" style="background:transparent !important;border-color:transparent;" readonly/>';
	echo '</div>';

	/* editable input field, visible once the readonly input field has been clicked */
	echo '<div id="' . $p_label . '_rw" style="display:none">';
	echo '<input type="text" class="' . $p_class . '" value="' . $p_value . '" name="' . $p_label . '" />';
	echo '</div>';
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
function table_header($p_headrow, $p_class = '', $p_tr_attr = '', $p_th_attr = ''){
	echo '<table class="table table-bordered table-condensed ' . $p_class . '">';
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
function table_footer(){
	echo '</table>';
}

?>
