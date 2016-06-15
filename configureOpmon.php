#!/usr/bin/php -q
<?php

include_once "/usr/local/opmon/etc/config.php";
require_once("BpNOC/BpSyncDB.php");

require_once(dirname(__FILE__)."/BmNOC/BmConf.php");

//Insere comando
$check_interop_id = BmConf::insere_command_interop();
var_dump($check_interop_id);

//modify host template
BmConf::update_host_template($check_interop_id);

echo "Inserido Generic Host Template: \n";

//parameter in template
BmConf::insere_parameter_host_template();
echo "Inserido parametros do check_ic_status em Generic Host Template \n";


BmConf::update_service_template($check_interop_id);
//modify service template

echo "Inserido Generic Service Template: \n";

BpSyncDB::inicial_start();





