<?php
require_once(dirname(dirname(__FILE__))."/BeNOC/BeBase.php");
include dirname(dirname(__FILE__))."/config-inc.php";
include dirname(dirname(__FILE__))."/globals.php";

class BmPriax{
/*funcoes para ICs*/

	//get para Ics do Priax em array
	public function getPriaxICs()
	{
        $ret = BmPriax::getPriaxNodes(NULL);
        /**VALIDA CONEXAO COM O PRIAX*/
        if($ret->ErrStatus){
            Bplog::save("FALHA CONEXAO PRIAX  $ret->ErrMensagem",2);
            return false;
        }
        return $ret;
	}

	public function getPriaxScats()
	{
		//var_dump(BmPriax::getPriaxNodes('scats')); exit;
		return BmPriax::getPriaxNodes('scats');
	}

	public function getPriaxICbyID($id)
	{
		//var_dump($id);
		$listIC = BmPriax::getFormattedNodesID($id);

        $listIC = BmPriax::normalizaArrayNodesToArrayICs(array($listIC));
		return $listIC[0];
	}

    public function getPriaxICbyKPI($id)
    {
        //var_dump("getPriaxICbyKPI",$id);
        $listIC = BmPriax::getNodeRelatedToByKpi($id);
        $listIC = BmPriax::normalizaNodeToIC(array($listIC));
        return $listIC[0];
    }

	public function getPriaxICsbyArrayIDs($ids)
	{
		$result = array();
		foreach ($ids as $id) {
			$result[] = getPriaxICbyID($id);
		}
		return 	$result;
	}

	public function getPriaxNodes($type)
	{
		$lstIcPriax = BmPriax::getFormattedNodesICs($type);
		//var_dump(BmPriax::normalizaArrayNodesToArrayICs($lstIcPriax)); exit;
		return BmPriax::normalizaArrayNodesToArrayICs($lstIcPriax);
	}

	public function getPriaxDependents($node_id)
	{
		$lstIcPriax = BmPriax::getDependentNodes($node_id);
		//var_dump($lstIcPriax);
		$ics = array();
		if($lstIcPriax->ErrStatus == 0){
			foreach ($lstIcPriax->Node as $obj) {
				//var_dump($obj->id);
				$ics[] = BmPriax::getFormattedNodesID($obj->id);
			}
			$dependentes = BmPriax::normalizaArrayNodesToArrayICs($ics);
			return $dependentes;
		}
		return $lstIcPriax;
	}

	public function getPriaxICsDependentsAssets($id_priax)
    {
		//var_dump('----------------getPriaxDependents');
		$arrDependents = BmPriax::getPriaxDependents($id_priax);
		//var_dump($arrDependents); exit;
		//somente os ICs cadastrados no opmon
		$arrScatICs = array();
		foreach ($arrDependents as $key => $value) {
			if( ( $value->opmon_active == 'Yes' and $value->host_id != null) || $value->catalog_active == 'Yes'  ){
				$arrScatICs[] = $value;
			}
		}
		//var_dump($arrScatICs);
		return $arrScatICs;
		//return $arrDependents;
	}

	private function getFormattedNodesICs($type = null)
    {
		try {
			return BmPriax::getListNodes($type);
		}
		catch (exception $ex) {
			$beBase = new BeBase();
			$beBase->ErrStatus = 1;
			Bplog::save("BmPriax - getFormattedNodesICs: ".$ex->getMessage(), 2);
			return $beBase;
		}
	}

    function get_priax_id_by_host_id($id)
    {
        Bplog::save("Buscando ID no Priax sobre o IC:".$id);

        //DISABLE WSDL CACHE.
        ini_set("soap.wsdl_cache_enabled", "0");
        ini_set('default_socket_timeout', 600);

        //Retorna array
        $options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);

        $client = new SoapClient(PRIAXURL, $options);

        $param = array(
            'queryNodeObject'=>array(
                'queryNode'=>array(
                    'attributes'=>array(
                        'entry'=>array(
                            '0'=>array('key'=>'OpMon\OpMonID','value' => $id)
                        )
                    )
                ),
                'resultNode'=>array(
                    'attributes'=>array(
                        'entry'=>array(
                            '0'=>array('key'=>'OpMon\OpMonCIName','value' => ''),
                        )
                    )
                )
            )
        );

