<?php
require_once("BmMySQL.php");

class BmScatContacts{

	public function create($beScats) {
		//var_dump("\n\nISERCAO DO BANCO\n\n");
		//var_dump($beScats);
		//exit;

		$bmMySQL = new BmMySQL();
		
		$query = "INSERT INTO opmon4.scat_contacts values('".$beScats->scat_id."','responsable','1','9')";

		$beBase = $bmMySQL->query($query, "BeScats", "opmon4");
		
		if ($beBase->ErrStatus != 0) {
			$beBase->ErrMensagem = "Retorno do Metodo: query";
			$beBase->ErrClasse = "BmScatContacts";
			$beBase->ErrMetodo = "Insere";
			Bplog::save($beBase->ErrTitulo.": Class: ".$beBase->ErrClasse." - Metodo: ".$beBase->ErrMetodo." - Mensagem: Retorno de Class: BmMySQL - Metodo: Query ", 2);
		
		} else {
			$beBase->scat_id = $beScats->scat_id;
		}
		return $beBase;
	}
	

	public function Lista($criterion=null, $order=null, $limit=null) {
		
		$query = "SELECT scats.*, 0 AS ErrStatus FROM nagios_services " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeScats", "opmon4");
	}

	public function delete_by_scat_id($scat_id) {
		try{

            $beScats = BmScats::execQuery('DELETE FROM  opmon4.scat_contacts where scat_id = '.$scat_id , "beScats", "opmon4");
			return $beScats;

		}catch(Exception $e){
            $beScats = new BeScats();
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