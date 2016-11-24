<?php
require_once ("../dict.class.php");
//require_once("order.const.php");
//require_once("order.class.php");
//require_once("orderreader.php");
require_once("report.class.php");
include_once ("../dictXML.inc.php");
include_once ("../calc.inc.php");

class ReportWriter
{
    const DB_NAME = 'wuxing';
    const DB_USER = 'wx_admin';
    const DB_PASS = '';
    const DB_HOST = 'localhost';

    const QUERY_ADDREPORT = 'INSERT INTO reports (regdate, id_order, name, lastname, email, note, method, elems) VALUES (NOW(), :ORDER, :FNAME, :LNAME, :EMAIL, :NOTE, :METHOD, :MASK)';
    const QUERY_ADDOPTION = 'INSERT INTO report_option (id_report, type, id_option) VALUES (:RID, :TYPE, :VALUE)';
    const QUERY_ADDXTEXT  = 'INSERT INTO textdata (type, data, id_report, id_option) VALUES (:TYPE, :TEXT, :RID, :OPTID)';

    private $dictionary;
    private $calculator;
    private $conn = NULL;

    public function __construct(Dictionary $dict, Calculator $calc)
    {
        //$this->orderReader = $orderReader;
        $this->dictionary = $dict;
        $this->calculator = $calc;
    }

    public function analyzeParams()
    {   $report = null;
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
                $els = $_POST[Report::PARAM_ELEMS];  // elem[0][id]=E, elem[0][data]=0.5
                $report->elems = Array();
                foreach ($els as $el) {
                    $report->elems[ $el['id'] ] = floatval($el['data']);
                }
            }
            if (!empty($_POST[Report::PARAM_METHOD])) {
                $report->method = $_POST[Report::PARAM_METHOD];
            }
            if (!empty($_POST[Report::PARAM_PROFILES])) {
                $report->data = Array();
                foreach ($_POST[Report::PARAM_PROFILES] as $pid) {
                    $report->data[$pid] = $this->calculator->getCalcPropList($report->elems, $pid, $report->method);
                }
            }
            if (!empty($_POST[Report::PARAM_RISK])) {
                $report->risk = $_POST[Report::PARAM_RISK];
            }
            if (!empty($_POST[Report::PARAM_XCOMMENTS])) {
                $report->xComments = $_POST[Report::PARAM_XCOMMENTS];
            }
            if (!empty($_POST[Report::PARAM_XRISK])) {
                $report->xRisk = $_POST[Report::PARAM_XRISK];
            }
        }
        /*
        $this->order = $this->reportReader->getOrderById($id);
        if (!$this->order) {
            die("Error reading information");
        }
        */

        return $report;
    }
    protected function checkConnection()
    {
        $res = true;
        if (!$this->conn) {
            $res = true;
            $dsn = 'mysql:dbname=' . self::DB_NAME . ';host=' . self::DB_HOST;
            $user = self::DB_USER;
            $pass = self::DB_PASS;
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

    protected function doWriteReport(Report $report)
    {
        try {
            $stmt = $this->conn->prepare(self::QUERY_ADDREPORT);
            $stmt->bindParam(":FNAME", $report->firstName, PDO::PARAM_STR);
            $stmt->bindParam(":LNAME", $report->lastName, PDO::PARAM_STR);
            $stmt->bindParam(":EMAIL", $report->email, PDO::PARAM_STR);
            $stmt->bindParam(":METHOD", $report->method, PDO::PARAM_INT);
            $stmt->bindParam(":ORDER", $report->orderId, PDO::PARAM_INT);
            $stmt->bindParam(":MASK", $report->getMask(), PDO::PARAM_INT);
            $stmt->bindParam(":NOTE", $report->note, PDO::PARAM_STR);

            $this->conn->beginTransaction();

            $stmt->execute();
            $report->id = $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(self::QUERY_ADDOPTION);
            $stmt->bindParam(":RID", $report->id, PDO::PARAM_INT);
            // store profiles
            $type = 'prof';
            $stmt->bindParam(":TYPE", $type, PDO::PARAM_STR);
            foreach (array_keys($report->data) as $key) {
                $stmt->bindParam(":VALUE", $key, PDO::PARAM_INT);
                $stmt->execute();
            }
            // store risk
            $type = 'risk';
            $stmt->bindParam(":TYPE", $type, PDO::PARAM_STR);
            foreach ($report->risk as $key) {
                $stmt->bindParam(":VALUE", $key, PDO::PARAM_INT);
                $stmt->execute();
            }
            if ($report->hasExtraText()) {
                $stmt = $this->conn->prepare(self::QUERY_ADDXTEXT);
                $stmt->bindParam(":RID", $report->id, PDO::PARAM_INT);

                if (is_array($report->xComments) and count($report->xComments) > 0) {
                    // store xComments
                    $type = 'comm';
                    $stmt->bindParam(":TYPE", $type, PDO::PARAM_STR);
                    foreach ($report->xComments as $cID => $text) {
                        $stmt->bindParam(":OPTID", $cID, PDO::PARAM_INT);
                        $stmt->bindParam(":TEXT", $text, PDO::PARAM_STR);
                        $stmt->execute();
                    }
                }
                if (is_array($report->xRisk) and count($report->xRisk) > 0) {
                    // store xRisk
                    $type = 'risk';
                    $stmt->bindParam(":TYPE", $type, PDO::PARAM_STR);
                    foreach ($report->xRisk as $rID => $text) {
                        $stmt->bindParam(":OPTID", $rID, PDO::PARAM_INT);
                        $stmt->bindParam(":TEXT", $text, PDO::PARAM_STR);
                        $stmt->execute();
                    }
                }
            }
            $this->conn->commit();
            return $report->id;
        } catch (PDOException $e) {
            //$res = NULL;
            $res = 0; //echo 'error: ' . $e->getMessage(); //echo $res;
            if ($this->conn->InTransaction()) {
                $this->conn->rollBack();
            }
        }
        return false;
    }
    public function writeReport(Report $report) {
        if ($this->checkConnection()) {
            return $this->doWriteReport($report);
        }
        return false;
    }
}

if (empty($reportWriter)) {
    $reportWriter = new ReportWriter($dictionary, $calculator);
}
$report = $reportWriter->analyzeParams();
echo $reportWriter->writeReport($report);