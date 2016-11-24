<?php
require_once('group.class.php');
require_once('GroupSQLite.php');

if (empty($GroupManager)) {
    $GroupManager = new GroupManager();
}
if (empty($GroupManager->dbManager)) {
    $GroupManager->dbManager = new GroupSQLite();
}    
if (!$GroupManager->loaded) {
    $GroupManager->Load();
}    