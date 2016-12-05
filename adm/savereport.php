<?php
require_once ("../dict.class.php");
//require_once("order.const.php");
//require_once("order.class.php");
//require_once("orderreader.php");
require_once("report.class.php");
require_once("reportwriter.class.php");
require_once("../usreportXML.php");
include_once ("../dictXML.inc.php");
include_once ("calc.inc.php");

function analyzeParams(Calculator $calculator) {
    $report = null;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $report = new Report();
        if (!empty($_POST[Report::PARAM_ID])) {
            $report->id = $_POST[Report::PARAM_ID];
        }
        if (!empty($_POST[Report::PARAM_ORDERID])) {
            $report->orderId = $_POST[Report::PARAM_ORDERID];
        }
        if (!empty($_POST[Report::PARAM_FIRSTNAME])) {
            $report->firstName = $_POST[Report::PARAM_FIRSTNAME];
        }
        if (!empty($_POST[Report::PARAM_LASTNAME])) {
            $report->lastName = $_POST[Report::PARAM_LASTNAME];
        }
        if (!empty($_POST[Report::PARAM_EMAIL])) {
            $report->email = $_POST[Report::PARAM_EMAIL];
        }
        if (!empty($_POST[Report::PARAM_NOTES])) {
            $report->note = $_POST[Report::PARAM_NOTES];
        }
        if (!empty($_POST[Report::PARAM_ELEMS])) {
            $report->elems = $_POST[Report::PARAM_ELEMS];
            foreach ($report->elems as $id=>$value) {
                $report->elems[$id] = floatval($value);
            }
/*
            $els = $_POST[Report::PARAM_ELEMS];  // elem[0][id]=E, elem[0][data]=0.5
            $report->elems = Array();
            foreach ($els as $el) {
                $report->elems[ $el['id'] ] = floatval($el['data']);
            }
*/
        }
        if (!empty($_POST[Report::PARAM_METHOD])) {
            $report->method = $_POST[Report::PARAM_METHOD];
        }
        if (!empty($_POST[Report::PARAM_PROFILES])) {
            $report->data = Array();
            $plist = explode(',', $_POST[Report::PARAM_PROFILES]);
            foreach($plist as $pid) {
                $report->data[$pid] = $calculator->getCalcPropList($report->elems, $pid, $report->method);
            }
/*
            
            foreach ($_POST[Report::PARAM_PROFILES] as $pid) {
                $report->data[$pid] = $calculator->getCalcPropList($report->elems, $pid, $report->method);
            }
*/
        }
        if (!empty($_POST[Report::PARAM_RISK])) {
            $report->risk = explode(',', $_POST[Report::PARAM_RISK]);
        }
        if (!empty($_POST[Report::PARAM_XCOMMENTS])) {
            $report->xComments = $_POST[Report::PARAM_XCOMMENTS];
/*
            $els = $_POST[Report::PARAM_XCOMMENTS];  // xcomm[0][id]=2, xcomm[0][data]="afwefw"            $report->elems = Array();
            foreach ($els as $el) {
                $report->xComments[ $el['id'] ] = $el['data'];
            }
*/
        }
        if (!empty($_POST[Report::PARAM_XRISK])) {
            $report->xRisk = $_POST[Report::PARAM_XRISK];
/*
            $els = $_POST[Report::PARAM_XRISK];  // xrisk[0][id]=2, xrisk[0][data]="afwefw"            $report->elems = Array();
            foreach ($els as $el) {
                $report->xRisk[ $el['id'] ] = $el['data'];
            }
*/
        }    
    }
    return $report;
}

if (empty($reportWriter)) {
    $reportWriter = new ReportWriter($dictionary, $calculator);
}
if ($report = analyzeParams($calculator)) {
    //var_dump($report); //die();
    $hash = $reportWriter->writeReport($report);
    if ($hash) {
            header('Location: http://' . $_SERVER['HTTP_HOST'] . '/rpt.php?code=' . $hash);
    }
}