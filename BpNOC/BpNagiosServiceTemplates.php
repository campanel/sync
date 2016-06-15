<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmNagiosServiceTemplates.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeNagiosServiceTemplates.php");

class BpNagiosServiceTemplates {	

	public static function ConsultaPorNome($beNagiosServices) {
		$beNagiosServiceTemplates = new BeNagiosServiceTemplates();
		$beNagiosServiceTemplates->template_name = $beNagiosServices->template_name;
		if($beNagiosServices->template_name == null){
			Bplog::save("IC PRIAX $beNagiosServices->service_name DEVE CONTER OPMON CI TEMPLATE", 1);
			$beNagiosServiceTemplates->ErrStatus = 1;
			return $beNagiosServiceTemplates;
		}
		return BmNagiosServiceTemplates::ConsultaPorNome($beNagiosServiceTemplates);
	}

	public static function Lista($criterion, $ordem, $limite) {
		$bmNagiosServiceTemplates = new BmNagiosServiceTemplates();
				
		return $bmNagiosServiceTemplates->Lista($criterion, $ordem, $limite);
	}
}
?>