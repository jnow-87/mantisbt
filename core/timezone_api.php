<?php

/**
 *	return array of available time zones
 */
function timezone_list(){
	$t_identifiers = timezone_identifiers_list(DateTimeZone::ALL);
	$t_res = array();

	foreach($t_identifiers as $t_identifier)
		$t_res[$t_identifier] = $t_identifier;

	ksort($t_res);

	return $t_res;
}
