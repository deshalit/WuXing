<?php
require_once('group.class.php');

include_once('groupman.inc.php');

$res = 'error';
try {
        if ( (!empty($_REQUEST['id'])) and (!empty($_REQUEST['elem']) ) and isset($_REQUEST['value'])) {
            $memberID = $_REQUEST['id'];
            $elementID = $_REQUEST['elem'];
            $newValue = $_REQUEST['value'];
            if (($GroupManager !== null) and $GroupManager->loaded) {
                if ($GroupManager->setMemberValue($memberID, $elementID, $newValue)) {
                    $res = 'ok';
                }
            } //else { var_dump($GroupManager);}
        }
    }
catch (PDOException $e) {
    $res = 'error: ' . $e->getMessage();
}
finally {
        echo $res;
}