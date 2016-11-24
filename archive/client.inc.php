<?php
/**
 * Created by PhpStorm.
 * User: BOSS
 * Date: 01.10.2016
 * Time: 20:34
 */
require_once "dict.class.php";

const MODE_ONE_CLIENT   = "C1";
const MODE_TWO_CLIENTS  = "C2";
const MODE_CLIENT_GROUP = "CG";
const MODE_DEFAULT = MODE_ONE_CLIENT;

const TYPE_UNKNOWN = 0;
const TYPE_CLIENT  = 1;
const TYPE_GROUP   = 2;

class SubjectData
{
    public $id = 0;
    public $Name = '';
    public $elems = NULL;
    public $profiles = NULL;
    public $type = TYPE_UNKNOWN;
    public function __construct($type, $name = '', $id = 0) {
        $this->id = $id;
        $this->Name = $name;
        $this->type = $type;
        $this->profiles = Array();
        $this->elems = Array();
    }
}
class ClientData extends SubjectData
{
   public $lastName = '';
   public $email = '';

    public function __construct() {
        parent::__construct(TYPE_CLIENT);
    }
}
class GroupData extends SubjectData
{
    public function __construct($name = 'unknown') {
        parent::__construct(TYPE_GROUP, $name);
    }
}
class RequestData 
{   
   public $complete = false; 
   public $profiles = NULL;
   public $client1 = NULL;
   public $client2 = NULL;
   public $group = NULL;
   public $mode = MODE_DEFAULT;
   
   public function __construct() {
        $this->client1 = new ClientData();
        $this->client2 = new ClientData();
        $this->group   = NULL;

        $this->complete = false;
        $this->profiles = Array();
        $this->mode = MODE_DEFAULT;
   }
   public function subjectCount() {
       return ($this->mode == MODE_ONE_CLIENT) ? 1 : 2;
   }
/*
   public function reset_profile(Dictionary $dict, $profile_id) {       
   
       if (!in_array($profile_id, $this->profiles)) {  
          $this->profiles[] = $profile_id;
       }     
       $this->client1->profiles[$profile_id] = array_fill_keys( $dict->get_profile_properties($profile_id), 0);
       $this->client2->profiles[$profile_id] = array_fill_keys( $dict->get_profile_properties($profile_id), 0);
       
           // Resetting the completion flag
       $this->complete = false;  
   }
*/
}