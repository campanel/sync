<?php
require_once("BmMySQL.php");

class BmSnmpCommunities{

	public function Insere($beSnmpCommunities) {
		//arrumar
		
		$bmMySQL = new BmMySQL();
		$query = "INSERT INTO opcfg.snmp_communities (snmp_version, community, active) values( '".$beSnmpCommunities->snmp_version."', '".$beSnmpCommunities->community."', 1)";

		//var_dump($query);
		$beBase = $bmMySQL->query($query, "BeSnmpCommunities", "opcfg");
		
		if ($beBase->ErrStatus != 0) {
            $beScats = new BeScats();
			$beScats->ErrMensagem = "Retorno do Metodo: query";
			$beScats->ErrTitulo = $beBase->ErrTitulo;
			$beScats->ErrClasse = "BmSnmpCommunities";
			$beScats->ErrMetodo = "Insere";
			$beScats->ErrStatus = $beBase->ErrStatus;
			Bplog::save($beScats->ErrTitulo.": Class: ".$beScats->ErrClasse." - Metodo: ".$beScats->ErrMetodo." - Mensagem: Retorno de Class: BmMySQL - Metodo: Query ", 2);
		
		} else {
            $beScats = new BeScats();
			$beScats->community_id = $beBase->ErrMensagem;
			$beScats->ErrStatus = 0;
		}
		
		return $beScats;
	}
	
	public function Lista($criterion=null, $order=null, $limit=null) {
		//arrumar
		$query = "SELECT community_id, community, 0 AS ErrStatus FROM snmp_communities " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeSnmpCommunities", "opcfg");
	}

	public function Delete($beScats) {
		//arrumar
		try{
			
			BmScats::execQuery('DELETE FROM  scats where id = '.$beScats->community_id , "beScats", "opmon4");
			return $beScats;	

		}catch(Exception $e){
			$beScats->ErrMensagem = $e->getMessage();
			$beScats->ErrTitulo = "Erro";
			$beScats->ErrClasse = "BmScats";
			$beScats->ErrMetodo = "Delete";
			$beScats->ErrStatus = 1;
            Bplog::save($beScats->ErrTitulo.": Class: ".$beScats->ErrClasse." - Metodo: ".$beScats->ErrMetodo." - ".$beScats->ErrMensagem,2);
		}
		return $beScats;
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