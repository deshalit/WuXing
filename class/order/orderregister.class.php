<?php
require('class/order/orderregdb.class.php');
class UploadOptions {
    const MAX_PHOTO_UPLOAD_SIZE = 0x500000;  // 1 M
    const MAX_PHOTO_COUNT = 4;
    
    public $extensions = Array ('png', 'jpg', 'jpeg', 'gif');
    //public $photoDir = './photos/';
    public $maxPhotoSize = self::MAX_PHOTO_UPLOAD_SIZE;
    public $maxPhotoCount = self::MAX_PHOTO_COUNT;
}

class OrderRegister extends OrderRegDB
{
    const QUERY_ADDORDER = 'INSERT INTO orders (regdate, name, lastname, email, note, promocode, targetname, height, eyecolor, haircolor) VALUES (NOW(), :FNAME, :LNAME, :EMAIL, :NOTE, :PROMO, :TARGET, :HEIGHT, :EYES, :HAIR)';
    const QUERY_ADDPROFILE = 'INSERT INTO order_profiles (id_order, id_profile) VALUES (:ID, :PID)';
    
    const PARAM_FIRSTNAME = 'fname';
    const PARAM_LASTNAME  = 'lname';
    const PARAM_TARGETNAME = 'tname';
    const PARAM_EMAIL     = 'email';
    const PARAM_ID        = 'id';
    const PARAM_PROFILES  = 'prof';
    const PARAM_PHOTO     = 'photo';
    const PARAM_NOTE      = 'notes';
    const PARAM_PROMOCODE = 'promo';
    const PARAM_EYECOLOR  = 'eyes';
    const PARAM_HAIRCOLOR = 'hair';
    const PARAM_HEIGHT    = 'height';

    protected function doWriteOrder(Order $order) 
    {
        try {
            $stmt = $this->conn->prepare(self::QUERY_ADDORDER);
            $stmt->bindParam(":FNAME", $order->firstName, PDO::PARAM_STR);
            $stmt->bindParam(":LNAME", $order->lastName, PDO::PARAM_STR);
            $stmt->bindParam(":EMAIL", $order->email, PDO::PARAM_STR);
            $stmt->bindParam(":NOTE", $order->notes, PDO::PARAM_STR);
            $stmt->bindParam(":PROMO", $order->promoCode, PDO::PARAM_STR);
            $stmt->bindParam(":EYES", $order->eyes, PDO::PARAM_STR);
            $stmt->bindParam(":HAIR", $order->hair, PDO::PARAM_STR);
            $stmt->bindParam(":TARGET", $order->targetName, PDO::PARAM_STR);
            $stmt->bindParam(":HEIGHT", $order->height, PDO::PARAM_STR);
            $this->conn->beginTransaction();
            $stmt->execute();
            $order->id = $this->conn->lastInsertId();
            
            $stmt = $this->conn->prepare(self::QUERY_ADDPROFILE);
            $stmt->bindParam(":ID", $order->id, PDO::PARAM_INT);
            // store profiles
            foreach ($order->profiles as $pid) {
                $stmt->bindParam(":PID", $pid, PDO::PARAM_INT);
                $stmt->execute();
            }
            $this->conn->commit();
        } catch (PDOException $e) {
            //$res = NULL;
            $this->lastError = 'DB.QUERY.' . $e->getMessage();
            return false;  //echo $res;
        }
        //echo 'orderid=' . $order->id;
        return true;
    }

    public function saveOrder(Order $order)
    {
        if ($this->checkConnection()) {
            if ($this->doWriteOrder($order)) {
                $this->renamePhotoFiles($order);   
                return $order->id; 
            }    
        }    
        $this->removePhotoFiles($order);
        return false;
    } 

