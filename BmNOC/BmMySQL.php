<?php
require_once(dirname(dirname(__FILE__))."/BeNOC/BeBase.php");
include_once "/usr/local/opmon/etc/config.php";

class BmMySQL {
	
	public $database;       			// MySQL Database Name
	public $link;
	public $query;
	public $result;
	public $rows;
	public $total;
	
	//public function __construct($strHost = "localhost", $strUser = "root", $strPassword = "*interop2014*") {
	public function __construct($strHost = DBHOST, $strUser = DBUSER, $strPassword = DBPASS) {
		$this->host 	= $strHost;
		$this->password = $strPassword;
		$this->user 	= $strUser;
	}

	public function __destruct() {
		$conn = null;
	}

	
	public function Query($query, $class, $database) {

		try {
			$conn = new PDO("mysql:host=" . $this->host . "; dbname=" . $database, $this->user, $this->password);
			//var_dump($query);

            if(DEBUG >= 3) Bplog::save(json_encode($query),4);
		
			if(stristr($query, "SELECT") != FALSE) {
				$conn->exec("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");      // Sets encoding UTF-8

				$result = $conn->query($query);
				
				return $result->fetchALL(PDO::FETCH_CLASS, $class);      // Apply FETCH_CLASS with Sites class
			}
			
			if (stristr($query, "INSERT") != FALSE) {
				$conn->exec("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");      // Sets encoding UTF-8

				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				$stmt = $conn->prepare($query); 
				
				$stmt->execute(); 
				
				$beBase = new BeBase;
				$beBase->ErrMensagem = $conn->lastInsertId();
				$beBase->ErrStatus = 0;
				
				return $beBase;
			}
			
			if (stristr($query, "UPDATE") != FALSE)  {
				$conn->exec("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");      // Sets encoding UTF-8

				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				$stmt = $conn->prepare($query); 
				
				$stmt->execute(); 
				
				$beBase = new BeBase;
				$beBase->ErrStatus = 0;
				
				return $beBase; 
			}
			
			if( (stristr($query, "DELETE") != FALSE)) {
				$conn->exec("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");      // Sets encoding UTF-8

				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				$stmt = $conn->prepare($query); 
				
				$stmt->execute(); 
				
				$beBase = new BeBase;
				$beBase->ErrStatus = 0;
				
				return $beBase; 
			}
		}
		catch(PDOException $ex) {
			//var_dump($ex->getMessage());exit;

			$beBase = new BeBase;
			$beBase->ErrClasse = "BmMySQL";
			$beBase->ErrMetodo = "Query";
			$beBase->ErrTitulo = "ERRO";
			$beBase->ErrMensagem = $ex->getMessage();
			$beBase->ErrStatus = 1;
			
			Bplog::save($beBase->ErrTitulo.": Class: ".$beBase->ErrClasse." - Metodo: ".$beBase->ErrMetodo." - Mensagem: ".$beBase->ErrMensagem ."\n\n QUERY RECEBIDO: ".$query , 2);
			
			return $beBase;
		}

	}
}
?>
