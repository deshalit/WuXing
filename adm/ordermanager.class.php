<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/class/order/common/order.class.php');
require_once('orderadmdb.class.php');

class OrderManager extends OrderAdmDB {
    
    const QUERY_NEW_ORDERS = 
'SELECT id, regdate, name, lastname, email, note, promocode, height, targetname, eyecolor, haircolor
 FROM orders WHERE id > 0 and status = 0 LIMIT :SKIP,:COUNT'; 

    const QUERY_PROFILES      = 'SELECT id_order, id_profile 
                                 FROM order_profiles OP 
                                 INNER JOIN orders O ON O.id = OP.id_order AND O.status = 0';
    const QUERY_DELETE_ORDER  = 'DELETE FROM orders WHERE id = :ID';
    const QUERY_UPDATE_STATUS = 'UPDATE orders SET status = 1 WHERE id = :ID';
    const DEFAULT_COUNT = 20;
    
    const DEFAULT_THUMB_WIDTH  = 128;
    const DEFAULT_THUMB_HEIGHT = 96;    
       
    private $thumbnail_width = self::DEFAULT_THUMB_WIDTH;
    private $thumbnail_height = self::DEFAULT_THUMB_HEIGHT;
    
    private function makeThumbnail($fileName) {
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
    private function deleteOrderPhotos($orderId) {
        $dir = realpath($this->photoDir);
        if (!empty($orderId)) {
            array_map('unlink', glob($dir . '/' . intval($orderId) . ".*.*"));
        }    
    }    
    private function makeXML($query, $profiles, $withImages = true) {
        date_default_timezone_set('Europe/Kiev');
        $res = '<?xml version="1.0" encoding="UTF-8"?><' . Order::PARAM_ROOT . '>';
        
        foreach ($query as $record) {
            $res .= '<' . Order::PARAM_ITEM . '>';
            if ( $record['id'] ) {
                $res .= '<' . Order::PARAM_ID . '>' . $record['id'] . '</' . Order::PARAM_ID . '>';
            }
            if ( $record['regdate'] ) {
                // make timestamp
                $t = strtotime($record['regdate']);
                $res .= '<' . Order::PARAM_DATE . '>' . date(DATE_ATOM, $t) . '</' . Order::PARAM_DATE . '>';
            }
            $res .= '<' . Order::PARAM_NAME . '>' . $record['name'] . '</' . Order::PARAM_NAME . '>';
            $res .= '<' . Order::PARAM_LASTNAME . '>' . $record['lastname'] . '</' . Order::PARAM_LASTNAME . '>';
            $res .= '<' . Order::PARAM_EMAIL . '>' . $record['email'] . '</' . Order::PARAM_EMAIL . '>';
            $res .= '<' . Order::PARAM_NOTE . '>' . $record['note'] . '</' . Order::PARAM_NOTE . '>';
            $res .= '<' . Order::PARAM_PROMO . '>' . $record['promocode'] . '</' . Order::PARAM_PROMO . '>';
            //$res .= '<' . Order::PARAM_STATUS . '>' . $record['status'] . '</' . Order::PARAM_STATUS . '>';
            $res .= '<' . Order::PARAM_TARGETNAME . '>' . $record['targetname'] . '</' . Order::PARAM_TARGETNAME . '>';
            $res .= '<' . Order::PARAM_HEIGHT . '>' . $record['height'] . '</' . Order::PARAM_HEIGHT . '>';
            $res .= '<' . Order::PARAM_EYES . '>' . $record['eyecolor'] . '</' . Order::PARAM_EYES . '>';
            $res .= '<' . Order::PARAM_HAIR . '>' . $record['haircolor'] . '</' . Order::PARAM_HAIR . '>';
            $res .= '<' . Order::PARAM_PROFILES . '>';
            //echo $record['id'] . "<br/>";
            //print_r($profiles); die();
            foreach($profiles[$record['id']] as $pid) {
                $res .= '<' . Order::PARAM_PROFILE . ' id="' . $pid . '"/>';
            }    
            $res .= '</' . Order::PARAM_PROFILES . '>';
            if ($withImages) {
                $res .= '<' . Order::PARAM_IMAGES . '>';
                $dir = realpath($this->photoDir);
                foreach (glob($dir . '/' . $record['id'] . ".*.*") as $filename) {
                    $baseName = pathinfo($filename, PATHINFO_BASENAME);
                    $ext = explode('.', $baseName)[2];
                    //echo $ext;
                    $res .= '<' . Order::PARAM_IMAGE . ' type="image/' . $ext . '" id="' . $baseName .
                            '">' . $this->makeEncodedThumbnail($filename) . '</' . Order::PARAM_IMAGE . '>';
                }
                $res .= '</' . Order::PARAM_IMAGES . '>';
            }
            $res .= '</' . Order::PARAM_ITEM . '>';            
        }   
        $res .= '</' . Order::PARAM_ROOT . '>';
        //echo $res;
        return $res;    
    }
    private function openProfileQuery() {
        if (!$this->checkConnection()) {
            return false;
        }         
        try {
            $stmt = $this->conn->prepare(self::QUERY_PROFILES);
            //$stmt->bindParam(":ID", $id, PDO::PARAM_INT);
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
    private function openNewOrdersQuery($skipCount, $selectCount) {
        if (!$this->checkConnection()) {
            echo 'bad connect';
            return false;
        }         
        try {
            $stmt = $this->conn->prepare(self::QUERY_NEW_ORDERS);
            //$stmt->bindParam(":ID", $lastId, PDO::PARAM_INT);
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
    private function prepareUpdateStatusQuery() {
        if (!$this->checkConnection()) {
            echo 'bad connect';
            return false;
        }         
        try {
            $stmt = $this->conn->prepare(self::QUERY_UPDATE_STATUS);
            $this->conn->beginTransaction();
        } catch (PDOException $e) {
            $res = NULL;
            echo 'Error: ' . $e->getMessage();
        }
        return $stmt;
    }
    private function execUpdateStatusQuery($stmt, $id) {
        $res = true;
        try {
            $stmt->bindParam(":ID", $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            $res = false;
            echo 'Error: ' . $e->getMessage();
        }
        return $res;
    }
    
    public function getImage($imageId) {
        $this->readOptions();  // reading the value for $this->photoDir
        $res = Array(3);
        $fname = $this->photoDir . '/' . basename($imageId);
        //echo 'fname= ' . $fname; 
        $res[0] = filesize($fname);
        //echo 'fsize= ' . $res[0]; 
        $res[1] = file_get_contents($fname);
        $res[2] = pathinfo($fname, PATHINFO_EXTENSION);
        //echo 'ext= ' . $res[2]; die();
        return $res;    
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
        echo $fcount;
        if ($fcount > 1) {
            return unlink($fullName);
        }
    }  
    public function getNewOrdersXML($skipCount = 0, $selectCount = self::DEFAULT_COUNT) {
        if ($query = $this->openNewOrdersQuery($skipCount, $selectCount)) {
            //echo 'main query opened<br/>';
            //print_r($query[0]);
            $profiles = array();
            if ($pquery = $this->openProfileQuery()) {
                //echo 'profile query opened<br/>';
                foreach($pquery as $pr) {
                    $oid = $pr['id_order'];
                    if (!array_key_exists($oid, $profiles)) {
                        $profiles[$oid] = array();
                    }    
                    array_push($profiles[$oid], $pr['id_profile']);
                }    
                unset($pquery);
            }    
            $res = $this->makeXML($query, $profiles);    
        } else {
            $res = false;
        }
        return $res;
    }
    public function updateStatus($orderList) {
        if ($stmt = $this->prepareUpdateStatusQuery()) {
            try {
                foreach($orderList as $id) {
                    $this->execUpdateStatusQuery($stmt, $id); 
                }            
                $this->conn->commit();
                return true;
            } catch (PDOException $e) {
                if ($this->conn->InTransaction()) {
                    $this->conn->rollBack();
                }
            }
        }
        return false;
    }
}    
