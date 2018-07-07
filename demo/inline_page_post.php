<?php
require_once('../core.php');
require_api('error_api.php');
require_api('gpc_api.php');

$f_text = gpc_get_string('text0', 'dummy');
$f_textarea = gpc_get_string('textarea', 'dummy');

$t_res = '';
$t_res .= 'text: ' . $f_text . '<br>';
$t_res .= 'textarea: ' . $f_textarea . '<br>';

json_prepare();
json_success('inline_post: ' . $t_res);
?>
