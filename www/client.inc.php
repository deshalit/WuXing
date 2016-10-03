<?php
/**
 * Created by PhpStorm.
 * User: BOSS
 * Date: 01.10.2016
 * Time: 20:34
 */
require_once "dict.inc.php";

class ClientData {
   public $first_name = '';
   public $last_name = '';
   public $email = '';
   public $elems = Array();
   public $profiles = Array();
    public $complete = false;

   public function reset_profile(Dictionary $dict, $profile_id) 
   {       // Setting to zero all properties of the profile
       $this->profiles[$profile_id] = array_fill_keys( $dict->get_profile_properties($profile_id), 0);
           // Resetting the completion flag
       $this->complete = false;  
   }
}