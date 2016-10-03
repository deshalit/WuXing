<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестируем алгоритм</title>
    <style>
        #form {float: left; margin-left: 50px; padding: 20px 20px;}
        #result {float: left; margin-right: 50px; padding: 20px 20px;}
    </style>
    <script>
        function range1changed() {
            el = document.getElementById("elem1val");
            el.valueAsNumber = document.getElementById("balance").value;
        }    
        function value1changed(newvalue) {
            el = document.getElementById("elem2val");
            if (el && (el.valueAsNumber != (1.0 - newvalue))) {
            el.valueAsNumber = 1.0 - newvalue;
            }            
        }    
        function value2changed(newvalue) {
            el = document.getElementById("elem1val");
            if (el && (el.valueAsNumber != (1.0 - newvalue))) {
                el.valueAsNumber = 1.0 - newvalue;
            }           
        }                    
    </script>
</head>
<body>
<?php if ($mainData->complete) {
    echo '<div id="result">' . "\n" . '<table>' . "\n";
    echo "<tr><td>Срез</td><td>Результат</td></tr>\n";
    foreach ($mainData->profiles as $pid=>$pdata) {
        echo "<tr><td>" . $dictionary->get_profile_name($pid) . "</td><td></td></tr>\n";
        $props = $dictionary->get_profile_properties($pid);
        foreach ($props as $propid) {
            echo "<tr><td>" . $dictionary->get_property_name($propid) . "</td>"; // title of property
            echo "<td>$pdata[$propid]</td></tr>\n";
        }
    }
    echo "</table>\n</div>\n";
} ?>

    <div id="form">
        <form action="index.php" method="post">
            <fieldset>
                <label for="firstname">Имя:</label><br />
                <input id="firstname" type="text" name="fname" placeholder="Иван" /><br />
                <label for="lastname">Фамилия:</label><br />
                <input id="lastname" type="text" name="lname" placeholder="Иванов"/><br />
                <label for="email">Email:</label><br />
                <input id="email" type="email" name="email" placeholder="test@test.net"/><br />
            </fieldset>
            <fieldset>
                <label for="elemcount">Сколько элементов:</label>
                <input id="elemcount" type="number" min="2" max="5" value="2" readonly onchange="elem_count_changed(this.value)"/><br/>
                <label for="elem1">Элемент 1:</label>
                <select id="elem1" name="el1">
                    <?php foreach(Dictionary::$elemNames as $key=>$name) {
                           echo '<option value="' . $key . '">' . $name . "</option>\n";
                          } ?>
                </select>
                <input id="elem1val" name="value1" type="number" min="0.01" max="0.99" step="0.01" value="0.5" list="steps" onchange="value1changed(this.value)"/><br/>
                <!-- <input id="balance" name="ratio" type="range" min="0.01" max="0.99" value="0.50" step="0.01" list="steps" onchange=""/><br/>
                <datalist id="steps">
                    <option value="0.5" label="0.5">
                </datalist>
                -->
                <label for="elem2">Элемент 2:</label>
                <select id="elem2" name="el2">
                    <?php foreach(Dictionary::$elemNames as $key=>$name) {
                        echo '<option value="' . $key . '">' . $name . "</option>\n";
                    } ?>
                </select>
                <input id="elem2val" name="value2" type="number" min="0.01" max="0.99" step="0.01" value="0.5" onchange="value2changed(this.valueAsNumber)"/>
            </fieldset>
            <fieldset>
                <label>Срезы:</label><br/>
                <?php
                    foreach($dictionary->profiles as $id=>$data) {
                        echo '<input type="checkbox" name="prof' . $id . '">' . $data[0] . "<br/>\n";
                    }
                ?>
            </fieldset>
            <br />
            <input type="submit" value="Рассчитать" />
    </form>
    </div>
</body>
</html>