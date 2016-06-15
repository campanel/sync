<?php
require_once("BpSync.php");

class BpOpMonSync{
	
	public static function SyncAll() {
        Bplog::save("Start Sync Full",0);
        Bplog::save("--- Inicio Sync ICs ---");
        Bplog::save("GET ICs PRIAX");
        $bmPriax = new BmPriax;
        $ics_priax = $bmPriax->getPriaxICs();

        if(DEBUG >= 1) Bplog::save("GET ICs OPMON");
        $ics_opmon = BpNagiosHosts::getAll();

        /**Excluir ICs Priax sem nome da lista e add a lista de ICs com erros*/
        //list( $ics_priax, $lstErros ) = BpSync::remove_ics_unnamed_without_ip($ics_priax);

        /**CADASTRA SNMP NO OPMON E ADICIONA COMUNITY_ID AOS ICS DO PRIAX*/
        $ics_priax = BpSync::add_snmp_in_opmon_and_snmp_id_in_ics_priax($ics_priax);

        /** SE O IC EXISTIR NO OPMON E NÃO EXISTIR NO PRIAX ELE DEVE SER DELETADO,
         * cria lista de ICs a serem deletados do OpMom(seus AICs são deletados junto)*/
        $ics_delete = BpSync::diff_array_of_objects_by_parameter($ics_priax,$ics_opmon,'host_id', 'host_name');
        if( 0 == count($ics_delete) ) Bplog::save("SEM ICs para DELETAR");

        return BpSync::sync($ics_priax, $ics_opmon,$ics_delete, null, null);
	}
}

