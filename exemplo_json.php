<?php                                                                                                                                                                                                                                            
$out = array('custom' => 'custom value '.date('H:i:s'),'host_name' => 'host teste', 'service_description' => 'service teste', 'display_status' => '0');                           
                                                                                                                                                                                                                                                 
$metrics =  array(                                                                                                                                                                                                                               
    '1001' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'cpu_utilization','max' => '150','min' => '-100', 'unit' => '%'),
    '1002' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'idle','max' => '100','min' => '0','unit' => '%'),
    '1003' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'iowait','max' => '100','min' => '0','unit' => '%'),
    '1004' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'system','max' => '100','min' => '0','unit' => '%'),
    '1005' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'total','max' => '100','min' => '0', 'unit' => '%'),
                                                                                                                                                                                                                                                 
);                                                                                                                                                                                                                                               
    
//parameters example    
if ($_REQUEST['mode'] == '2') {	    
	$metrics =  array(                                                                                                                                                                                                                               
    	'1001' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'cpu_utilization','max' => '150','min' => '0', 'unit' => '%'),                                                                                            
   	'1002' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'idle','max' => '100','min' => '0','unit' => '%'),                                                                                                          
    	'1003' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'iowait','max' => '100','min' => '0','unit' => '%'),                                                                                                        
    	'1004' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'system','max' => '100','min' => '0','unit' => '%'),                                                                                                        
    	'1005' => array('value' => rand(0,100),'warning' => '70','critical' => '80','metric' => 'total','max' => '100','min' => '0', 'unit' => '%'),                                                                                                                                                                                                                                                                                                                                                 
	);
}                                                                                                                                                                                                                                               
                                                                                                                                                                                                                                                     
for ($i=0;$i<=10;$i++){                                                                                                                                                                                                                          
    $data[] =                                                                                                                                                                                                                            
        array(                                                                                                                                                                                                                                   
            'cpu_utilization' => rand(1,100),                                                                                                                                                                                                    
            'idle' => rand(0,100),
            'iowait' => rand(0,100),
            'system' => rand(0,100),
            'total' => rand(0,100),
            'label' => 'label '.$i,                                                                                                                                                                                                              
        );                                                                                                                                                                                                                                       
}                                                                                                                                                                                                                                                
                                                                                                                                                                                                                                                 
$out['metrics'] = $metrics;                                                                                                                                                                                                              
$out['data'] = $data;                                                                                                                                                                                                            

print json_encode($out);