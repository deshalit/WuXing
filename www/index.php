<?php
require_once ("dict.inc.php");
require_once ("dictloaderxml.php");
$dictionary = new Dictionary();
$loader = new DictLoaderXML('dict.xml');
$dictionary->Load($loader);
