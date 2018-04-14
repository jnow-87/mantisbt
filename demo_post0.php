<?php
require_once('core.php');
require_api('error_api.php');
require_api('gpc_api.php');

$f_text0 = gpc_get_string('text0', 'empty');
$f_text1 = gpc_get_string('text1', 'empty');
$f_text2 = gpc_get_string('text2', 'empty');
$f_textarea = gpc_get_string('textarea', 'empty');
$f_select = gpc_get_string('select', 'empty');
$f_checkbox = gpc_get_bool('checkbox', false);


$t_res = '';
$t_res .= $f_text0 . ' ';
$t_res .= $f_text1 . ' ';
$t_res .= $f_text2 . ' ';
$t_res .= $f_textarea . ' ';
$t_res .= $f_select . ' ';
$t_res .= ($f_checkbox ? 'true' : 'false');

json_prepare();
//json_warning('test_post: this is a warning');
json_success('test_post: ' . $t_res);
?>
