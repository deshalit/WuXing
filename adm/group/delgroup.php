<?php
require_once('group.class.php');

include_once('groupman.inc.php');

$res = 'error';
try {
        if (!empty($_REQUEST['id'])) {
            $groupID = ($_REQUEST['id']);
            if ($GroupManager && $GroupManager->loaded) {
                if ($GroupManager->deleteGroup($groupID)) {
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