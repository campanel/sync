<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmNagiosHostTemplates.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeNagiosHostTemplates.php");

class BpNagiosHostTemplates {	

	public static function ConsultaPorNome($beNagiosHosts)
    {
		$beNagiosHostTemplates = new BeNagiosHostTemplates();
		$beNagiosHostTemplates->template_name = $beNagiosHosts->template_name;
		if($beNagiosHosts->template_name == null){
			$beNagiosHostTemplates->ErrStatus = 1;
			return $beNagiosHostTemplates;
		}
		return BmNagiosHostTemplates::ConsultaPorNome($beNagiosHostTemplates);
	}
	
	public static function Lista($criterion, $ordem, $limite) {
		$bmNagiosHostTemplates = new BmNagiosHostTemplates();
		return $bmNagiosHostTemplates->Lista($criterion, $ordem, $limite);
	}
}
?>