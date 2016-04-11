<?php

class MongoDbDriver { 
     
    private $config = null;
    private $mongo;     
    private $curr_db_name;
    private $error;     

    public function __construct() {

	}

    function open($config){
        $this->config = $config;
        if($config['autoconnect'] == 1) {
            $this->connect();
        }
    }

    function connect(){
        try{
            $this->mongo = new Mongo($this->config['hostname']);
            $this->selectDb($this->config['database']);
            //$this->mongo = new Mongo("mongodb://${$this->config['username']}:${$this->config['password']}@$this->config['hostname']/$this->config['database']");
        }catch(MongoConnectionException $e){
            $this->error = $e->getMessage();
        }
    }
    function selectDb($dbname) {     
        $this->curr_db_name = $dbname;     
    }
    function insert($table_name, $record) {     
        $dbname = $this->curr_db_name;     
        try {     
            $this->mongo->$dbname->$table_name->insert($record, array('safe'=>true));     
            return true;     
        }     
        catch (MongoCursorException $e) {     
            $this->error = $e->getMessage();     
            return false;     
        }     
    }

    function count($table_name){     
        $dbname = $this->curr_db_name;     
        return $this->mongo->$dbname->$table_name->count();
    }
    function update($table_name, $condition, $newdata, $options=array()){     
        $dbname = $this->curr_db_name;     
        $options['safe'] = 1;     
        if (!isset($options['multiple'])) { 
        $options['multiple'] = 0;
        }     
        try{     
            $this->mongo->$dbname->$table_name->update($condition, $newdata, $options);     
            return true;
        }     
        catch (MongoCursorException $e) {     
            $this->error = $e->getMessage();     
            return false;     
        }          
    }
    function remove($table_name, $condition, $options=array()) {     
        $dbname = $this->curr_db_name;     
        $options['safe'] = 1;     
        try {     
            $this->mongo->$dbname->$table_name->remove($condition, $options);     
            return true;     
        }     
        catch (MongoCursorException $e){     
            $this->error = $e->getMessage();     
            return false;     
        }         
    }
    function find($table_name, $query_condition, $result_condition=array(), $fields=array()) {     
        $dbname = $this->curr_db_name;     
        $cursor = $this->mongo->$dbname->$table_name->find($query_condition, $fields); 
        
        //$cursor->snapshot();        
        if (!empty($result_condition['start'])) {     
            $cursor->skip($result_condition['start']);     
        }     
        if (!empty($result_condition['limit'])) {     
            $cursor->limit($result_condition['limit']);     
        }     
        if (!empty($result_condition['sort'])) {     
            $cursor->sort($result_condition['sort']);     
        }     
        $result = array();     
        try{     
            while ($cursor->hasNext()){     
                $result[] = $cursor->getNext();     
            }     
        }catch (MongoConnectionException $e) {     
            $this->error = $e->getMessage();     
            return false;     
        }catch (MongoCursorTimeoutException $e){     
            $this->error = $e->getMessage();     
            return false;     
        }     
        return $result;     
    }
    
    function findOne($table_name, $condition, $fields=array()) {     
        $dbname = $this->curr_db_name;     
        return $this->mongo->$dbname->$table_name->findOne($condition, $fields);     
    }
    function getError() {     
        return $this->error;     
    }
    function close(){
        $this->mongo->close();
    }
    function table_exists($table_name){
        $tables = $this->list_tables();
		return in_array($table_name, $tables) ? 1 : 0;
    }
    function list_tables(){
        return $this->mongo->selectDB($this->curr_db_name)->getCollectionNames();
    }
    public function __destruct(){
        $this->close();
    }
}
?>