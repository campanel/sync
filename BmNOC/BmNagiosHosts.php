<?php
require_once("BmMySQL.php");
//require_once("BpNagiosServices.php");

class BmNagiosHosts{

	public function query($criterion, $order, $limit) {
		$query = "SELECT host_id, host_name, alias, address, use_template_id, 0 AS ErrStatus FROM nagios_hosts " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeNagiosHosts", "opcfg");
	}

	public function update($beNagiosHosts,$parameters) {
		$query = "UPDATE nagios_hosts SET ";
		//Monta query apenas com os campos que contenham valor (!= null)
		foreach($parameters as $parameter) {
			$query .= $parameter . " = '" . $beNagiosHosts->$parameter . "', ";
		}
		$query = substr($query, 0, strlen($query) - 2);
		$query .= " WHERE host_id = " . $beNagiosHosts->host_id;
		$bmMySQL = new BmMySQL();
		//var_dump($query); exit;
		$beBase = $bmMySQL->query($query, "BeNagiosHosts", "opcfg");
		if ($beBase->ErrStatus != 0) {
			$beNagiosHosts->ErrClasse = $beBase->ErrClasse;
			$beNagiosHosts->ErrMensagem = $beBase->ErrMensagem;
			$beNagiosHosts->ErrMetodo = $beBase->ErrMetodo;
			$beNagiosHosts->ErrTitulo = $beBase->ErrTitulo;
			$beNagiosHosts->ErrStatus = $beBase->ErrStatus;
            Bplog::save($beBase->ErrTitulo." ".$beBase->ErrClasse ." ". $beBase->ErrMetodo." ".$beBase->ErrMensagem, 2);
		}
		return $beNagiosHosts;
	}
}


?>