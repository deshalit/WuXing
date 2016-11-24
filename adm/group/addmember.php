<?php
require_once('group.class.php');

include_once('groupman.inc.php');

$res = 0;
try {
        if ( (!empty($_REQUEST['name'])) and (!empty($_REQUEST['group']))) {
            $memberName = trim($_REQUEST['name'], '\'\"');
            $groupID = $_REQUEST['group'];
            //echo 'name is ' . $groupName;
            if ($GroupManager && $GroupManager->loaded) {
                if (!$res = $GroupManager->addMember($memberName, $groupID)) {
                    $res = '-1';
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