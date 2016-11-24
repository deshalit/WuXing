<?php
require_once ('dict.class.php');
require_once ('dictXML.php');
//echo 'path: ' . __DIR__ . '<br/>';
//echo 'file: ' . __FILE__ . '<br/>';
if (!isset($dictionary)) {
    $dictionary = new Dictionary();
    //$pinfo = pathinfo('/dict.xml');
    //var_dump($pinfo);
    
    $loader = new DictLoaderXML(__DIR__ . '/dict.xml');
    $dictionary->Load($loader);
}

