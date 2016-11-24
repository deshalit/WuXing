<?php
header('Content-Type: application/xml; charset=utf-8');

require_once('group.class.php');

include_once('group.inc.php');
include_once('groupman.inc.php');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<' . NODE_GROUPLIST_ROOT . '>';
try {
        if (!$GroupManager->loaded) {
        //  echo 'bullshit';   
           exit;
        }
        
        if ($GroupManager->groups) {
            foreach($GroupManager->groups as $group) {
                echo '<' . NODE_GROUPLIST_GROUP . '>';   
                echo '<' . NODE_GROUPLIST_ID . '>' . $group->id . '</' . NODE_GROUPLIST_ID . '>';
                echo '<' . NODE_GROUPLIST_NAME . '>' . $group->name . '</' . NODE_GROUPLIST_NAME . '>';
                echo '<' . NODE_GROUPLIST_NOTE . '>' . $group->note . '</' . NODE_GROUPLIST_NOTE . '>';
                $cnt = count($group->members);
                if ($group->members) {
                    $cnt = count($group->members);
                } else $cnt = 0;
                echo '<' . NODE_GROUPLIST_COUNT . '>' . $cnt . '</' . NODE_GROUPLIST_COUNT . '>';
                echo '</' . NODE_GROUPLIST_GROUP . '>';
            }
        }    
    } finally {
        echo '</' . NODE_GROUPLIST_ROOT . '>';
    }