        $obj = $client->getFormattedNodes($param);
        return $obj->Node[0]->id;
    }

    function get_priax_id_by_service_id($id)
    {
        Bplog::save("Buscando ID no Priax sobre o AIC:".$id);

        //DISABLE WSDL CACHE.
        ini_set("soap.wsdl_cache_enabled", "0");
        ini_set('default_socket_timeout', 600);

        //Retorna array
        $options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);

        $client = new SoapClient(PRIAXURL, $options);

        $param = array(
            'queryNodeObject'=>array(
                'queryNode'=>array(
                    'attributes'=>array(
                        'entry'=>array(
                            '0'=>array('key'=>'OpMon\OpMonKpiID','value' => $id)
                        )
                    )
                ),
                'resultNode'=>array(
                    'attributes'=>array(
                        'entry'=>array(
                            '0'=>array('key'=>'OpMon\ServiceName','value' => ''),
                        )
                    )
                )
            )
        );

        $obj = $client->getFormattedNodes($param);
        return $obj->Node[0]->id;
    }

	private function getFormattedNodesID($id)
    {
		try {
            Bplog::save("Buscando informacoes no Priax sobre o BEING:".$id);

			//DISABLE WSDL CACHE.
    		ini_set("soap.wsdl_cache_enabled", "0");
    		ini_set('default_socket_timeout', 600);

			//Retorna array
			$options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);

			$client = new SoapClient(PRIAXURL, $options);

			$param = array(
				'queryNodeObject'=>array(
					'queryNode'=>array('attributes'=>'', 'id'=>$id),
					'resultNode'=>array(
						'attributes'=>array(
							'entry'=>array(
								'0'=>array('key'=>'OpMon\OpMonCIName','value' => ''),
								'1'=>array('key'=>'OpMon\OpMonDescription','value' => ''),
								'2'=>array('key'=>'OpMon\OpMonMonitoringAddress','value' => ''),
								'3'=>array('key'=>'OpMon\OpmonCITemplate','value' => ''),
								'4'=>array('key'=>'OpMon\OpMonID','value' => ''),
								'5'=>array('key'=>'OpMon\OpMonActive','value' => ''),
								'6'=>array('key'=>'OpMon\OpMonCIIcon','value' => ''),
								'7'=>array('key'=>'OpMon\OpMonCIActionsPage','value' => ''),
								'8'=>array('key'=>'OpMon\OpMonCatalogName','value' => ''),
								'9'=>array('key'=>'OpMon\OpMonCatalogID','value' => ''),
								'10'=>array('key'=>'Basic\NAME','value' => ''),
								'11'=>array('key'=>'OpMon\OpMonCatalog','value' => ''),
								'12'=>array('key'=>'DN','value' => ''),
								'13'=>array('key'=>'OpMonAgentConfiguration\SNMPComunity','value' => ''),
								'14'=>array('key'=>'OpMonAgentConfiguration\SNMPPort','value' => ''),
								'15'=>array('key'=>'OpMonAgentConfiguration\SNMPVersion','value' => ''),
								'16'=>array('key'=>'OpMonAgentConfiguration\SNMPCommunityID','value' => ''),
								'17'=>array('key'=>'OpMonAgentConfiguration\SNMPUser','value' => ''),
								'18'=>array('key'=>'OpMonAgentConfiguration\SNMPPass','value' => '')
							)
						)
					)
				)
			);

			//var_dump($client->getFormattedNodes($param)); exit;
			return $client->getFormattedNodes($param);
		}
        catch (exception $ex) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("BmPriax - getFormattedNodesID: ".$ex->getMessage(), 2);
            return $beBase;
        }
	}

    private function getNodeRelatedToByKpi($id)
    {
        try {
            Bplog::save("Buscando informacoes no Priax sobre o BEING PAI do KPI:".$id);

            //DISABLE WSDL CACHE.
            ini_set("soap.wsdl_cache_enabled", "0");
            ini_set('default_socket_timeout', 600);

            //Retorna array
            $options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);

            $client = new SoapClient(PRIAXURL, $options);

            $param = array(
               // 'queryNodeObject'=>array(
                    'KpiNode'=>array('attributes'=>'', 'id'=>$id),
                    'ReturnAttribute'=>array(
                        '0'=>'OpMon\OpMonCIName',
                        '1'=>'OpMon\OpMonDescription',
                        '2'=>'OpMon\OpMonMonitoringAddress',
                        '3'=>'OpMon\OpmonCITemplate',
                        '4'=>'OpMon\OpMonID',
                        '5'=>'OpMon\OpMonActive',
                        '6'=>'OpMon\OpMonCIIcon',
                        '7'=>'OpMon\OpMonCIActionsPage',
                        '8'=>'OpMon\OpMonCatalogName',
                        '9'=>'OpMon\OpMonCatalogID',
                        '10'=>'Basic\NAME',
                        '11'=>'OpMon\OpMonCatalog',
                        '12'=>'DN',
                        '13'=>'OpMonAgentConfiguration\SNMPComunity',
                        '14'=>'OpMonAgentConfiguration\SNMPPort',
                        '15'=>'OpMonAgentConfiguration\SNMPVersion',
                        '16'=>'OpMonAgentConfiguration\SNMPCommunityID',
                        '17'=>'OpMonAgentConfiguration\SNMPUser',
                        '18'=>'OpMonAgentConfiguration\SNMPPass'
                    )
            );
            $ret = $client->getNodeRelatedToByKpi($param);
            //var_dump($ret); exit;
            return $ret;
        }
        catch (exception $ex) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("BmPriax - getFormattedNodesICs: ".$ex->getMessage(), 2);
            return $beBase;
        }
    }

	public function getListNodes($type = null)
    {
		try {

			//DISABLE WSDL CACHE.
    		ini_set("soap.wsdl_cache_enabled", "0");
    		ini_set('default_socket_timeout', 600);

			//Retorna array
			$options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);

			$client = new SoapClient(PRIAXURL, $options);

			if($type == 'scats'){
				$queryNode = array(
					'attributes'=>array(
						'entry'=> array(
							'0'=>array('key'=>'OpMon\OpMonCatalog','value' => 'Yes')
						)
					)
				);
			}else{
				$queryNode = array(
					'attributes'=>array(
						'entry'=> array(
							'0'=>array('key'=>'OpMon\OpMonActive','value' => 'Yes'),
						)
					)
				);
			}

			$param = array('arg0'=>$queryNode );

			//var_dump($client->getListIdNamesForNodes($param)); exit;

			$arrObjectsNodes = $client->getListIdNamesForNodes($param);

			$nodes = array();
			foreach ($arrObjectsNodes->node as $key => $value) {
				$nodes[] = BmPriax::getFormattedNodesID($value->id);
			}

			return $nodes;
		}
        catch (exception $ex) {
            Bplog::save("BmPriax - getListNodes: ".$ex->getMessage(), 2);
            exit;
        }
	}

	public function getDependentNodes($node_id)
    {
		//$node_id = '14071';
		try {

			//DISABLE WSDL CACHE.
    		ini_set("soap.wsdl_cache_enabled", "0");

			//Retorna array
			$options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);
			$client = new SoapClient(PRIAXURL, $options);
			$param = array('Node'=>array('attributes'=>'','id'=>$node_id));

			//var_dump($client->getDependentNodes($param)); exit;
			return $client->getDependentNodes($param);
		}
		catch (exception $ex) {
            Bplog::save("BmPriax - getDependentNodes: ".$ex->getMessage(), 2);
			exit;
		}
	}

	//transforma objetos de node em array de ICs
	private static function normalizaNodeToIC($lstIcPriax, $type="IC")
	{
		//var_dump($lstIcPriax); exit;
		$arrIcs = array();
		foreach ($lstIcPriax as $node) {

			$beNagiosHosts = new BeNagiosHosts();
			$beNagiosHosts->priax_id = $node->Node->id;


			foreach ( $node->Node->attributes->entry as $entry)
			{
                //var_dump($entry); exit;

				switch ($entry->key) {
                    case 'OpMon\OpMonCIName':
                        $beNagiosHosts->host_name = $entry->value;
                        break;
                    case 'OpMon\OpMonDescription':
                        $beNagiosHosts->alias = $entry->value;
                        break;
                    case 'OpMon\OpMonMonitoringAddress':
                        $beNagiosHosts->address = $entry->value;
                        break;
                    case 'OpMon\OpMonID':
                        $beNagiosHosts->host_id = $entry->value;
                        break;
                    case 'OpMon\OpMonActive':
                        $beNagiosHosts->opmon_active = $entry->value;
                        break;
                    case 'OpMon\OpmonCITemplate':
                        $beNagiosHosts->template_name = $entry->value;
                        break;
                    case 'OpMon\OpMonCIIcon':
                        $beNagiosHosts->icon_image = $entry->value;
                        break;
                    case 'OpMon\OpMonCIActionsPage':
                        $beNagiosHosts->action_url = $entry->value;
                        break;
                    case 'OpMon\OpMonCatalogName':
                        $beNagiosHosts->scat_name = $entry->value;
                        break;
                    case 'Basic\NAME':
                        $beNagiosHosts->basic_name = $entry->value;
                        break;
                    case 'OpMon\OpMonCatalogID':
                        $beNagiosHosts->scat_id = $entry->value;
                        break;
                    case 'OpMon\OpMonCatalog':
                        $beNagiosHosts->catalog_active = $entry->value;
                        break;
                    case 'DN':
                        $beNagiosHosts->DN = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPComunity':
                        $beNagiosHosts->snmp_community = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPPort':
                        $beNagiosHosts->snmp_port = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPVersion':
                        $beNagiosHosts->snmp_version = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPCommunityID':
                        $beNagiosHosts->snmp_community_id = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPUser':
                        $beNagiosHosts->snmp_security_username = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPPass':
                        $beNagiosHosts->snmp_authentication_passphrase = $entry->value;
                        break;
                }
			}
			/*Alterar caracteres especiais do nome do IC para _*/
			$beNagiosHosts->host_name = preg_replace('/[^A-Za-z0-9\-\/._]/', '_', $beNagiosHosts->host_name);
			$beNagiosHosts->scat_name = preg_replace('/[^A-Za-z0-9\-\/._]/', '_', $beNagiosHosts->scat_name);

			/*IMPORTANTE TEMPLATE DESATIVADO
			//verifica se existe o template, senão existe coloca ID default 1
			$beNagiosHostTemplates = BpNagiosHostTemplates::consultaPorNome($beNagiosHosts);
			//var_dump($beNagiosHostTemplates);
			$beNagiosHosts->use_template_id = null;
			if( $beNagiosHostTemplates->host_template_id > 0 ){
				$beNagiosHosts->use_template_id = $beNagiosHostTemplates->host_template_id;

			}else{
				$beNagiosHosts->use_template_id = 1;
				if($type != "SCAT"){
					Bplog::save("priax id:$beNagiosHosts->priax_id $beNagiosHosts->DN - SEM TEMPLATE DEFINIDO, template_id = 1", 1);
				}
			}
			TEMPLATE FIM*/
            $beNagiosHosts->use_template_id = IC_TEMPLATE_ID;

			$arrIcs[] = $beNagiosHosts;
		}
		//var_dump($arrIcs); //exit;
		//return $arrIcs = array();
		return $arrIcs;
	}

    //transforma objetos de node em array de ICs
    private static function normalizaArrayNodesToArrayICs($lstIcPriax, $type="IC")
    {
        //var_dump($lstIcPriax); exit;
        $arrIcs = array();
        foreach ($lstIcPriax as $node) {

            $beNagiosHosts = new BeNagiosHosts();

            $beNagiosHosts->priax_id = $node->Node[0]->id;


            foreach ( $node->Node[0]->attributes->entry as $entry)
            {
                //var_dump($entry); exit;

                switch ($entry->key) {
                    case 'OpMon\OpMonCIName':
                        $beNagiosHosts->host_name = $entry->value;
                        break;
                    case 'OpMon\OpMonDescription':
                        $beNagiosHosts->alias = $entry->value;
                        break;
                    case 'OpMon\OpMonMonitoringAddress':
                        $beNagiosHosts->address = $entry->value;
                        break;
                    case 'OpMon\OpMonID':
                        $beNagiosHosts->host_id = $entry->value;
                        break;
                    case 'OpMon\OpMonActive':
                        $beNagiosHosts->opmon_active = $entry->value;
                        break;
                    case 'OpMon\OpmonCITemplate':
                        $beNagiosHosts->template_name = $entry->value;
                        break;
                    case 'OpMon\OpMonCIIcon':
                        $beNagiosHosts->icon_image = $entry->value;
                        break;
                    case 'OpMon\OpMonCIActionsPage':
                        $beNagiosHosts->action_url = $entry->value;
                        break;
                    case 'OpMon\OpMonCatalogName':
                        $beNagiosHosts->scat_name = $entry->value;
                        break;
                    case 'Basic\NAME':
                        $beNagiosHosts->basic_name = $entry->value;
                        break;
                    case 'OpMon\OpMonCatalogID':
                        $beNagiosHosts->scat_id = $entry->value;
                        break;
                    case 'OpMon\OpMonCatalog':
                        $beNagiosHosts->catalog_active = $entry->value;
                        break;
                    case 'DN':
                        $beNagiosHosts->DN = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPComunity':
                        $beNagiosHosts->snmp_community = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPPort':
                        $beNagiosHosts->snmp_port = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPVersion':
                        $beNagiosHosts->snmp_version = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPCommunityID':
                        $beNagiosHosts->snmp_community_id = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPUser':
                        $beNagiosHosts->snmp_security_username = $entry->value;
                        break;
                    case 'OpMonAgentConfiguration\SNMPPass':
                        $beNagiosHosts->snmp_authentication_passphrase = $entry->value;
                        break;
                }
            }
            /*Alterar caracteres especiais do nome do IC para _*/
            $beNagiosHosts->host_name = preg_replace('/[^A-Za-z0-9\-\/._]/', '_', $beNagiosHosts->host_name);
            $beNagiosHosts->scat_name = preg_replace('/[^A-Za-z0-9\-\/._]/', '_', $beNagiosHosts->scat_name);

            /*IMPORTANTE TEMPLATE DESATIVADO
            //verifica se existe o template, senão existe coloca ID default 1
            $beNagiosHostTemplates = BpNagiosHostTemplates::consultaPorNome($beNagiosHosts);
            //var_dump($beNagiosHostTemplates);
            $beNagiosHosts->use_template_id = null;
            if( $beNagiosHostTemplates->host_template_id > 0 ){
                $beNagiosHosts->use_template_id = $beNagiosHostTemplates->host_template_id;

            }else{
                $beNagiosHosts->use_template_id = 1;
                if($type != "SCAT"){
                    Bplog::save("priax id:$beNagiosHosts->priax_id $beNagiosHosts->DN - SEM TEMPLATE DEFINIDO, template_id = 1", 1);
                }
            }
            TEMPLATE FIM*/
            $beNagiosHosts->use_template_id = IC_TEMPLATE_ID;

            $arrIcs[] = $beNagiosHosts;
        }
        //var_dump($arrIcs); //exit;
        //return $arrIcs = array();
        return $arrIcs;
    }

	public function setOpMonIDinPriaxNode($opmonId, $node_id)
    {

        if($opmonId == NULL || $node_id == NULL) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("Sem key ou node_id - BmPriax - setOpMonIDinPriaxNode: opmonId: $opmonId, PriaxID: $node_id", 2);
            return $beBase;
        }

		try {
			//DISABLE WSDL CACHE.
			ini_set("soap.wsdl_cache_enabled", "0");
			$client = new SoapClient(PRIAXURL);
			$param = array(
				'arg0'=>array(
					'queryNode'=>array('attributes'=>'', 'id'=>$node_id),
					'resultNode'=>array('attributes'=>array(
							'entry'=>array(
								'key'=>'OpMon\OpMonID',
								'value'=> $opmonId
							)
						)
					)
				)
			);

			$result = $client->setNodeAs($param);

		}catch (exception $ex) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("BmPriax - setOpMonIDinPriaxNode: ".$ex->getMessage(), 2);
            return $beBase;
        }
	}

	public function setOpMonIDinPriaxCatalog($opmonId, $node_id)
    {

        if($opmonId == NULL || $node_id == NULL) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("Sem key ou node_id - BmPriax - setOpMonIDinPriaxCatalog: opmonId: $opmonId, PriaxID: $node_id", 2);
            return $beBase;
        }

		try {
			//DISABLE WSDL CACHE.
			ini_set("soap.wsdl_cache_enabled", "0");
			$client = new SoapClient(PRIAXURL);
			$param = array(
				'arg0'=>array(
					'queryNode'=>array('attributes'=>'', 'id'=>$node_id),
					'resultNode'=>array('attributes'=>array(
							'entry'=>array(
								'key'=>'OpMon\OpMonCatalogID',
								'value'=> $opmonId
							)
						)
					)
				)
			);

			$client->setNodeAs($param);

		} catch (exception $ex) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("BmPriax - setOpMonIDinPriaxCatalog: ".$ex->getMessage(), 2);
            return $beBase;
        }
	}

