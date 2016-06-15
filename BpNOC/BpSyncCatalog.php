<?php

class BpSyncCatalog {
    public static function composePriaxCatalogs($priaxScats)
    {
        $bmPriax = new BmPriax;
        if(DEBUG >= 1) Bplog::save(">>>>>>>>>>>>>>>>>>>> PRIAX - COMPOR CATALOGOS <<<<<<<<<<<<<<<<<<<<");

        //$array = array("tamanho" => "G", "cor" => "dourado");
        //print_r(array_values ($array));
        //var_dump($priaxScats); exit;
        // Q1 = $priaxScats
        // Q2 = $dependentsICs
        // Q3 = $dependentsCatalogs
        // Q4 = $dependentsInDependentsCatalogs
        // Q5 = $ICsToCatalog
        // Q6 = $arrScatsForCatalog


        foreach ($priaxScats as $key_scat => $scat) {

            $dependentsCatalogs = array();
            $dependentsInDependentsCatalogs = array();

            //busca ICs com relação de dependencia
            if(DEBUG >= 1) Bplog::save("GET DEPENDENTS $scat->priax_id $scat->DN");
            //Q2
            $dependentsICs = $bmPriax->getPriaxICsDependentsAssets($scat->priax_id);
            //var_dump('$dependentsICs',$dependentsICs);

            if(DEBUG >= 1) Bplog::save("DEPENDENTS de: $scat->DN");
            foreach ($dependentsICs as $value) {
                Bplog::save(" $value->DN - priax_id: $value->priax_id");
            }

            //Quais dependents é catalogo?
            if(DEBUG >= 1) Bplog::save("Quais dependents é catalogo de: $scat->scat_name ?",1);
            $temScatDep = false;
            foreach ($dependentsICs as $key => $dependentsIC) {
                if($dependentsIC->catalog_active == "Yes"){
                    $temScatDep = true;
                    Bplog::save(" -> Catalogo: $dependentsIC->scat_name DN: $dependentsIC->DN",1 );
                    $dependentsCatalogs[] = $dependentsIC;
                    //unset($dependentsICs[$key]);
                }
            }
            if($temScatDep == false) Bplog::save("Nenhum catalogo na lista de dependentes",1);
            //Resposta é lista de dependents que são catalogos: $dependentsCatalogs -> Q3
            //var_dump('$dependentsICs',$dependentsICs);
            //var_dump('$dependentsCatalogs',$dependentsCatalogs);

            //Listar todos os dependentes dos catalogos contidos no dependents deste catalogo Q4
            foreach ($dependentsCatalogs as $dependentsCatalogo) {
                if(DEBUG >= 1) Bplog::save("SCAT $scat->DN dependent catalog $dependentsCatalogo->priax_id -> tem ICs dependentes?");
                $iCsDependentsAssets = $bmPriax->getPriaxICsDependentsAssets($dependentsCatalogo->priax_id);
                //teste - $foo = $bmPriax->getPriaxICsDependentsAssets('19762');
                if(!empty($iCsDependentsAssets)) {
                    //Q4
                    $dependentsInDependentsCatalogs = array_merge(array_values($dependentsInDependentsCatalogs), array_values($iCsDependentsAssets));
                }
            }
            //Resposta de Lista todos os dependentes dos catalogos contidos no dependents deste catalogo - Q4
            //var_dump($dependentsInDependentsCatalogs);

            //ICs que farão parte do catalogo - Q5
            $ICsToCatalog = $dependentsICs;

            foreach ($dependentsInDependentsCatalogs as $dependent) {
                foreach ($dependentsICs as $key_d => $scat_dependent) {
                    //print "IF  $scat_dependent->priax_id == $dependent->priax_id \n";
                    if ($scat_dependent->priax_id == $dependent->priax_id) {
                        unset($ICsToCatalog[$key_d]);
                        //	print "Remove $scat_dependent->DN from dependents Catalog \n";
                    }
                }
            }
            //var_dump('*****Serao add ICsToCatalog*****');
            //var_dump('$ICsToCatalog',$ICsToCatalog);
            //var_dump('$dependentsICs',$dependentsICs);
            //Array com os AICs avail
            $AICsAvailScat = array();
            //AICs do proprio catalogo
            if(DEBUG >= 1) Bplog::save("Se o Catalogo for um IC Busca Itens Validos dele mesmo: ".$scat->scat_name );
            //var_dump('AICs do proprio catalogo',$scat);
            if(is_numeric($scat->host_id)) {
                $aic = BpNagiosServices::get_aics_avail_by_host_id($scat->host_id);
                //var_dump('AIC AVAIL',$aic );
                $AICsAvailScat = array_merge(array_values($AICsAvailScat), array_values($aic));
            }else{
                if(DEBUG >= 1) Bplog::save("Este catalogo nao e um IC no OpMon: ".$scat->scat_name,1 );
            }

            //var_dump('**** AICS AVAIL',$AICsAvailScat );

            //buscar apenas AICs de disponibilidade de todos os "ICs NAO CATALOGOS" com relação de dependencia
            //var_dump('$ICsToCatalog',$ICsToCatalog);
            foreach ($ICsToCatalog as $key_ic => $ic) {
                $aics = array();
                //var_dump('IIIIIIIC',$ic);
                if ($ic->catalog_active != "Yes") {//AICs somente se NAO for CATALOGO
                    //var_dump('--- NAO eh catalogo ---- ' );
                    $aics_result = BpNagiosServices::get_aics_avail_by_host_id($ic->host_id);

                    $aics = array();
                    foreach($aics_result as $key => $aic){
                        $aic->catalog_active = "No";
                        $aics[] = $aic;
                    }
                    //var_dump($aics); exit;

                }else{ //add um catalogo dentro do outro catalogo
                    //var_dump('--- eh catalogo ---- ' );
                    //Se ja EXISTIR o CATALOGO
                    $aic = BpNagiosServices::get_service_by_host_name_and_service_description(CATALOG_PRINCIPAL, $ic->scat_name);

                    //var_dump('AAAAAIIICCC', $aic);
                    if(!$aic){//Se o CATALOGO NAO existir cria-se um AIC com apenas o nome
                        //var_dump('>>>>>> Nao existe ainda, ic: ', $ic);exit;
                        $aic = new BeNagiosServices();
                        $aic->service_description = $ic->scat_name;
                        $aic->catalog_active = "Yes";
                    }

                    $aics = array($aic);
                    //var_dump($aic);
                }
                $AICsAvailScat = array_merge(array_values($AICsAvailScat), array_values($aics));
                //var_dump('**** AICS AVAIL 2222',$AICsAvailScat );
            }

            //var_dump('**** AICS AVAIL 2222',$AICsAvailScat );

            //Criar array de itens do catalogo
            $arrItens = array();
            $itenLevel = 1;
            foreach ($AICsAvailScat as $key => $aic) {

                //adiciona os AICs a um array para criar o catalogo
                $iten = new BeScatItens;
                $iten->scat_id = $scat->scat_id;
                $iten->host_name = $aic->host_id;
                $iten->service_name = $aic->service_id;
                $iten->level = $itenLevel;
                $iten->catalog_active = $aic->catalog_active;
                $iten->service_description = $aic->service_description;
                $iten->host_description = $aic->host_name;
                $itenLevel++;
                $arrItens[] = $iten;
            }

            if(DEBUG >= 1) Bplog::save("ITENS VALIDOS - General Name: $scat->DN - IC Name: $scat->host_name");
            foreach ($arrItens as $value) {
                if(DEBUG >= 1) Bplog::save(" $scat->scat_name : $value->service_description");
            }

            //Monta o Catalago
            $new_scat = new BeScats;
            $new_scat->scat_name = $priaxScats[$key_scat]->scat_name;
            $new_scat->DN = $scat->DN;
            $new_scat->scat_id = $scat->scat_id;
            $new_scat->scat_itens = $arrItens;
            $new_scat->priax_id = $scat->priax_id;

            //Adiciona a lista de catalogos
            $priaxScats[$key_scat] = $new_scat;
        }

        foreach ($priaxScats as $key => $value) {
            if (count($value->scat_itens) < 1) {
                if(DEBUG >= 1) Bplog::save("Catalogo $value->scat_name sem itens, adicione itens de disponibilidade a este catalogo, removendo este catalogo da lista.",2);
                unset($priaxScats[$key]);
            }
        }

        if(DEBUG >= 1) Bplog::save(">>>>>>>>>>>>>>>>>>>> PRIAX - FIM COMPOR CATALOGOS <<<<<<<<<<<<<<<<<<<<");
        //var_dump("priaxScats");
        //var_dump($priaxScats);
        //exit;
        return $priaxScats;
    }

