<?php
require_once('core.php');
require_api('gpc_api.php');
require_api('layout_api.php');
require_api('elements_api.php');

layout_inlinepage_begin();
page_title('inline page');

echo '<div class="col-md-12">';
echo '<form method="post" action="demo_inline_page_post.php" class="input-hover-form inline-page-form">'
;
	table_begin(array('hover text'), 'table-bordered table-condensed table-striped');
	echo '<tr>';
	echo '<td>';
	echo format_text('inline_text', 'inline_text', 'dummy text');
	echo '</td>';
	echo '</tr>';
	table_end();

	echo format_textarea('inline_textarea', 'inline_textarea', 'dummy text', '', 'width:100%;height:100px');

	button('submit inline', 'submit_inline_page', 'submit');
echo '</form>';
echo '</div>';

layout_inlinepage_end();

?>
