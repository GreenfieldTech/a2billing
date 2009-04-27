<?php

/*
 * Following fuctions return the latest title to add as
 * agi-conf(title_number) for Global configurations and List of configurations
 * Tables : cc_confi_group
 * Operations : SELECT
 *
 * It will browse the agi-conf% existing find the new one to add (last or missing agi-conf)
 * return config_group_id of the first agi-conf% existing
 *  
 */

function agi_confx_title($handle=null)
{
	if (empty($handle)) {
		$handle = DbConnect();
	}
	$instance_table = Table::getInstance();

	$QUERY =  "SELECT id, group_title, group_description FROM cc_config_group WHERE group_title like '%agi-conf%' ORDER BY group_title";
	$result = $instance_table -> SQLExec ($handle, $QUERY);
	
	if (is_array($result)) {
		$num_cur = count($result);
		for ($i=0;$i<$num_cur;$i++) {
			$config_group_id = $result[0][0];
			$group_title[] = $result[$i][1];
			$description = $result[0][2];
		}
	}
	foreach($group_title as $value) {
		$agi_number[] = (int)substr($value, -1);
	}
	$len_agi_array = sizeof($agi_number);
	$agi_conf_number = $len_agi_array + 1;
	for($i=1; $i <= $len_agi_array; $i++) {
		if ($i != $agi_number[$i - 1]) {
			$agi_conf_number = $i;
			break;
		}
	}
	$config_group = array();
	$config_group[0] = "agi-conf".$agi_conf_number;
	$config_group[1] = $config_group_id;
	$config_group[2] = $description;
	$config_group[3] = $agi_number[0];
	return $config_group;
}


/*
 * Following function will generate agi-confx,
 * Duplicate all the configurations of agi-conf1 and produce agi-confx
 * Subquery is also used in this function to improve functional response.
 * Operations : SELECT , INSERT
 * Tables : cc_config, cc_config_group
 */

function add_agi_confx($handle = null)
{
	if (empty($handle)){
		$handle = DbConnect();
	}
	$instance_table = Table::getInstance();
	$config_group = array();
	$config_group  = agi_confx_title(); // calling function  to generate agi-conf(title_number)
	$group_title = $config_group[0];
	$config_group_id = $config_group[1];
	$description = $config_group[2];
	$base_group_title = 'agi-conf'.$config_group[3];
	
	$value = "'$group_title', '$description'";
	$func_fields = "group_title, group_description";
	$func_table = 'cc_config_group';
	$id_name = "id";
	$inserted_id = $instance_table -> Add_table ($handle, $value, $func_fields, $func_table, $id_name);

	$value = "SELECT config_title, config_key, config_value, config_description, config_valuetype, '$group_title', config_listvalues FROM cc_config WHERE config_group_title = '$base_group_title'";
	$func_fields = "config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues";
	$func_table = 'cc_config';
	$id_name = "";
	$subquery = true;
	$result = $instance_table -> Add_table ($handle, $value, $func_fields, $func_table, $id_name, $subquery);
	return $group_title;
}


/*
 * This function delete agi-confx, all its global configurations and list of configurations
 * Operations : DELETE
 * Tables : cc_config, cc_config_group
 */
function delete_agi_confx($agi_conf)
{
	if (empty($handle)){
		$handle = DbConnect();
	}
	$instance_table = Table::getInstance();

	$clause = "group_title = '$agi_conf'";
	$fun_table = "cc_config_group";
	$result = $instance_table -> Delete_table ($handle, $clause, $fun_table);

	$clause = "config_group_title = '$agi_conf'";
	$fun_table = "cc_config";
	$result = $instance_table -> Delete_table ($handle, $clause, $fun_table);

	return $result;
}