    public static function getOpMonScats()
    {
        if( DEBUG >= 1 ) Bplog::save(">>>>>>>>>>>>>>>>>>>> OPMON - INICIO COMPOR CATALOGOS <<<<<<<<<<<<<<<<<<<<");
        $opmonScats = BpScats::get_all();
        //var_dump($opmonScats);

        foreach ($opmonScats as $key => $scat) {
            //var_dump($scat);
            $opmonScats[$key]->scat_itens = BpScatItens::get_all_by_scat_id($scat->scat_id);
        }

        //log
        foreach ($opmonScats as $scat) {
            if(DEBUG >= 1) Bplog::save("OPMON Catalog $scat->scat_name");
            foreach ($scat->scat_itens as $iten) {
                //var_dump($iten);
                $beNagiosServices = BpNagiosServices::get_by_id($iten->service_name);
                $beNagiosHosts = BpNagiosHosts::get_by_id($iten->host_name);
                if(DEBUG >= 1) Bplog::save(" ".$beNagiosHosts->host_name .":".$beNagiosServices->service_description);
            }
        }
        if( DEBUG >= 1 ) Bplog::save(">>>>>>>>>>>>>>>>>>>> OPMON - FIM COMPOR CATALOGOS <<<<<<<<<<<<<<<<<<<<");
        return $opmonScats;
    }

