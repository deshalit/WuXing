<?php
//error_reporting(E_ALL);
require_once ("dict.inc.php");
require_once ("dictloaderxml.php");
require_once ("calc.php");
require_once ("client.inc.php");

$dictionary = new Dictionary();
$loader = new DictLoaderXML('dict.xml');
$dictionary->Load($loader);

$mainData = new ClientData();

function analyze_params(Dictionary $dict, ClientData $data, $input)
{
    $data->first_name = $input['fname'];
    $data->last_name = $input['lname'];
    $data->email = $input['email'];
    $data->elems = Array();
    $el1 = $input['el1'];
    $el2 = $input['el2'];
    $ratio = doubleval($input['ratio']);
    $data->elems[$el1] = $ratio;
    $data->elems[$el2] = 1.0 - $ratio;
    foreach (array_keys($dict->profiles) as $id) {
        if (!empty($input['prof' . $id])) {  // prof1=on&prof5=on...
                $data->reset_profile($dict, $id);
                //print_r($data->profiles[$id]);
        }
    }
}

function calculate(Dictionary $dict, ClientData $data) {
    // profiles[profile_id] = [ property_id1 => value, property_id2 => value, property_id3 => value,... ]
    foreach ($data->profiles as $profile_id=>$props) {
        //echo 'Profile:' . $dict->profiles[$profile_id][0] . '<br/>';
        foreach (array_keys($props) as $prop_id) {
            //echo '     Property: ' . $dict->properties[$prop_id][0] . '<br/>';
            $data->profiles[$profile_id][$prop_id] = calculate_prop($dict, $data->elems, $prop_id);
        }
    }
    $data->complete = true;
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    analyze_params($dictionary, $mainData, $_POST);
    //print_r($mainData->profiles);
    //echo '-----------------------------------';
    calculate($dictionary, $mainData);
    //var_dump($mainData->profiles);
}
//} else {
    include "calctest.php";

/*
class Order {
    private $_time;
    private $_firstname = '';
    private $_lastname = '';
    private $_email = '';
    private $_complete = false;
    
}
*/