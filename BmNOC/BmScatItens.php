<?php
require_once("BmMySQL.php");

class BmScatItens{

	public function create($beScatItens) {
		$bmMySQL = new BmMySQL();
		$query = "INSERT INTO opmon4.scat_itens values('','".$beScatItens->scat_id."','".$beScatItens->host_name."',".$beScatItens->service_name.",'".$beScatItens->level."')";
		//var_dump($query);
		$beBase = $bmMySQL->query($query, "BeScats", "opmon4");
		
		if ($beBase->ErrStatus != 0) {
			$beBase->ErrMensagem = "Retorno do Metodo: query";
			$beBase->ErrTitulo = $beBase->ErrTitulo;
			$beBase->ErrClasse = "BmScatItens";
			$beBase->ErrMetodo = "InsereAIC";
			$beBase->ErrStatus = $beBase->ErrStatus;
			Bplog::save($beBase->ErrTitulo.": Class: ".$beBase->ErrClasse." - Metodo: ".$beBase->ErrMetodo." - Mensagem: Retorno de Class: BmMySQL - Metodo: Query ", 2);
		
		} else {
			$beBase->id = $beBase->ErrMensagem;
			$beBase->ErrStatus = 0;
		}
		return $beBase;
	}

	public function Lista($criterion=null, $order=null, $limit=null) {
		$query = "SELECT *, 0 AS ErrStatus FROM scat_itens " . $criterion . " " . $order . " " . $limit;
		$bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeScatItens", "opmon4");
	}

    public function delete_by_id($id) {
        try{
            $beScats = new BeScats();
            BmScatItens::execQuery('DELETE FROM  scat_itens where id = '.$id , "beScats", "opmon4");
            return $beScats;

        }catch(Exception $e){
            $beScats->ErrMensagem = $e->getMessage();
            $beScats->ErrTitulo = "Erro";
            $beScats->ErrClasse = "BmScatItens";
            $beScats->ErrMetodo = "delete_by_id";
            $beScats->ErrStatus = 1;
            Bplog::save($beScats->ErrTitulo.": Class: ".$beScats->ErrClasse." - Metodo: ".$beScats->ErrMetodo." - ".$beScats->ErrMensagem,2);
        }
        return $beScats;
    }

	public function delete_by_scat_id($scat_id) {
		try{
            $beScats = new BeScats();
			BmScatItens::execQuery('DELETE FROM  scat_itens where scat_id = '.$scat_id , "beScats", "opmon4");
			return $beScats;	

		}catch(Exception $e){
			$beScats->ErrMensagem = $e->getMessage();
			$beScats->ErrTitulo = "Erro";
			$beScats->ErrClasse = "BmScatItens";
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