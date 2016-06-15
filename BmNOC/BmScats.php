<?php
require_once("BmMySQL.php");

class BmScats{

	public function create($beScats) {
		$today = date('d-m-Y');
		$bmMySQL = new BmMySQL();
		$query = "INSERT INTO opmon4.scats values( '', '".$beScats->scat_name."', '".$beScats->type."', NULL, '', '".$today."', '0.00', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', NULL, '' )";
		//var_dump($query);
		$beBase = $bmMySQL->query($query, "BeScats", "opmon4");
		
		if ($beBase->ErrStatus != 0) {
			$beScats->ErrMensagem = "Retorno do Metodo: query";
			$beScats->ErrTitulo = $beBase->ErrTitulo;
			$beScats->ErrClasse = "BmScats";
			$beScats->ErrMetodo = "InsereAIC";
			$beScats->ErrStatus = $beBase->ErrStatus;
			Bplog::save($beScats->ErrTitulo.": Class: ".$beScats->ErrClasse." - Metodo: ".$beScats->ErrMetodo." - Mensagem: Retorno de Class: BmMySQL - Metodo: Query ", 2);
		
		} else {
			$beScats->scat_id = $beBase->ErrMensagem;
			$beScats->ErrStatus = 0;
		}
		return $beScats;
	}
	

	public function Lista($criterion=null, $order=null, $limit=null) {
		$query = "SELECT id as scat_id, scat_name, 0 AS ErrStatus FROM scats " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeScats", "opmon4");
	}

	public function update_scat_info($beScats) {
		$query = "UPDATE scats SET scat_name = '".$beScats->scat_name."' where id = ".$beScats->scat_id." ";
		$bmMySQL = new BmMySQL();
		//var_dump($query); exit;
		$beBase = $bmMySQL->query($query, "beScats", "opmon4");
		
		if ($beBase->ErrStatus != 0) {
			$beScats->ErrClasse = $beBase->ErrClasse;
			$beScats->ErrMensagem = $beBase->ErrMensagem;
			$beScats->ErrMetodo = $beBase->ErrMetodo;
			$beScats->ErrTitulo = $beBase->ErrTitulo;
			$beScats->ErrStatus = $beBase->ErrStatus;
		}
		return $beScats;
	}

	public function delete($beScats) {
		try{
			BmScats::execQuery('DELETE FROM  scats where id = '.$beScats->scat_id , "beScats", "opmon4");
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