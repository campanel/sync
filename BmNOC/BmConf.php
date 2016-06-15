<?php
require_once("BmMySQL.php");
require_once("BmNagiosCommands.php");

class BmConf{

    public function insere_command_interop(){
        $interop_command  = BmNagiosCommands::getIdByName('check_interop');
        if($interop_command ){
            return $interop_command ;
        }
        //Insere comando
        $query = "insert into nagios_commands (network_id,command_name,command_line,command_desc,system,command_type)
		values (0,'check_interop','\$USER1\$/\$ARG1\$','',0,1)";
        $bmMySQL = new BmMySQL();
        $check_interop_id = $bmMySQL->query($query, BeNagiosCommands ,"opcfg");

        return $check_interop_id->ErrMensagem;
    }

	public function update_host_template( $check_interop_id ){
        if($check_interop_id < 1 || $check_interop_id == null ){
            Bplog::save("Erro update_host_template sem command_id: ", 2);
        }
        $query = "UPDATE nagios_host_templates SET
        use_template_id = 0 ,
        check_command = ".$check_interop_id." ,
        max_check_attempts = 2 ,
        check_interval = 5 ,
        passive_checks_enabled = '1' ,
        check_period = 1 ,
        obsess_over_host = NULL ,
        check_freshness = NULL ,
        freshness_threshold = NULL ,
        active_checks_enabled = '1' ,
        checks_enabled = '1' ,
        event_handler = NULL ,
        event_handler_enabled = '1' ,
        low_flap_threshold = NULL ,
        high_flap_threshold = NULL ,
        flap_detection_enabled = '1' ,
        process_perf_data = NULL ,
        retain_status_information = NULL ,
        retain_nonstatus_information = NULL ,
        notification_interval = 0 ,
        notification_period = 2 ,
        notifications_enabled = '1' ,
        notification_options_down = '1' ,
        notification_options_unreachable = '0' ,
        notification_options_recovery = '1' ,
        notification_options_flapping = '0' ,
        stalking_options_up = NULL ,
        stalking_options_down = NULL ,
        stalking_options_unreachable = NULL ,
        failure_prediction_enabled = NULL ,
        retry_interval = 1 ,
        notification_options_downtime = '0'
        where host_template_id = 1";

        $bmMySQL = new BmMySQL();
        $hostTpl = $bmMySQL->query($query, "BeNagiosServices", "opcfg");
		return $hostTpl;
	}

	public function insere_parameter_host_template(){
        $query = "insert into nagios_hosts_check_command_parameters ( host_id, host_template_id, parameter)
            values (NULL, 1, 'priax/ic/check_ic_status.pl \$HOSTNAME\$')";
        $bmMySQL = new BmMySQL();
		return $bmMySQL->query($query, "BeNagiosServices", "opcfg");
		//return $foo[0];
	}

    public function update_service_template( $check_interop_id ){
        if($check_interop_id < 1 || $check_interop_id == null ){
            Bplog::save("Erro update_service_template sem command_id: ", 2);
        }
        $query = "UPDATE nagios_service_templates SET
				is_volatile = NULL,
               check_command = ".$check_interop_id.",
          max_check_attempts = 10,
       normal_check_interval = 5,
        retry_check_interval = 1,
       active_checks_enabled = '1',
      passive_checks_enabled = '1',
                check_period = 1,
           parallelize_check = '1',
         obsess_over_service = '1',
             check_freshness = NULL,
         freshness_threshold = NULL,
               event_handler = NULL,
       event_handler_enabled = '1',
          low_flap_threshold = NULL,
         high_flap_threshold = NULL,
      flap_detection_enabled = '1',
           process_perf_data = NULL,
   retain_status_information = NULL,
retain_nonstatus_information = NULL,
       notification_interval = 0,
         notification_period = 2,
notification_options_warning = '0',
notification_options_unknown = '0',
notification_options_critical = '1',
notification_options_recovery = '1',
notification_options_flapping = '0',
       notifications_enabled = '1',
         stalking_options_ok = NULL,
    stalking_options_warning = NULL,
    stalking_options_unknown = NULL,
   stalking_options_critical = NULL,
  failure_prediction_enabled = NULL,
notification_options_downtime = '0',
            baseline_enabled = NULL,
        baseline_seasonality = NULL,
        baseline_time_window = NULL,
             baseline_method = NULL,
           baseline_multiply = NULL
            where service_template_id = 1 ";
        $bmMySQL = new BmMySQL();
        $serviceTpl = $bmMySQL->query($query, 'BeNagiosHostTemplates' ,"opcfg");
        return $serviceTpl;
    }

}
