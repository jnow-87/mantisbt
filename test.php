<?php
require_once('core.php');
require_api('gpc_api.php');
require_api('layout_api.php');
require_api('elements_api.php');


# TODO
#	proper action bar for buttons and inputs
#
#	input-toggle: either toggle on hover or show cursor on hover
#	remove table from timeline
#	select_input_toggle
#	extend navbar issue search to actually perform a textual search, analog to filters
#	remove lang_get()
#	save collapse state for site reloads
#	add time estimate, time spent to bug view
#
#	pages
#
function tab_page0(){
	echo '<div class="row">';
	echo '<div class="col-md-6-left">';
	table_begin(array('col 0', 'col 1', 'col 2'), 'table-bordered table-condensed table-striped table-hover table-sortable');
	table_row(array('bug-0', 'a01', 'b02'));
	table_row(array('issue-0', 'b11', 'c12'));
	table_row(array('bug-2', 'c01', 'd02'));
	table_row(array('feature-2', 'd01', 'a02'));
	table_row(array('bug-0', 'a01', 'b02'));
	table_row(array('issue-0', 'b11', 'c12'));
	table_row(array('bug-2', 'c01', 'd02'));
	table_row(array('feature-2', 'd01', 'a02'));
	table_row(array('bug-0', 'a01', 'b02'));
	table_row(array('issue-0', 'b11', 'c12'));
	table_row(array('bug-2', 'c01', 'd02'));
	table_row(array('feature-2', 'd01', 'a02'));
	table_end();
	echo '</div>';

	echo '<div class="col-md-6-right">';
	table_begin(array('col 0', 'col 1', 'col 2'), 'table-bordered table-condensed table-striped table-hover table-sortable');
	table_row(array('bug-0', 'a01', 'b02'));
	table_row(array('issue-0', 'b11', 'c12'));
	table_row(array('bug-2', 'c01', 'd02'));
	table_row(array('feature-2', 'd01', 'a02'));
	table_row(array('bug-0', 'a01', 'b02'));
	table_row(array('issue-0', 'b11', 'c12'));
	table_row(array('bug-2', 'c01', 'd02'));
	table_row(array('feature-2', 'd01', 'a02'));
	table_row(array('bug-0', 'a01', 'b02'));
	table_row(array('issue-0', 'b11', 'c12'));
	table_row(array('bug-2', 'c01', 'd02'));
	table_row(array('feature-2', 'd01', 'a02'));
	table_end();
	echo '</div>';
	echo '</div>';
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
layout_page_begin('elements demos');

page_title("Layout elements demo");

?>
<div class="col-md-10">
	<!-- action bar -->
	<?php
	section_begin('action bar');
		actionbar_begin();

		button_link('view issue 92', 'view.php', array('id' => '92'), 'btn-xs');

		echo '<div class="pull-right">';
			echo '<div class="pull-left">';
				echo 'foo';
				hspace('5px');
				echo '<input type="text" id="foo" class="input-xs pull-right">';
			echo '</div>';

			hspace('15px');

			echo '<div class="pull-right">';
				button_link('view issue 92', 'view.php', array('id' => '92'), 'btn-xs');
			echo '</div>';
		echo '</div>';

		actionbar_end();
		actionbar_begin();

		button_link('view issue 92', 'view.php', array('id' => '92'), 'btn-xs');

		echo '<div class="pull-right">';
			echo '<div class="pull-left">';
				echo 'foo';
				hspace('5px');
				echo '<input type="text" id="foo" class="input-xs pull-right">';
			echo '</div>';

			hspace('15px');

			echo '<div class="pull-right">';
				button_link('view issue 92', 'view.php', array('id' => '92'), 'btn-xs');
			echo '</div>';
		echo '</div>';

		actionbar_end();
	section_end();
	?>

	<!-- dropdown menu -->
	<?php section_begin('dropdown demo') ?>
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
	<?php section_begin('tabs demo') ?>
	<?php tabs(array('tab 0' => 'tab_page0', 'tab 1' => 'tab_page1', 'tab2' => 'tab_page1'));?>
	<?php section_end() ?>

	<!-- link button demo -->
	<?php
	$f_link_button_input = gpc_get_string('link_button_input', '');
	?>

	<?php section_begin('link button demo') ?>
	<h4>form inputs</h4>
	<?php label('link button input:'); echo $f_link_button_input . '<br>' ?>

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

	<?php section_begin('form button demo') ?>
	<h4>form inputs</h4>
	<?php label('submit button state:'); echo $f_submit_button_state . '<br>' ?>
	<?php label('delete button state:', 'label-grey'); echo $f_delete_button_state . '<br>' ?>
	<?php label('form button input:'); echo $f_form_button_input . '<br>' ?>

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

	<?php section_begin('input text toggle demo') ?>
	<h4>form inputs</h4>
	<?php label('editable input0:', 'arrowed'); echo $f_editable_input0 . '<br>' ?>
	<?php label('editable input1:', 'arrowed-right'); echo $f_editable_input1 . '<br>' ?>
	<?php label('editable input2:', 'arrowed-in-right'); echo $f_editable_input2 ?>

	<form method="post" action="">
	<?php table_begin(array('clickable input0', 'clickable input1', 'clickable input2'), 'table-bordered table-condensed table-striped') ?>
		<tr>
			<td><?php text_input_toggle('editable_input0', $f_editable_input0, 'input-xs'); ?></td>
			<td><?php text_input_toggle('editable_input1', $f_editable_input1, 'input-xs'); ?></td>
			<td><?php text_input_toggle('editable_input2', $f_editable_input2, 'input-xs'); ?></td>
		</tr>

	<?php table_end() ?>
	<?php button_submit('submit', '') ?>
	</form>
	<?php section_end() ?>

	<?php section_begin('hover demo') ?>

	<span id="m0" class="input-toggle-master">
	<a class="" href="#">hover here</a>
	<a id="m0-commit" class="input-toggle-button" href="#"><i class="fa fa-times"></i></a>
	</span>

	<span id="imaster" class="input-toggle-master">
	<input type="text" id="imaster-input" class="input-xs input-toggle-input" value="test" readonly>
	<a id="imaster-commit" class="input-toggle-button" href="#"><i class="fa fa-check"></i></a>
	<a id="imaster-cancel" class="input-toggle-button" href="#"><i class="fa fa-times"></i></a>
	</span>

	<span id="amaster" class="input-toggle-master">
	<textarea id="amaster-input" rows="4" cols="30" class="input-xs input-toggle-input" value="test" readonly>test</textarea>
	<a id="amaster-commit" class="input-toggle-button" href="#"><i class="fa fa-check"></i></a>
	<a id="amaster-cancel" class="input-toggle-button" href="#"><i class="fa fa-times"></i></a>
	</span>

	<span id="cmaster" class="input-toggle-master">
	<input type="checkbox" id="cmaster-input" rows="4" cols="30" class="input-xs input-toggle-input" value="test0" readonly>test0
	<a id="cmaster-commit" class="input-toggle-button" href="#"><i class="fa fa-check"></i></a>
	<a id="cmaster-cancel" class="input-toggle-button" href="#"><i class="fa fa-times"></i></a>
	</span>

	<span id="rmaster" class="input-toggle-master">
	<input type="radio" name="foo" id="rmaster-input" class="input-xs input-toggle-input" readonly>test0
	<input type="radio" name="foo" id="rmaster-input" class="input-xs input-toggle-input" readonly>test1
	<input type="radio" name="foo" id="rmaster-input" class="input-xs input-toggle-input" readonly>test2
	<a id="rmaster-commit" class="input-toggle-button" href="#"><i class="fa fa-check"></i></a>
	<a id="rmaster-cancel" class="input-toggle-button" href="#"><i class="fa fa-times"></i></a>
	</span>

	<span id="smaster" class="input-toggle-master">
	<select id="smaster-input" class="input-xs input-toggle-input" readonly>
		<option value="foo">foo</option>
		<option value="bar">bar</option>
		<option value="foobar">foobar</option>
	</select>
	<a id="smaster-commit" class="input-toggle-button" href="#"><i class="fa fa-check"></i></a>
	<a id="smaster-cancel" class="input-toggle-button" href="#"><i class="fa fa-times"></i></a>
	</span>


	<style>
	.input-toggle-input[readonly]{
		background: transparent !important;
		border-color: transparent !important;
	}
	</style>



	<?php section_end() ?>
</div>

<div class="col-md-2">
	<?php
		section_begin('collapsed section', true);

		table_begin(array('table right'), 'table-bordered table-condensed table-striped', '', 'colspan=3');
		table_row(array('c00', 'c01', 'c02'));
		table_row(array('c10', 'c11', 'c12'));
		table_end();

		section_end();
	?>

	<?php
		section_begin('right column');

		echo '<div class="overflow-scroll" style="height:50px">';
		table_begin(array('table right'), 'table-bordered table-condensed table-striped', '', 'colspan=3');
		table_row(array('c00', 'c01', 'c02'), 'class="tr-url" data-url="dummy-url"');
		table_row(array('c10', 'c11', 'c12'), 'class="tr-url" data-url="dummy-url"');
		table_end();
		echo '</div>';

		section_end();
	?>
</div>

<?php
layout_page_end();
