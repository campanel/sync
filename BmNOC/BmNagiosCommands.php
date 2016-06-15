<?php
require_once("BmMySQL.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeNagiosCommands.php");

class BmNagiosCommands{

		
	public function getIdByName($name) {
		$command =   BmNagiosCommands::query("where command_name = '".$name."'", "", "");
        return $command[0]->command_id;
	}
	
	public function query($criterion=null, $order=null, $limit=null) {
		
		$query = "SELECT *, 0 AS ErrStatus FROM nagios_commands " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeNagiosCommands", "opcfg");
	}
}