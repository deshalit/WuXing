<?php
/**
 * Created by PhpStorm.
 * User: BOSS
 * Date: 01.10.2016
 * Time: 16:46
 */
require_once ("dict.inc.php");

const S_ERROR_INVALID_SUM = 'Sum does not equal to 1';

const CALC_PRECISION = 3;

function validate_elements($elems) {
    $sum = 1.0;
    foreach ($elems as $value) {
        $sum -= abs($value);
        //echo 'Sum = ' . $sum . '<br>';
    }
//    print_r ($elems);
    return round(abs($sum), CALC_PRECISION) == 0.0;
}

function calculate_prop(Dictionary $dict, $elems, $prop_id)
{
   $result = 0.0;
   if (!validate_elements($elems)) {
       throw new ErrorException( S_ERROR_INVALID_SUM . ': ' . $elems);
   }
   foreach ($elems as $id => $value) {
       //var_dump($dict->basetypes[$prop_id]);
       //var_dump($value);
       $result += $dict->basetypes[$prop_id][$id] * $value;
   }
   return round($result, CALC_PRECISION);
}