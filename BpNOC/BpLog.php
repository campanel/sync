<?php
class BpLog {
		
	public static function save($msg, $level=3) {
		$debug = 2;

		if($debug == 0 ) return true;

		$arr = array(1,2);

		if(in_array($debug , $arr)){

			if(!is_string($msg)) $msg = json_encode($msg);
			
			//$contents = date('Y-m-d H:i:s').";".$level.";".$msg." \n";

            //if($level != 3 ){
                BpSyncDB::insere_log(date('Y-m-d H:i:s') , $level, $msg );
            //}

			if($debug == 2){
				switch ($level) {
					case 0:
						$level_msg = "\033[32mOK\033[0m";
						break;
					case 1:
                        $level_msg = "\033[33mWARNING\033[0m";
						break;
					case 2:
                        $level_msg = "\033[31mERROR\033[0m";
						break;
                    case 4:
                        $level_msg = "\033[35mQUERY\033[0m";
                        break;
					default:
                        $level_msg = "\033[37mINFO\033[0m";
						break;
				}

                //var_dump('DEBUG',DEBUG);
                //var_dump('$level',$level);
                if(DEBUG == 0 and $level == 3 ){

                }else{
                    echo "[".date('Y-m-d H:i:s')."] ".$level_msg." ".$msg." \n";
                }

			}
		}		
	}

    public static function save_old($msg, $level=3) {
        $debug = 2;
        $filename = "/var/log/priax-sync.log";

        if($debug == 0 ) return true;

        $arr = array(1,2);

        if(in_array($debug , $arr)){

            if(!is_string($msg)) $msg = json_encode($msg);

            $contents = date('Y-m-d H:i:s').";".$level.";".$msg." \n";

            file_put_contents($filename, $contents, FILE_APPEND);
            chmod($filename, 0777);

            if($debug == 2){
                switch ($level) {
                    case 0:
                        $level = "\033[32mOK\033[0m";
                        break;
                    case 1:
                        $level = "\033[33mWARNING\033[0m";
                        break;
                    case 2:
                        $level = "\033[31mERROR\033[0m";
                        break;
                    case 4:
                        $level = "\033[35mQUERY\033[0m";
                        break;
                    default:
                        $level = "\033[37mINFO\033[0m";
                        break;
                }

                echo "[".date('Y-m-d H:i:s')."] ".$level." ".$msg." \n";
            }
        }
    }
}
/*
30 black foreground
31 red foreground
32 green foreground
33 brown foreground
34 blue foreground
35 magenta (purple) foreground
36 cyan (light blue) foreground
37 gray foreground
*/
?>