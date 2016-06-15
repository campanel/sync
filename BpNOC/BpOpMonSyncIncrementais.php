<?php
require_once(dirname(dirname(__FILE__))."/BeNOC/BeNagiosHosts.php");
require_once(dirname(dirname(__FILE__))."/BmNOC/BmPriax.php");
require_once("BpNagiosHosts.php");
require_once("BpNagiosHostTemplates.php");
require_once("BpNagiosServiceTemplates.php");
require_once("BpSnmpCommunities.php");
require_once("BpLog.php");
require_once("BpSync.php");


class BpOpMonSyncIncrementais{

	public static function Sync()
    {
        Bplog::save("Start Incremental",0);

        //for($i=0; $i <= 10000; $i++){
        //    BpSyncDB::insere_log(date('Y-m-d H:i:s') , 2, 'Ferra '.$i );
        //}
        //BpSyncDB::log_delete();
        //exit;

        $bmPriax = new BmPriax();

        $first_id = $bmPriax->getFirstChangeId();
        if(DEBUG >= 2) Bplog::save("Fist ID: ".$first_id);

        $last_id = $bmPriax->getLastChangeId();
        if(DEBUG >= 2) Bplog::save("Last ID: ".$last_id);

        $incremental_id = BpSyncDB::get_incremental_id();
        if(DEBUG >= 2) Bplog::save("INCREMENTAL_ID in DB: ".$incremental_id);


        if($first_id > $incremental_id){
            Bplog::save("Favor executar SINCRONISMO FULL",2);
            exit;
        }

        if($last_id == $incremental_id){
            Bplog::save("Incremental - Nada para ser alterado",0);
            return false;
        }

        $ics_priax = BpOpMonSyncIncrementais::get_ics_incremental($last_id, $incremental_id);
        //var_dump($ics_priax);
        //exit;

        /**Excluir ICs Priax sem nome da lista e add a lista de ICs com erros*/
        list( $ics_priax, $lstErros ) = BpSync::remove_ics_unnamed_without_ip($ics_priax);

        /**CADASTRA SNMP NO OPMON E ADICIONA COMUNITY_ID AOS ICS DO PRIAX*/
        $ics_priax = BpSync::add_snmp_in_opmon_and_snmp_id_in_ics_priax($ics_priax);

        /** Criar lista de ics a ser deletados: ics_delete*/
        list($ics_priax,$ics_delete) = BpOpMonSyncIncrementais::delete_ics_incremental($ics_priax);

        if(DEBUG >= 1) Bplog::save("GET ICs OPMON");
        $ics_opmon = BpNagiosHosts::getAll();

        if( 0 == count($ics_delete) ) Bplog::save("SEM ICs para DELETAR");

        return BpSync::sync($ics_priax, $ics_opmon,$ics_delete,$lstErros,$last_id);

    }

    function delete_ics_incremental($ics_priax = array())
    {

        $ics_delete = array();
        foreach($ics_priax as $key => $ic){
            //var_dump('entrou aki');
            if( $ic->opmon_active != 'Yes' ){
                //var_dump('entrou aki');
                //var_dump('$ic', $ic);
                //Se não existe IC no opmon exclui da lista de exclusão
                $ic->host_id = BpSyncDB::get_host_id_by_priax_id($ic->priax_id);
                $ic_opmon = BpNagiosHosts::get_by_id($ic->host_id);
                //var_dump('$ic_opmon', $ic_opmon);
                //exit;

                if($ic_opmon != null){
                    //exit;
                    $ics_delete[] = $ic_opmon;
                    unset($ics_priax[$key]);
                    //$bmPriax = new BmPriax();
                    //$bmPriax->setOpMonIDinPriaxNode(' ', $ic->priax_id);
                    Bplog::save('Excluir IC: '.$ic_opmon->host_name,1);
                }else{
                    Bplog::save('Não existe no OpMon - Excluir IC da lista de exclusão:'.$ic->host_name,1);
                }
            }
        }
        return array($ics_priax,$ics_delete);
    }

