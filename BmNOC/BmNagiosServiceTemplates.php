<?php
require_once("BmMySQL.php");

class BmNagiosServiceTemplates{

	public function ConsultaPorNome($beNagiosServiceTemplates) { 
		 
		$beBase = BmNagiosServiceTemplates::Lista("WHERE template_name = '".$beNagiosServiceTemplates->template_name."' ");
		//var_dump($beBase[0]); exit;
		return $beBase[0];
	}
	
	public function Lista($criterion=null, $order=null, $limit=null) {
		
		$query = "SELECT service_template_id, template_name,  0 AS ErrStatus FROM nagios_service_templates " . $criterion . " " . $order . " " . $limit;
		
		$bmMySQL = new BmMySQL();
				
		return $bmMySQL->query($query, "BeNagiosServiceTemplates", "opcfg");

	}
		
	
}


?>