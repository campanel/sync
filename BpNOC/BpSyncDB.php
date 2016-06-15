<?php
require_once(dirname(dirname(__FILE__))."/BmNOC/BmPriax.php");
require_once("BpOpMonSync.php");


class MyDB extends SQLite3
{
    function __construct()
    {
        $file = dirname(dirname(__FILE__)).'/priax_sync.db';
        $this->open($file);
    }
}

class BpSyncDB {
    /**INSERTs**/
    public static function create_tables()
    {
        $file = dirname(dirname(__FILE__)).'/priax_sync.db';
        //$db = new SQLite3($file);
        $db = new PDO('sqlite:'.$file);
        chmod ($file, 0777);
        Bplog::save("Create DB ids: ".$file, 1);
        $db->query('DROP TABLE hosts ');
        $result = $db->query('CREATE TABLE hosts (id integer primary key autoincrement, host_id int UNIQUE, priax_id int UNIQUE, host_name varchar(128) )');
        /*
        if (!($result instanceof Sqlite3Result)) {
            echo "Create hosts successful."; // This will never echo.
        } else {
            echo "NO successful.";
            $result->fetchArray(); // This will throw an error.
        }*/

        $db->query('DROP TABLE services ');
        $result = $db->query('CREATE TABLE services (id integer primary key autoincrement, service_id int UNIQUE, host_id int, priax_id int UNIQUE, service_description varchar(128))');
        /*
        if (!($result instanceof Sqlite3Result)) {
            echo "Create services successful."; // This will never echo.
        } else {
            echo "NO successful.";
            $result->fetchArray(); // This will throw an error.
        }
        */
        $db->query('DROP TABLE logs ');
        $db->query('CREATE TABLE logs (id integer primary key autoincrement, entry_time date , entry_level int, mensage text )');
        $db->query('DROP TABLE incremental_id ');
        $db->query('CREATE TABLE incremental_id (id integer primary key autoincrement, incremental_id int )');

    }

    public static function set_incremental_id( $value = null)
    {
        $db = new MyDB();
        $query = "INSERT or replace INTO incremental_id (id , incremental_id ) VALUES (( select max(id) from incremental_id) , ".$value." )";
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $db->close();
    }

    public static function insere_host($host_id, $priax_id, $name = null)
    {
        $db = new MyDB();
        $query = "INSERT or replace INTO hosts (id , host_id, priax_id, host_name) VALUES ((select ID from hosts where priax_id = ".$priax_id." ) , ".$host_id.", ".$priax_id.", '".$name."' )";
        //$query = "INSERT INTO hosts (host_id, priax_id, host_name) VALUES (".$host_id.", ".$priax_id.", '".$name."' )";
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $db->close();
    }

    public static function insere_service($service_id, $host_id, $priax_id, $service_description)
    {
        $db = new MyDB();
        $query = "INSERT or replace INTO services (id, service_id , host_id, priax_id, service_description) VALUES ((select ID from services where priax_id = ".$priax_id." ) , ".$service_id.",".$host_id.", ".$priax_id.", '".$service_description."' )";
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $db->close();
    }

    public static function insere_log($entry_time , $entry_level, $mensage )
    {
        $db = new MyDB();
        $mensage = htmlspecialchars($mensage, ENT_QUOTES);
        $query = "INSERT INTO logs (entry_time , entry_level, mensage) VALUES ('$entry_time','$entry_level', '$mensage')";
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            //var_dump("\n\n *************** Informar ao setor de DESENVOLVIMENTO *************** ");
            var_dump($query);
            var_dump($db->lastErrorMsg());
        }

