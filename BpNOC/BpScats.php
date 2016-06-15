<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmScats.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeScats.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeScatTypes.php");
require_once(dirname(dirname(__FILE__))."/BmNOC/BmNagiosCommands.php");
require_once("BpScatContacts.php");
require_once("BpScatSazdays.php");
require_once("BpScatTypes.php");

class BpScats {

    public function create($scat)
    {
        $scat = BpScats::create_only_scat($scat);
        BpScatItens::create($scat->scat_itens);
        return $scat;
    }

	public function create_only_scat($scat)
    {

        /** não pode add sem nome */
		if($scat->scat_name == ''){
            Bplog::save("Catalogo sem nome: ".$scat->priax_id, 2);
			$scat->ErrStatus = 1;
			return $scat;
		}

        /** não pode add se já existe */
        $scat_opmon = BpScats::get_by_name($scat->scat_name);
        if( $scat_opmon ){
            Bplog::save("Ja existe catalogo com este nome: ".$scat->scat_name, 2);
            $scat_opmon->ErrStatus = 1;
            return $scat_opmon;
        }

        /** não pode add se não tiver itens */
        if(count($scat->scat_itens) <= 0){
            Bplog::save("Catalogo sem Itens, nao sera add: ".$scat->scat_name, 2);
            $scat_opmon->ErrStatus = 1;
            return $scat_opmon;
        }


		$business = BpSyncCatalog::getOrCreateHostScatBusiness();
		//Novo catalogo
		$scat->type = $business->scat_type_id; //tipo do catalogo: interface web(main config);

        $scat = BmScats::create($scat);
        if($scat->ErrStatus != 0){
            Bplog::save("Erro ao Add Scat: ".$scat->scat_name, 2);
            $scat->ErrStatus = 1;
            return $scat;
        }
        $scat_contacts = BpScatContacts::create($scat);
        if($scat_contacts->ErrStatus != 0){
            Bplog::save("Erro ao Add Scat Contacts: ".$scat->scat_name, 2);
            $scat->ErrStatus = 1;
            return $scat;
        }
        $scat_saz_days = BpScatSazdays::create($scat);
        if($scat_saz_days->ErrStatus != 0){
            Bplog::save("Erro ao Add Scat Saz Days: ".$scat->scat_name, 2);
            $scat->ErrStatus = 1;
            return $scat;
        }
         /** Add itens*/
		foreach ($scat->scat_itens as $key_scat_itens => $scat_iten) {
			$scat->scat_itens[$key_scat_itens]->scat_id = $scat->scat_id;
		}
		
		//$catalogItens = BpScatItens::InsereItens($scat->scat_itens);
		//var_dump($catalogItens);
		
		//Adicionar o AIC Catalogo ao IC de Catalogos
		$beNagiosServices = new BeNagiosServices;
		$beNagiosServices->host_id = $business->host_id; //Id do Host de catalogos
		$beNagiosServices->service_description = $scat->scat_name; // Mesmo nome do catalogo sera usado no nome do AIC
		$beNagiosServices->check_command = BmNagiosCommands::getIdByName('check_interop'); // comando de verificação dos catologos
		$beNagiosServices->use_template_id = 1;
		$beNagiosServices->command_parameter = 'priax/scat/priax_check_scat_status.php -i '.$scat->scat_id.' -d 2 ';
        $beNagiosServices->priax_id = null;
        $beNagiosServices->is_catalog = 1;
		BpNagiosServices::create($beNagiosServices);


		//setar o "opmon service_id" do catalogo ao opmoncatalogid do priax
		$bmPriax = new BmPriax;
		$foo3 = $bmPriax->setOpMonIDinPriaxCatalog($scat->scat_id, $scat->priax_id);
		//var_dump($foo3);

		return $scat;
	}


	public function update_scat_info($beScats)
    {
        Bplog::save("update_scat_info: ".$beScats->scat_name, 1);

		$aic = BpNagiosServices::get_service_by_host_name_and_service_description(CATALOG_PRINCIPAL, $beScats->service_name);
		$aic->service_description = $beScats->scat_name;
		$parameters = array('service_description');

        //var_dump($aic);
        //exit;
		BpNagiosServices::update_whith_parameter($aic,$parameters);
		$scat =  BmScats::update_scat_info($beScats);
		return $scat;
	}

	public function delete($beScats)
    {
        if(!$beScats->scat_id){
            Bplog::save("Erro delete sem scat_id: ".$beScats->scat_name , 2);
            $beScats->ErrStatus = 1;
            return $beScats;
        }

        Bplog::save("delete: ".$beScats->scat_name, 1);

		$foo = BmScats::delete($beScats);
		//var_dump($foo);
		$foo2 = BpScatContacts::delete_by_scat_id($beScats->scat_id);
		//var_dump($foo2);
		$foo3 = BpScatSazdays::delete_by_scat_id($beScats->scat_id);
		//var_dump($foo3);
		$foo4 = BpScatItens::delete_by_scat_id($beScats->scat_id);
		//var_dump($foo3);

		//deletar o aic correspondente
		//var_dump($beScats);
		$aic = BpNagiosServices::get_service_by_host_name_and_service_description(CATALOG_PRINCIPAL, $beScats->scat_name);
		BpNagiosServices::delete($aic);
		return $beScats;
	}

/**CRUDS ARRAYS*/

    public function create_scats($listScatsInsere)
    {
        if(count($listScatsInsere) <= 0 ) return $listScatsInsere;

        $scats = array();
        foreach ($listScatsInsere as $key_scat => $scat) {
            $scats[] = BpScats::create_only_scat($scat);
        }

        foreach ($listScatsInsere as $key_scat => $scat) {
            BpScatItens::create($scat->scat_itens);
        }

        return $scats;
    }

    public function delete_scats($listScats)
    {
        if(count($listScats) <= 0 ) return $listScats;

        $foo = array();
        foreach ($listScats as $key => $scat) {
            $foo[] = BpScats::delete($scat);
        }
        return $foo;
    }
    public function update_scats_info($listScats)
    {
        if(count($listScats) <= 0 ) return $listScats;

        $foo = array();
        foreach ($listScats as $key => $scat) {
            $foo[] = BpScats::update_scat_info($scat);
        }
        return $foo;
    }

    /**GETS*/

    public function get_by_id($id)
    {
        $foo = BmScats::Lista("WHERE id = " . $id, "", "");
        return $foo[0];
    }

    public function get_by_name($name)
    {
        $foo = BmScats::Lista("WHERE scat_name = '" . $name."'", "", "");
        return $foo[0];
    }

    public static function get_all()
    {
        return BmScats::Lista();
    }

    
    
}