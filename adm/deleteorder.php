<?php
require_once('orderreader.php');
require_once('../order.const.php');

if (empty($orderReader)) {
    $orderReader = new OrderReader();
}   
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET[PARAM_ORDER_ID])) {
        $id = $_GET[PARAM_ORDER_ID];  
    }
}    
$res = $orderReader->deleteOrder($id);
if ($res) {
    echo 'OK';
}    