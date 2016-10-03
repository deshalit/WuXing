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

   public function reset_profile(Dictionary $dict, $profile_id) {
      //echo 'Profile_id: '.$profile_id.'<br>';
      //echo 'Profile_id: '.$profile_id.'<br>';
      //print_r( array_fill_keys( array_values( $dict->profiles[$profile_id][1]),0 ));
      $this->profiles[$profile_id] = array_fill_keys( array_values( $dict->profiles[$profile_id][1] ), 0);
       $this->complete = false;
   }
}