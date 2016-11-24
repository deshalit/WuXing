<?php
require_once('../order.const.php');
require_once('../order.class.php');

class OrderReader {
    
    const QUERY_NEW_ORDERS_SINCE = 'SELECT id, status, regdate, name, lastname, email, note, promocode, height, targetname, eyecolor, haircolor FROM orders WHERE status = 0 AND id > 0 AND regdate > :DATE LIMIT :SKIP,:COUNT';
    const QUERY_ORDER_BY_ID      = 'SELECT name, lastname, email, note, promocode, height, targetname, eyecolor, haircolor FROM orders WHERE id = :ID';
    const QUERY_PROFILES         = 'SELECT id_profile FROM order_profiles WHERE id_order = :ID';
    const QUERY_DELETE_ORDER     = 'DELETE FROM orders WHERE id = :ID';
    const DEFAULT_COUNT = 20;
    
    const OPTIONS_FILE = 'adm.ini';
    const OPTION_DB = 'dbname';
    const OPTION_USER = 'user';
    const OPTION_PASS = 'pass';
    const OPTION_HOST = 'host';
    const OPTION_PHOTODIR = 'photodir';
    const OPTION_VER  = 'version';
    
    const DB_HOST = 'localhost';       
    const DB_NAME = 'wuxing';
    const DB_USER = 'wx_admin';
    const DB_PASS = '';
    const DEFAULT_DATE = '2016-09-01 00:00:00';
    const PHOTO_DIR = '../photos';

    const DEFAULT_THUMB_WIDTH  = 128;
    const DEFAULT_THUMB_HEIGHT = 96;    

    private $thumbnail_width = self::DEFAULT_THUMB_WIDTH;
    private $thumbnail_height = self::DEFAULT_THUMB_HEIGHT;
    
    private $conn = null;
    private $photoDir = self::PHOTO_DIR;
    private $optVersion = 0;
    
