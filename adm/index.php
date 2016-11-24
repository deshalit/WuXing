<?php
//error_reporting(E_ALL);
require_once ("dict.class.php");
require_once ("dictxml.php");
require_once ("calc.php");
require_once ("client.inc.php");
require_once ('group.class.php');

include_once("groupman.inc.php");
//include_once("param.inc.php");
include_once("dictxml.inc.php");

$requestData = new RequestData();

function fill_subject_data($no, SubjectData $data, $input)
{
    $data->id    = $input[PARAM_ID];
    $data->Name  = $input[PARAM_FIRSTNAME . $no];
    $data->elems = Array();
    $data->profiles = Array();
    foreach(Dictionary::$elemNames as $key=>$name) {
        //$inputKey = 'el' . $no . '_' . $key;
        $elval = $input[$key][$no];
        if ($elval) {
            $data->elems[$key] = doubleval($elval);
        }
    }
}

function fill_client_data($no, ClientData $data, $input) 
{
    fill_subject_data($no, $data, $input);
    $data->lastName = $input[PARAM_LASTNAME . $no];
    $data->email    = $input[PARAM_EMAIL . $no];
}
function fill_group_data(GroupManager $G, GroupData $data, $input)
{    
    fill_subject_data(2, $data, $input);
    //var_dump($G);
    $gr = $G->getGroupById($data->id);
        
    if (!empty($gr)) {
        $data->Name = $gr->name;    
    }
}
function calc_client(Dictionary $dict, SubjectData $subject, $profile_id, $prop_id) {
    $subject->profiles[$profile_id][$prop_id] = calculate_prop($dict, $subject->elems, $prop_id);
}

function analyze_params(Dictionary $dict, GroupManager $G, RequestData $data, $input) 
{
    $data->mode = empty($input[PARAM_MODE]) ? MODE_DEFAULT : $input[PARAM_MODE];
    //var_dump($data);
    fill_client_data(1, $data->client1, $input);
    if ($data->mode == MODE_TWO_CLIENTS) {
        fill_client_data(2, $data->client2, $input);
    } elseif ($data->mode == MODE_CLIENT_GROUP) {
        if (empty($data->group)) {
           $data->group = new GroupData();   
        }    
        fill_group_data($G, $data->group, $input);
    }
    $data->profiles = Array();    
    foreach(array_keys($input['prof']) as $profile_id) {       
        $data->profiles[] = $profile_id;
        foreach ($dict->get_profile_properties($profile_id) as $prop_id) {
            //var_dump($data->client1);
            calc_client($dict, $data->client1, $profile_id, $prop_id); 
            
            if ($data->mode == MODE_TWO_CLIENTS) {
               calc_client($dict, $data->client2, $profile_id, $prop_id); 
            } elseif ($data->mode == MODE_CLIENT_GROUP) {
               calc_client($dict, $data->group, $profile_id, $prop_id);
            }
        }    
        //}
    }    
    $data->complete = true;
    //var_dump($data);
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    analyze_params($dictionary, $GroupManager, $requestData, $_POST);
    //var_dump($mainData);
    //exit;
}


    include "calctest.php";

