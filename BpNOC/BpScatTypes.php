<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmScatTypes.php");

class BpScatTypes {	
	

	public function insere($beScats) {
		return BmScatTypes::insere($beScats);
	}
	
	public function update($beScats) {
		return BmScatTypes::update($beScats);
	}

	public function getAll() {
		return BmScatTypes::getAll();
	}

	public function getByName($name)  {
		return BmScatTypes::getByName($name);
	}

	public function delete_by_scat_id($id) {
		return BmScatTypes::delete_by_scat_id($id);
	}
}

?>