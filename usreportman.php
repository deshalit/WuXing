<?php
require_once ('usreport.class.php');

abstract class UserReportLoader {
    
    protected $dictionary = null;
    
    protected abstract function doLoad($hash); 
    
    public function load($hash) {
        try {
            return $this->doLoad($hash);
        } catch (Exception $e) {
            return false;
        }
    }
}

class UserReportManager {
    public $loader = null;
    
    public function loadReport($hash) {
        if ($this->loader) {
            return $this->loader->load($hash);
        }
        return false;
    }
}