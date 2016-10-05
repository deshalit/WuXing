<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестируем алгоритм</title>
    <style>
        #form {float: left; margin-left: 50px; padding: 20px 20px;}
        #result {float: left; margin-right: 50px; padding: 20px 20px;}
        .chartholder { float: left; width: 500px; minwidth: 200px; minheight: 200px; height: 300px; margin: 30px; }
        table {float: left; margin: 20px 10px 20px 20px;}
        
    </style>
    <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
    <script language="javascript" type="text/javascript" src="jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="jquery.jqplot.min.js"></script>
    <script language="javascript" type="text/javascript" src="jqplot.barRenderer.min.js"></script>
    <script language="javascript" type="text/javascript" src="jqplot.categoryAxisRenderer.min.js"></script>
    <link rel="stylesheet" type="text/css" href="jquery.jqplot.min.css" />
    <script>
        $(document).ready( function(){
                $.jqplot.config.enablePlugins = true;
                if (document.getElementById('result')) {
                    for (var i=0; i<Profiles.length; i++) {
                        holderID = 'chart_' + Profiles[i].id;
                        $("#" + holderID).bind("jqplotDataClick", 
                            function (ev, seriesIndex, pointIndex, data) {
                                      $("#info1").html("series: " + seriesIndex + ", point: " + pointIndex + ", data: " + data); 
                            } );                                          
                        ViewData(Profiles[i].data, Profiles[i].names, holderID);
                    }
                }    
           } );
    </script>
    <script>
        function ViewData(data, names, holderID) {
            plot1 = $.jqplot(holderID, [data], {
                // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
                animate: !$.jqplot.use_excanvas,
                seriesDefaults:{
                    renderer: $.jqplot.BarRenderer,
                    pointLabels: { show: true, location: 'e', edgeTolerance: -15 },
                    rendererOptions: {
                        barDirection: 'horizontal'}
                },
                axes: {
                    yaxis: {
                        renderer: $.jqplot.CategoryAxisRenderer,
                        ticks: names
                    }
                },
                highlighter: { show: false }
            });
        }
    </script>
    <script>
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
                <input id="elemcount" type="number" min="2" max="5" value="2" readonly onchange="elem_count_changed(this.value)"/>
                <br /><br/>
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
                --><br/>
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
                        echo '<input type="checkbox" id="prof' . $id . '" name="prof' . $id . '">' . $data[0] . "<br/>\n";
                    }
                ?>
            </fieldset>
            <br />
            <input type="submit" value="Рассчитать" />
    </form>
    </div>
    <?php if ($mainData->complete) {
        echo '<script>' . 
             'document.getElementById("firstname").value = "' . $mainData->first_name . '"; ' .
             'document.getElementById("lastname").value = "' . $mainData->last_name . '"; ' .
             'document.getElementById("email").value = "' . $mainData->email . '"; ';
        $temp = array_keys($mainData->elems);
        for($i=0; $i<count($temp); $i++) {
            echo 'document.getElementById("elem' . ($i+1) . '").value = "' . $temp[$i] . '"; ' .
                 'document.getElementById("elem' . ($i+1) . 'val").value = ' . $mainData->elems[$temp[$i]] . '; ';
            }    
        foreach ($mainData->profiles as $pid=>$pdata) {
            echo 'document.getElementById("prof' . $pid . '").checked = true;';
        }    
        echo '</script>';
        $createProfileStrings = Array();
        echo '<div id="result">' . "\n";
        foreach ($mainData->profiles as $pid=>$pdata) {
            echo '<div id="profile_' . $pid . '"><table>';
            echo "<tr><td>" . $dictionary->get_profile_name($pid) . "</td><td></td></tr>\n";
            $props = $dictionary->get_profile_properties($pid);            
            $names = Array();
            foreach ($props as $propid) {
                $prop_name = $dictionary->get_property_name($propid);                
                $names[] = $prop_name;
                echo "<tr><td>" . $prop_name . "</td>"; // title of property
                echo "<td>$pdata[$propid]</td></tr>\n";
            }
            $createProfileStrings[] = ' { id: ' . $pid . ', data: [' . implode(',', array_reverse( array_values($pdata))) 
                                    . '], names: ["' . implode('","', array_reverse($names)) . '"]}';
            $holderID = 'chart_' . $pid;
            echo '</table><div class="chartholder" id="' . $holderID . '"></div>';
        }
        echo "</div>\n"; // end of div #result
        echo '<script> var Profiles = [' . implode(',', $createProfileStrings) . ']; </script>';
    } ?>
</body>
</html>