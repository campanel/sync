<?php
require_once("BpNOC/BpOpMonSync.php");
require_once("BpNOC/BpOpMonSyncCatalogs.php");


$bmPriax = new BmPriax;

$listPriaxAICs = array();
$listOpmonAICs = array();

Bplog::save("--- Inicio limpar IDs ---");

$lstBeNagiosHostsPriax = $bmPriax->getPriaxICs();
Bplog::save("ICs PRIAX a Reparar IDs: ".count($lstBeNagiosHostsPriax),1);
//var_dump($lstBeNagiosHostsPriax); 

Bplog::save("Limpando IC IDs",1);
foreach ($lstBeNagiosHostsPriax as $key => $value) {
	
	$ret = $bmPriax->setOpMonIDinPriaxNode(" ", $value->priax_id);
	$ret = 0;
	if($ret->ErrStatus == 0){
		$exit_code = 0;
	}else{
		$exit_code = 2;
	}
	Bplog::save("IC Priax node_id ".$value->priax_id." ".$value->DN." host_name: ".$value->host_name." - empty ID",$exit_code);
	$AICs = $bmPriax->getAICsPriaxArray($value);
	$listPriaxAICs = array_merge(array_values($listPriaxAICs),array_values($AICs));
}

Bplog::save("Limpando AIC IDs",1);
Bplog::save("AICs PRIAX a Reparar IDs: ".count($listPriaxAICs),1);
foreach ($listPriaxAICs as $k => $aic) {
	$aic->service_id = " ";
	$res = $bmPriax->setOpmonIdInPriaxKpi($aic);

	if($ret->ErrStatus == 0){
		$exit_code = 0;
	}else{
		$exit_code = 2;
	}
	Bplog::save("AIC Priax node_id ".$aic->priax_id." service_description ".$aic->service_description." - empty ID",$exit_code);
}

/*CATALOGOS*/
$lstBeNagiosHostsPriax = $bmPriax->getPriaxScats();
Bplog::save("Catalogos a Reparar IDs: ".count($lstBeNagiosHostsPriax),1);
//var_dump($lstBeNagiosHostsPriax); 

Bplog::save("Limpando Catalogs IDs",1);
foreach ($lstBeNagiosHostsPriax as $key => $value) {
	
	$ret = $bmPriax->setOpMonIDinPriaxCatalog(" ", $value->priax_id);

	if($ret->ErrStatus == 0){
		$exit_code = 0;
	}else{
		$exit_code = 2;
	}
	Bplog::save("Catalog Priax node_id ".$value->priax_id." ".$value->DN." - empty ID",$exit_code);
}

Bplog::save("--- Clean IDs --- ");
exit;
?>