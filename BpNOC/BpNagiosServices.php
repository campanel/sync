<?php
require(dirname(dirname(__FILE__))."/BmNOC/BmNagiosServices.php");
require(dirname(dirname(__FILE__))."/BpNOC/BpNagiosServicesCheckCommandParameters.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeNagiosServices.php");

class BpNagiosServices{

    public static function create($aic)
    {
        if(DEBUG >= 2) Bplog::save("INSERE AIC $aic->service_description em $aic->host_id");
        /**Validação**/
        if($aic->use_template_id == '' || $aic->host_id == '' || $aic->host_id === 0 || $aic->service_description == '' ){
            Bplog::save("KPI SEM NOME ou SEM TEMPLATE ou SEM host_id -> $aic->service_description ; host_id: $aic->host_id ; use_template_id: $aic->use_template_id ; Priax ID $aic->priax_id",2);
            $aic->ErrStatus = 1;
            return $aic;
        }
        /** Array com os parametros a serem inseridos, se check_command for vazio retirar não add o array*/
        $service = array(
            'use_template_id' => $aic->use_template_id,
            'host_id' => $aic->host_id,
            'service_description' => $aic->service_description,
            'max_check_attempts' => $aic->max_check_attempts,
            'normal_check_interval' => $aic->normal_check_interval,
            'retry_check_interval' => $aic->retry_check_interval,
            'process_perf_data' => $aic->process_perf_data
        );
        if($aic->check_command != ''){
            $service['check_command'] = $aic->check_command;
        }

        /** verifica se já existe SERVICE  neste HOST**/
        if(DEBUG >= 2) Bplog::save(" Verifica se já existe SERVICE $aic->service_description no $aic->host_id");
        $service_id = BpNagiosServices::get_service_id_by_host_id_and_service_description($aic->host_id, $aic->service_description);
        if(DEBUG >= 2) Bplog::save($service_id);

        if ($service_id) {
            $aic->$service_id;
            $aic->ErrStatus = 1;
            Bplog::save(" FALHOU AIC: $aic->service_description JA EXISTE, OPMON nao pode ter AICs com nomes iguais no mesmo IC priax_id: ".$aic->priax_id,2);
            return $aic;
        }

        /**ADD Service**/
        BmApiOpmon::add_service($service);

        /** busca id do service*/
        if(DEBUG >= 2) Bplog::save(' Busca id de servico adicionado');
        $aic->service_id = BpNagiosServices::get_service_id_by_host_id_and_service_description($aic->host_id, $aic->service_description);
        //var_dump($aic);exit;
        if ( !$aic->service_id || $aic->service_id == '' ) {
            $aic->ErrStatus = 1;
            Bplog::save(" FALHOU AIC OPMON INCLUIR, get_service_id_by_host_id_and_service_description $aic->service_description",2);
            return $aic;
        }

        /**insere comando**/
        BpNagiosServicesCheckCommandParameters::insere_by_service($aic);

        /**insere service_id no kpi do PRIAX**/
        if($aic->priax_id ){
            $bmPriax = new bmPriax;
            $bmPriax->setOpmonIdInPriaxKpi($aic);

        }else{
            if($aic->is_catalog != 1) Bplog::save(" AIC NAO tem priax_id *SE FOR CATALOGO DESCONSIDERAR: ".$aic->service_description,2);
        }

        if( DEBUG >= 2 ) Bplog::save(" AIC OPMON INCLUIDO $aic->service_description em $aic->host_id",0);
        if($aic->is_catalog != 1) BpSyncDB::insere_service($aic->service_id, $aic->host_id, $aic->priax_id, $aic->service_description);
        return $aic;
	}

