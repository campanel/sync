<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmApiOpmon.php");
require_once(dirname(dirname(__FILE__))."/BmNOC/BmNagiosHosts.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeNagiosHostExtendedInfo.php");
require_once(dirname(dirname(__FILE__))."/BpNOC/BpScatTypes.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeScatTypes.php");
require_once("BpNagiosHostExtendedInfo.php");
require_once("BpNagiosServices.php");

class BpNagiosHosts {
    public static function create($ic) {
        /**
         * cria host no opmon e seu servicos caso exista
         **/
        if(DEBUG >= 2) Bplog::save("IC INCLUIR $ic->host_name");
        /**Validação**/
        if (preg_match('/[^A-Za-z0-9\-\/._]/', $ic->host_name) || empty($ic->host_name) || empty($ic->address) ){
            $ic->ErrStatus = 1;
            Bplog::save("Insere IC, Nome ou IP do host invalido ou vazio, DN: $ic->DN priax_id: ".$ic->priax_id , 2);
            return $ic;
        }

        /***Se ja existir IC com mesmo nome não add*/
        $ic_opmon = BpNagiosHosts::getByName($ic->host_name);
        //var_dump($ic_opmon);exit;
        if($ic_opmon){
            $ic->ErrStatus = 1;
            Bplog::save("IC NAME: $ic->host_name EXISTENTE, OpMon nao pode ter ICs com mesmo nome, priax_id: ".$ic->priax_id , 2);
            return $ic;
        }

        $host = array(
            "host_name" => $ic->host_name,
            "address" => $ic->address,
            "alias" => $ic->alias,
            "use_template_id" => $ic->use_template_id,
        );

        if($ic->check_command != ''){
            $host['check_command'] = $ic->check_command;
        }

        $ic->host_id = BmApiOpmon::add_host($host);

        if(!is_numeric($ic->host_id ) || $ic->host_id == 0 ){
            Bplog::save("Insere IC - Erro na API OPMON, priax_id: ".$ic->priax_id , 2);
            $ic->ErrStatus = 1;
            Bplog::save($ic, 2);
            return $ic;
        }

        $info = BpNagiosHostExtendedInfo::updateByBeHost($ic);
        if ($info->ErrStatus != 0) {
            Bplog::save(" FALHOU - Atualizar INFO IC OPMON: $ic->host_name",2);
            $ic->ErrStatus = 1;
            return $ic;
        }

        if ($ic->priax_id != null AND $ic->priax_id != 0 AND $ic->priax_id != '' ) {
            //Grava Id OpMon no Priax
            $bmPriax = new BmPriax();
            $bmPriax->setOpMonIDinPriaxNode($ic->host_id, $ic->priax_id);
            //adiciona o id do Priax ao objeto
            if(DEBUG >= 2) Bplog::save('Gravando host_id no priax -  '.$ic->host_name .' ID Priax: '.$ic->priax_id, 0);
        }else{
            Bplog::save("IC NAO possui priax_id, se for catalogo desconsiderar, host: ".$ic->host_name , 2);
        }

        /**
         * ADD services
         * Se existir priax_id
         */
        if($ic->priax_id != null AND $ic->priax_id != 0 AND $ic->priax_id != '' ) {
            if(DEBUG >= 2)  Bplog::save("GET AICs PRIAX ".$ic->host_name);
            $bmPriax = new BmPriax;
            $aics = $bmPriax->getAICsPriaxArray($ic);
            if(DEBUG >= 2) Bplog::save("EXISTE KPIs?");
            if( count($aics) > 0 ){
                if(DEBUG >= 2) Bplog::save(" SIM INSERE AICs");
                BpNagiosServices::create_aics($aics);
            }else{
                if(DEBUG >= 2) Bplog::save(" NAO ".count($aics));
            }
        }else{
            Bplog::save("Nao existe priax_id $ic->host_name , nao buscara por AICs no PRIAX",1);
        }

        if(DEBUG >= 2) Bplog::save("IC INCLUIDO $ic->host_name",0);

        /**Insere no banco os ids*/
        BpSyncDB::insere_host($ic->host_id, $ic->priax_id ,  $ic->host_name);

        return $ic;
    }

    public static function delete($ic)
    {
        if(DEBUG >= 2) Bplog::save('EXCLUIR IC e TODOS AICs do OPMON:'. $ic->host_name);
        /**Validação**/
        if($ic->host_id == '' || !$ic->host_id){
            Bplog::save("FALHOU DELETE host SEM host_id, priax_id:".$ic->priax_id, 2);
            $ic->ErrStatus = 1;
            return $ic;
        }

        $ret_delete_ic = BpNagiosHosts::delete_by_id($ic->host_id);

        return $ret_delete_ic;
    }

    public static function delete_by_id($host_id)
    {
        /**Validação**/
        if($host_id == '' || !$host_id ){
            Bplog::save("FALHOU delete_by_id host SEM host_id", 2);
            $status = new BeBase();
            $status->ErrStatus = 1;
            return $status;
        }

        $retorno_ic = BmApiOpmon::delete_host_by_id($host_id);

        if($retorno_ic == false){
            Bplog::save("delete_by_id IC - Erro na API OPMON" , 2);
            $status = new BeBase();
            $status->ErrStatus = 1;
            return $status;
        }else{
            $status = new BeBase();
            $status->ErrStatus = 0;
        }

        if(DEBUG >= 2) Bplog::save(" delete_by_id IC OPMON ok",0);
        /**Deletar IDs**/
        BpSyncDB::delete_host_and_services_by_host_id($host_id);
        return $status;
    }

