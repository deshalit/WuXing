<?php

$dictPath = $_SERVER['DOCUMENT_ROOT'] . '/class/dictionary/';
require_once($dictPath . 'dictionary.class.php');
require_once($dictPath . 'dictloaderxml.class.php');

if (!isset($dictionary)) {
    $dictionary = new Dictionary();
    //$pinfo = pathinfo('/dict.xml');
    //var_dump($pinfo);
    
    $loader = new DictLoaderXML($dictPath . 'dict.xml');
    $dictionary->Load($loader);
}

