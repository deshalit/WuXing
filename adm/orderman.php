<?php
require_once('ordermanager.class.php');
if (empty($orderManager)) {
    $orderManager = new OrderManager();
}   

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    switch ($_GET['action']) {
	case 'news':
	    $xml = $orderManager->getNewOrdersXML();
	    if ($xml) {
            header('Content-Type: application/xml; charset=utf-8');
            echo $xml;
        }    
	    break;
    case 'delphoto':
        $fname = $_GET['id'];
        if (empty($fname)) {
            throw new RuntimeException('Неверно заданы параметры'); 
        }
        $res = $orderManager->deletePhoto($fname);
        if ($res) {
            echo 'OK';
        } else echo 'error';
        break;
    case 'delorder':
        $id = $_GET['id'];
        if (empty($id)) {
            throw new RuntimeException('Неверно заданы параметры'); 
        }
        $res = $orderManager->deleteOrder($id);
        if ($res) {
            echo 'OK';
        } else echo 'Error: ' . $res;
        break;
    case 'getphoto':
        $fname = $_GET['id'];
        if (empty($fname)) {
            throw new RuntimeException('Неверно заданы параметры'); 
        }
        if ($data = $orderManager->getImage($fname)) {
            header('Content-Type: image/' . $data[2] . "; " . 'Content-Length: ' . $data[0]);
            echo $data[1];
        }    
        break;    
    case 'updstatus':
        $list = explode(',', $_GET['id']);
        if (empty($list) or (!is_array($list))) {
            throw new RuntimeException('Неверно заданы параметры'); 
        }
        $res = $orderManager->updateStatus($list);
        if ($res) {
            echo 'OK';
        } else echo 'Error: ' . $res;
        break;
    }
}