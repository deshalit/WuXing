<?php
require_once('group.class.php');

include_once('groupman.inc.php');

$res = 'error';
try {
        if (!empty($_REQUEST['id'])) {
            $memberID = ($_REQUEST['id']);
            if ($GroupManager && $GroupManager->loaded) {
                if ($GroupManager->deleteMember($memberID)) {
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