<?php
require_once('group.class.php');

include_once('groupman.inc.php');

$res = 'error';
try {
        if ( (!empty($_REQUEST['id'])) and (!empty($_REQUEST['name']) )) {
            $memberID = $_REQUEST['id'];
            $memberName = trim($_REQUEST['name'], '\'\"');
            if ($GroupManager && $GroupManager->loaded) {
                if ($GroupManager->renameMember($memberID, $memberName)) {
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