<?php
require_once('core.php');
require_api('gpc_api.php');
require_api('layout_api.php');
require_api('elements_api.php');


$f_btn_text = gpc_get_string('confirm_btn', '');
$f_redirect = gpc_get_string('confirm_redirect', '');
$f_msg = gpc_get_string('confirm_msg', '');
$f_msg_class = gpc_get_string('confirm_msg_class', '');


layout_inline_page_begin();
page_title('Confirmation');

echo '<div class="center">';
echo '<div class="' . $f_msg_class . '">' . $f_msg . '</div><br>';

button('Cancel', 'cancel-confirm', 'button', '',  'inline-page-close');
button_link($f_btn_text, $f_redirect);
echo '</div>';

layout_inline_page_end();
