<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $fname = $_GET['id'];
    if (empty($fname)) {
        throw new RuntimeException('Неверно заданы параметры'); 
    }
}  
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Фото</title>
    <style>
       img {
         border-width: 1px;         
       }    
       button { display: block; cursor: pointer; }
    </style>
</head>
<body>
    <form action="deletephoto.php?id=<?=$fname?>" method="POST">
    <button type="submit">Удалить</button>
    <img src="getimage.php?id=<?=$fname?>" />
    </form>
</body>
</html>