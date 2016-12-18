<?php
require_once ('userreport.class.php');

abstract class UserReportLoader {
    
    protected $dictionary = null;
    
    protected abstract function doLoad($hash); 
    
    public function load($hash) {
        try {
            return $this->doLoad($hash);
        } catch (Exception $e) {
            echo 'ERROR: ' . $e->getMessage();
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
        else echo 'empty loader';
        return false;
    }
}