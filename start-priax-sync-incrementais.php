<?php
require_once("BpNOC/BpOpMonSyncIncrementais.php");
require_once("BpNOC/BpOpMonSyncCatalogs.php");

BpSync::check_run_sync();

//Sincronismo de ICs e AICs
$sync = BpOpMonSyncIncrementais::Sync();
//if($sync == true ){
    //Sincronismo de Catalogos
    BpOpMonSyncCatalogs::SyncCatalogs();
//}

flush();