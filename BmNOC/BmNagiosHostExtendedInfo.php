<?php
require_once("BmMySQL.php");

class BmNagiosHostExtendedInfo{

	public function update($ext_info) {
		//var_dump($beNagiosHosts); //exit;
		$query = "UPDATE nagios_hosts_extended_info SET ";
		$query .= "icon_image = '$ext_info->icon_image', action_url = '$ext_info->action_url' ";
		$query .= "where host_id = $ext_info->host_id";
		$bmMySQL = new BmMySQL();
		$beBase = $bmMySQL->query($query, "BeNagiosHostExtendedInfo", "opcfg");
		//var_dump($beBase);
		return $beBase;	
	}

	public function query($criterion=null, $order=null, $limit=null) {
		$query = "SELECT nagios_hosts_extended_info.*, 0 AS ErrStatus FROM nagios_hosts_extended_info " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeNagiosHostExtendedInfo", "opcfg");
	}
}


?>