<?php
include_once('usreport.inc.php');
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
   $hash = $_GET['order'];    
   $report = $userReportManager->loadReport($hash);
   var_dump($report);
}    