    function get_ics_incremental($last_id, $incremental_id)
    {
        $bmPriax = new BmPriax;

        $listAttributes = array(
            'OpMon\OpMonCIName',
            'OpMon\OpMonDescription',
            'OpMon\OpMonMonitoringAddress',
            //'OpMon\OpMonID',
            'OpMon\OpMonActive',
            'OpMon\OpmonCITemplate',
            'OpMon\OpMonCIIcon',
            'OpMon\OpMonCIActionsPage',
            //'OpMon\OpMonCatalogName',
            //'OpMon\OpMonCatalogID',
            //'OpMon\OpMonCatalog',
            'OpMon\CustomMonitoringAttributes',
            'OpMonAgentConfiguration\SNMPComunity',
            'OpMonAgentConfiguration\SNMPPort',
            'OpMonAgentConfiguration\SNMPVersion',
            'OpMonAgentConfiguration\SNMPCommunityID',
            'OpMonAgentConfiguration\SNMPUser',
            'OpMonAgentConfiguration\SNMPPass',
            'OpMon\ACTIVE',
            //'OpMon\OpMonKpiID',
            'OpMon\ServiceName',
            'OpMon\MaximumChecksAttemps',
            'OpMon\OpMonCommand',
            'OpMon\CheckInterval',
            'OpMon\RetryCheckInterval',
            'OpMon\OpMonTemplate',
            'OpMon\AffectAvailability',
            'OpMon\CriticalLevel',
            'OpMon\WarningLevel',
        );

        //Get dos seres com atributos pertinentes
        if(DEBUG >= 2) Bplog::save("Atributos relevantes:");
        $listNodes = array();
        $beingtype_id_delete = array();
        $kpitype_id_delete = array();

        for($i=$incremental_id+1; $i <= $last_id ;$i++){
            $obj = $bmPriax->getChangeLogById($i);
//            var_dump($obj);
            if($obj->ChangeLog->action == "DELETE"){
                if($obj->ChangeLog->type == "BEINGTYPE" ){
                    $beingtype_id_delete[] = $obj->ChangeLog->id;
                    Bplog::save('BEINGTYPE sera deletado: '.$obj->ChangeLog->id,1);
                }elseif( $obj->ChangeLog->type == "KPITYPE" ){
                    $kpitype_id_delete[] = $obj->ChangeLog->id;
                    Bplog::save('KPITYPE sera deletado: '.$obj->ChangeLog->id,1);
                }
            }

            if(DEBUG >= 2) Bplog::save("verificando: - ".$obj->ChangeLog->id." -> ".$obj->ChangeLog->attribute);
            if(in_array($obj->ChangeLog->attribute, $listAttributes)){
                if(DEBUG >= 1) Bplog::save("SIM - ".$obj->ChangeLog->id." ".$obj->ChangeLog->attribute);
                //Bplog::save($obj->ChangeLog);
                $listNodes[$obj->ChangeLog->id] = $obj->ChangeLog->type;
            }
        }

        /**ICs a ser verificado*/
        $ics_priax = array();
        foreach($listNodes as $key => $value) {
            if ($value == "BEING") {
                //var_dump('BEING',$key);
                /**somente add se nao estiver na lista de delete**/
                if(!in_array($key,$beingtype_id_delete )){
                    $node = $bmPriax->getPriaxICbyID($key);
                    //var_dump('$ic_priax',$node);
                    if( $node->priax_id ){
                        //var_dump('Entrou');
                        $ic_priax = $node;
                    }
                }
            } elseif ($value == "KPITYPE") {
                $ic_priax = $bmPriax->getPriaxICbyKPI($key);//senão retornar um nodo ele não deve ser add a lista de verificacao
                //$aic->host = $host;
                //$aics_priax[] = $aic;
            } else {
                Bplog::save("Objeto nao Identificado: " . $key, 2);
            }

            if(is_numeric($ic_priax->priax_id)){
                $ics_priax[$ic_priax->priax_id] = $ic_priax;
            }
        }

        /*var_dump($beingtype_id_delete);
        var_dump($kpitype_id_delete);
        var_dump($ics_priax);
        exit;
        */
        BpOpMonSyncIncrementais::delete_nodes($beingtype_id_delete, $kpitype_id_delete );

        return $ics_priax;
    }

    function delete_nodes($beingtype_id_delete = null, $kpitype_id_delete = null)
    {
        $delets = 0;

        /** delete ICs*/
        foreach( $beingtype_id_delete as $priax_id ){
            $host_id = BpSyncDB::get_host_id_by_priax_id($priax_id);
            //var_dump('$host_id',$host_id);
            if($host_id) {
                $ret = BpNagiosHosts::delete_by_id($host_id);
                if ($ret->ErrStatus == 0) {
                    Bplog::save("HOST deletado ".$host_id,1);
                    $delets++;
                }
            }else{
                Bplog::save("Nao existe HOST no OpMon priax_id: ".$priax_id,1);
            }
        }

        /** delete AICs*/
        foreach( $kpitype_id_delete as $priax_id ){
            $service_id = BpSyncDB::get_service_id_by_priax_id($priax_id);
            //var_dump('service_id',$service_id);
            if($service_id){
                $ret = BpNagiosServices::delete_by_id($service_id);
                if($ret->ErrStatus == 0){
                    Bplog::save("SERVICE deletado ".$service_id,1);
                    $delets++;
                }
            }else{
                Bplog::save("Nao existe SERVICE no OpMon priax_id: ".$priax_id,1);
            }
        }

        /**Export**/
        if( $delets > 0 ) {
            Bplog::save("EFETUADO ALTERACOES EXPORT OPMON",1);
            $retExport = BpSync::exportConfigOpmon();
            Bplog::save("\n".$retExport);
        }else{
            Bplog::save("SEM ICs/AICs para deletar NAO HAVERA EXPORT NO OPMON");
        }
    }
}