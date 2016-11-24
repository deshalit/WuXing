<?php
/**
 * Created by PhpStorm.
 * User: BOSS
 * Date: 01.10.2016
 * Time: 16:46
 */
require_once ("../dict.class.php");

class Calculator {
    
    const S_ERROR_INVALID_SUM = 'Sum does not equal to 1';
    const CALC_PRECISION = 3;
    const DEFAULT_METHOD = 1;
    
    private $method = self::DEFAULT_METHOD;
    public $dict;
    
    public function __construct(Dictionary $d, $method = self::DEFAULT_METHOD) {
        $this->dict = $d;
        $this->method = $method;
    }    
    
    public static function validate($elems) {
        $sum = 1.0;
        foreach ($elems as $value) {
            $sum -= abs($value);
            //echo 'Sum = ' . $sum . '<br/>';
        }
        //print_r ($elems);
        //echo 'round(abs($sum), CALC_PRECISION) = ' . round(abs($sum), CALC_PRECISION) . '<br/   >';
        return round(abs($sum), self::CALC_PRECISION) == 0.0;
    }

    private function _calc($elems, $prop_id, $method) {
        $result = 0.0;
        foreach ($elems as $id => $value) {
           //var_dump($dict->basetypes[$prop_id]);
           //var_dump($value);
           $result += $this->dict->basetypes[$prop_id][$id] * $value;
       }
       return round($result, self::CALC_PRECISION);
    }    
    
    public function calcProp($elems, $prop_id, $method = 0) {
       if (!$this->validate($elems)) {
           throw new ErrorException( self::S_ERROR_INVALID_SUM . ': ' . $elems);
       }
       if ($method == 0) {
           $method = $this->method;
       }
       return $this->_calc($elems, $prop_id, $method);
    }
    
    public function getCalcPropList($elems, $profileId, $method = 0) {
        if (!$this->validate($elems)) {
           throw new ErrorException( self::S_ERROR_INVALID_SUM . ': ' . $elems);
        }
        $res = [];
        if ($method == 0) {
            $method = $this->method;
        }
        foreach ($this->dict->get_profile_properties($profileId) as $propId) {
            $res[$propId] = $this->_calc($elems, $propId, $method);
        }    
        return $res;    
    }    
}    