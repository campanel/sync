<?php

include_once("BeBase.php");

class BeSnmpCommunities extends BeBase {
	public $community_id = NULL;
	public $snmp_version = NULL;
	public $community = NULL; //public
	public $authentication_protocol = NULL;
	public $authentication_passphrase = NULL;
	public $security_level = NULL;
	public $context_name = NULL;
	public $security_username = NULL;
	public $privacy_protocol = NULL;
	public $privacy_protocol_passphrase = NULL;
	public $destination_engine = NULL;
	public $active = NULL;
}

?>