        $query = "delete from logs where id < (select max(ID)-120000 from logs)";
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            //var_dump("\n\n *************** Informar ao setor de DESENVOLVIMENTO *************** ");
            var_dump($query);
            var_dump($db->lastErrorMsg());
        }
        $db->close();

    }

    public static function vacuum()
    {
        $db = new MyDB();
        $query = 'VACUUM';
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
    }

    /**DELETEs**/
    public static function delete_service_by_service_id($service_id)
    {
        $db = new MyDB();
        $query = 'delete from services where service_id = '.$service_id;
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $db->close();
    }

    public static function delete_services_by_host_id($host_id)
    {
        $db = new MyDB();
        $query = 'delete from services where host_id = '.$host_id;
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $db->close();
    }

    public static function delete_host_by_host_id($host_id)
    {
        $db = new MyDB();
        $query = 'delete from hosts where host_id = '.$host_id;
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $db->close();
    }

    public static function delete_host_and_services_by_host_id($host_id)
    {
        BpSyncDB::delete_host_by_host_id($host_id);
        BpSyncDB::delete_services_by_host_id($host_id);
    }

    public static function logs_delete()
    {
        $db = new MyDB();
        $query = 'delete from logs' ;
        $sqliteResult =  $db->query($query);
        if (!$sqliteResult ) {
            var_dump("Informar ao setor de DESENVOLVIMENTO");
            var_dump($query);
            var_dump($db->lastErrorMsg());
        }
        $db->close();
    }

    /**GETs**/

    public function get_logs_all(){
        $db = new MyDB();
        $query = 'select * from logs ORDER BY id asc';
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $result = null;
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $result[] = $row;
        }
        $db->close();
        return $result;
    }

    public function get_count_logs(){
        $db = new MyDB();
        $query = 'select count(id) as n from logs';
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $result = null;
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $result = $row["n"];
        }
        $db->close();
        return $result;
    }

    public function get_logs_number($number){
        $db = new MyDB();
        if(!$number){
            $number = 500;
        }
        $query = "select * from logs where id in (select id from logs ORDER BY id desc limit $number)  ORDER BY id asc ";
        //$query = "select priax_id from hosts order by priax_id asc";
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $result = null;
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $result[] = $row;
        }
        $db->close();
        return $result;
    }

    public function get_logs_pag($inicio, $qnt){
        $db = new MyDB();

        $query = "select * from logs ORDER BY id asc limit $inicio, $qnt ";
        //$query = "select priax_id from hosts order by priax_id asc";
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $result = null;
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $result[] = $row;
        }
        $db->close();
        return $result;
    }

    public function get_logs_level($level = 2){
        $db = new MyDB();
        $number = 500;
        //$query = 'select * from logs where entry_level = '.$level.' ORDER BY id asc ';
        $query = "select * from logs where id in (select id from logs where entry_level = ".$level." ORDER BY id desc limit $number)  ORDER BY id asc ";
        //var_dump($query);
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $result = null;
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $result[] = $row;
        }
        $db->close();
        return $result;
    }

    public function get_incremental_id(){
        $db = new MyDB();
        $query = 'select max(id), incremental_id from incremental_id';
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $result = null;
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $result = $row['incremental_id'];
        }
        $db->close();
        return $result;
    }

    public function get_logs_error(){
        return BpSyncDB::get_logs_level(2);
    }

    public function get_logs_alert(){
        return BpSyncDB::get_logs_level(1);
    }

    public function get_service_id_by_priax_id($priax_id){
        $db = new MyDB();
        $query = 'select service_id from services where priax_id = '.$priax_id;
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }

        $result = null;
        while ($row = $results->fetchArray()) {
            $result = $row[service_id];
        }
        $db->close();
        return $result;
    }

    public function get_priax_id_by_service_id($service_id){
        $db = new MyDB();
        $query = 'select priax_id from services where service_id = '.$service_id;
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }

        $result = null;
        while ($row = $results->fetchArray()) {
            $result = $row[priax_id];
        }
        $db->close();
        return $result;
    }

    public function get_priax_id_by_host_id($host_id){
        $db = new MyDB();
        $query = 'select priax_id from hosts where host_id = '.$host_id;
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }

        $result = null;
        while ($row = $results->fetchArray()) {
            $result = $row[priax_id];
        }
        $db->close();
        return $result;
    }

    public function get_host_id_by_priax_id($priax_id){
        $db = new MyDB();
        $query = 'select host_id from hosts where priax_id = '.$priax_id.' ';
        $results =  $db->query($query);
        if (!$results ) {
            Bplog::save("SQLITE $query ".$db->lastErrorMsg(),2);
        }
        $result = null;
        while ($row = $results->fetchArray()) {
            $result = $row[host_id];
        }
        $db->close();
        return $result;
    }

    /**A T E N C I O N Sinistro**/
    public function inicial_start()
    {
        /**pasta do sync**/
        chmod (dirname(dirname(__FILE__)), 0777);
        /**config**/
        chmod (dirname(dirname(__FILE__)).'/config-inc.php', 0777);

        BpSyncDB::create_tables();

        Bplog::save("--- Finish --- ");
        exit;
    }
}