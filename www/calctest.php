<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестируем алгоритм</title>
    <style>
        #from {float: left;}
        #result {float: right;}
    </style>
</head>
<body>
<?php if ($mainData->complete) {
    echo '<div id="result">' . "\n" . '<table>' . "\n";
    echo "<tr><td>Срез</td><td>Результат</td></tr>\n";
    foreach ($mainData->profiles as $pid=>$pdata) {
        echo "<tr><td>" . $dictionary->profiles[$pid][0] . "</td><td></td></tr>\n";
        $props = $dictionary->profiles[$pid][1];
        foreach ($props as $propid) {
            echo "<tr><td>" . $dictionary->properties[$propid][0] . "</td>"; // title of property
            echo "<td>$pdata[$propid]</td></tr>\n";
        }
    }
    echo "</table>\n</div>\n";
} ?>

    <div id="form">
        <form action="index.php" method="post">
            <label for="firstname">Имя:</label>:<br />
            <input id="firstname" type="text" name="fname" /><br />
            <label for="lastname">Фамилия:</label>:<br />
            <input id="lastname" type="text" name="lname" /><br />
            <label for="email">Email:</label><br />
            <input id="email" type="email" name="email" /><br />
            <label for="elem1">Элемент 1:</label><br />
            <select id="elem1" name="el1">
                <?php foreach(Dictionary::$elemNames as $key=>$name) {
                       echo '<option value="' . $key . '">' . $name . "</option>\n";
                      } ?>
            </select><br/>
            <input id="balance" name="ratio" type="range" min="0.1" max="0.9" value="0.5" step="0.1" list="steps" onchange=""/>
            <datalist id="steps">
                <option value="0.5" label="0.5">
            </datalist>
            <br/>
            <label for="elem2">Элемент 2:</label><br />
            <select id="elem2" name="el2">
                <?php foreach(Dictionary::$elemNames as $key=>$name) {
                    echo '<option value="' . $key . '">' . $name . "</option>\n";
                } ?>
            </select><br/>
            <label>Срезы:</label><br/>
            <?php
                foreach($dictionary->profiles as $id=>$data) {
                    echo '<input type="checkbox" name="prof' . $id . '">' . $data[0] . "<br/>\n";
                }
            ?>
            <br />
            <input type="submit" value="Добавить!" />
    </form>
    </div>
</body>
</html>