<?php
/**
 * Created by PhpStorm.
 * User: BOSS
 * Date: 01.10.2016
 * Time: 20:34
 */
require_once "dict.inc.php";

const MODE_ONE = "one";
const MODE_TWO = "two";
const MODE_DEFAULT = MODE_ONE; 

class ClientData 
{
   public $first_name = '';
   public $last_name = '';
   public $email = '';
   public $elems = Array();  
   public $profiles = Array();
}
    
class RequestData 
{   
   public $complete = false; 
   public $profiles = NULL;
   public $client1 = NULL;
   public $client2 = NULL;
   public $mode = MODE_DEFAULT;
   
   public function __construct() {
        $this->client1 = new ClientData();
        $this->client1->profiles = Array();
        
        $this->client2 = new ClientData();
        $this->client2->profiles = Array();
        
        $this->complete = false;
        $this->profiles = Array();
        $this->profiles = Array();
        $this->mode = MODE_DEFAULT;
   }
   
   public function reset_profile(Dictionary $dict, $profile_id) {       
   
       if (!in_array($profile_id, $this->profiles)) {  
          $this->profiles[] = $profile_id;
       }     
       $this->client1->profiles[$profile_id] = array_fill_keys( $dict->get_profile_properties($profile_id), 0);
       $this->client2->profiles[$profile_id] = array_fill_keys( $dict->get_profile_properties($profile_id), 0);
       
           // Resetting the completion flag
       $this->complete = false;  
   }
}