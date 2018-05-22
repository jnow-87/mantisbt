<?php

if(!defined('INCLUDE_WORKFLOW'))
 	return;

require_once('core.php');
require_api('workflow_api.php');
require_api('elements_api.php');


section_begin('Configuration');
	echo '<div class="col-md-3">';
		table_begin(array(), 'no-border');
		table_row_bug_info_long('New issue status:', get_enum_element('status', config_get('bug_submit_status')), '40%');
		table_row_bug_info_long('Reopen issue status:', get_enum_element('status', config_get('bug_reopen_status')), '40%');
		table_row_bug_info_long('Reopen resolution:', get_enum_element('resolution', config_get('bug_reopen_resolution')), '40%');
		table_row_bug_info_long('Resolve issue status:', get_enum_element('status', config_get('bug_resolved_status_threshold')), '40%');
		table_row_bug_info_long('Readonly issue status:', get_enum_element('status', config_get('bug_readonly_status_threshold')), '40%');
		table_row_bug_info_long('Assigned issue status:', get_enum_element('status', config_get('bug_assigned_status')), '40%');
		table_end();
	echo '</div>';

	echo '<div class="col-md-3">';
		table_begin(array(), 'no-border');
		table_row_bug_info_long('Reporter can close:', config_get('allow_reporter_close') ? 'yes' : 'no', '50%');
		table_row_bug_info_long('Reporter can reopen:', config_get('allow_reporter_reopen') ? 'yes' : 'no', '50%');
		table_row_bug_info_long('Update status when assigning:', config_get('auto_set_status_to_assigned') ? 'yes' : 'no', '50%');
		table_row_bug_info_long('Reporter can access others\' issues:', config_get('limit_reporters') ? 'no' : 'yes', '50%');
		table_end();
	echo '</div>';
section_end();

section_begin('Transistions');
	$t_states = status_list();
	$t_transitions = config_get('status_enum_workflow');
	$t_access_thr = config_get('set_status_threshold');
	$t_workflow = workflow_parse(config_get('status_enum_workflow', null, ALL_USERS, helper_get_current_project()));

	$t_head = array('from -> to');
	$t_td_attr = array('class="thead" width="10%"');

	foreach($t_states as $t_state_name => $t_state_id){
		$t_td_attr[] = 'class="center"';
		$t_head[] = $t_state_name;
	}

	$t_head[] = 'Default Transition';
	$t_head[] = 'Access Level for Status';
	$t_head[] = 'Validation Comment';

	table_begin($t_head, 'table-condensed table-hover no-border', '', $t_td_attr);

	foreach($t_states as $t_src_name => $t_src_id){
		/* transitions */
		$t_data = array($t_src_name);

		foreach($t_states as $t_tgt_name => $t_tgt_id){
			if(MantisEnum::hasValue($t_transitions[$t_src_id], $t_tgt_id))
				$t_data[] = format_icon('fa-times');
			else
				$t_data[] = '';
		}

		/* details */
		if(empty($t_transitions[$t_src_id]))
			$t_data[] = '';
		else
			$t_data[] = get_enum_element('status', explode(':', $t_transitions[$t_src_id])[0]);

		$t_data[] = get_enum_element('access_levels', $t_access_thr[$t_src_id]);

		/* validation */
		$t_comment = '';

		// edge to itself
		if(isset($t_workflow['exit'][$t_src_id][$t_src_id]))
			$t_comment .= '<span class="text-warning">implied edge to itself does not need to be specified</span>'; 

		// unreachable
		if(count($t_workflow['entry'][$t_src_id]) == 0)
			$t_comment .= '<span class="text-danger">unreachable</span>'; 

		// no exit
		if(count($t_workflow['exit'][$t_src_id]) == 0)
			$t_comment .= '<span class="text-danger">no exit</span>'; 

		if($t_comment == '')
			$t_comment .= '<span class="text-success">ok</span>'; 

		$t_data[] = $t_comment;


		table_row($t_data, '', $t_td_attr);
	}

	table_end();
section_end();
