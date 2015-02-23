<?php
namespace Ezrun\Core;

class Model extends BaseCore {
    
    protected $modelName;
    protected $db;
    protected $dbTable;
    
    public function __construct() {
        
        $this->setModelName();
        
        $this->setDB();
        $this->setDBTable();
    }
    
    public static function Model() {
    }
    
    public function execute($sql, $values = array(), $from = null, $limit = null) {
        
        $stmt = $this->getDB()->prepare($sql);
        
        foreach($values as $key => $value) {
            
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt;
    }
    
    public function findAll($filters = array(), $from = null, $limit = null) {
        
        $sql = "SELECT * FROM " . $this->getDBTable();
        
        if(!empty($filters)) {
            
            $sql        .= " WHERE ";
            $sql_clauses = array();
            
            foreach($filters as $key => $value) {
                
                array_push($sql_clauses, "`{$key}` = :{$key}");
            }
            
            $sql .= implode(",", $sql_clauses);
        }
        
        $all = $this->execute($sql, $filters, $from, $limit);
        
        return $all->fetchAll();
    }
    
    public function setModelName() {
        
        $this->modelName = preg_replace('/(Models\\\|Model)/u', '', get_class($this));
        
        return $this;
    }
    
    public function getModelName() {
        
        return $this->modelName;
    }
    
    public function setDB() {
        
        $connection = new DBConnection();
        
        $this->db   = $connection->getConnection();
        
        return $this;
    }
    
    public function getDB() {
        
        return $this->db;
    }
    
    public function setDBTable() {
        
        //custom model database table
        if(isset($this->customDBTable) && !empty($this->customDBTable)) {
            
            if(!empty(db_prefix))
                $this->dbTable = trim(db_prefix, '_') . '_' . $this->customDBTable;
            else
                $this->dbTable = $this->customDBTable;
        }
        //self model database table
        else {
            
            $model_name_split   = preg_split('/(?=\p{Lu})/u', $this->getModelName());
            
            $this->dbTable      = strtolower(trim(db_prefix, '_') . implode('_', $model_name_split));
        }
        
        return $this;
    }
    
    public function getDBTable() {
        
        return $this->dbTable;
    }
}
