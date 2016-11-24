<?php
require_once('group.class.php');

include_once('groupman.inc.php');

$res = 'error';
try {
        if ( (!empty($_REQUEST['id'])) and (!empty($_REQUEST['name']) )) {
            $groupID = $_REQUEST['id'];
            $groupName = trim($_REQUEST['name'], '\'\"');
            if ($GroupManager && $GroupManager->loaded) {
                if ($GroupManager->renameGroup($groupID, $groupName)) {
                    $res = 'ok';
                }
            } //else { var_dump($GroupManager);}
        }
    }
catch (PDOException $e) {
    $res = $e->getMessage();
}
finally {
        echo $res;
}