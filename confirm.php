<?php
require_once('core.php');
require_api('gpc_api.php');
require_api('layout_api.php');
require_api('elements_api.php');


$f_btn_text = gpc_get_string('confirm_btn', '');
$f_redirect = gpc_get_string('confirm_redirect', '');
$f_arg_keys = explode('|', gpc_get_string('confirm_arg_keys', ''));
$f_arg_values = explode('|', gpc_get_string('confirm_arg_values', ''));
$f_msg = gpc_get_string('confirm_msg', '');
$f_msg_class = gpc_get_string('confirm_msg_class', '');


$f_args = array();

for($i=0; $i<count($f_arg_keys); $i++)
	$f_args[$f_arg_keys[$i]] = $f_arg_values[$i];


layout_inline_page_begin();
page_title('Confirmation');

echo '<div class="center">';
alert($f_msg_class, $f_msg);

echo '<form action="' . $f_redirect . '" method="post" class="input-hover-form input-hover-form-reload">';
	for($i=0; $i<count($f_arg_keys); $i++)
		input_hidden($f_arg_keys[$i], $f_arg_values[$i]);

button('Cancel', 'cancel-confirm', 'button', '',  'inline-page-close');
button($f_btn_text, 'submit', 'submit');
//button_link($f_btn_text, $f_redirect, $f_args);
echo '</form>';
echo '</div>';

layout_inline_page_end();
