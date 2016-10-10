<?php
//error_reporting(E_ALL);
require_once ("dict.inc.php");
require_once ("dictloaderxml.php");
require_once ("calc.php");
require_once ("client.inc.php");

$dictionary = new Dictionary();
$loader = new DictLoaderXML('dict.xml');
$dictionary->Load($loader);

$mainData = new RequestData();

function fill_client_data($no, ClientData $data, $input) 
{
    $data->first_name = $input['fname' . $no];
    $data->last_name  = $input['lname' . $no];
    $data->email      = $input['email' . $no];
    $data->elems = Array();
    $data->profiles = Array();
    foreach(Dictionary::$elemNames as $key=>$name) {
        $elval = $input['el' . $no . '_' . $key];
        if ($elval) {
             $data->elems[$key] = doubleval($input['el' . $no . '_' . $key]);   
        }     
    }
}

function calc_client(Dictionary $dict, ClientData $client, $profile_id, $prop_id) {
    $client->profiles[$profile_id][$prop_id] = calculate_prop($dict, $client->elems, $prop_id);
}

function analyze_params(Dictionary $dict, RequestData $data, $input) 
{
    $data->mode = empty($input['mode']) ? MODE_DEFAULT : $input['mode']; 
    fill_client_data(1, $data->client1, $input);
    if ($data->mode == MODE_TWO) { fill_client_data(2, $data->client2, $input); }    
    $data->profiles = Array();    
    foreach ($dict->profiles as $profile_id=>$props) {
        if (isset($input['prof' . $profile_id])) {  // prof1=on&prof5=on...
            $data->profiles[] = $profile_id;
            foreach ($dict->get_profile_properties($profile_id) as $prop_id) {
                calc_client($dict, $data->client1, $profile_id, $prop_id);               
                if ($data->mode == MODE_TWO) {
                   calc_client($dict, $data->client2, $profile_id, $prop_id); 
                }
            }    
        }
    }    
    $data->complete = true;
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    analyze_params($dictionary, $mainData, $_POST);
}


    include "calctest.php";

