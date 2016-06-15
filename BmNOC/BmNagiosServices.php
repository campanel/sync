<?php
require_once("BmMySQL.php");

class BmNagiosServices{
    public function get_service_id_by_host_id_and_service_description($host_id, $service_description){
        $query = "select service_id from nagios_services where service_description = '".$service_description."' and host_id = '".$host_id."'";
        $bmMySQL = new BmMySQL();
        return $bmMySQL->query($query, "BeNagiosServices", "opcfg");
    }

	public function get_service_by_host_name_and_service_description($host, $service){
		$query = "select * from nagios_services left join nagios_hosts on nagios_hosts.host_id = nagios_services.host_id ";
        $query .= " where service_description = '".$service."' and host_name = '".$host."'";
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeNagiosServices", "opcfg");
	}

	public function getServicesAvailByHostID($host_id){
		$query = "select * from nagios_services where host_id = '".$host_id."' and (process_perf_data is NULL or process_perf_data = '0') ";
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeNagiosServices", "opcfg");
		//return $foo[0];
	}
	
	public function query($criterion=null, $order=null, $limit=null) {
		$query = "SELECT nagios_services.*, 0 AS ErrStatus FROM nagios_services " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeNagiosServices", "opcfg");
	}
	
	
	public function update($aic,$parameters) {
		
		$query = "UPDATE nagios_services SET ";
		//Monta query apenas com os campos que contenham valor (!= null)
		foreach($parameters as $parameter) {
			$query .= $parameter . " = '" . $aic->$parameter . "', ";
		} 
		
		$query = substr($query, 0, strlen($query) - 2);
		$query .= " WHERE service_id = " . $aic->service_id;
		
		$bmMySQL = new BmMySQL();
		//var_dump($query); exit;
		$beBase = $bmMySQL->query($query, "BeNagiosServices", "opcfg");
		
		if ($beBase->ErrStatus != 0) {
            $aic->ErrStatus = $beBase->ErrStatus;
            Bplog::save("Erro UpdateAICInfos: ".$aic->service_description, 2);
		}
        $aic->ErrStatus = $beBase->ErrStatus;
		return $aic;
	}

}


?>