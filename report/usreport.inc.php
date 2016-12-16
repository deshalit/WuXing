<?php
$classRoot = $_SERVER['DOCUMENT_ROOT'] . '/class/';
require_once($classRoot . 'report/userreportmanager.class.php');
require_once($classRoot . 'report/userreportxmlloader.class.php');
include_once($classRoot . 'dictionary/dictxml.inc.php');

if (!$userReportManager) {
    $userReportManager = new UserReportManager();
    $userReportManager->loader = new UserReportXMLLoader($dictionary);
}

//$report = $userReportManager->loadReport($hash);