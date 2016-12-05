<?php
class ReportWriter
{
    const DB_NAME = 'wuxing';
    const DB_USER = 'wx_admin';
    const DB_PASS = '';
    const DB_HOST = 'localhost';

    const QUERY_ADDREPORT = 'INSERT INTO reports (regdate, id_order, name, lastname, email, note, method, elems) VALUES (NOW(), :ORDER, :FNAME, :LNAME, :EMAIL, :NOTE, :METHOD, :MASK)';
    const QUERY_ADDOPTION = 'INSERT INTO report_option (id_report, type, id_option) VALUES (:RID, :TYPE, :VALUE)';
    const QUERY_ADDELEM   = 'INSERT INTO report_elem (id_report, id_elem, value) VALUES (:RID, :EID, :VALUE)';
    const QUERY_ADDXTEXT  = 'INSERT INTO textdata (type, data, id_report, id_option) VALUES (:TYPE, :TEXT, :RID, :OPTID)';
    const QUERY_UPDREPORT = 'UPDATE reports SET sentdate = :DATE, hash = :HASH WHERE id = :ID';

    private $dictionary;
    private $calculator;
    private $conn = NULL;

    public function __construct(Dictionary $dict, Calculator $calc)
    {
        //$this->orderReader = $orderReader;
        $this->dictionary = $dict;
        $this->calculator = $calc;
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

            $stmt = $this->conn->prepare(self::QUERY_ADDELEM);
            $stmt->bindParam(":RID", $report->id, PDO::PARAM_INT);
            // store elements
            $keys = array_keys(Dictionary::$elemHash);
            foreach ($report->elems as $key=>$value) {
                $el = array_search($key, $keys) + 1;
                $stmt->bindParam(":EID", $el, PDO::PARAM_INT);
                $stmt->bindParam(":VALUE", intval($value * 100), PDO::PARAM_INT);
                $stmt->execute();
            }

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
                    $type = 'prof';
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
            $res = 0; echo 'error: ' . $e->getMessage(); //echo $res;
            if ($this->conn->InTransaction()) {
                $this->conn->rollBack();
            }
        }
        return false;
    }
    private function writeHash(Report $report, $hash, $sent = false) {
        try {
            $stmt = $this->conn->prepare(self::QUERY_UPDREPORT);
            $stmt->bindParam(":ID", $report->id, PDO::PARAM_INT);
            $stmt->bindParam(":HASH", $hash, PDO::PARAM_STR);
            if ($sent) {
                date_default_timezone_set('Europe/Kiev');
                $now = date('Y-m-d H:i:s');
            } else $now = null;
            $stmt->bindParam(":DATE", $now, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }
    public function writeReport(Report $report) {
        if ($this->checkConnection()) {
            //  echo 'connection Ok' . '<br/>';
            if ($this->doWriteReport($report)) {
                //echo 'doWriteReport Ok' . '<br/>';
                $hash = $this->createHash($report);
                $uploaded = $this->upload($report, $hash);
                $this->writeHash($report, $hash, $uploaded);
                return $hash;
            }
        }
        return false;
    }
    public function createHash(Report $report) {
        return sha1($report->targetName . $report->id . rand(1, 100));
    }
    public function upload(Report $report, $hash) {
        $fileName = UserReportXML::getfileName($hash);
        $xml = new DomDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $node = $xml->createElement(UserReportXML::NODE_ROOT);
        $root = $xml->appendChild($node);

        $node = $xml->createElement(UserReportXML::NODE_ORDERID);
        $node->nodeValue = $report->orderId;
        $root->appendChild($node);

        $node = $xml->createElement(UserReportXML::NODE_ID);
        $node->nodeValue = $report->id;
        $root->appendChild($node);

        $node = $xml->createElement(UserReportXML::NODE_FIRSTNAME);
        $node->nodeValue = $report->firstName;
        $root->appendChild($node);

        $node = $xml->createElement(UserReportXML::NODE_LASTNAME);
        $node->nodeValue = $report->lastName;
        $root->appendChild($node);

        $node = $xml->createElement(UserReportXML::NODE_TARGETNAME);
        $node->nodeValue = $report->targetName;
        $root->appendChild($node);
        
        $node = $xml->createElement(UserReportXML::NODE_USERNOTES);
        $node->nodeValue = $report->orderNote;
        $root->appendChild($node);

        $node = $xml->createElement(UserReportXML::NODE_EMAIL);
        $node->nodeValue = $report->email;
        $root->appendChild($node);

        $node = $xml->createElement(UserReportXML::NODE_MASK);
        $node->nodeValue = $report->getPrevailMask();
        $root->appendChild($node);

        $node = $xml->createElement(UserReportXML::NODE_PROFILES);
        $root->appendChild($node);
        foreach($report->data as $profId=>$props) {
            $childNode = $xml->createElement(UserReportXML::NODE_PROFILE);
            $childNode->setAttribute(UserReportXML::ATTR_ID, $profId);
            $node->appendChild($childNode);
            if (is_array($report->xComments) and array_key_exists($profId, $report->xComments)) 
            {
                $pNode = $xml->createElement(UserReportXML::NODE_TEXT);
                $pNode->nodeValue = $report->xComments[$profId];
                $childNode->appendChild($pNode);
            }    
            foreach($props as $pid=>$pvalue) {
                $pNode = $xml->createElement(UserReportXML::NODE_PROPERTY);
                $childNode->appendChild($pNode); 
                $pNode->setAttribute(UserReportXML::ATTR_ID, $pid);
                $pNode->nodeValue = $pvalue;
            }
        }
        $node = $xml->createElement(UserReportXML::NODE_RISK);
        $root->appendChild($node);
        foreach($report->risk as $rid) {
            $childNode = $xml->createElement(UserReportXML::NODE_RISKITEM);
            $childNode->setAttribute(UserReportXML::ATTR_ID, $rid);
            $node->appendChild($childNode);
            if (is_array($report->xRisk) and array_key_exists($rid, $report->xRisk)) 
            {
                $pNode = $xml->createElement(UserReportXML::NODE_TEXT);
                $pNode->nodeValue = $report->xRisk[$rid];
                $childNode->appendChild($pNode);
            }    
        }
        $xml->save($fileName);
        return true;
    }
}