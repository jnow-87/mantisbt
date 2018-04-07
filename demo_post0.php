<?php
require_once('core.php');
require_api('error_api.php');
require_api('gpc_api.php');

$f_text0 = gpc_get_string('text0', 'dummy');
$f_text1 = gpc_get_string('text1', 'dummy');
$f_text2 = gpc_get_string('text2', 'dummy');
$f_textarea = gpc_get_string('textarea', 'dummy');
$f_select = gpc_get_string('select', 'dummy');
$f_checkbox = gpc_get_bool('checkbox', false);


$t_res = '';
$t_res .= 'text0: ' . $f_text0 . '<br>';
$t_res .= 'text1: ' . $f_text1 . '<br>';
$t_res .= 'text2: ' . $f_text2 . '<br>';
$t_res .= 'textarea: ' . $f_textarea . '<br>';
$t_res .= 'select: ' . $f_select . '<br>';
$t_res .= 'checkbox: ' . ($f_checkbox ? 'true' : 'false');

json_prepare();
json_warning('test_post: this is a warning');
json_success('test_post: ' . $t_res);
?>
