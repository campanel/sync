<?php
date_default_timezone_set('America/Sao_Paulo');
define("DEBUG",2); /** 0 | 1 |2 | 3 | 4 */
define("VERSION",'1.1');
define("CATALOG_PRINCIPAL",'Business');
define("IC_TEMPLATE_ID",1);
define("AIC_TEMPLATE_ID",1);

$ic_parameters_to_check = array("host_name","alias","address","use_template_id","icon_image","action_url");
$aic_parameters_to_check =  array("service_description","max_check_attempts","normal_check_interval","retry_check_interval","use_template_id","command_parameter","process_perf_data");