    public static function getPriaxScats()
    {
        $bmPriax = new BmPriax;

        //Get dos Nodos com opmon catalog = yes
        $priaxScats = $bmPriax->getPriaxScats(); //"scat_name" "priax_id"

        //Forma os catalogos
        $priaxScats = BpSyncCatalog::composePriaxCatalogs($priaxScats);

        //log
        foreach ($priaxScats as $scat) {
            if(DEBUG >= 1) Bplog::save("PRIAX CATALOG $scat->scat_name");
            foreach ($scat->scat_itens as $iten) {
                //$beNagiosServices = BpNagiosServices::getByID($iten->service_name);
                $foo = CATALOG_PRINCIPAL;
                //var_dump($iten->host_name);
                if($iten->host_name != NULL) {
                    $beNagiosHosts = BpNagiosHosts::get_by_id($iten->host_name);
                    $foo = $beNagiosHosts->host_name;
                }
                if(DEBUG >= 1) Bplog::save( " ".$foo.":".$iten->service_description);
            }
        }
        //var_dump($priaxScats);exit;
        return $priaxScats;
    }

    public function getOrCreateHostScatBusiness()
    {
        $beNagiosHosts = new BeNagiosHosts;
        $beNagiosHosts->host_name = CATALOG_PRINCIPAL;
        //Se não existir o IC para os catalogos ele cria
        $searchIcScats = BpNagiosHosts::getByName($beNagiosHosts->host_name);

        if($searchIcScats) {
            $IcScats = $searchIcScats;
        }else{
            //var_dump("nao ja existia");
            $beNagiosHosts->alias = CATALOG_PRINCIPAL;
            $beNagiosHosts->use_template_id = '1';
            $beNagiosHosts->address = 'localhost';
            //get command
            $beNagiosHosts->check_command = BmNagiosCommands::getIdByName('system-discovery-icmp'); // comando de icmp
            $beNagiosHosts->priax_id = null;
            $IcScats = BpNagiosHosts::create($beNagiosHosts);
        }

        //Scat Types
        $scatTypes = BpScatTypes::getByName($beNagiosHosts->host_name);
        //se nao existir scat type ele criará
        if(empty($scatTypes)) {
            $scatTypes = new BeScatTypes;
            $scatTypes->scat_type_name = $beNagiosHosts->host_name ;
            $scatTypes->host_id = $IcScats->host_id;
            $scatTypes->host_template_id = 1;
            $scatTypes->service_template_id = 1;
            $scatTypes = BpScatTypes::insere($scatTypes);
        }else{
            $scatTypes = $scatTypes[0];
        }

        //se id business  = host_id types
        if ( $scatTypes->host_id != $IcScats->host_id ) {
            //var_dump("Faz update opcfg types");
            $scatTypes->host_id = $IcScats->host_id;
            BpScatTypes::update($scatTypes);
        }
        //set host business na tabela opcfg:scat_types
        $IcScats->scat_type_id = $scatTypes->scat_type_id;
        return $IcScats;
    }