    public static function update($ic) {
        global $ic_parameters_to_check;
        if(DEBUG >= 2) Bplog::save("Atualizar IC OPMON: $ic->host_name");
        /**Validação**/
        if( $ic_parameters_to_check == '' || !$ic_parameters_to_check ){
            Bplog::save("FALHOU UPDATE HOST SEM PARAMETROS GLOBAIS", 2);
            $ic->ErrStatus = 1;
            return $ic;
        }

        if($ic->host_name == '' || !$ic->host_name  ){
            Bplog::save("FALHOU UPDATE HOST SEM host_name,  DN: $ic->DN priax_id:".$ic->priax_id, 2);
            $ic->ErrStatus = 1;
            return $ic;
        }

        /** Validar nome, se o nome for alterado ele nao pode ter nome de IC existente**/

        $opmon_ic = BpNagiosHosts::get_by_id($ic->host_id);
        if($opmon_ic->host_name != $ic->host_name){
            $ic_existente = BpNagiosHosts::getByName($ic->host_name);

            if($ic_existente->host_id){
                Bplog::save("FALHOU UPDATE IC,host_name não pode ser igual a um existente, priax_id:".$ic->priax_id, 2);
                $ic->ErrStatus = 1;
                return $ic;
            }
        }

        $parameters = array_diff($ic_parameters_to_check, array("icon_image","action_url"));

        $ic = BmNagiosHosts::update($ic,$parameters);
        if ($ic->ErrStatus != 0) {
            Bplog::save(" FALHOU - Atualizar IC OPMON: $ic->host_name",2);
            $ic->ErrStatus = 1;
            return $ic;
        }

        $info = BpNagiosHostExtendedInfo::updateByBeHost($ic);
        if ($info->ErrStatus != 0) {
            Bplog::save(" FALHOU - Atualizar INFO IC OPMON: $ic->host_name",2);
            $ic->ErrStatus = 1;
            return $ic;
        }
        if(DEBUG >= 2) Bplog::save("Atualizar IC OPMON: $ic->host_name",0);
        return $ic;
    }

    public function getByName($name) {
        /**Validação**/
        if( $name == '' || !$name ){
            if(DEBUG >= 2) Bplog::save("getByName SEM name", 1);
            return null;
        }

        //var_dump($beNagiosHosts);
        $ic = BmNagiosHosts::query("WHERE host_name = '" . $name."'", "", "");
        if(count($ic) == 0){
            return null;
        }
        return $ic[0];
    }

    public function get_by_id($id) {
        /**Validação**/
        if( $id == '' || !$id ){
            Bplog::save("FALHOU getByID SEM id", 2);
            return null;
        }
        $ic = BmNagiosHosts::query("WHERE host_id = '" . $id."'", "", "");
        return $ic[0];
    }

    public static function getAll() {
        $listICs = BmNagiosHosts::query(null,null,null);
        //var_dump($listICs); exit;
        $listbeNagiosHostsOpmon = array();
        //adiciona infos a cada um IC, pois vem de uma tabela separada
        foreach ($listICs as $beNagiosHostsOpmon) {
            $result = BpNagiosHostExtendedInfo::getByHostId($beNagiosHostsOpmon->host_id);
            $beNagiosHostsOpmon->action_url = $result->action_url;
            $beNagiosHostsOpmon->icon_image = $result->icon_image;
            $listbeNagiosHostsOpmon[] = $beNagiosHostsOpmon;
        }
        //retirar os catalogos da lista de ICs
        $scatTypes = BpScatTypes::getAll();
        foreach ($scatTypes as $scatType) {
            //var_dump($scatType);exit;
            foreach ($listbeNagiosHostsOpmon as $key_host => $host) {
                if($host->host_name == $scatType->scat_type_name ){
                    unset($listbeNagiosHostsOpmon[$key_host]);
                }
            }
        }
        $ics = array();
        foreach($listbeNagiosHostsOpmon as $ic){
            $ics[$ic->host_id] = $ic;
        }
        return $ics ;
    }

    /**plural**/
    public static function create_ics($ics)
    {
        if(DEBUG >= 1) Bplog::save("--- INSERIR ICs ---");
        $oks = array();
        foreach ($ics as $ic) {
            $return = BpNagiosHosts::create($ic);
            if($return->ErrStatus === 0){
                $oks[] = $return;
            }
        }
        return $oks;
    }

    public static function delete_ics($ics)
    {
        if(DEBUG >= 1) Bplog::save("--- Exclui ICs ---");
        $oks = array();
        foreach ($ics as $ic) {
            $return = BpNagiosHosts::delete($ic);
            if($return->ErrStatus === 0){
                $oks[] = $return;
            }
        }
        return $oks;
    }

    public static function update_ics( $ics )
    {
        if(DEBUG >= 1) Bplog::save("--- Atualizar ICs ---");
        $oks = array();
        foreach ($ics as $ic) {
            $return = BpNagiosHosts::update( $ic );
            if($return->ErrStatus === 0){
                $oks[] = $return;
            }
        }
        return $oks;
    }


}

?>
