<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmScatContacts.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeScatContacts.php");

class BpScatContacts {
	public function create($beScats) {
		return BmScatContacts::create($beScats);
	}

	public function delete_by_scat_id($scat_id) {
		return BmScatContacts::delete_by_scat_id($scat_id);
	}
}
?>