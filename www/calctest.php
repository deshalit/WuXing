<?php
const MIN_CHART_HEIGHT = 175;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестируем алгоритм</title>
    <style>
        #form {float: left; margin-left: 50px; padding: 20px 20px;}
        #result {float: left; margin-right: 50px; padding: 20px 20px;}
        .chartholder { /*background-color: darkgrey;*/ float: left; width: 500px; minwidth: 200px;
                       minheight: <?=MIN_CHART_HEIGHT?>px; margin: 30px; }
        #result table {float: left; margin: 20px 10px 20px 20px; width: 250px; }
        #wrong_sum {color: darkred; font-weight: bold;}
	#elems { margin: 5px 5px; }
        
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
                        //$("#" + holderID).height = Profiles[i].data.length * 35;
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
                animate: false, //!$.jqplot.use_excanvas,
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
                    },
                    xaxis: { max: 5.0 }
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
        function calc_sum() {
            var sum = parseFloat(0.0);
            $('#elems input[type="number"]:enabled').each(function(index, element){
                sum += parseFloat(element.value) });
            return sum; // { console.log('Wrong sum: ' + sum); }
        }
        function sum_changed() {
            var sum = calc_sum();
            if (sum != 1.0){ $('#wrong_sum').text(sum); $('#wrong_sum').show();
            } else {
                $('#wrong_sum').hide(); //console.log('Good sum');
            }
        }
        function elem_state_changed(elemid, checked) {
            $("#" + elemid.replace('elem', 'value').replace('_chk', '')).prop("disabled", !checked);
            sum_changed();
        }
        function elem_value_changed() {
            sum_changed();
        }
    </script>
</head>
<body>
    <div id="form">
        <form action="index.php" method="post">
            <fieldset>
                <legend>Персона</legend>
                <label for="firstname">Имя:</label><br />
                <input id="firstname" type="text" name="fname" placeholder="Иван" /><br />
                <label for="lastname">Фамилия:</label><br />
                <input id="lastname" type="text" name="lname" placeholder="Иванов"/><br />
                <label for="email">Email:</label><br />
                <input id="email" type="email" name="email" placeholder="test@test.net"/><br />
            </fieldset>
            <fieldset><legend>Элементы</legend><table id="elems">
                <label id="wrong_sum" hidden></label><br/>
                <?php  foreach(Dictionary::$elemNames as $key=>$name) {
                    echo '<tr><td><label for="elem_' . $key . '_chk">' . mb_substr($name, 0, 1, 'UTF-8') . '</label></td>' .
                        '<td><input id="elem_' . $key . '_chk" type="checkbox" onchange="elem_state_changed(this.id, this.checked)" /></td>' .
                        '<td><input class="elvalue" id="value_' . $key . '" name="el_' . $key . '" type="number" min="0.0" max="0.95" value="0.5" step="0.05" disabled onchange="elem_value_changed()"/></td></tr>';
                } ?>

                </table>
            </fieldset>
            <fieldset><legend>Срезы</legend>
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
        echo '<script>'
           . 'document.getElementById("firstname").value = "' . $mainData->first_name . '"; ' 
           . 'document.getElementById("lastname").value = "' . $mainData->last_name . '"; ' 
           . 'document.getElementById("email").value = "' . $mainData->email . '"; '; 
        foreach($mainData->elems as $elkey=>$elvalue) {
            echo 'document.getElementById("elem_' . $elkey . '_chk").checked = "on"; ' .
                 'el = document.getElementById("value_' . $elkey . '"); if (el) { el.disabled = false; el.value = ' . $elvalue . '}; ';
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
            $h = count($props)*30;
            $h = ($h < MIN_CHART_HEIGHT) ? MIN_CHART_HEIGHT : $h;
            echo '</table><div class="chartholder" id="' . $holderID . '" style="height: ' . $h . 'px;"></div>';
        }
        echo "</div>\n"; // end of div #result
        echo '<script> var Profiles = [' . implode(',', $createProfileStrings) . ']; </script>';
    } ?>
</body>
</html>