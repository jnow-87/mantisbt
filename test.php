<?php
require_once('core.php');
require_api('collapse_api.php');
require_api('config_api.php');
require_api('database_api.php');
require_api('filter_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('lang_api.php');
require_api('string_api.php');
require_api('utility_api.php');
require_api('print_api.php');
require_api('access_api.php');
require_api('worklog_api.php');
require_api('html_api.php');
require_api('project_api.php');
require_api( 'timeline_api.php' );




# TODO
#	overhaul layout
#		improve layout of worklog in bug view
#		move control panel to the top
#		make use of tabs
#		remove boxes, replacing them with a more open layout
#		streamline toolbars
#		add spaces between buttons
#		which css files are used
function content0(){
?>
	<table class="table table-bordered table-condensed table-striped table-hover" data-toggle="table">
		<thead style="cursor:pointer">
		<th data-sortable="true">col 0 <i class="ace-icon fa fa-sort" style="padding-left:5px"/></th>
		<th data-sortable="true">col 1 <i class="ace-icon fa fa-sort" style="padding-left:5px"/></th>
		<th data-sortable="true">col 2 <i class="ace-icon fa fa-sort" style="padding-left:5px"/></th>
		</thead>

		<tr>
		<td> c00 </td>
		<td> c01 </td>
		<td> c02 </td>
		</tr>
		<tr>
		<td> c10 </td>
		<td> c11 </td>
		<td> c12 </td>
		</tr>
	</table>
</table>

<?php
}

function content1(){
	echo 'some content1';
}

function print_tabs($p_tabs){
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

function print_menu($p_title, $p_items, $p_color = '', $p_icon = ''){
	$t_padding_button = '0px';
	$t_padding_icon = '0px';

	if($p_color == ''){
		$t_padding_button = '10px';
		$t_padding_icon = '5px';
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
		echo '<i class="ace-icon fa ' . $p_icon . '" style="padding-right:' . $t_padding_icon . '"></i>';

	# title
	echo $p_title . '<i class="ace-icon fa fa-angle-down" style="padding-left:' . $t_padding_icon . '"></i>';
	echo '</a>';

	# menu items
	echo '<ul class="dropdown-menu">';

	foreach($p_items as $t_name => $t_data){
		if($t_name == 'header'){
			echo '<li class="dropdown-header">test</li>';
		}
		else if($t_name == 'divider'){
			echo '<li class="divider"/>';
		}
		else{
			echo '<li><a class="dropdown-item" href="' . $t_data['link'] . '">';
			echo '<i class="ace-icon fa ' . $t_data['icon'] . '" style="padding-right:5px"></i>' . $t_name;
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
function print_section_start($p_heading, $p_collapsed = false){
	global $g_section_label_cnt;

	$g_section_label_cnt++;
	$t_label = 'label_' . $g_section_label_cnt;

	echo '<hr class="hr-text ' . ($p_collapsed ? 'collapsed' : '') . '" data-content="' . $p_heading . '" data-toggle="collapse" data-target="#' . $t_label . '_target">';
	echo '<div id="' . $t_label . '_target" class="collapse ' . (!$p_collapsed ? 'in' : '') . '">';
}

function print_section_end(){
	echo '</div>';
}


/**
 * print a HTML link with a button look
 * @param string  $p_link       The page URL.
 * @param string  $p_url_text   The displayed text for the link.
 * @param string  $p_class      The CSS class of the link.
 * @param array   $p_arg		array of <key> <value> pairs
 * @return void
 */
function print_linkbutton( $p_button_text, $p_link, $p_arg = array(), $p_class = 'btn-xs btn-round') {
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


function print_formbutton($p_text, $p_label, $p_class = 'btn-xs btn-round'){
	echo '<input name="' . $p_label . '" class="btn btn-primary btn-white ' . $p_class . '" value="' . $p_text .'" type="submit"/>';
}

function print_text_input_toggle($p_label, $p_value, $p_class){
?>
	<!-- readonly input field, visible by default -->
	<div id="<?php echo $p_label ?>_ro" onclick="toggle_visibility('<?php echo $p_label ?>')" style="display:block">
		<input type="text" class="<?php echo $p_class ?>" value="<?php echo $p_value ?>" style="background:transparent !important;border-color:transparent;" readonly/>
	</div>

	<!-- editable input field, visible once the readonly input field has been clicked -->
	<div id="<?php echo $p_label ?>_rw" style="display:none">
		<input type="text" class="<?php echo $p_class ?>" value="<?php echo $p_value ?>" name="<?php echo $p_label ?>" />
	</div>
<?php
}

?>
<script>
function toggle_visibility(id){
	var rw = document.getElementById(id + "_rw");

	document.getElementById(id + "_ro").style.display = "none";
	rw.style.display = "block";
}
</script>
<?php



$t_tabs = array(
	'tab0' => 'content0',
	'tab1' => 'content1',
	'tab2' => 'content1',
);

$t_menu0 = array(
	'header' => 'title',
	'item0' => array('link' => 'my_view_page.php', 'icon' => 'fa-android'),
	'divider' => '',
	'item1' => array('link' => 'worklog_summary_page.php', 'icon' => 'fa-user'),
);



layout_page_header(lang_get('worklog_link'));
layout_page_begin();


?>
<div class="col-md-10">
	<!-- dropdown menu -->
	<?php print_section_start('dropdown demo') ?>
	<div>
		<?php print_menu('dropdown', $t_menu0, 'grey', 'fa-android'); ?>
		<?php print_menu('dropdown', $t_menu0, 'green', 'fa-user'); ?>
	</div>
	<div>
		<?php print_menu('dropdown', $t_menu0); ?>
		<?php print_menu('dropdown', $t_menu0, '', 'fa-user'); ?>
	</div>
	<?php print_section_end() ?>

	<!-- tabs -->
	<?php print_section_start('tabs demo') ?>
	<?php print_tabs($t_tabs) ?>
	<?php print_section_end() ?>

	<!-- link button demo -->
<?php
$f_link_button_input = gpc_get_string('link_button_input', '');
?>

	<?php print_section_start('link button demo') ?>
	<h4>form inputs</h4>
	<?php echo 'link button input: ' . $f_link_button_input . '<br>' ?>

	<div>
		<?php print_linkbutton('view issue 92', 'view.php', array('id' => '92'), ''); ?>
		<?php print_linkbutton('view issue 92', 'view.php', array('id' => '92'), 'btn-white'); ?>
	</div>

	<div>
		<?php print_linkbutton('view issue 92', 'view.php', array('id' => '92'), 'btn-xs'); ?>
		<?php print_linkbutton('view issue 92', 'view.php', array('id' => '92'), 'btn-sm'); ?>
		<?php print_linkbutton('view issue 82', 'view.php', array('id' => '82'), 'btn-round btn-sm'); ?>
		<?php print_linkbutton('view issue 82', 'view.php', array('id' => '82'), 'btn-round btn-xs'); ?>
	</div>

	<div>
		<?php print_linkbutton('submit data to this page', '', array('link_button_input' => 'button submits data')); ?>
	</div>
	<?php print_section_end() ?>

	<!-- form button demo -->
<?php
$f_form_button_input = gpc_get_string('form_button_input', '');
$f_submit_button_state = gpc_get_bool('submit_button', false);
$f_delete_button_state = gpc_get_bool('delete_button', false);
?>

	<?php print_section_start('form button demo') ?>
	<h4>form inputs</h4>
	<?php echo 'submit button state: ' . $f_submit_button_state . '<br>' ?>
	<?php echo 'delete button state: ' . $f_delete_button_state . '<br>' ?>
	<?php echo 'form button input: ' . $f_form_button_input . '<br>' ?>

	<form method="post" action="">
		<input type="text" name="form_button_input" placeholder="type something"/>
		<?php print_formbutton('submit', 'submit_button'); ?>
		<?php print_formbutton('delete', 'delete_button', 'btn-xs'); ?>
	</form>
	<?php print_section_end() ?>

	<!-- input text toggle -->
<?php
$f_editable_input0 = gpc_get_string('editable_input0', 'click me');
$f_editable_input1 = gpc_get_string('editable_input1', 'click me');
$f_editable_input2 = gpc_get_string('editable_input2', 'click me');
?>

	<?php print_section_start('input text toggle demo') ?>
	<h4>form inputs</h4>
	<?php echo 'editable input0: ' . $f_editable_input0 . '<br>' ?>
	<?php echo 'editable input1: ' . $f_editable_input1 . '<br>' ?>
	<?php echo 'editable input2: ' . $f_editable_input2 ?>

	<form method="post" action="">
		<table class="table table-bordered table-condensed table-striped">
			<thead>
			<th>clickable input0</th>
			<th>clickable input1</th>
			<th>clickable input2</th>
			</thead>

			<tr>
			<td><?php print_text_input_toggle('editable_input0', $f_editable_input0, 'input-xs'); ?></td>
			<td><?php print_text_input_toggle('editable_input1', $f_editable_input1, 'input-xs'); ?></td>
			<td><?php print_text_input_toggle('editable_input2', $f_editable_input2, 'input-xs'); ?></td>
			</tr>
		</table>

		<?php print_formbutton('submit', ''); ?>
	</form>
	<?php print_section_end() ?>
</div>

<div class="col-md-2">
	<?php print_section_start('collapsed section', true) ?>
	<table class="table table-bordered table-condensed table-striped">
		<thead>
		<th colspan=3>table right</th>
		</thead>

		<tr>
		<td> c00 </td>
		<td> c01 </td>
		<td> c02 </td>
		</tr>
		<tr>
		<td> c10 </td>
		<td> c11 </td>
		<td> c12 </td>
		</tr>
	</table>
	<?php print_section_end() ?>

	<?php print_section_start('right column') ?>
	<table class="table table-bordered table-condensed table-striped">
		<thead>
		<th colspan=3>table right</th>
		<tr>
		</thead>

		<td> c00 </td>
		<td> c01 </td>
		<td> c02 </td>
		</tr>
		<tr>
		<td> c10 </td>
		<td> c11 </td>
		<td> c12 </td>
		</tr>
	</table>
	<?php print_section_end() ?>
</div>

<?php
layout_page_end();
