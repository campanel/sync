<?php 
require_once(dirname(dirname(__FILE__))."/BeNOC/BeBase.php");

include_once "/usr/local/opmon/etc/config.php";
set_logged_user("opmonadmin");

class BmApiOpmon{
/*funcoes para ICs*/
	
	#Inserção de host API
	public function add_host($host)
	{	
		$manager = new ConfigManager();
		/*$host = array(
		    "host_name" => 'host',
		    "address" => 'localhost',
		    "alias" => 'alias',
		    "use_template_id" => 1,
		    "check_command" => 22,
		);*/

		$manager->cfg->add_host($host);

		$host_id = $manager->cfg->return_host_id_by_name($host['host_name']);
		return $host_id;
	}

    public function delete_host_by_id($host_id)
    {
        /**
         * deletando servico
         */
        $manager = new ConfigManager();
        $ret_del = $manager->cfg->delete_host($host_id);

        //var_dump($ret_del);

        $nome = $manager->cfg->return_host_name($host_id);
        //var_dump('nome');
        //var_dump($nome);
        $retorno = false;
        if( $nome == null ){ //se não retornar o nome do IC é porque foi apagado com sucesso
            $retorno = true;
        }
        return $retorno;
    }



    /*Services*/
    public function add_service($service)
    {
        /**
         * add service
         * $hash = array(
        "host_id" => $host_id,
        "service_description" => "Load",
        "max_check_attempts" => 5,
        "normal_check_interval" => 5,
        "retry_check_interval" => 1,
        "check_command" => $manager->cfg->return_command_id_by_name("check-load"),
        "check_period" => $manager->cfg->return_period_id_by_name("24x7"),
        "notification_period" => $manager->cfg->return_period_id_by_name("24x7")
        );
         */

        /*$service = array(
            'use_template_id' => 1,
            'host_id' => 365,
            'service_description' => 'esse_ae',
            'max_check_attempts' => 3,
            'normal_check_interval' => 5,
            'retry_check_interval' => 1,
            'check_command' => 22,
            'process_perf_data' => 1,
        );*/
        $manager = new ConfigManager();
        $manager->cfg->add_service($service);
    }

    public function delete_service_by_id($service_id)
    {
        $manager = new ConfigManager();
        $manager->cfg->delete_service($service_id);
    }

    public function license_hosts()
    {
        $lic =  get_opmon_license_properties();

        foreach($lic as $obj){
            if($obj->param  == "Hosts"){
                $hosts = $obj->content;
            }
        }
        return $hosts;
    }

    public function license_services()
    {
        $lic =  get_opmon_license_properties();
        foreach($lic as $obj){
            if($obj->param  == "Services"){
                $services = $obj->content;
            }
        }
        return  $services;
    }

    /**dev null*/
    public function view_all_methods()
    {
        $manager = new ConfigManager();
        $class_methods = get_class_methods($manager);
        var_dump($class_methods);
        $class_methods = get_class_methods($manager->cfg);
        var_dump($class_methods);
    }

}
?>
