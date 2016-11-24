<?php
header('Content-Type: application/xml; charset=utf-8');

require_once('group.class.php');

include_once('group.inc.php');  // declaration for nodes' names
include_once('groupman.inc.php');

$groupID = $_REQUEST['id'];

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<' . NODE_MEMBERLIST_ROOT . '>';
try {
    if ($groupID !== null) {
        if (!$GroupManager->loaded) {
        //  echo 'bullshit';   
           exit;
        }
        $group = $GroupManager->getGroupById($groupID);
        //var_dump($group);
        if ($group && $group->members) {
            foreach($group->members as $member) {
                echo '<' . NODE_MEMBERLIST_MEMBER . '>';   
                echo '<' . NODE_MEMBERLIST_ID . '>' . $member->id . '</' . NODE_MEMBERLIST_ID . '>';
                echo '<' . NODE_MEMBERLIST_NAME . '>' . $member->name . '</' . NODE_MEMBERLIST_NAME . '>';
                echo '<' . NODE_MEMBERLIST_DATA . '>';
                if ($member->values) {
                    foreach ($member->values as $key=>$value) {
                        echo '<' . NODE_MEMBERLIST_ITEM . '>';
                        echo '<' . NODE_MEMBERLIST_ELEMENT . '>' . $key . '</' . NODE_MEMBERLIST_ELEMENT . '>';
                        echo '<' . NODE_MEMBERLIST_VALUE . '>' . $value . '</' . NODE_MEMBERLIST_VALUE . '>';
                        echo '</' . NODE_MEMBERLIST_ITEM . '>';
                    }
                }    
                echo '</' . NODE_MEMBERLIST_DATA . '>';
                echo '</' . NODE_MEMBERLIST_MEMBER . '>';
            }
        }    
    }  
    } finally {
        echo '</' . NODE_MEMBERLIST_ROOT . '>';
    }