    private function handlePhoto($no, UploadOptions $options, $order) {
        //var_dump($photo); die();
        $origName = $_FILES[self::PARAM_PHOTO]['name'][$no];
        $errCode = $_FILES[self::PARAM_PHOTO]['error'][$no];
        if (!isset($errCode)) {
            throw new Exception('PHOTO['.$no.']');
        }
        if ($errCode != UPLOAD_ERR_OK) {
            throw new Exception('PHOTO["' . $origName . '"].' . $errCode);
        }
        $tempFileName = $_FILES[self::PARAM_PHOTO]['tmp_name'][$no];
        //echo 'temp name: "' . $tempFileName . '"';
        $mimeType = mime_content_type($tempFileName);
        //echo 'mime type: "' . $mimeType . '"';
        if ($pos = strpos($mimeType, '/')) {
            $ext = substr($mimeType, $pos + 1);
            //echo $pos . '; ' . $ext;
        } else {
            //echo $pos . "\n"; strp
            throw new Exception('PHOTO["' . $origName . '"].FORMAT.WRONG(' . $mimeType . ')');
        }
        if (!in_array($ext, $options->extensions)) {
            throw new Exception('PHOTO["' .  $origName . '"].FORMAT.DISALLOWED(' . $mimeType . ')');
        }
        $targetName = sha1_file($tempFileName) . '.' . $ext;
        if (!move_uploaded_file($tempFileName, $this->photoDir . '/' . $targetName)) {
            throw new Exception('PHOTO["' .  $origName . '"].MOVE');
        }
        array_push($order->fileNames, $targetName);
    }
    
    public function createOrder(UploadOptions $options)
    {
        $order = false;
        $fileNames = [];
        $this->lastError = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->readOptions();
            $order = new Order();
            $checks = Array(self::PARAM_FIRSTNAME, self::PARAM_LASTNAME, self::PARAM_EMAIL, self::PARAM_PROFILES); 
            try {
                foreach ($checks as $check) {
                    if (empty($_POST[$check])) {
                        throw new Exception('EMPTY.' . $check);
                    }
                }    
                $order->firstName = $_POST[self::PARAM_FIRSTNAME];               
                $order->lastName = $_POST[self::PARAM_LASTNAME];               
                $order->email = $_POST[self::PARAM_EMAIL];
                
                if (!is_array($_POST[self::PARAM_PROFILES]) or (count($_POST[self::PARAM_PROFILES]) == 0)) {
                    throw new Exception('EMPTY.' . self::PARAM_PROFILES);
                }
                $order->profiles = array_keys($_POST[self::PARAM_PROFILES]);
                $order->height = $_POST[self::PARAM_HEIGHT];
                $order->eyes = $_POST[self::PARAM_EYECOLOR];
                $order->hair = $_POST[self::PARAM_HAIRCOLOR];
                $order->targetName = $_POST[self::PARAM_TARGETNAME];
                
                //var_dump($_FILES[self::PARAM_PHOTO]); die();
                if (empty($_FILES[self::PARAM_PHOTO]) or (count($_FILES[self::PARAM_PHOTO]) == 0)) {
                    throw new Exception('EMPTY.' . self::PARAM_PHOTO);
                } 
                $fileCount = count($_FILES[self::PARAM_PHOTO]['name']);
                if ($fileCount > $options->maxPhotoCount) {
                    $fileCount = $options->maxPhotoCount;
                }    
                for ($i = 0; $i < $fileCount; $i++) {
                    $this->handlePhoto($i, $options, $order);
                }
            } catch (Exception $e) {
                $this->lastError = $e->getMessage();
                unset($order);
                return false;
            }
            if (!empty($_POST[self::PARAM_NOTE])) {
                $order->notes = $_POST[self::PARAM_NOTE];
            }
            if (!empty($_POST[self::PARAM_PROMOCODE])) {
                $order->promoCode = $_POST[self::PARAM_PROMOCODE];
            }
        }
        return $order;
    }
    
    private function renamePhotoFiles($order) {
        //var_dump($order->fileNames);
        foreach($order->fileNames as $fname) {
            rename(realpath($this->photoDir) . '/' . $fname, realpath($this->photoDir) . '/' . $order->id . '.' . $fname);
        }
    }    
    private function removePhotoFiles($order) {
        foreach($order->fileNames as $fname) {
            unlink(realpath($this->photoDir) . '/' . $fname);
        }
    }
}