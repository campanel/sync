<?php
require_once("BmMySQL.php");

class BmNagiosServicesCheckCommandParameters{

	public function insere($service_id, $parameter) {
		$bmMySQL = new BmMySQL();
		//var_dump(mysql_escape_string($beNagiosServices->command_parameter));
		$query = "INSERT INTO nagios_services_check_command_parameters ( service_id , parameter)
			VALUES (".$service_id.",
			'".mysql_escape_string($parameter)."' );";
		//var_dump($query);
        return $bmMySQL->query($query, "BeNagiosServicesCheckCommandParameters", "opcfg");
	}

	public function query($criterion=null, $order=null, $limit=null) {
		$query = "SELECT nagios_services_check_command_parameters.*, 0 AS ErrStatus FROM nagios_services_check_command_parameters " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeNagiosServicesCheckCommandParameters", "opcfg");
	}

	public function deleteByService_id($service_id) {
        $foo = BmNagiosServicesCheckCommandParameters::execQuery(
            'DELETE FROM nagios_services_check_command_parameters WHERE service_id = '.
            $service_id , "BeNagiosServicesCheckCommandParameters", "opcfg"
        );
		return $foo;
	}

	public function execQuery($query, $class, $database){
		$bmMySQL = new BmMySQL();
		$beBase = $bmMySQL->query($query, $class, "opcfg");
		if ($beBase->ErrStatus != 0) {
			throw new Exception('Error in query: '.$query.' ');
		}
		else{
			return $beBase;
		}
	}
}

?>