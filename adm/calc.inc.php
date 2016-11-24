<?php
require_once ("../dict.class.php");
require_once ("calculator.class.php");
include_once ("../dictxml.inc.php");

if (!isset($calculator)) {
    $calculator = new Calculator($dictionary);
}