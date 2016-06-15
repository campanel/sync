<?php
require_once(dirname(dirname(__FILE__))."/BeNOC/BeNagiosHosts.php");
require_once(dirname(dirname(__FILE__))."/BmNOC/BmPriax.php");
require_once("BpNagiosHosts.php");
require_once("BpNagiosHostTemplates.php");
require_once("BpNagiosServiceTemplates.php");
require_once("BpSnmpCommunities.php");
require_once("BpLog.php");
require_once("BpSyncDB.php");

class BpSync{
    public function sync($ics_priax = array(), $ics_opmon = array(), $ics_delete = array(), $lstErros = array(), $last_id = null)
    {
        /**Verificação dos ICs, Retorna 3 arrays INCLUIR, ATUALIZAR, ICs Existentes que necessitam verificar os AICs*/
        list( $ics_create, $ics_update, $ics_to_check_aics ) = BpSync::get_ic_arrays_add_update($ics_priax, $ics_opmon);

        //var_dump($ics_update);exit;

        /***INICIO CRUD ICs***/
        $ics_delete = BpNagiosHosts::delete_ics($ics_delete);
        $ics_update = BpNagiosHosts::update_ics($ics_update);
        $ics_create = BpNagiosHosts::create_ics($ics_create);

        Bplog::save("--- FIM CRUD ICs ---");

        Bplog::save("--- CRUD AICs ---");
        /** Verificação AICs
         * Retorna 3 arrays INCLUIR, ATUALIZAR, EXCLUIR
         */
        list( $aics_create, $aics_update, $aics_delete ) = BpSync::get_aic_arrays_crud($ics_to_check_aics);

        /***CRUDs AICs***/
        $aics_delete = BpNagiosServices::delete_aics($aics_delete);
        $aics_update = BpNagiosServices::update_aics($aics_update);
        $aics_create = BpNagiosServices::create_aics($aics_create);

        Bplog::save("--- FIM CRUD AICs ---");
        Bplog::save("IC INCLUIR: ".count($ics_create),1);
        Bplog::save("IC ATUALIZAR: ".count($ics_update),1);
        Bplog::save("ICS EXCLUIR: ".count($ics_delete),1);
        Bplog::save("AIC INCLUIR: ".count($aics_create),1);
        Bplog::save("AIC ATUALIZAR: ".count($aics_update),1);
        Bplog::save("AIC EXCLUIR: ".count($aics_delete),1);

        //var_dump($lstErros);
        if( count($lstErros) > 0  ){
            Bplog::save("ERRO - NODOs PRIAX NAO PROCESSADOS:",2);
            foreach ($lstErros as $value) {
                Bplog::save("ERRO ".get_class($value)." PRIAX ID: $value->priax_id - $value->ErrMensagem",2);
            }
        }

        /*Se houve alterações exporta OpMon*/
        if(count($ics_create) > 0 || count($ics_update) > 0 || count($ics_delete) > 0 ||
            count($aics_create) > 0 || count($aics_update) > 0 || count($aics_delete) > 0 ){
            Bplog::save("EFETUADO ALTERACOES EXPORT OPMON",1);
            $retExport = BpSync::exportConfigOpmon();
            Bplog::save("\n".$retExport, 0);
        }else{
            Bplog::save("SEM ALTERAÇÕES NAO HAVERA EXPORT NO OPMON");
        }

        /** Setando id incremental no arquivo*/

        BpSync::set_value_incremental($last_id);

        Bplog::save("--- Fim Sync ICs/AICs ---");
        Bplog::save("");

        /**Limpa o banco sqlite*/
        BpSyncDB::vacuum();
        return true;
    }

