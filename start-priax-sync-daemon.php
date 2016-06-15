<?php
/**Daemon sync priax**/

for($i=1; $i < 100 ; $i++){
    if($i <= 10){
        var_dump("a cada tempo");
        //shell_exec('/usr/bin/php -q '.dirname(__FILE__).'/start-priax-sync-incrementais-only-ics-aics.php');
        shell_exec('/usr/bin/php -q '.dirname(__FILE__).'/teste.php');

    }else{
        var_dump("a cada 10x");
        //shell_exec('/usr/bin/php -q '.dirname(__FILE__).'/start-priax-sync-incrementais.php');
        shell_exec('/usr/bin/php -q '.dirname(__FILE__).'/teste.php');

        $i = 1;
    }
    sleep(10);
    flush();
}