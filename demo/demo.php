<?php
require_once('../core.php');
require_api('gpc_api.php');
require_api('layout_api.php');
require_api('helper_api.php');
require_api('elements_api.php');


function tab_page0(){
	echo '<div class="row">';
	column_begin('6-left');
	table_begin(array('col 0', 'col 1', 'col 2'), 'table-bordered table-condensed table-striped table-hover table-datatable');
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
	column_end();

	column_begin('6-right');
	table_begin(array('col 0', 'col 1', 'col 2'), 'table-bordered table-condensed table-striped table-hover table-datatable');
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
	column_end();
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
	array('label' => 'item0', 'data' => array('link' => helper_mantis_url('dashboard.php'), 'icon' => 'fa-android')),
	array('label' => 'divider', 'data' => ''),
	array('label' => 'item1', 'data' => array('link' => helper_mantis_url('worklog_summary_page.php'), 'icon' => 'fa-user')),
);

$t_page_title = 'Layout elements demos';



layout_page_header($t_page_title);
layout_page_begin();

page_title($t_page_title);

/* action bar */
column_begin('10');
	section_begin('action bar');
		actionbar_begin();

		button_link('view issue 92', helper_mantis_url('bug_page.php'), array('id' => '92'), 'btn-xs');

		echo '<div class="pull-right">';
			echo '<div class="pull-left">';
				echo 'foo';
				hspace('5px');
				echo '<input type="text" id="foo" class="input-xs pull-right">';
			echo '</div>';

			hspace('15px');

			echo '<div class="pull-right">';
				button_link('view issue 92', helper_mantis_url('bug_page.php'), array('id' => '92'), 'btn-xs');
			echo '</div>';
		echo '</div>';

		actionbar_end();
		actionbar_begin();

		button_link('view issue 92', helper_mantis_url('bug_page.php'), array('id' => '92'), 'btn-xs');

		echo '<div class="pull-right">';
			echo '<div class="pull-left">';
				echo 'foo';
				hspace('5px');
				echo '<input type="text" id="foo" class="input-xs pull-right">';
			echo '</div>';

			hspace('15px');

			echo '<div class="pull-right">';
				button_link('view issue 92', helper_mantis_url('bug_page.php'), array('id' => '92'), 'btn-xs');
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
		<?php button_link('view issue 92', helper_mantis_url('bug_page.php'), array('id' => '92'), ''); ?>
		<?php button_link('view issue 92', helper_mantis_url('bug_page.php'), array('id' => '92'), 'btn-white'); ?>
	</div>

	<div>
		<?php button_link('view issue 92', helper_mantis_url('bug_page.php'), array('id' => '92'), 'btn-xs'); ?>
		<?php button_link('view issue 92', helper_mantis_url('bug_page.php'), array('id' => '92'), 'btn-sm'); ?>
		<?php button_link('view issue 82', helper_mantis_url('bug_page.php'), array('id' => '82'), 'btn-round btn-sm'); ?>
		<?php button_link('view issue 82', helper_mantis_url('bug_page.php'), array('id' => '82'), 'btn-round btn-xs'); ?>
	</div>

	<div>
		<?php button_link('submit data to this page', '', array('link_button_input' => 'button submits data')); ?>
	</div>

	<?php section_end() ?>

	<!-- form button demo -->
	<?php
	$f_form_button_input = gpc_get_string('form_button_input', '');
	$f_submit_button_state = gpc_get_bool('submit_button', false);
	$f_delete_button_state = gpc_get_bool('reset_button', false);
	?>

	<?php section_begin('form button demo') ?>
	<h4>form inputs</h4>
	<?php label('submit button state:'); echo $f_submit_button_state . '<br>' ?>
	<?php label('delete button state:', 'label-grey'); echo $f_delete_button_state . '<br>' ?>
	<?php label('form button input:'); echo $f_form_button_input . '<br>' ?>

	<form method="post" action="">
		<input type="text" name="form_button_input" placeholder="type something"/>
		<?php button('submit', 'submit_button', 'submit'); ?>
		<?php button('reset', 'reset_button', 'reset', '', 'btn-xs'); ?>
	</form>
	<?php section_end() ?>

	<!-- input text toggle -->
	<?php section_begin('input hover demo') ?>

	<form id="input-hover" action="post0.php" class="input-hover-form" method="post">
	<?php
		actionbar_begin();

		echo '<div class="pull-right">';
		button('show all', 'x0', 'button', '', 'input-hover-show-all');
		button('reset', 'x1', 'button', '', 'input-hover-reset-all');
		button('submit', 'x2', 'submit', '', 'input-hover-submit-all');
		echo '</div>';
		
		actionbar_end();
	?>

		<?php table_begin(array('hover ink', 'hover text', 'hover select', 'hover checkbox'), 'table-bordered table-condensed table-striped') ?>
		<tr>
		<td><?php input_hover_element('l', format_link('lonk', '#', array(), '', 'margin-right:20px!important'), array(array('icon' => 'fa-times', 'href' => format_href(helper_mantis_url('bug_page.php'), array('id' => '89')), 'position' => 'right:4px'))); ?></td>
		<td>
			<?php input_hover_text('text0', 'dummy text 0', '', 'post1.php') ?>
			<?php input_hover_text('text1', 'dummy text 1') ?>
		</td>
		<td><?php input_hover_select('select', array('bar' => 0, 'foo' => 1, 'foobar' => 2), 'foo'); ?></td>
		<td><?php input_hover_checkbox('checkbox', false); ?></td>
		</tr>

		<?php table_end() ?>

		<?php input_hover_textarea('textarea', 'dummy text', '100%', '100px'); ?>
	</form>

	<?php section_end() ?>

	<!-- inline page -->
	<?php section_begin('inline page demo') ?>

	<form id="inline" method="post" action="post1.php" class="input-hover-form">
		<?php
		actionbar_begin();

		echo '<div class="pull-right">';
		button('submit', 'trigger_inline', 'submit');
		button('show all', 'x3', 'button', '', 'input-hover-show-all');
		button('reset', 'x4', 'button', '', 'input-hover-reset-all');
		button('submit', 'x5', 'submit', '', 'input-hover-submit-all');
		echo '</div>';
		
		actionbar_end();
		input_hidden('text0', 'v0');
		input_hidden('text1', 'v1');

		input_hover_text('text2', 'v3');

		echo format_link('open link in inline-page', 'inline_page.php', array(), 'inline-page-link');
		?>
	</form>

	<?php 
	section_end();
column_end();

column_begin('2');
	section_begin('collapsed section', true);

	table_begin(array('table right'), 'table-bordered table-condensed table-striped', '', array('colspan=3'));
	table_row(array('c00', 'c01', 'c02'));
	table_row(array('c10', 'c11', 'c12'));
	table_end();

	section_end();

	section_begin('right column');

	echo '<div class="overflow-scroll" style="height:50px">';
	table_begin(array('table right'), 'table-bordered table-condensed table-striped', '', array('colspan=3'));
	table_row(array('c00', 'c01', 'c02'), 'class="tr-url" data-url="dummy-url"');
	table_row(array('c10', 'c11', 'c12'), 'class="tr-url" data-url="dummy-url"');
	table_end();
	echo '</div>';

	section_end();
column_end();

layout_page_end();
