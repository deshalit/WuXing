<?php
header('Content-Type: application/xml; charset=utf-8');

require_once('group.class.php');

include_once('group.inc.php');  // declaration for nodes' names
include_once('groupman.inc.php');

$groupID = $_REQUEST['id'];

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<' . NODE_VALUELIST_ROOT . '>';
try {
    if ($groupID !== null) {
        if (!$GroupManager->loaded) {
          //echo 'bullshit';   
           exit;
        }
        $values = $GroupManager->getGroupValues($groupID);
        //var_dump($values);
        if (is_array($values)) {
            foreach($values as $key=>$value) {
                echo '<' . NODE_VALUELIST_ITEM . '>';
                echo '<' . NODE_VALUELIST_ID . '>' . $key . '</' . NODE_VALUELIST_ID . '>';
                echo '<' . NODE_VALUELIST_VALUE . '>' . $value . '</' . NODE_VALUELIST_VALUE . '>';
                echo '</' . NODE_VALUELIST_ITEM . '>';
            }
        }    
    }  
    } finally {
        echo '</' . NODE_VALUELIST_ROOT . '>';
    }