<?php
require_once("BmMySQL.php");

class BmScatSazdays{

	public function create($beScats,$num) {
		$bmMySQL = new BmMySQL();
		$query = "INSERT INTO opmon4.scat_sazdays values ('".$beScats->scat_id."','".$num."','0')";
		$beBase = $bmMySQL->query($query, "BmScatSazdays", "opmon4");
		
		if ($beBase->ErrStatus != 0) {
			$beBase->ErrMensagem = "Retorno do Metodo: query";
			$beBase->ErrTitulo = $beBase->ErrTitulo;
			$beBase->ErrClasse = "BmScatSazdays";
			$beBase->ErrMetodo = "Insere";
			$beBase->ErrStatus = $beBase->ErrStatus;
			Bplog::save($beBase->ErrTitulo.": Class: ".$beBase->ErrClasse." - Metodo: ".$beBase->ErrMetodo." - Mensagem: Retorno de Class: BmMySQL - Metodo: Query ", 2);
		
		} else {
			$beBase->ErrStatus = 0;
		}
		return $beBase;
	}
	
	public function ConsultaId($beScats) { 
		$foo = BmScats::Lista("WHERE scat_name = " . $beScats->scat_name, "", "");
		return $foo[0];
	}
	
	public function Lista($criterion=null, $order=null, $limit=null) {
		$query = "SELECT scats.*, 0 AS ErrStatus FROM nagios_services " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeScats", "opmon4");
	}

	public function Update($beScats) {

	}

	public function delete_by_scat_id($scat_id) {
		try{
            $beScats = new BeScats();
			BmScats::execQuery('DELETE FROM  scat_sazdays where scat_id = '.$scat_id , "beScats", "opmon4");

			return $beScats;	

		}catch(Exception $e){
			$beScats->ErrMensagem = $e->getMessage();
			$beScats->ErrTitulo = "Erro";
			$beScats->ErrClasse = "BmScats";
			$beScats->ErrMetodo = "DeletePorScatId";
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