    public function getImage($imageId) {
        $res = Array(3);
        $fname = $this->photoDir . '/' . basename($imageId);
        $res[0] = filesize($fname);
        $res[1] = file_get_contents($fname);
        $res[2] = pathinfo($fname, PATHINFO_EXTENSION);
        return $res;    
    }    
    private function readOptions() {
        $iniData = parse_ini_file(self::OPTIONS_FILE); 
        $res = Array();
        $dbname = empty($iniData[self::OPTION_DB]) ? self::DB_NAME : $iniData[self::OPTION_DB];
        $dbhost = empty($iniData[self::OPTION_HOST]) ? self::DB_HOST : $iniData[self::OPTION_HOST];
        $res['dsn'] = 'mysql:dbname=' . $dbname . ';host=' . $dbhost;
        $res['user'] = empty($iniData[self::OPTION_USER]) ? self::DB_USER : $iniData[self::OPTION_USER];
        $res['pass'] = empty($iniData[self::OPTION_PASS]) ? self::DB_PASS : $iniData[self::OPTION_PASS];
        $res['ver'] = intval($iniData[self::OPTION_VER]);
        $this->photoDir = empty($iniData[self::OPTION_PHOTODIR]) ? self::PHOTO_DIR : $iniData[self::OPTION_PHOTODIR];
        return $res;
    }
    protected function checkConnection()
    {
        $res = true;
        $connectData = $this->readOptions();
        if ($this->conn) {
            if ($connectData['ver'] > $this->optVersion) {
                unset($this->conn);
            }
        }  
        if (!$this->conn) {
            $res = true;
            //print_r($connectData);
            try {
                $options  = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' COLLATE 'utf8_general_ci'"];
                $this->conn = new PDO($connectData['dsn'], $connectData['user'], $connectData['pass'], $options);
                $this->optVersion = intval($connectData['ver']);
            } catch (PDOException $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }
    private function makeThumbnail($fileName)
    {
        $arr_image_details = getimagesize($fileName); // full filename (with path)
        $original_width = $arr_image_details[0];
        $original_height = $arr_image_details[1];
        if ($original_width > $original_height) {
            $new_width = $this->thumbnail_width;
            $new_height = intval($original_height * $new_width / $original_width);
        } else {
            $new_height = $this->thumbnail_height;
            $new_width = intval($original_width * $new_height / $original_height);
        }
        $dest_x = intval(($this->thumbnail_width - $new_width) / 2);
        $dest_y = intval(($this->thumbnail_height - $new_height) / 2);
        switch (strtolower($arr_image_details['mime'])) {
            case 'image/png':
                $imgt = "ImagePNG";
                $ext = 'png';
                $imgcreatefrom = "ImageCreateFromPNG";
                break;
            case 'image/jpeg':
                $imgt = "ImageJPEG";
                $ext = 'jpg';
                $imgcreatefrom = "ImageCreateFromJPEG";
                break;
            case 'image/gif':
                $imgt = "ImageGIF";
                $ext = 'gif';
                $imgcreatefrom = "ImageCreateFromGIF";
                break;
            default:
                //echo 'unknown type';
                //die();
        }

        if ($imgt) {
            $old_image = $imgcreatefrom($fileName);
            $new_image = imagecreatetruecolor($this->thumbnail_width, $this->thumbnail_height);
            if (imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height)) {
                $tempFileName = tempnam(sys_get_temp_dir(), 'wx_');
                //echo 'Temp File is ' . $tempFileName;
                if ($imgt($new_image, $tempFileName)) {
                    $tempFile = fopen($tempFileName, "r");
                    $contents = fread($tempFile, filesize($tempFileName));
                    fclose($tempFile);
                    //echo 'size: ' . filesize($tempFileName) . '; data = ' . $contents;
                    return $contents;
                }
                unlink($tempFileName);  // delete temp file
            }

        }
        return null;
    }
    private function makeEncodedThumbnail($fileName) {
        if ($rawData = $this->makeThumbnail($fileName)) {
            return base64_encode($rawData);
        } else return null;   
    }
    public function deleteOrder($id) {
        if (!$this->checkConnection()) {
            return false;
        }         
        try {
            $stmt = $this->conn->prepare(self::QUERY_DELETE_ORDER);
            $stmt->bindParam(":ID", $id, PDO::PARAM_INT);
            $stmt->execute();
            $this->deleteOrderPhotos($id);
        } catch (PDOException $e) {
            $res = NULL;
            echo 'Error: ' . $e->getMessage();
        }
        return true;
    }
    public function deletePhoto($photoName) {
        $dir = realpath($this->photoDir);
        $baseName = basename($photoName);
        $id = explode('.', $baseName)[0];
        $fullName = $dir . '/' . $baseName;
        $fcount = 0;
        foreach (glob($dir . '/' . $id . ".*.*") as $filename) {
            $fcount += 1;
        }    
        if ($fcount > 1) {
            return unlink($fullName);
        }
    }  
    private function deleteOrderPhotos($orderId) {
        $dir = realpath($this->photoDir);
        if (!empty($orderId)) {
            array_map('unlink', glob($dir . '/' . intval($orderId) . ".*.*"));
        }    
    }    
    private function makeXML($query, $withImages = true) {
        date_default_timezone_set('Europe/Kiev');
        $res = '<?xml version="1.0" encoding="UTF-8"?><' . NODE_ORDER_ROOT . '>';
        
        foreach ($query as $record) {
            $res .= '<' . NODE_ORDER_ITEM . '>';
            if ( $record['id'] ) {
                $res .= '<' . NODE_ORDER_ID . '>' . $record['id'] . '</' . NODE_ORDER_ID . '>';
            }
            if ( $record['regdate'] ) {
                // make timestamp
                $t = strtotime($record['regdate']);
                $res .= '<' . NODE_ORDER_DATE . '>' . date(DATE_ATOM, $t) . '</' . NODE_ORDER_DATE . '>';
            }
            $res .= '<' . NODE_ORDER_NAME . '>' . $record['name'] . '</' . NODE_ORDER_NAME . '>';
            $res .= '<' . NODE_ORDER_LASTNAME . '>' . $record['lastname'] . '</' . NODE_ORDER_LASTNAME . '>';
            $res .= '<' . NODE_ORDER_EMAIL . '>' . $record['email'] . '</' . NODE_ORDER_EMAIL . '>';
            $res .= '<' . NODE_ORDER_NOTE . '>' . $record['note'] . '</' . NODE_ORDER_NOTE . '>';
            $res .= '<' . NODE_ORDER_PROMO . '>' . $record['promocode'] . '</' . NODE_ORDER_PROMO . '>';
            $res .= '<' . NODE_ORDER_STATUS . '>' . $record['status'] . '</' . NODE_ORDER_STATUS . '>';
            $res .= '<' . NODE_ORDER_TARGETNAME . '>' . $record['targetname'] . '</' . NODE_ORDER_TARGETNAME . '>';
            $res .= '<' . NODE_ORDER_HEIGHT . '>' . $record['height'] . '</' . NODE_ORDER_HEIGHT . '>';
            $res .= '<' . NODE_ORDER_EYES . '>' . $record['eyecolor'] . '</' . NODE_ORDER_EYES . '>';
            $res .= '<' . NODE_ORDER_HAIR . '>' . $record['haircolor'] . '</' . NODE_ORDER_HAIR . '>';
            if ($withImages) {
                $res .= '<' . NODE_ORDER_IMAGES . '>';
                $dir = realpath($this->photoDir);
                foreach (glob($dir . '/' . $record['id'] . ".*.*") as $filename) {
                    $baseName = pathinfo($filename, PATHINFO_BASENAME);
                    $ext = explode('.', $baseName)[2];
                    //echo $ext;
                    $res .= '<' . NODE_ORDER_IMAGE . ' type="image/' . $ext . '" id="' . $baseName .
                            '">' . $this->makeEncodedThumbnail($filename) . '</' . NODE_ORDER_IMAGE . '>';
                }
                $res .= '</' . NODE_ORDER_IMAGES . '>';
            }
            $res .= '</' . NODE_ORDER_ITEM . '>';            
        }   
        $res .= '</' . NODE_ORDER_ROOT . '>';
        //echo $res;
        return $res;    
    }
    private function openOrderQuery($id) {
        if (!$this->checkConnection()) {
            return false;
        }         
        try {
            $stmt = $this->conn->prepare(self::QUERY_ORDER_BY_ID);
            $stmt->bindParam(":ID", $id, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $res = NULL;
            echo 'Error: ' . $e->getMessage();
        }
        return $res;
    }
    private function openProfileQuery($id) {
        if (!$this->checkConnection()) {
            return false;
        }         
        try {
            $stmt = $this->conn->prepare(self::QUERY_PROFILES);
            $stmt->bindParam(":ID", $id, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            //var_dump($res);
        } catch (PDOException $e) {
            $res = NULL;
            echo 'Error: ' . $e->getMessage();
        }
        return $res;
    }
    private function openNewOrdersQuery($dateSince, $skipCount, $selectCount) {
        if (!$this->checkConnection()) {
            return false;
        }         
        try {
            $stmt = $this->conn->prepare(self::QUERY_NEW_ORDERS_SINCE);
            $stmt->bindParam(":DATE", $dateSince, PDO::PARAM_STR);
            $stmt->bindParam(":SKIP", $skipCount, PDO::PARAM_INT);
            $stmt->bindParam(":COUNT", $selectCount, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $res = NULL;
            echo 'Error: ' . $e->getMessage();
        }
        return $res;
    }
    public function getNewOrdersXML($dateSince = self::DEFAULT_DATE, $skipCount = 0, $selectCount = self::DEFAULT_COUNT) {
        if ($query = $this->openNewOrdersQuery($dateSince, $skipCount, $selectCount)) {
            //echo 'query opened<br/>';
            //print_r($query[0]);
            $res = $this->makeXML($query);    
        } else {
            $res = false;
        }
        return $res;
    }
    public function getOrderById($id) {
        if ($query = $this->openOrderQuery($id)) {
            $res = new Order();
            $res->id        = $id;
            $res->firstName = $query['name'];
            $res->lastName  = $query['lastname'];
            $res->notes     = $query['note'];
            $res->email     = $query['email'];
            $res->promoCode = $query['promocode'];
            $res->height    = $query['height'];
            $res->targetName = $query['targetname'];
            $res->eyes      = $query['eyecolor'];
            $res->hair      = $query['haircolor'];
            if ($query = $this->openProfileQuery($id)) {
                $res->profiles = Array();
                //var_dump($pquery);
                foreach ($query as $prof) {
                     array_push($res->profiles, intval($prof['id_profile']));
                }    
                //var_dump($res->profiles);
            } else $res = false;    
        } else {
            $res = false;
        }
        return $res;
    }
}    
