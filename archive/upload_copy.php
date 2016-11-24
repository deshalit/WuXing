<?php
require_once("dict.class.php");
require_once("order.const.php");
require_once("order.class.php");

include_once("dictxml.inc.php");

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
    const QUERY_ADDORDER = 'INSERT INTO orders (regdate, name, lastname, email, note, promocode) VALUES (NOW(), :FNAME, :LNAME, :EMAIL, :NOTE, :PROMO)';
    const DB_NAME = 'wuxing';
    const DB_USER = 'wx_user';
    const DB_PASS = '';
    const DB_HOST = 'localhost';

    private $conn = NULL;

    protected function checkConnection()
    {
        $res = true;
        if (!$this->conn) {
            $res = true;
            $dsn = 'mysql:dbname=' . self::DB_NAME . ';host=' . self::DB_HOST;
            $user = self::DB_USER;
            $pass = self::DB_PASS;
            //echo 'dsn is "' . $dsn . '"' . "<br/>";
            try {
                $this->conn = new PDO($dsn, $user, $pass);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }

    protected function doWriteOrder(Order $order)
    {
        try {
            $stmt = $this->conn->prepare(self::QUERY_ADDORDER);
            $stmt->bindParam(":FNAME", $order->firstName, PDO::PARAM_STR);
            $stmt->bindParam(":LNAME", $order->lastName, PDO::PARAM_STR);
            $stmt->bindParam(":EMAIL", $order->email, PDO::PARAM_STR);
            //$stmt->bindParam(":FILE", $order->fileName, PDO::PARAM_STR);
            $stmt->bindParam(":NOTE", $order->notes, PDO::PARAM_STR);
            $stmt->bindParam(":PROMO", $order->promoCode, PDO::PARAM_STR);
            $this->conn->beginTransaction();
            $stmt->execute();
            $order->id = $this->conn->lastInsertId();
            $this->conn->commit();
        } catch (PDOException $e) {
            //$res = NULL;
            //echo 'error: ' . $e->getMessage();
            return false;  //echo $res;
        }
        //echo 'orderid=' . $order->id;
        return true;
    }

    public function saveOrder(Order $order, UploadOptions $options)
    {
        if ($this->checkConnection()) {
            if ($this->doWriteOrder($order)) {
                $this->renamePhotoFiles($options, $order);   
                return $order->id; 
            }    
        }    
        return false;
    } 

    private function handlePhoto($no, UploadOptions $options, $order) {
        //var_dump($photo); die();
        $origName = $_FILES[PARAM_ORDER_PHOTO]['name'][$no];
        $errCode = $_FILES[PARAM_ORDER_PHOTO]['error'][$no];
        if (!isset($errCode)) {
            throw new RuntimeException('Photo is empty');
        }
        if ($errCode != UPLOAD_ERR_OK) {
            throw new RuntimeException('Photo ' . $origName . ' failed to load: code ' . $errCode);
        }
        $tempFileName = $_FILES[PARAM_ORDER_PHOTO]['tmp_name'][$no];
        //echo 'temp name: "' . $tempFileName . '"';
        $mimeType = mime_content_type($tempFileName);
        //echo 'mime type: "' . $mimeType . '"';
        if ($pos = strpos($mimeType, '/')) {
            $ext = substr($mimeType, $pos + 1);
            //echo $pos . '; ' . $ext;
        } else {
            //echo $pos . "\n"; strp
            throw new RuntimeException('Wrong photo format: ' . $origName);
        }
        if (!in_array($ext, $options->extensions)) {
            throw new RuntimeException('Photo ' .  $origName . ' has disallowed format (' . $mimeType . ')');
        }
        $targetName = sha1_file($tempFileName) . '.' . $ext;
        if (!move_uploaded_file($tempFileName, $options->photoDir . $targetName)) {
            throw new RuntimeException('Failed to save photo ' .  $origName);
        }
        array_push($order->fileNames, $targetName);
    }
    
    public function createOrder(UploadOptions $options)
    {
        $order = false;
        $fileNames = [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order = new Order();
            try {
                if (empty($_POST[PARAM_ORDER_FIRSTNAME])) {
                    throw new RuntimeException('First name is empty');
                }
                $order->firstName = $_POST[PARAM_ORDER_FIRSTNAME];
                
                if (empty($_POST[PARAM_ORDER_LASTNAME])) {
                    throw new RuntimeException('Last name is empty');
                }
                $order->lastName = $_POST[PARAM_ORDER_LASTNAME];
                
                if (empty($_POST[PARAM_ORDER_EMAIL])) {
                    throw new RuntimeException('Email is empty');
                }
                $order->email = $_POST[PARAM_ORDER_EMAIL];
                
                //var_dump($_FILES[PARAM_ORDER_PHOTO]); die();
                if (empty($_FILES[PARAM_ORDER_PHOTO]) or (count($_FILES[PARAM_ORDER_PHOTO]) == 0)) {
                    throw new RuntimeException('NO PHOTOS');
                } 
                $fileCount = count($_FILES[PARAM_ORDER_PHOTO]['name']);
                if ($fileCount > $options->maxPhotoCount) {
                    $fileCount = $options->maxPhotoCount;
                }    
                for ($i = 0; $i < $fileCount; $i++) {
                    $this->handlePhoto($i, $options, $order);
                }
            } catch (RuntimeException $e) {
                echo $e->getMessage();
                unset($order);
                return false;
            }
            if (!empty($_POST[PARAM_ORDER_NOTE])) {
                $order->notes = $_POST[PARAM_ORDER_NOTE];
            }
            if (!empty($_POST[PARAM_ORDER_PROMOCODE])) {
                $order->promoCode = $_POST[PARAM_ORDER_PROMOCODE];
            }
        }
        return $order;
    }
    
    private function renamePhotoFiles(UploadOptions $options, $order) {
        //var_dump($order->fileNames);
        foreach($order->fileNames as $fname) {
            //echo realpath($options->photoDir) . '<br/>';
            //echo $fname. '<br/>';
            //echo $order->id . '.' . $fname;
            rename(realpath($options->photoDir) . '/' . $fname, realpath($options->photoDir) . '/' . $order->id . '.' . $fname);
        }
    }    
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderMan = new OrderRegister();
    $options = new UploadOptions();
    if ($order = $orderMan->createOrder($options)) {
        $res = $orderMan->saveOrder($order, $options);
        exit($res);
    }
}
function buildProfiles(Dictionary $dictionary) {
    foreach($dictionary->profiles as $pKey=>$pInfo) {
       echo '<li><label><input type="checkbox" id="prof' . $pKey . '" name="prof[' . $pKey . ']">' . $pInfo[0] . '</label></li>' . "\n";     
    }    
}    

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявка</title>
    <style>
        label, input, textarea {
            display: block; 
        }    
        input, textarea {
            width: 100%;    
            margin-bottom: 20px;
            font-size: large;
        }    
        ul {
            list-style-type: none;
            letter-spacing: 1px;
        }    
        a {
            font-size: large;  
            text-decoration: none;
            border-color: burlywood;
            border-width: medium;
            display: inline-block;
            border-style: solid;
            margin-top: 5px;
            border-radius: 10px;
            padding: 5px 10px;
            background-color: ivory;
                    
        }    
        form {
            font-family: "Trebuchet MS",Arial,Helvetica,sans-serif;
            width: 60%;
            max-width: 700px;
            margin: auto;
            border-width: medium;
            border-style: outset;
            padding: 15px 17px 0 12px;
            background-color: #EAEBDB;
            border-radius: 5px;
        }    
        span.param {
            font-size: large; 
            margin-left: 5px;
            margin-right: 5px;
        }    
        p { margin: auto; 
            margin-top: 50px;
            font-size: large;
        }
        input[type=submit], input[type=button] { 
            height: 40px; 
        }
        table {
            border-spacing: 0px;    
            width: 100%;
        }    
        table label { display: inline; }
        
        .c1 {
            width: 50%;
        }    
        
        span.note {
            font-size: small;   
            padding-left: 5px;
        }    
        
        td:last-of-type {
            padding-left: 4px;
        }    
        tr > td:first-of-type {
            padding-right: 4px;
        }    
        .c2 {
            padding-left: 4px;
            padding-right: 4px;
        }
        #warnManyFiles {
            color: red;
            font-weight: bold;
            display: block;
            display: none;
        }   
        #file_input {
            display: none;
        }   
        textarea {
            margin-top: 10px;
        }    
        #promo {
            width: 90px;
        }    
        #promocode > * {
            display: inline;   
        }    
        #profiles > * {
           display: inline-block;   
        }    
        #profiles input {
            width: 20px;   
            display: inline-block;  
            cursor: pointer;    
        }    
       /*
        table {
           display: none; 
           font-family: monospace;
           background-color: #DDE3BA;
           width: 100%;
        }    
        tbody { text-align: center; }
        */        
    </style>
    <script src="jquery.min.js"></script>
    <script>
        const MAX_FILE_COUNT = <?=UploadOptions::MAX_PHOTO_COUNT?>; 
        function updateFileList(files) {
            var fileCount = (files.length > MAX_FILE_COUNT) ? MAX_FILE_COUNT : files.length;
//            var s = '';
            for (var i=0, summSize = 0; i < fileCount; i++) {
                summSize += files[i].size;
/*
                var fn = (files[i].name.length <= 15) ? files[i].name.length : 15;
                s += '<tr><td>' + (i+1) + '</td><td>' + files[i].name.substr(0,fn) + '</td><td>' + (files[i].size / 0x100000).toFixed(2) +
                     '</td><td>ожидает отправки</td></tr>';                
*/     
            }  
           
//            var summaryText = 'Выбрано файлов: ' + files.length;
            $('#filecount span').html(files.length);
            if (fileCount < files.length) {
//              summaryText += '<span id="warnManyFiles">Загружены будут только первые ' + fileCount + ' из них!</span>';
                $('#warnManyFiles').show().children().first().html(fileCount);
            } else 
                $('#warnManyFiles').hide();   
            
//            $('tbody').html(s); 
//            $('tfoot td').html(summaryText);
//            $('table').css('display', (files.length == 0) ? 'none' : 'table');
        }  
        
        function rebuildPage(code) {
            $('body').html('<p>Ваши данные внесены под номером<span>' + code + '</span></p>' +
                           '<p>Пожалуйста, запомните этот номер. Спасибо.</p>');   
        }    
        function onformsubmit() { 
            fcname="<?=PARAM_ORDER_PHOTO?>[]";
            var fileCount = ($('#file_input')[0].files.length > MAX_FILE_COUNT) ? MAX_FILE_COUNT : $('#file_input')[0].files.length;
            if (fileCount == 0) {
                alert('Выберите хотя бы одно фото!'); return;
            }
            var elems = [$('#fname')[0], $('#lname')[0], $('#email')[0], $('#promo')[0], $('#note')[0]];
            for (var i=0; i<elems.length; i++) {
                if (!elems[i].reportValidity()) return;
            }    
            
            fd = new FormData();
            for (var i=0; i<fileCount; i++) {
                fd.append(fcname, $('#file_input')[0].files[i]);
            }    
            for (var i=0; i<elems.length; i++) {
                if (elems[i].value) {
                    fd.append(elems[i].name, elems[i].value.trim());
                }    
            }    
            
            var xhr = new XMLHttpRequest();          
            xhr.open("POST", $('form').prop('action'), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                     rebuildPage(xhr.responseText); 
                }
            };
            xhr.send(fd);
        }    
        $(document).ready( function(){
            if (!window.FormData) {
               alert('К сожалению, в Вашем браузере работа данной страницы невозможна'); 
                              
            }    
            var fileSelect = document.getElementById("file_input2"),
                fileInput = document.getElementById("file_input");
            fileSelect.addEventListener("click", 
                function (e) {
                    fileInput.click();
                    e.preventDefault(); // prevent navigation to "#"
            }, false);  
            //fileInput.setCustomValidity("Вы не выбрали фото!");
            //promo = document.getElementById("promo");
            //promo.setCustomValidity("Введите промокод в формате 'пять цифр'!");
        } );
         
    </script>
