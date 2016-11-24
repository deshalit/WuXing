<?php
require_once('orderreader.php');
require_once('../order.const.php');
if (empty($orderReader)) {
    $orderReader = new OrderReader();
}   
$dateSince = OrderReader::DEFAULT_DATE;
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET[PARAM_ORDER_DATE])) {
        $dateSince = $_GET[PARAM_ORDER_DATE];  
    }
}    
$xml = $orderReader->getNewOrdersXML($dateSince);
if ($xml) {
    header('Content-Type: application/xml; charset=utf-8');
    echo $xml;
}    