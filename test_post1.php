<?php
require_once('core.php');
require_api('gpc_api.php');

$f_text0 = gpc_get_string('text0-input', 'dummy');
$f_text1 = gpc_get_string('text1-input', 'dummy');
$f_text2 = gpc_get_string('text2-input', 'dummy');
$f_textarea = gpc_get_string('textarea-input', 'dummy');
$f_select = gpc_get_string('select-input', 'dummy');
$f_checkbox = gpc_get_bool('checkbox-input', false);


$t_res = 'test_post1    ';

$t_res .= 'text0: ' . $f_text0 . ' ';
$t_res .= 'text1: ' . $f_text1 . ' ';
$t_res .= 'text2: ' . $f_text2 . ' ';
$t_res .= 'textarea: ' . $f_textarea . ' ';
$t_res .= 'select: ' . $f_select . ' ';
$t_res .= 'checkbox: ' . ($f_checkbox ? 'true' : 'false');

echo json_encode($t_res);
?>
