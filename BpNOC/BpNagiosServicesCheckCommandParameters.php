<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmNagiosServicesCheckCommandParameters.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeNagiosServicesCheckCommandParameters.php");

class BpNagiosServicesCheckCommandParameters {
	public function insere_by_service($aic) {
        /**Validação**/
        if($aic->service_id == '' || !$aic->service_id){
            Bplog::save("FALHOU Insere CheckCommandParameters SEM service_id, priax_id:".$aic->priax_id, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }
        $command =  BmNagiosServicesCheckCommandParameters::insere($aic->service_id, $aic->command_parameter);
        if ($command->ErrStatus != 0) {
            Bplog::save("Erro - BeNagiosServicesCheckCommandParameters Insere AIC Command AIC:".$aic->service_id, 2);
        }
        return $command;
	}

    public function delete($aic) {
        /**Validação**/
        if($aic->service_id == '' || !$aic->service_id){
            Bplog::save("FALHOU DELETE  CheckCommandParameters SEM service_id, priax_id:".$aic->priax_id, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }

        $foo = BmNagiosServicesCheckCommandParameters::deleteByService_id($aic->service_id);
        if($foo->ErrStatus != 0){
            Bplog::save(" Erro: DeleteCommandParameters service_id:".$aic->service_id,2);
        }
        return $foo;
    }

    public function update($aic) {
        /**Validação**/
        if($aic->service_id == '' || !$aic->service_id){
            Bplog::save("FALHOU UPDATE CheckCommandParameters SEM service_id, priax_id:".$aic->priax_id, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }

        $beBase1 = BpNagiosServicesCheckCommandParameters::delete($aic);
        if ( $beBase1->ErrStatus != 0 ) {
            Bplog::save("FALHOU Update CheckCommandParameters priax_id:".$aic->priax_id, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }

        $beBase2 = BpNagiosServicesCheckCommandParameters::insere_by_service($aic);
        if ( $beBase2->ErrStatus != 0) {
            Bplog::save("FALHOU Update CheckCommandParameters priax_id:".$aic->priax_id, 2);
            $aic->ErrStatus = 1;
            return $aic;
        }

        return $aic;
    }

    public function getByServiceId($id) {
        $foo = BmNagiosServicesCheckCommandParameters::query("WHERE service_id = " . $id, NULL, "LIMIT 1");
        //var_dump($oi); exit;
        return $foo[0];
    }


}

?>