<?php
require_once('orderreader.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $fname = $_GET['id'];
    if (empty($fname)) {
        throw new RuntimeException('Неверно заданы параметры'); 
    }
}  
if (empty($orderReader)) {
    $orderReader = new OrderReader();  
}    
if ($data = $orderReader->getImage($fname)) {
    header('Content-Type: image/' . $data[2] . "; " . 'Content-Length: ' . $data[0]);
    echo $data[1];
}    