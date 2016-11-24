<?php

class UploadOptions {
    const MAX_PHOTO_UPLOAD_SIZE = 0x500000;  // 1 M
    const MAX_PHOTO_COUNT = 4;
    
    public $extensions = Array ('png', 'jpg', 'jpeg', 'gif');
    public $photoDir = './photos/';
    public $maxPhotoSize = self::MAX_PHOTO_UPLOAD_SIZE;
    public $maxPhotoCount = self::MAX_PHOTO_COUNT;
}
class OrderRegister
{
    const QUERY_ADDORDER = 'INSERT INTO orders (regdate, name, lastname, email, note, promocode, targetname, height, eyecolor, haircolor) VALUES (NOW(), :FNAME, :LNAME, :EMAIL, :NOTE, :PROMO, :TARGET, :HEIGHT, :EYES, :HAIR)';
    const QUERY_ADDPROFILE = 'INSERT INTO order_profiles (id_order, id_profile) VALUES (:ID, :PID)';
    
    const OPTIONS_FILE = 'reg.ini';
    const OPTION_DB = 'dbname';
    const OPTION_USER = 'user';
    const OPTION_PASS = 'pass';
    const OPTION_HOST = 'host';
    const OPTION_VER  = 'version';
    
    const DB_NAME = 'wuxing';
    const DB_USER = 'wx_user';
    const DB_PASS = '';
    const DB_HOST = 'localhost';

    private $conn = NULL;
    private $lastError = '';
    private $optVersion = 0;

    public function getError() {
         return $this->lastError;   
    }
    
    private function readOptions() {
        $iniData = parse_ini_file(self::OPTIONS_FILE); 
        var_dump($iniData);
        echo "<br/>" . 'user: ' . $iniData['pass'] . 'empty: ' . empty($iniData[self::OPTION_PASS]) . ', isset: "' . isset($iniData[self::OPTION_PASS]) .'", value: '. $iniData[self::OPTION_PASS];
        $res = Array();
        $dbname = empty($iniData[self::OPTION_DB]) ? self::DB_NAME : $iniData[self::OPTION_DB];
       
        $dbhost = empty($iniData[self::OPTION_HOST]) ? self::DB_HOST : $iniData[self::OPTION_HOST];
        $res['dsn'] = 'mysql:dbname=' . $dbname . ';host=' . $dbhost;
        $res['user'] = empty($iniData[self::OPTION_USER]) ? self::DB_USER : $iniData[self::OPTION_USER];
        echo "<br/>" . $iniData[self::OPTION_USER];
        $res['pass'] = empty($iniData[self::OPTION_PASS]) ? self::DB_PASS : $iniData[self::OPTION_PASS];
        $res['ver'] = $iniData[self::OPTION_VER];
        //var_dump($res);
        return $res;
    }
    public function checkConnection()
    {
        $res = true;
        $connectData = $this->readOptions();
        if ($this->conn) {
            if (intval($connectData['ver']) > $this->optVersion) {
                echo 'point 1: ' . intval($connectData['ver']) . ', ' . $this->optVersion;
                unset($this->conn);
            }
        }    
        if (!$this->conn) {
            $res = true;
            try {
                $options  = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' COLLATE 'utf8_general_ci'"];    
                $this->conn = new PDO($connectData['dsn'], $connectData['user'], $connectData['pass'], $options);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->optVersion = intval($connectData['ver']);
            } catch (PDOException $e) {
                $this->lastError = 'DB.CONN.' . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }
}
if (!$o)
    $o = new OrderRegister();  
$o->checkConnection();  
    