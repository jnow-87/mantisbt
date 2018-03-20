<?php
function tabs($p_tabs){
	# tab bar
	echo '<ul class="nav nav-tabs">';

	$t_active = 'class="active"';

	foreach($p_tabs as $t_name => $t_content){
		echo '<li ' . $t_active . '><a data-toggle="tab" href="#' . $t_name . '">' . $t_name . '</a></li>';
		$t_active = '';
	}

	echo '</ul>';

	# tab content
	echo '<div class="tab-content">';

	$t_active = 'in active';

	foreach($p_tabs as $t_name => $t_content){
		echo '<div id="' . $t_name . '" class="tab-pane fade ' . $t_active . '">';
		$t_active = '';
		echo $t_content();
		echo '</div>';
	}

	echo '</div>';
}

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

$g_section_label_cnt = 0;

function section_start($p_heading, $p_collapsed = false){
	global $g_section_label_cnt;

	$g_section_label_cnt++;
	$t_label = 'label_' . $g_section_label_cnt;

	echo '<hr class="hr-text ' . ($p_collapsed ? 'collapsed' : '') . '" data-content="' . $p_heading . '" data-toggle="collapse" data-target="#' . $t_label . '_target">';
	echo '<div id="' . $t_label . '_target" class="collapse ' . (!$p_collapsed ? 'in' : '') . '">';
}

function section_end(){
	echo '</div>';
}

function hspace($p_space){
	echo '<span style="padding-right:' . $p_space . '"></span>';
}

function page_title($p_title){
	echo '<hr class="hr-page-title" data-content="' . $p_title . '"></hr>';
}


/**
 * print a HTML link with a button look
 * @param string  $p_link       The page URL.
 * @param string  $p_url_text   The displayed text for the link.
 * @param string  $p_class      The CSS class of the link.
 * @param array   $p_arg		array of <key> <value> pairs
 * @return void
 */
function button_link( $p_button_text, $p_link, $p_arg = array(), $p_class = 'btn-xs btn-round') {
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


function button_submit($p_text, $p_label, $p_class = 'btn-xs btn-round'){
	echo '<input name="' . $p_label . '" class="btn btn-primary btn-white ' . $p_class . '" value="' . $p_text .'" type="submit"/>';
}

function text_input_toggle($p_label, $p_value, $p_class){
?>
	<!-- readonly input field, visible by default -->
	<div id="<?php echo $p_label ?>" class="input-toggle" style="display:block">
		<input type="text" class="<?php echo $p_class ?>" value="<?php echo $p_value ?>" style="background:transparent !important;border-color:transparent;" readonly/>
	</div>

	<!-- editable input field, visible once the readonly input field has been clicked -->
	<div id="<?php echo $p_label ?>_rw" style="display:none">
		<input type="text" class="<?php echo $p_class ?>" value="<?php echo $p_value ?>" name="<?php echo $p_label ?>" />
	</div>
<?php
}

function label($p_name, $p_class = ''){
?>
	<span class="label label-default <?php echo $p_class ?>"><?php echo $p_name ?></span>
<?php
}

?>
