<?php
require_once("../order.class.php");

/**
 * Class Report
 */
class Report
{   const DEFAULT_METHOD = 1;

    const PARAM_FIRSTNAME = 'fname';
    const PARAM_LASTNAME  = 'lname';
    const PARAM_EMAIL     = 'email';
    const PARAM_NOTES     = 'note';
    const PARAM_ORDERID   = 'order';
    const PARAM_ID        = 'id';
 //   const PARAM_DATE      = '';
    const PARAM_METHOD    = 'method';
    const PARAM_PROFILES  = 'prof';
    const PARAM_RISK      = 'risk';
    const PARAM_ELEMS     = 'elem';
    const PARAM_XCOMMENTS = 'xcomm';
    const PARAM_XRISK     = 'xrisk';

    public $id = 0;
    public $orderId = 0;
    public $elems = [];
    public $risk = [];
    public $firstName = '';
    public $lastName = '';
    public $targetName = '';
    public $email = '';
    public $data = [];
    public $orderNote = '';
    public $note = '';
    public $xComments = [];
    public $xRisk = [];
    public $method = self::DEFAULT_METHOD;

    public function __construct(Order $order = null) {
        if ($order) {
            $this->orderId = $order->id;
            $this->email   = $order->email;
            $this->firstName = $order->firstName;
            $this->lastName  = $order->lastName;
            $this->targetName = $order->targetName;
            $this->orderNote = $order->notes;
            foreach($order->profiles as $pid) {
                $this->data[$pid] = Array();
            }    
        }
    }
    public function getMask(){
        $res = 0;
        if (is_array($this->elems)) {
            $res =  Dictionary::get_elem_hash(array_keys($this->elems));
        }
        return $res;
    }
    public function getPrevailMask(){
        $res = 0;
        if (is_array($this->elems)) {
            $elems = array();
            $firstId = 0;
            $secondId = 0;
            $mostValue = 0.0;
            foreach ($this->elems as $eid=>$value) {
                if ($value > $mostValue) {
                    $mostValue = $value;
                    $firstId = $eid;
                }        
            }
            $mostValue = 0.0;
            foreach ($this->elems as $eid=>$value) {
                if ($eid === $firstId) continue; 
                if ($value > $mostValue) {
                    $mostValue = $value;
                    $secondId = $eid;
                }        
            } 
            array_push($elems, $firstId, $secondId);
            $res =  Dictionary::get_elem_hash($elems);
        }
        return $res;
    }
    public function hasExtraText() {
        return (is_array($this->xComments) and count($this->xComments) > 0) or
               (is_array($this->xRisk) and count($this->xRisk) > 0);
    }
}