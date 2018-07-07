<?php

require_once('../core.php');
require_api('authentication_api.php');
require_api('form_api.php');
require_api('elements_api.php');


$t_user_id = auth_get_current_user_id();


layout_inline_page_begin();

page_title('Change Password');

echo '<form action="settings/user_update.php" methdo"post" class="input-hover-form inline-page-form">';

form_security_purge('user_update');

input_hidden('user_id', $t_user_id);
input_hidden('cmd', 'set_pw');
echo form_security_field('user_update');


actionbar_begin();
	echo '<div class="pull-left">';
		button('Update', 'update', 'submit');
	echo '</div>';
actionbar_end();

table_begin(array(), 'no-border');
table_row_bug_info_long('Current Password:', format_password('pw_current', 'pw_current'), '20%');
table_row_bug_info_long('New Password:', format_password('pw_new0', 'pw_new0'), '20%');
table_row_bug_info_long('Confirm Password:', format_password('pw_new1', 'pw_new1'), '20%');
table_end();

echo '</form>';

layout_inline_page_end();
