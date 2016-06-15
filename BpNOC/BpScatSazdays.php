<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmScatSazdays.php");
require_once(dirname(dirname(__FILE__))."/BeNOC/BeScatSazdays.php");

class BpScatSazdays {	
	

	public function create($beScats) {

		for ($i=0; $i < 31; $i++) { 
			$foo = BmScatSazdays::create($beScats,$i);
		}
		return $foo;
		
	}

	public function Update($beScats) {
		return BmScatSazdays::Update($beScats);
	}

	public function delete_by_scat_id($id) {
		return BmScatSazdays::delete_by_scat_id($id);
	}
}

?>