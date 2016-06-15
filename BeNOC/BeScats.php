<?php

include_once("BeBase.php");

class BeScats extends BeBase {
	public $scat_id = NULL;
	public $priax_id = NULL;
	public $scat_name = NULL;
	public $DN = NULL;
	public $basic_name = NULL;
	public $type = NULL;
	public $service_id = NULL;
	public $service_name = NULL;
	public $scat_itens = NULL; //array de BeScatItens
}

?>