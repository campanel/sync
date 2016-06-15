<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmSnmpCommunities.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeSnmpCommunities.php");

class BpSnmpCommunities {

	public function Insere($beSnmpCommunities) {
		return BmSnmpCommunities::Insere($beSnmpCommunities);
	}

	public function getByName($name) {
		return BmSnmpCommunities::Lista("WHERE community = '" . $name."'", $order=null, $limit=null);
	}
	
	public function Delete($beSnmpCommunities) {
		return BmSnmpCommunities::Delete($beSnmpCommunities);
	}

	public function getID($ic) {
		//$ic->snmp_community = "naopublic222";
		//$ic->snmp_version = "2c";

		$foo = BpSnmpCommunities::getByName($ic->snmp_community);
		//$foo = BpSnmpCommunities::getByName("blo");
		if(empty($foo)){
			if(empty($ic->snmp_version)) $ic->snmp_version = "2c";
			$beSnmpCommunities = new BeSnmpCommunities;
			$beSnmpCommunities->community = $ic->snmp_community;
			$beSnmpCommunities->snmp_version = $ic->snmp_version;
			
			$new_snmp = BpSnmpCommunities::Insere($beSnmpCommunities);
			//var_dump($new_snmp); exit;
			$id = $new_snmp->community_id;
		}else{

			$id = $foo[0]->community_id;
		}

		//var_dump($id);exit;
		return $id;
	}


}

?>