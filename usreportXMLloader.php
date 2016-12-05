<?php
require_once ('usreport.class.php');
require_once ('usreportman.php');
require_once ('usreportXML.php');
include_once('dictxml.inc.php');

class UserReportXMLLoader extends UserReportLoader {
    
    const S_EXCEPTION_FILE_NOT_EXISTS = 'File does not exist';
    const S_EXCEPTION_FILE_CORRUPTED = 'File is corrupted or locked';
    
    public function __construct(Dictionary $dict) {
        $this->dictionary = $dict;
    }
    protected function doLoad($hash) {
        $fileName = UserReportXML::getFileName($hash);
        if (!file_exists($fileName)) {
        //echo $this->fileName;
            throw new ErrorException(self::S_EXCEPTION_FILE_NOT_EXISTS);
        }
        $xml = new DomDocument('1.0', 'utf-8');
        if (!$xml->load($fileName)) {
            unset($xml);
            throw new ErrorException(self::S_EXCEPTION_FILE_CORRUPTED);
        }
        $report = new UserReport($this->dictionary);
        $report->firstName = $xml->getElementsByTagName(UserReportXML::NODE_FIRSTNAME)->item(0)->nodeValue;
        $report->lastName = $xml->getElementsByTagName(UserReportXML::NODE_LASTNAME)->item(0)->nodeValue;
        $report->targetName = $xml->getElementsByTagName(UserReportXML::NODE_TARGETNAME)->item(0)->nodeValue;
        $report->email = $xml->getElementsByTagName(UserReportXML::NODE_EMAIL)->item(0)->nodeValue;
        $report->id = $xml->getElementsByTagName(UserReportXML::NODE_ID)->item(0)->nodeValue;
        $report->orderId = $xml->getElementsByTagName(UserReportXML::NODE_ORDERID)->item(0)->nodeValue;
        $report->mask = $xml->getElementsByTagName(UserReportXML::NODE_MASK)->item(0)->nodeValue;
        $report->orderNote = $xml->getElementsByTagName(UserReportXML::NODE_USERNOTES)->item(0)->nodeValue;
        //$report->orderNote = $xml->getElementsByTagName(UserReportXML::NODE_ORDERNOTE)[0];
        $nodes = $xml->getElementsByTagName(UserReportXML::NODE_RISKITEM);
        $report->risk = array();
        foreach($nodes as $node) {
            $id = $node->attributes->getNamedItem(UserReportXML::ATTR_ID)->value;  // id
            $text = '';
            $childNodes = $node->getElementsByTagName(UserReportXML::NODE_TEXT);
            if (($childNodes !== null) and ($childNodes->length > 0)) {
                $text = $childNodes->item(0)->nodeValue;
            }
            $report->risk[$id] = $text;
        }
        $nodes = $xml->getElementsByTagName(UserReportXML::NODE_PROFILE);
        if (!$nodes) {
            throw new ErrorException(self::S_EXCEPTION_FILE_CORRUPTED);
        }    
        $report->data = array();
        foreach($nodes as $node) {
            $id = $node->attributes->getNamedItem(UserReportXML::ATTR_ID)->value;  // id
            $profile = new ProfileInfo();
            $report->data[$id] = $profile;
            $childNodes = $node->getElementsByTagName(UserReportXML::NODE_TEXT);
            if (($childNodes !== null) and ($childNodes->length > 0)) {
                $profile->text = $childNodes->item(0)->nodeValue;
            }
            $childNodes = $node->getElementsByTagName(UserReportXML::NODE_PROPERTY);
            if (!$childNodes) {
                throw new ErrorException(self::S_EXCEPTION_FILE_CORRUPTED);
            }
            $profile->props = array();
            foreach ($childNodes as $propNode) {
                $id = $propNode->attributes->getNamedItem(UserReportXML::ATTR_ID)->value;  // id
                $profile->props[$id] = floatval($propNode->nodeValue);   
            }    
        }
        return $report;
    } 
}