    public function remove_ics_unnamed_without_ip($ics){
        /**
         *Retorna array com 2 arrays um com os aics sem os erros e outro com somente os aics errados
         */
        if(count($ics) <= 0){
            $ret = array(array(), array());
            return $ret;
        }

        $lstErros = array();
        /**Excluir ICs sem nome da lista do priax e add a lista de ICs com erros*/
        foreach ($ics as $key => $ic) {
            //var_dump($ic);
            if($ic->host_name == '' || $ic->address == ''){
                if( $ic->opmon_active == 'Yes' ){ //se nao tiver active yes ele apenas deleta
                    $ic->ErrMensagem = "priax id:$ic->priax_id $ic->DN - IC sem NOME ou sem IP";
                    $lstErros[] = $ic;
                    if(DEBUG >= 1) Bplog::save($ic->ErrMensagem." EXCLUIDO da lista PRIAX ICs",1);
                }
                unset($ics[$key]);
            }
        }
        $ret = array($ics, $lstErros);
        return $ret;
    }

    public function add_snmp_in_opmon_and_snmp_id_in_ics_priax($ics){
        /**
         * CADASTRA SNMP NO OPMON E ADICIONA COMUNITY_ID AOS ICS DO PRIAX
         * retorna um array de ICs
         */
        foreach ($ics as $key => $ic) {
            if ( $ic->snmp_community ) {
                $snmp_id = BpSnmpCommunities::getId($ic);
                if ( $snmp_id != $ic->snmp_community_id ) {
                    $ics[$key]->community_id = $snmp_id;
                    BmPriax::setParameterInPriax(
                        $ics[$key]->priax_id,
                        'OpMonAgentConfiguration\SNMPCommunityID',
                        $ics[$key]->community_id
                    );
                    if(DEBUG >= 2) Bplog::save("Set id_community: ".$ics[$key]->community_id." in priax :".$ics[$key]->community_id,1);
                }
            }
        }
        
        return $ics;
    }

    public function diff_array_of_objects_by_parameter($array1,$array2,$parameter, $parameter_view = null){
        /**
         * SE O IC EXISTIR NO OPMON E NÃO EXISTIR NO PRIAX ELE DEVE SER DELETADO
         *recebe dois arrays um do priax e outro do opmon
         */
        if($parameter_view == null){
            $parameter_view = $parameter;
        }
        $result = array();
        foreach ($array2 as $obj) {
            if(DEBUG >= 2) Bplog::save("Objeto EXISTE?");
            if(BpSync::Exist( $array1, $obj, $parameter) == null) {

                /***
                Só deleta os ics controlados pelo priax
                 **/
                $priax_id = null;
                $priax_id = BpSyncDB::get_priax_id_by_host_id($obj->host_id);

                if($priax_id){
                    array_push($result, $obj);
                    Bplog::save(" Objeto: ".$obj->$parameter_view,1);
                }

            }
        }
        return $result;
    }

    public function get_ic_arrays_add_update($icsPriax, $icsOpmon){
        global $ic_parameters_to_check;
        /**
         * Verificação dos ICs
         * Retorna 3 arrays INCLUIR, ATUALIZAR, ICs Existentes que necessitam verificar os AICs
         */
        if(DEBUG >= 2) Bplog::save("-- VERIFICACAO POR IC --");
        $icsCreate = array();
        $icsUpdate = array();
        $icsToCheAics = array();
        foreach ($icsPriax as $ic) {
            //verifica se IC do Priax existe no OpMon, retorno false ou o IC
            if(DEBUG >= 2) Bplog::save("EXISTE IC PRIAX no OPMON? $ic->host_name");
            $icOpmon = BpSync::Exist($icsOpmon, $ic, "host_id");

            //Se existe entra aqui
            if( $icOpmon != null ){
                array_push($icsToCheAics, $ic);
                if(DEBUG >= 2) Bplog::save(" SIM IC add a lista de VERIFICACAO DE AICs ".$ic->host_name);
                if(DEBUG >= 2) Bplog::save("CONFIGURACOES deste IC estão IGUAIS no OPMON e no PRIAX? ".json_encode( $ic_parameters_to_check));
                //verifica se algum parametro é diferente retona true ou false
                if(BpSync::equalObjsByParameter($ic, $icOpmon, $ic_parameters_to_check)){
                    //Se existir parametro diferente adiciona IC a lista de update
                    if(DEBUG >= 2) Bplog::save(" IC SEM modificacoes");
                }else{
                    if(DEBUG >= 1) Bplog::save(" IC sera ATUALIZADO $ic->host_name",0);
                    array_push($icsUpdate, $ic);
                }
            }else{//se não existe no OpMon
                if(DEBUG >= 1) Bplog::save(" ADD IC A LISTA DE INCLUSAO $ic->host_name",0);
                array_push($icsCreate, $ic);
            }
            if(DEBUG >= 2) Bplog::save("-- FIM DA VERIFICACAO POR IC --");
        }

        if(count($icsCreate) <= 0 ) Bplog::save("SEM ICs para INCLUIR");
        if(count($icsUpdate) <= 0 ) Bplog::save("SEM ICs para Atualizar");
        if(count($icsToCheAics) <= 0 ) Bplog::save("SEM ICs para CHECAR os AICs");

        $ret = array($icsCreate, $icsUpdate, $icsToCheAics );
        return $ret;
    }

