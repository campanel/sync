<?php
require_once("BmMySQL.php");

class BmNagiosHostTemplates{

	public function ConsultaPorNome($beNagiosHostTemplates) { 
		 
		$beBase = BmNagiosHostTemplates::Lista("WHERE template_name = '".$beNagiosHostTemplates->template_name."' ");
		//var_dump($beBase[0]); exit;
		return $beBase[0];
	}
	
	public function Lista($criterion=null, $order=null, $limit=null) {
		
		$query = "SELECT host_template_id, template_name,  0 AS ErrStatus FROM nagios_host_templates " . $criterion . " " . $order . " " . $limit;
		
		$bmMySQL = new BmMySQL();
				
		return $bmMySQL->query($query, "BeNagiosHostTemplates", "opcfg");

	}
		
	
}


?>