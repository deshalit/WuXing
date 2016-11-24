<?php
include_once ("calc.inc.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['prof'])) {
        $profs = $_GET['prof']; // prof[]=1&prof=5
        $elems = $_GET['elem']; // elem[F]=0.3&elem[T]=0.7
        foreach($elems as $id=>$val) {
            $elems[$id] = floatval($val);    
        }    
        $profList = [];
        foreach($profs as $pid) {
            $propList = $calculator->getCalcPropList($elems, $pid);
            array_push($profList, '{"id": ' . $pid . ', "data": [[' . implode(',', $propList) . ']]}');
        }    
        echo '[' . implode(',', $profList) . ']';
    }
}
