<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmScats.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeScatItens.php");
require_once("BpScats.php");
require_once("BpScatItens.php");
require_once("BpSync.php");
require_once("BpSyncCatalog.php");

class BpOpMonSyncCatalogs{
	
	public static function SyncCatalogs() {

        Bplog::save("--- Inicio Sync Catalogos ---");




	/**
	O sync do catalogo nao serve para este cliente

*/Bplog::save("O sync do catalogo não satisfaz as necessidades deste cliente NETCENTRICS");
exit;



        Bplog::save("GET CATALOGOS PRIAX");
        $scats_priax = BpSyncCatalog::getPriaxScats();
        //var_dump('priaxScats'); var_dump($scats_priax);

        if(DEBUG >= 1) Bplog::save("GET CATALOGOS OPMON");
        $scats_opmon = BpSyncCatalog::getOpMonScats();
        //var_dump('opmonScats'); var_dump($scats_opmon);
        //exit;

        /** Excluir catalogos sem nome da lista do priax e add a lista de catalogo com erros*/
        list( $scats_priax, $scats_erros ) = BpSyncCatalog::remove_scat_unnamed($scats_priax);

        /** Cria arrays de CRUD dos Catalogos  */
        list( $scats_create, $scats_delete, $scats_update_info, $scats_update_itens ) = BpSyncCatalog::crud($scats_priax, $scats_opmon);

        //var_dump('$scats_create'); var_dump($scats_create);
        //var_dump('$scats_delete'); var_dump($scats_delete);
        //var_dump('$scats_edit_info'); var_dump($scats_update_info);
        //var_dump('$scats_edit_itens'); var_dump($scats_update_itens);
        //exit;

        /** CRUD */
        $deleteScats = BpScats::delete_scats($scats_delete);
        $insereScats = BpScats::create_scats($scats_create);
        $updateInfoScats = BpScats::update_scats_info($scats_update_info);
        $updateItensScats = BpScatItens::update_scats_itens($scats_update_itens);

        Bplog::save("CATALOGOS EXCLUIR: ".count($scats_delete), 1);
        BpOpMonSyncCatalogs::msgArray($deleteScats, 'scat_name');
        Bplog::save("CATALOGOS INCLUIR: ".count($scats_create), 1);
        BpOpMonSyncCatalogs::msgArray($insereScats, 'scat_name');
        Bplog::save("CATALOGOS EDITAR INFORMAÇOES: ".count($scats_update_info), 1);
        BpOpMonSyncCatalogs::msgArray($updateInfoScats, 'scat_name');
        Bplog::save("CATALOGOS EDITAR LISTA DE ITENS: ".count($scats_update_itens), 1);
        BpOpMonSyncCatalogs::msgArray($updateItensScats, 'scat_name');

        //Log erro
        //var_dump($arr_not_ok);
        if( $scats_erros ){
            Bplog::save("NODOs PRIAX NAO PROCESSADOS:", 2);
            foreach ($scats_erros as $value) {
                Bplog::save("-> ".get_class($value)." PRIAX ID: $value->priax_id", 2);
            }
        }

        /*Se houve alterações exporta OpMon*/
        if(count($deleteScats) > 0 || count($insereScats) > 0 || count($updateInfoScats) > 0 || count($updateItensScats) > 0){
            Bplog::save("EFETUADO ALTERACOES -> EXPORT OPMON");
            $retExport = BpSync::exportConfigOpmon();
            Bplog::save("\n".$retExport);

        }else{
            Bplog::save("SEM ALTERAÇÕES NAO HAVERA EXPORT NO OPMON", 0);
        }
        Bplog::save("--- Fim Sync Catalogos ---");
        return true;
	}

	public function msgArray($arr, $def) {
		foreach ($arr as $key => $value) {
			Bplog::save(" ".$value->$def,0);
		}
	}
}

?>