/*setar qualquer parametro */
	public function setParameterInPriax($node_id, $key, $value)
    {
		Bplog::save("Setando parametro no priax - node:".$node_id." key:".$key." value:".$value, 3);

		if($key == NULL || $node_id == NULL) {
			$beBase = new BeBase();
			$beBase->ErrStatus = 1;
            Bplog::save("Sem key ou node_id - BmPriax - setParameterInPriax: key: $key, PriaxID: $node_id", 2);
			return $beBase;
		}

		try {
			//DISABLE WSDL CACHE.
			ini_set("soap.wsdl_cache_enabled", "0");

			$client = new SoapClient(PRIAXURL);
			$param = array(
				'arg0'=>array(
					'queryNode'=>array('attributes'=>'', 'id'=>$node_id),
					'resultNode'=>array('attributes'=>array(
							'entry'=>array(
								'key'=>$key,
								'value'=> $value
							)
						)
					)
				)
			);

		    $client->setNodeAs($param);

		} catch (exception $ex) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("BmPriax - setParameterInPriax: ".$ex->getMessage(), 2);
            return $beBase;
        }
	}

    /*Funções para AICs*/

    public function getAICsPriaxArray($ic)
    {
        //var_dump($beNagiosHost);exit;
        $aics = BmPriax::getFormattedKPIs($ic);
        if (count($aics) <= 0) return array();
        return BmPriax::normalizaAICs($aics);
    }

    public function getAicByKpiId($kpi_id)
    {
        //var_dump($beNagiosHost);exit;
        $lstAIcPriax = BmPriax::getFormattedKPIsByKpiId($kpi_id);
        if (count($lstAIcPriax) <= 0) return array();
        $AIC =  BmPriax::normalizaAICs($lstAIcPriax);
        return $AIC[0];
    }

	private function getFormattedKPIs($beNagiosHost)
    {
		try {
            Bplog::save("Buscando KPIs no Priax sobre o BEING :".$beNagiosHost->priax_id);
	    	ini_set("soap.wsdl_cache_enabled", "0");
	    	$options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);
			$client = new SoapClient(PRIAXURL, $options);
			$param = array(
                'Node'=>array(
                    'attributes'=>array('entry'=>array('0'=>array('key'=>'OpMon\ACTIVE','value'=>'Yes'),)),
                    'id'=>$beNagiosHost->priax_id
                ),
                'ReturnAttribute'=>array(
                    'OpMon\OpMonKpiID',
                    'OpMon\CheckInterval',
                    'OpMon\ServiceName',
                    'OpMon\AffectAvailability',
                    'OpMon\RetryCheckInterval',
                    'OpMon\OpMonCommand',
                    'OpMon\ACTIVE',
                    'OpMon\MaximumChecksAttemps',
                    'OpMon\OpMonTemplate'
                )
			);

			$list = $client->getFormattedKpisByNode($param);

			//var_dump($list);exit;
			$arrayKpis = array();
			foreach ($list->KpiNode as $key => $value) {
				$arrayKpisInterno = array();
				foreach ($value->attributes->entry as $k => $v) {
					$arrayKpisInterno[$v->key] = $v->value;
				}
				$arrayKpisInterno['host_id'] = $beNagiosHost->host_id;
				$arrayKpisInterno['host_name'] = $beNagiosHost->host_name;
				$arrayKpisInterno['priax_id'] = $value->id;
				$arrayKpis[] = $arrayKpisInterno;
			}

			//var_dump($arrayKpis); exit;
			return $arrayKpis;

		} catch (exception $ex) {
            Bplog::save("BmPriax - getFormattedKPIs: ".$ex->getMessage(), 2);
			exit;
		}

	}

    private function getFormattedKPIsByKpiId($id)
    {
        try {
            Bplog::save("Buscando informacoes no Priax sobre o KPITYPE:".$id);
            ini_set("soap.wsdl_cache_enabled", "0");
            $options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);
            $client = new SoapClient(PRIAXURL, $options);
            $param = array(
                'queryNodeObject'=>array(
                    'queryNode'=>array('attributes'=>'', 'id'=>$id),
                    'resultNode'=>array(
                        'attributes'=>array(
                            'entry'=>array(
                                '0'=>array('key'=>'OpMon\OpMonKpiID','value' => ''),
                                '1'=>array('key'=>'OpMon\CheckInterval','value' => ''),
                                '2'=>array('key'=>'OpMon\ServiceName','value' => ''),
                                '3'=>array('key'=>'OpMon\AffectAvailability','value' => ''),
                                '4'=>array('key'=>'OpMon\RetryCheckInterval','value' => ''),
                                '5'=>array('key'=>'OpMon\OpMonCommand','value' => ''),
                                '6'=>array('key'=>'OpMon\ACTIVE','value' => ''),
                                '7'=>array('key'=>'OpMon\MaximumChecksAttemps','value' => ''),
                                '8'=>array('key'=>'OpMon\OpMonTemplate','value' => ''),
                            )
                        )
                    )
                )
            );

            $AIC = $client->getFormattedNodes($param);
            //var_dump($AIC); exit;
            $arrayKpis = array();
            foreach ($AIC->Node as $key => $value) {
                $arrayKpisInterno = array();
                foreach ($value->attributes->entry as $k => $v) {
                    $arrayKpisInterno[$v->key] = $v->value;
                }
                $arrayKpisInterno['host_id'] = null;
                $arrayKpisInterno['host_name'] = null;
                $arrayKpisInterno['priax_id'] = $value->id;
                $arrayKpis[] = $arrayKpisInterno;
            }

            //var_dump($arrayKpis); exit;
            return $arrayKpis;


        } catch (exception $ex) {
            Bplog::save("BmPriax - getFormattedKPIsByKpiId: ".$ex->getMessage(), 2);
            exit;
        }

    }

	private static function normalizaAICs($lstAIcPriax=0)
	{
		$arrAIcs = array();
		foreach ($lstAIcPriax as $AIcPriax) {
			$beNagiosServices = new BeNagiosServices();
            $beNagiosServices->service_id = $AIcPriax['OpMon\OpMonKpiID'];
            $beNagiosServices->priax_id = $AIcPriax['priax_id'];
            $beNagiosServices->opmon_active = $AIcPriax['OpMon\ACTIVE'];
            $beNagiosServices->service_description = $AIcPriax['OpMon\ServiceName'];
            $beNagiosServices->max_check_attempts = $AIcPriax['OpMon\MaximumChecksAttemps'];
            $beNagiosServices->command_parameter = $AIcPriax['OpMon\OpMonCommand'];
            $beNagiosServices->normal_check_interval = $AIcPriax['OpMon\CheckInterval'];
            $beNagiosServices->retry_check_interval = $AIcPriax['OpMon\RetryCheckInterval'];
            $beNagiosServices->host_id = $AIcPriax['host_id'];
            $beNagiosServices->host_name = $AIcPriax['host_name'];
            $beNagiosServices->template_name = $AIcPriax['OpMon\OpMonTemplate'];
            $beNagiosServices->affect_availability = $AIcPriax['OpMon\AffectAvailability'];

			/*Alterar caracter invalido em _*/
			$beNagiosServices->service_description = preg_replace('/[^A-Za-z0-9\-\/._]/', '_', $beNagiosServices->service_description);

			/*TEMPLATE será definido sempre 1
			//verifica se existe o template, senão existe coloca ID default 1
			$beNagiosServiceTemplates = BpNagiosServiceTemplates::consultaPorNome($beNagiosServices);
			//var_dump($beNagiosServiceTemplates);

			$beNagiosServices->use_template_id = null;

			if( $beNagiosServiceTemplates->service_template_id > 0 ){
				$beNagiosServices->use_template_id = $beNagiosServiceTemplates->service_template_id;
			}else{
				$beNagiosServices->use_template_id = 1;
				Bplog::save("AIC $beNagiosServices->service_description NAO EXISTE TEMPLATE $beNagiosServices->template_name NO OPMOM SERA DEFINIDO ID 1", 1);
			}
			/*TEMPLATE FIM*/
            $beNagiosServices->use_template_id = AIC_TEMPLATE_ID;

            /** Troca $ por $$, por causa de BUG no OpMon*/
            $beNagiosServices->command_parameter = str_replace('$', '$$' , $beNagiosServices->command_parameter);
            /** fim troca $ por $$, por causa de BUG no OpMon */

			/*affect_availability"*/
			if( $beNagiosServices->affect_availability == 'Yes'){
				//Quando afetar será setado 0 no process_perf_data
				$beNagiosServices->process_perf_data = '0';
			}else{
				$beNagiosServices->process_perf_data = '1';
			}

			$arrAIcs[] = $beNagiosServices;
		}

		//var_dump($arrAIcs); exit;
		return $arrAIcs;
	}

	public function setOpmonIdInPriaxKpi($beNagiosServices)
    {
		try{
            if(DEBUG == 2) Bplog::save(" ADD ID $beNagiosServices->service_id no Priax $beNagiosServices->service_description -> Priax ID $beNagiosServices->priax_id");
			ini_set("soap.wsdl_cache_enabled", "0");
	    	$client = new SoapClient(PRIAXURL);
	    	$param = array(
	    		'arg0'=>array('queryNode'=>array(
				'attributes'=>'',
				'id'=>$beNagiosServices->priax_id),
					'resultNode'=>array('attributes'=>array('entry'=>array(
							'key'=>'OpMon\OpMonKpiID',
							'value'=> $beNagiosServices->service_id
							)
						)
					)
				)
    		);
			$client->setNodeAs($param);
		} catch (exception $ex) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("BmPriax - setOpmonIdInPriaxKpi: ".$ex->getMessage(), 2);
            return $beBase;
        }
	}

    public function getLastChangeId()
    {
        try {
            //DISABLE WSDL CACHE.
            ini_set("soap.wsdl_cache_enabled", "0");

            //Retorna array
            $options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);
            $client = new SoapClient(PRIAXURL, $options);
            $out = $client->getLastChangeId();
            return $out->return;
        }
        catch (exception $ex) {
            Bplog::save("BmPriax - getFormattedNodesICs: ".$ex->getMessage(), 2);
            exit;
        }
    }

    public function getFirstChangeId()
    {
        try {
            //DISABLE WSDL CACHE.
            ini_set("soap.wsdl_cache_enabled", "0");

            //Retorna array
            $options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);
            $client = new SoapClient(PRIAXURL, $options);
            $out = $client->getFirstChangeId();
            return $out->return;
        }
        catch (exception $ex) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("BmPriax - getFormattedNodesICs: ".$ex->getMessage(), 2);
            return $beBase;
        }
    }

    public function getChangeLogById($id)
    {
        try {
            //DISABLE WSDL CACHE.
            ini_set("soap.wsdl_cache_enabled", "0");
            ini_set('default_socket_timeout', 600);

            //Retorna array
            $options = array('features' => SOAP_SINGLE_ELEMENT_ARRAYS);
            $client = new SoapClient(PRIAXURL, $options);
            $param = array('arg0'=>$id );
            $arrObjectsNodes = $client->getChangeLogById($param);
            return $arrObjectsNodes;
        }
        catch (exception $ex) {
            $beBase = new BeBase();
            $beBase->ErrStatus = 1;
            Bplog::save("BmPriax - getChangeLogById: ".$ex->getMessage(), 2);
            return $beBase;
        }
    }

}