    function remove_scat_unnamed($priaxScats = array()){
        /** Excluir catalogos sem nome da lista do priax e add a lista de catalogo com erros*/
        $arr_not_ok = array();
        foreach ($priaxScats as $key => $scat) {
            if($scat->scat_name == ''){
                $arr_not_ok[] = $scat;
                var_dump($scat);
                Bplog::save("Excluir da LISTA o CATALOGO sem nome: $scat->priax_id",2);
                unset($priaxScats[$key]);

            }
            /*
            foreach ($scat->scat_itens as $k_iten => $iten) {
                //var_dump($scat_itens);
                if($iten->service_description == ''){
                    $arr_not_ok[] = $iten;
                    var_dump($scat->scat_name, $iten);
                    Bplog::save("Excluir da LISTA o ITEM sem nome: ",2);
                    unset($priaxScats[$key]->scat_itens[$k_iten]);
                    //var_dump($iten);
                }
            }
            */
        }
        return array($priaxScats, $arr_not_ok);
    }

    function crud($scats_priax = array(), $scats_opmon = array())
    {

        /** Cria arrays de CRUD dos Catalogos  */
        $scats_delete = array();
        $scats_create = array();
        $scats_edit_info = array();
        $scats_edit_itens = array();

        //se NAO existe no PRIAX DELETA o catalogo
        foreach ($scats_opmon as $scat) {
            if(!BpSync::Exist($scats_priax,$scat,"scat_id")){
                $scats_delete[] = $scat;
            }
        }

        //se NAO existe no OPMON INCLUI o catalogo;
        foreach ($scats_priax as $scat) {
            if(!BpSync::Exist($scats_opmon,$scat,"scat_id")){
                $scats_create[] = $scat;
            }
        }

        //existe e infos diferentes entra na lista de edição
        foreach ($scats_priax as $scat_priax) {
            $scat_opmon = BpSync::Exist($scats_opmon,$scat_priax,"scat_id");
            if($scat_opmon){
                //Infos
                if(!BpSync::equalObjsByParameter($scat_opmon, $scat_priax, array('scat_name'))){
                    $scat_priax->service_name = $scat_opmon->scat_name;
                    $scats_edit_info[] = $scat_priax;
                }

                //ITENS -> existe no OPMON e nao no PRIAX
                foreach ($scat_opmon->scat_itens as $scat_opmon_iten) {
                    if(!BpSync::Exist($scat_priax->scat_itens,$scat_opmon_iten,'service_name')){
                        $scats_edit_itens[$scat_priax->scat_id] = $scat_priax;
                    }
                }

                //ITENS -> existe no PRIAX e nao no OPMON
                foreach ($scat_priax->scat_itens as $scat_priax_iten) {
                    if(!BpSync::Exist($scat_opmon->scat_itens,$scat_priax_iten,'service_name')){
                        $scats_edit_itens[$scat_priax->scat_id] = $scat_priax;
                    }
                }
            }
        }

        return array( $scats_create, $scats_delete, $scats_edit_info, $scats_edit_itens );
    }

}