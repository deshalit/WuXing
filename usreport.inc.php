<?
require_once('usreportman.php');
require_once('usreportXMLloader.php');
include_once('dictxml.inc.php');

if (!$userReportManager) {
    $userReportManager = new UserReportManager();
    $userReportManager->loader = new UserReportXMLLoader($dictionary);
}

//$report = $userReportManager->loadReport($hash);