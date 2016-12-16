<?php

abstract class OrderDB
{
    const OPTION_DB = 'dbname';
    const OPTION_USER = 'user';
    const OPTION_PASS = 'pass';
    const OPTION_HOST = 'host';
    const OPTION_VER  = 'version';
    const OPTION_PHOTODIR = 'photodir';

    const DEFAULT_PHOTO_DIR = 'photos';   
    const DEFAULT_PASS = '';
    const DEFAULT_HOST = 'localhost';

    protected $conn = NULL;
    protected $lastError = '';
    protected $photoDir;
    
    private $optVersion = 0;

    public function getError() {
         return $this->lastError;   
    }

    abstract protected function getIniFileName();
    abstract protected function getDefaultUserName(); 
    abstract protected function getDefaultDatabaseName(); 

    protected function doReadOptions($iniData) {
        $res = Array();
        $dbname = empty($iniData[self::OPTION_DB]) ? $this->getDefaultDatabaseName() : $iniData[self::OPTION_DB];
        $dbhost = empty($iniData[self::OPTION_HOST]) ? self::DEFAULT_HOST : $iniData[self::OPTION_HOST];
        $res['dsn'] = 'mysql:dbname=' . $dbname . ';host=' . $dbhost;
        $res['user'] = empty($iniData[self::OPTION_USER]) ? $this->getDefaultUserName() : $iniData[self::OPTION_USER];
        $res['pass'] = empty($iniData[self::OPTION_PASS]) ? self::DEFAULT_PASS : $iniData[self::OPTION_PASS];
        $photoDir = empty($iniData[self::OPTION_PHOTODIR]) ? self::DEFAULT_PHOTO_DIR : $iniData[self::OPTION_PHOTODIR];
        $this->photoDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $photoDir;
        $res['ver'] = $iniData[self::OPTION_VER];  
        return $res;
    }    

    protected function getIniData() {
        return parse_ini_file( $_SERVER['DOCUMENT_ROOT'] . '/' . $this->getIniFileName() );     
    }    

    protected function readOptions() {
        $iniData = $this->getIniData();
        return $this->doReadOptions($iniData);
    }
    protected function checkConnection()
    {
        $res = true;
        $connectData = $this->readOptions();
        if ($this->conn) {
            if (intval($connectData['ver']) > $this->optVersion) {
                unset($this->conn);
            }
        }    
        if (!$this->conn) {
            $res = true;
            try {
                $options  = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' COLLATE 'utf8_general_ci'"];
                $this->conn = new PDO($connectData['dsn'], $connectData['user'], $connectData['pass'], $options);
                //$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->optVersion = intval($connectData['ver']);
            } catch (PDOException $e) {
                $this->lastError = 'DB.CONN.' . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }
}    

