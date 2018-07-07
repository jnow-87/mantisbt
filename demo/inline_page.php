<?php
require_once('../core.php');
require_api('gpc_api.php');
require_api('layout_api.php');
require_api('elements_api.php');

layout_inline_page_begin();
page_title('inline page');

column_begin('12');
echo '<form method="post" action="inline_page_post.php" class="input-hover-form inline-page-form">'
;
	table_begin(array('hover text'), 'table-bordered table-condensed table-striped');
	echo '<tr>';
	echo '<td>';
	echo format_text('inline_text', 'text0', 'dummy text');
	echo '</td>';
	echo '</tr>';
	table_end();

	echo format_textarea('inline_textarea', 'textarea', 'dummy text', '', 'width:100%;height:100px');

	button('submit inline', 'submit_inline_page', 'submit');
echo '</form>';
column_end();

layout_inline_page_end();

?>
