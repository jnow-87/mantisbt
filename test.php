<?php
require_once('core.php');
require_api('gpc_api.php');
require_api('layout_api.php');
require_api('elements_api.php');


# TODO
#	document elements_api
#
#	overhaul layout
#		improve layout of worklog in bug view
#		move control panel to the top
#		make use of tabs
#		remove boxes, replacing them with a more open layout
#		streamline toolbars
#		add spaces between buttons
#		which css files are used
function tab_page0(){
?>
	<table class="table table-bordered table-condensed table-striped table-hover" data-toggle="table">
		<thead style="cursor:pointer">
		<th data-sortable="true">col 0 <i class="ace-icon fa fa-sort" style="padding-left:5px"/></th>
		<th data-sortable="true">col 1 <i class="ace-icon fa fa-sort" style="padding-left:5px"/></th>
		<th data-sortable="true">col 2 <i class="ace-icon fa fa-sort" style="padding-left:5px"/></th>
		</thead>

		<tr><td> c00 </td><td> c01 </td><td> c02 </td></tr>
		<tr><td> c10 </td><td> c11 </td><td> c12 </td></tr>
	</table>
</table>

<?php
}

function tab_page1(){
	echo 'some content1';
}

?>

<?php
$t_menu0 = array(
	array('label' => 'header', 'data' => 'title'),
	array('label' => 'divider', 'data' => ''),
	array('label' => 'item0', 'data' => array('link' => 'my_view_page.php', 'icon' => 'fa-android')),
	array('label' => 'divider', 'data' => ''),
	array('label' => 'item1', 'data' => array('link' => 'worklog_summary_page.php', 'icon' => 'fa-user')),
);



layout_page_header('elements demos');
layout_page_begin();


?>
<div class="col-md-10">
	<!-- dropdown menu -->
	<?php section_start('dropdown demo') ?>
	<div>
		<?php dropdown_menu('dropdown', $t_menu0, 'grey', 'fa-android'); ?>
		<?php dropdown_menu('dropdown', $t_menu0, 'green', 'fa-user'); ?>
	</div>
	<div>
		<?php dropdown_menu('dropdown', $t_menu0); ?>
		<?php dropdown_menu('dropdown', $t_menu0, '', 'fa-user'); ?>
	</div>
	<?php section_end() ?>

	<!-- tabs -->
	<?php section_start('tabs demo') ?>
	<?php tabs(array('tab0' => 'tab_page0', 'tab1' => 'tab_page1', 'tab2' => 'tab_page1'));?>
	<?php section_end() ?>

	<!-- link button demo -->
<?php
$f_link_button_input = gpc_get_string('link_button_input', '');
?>

	<?php section_start('link button demo') ?>
	<h4>form inputs</h4>
	<?php echo 'link button input: ' . $f_link_button_input . '<br>' ?>

	<div>
		<?php button_link('view issue 92', 'view.php', array('id' => '92'), ''); ?>
		<?php button_link('view issue 92', 'view.php', array('id' => '92'), 'btn-white'); ?>
	</div>

	<div>
		<?php button_link('view issue 92', 'view.php', array('id' => '92'), 'btn-xs'); ?>
		<?php button_link('view issue 92', 'view.php', array('id' => '92'), 'btn-sm'); ?>
		<?php button_link('view issue 82', 'view.php', array('id' => '82'), 'btn-round btn-sm'); ?>
		<?php button_link('view issue 82', 'view.php', array('id' => '82'), 'btn-round btn-xs'); ?>
	</div>

	<div>
		<?php button_link('submit data to this page', '', array('link_button_input' => 'button submits data')); ?>
	</div>
	<?php section_end() ?>

	<!-- form button demo -->
	<?php
	$f_form_button_input = gpc_get_string('form_button_input', '');
	$f_submit_button_state = gpc_get_bool('submit_button', false);
	$f_delete_button_state = gpc_get_bool('delete_button', false);
	?>

	<?php section_start('form button demo') ?>
	<h4>form inputs</h4>
	<?php echo 'submit button state: ' . $f_submit_button_state . '<br>' ?>
	<?php echo 'delete button state: ' . $f_delete_button_state . '<br>' ?>
	<?php echo 'form button input: ' . $f_form_button_input . '<br>' ?>

	<form method="post" action="">
		<input type="text" name="form_button_input" placeholder="type something"/>
		<?php button_submit('submit', 'submit_button'); ?>
		<?php button_submit('delete', 'delete_button', 'btn-xs'); ?>
	</form>
	<?php section_end() ?>

	<!-- input text toggle -->
	<?php
	$f_editable_input0 = gpc_get_string('editable_input0', 'click me');
	$f_editable_input1 = gpc_get_string('editable_input1', 'click me');
	$f_editable_input2 = gpc_get_string('editable_input2', 'click me');
	?>

	<?php section_start('input text toggle demo') ?>
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
			<td><?php text_input_toggle('editable_input0', $f_editable_input0, 'input-xs'); ?></td>
			<td><?php text_input_toggle('editable_input1', $f_editable_input1, 'input-xs'); ?></td>
			<td><?php text_input_toggle('editable_input2', $f_editable_input2, 'input-xs'); ?></td>
			</tr>
		</table>

		<?php button_submit('submit', ''); ?>
	</form>
	<?php section_end() ?>
</div>

<div class="col-md-2">
	<?php section_start('collapsed section', true) ?>
	<table class="table table-bordered table-condensed table-striped">
		<thead>
		<th colspan=3>table right</th>
		</thead>

		<tr><td> c00 </td><td> c01 </td><td> c02 </td></tr>
		<tr><td> c10 </td><td> c11 </td><td> c12 </td></tr>
	</table>
	<?php section_end() ?>

	<?php section_start('right column') ?>
	<table class="table table-bordered table-condensed table-striped">
		<thead>
		<th colspan=3>table right</th>
		</thead>

		<tr><td> c00 </td><td> c01 </td><td> c02 </td></tr>
		<tr><td> c10 </td><td> c11 </td><td> c12 </td></tr>
	</table>
	<?php section_end() ?>
</div>

<?php
layout_page_end();
