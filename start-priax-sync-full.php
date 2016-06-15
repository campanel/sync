<?php
require_once("BpNOC/BpOpMonSync.php");
require_once("BpNOC/BpOpMonSyncCatalogs.php");


BpSync::check_run_sync();

//Sincronismo de ICs e AICs
$sync = BpOpMonSync::SyncAll();
if($sync == true ){
    //Sincronismo de Catalogos
    BpOpMonSyncCatalogs::SyncCatalogs();
}

flush();