    public static function delete($aic)
    {
        //Bplog::save("delete", 2);
        /**Validação**/
        if($aic->service_id == '' || !$aic->service_id){
            Bplog::save("FALHOU DELETE BpNagiosServices SEM service_id, priax_id:".$aic->priax_id, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }
        $ret_aic = BpNagiosServices::delete_by_id($aic->service_id);
        //var_dump($ret_aic);

        return $ret_aic;
    }

    public static function delete_by_id($service_id)
    {
        //Bplog::save("delete_by_id", 2);
        /**Validação**/
        if($service_id == '' || !$service_id){
            Bplog::save("FALHOU DELETE_BY_ID AIC SEM service_id", 2);
            $status = new BeBase();
            $status->ErrStatus = 1;
            return $status;
        }

        $ret_aic = BpNagiosServices::get_aic_by_service_id($service_id);
        if ($ret_aic == null) {
            Bplog::save("AIC OPMON DELETE_BY_ID, NAO ENCONTRADO AIC NO OPMON",1);
            $status = new BeBase();
            $status->ErrStatus = 0;
            return $status;
        }

        BmApiOpmon::delete_service_by_id($service_id);

        $aic_delete = BpNagiosServices::get_aic_by_service_id($service_id);
        if ($aic_delete == null) {
            if( DEBUG >= 2 ) Bplog::save(" AIC OPMON DELETE_BY_ID",0);
            $status = new BeBase();
            $status->ErrStatus = 0;
        }else{
            Bplog::save(" FALHOU AIC OPMON DELETE_BY_ID",2);
            $status = new BeBase();
            $status->ErrStatus = 1;
        }

        BpSyncDB::delete_service_by_service_id($service_id);

        return $status;
    }

    public function update($aic)
    {
        global $aic_parameters_to_check;
        /**Validação**/
        if($aic->service_id == '' || !$aic->service_id ){
            Bplog::save("FALHOU UPDATE service SEM service_id, priax_id:".$aic->priax_id, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }

        if( $aic_parameters_to_check == '' || !$aic_parameters_to_check ){
            Bplog::save("FALHOU UPDATE service SEM parametros globais service_description: ".$aic->service_description, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }

        //retira command_parameter do array , pois ele atualiza em outra tabela e usa outra funcao
        $parameters = array_diff($aic_parameters_to_check, array('command_parameter'));
        $beBase = BmNagiosServices::update($aic,$parameters);
        BpNagiosServicesCheckCommandParameters::update($aic);

        if ($beBase->ErrStatus != 0) {
            $aic->ErrStatus = $beBase->ErrStatus;
            Bplog::save(" FALHOU AIC OPMON Atualizar $beBase->service_description",2);
        }else{
            if( DEBUG >= 2 ) Bplog::save(" AIC OPMON Atualizado $beBase->service_description",0);
        }
        return $aic;
    }

    public function update_whith_parameter($aic, $aic_parameters_to_check)
    {
        /**Validação**/
        if($aic->service_id == '' || !$aic->service_id ){
            Bplog::save("FALHOU update_whith_parameter service SEM service_id, priax_id:".$aic->priax_id, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }

        if( $aic_parameters_to_check == '' || !$aic_parameters_to_check ){
            Bplog::save("FALHOU update_whith_parameter service SEM parametros globais service_description: ".$aic->service_description, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }

        //retira command_parameter do array , pois ele atualiza em outra tabela e usa outra funcao
        $parameters = array_diff($aic_parameters_to_check, array('command_parameter'));
        $beBase = BmNagiosServices::update($aic,$parameters);
        BpNagiosServicesCheckCommandParameters::update($aic);

        if ($beBase->ErrStatus != 0) {
            $aic->ErrStatus = $beBase->ErrStatus;
            Bplog::save(" FALHOU AIC OPMON Atualizar $beBase->service_description",2);
        }else{
            if( DEBUG >= 2 ) Bplog::save(" AIC OPMON Atualizado $beBase->service_description",0);
        }
        return $aic;
    }

    /** gets */

	public static function get_by_id($id)
    {
        /**Validação**/
        if( $id == '' || !$id ){
            Bplog::save("FALHOU getByID SEM service_id", 2);
            return null;
        }

        $service = BmNagiosServices::query("WHERE service_id = " . $id, "", "");
        $checkCommandParameter = BpNagiosServicesCheckCommandParameters::getByServiceId($id);
        //var_dump($result);
        $service->checkcommandparameter_id = $checkCommandParameter->checkcommandparameter_id;
        $service->command_parameter = $checkCommandParameter->parameter;
        return $service;
	}

    public function get_aics_by_host_id($host_id)
    {
        //Validação
        if( $host_id == '' || !$host_id ){
            Bplog::save("FALHOU getAICsByHostId SEM host_id", 2);
            return null;
        }

        $aics = BmNagiosServices::query("WHERE host_id = " . $host_id, "", "");
        $listAics = array();
        //adiciona o comando a cada um AIC, pois vem de uma tabela separada
        foreach ($aics as $aic) {
            $checkCommandParameter = BpNagiosServicesCheckCommandParameters::getByServiceId($aic->service_id);
            //var_dump($result);
            $aic->checkcommandparameter_id = $checkCommandParameter->checkcommandparameter_id;
            $aic->command_parameter = $checkCommandParameter->parameter;
            $listAics[$aic->service_id] = $aic;
        }
        return $listAics;
    }

    public function get_aic_by_service_id($id)
    {
        /**Validação**/
        if( $id == '' || !$id ){
            Bplog::save("FALHOU get_aic_by_service_id SEM service_id", 2);
            return null;
        }

        $aics = BmNagiosServices::query("WHERE service_id = " . $id, "", "");

        if ($aics[0]->ErrStatus != 0) {
            return null;
        }else{
            return $aics[0];
        }
    }

    public static function get_service_id_by_host_id_and_service_description($host_id, $service_description)
    {
        //Bplog::save("Entrou get_service_id_by_host_id_and_service_description", 1);
        /**Validação**/
        if($host_id == '' || !$host_id || $service_description == '' || !$service_description ){
            Bplog::save("FALHOU get_service_id_by_host_id_and_service_description SEM host_id ou service_description", 2);
            return null;
        }

        $aics =  BmNagiosServices::get_service_id_by_host_id_and_service_description($host_id, $service_description);


        if ($aics[0]->ErrStatus != 0) {
            if(DEBUG >= 2) Bplog::save("SEM service_id", 1);
            return null;
        }else{
            if(DEBUG >= 2) Bplog::save("service_id: ".$aics[0]->service_id, 1);
            return $aics[0]->service_id;
        }
    }

	public static function get_service_by_host_name_and_service_description($host_name, $service_name)
    {
        /**Validação**/
        if($host_name == '' || !$host_name || $service_name == '' || !$service_name ){
            Bplog::save("FALHOU getByNamesHostService SEM host_name ou service_description", 2);
            return null;
        }
        $aic = BmNagiosServices::get_service_by_host_name_and_service_description($host_name, $service_name);
        //var_dump('aics em servcies',$aic); exit;
		return $aic[0];
	}

	public static function get_aics_avail_by_host_id($host_id)
    {
        /**Validação**/
        if($host_id == '' || !$host_id ){
            Bplog::save("FALHOU getByNamesHostService SEM host_id", 2);
            return null;
        }

		return BmNagiosServices::getServicesAvailByHostID($host_id);
	}

    /** com arrays*/
    public static function create_aics($aics)
    {
        if(DEBUG >= 1) Bplog::save("--- INSERIR AICs ---");
        $oks = array();
        foreach ($aics as $aic) {
            $return = BpNagiosServices::create($aic);
            if($return->ErrStatus === 0){
                $oks[] = $return;
            }
        }
        return $oks;
    }

    public static function delete_aics($aics)
    {
        if(DEBUG >= 1) Bplog::save("--- Exclui AICs ---");
        $oks = array();
        foreach ($aics as $aic) {
            $return = BpNagiosServices::delete($aic);
            if($return->ErrStatus === 0){
                $oks[] = $return;
            }
        }
        return $oks;
    }

    public static function update_aics( $aics )
    {
        if(DEBUG >= 1) Bplog::save("--- Atualizar AICs ---");
        $oks = array();
        foreach ($aics as $aic) {
            $return =  BpNagiosServices::update( $aic );
            if($return->ErrStatus === 0){
                $oks[] = $return;
            }
        }
        return $oks;
    }
}
?>