</head>
<body>
    <form enctype="multipart/form-data" action="upload.php" method="POST">
        <table>
            <tr><td><label for="fname">Имя заказчика:</label></td><td><label for="lname">Фамилия заказчика:</label></td></tr>
            <tr><td class="c1"><input id="fname" name="<?=PARAM_ORDER_FIRSTNAME?>" required/></td>
                <td class="c1"><input id="lname" name="<?=PARAM_ORDER_LASTNAME?>" required/></td></tr>
        </table>
        <table>
            <tr><td><label for="fname">Имя диагностируемого:</label></td>
                <td><label for="email">Email</label><span class="note">(на этот адрес будет выслан отчет)</span></td></tr>
            <tr><td class="c1"><input id="tname" name="<?=PARAM_ORDER_TARGETNAME?>" required/></td>
                <td class="c1"><input id="email" name="<?=PARAM_ORDER_EMAIL?>" required/></td></tr>
        </table>
        <table>
        <tr><td><label for="height">Рост:</label></td><td class="c2"><label for="eyecolor">Цвет глаз:</label></td><td><label for="haircolor">Цвет волос:</label></td></tr>
        <tr><td><input id="height" name="<?=PARAM_ORDER_HEIGHT?>" required/></td><td class="c2"><input id="eyecolor" name="<?=PARAM_ORDER_EYECOLOR?>" required/></td><td><input id="haircolor" name="<?=PARAM_ORDER_HAIRCOLOR?>" required/></td></tr>
        </table>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?=UploadOptions::MAX_PHOTO_UPLOAD_SIZE?>" />
        
        <!--<label for="file_input2">Фото:</label>
        <table>
            <thead>
                <tr><th>№</th><th>Имя файла</th><th>Размер (Мб)</th><th>состояние</th></tr>
            </thead>
            <tfoot>
                <tr><td colspan="4"></td></tr>
            </tfoot>
            <tbody></tbody>
        </table>-->
        <label id="filecount">Выбрано файлов:<span class="param">0</span></label>
        <label id="warnManyFiles">Загружены будут только первые<span class="param"></span>из них!</label>
        <input id="file_input" type="file" multiple 
               accept=".png,.jpg,.jpeg,.gif" onchange="updateFileList(this.files)" />
        <a href="#" id="file_input2">Выбор фотографий...</a>
        <section id="profiles">
            <ul><?=buildProfiles($dictionary);?></ul>
        </section>
        <textarea id="note" name="<?=PARAM_ORDER_NOTE?>" rows="5" placeholder="Ваши пожелания" ></textarea>
        <label id="cost">Стоимость услуги:<span class="param">100<span>грн</label>
        <section id="promocode">
          <label for="promo">Для получения скидки введите номер Вашего купона:</label>
          <input id="promo" name="<?=PARAM_ORDER_PROMOCODE?>" size="5" pattern="[0-9]{5,5}" maxlength=5 placeholder="пять цифр"/>
        </section>  
        <input type="button" value="Отправить" onclick="onformsubmit()"/>
    </form>
</body>
</html>