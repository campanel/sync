<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmScatItens.php");

class BpScatItens {
	public function create($arrItens) {
		$foo = array();
		foreach ($arrItens as $key => $iten) {
			//*get do host_id e service_id dos ICs que sao CATALOGOS
			if( $iten->catalog_active == 'Yes'){
				$aic = BpNagiosServices::get_service_by_host_name_and_service_description('Business', $iten->service_description);
				$iten->host_name = $aic->host_id;
				$iten->service_name = $aic->service_id;
			}
			//var_dump($iten);
			$foo[] = BmScatItens::create($iten);
		}
		return $foo;
	}

	public function delete_by_scat_id($scat_id) {
        if(!$scat_id){
            Bplog::save("Erro delete_by_scat_id sem scat_id: ", 2);
        }
		return BmScatItens::delete_by_scat_id($scat_id);
	}

    public function delete_by_array_itens($arr_itens) {
        foreach($arr_itens as $iten){
            BmScatItens::delete_by_id($iten->id);

        }
    }

    public function update_by_scat($beScats)
    {
        //var_dump('update_by_scat',$beScats); exit;
        Bplog::save("update_by_scat: ".$beScats->scat_name, 1);
        //BpScatItens::delete_by_scat_id($beScats->scat_id);
        $itens_antigos = BpScatItens::get_all_by_scat_id($beScats->scat_id);
        //var_dump($itens_antigos);exit;
        BpScatItens::create($beScats->scat_itens);
        BpScatItens::delete_by_array_itens($itens_antigos);

        return $beScats;
    }

    public function update_scats_itens($listScats)
    {
        $foo = array();
        foreach ($listScats as $key => $scat) {
            $foo[] = BpScatItens::update_by_scat($scat);
        }
        return $foo;
    }

    public function get_all_by_scat_id($scat_id) {
        return  BmScatItens::Lista("WHERE scat_id = " . $scat_id, "", "");
    }

}
?>