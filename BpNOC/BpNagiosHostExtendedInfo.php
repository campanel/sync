<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmNagiosHostExtendedInfo.php");

class BpNagiosHostExtendedInfo {

    public function getByHostId($host_id) {
        /**Validação**/
        if( $host_id == '' || !$host_id ){
            Bplog::save("FALHOU getByHostId SEM host_id", 2);
            return null;
        }
        $foo = BmNagiosHostExtendedInfo::query("WHERE host_id = " . $host_id, NULL, NULL);
        return $foo[0];
    }

    public function updateByBeHost($beNagiosHosts) {
        /**Validação**/
        if( $beNagiosHosts->host_id == '' || !$beNagiosHosts->host_id ){
            Bplog::save("FALHOU BpNagiosHostExtendedInfo SEM host_id", 2);
            $beNagiosHosts->ErrStatus = 1;
            return $beNagiosHosts;
        }

        $ext_info = new BeNagiosHostExtendedInfo();
        $ext_info->host_id =  $beNagiosHosts->host_id;
        $ext_info->icon_image = $beNagiosHosts->icon_image;
        $ext_info->action_url =  $beNagiosHosts->action_url;
        $info =  BmNagiosHostExtendedInfo::update($ext_info);
        if ( $info->ErrStatus != 0) {
            Bplog::save("Erro update HostExtendedInfo", 2);
        }
        return $info;
    }
}

?>