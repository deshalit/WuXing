<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<pre>

<?php
error_reporting(E_ALL);
include_once('groupman.inc.php');

if ($GroupManager->loaded) {
    
   /* var_dump($GroupManager->members);
    foreach($GroupManager->members as $g) {
       var_dump($g);
    }*/
    $g = $GroupManager->getGroupValues(1);
    var_dump($g);
} else { echo 'Failed!'; }

/*
try {
    $dsn = new PDO("sqlite:" . DB_PATH);

    $stmt = $dsn->prepare(QUERY_GROUPLIST);

    $stmt->execute();
    //$stmt->setFetchMode();
    $groups = $stmt->fetchALL(PDO::FETCH_CLASS, 'Group');



    $dsn = null;
} catch (PDOException $e){
    print "Error!: " . $e->getMessage() . "<br />";
}
   foreach ($groups as $group) {
       $group->Say();
   }
*/
?>
    </pre>
</body>
</html>