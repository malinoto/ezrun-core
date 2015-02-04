<?php
namespace Core;

class DBConnection extends BaseCore {
    
    protected $config;
    protected $connection;
    
    public function __construct() {
        
        $connectionParams = array(
            'dbname'    => db_name,
            'user'      => db_user,
            'password'  => db_pass,
            'host'      => db_host,
            'driver'    => db_driver,
            'charset'   => db_charset,
        );
        
        $this->setConfig();
        $this->setConnection($connectionParams);
    }
    
    public function setConnection($connectionParams) {
        
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $this->getConfig());
        
        return $this;
    }
    
    public function getConnection() {
        
        return $this->connection;
    }
    
    public function setConfig() {
        
        $this->config = new \Doctrine\DBAL\Configuration();
        
        return $this;
    }
    
    public function getConfig() {
        
        return $this->config;
    }
}