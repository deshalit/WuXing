<?php
require_once('group.class.php');

include_once('groupman.inc.php');

$res = 0;
try {
        if (!empty($_REQUEST['name'])) {
            $groupName = trim($_REQUEST['name'], '\'\"');
            //echo 'name is ' . $groupName;
            if ($GroupManager && $GroupManager->loaded) {
                if (!$res = $GroupManager->addGroup($groupName)) {
                    $res = '-1';
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