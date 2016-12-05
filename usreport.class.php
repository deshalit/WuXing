<?php
//require_once("order.class.php");
require_once('dict.class.php');
   
class ProfileInfo
{
    public $text = '';
    public $props = [];
}    
/**
 * Class UserReport
 */
class UserReport
{
    public $id = 0;
    public $orderId = 0;
    public $mask = 0;
    public $risk = [];
    public $firstName = '';
    public $lastName = '';
    public $targetName = '';
    public $data = [];
    public $orderNote = '';
    public $hash = '';
    public $email = '';
    
    private $dictionary;
    
    public function __construct(Dictionary $dict) {
        $this->dictionary = $dict;    
    }    
    
    public function generateChartData() {
        $res = [];
        foreach ($this->data as $id=>$profile) {
            $s = '{id:' . $id . ',data:[[';
            $names = array();
            $values = array();
            foreach($profile->props as $pid=>$value) {
                array_push($names, $this->dictionary->get_property_name($pid));
                array_push($values, $value);
            }    
            $s .= implode(',', $values) . ']], names:["' . implode('","', $names) . '"]}' . "\n";
            array_push($res, $s);   
        }    
        return '[' . implode(',', $res) . ']';
    }    
    
    public function getConstitution() {
        $res = array();
        foreach(Dictionary::$elemHash as $id=>$value) {
            if (($this->mask & $value) == $value) {
                array_push($res, Dictionary::$elemNames[$id]); 
            }    
        }    
        return implode('+', $res);
    }    
    
    public function getProfileName($id) {
        return $this->dictionary->get_profile_name($id);
    }    
    public function getProfileText($id) {
        $profile = $this->data[$id];
        $text = '';
        if ($profile !== null) {
            if (is_string($profile->text) and (mb_strlen($profile->text) > 0)) {
                $text = $profile->text;
            } else {
                $text = $this->dictionary->comments[$id][$this->mask];
            }        
        }    
        return $text;
    }    
    public function getRiskCount() {
        $res = 0;
        if (is_array($this->risk)) {
            $res = count($this->risk);
        }
        return $res;
    }

    public function getRiskName($id) {
        return $this->dictionary->risk[$id][0];
    }
    public function getRiskText($id) {
        $text = $this->risk[$id];
        if ($text == '') {
            $text = $this->dictionary->risk[$id][1][$this->mask];
        }
        return $text;
    }
}