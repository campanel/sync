<?php
require_once("BmMySQL.php");

class BmScatTypes{

	public function Insere($beScatTypes) {
		//var_dump("\n\nISERCAO DO BANCO\n\n");
		//var_dump($beScatTypes);

		$bmMySQL = new BmMySQL();
		$query = "INSERT INTO scat_types values('','".$beScatTypes->scat_type_name."','".$beScatTypes->host_id."',".$beScatTypes->host_template_id.",'".$beScatTypes->service_template_id."')";
		//var_dump($query);
		$beBase = $bmMySQL->query($query, "BeScatTypes", "opcfg");
		
		if ($beBase->ErrStatus != 0) {
			$beBase->ErrMensagem = "Retorno do Metodo: query";
			$beBase->ErrClasse = "BmScatTypes";
			$beBase->ErrMetodo = "Insere";
			Bplog::save($beBase->ErrTitulo.": Class: ".$beBase->ErrClasse." - Metodo: ".$beBase->ErrMetodo." - Mensagem: Retorno de Class: BmMySQL - Metodo: Query ",2);
		
		} else {
			$beScatTypes->scat_type_id = $beBase->ErrMensagem;
			$beScatTypes->ErrStatus = 0;
		}
		
		return $beScatTypes;
	}
	
	public function getAll() {
		return  BmScatTypes::Lista();
	}

	public function getByName($name) { 
		return  BmScatTypes::Lista("where scat_type_name = '".$name."'", "", "");
	}
	
	public function Lista($criterion=null, $order=null, $limit=null) {
		
		$query = "SELECT *, 0 AS ErrStatus FROM scat_types " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeScatTypes", "opcfg");
	}

	public function Update($beScatTypes) {
		//var_dump($beNagiosHosts); //exit;
		$query = "UPDATE scat_types SET ";
		$query .= "host_id = '".$beScatTypes->host_id."' ";
		$query .= "where scat_type_id = '".$beScatTypes->scat_type_id."' ";

		$bmMySQL = new BmMySQL();
		$beBase = $bmMySQL->query($query, "BeScatTypes", "opcfg");
				
		return $beBase;	
	}


	public function DeletePorHostId($beScats) {
		try{
			
			BmScatTypes::execQuery('DELETE FROM  scat_types where host_id = '.$beScats->host_id , "beScats", "opmon4");
			return $beScats;	

		}catch(Exception $e){
			$beScats->ErrMensagem = $e->getMessage();
			$beScats->ErrTitulo = "Erro";
			$beScats->ErrClasse = "BmScattypes";
			$beScats->ErrMetodo = "DeletePorHostId";
			$beScats->ErrStatus = 1;
            Bplog::save($beScats->ErrTitulo.": Class: ".$beScats->ErrClasse." - Metodo: ".$beScats->ErrMetodo." - ".$beScats->ErrMensagem,2);
		}
		return $beScats;
	}
		
	public function execQuery($query, $class, $database){
		$bmMySQL = new BmMySQL();
		$beBase = $bmMySQL->query($query, $class, "opmon4");
		if ($beBase->ErrStatus != 0) {
			throw new Exception('Error in query: '.$query.' ');
		}
		else{
			return $beBase;
		}
	}
}

?>