    public function get_aic_arrays_crud($ics_to_check_aics){
        /** Verificação AICs
         * Recebe um array com ICs a serem verificados os AICs
         * Retorna 3 arrays INCLUIR, ATUALIZAR, EXCLUIR
         */
        global $aic_parameters_to_check;
        $aicsIncluir = array();
        $aicsAtualizar = array();
        $aicsExcluir = array();

        foreach ($ics_to_check_aics as $ic) {
            /**get AICs Priax*/
            Bplog::save("GET AICs PRIAX ".$ic->host_name);
            $bmPriax = new BmPriax();
            $aics_priax = $bmPriax->getAICsPriaxArray($ic);
            if(count($aics_priax) <= 0) Bplog::save(" NAO EXISTE KPIs no PRIAX em $ic->host_name",1);

            /**get AICs OpMon*/
            if(DEBUG >= 2) Bplog::save("GET AICs OPMON ".$ic->host_name);
            $aics_opmon = BpNagiosServices::get_aics_by_host_id($ic->host_id);

            if(count($aics_opmon) <= 0) Bplog::save(" NAO EXISTE AICs OPMON em $ic->host_name",1);

            /**AIC PRIAX EXISTE NO OPMON? senão add a lista add AICs*/
            foreach ($aics_priax as $aic_priax) {
                if(DEBUG >= 2) Bplog::save("AIC PRIAX EXISTE NO OPMON? ".$aic_priax->service_description);
                $resultExisteAIC = BpSync::Exist($aics_opmon,$aic_priax,"service_id");
                if( $resultExisteAIC == false) {
                    if(DEBUG >= 2) Bplog::save(" NAO AIC PRIAX SERA INSERIDO POSTERIORMENTE",1);
                    array_push($aicsIncluir, $aic_priax);
                }else{
                    if(DEBUG >= 2) Bplog::save(" SIM add AICs a lista de VERIFICACAO de AICs");
                }
            }

            /**Se existe no OpMon mas nao no Priax add a lista de Excluir AICs*/
            foreach ($aics_opmon as $aic_opmon) {
                if(DEBUG >= 2) Bplog::save("AIC OPMON existe no PRIAX? ".$aic_opmon->service_description);
                $resultExisteAIC = BpSync::Exist($aics_priax,$aic_opmon,"service_id");
                if( $resultExisteAIC != false) {
                    //Bplog::save(" SIM");
                    if(DEBUG >= 2) Bplog::save("CONFIGURACOES DE AICs estao IGUAIS? ".json_encode( $aic_parameters_to_check));
                    if(BpSync::equalObjsByParameter($resultExisteAIC, $aic_opmon, $aic_parameters_to_check)){
                        //Se existir parametro diferente adiciona IC a lista de update
                        if(DEBUG >= 2) Bplog::save(" SIM - AIC NAO HAVERA MODIFICACOES");
                    }else{
                        if(DEBUG >= 2) Bplog::save(" NAO - AIC OPMON LISTA ATUALIZAR ".$resultExisteAIC->service_description,1);
                        $resultExisteAIC->checkcommandparameter_id = $aic_opmon->checkcommandparameter_id;
                        array_push($aicsAtualizar, $resultExisteAIC);
                    }
                }else{
                    if(DEBUG >= 2) Bplog::save(" NAO - AIC SERA EXCLUIDO POSTERIORMENTE $aic_opmon->service_description",1);
                    /***
                     * ANTIGO - array_push($aicsExcluir, $aic_opmon);
                    excluir somente se vir do priax - SOLICITAÇÃO 26/01/2015
                    */
                    $priax_id = null;
                    $priax_id = BpSyncDB::get_priax_id_by_service_id($aic_opmon->service_id);
                    if($priax_id){
                        array_push($aicsExcluir, $aic_opmon);
                    }
                }
            }
        }

        if(count($aicsIncluir) <= 0 ) Bplog::save("SEM AICs para INCLUIR");
        if(count($aicsAtualizar) <= 0 ) Bplog::save("SEM AICs para Atualizar");
        if(count($aicsExcluir) <= 0 ) Bplog::save("SEM AICs para EXCLUIR");

        if(DEBUG >= 4) var_dump('incluir',$aicsIncluir);
        if(DEBUG >= 4) var_dump('$aicsAtualizar',$aicsAtualizar);
        if(DEBUG >= 4) var_dump('$aicsExcluir',$aicsExcluir);

        $ret = array($aicsIncluir, $aicsAtualizar, $aicsExcluir );
        return $ret;
    }

