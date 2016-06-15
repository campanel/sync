<?php

include_once("BeBase.php");

class BeNagiosServices extends BeBase {
	public $service_id = NULL;
	public $host_id = NULL;
	public $service_description = NULL;
	public $use_template_id = NULL;
	public $command_parameter = NULL;
	public $max_check_attempts = NULL;
	public $normal_check_interval = NULL;
	public $retry_check_interval = NULL;
	public $affect_availability = NULL;
	public $process_perf_data = NULL;
    public $is_catalog = NULL;

}
