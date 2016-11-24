<?php
require_once('orderreader.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_GET['id'];
    if (empty($fname)) {
        throw new RuntimeException('Неверно заданы параметры'); 
    }
    if (empty($orderReader)) {
        $orderReader = new OrderReader();  
    }    
    $res = $orderReader->deletePhoto($fname);
    if ($res) {
        echo 'Фото удалено';
    }    
}  
/*

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Фото</title>
    <style>
       img {
         border-width: 1px;         
       }    
       button { display: block; cursor: pointer; }
    </style>
</head>
<body>
    <form action="deletephoto.php?id=<?=$fname?>" method="POST">
    <button type="submit">Удалить</button>
    <img src="getimage.php?id=<?=$fname?>" />
    </form>
</body>
</html>
*/