    public function Exist($arr, $be, $parameter) {

        foreach($arr as $struct) {
            if(DEBUG >= 4) Bplog::save( $be->$parameter." == ".$struct->$parameter);
            if ($be->$parameter == $struct->$parameter) {
                return $struct;
            }
        }
        if(DEBUG >= 4) Bplog::save(" ".get_class($be) ." ".$parameter.": ".$be->$parameter." - NAO existe.", 1);
        //var_dump('exit'); exit;
        return null;
    }

    public function equalObjsByParameter($obj1, $obj2, $parameters){ //Se algum valor estiver diferente retorna falso

        foreach ($parameters as $parameter) {
            if(DEBUG >= 4)  Bplog::save( $parameter." - ".$obj1->$parameter." ??? ".$obj2->$parameter);
            if ($obj1->$parameter != $obj2->$parameter) {
                Bplog::save("Parametro: ".$parameter." - ".$obj1->$parameter." DIFERENTE ".$obj2->$parameter, 1);
                return false;
            }
        }
        if(DEBUG >= 4) Bplog::save("Parametro: ".$parameter." - ".$obj1->$parameter." IGUAL ".$obj2->$parameter,1);
        return true;
    }

    public function exportConfigOpmon(){
        $output = shell_exec('/usr/bin/php -q /usr/local/opmon/share/opcfg/tools/exporter/export.php 1 opmonadmin');
        return $output;
    }

    function set_value_incremental($last_id = null) {

        if($last_id == null){
            $bmPriax = new BmPriax();
            $last_id = $bmPriax->getLastChangeId();
        }

        BpSyncDB::set_incremental_id($last_id);
        Bplog::save("Set Last ID: ".$last_id);
    }

    function check_run_sync() {
        //var_dump('entrou aki');
        exec('ps axu | grep start-priax-sync-full.php | grep -v grep',$out, $return_full);
        exec('ps axu | grep start-priax-sync-incrementais.php | grep -v grep',$out2, $return_incremental);
        exec('ps axu | grep start-priax-sync-incrementais-only-ics-aics.php | grep -v grep',$out3, $return_incremental_only_ics_aics);

        //var_dump($out, $return_full);
        //var_dump($out2, $return_incremental);

        if( count($out) > 1 ||  count($out2) > 1 ||  count($out3) > 1
            || ($return_full == 0 and $return_incremental ==  0)
            || ($return_full == 0 and $return_incremental_only_ics_aics ==  0)
            || ($return_incremental == 0 and $return_incremental_only_ics_aics ==  0)
        ){
            Bplog::save('Sincronismo em andamento, tentar mais tarde...',2);
            exit;
        }

    }

}

