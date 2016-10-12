<?php
const MIN_CHART_HEIGHT = 175;
const H_KOEF_1 = 30;
const H_KOEF_2 = 50;

    function calc_row_height(RequestData $data, $pcount) {
        if ($data->mode == MODE_TWO) {
            $h = $pcount*H_KOEF_2;
        } else {
            $h = $pcount*H_KOEF_1;
        }
        return ($h < MIN_CHART_HEIGHT) ? MIN_CHART_HEIGHT : $h;
    }
    function fill_client_form_data(ClientData $client, $no) {
        echo    
             'document.getElementById("firstname' . $no . '").value = "' . $client->first_name . '"; ' 
           . 'document.getElementById("lastname' . $no . '").value = "' . $client->last_name . '"; ' 
           . 'document.getElementById("email' . $no . '").value = "' . $client->email . '"; '; 
        foreach($client->elems as $elkey=>$elvalue) {
            echo 'document.getElementById("elem_' . $elkey . $no .'_chk").checked = "on"; ' .
                 'el = document.getElementById("value_' . $elkey . $no .'"); if (el) { el.disabled = false; el.value = ' . $elvalue . '}; ';
            }    
    }
    function get_data_string(RequestData $mainData, $profile_id) {
        $res = '[' . implode(',', array_reverse( array_values($mainData->client1->profiles[$profile_id]))) . ']';
        if ($mainData->mode == MODE_TWO) {
            return $res . ', [' . implode(',', array_reverse( array_values($mainData->client2->profiles[$profile_id]))) .']';
        } else {
            return $res;
        }
    }
    function fill_form_data(RequestData $mainData) { 
        echo '<script> $("#mode").prop("value", "' . $mainData->mode . '"); ';
        fill_client_form_data($mainData->client1, 1);
        if ($mainData->mode == MODE_TWO) { fill_client_form_data($mainData->client2, 2); }
        foreach (array_values($mainData->profiles) as $pid) {
            echo 'document.getElementById("prof' . $pid . '").checked = true;';
        }    
        echo '</script>';    
    }
    function write_data_cells(RequestData $mainData, $profile_id, $property_id) {
        echo '<td>';
        if ($property_id) {
            echo $mainData->client1->profiles[$profile_id][$property_id];  
            if ($mainData->mode == MODE_TWO) {
                echo '</td><td>' . $mainData->client2->profiles[$profile_id][$property_id];
            } 
        } else {
            if ($mainData->mode == MODE_TWO) { echo '</td><td>'; }           
        }
        echo '</td>';
    }
    function write_person_section($no) {
        echo '        <section id="person' . $no . '">' . "\n" .
             '          <fieldset class="pers_data">' . "\n" .
             '            <legend>Персона</legend>' . "\n" .
             '            <label for="firstname' . $no . '">Имя:</label><br />' . "\n" .
             '            <input id="firstname' . $no . '" type="text" name="fname' . $no . '" placeholder="Иван" /><br />' . "\n" .
             '            <label for="lastname">Фамилия:</label><br />' . "\n" .
             '            <input id="lastname' . $no . '" type="text" name="lname' . $no . '" placeholder="Иванов"/><br />' . "\n" .
             '            <label for="email' . $no . '">Email:</label><br />' . "\n" .
             '            <input id="email' . $no . '" type="email" name="email' . $no . '" placeholder="test@test.net"/><br />' . "\n" .
             '          </fieldset>' . "\n" .
             '          <fieldset class="pers_data">' . "\n" .
             '            <legend>Элементы</legend><table id="elems' . $no . '">' . "\n" .
             '            <label id="wrong_sum' . $no . '" hidden></label><br/>' . "\n";
        foreach(Dictionary::$elemNames as $key=>$name) {
            echo '<tr><td><label for="elem_' . $key . $no . '_chk">' . mb_substr($name, 0, 1, 'UTF-8') . '</label></td>' .
                 '<td><input id="elem_' . $key . $no . '_chk" type="checkbox" onchange="elem_state_changed(' . $no . ', this.id, this.checked)" /></td>' .
                 '<td><input class="elvalue" id="value_' . $key . $no . '" name="el' . $no . '_' . $key . '" type="number" min="0.0" max="0.95" value="0.5" step="0.05" disabled onchange="elem_value_changed(' . $no . ')"/></td></tr>';
        } 
        echo '</table></fieldset></section>';
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестируем алгоритм</title>
    <style>
        #formholder {float: left; /*margin-left: 50px;*/ padding: 10px 10px; 10px; 10px;}
        /*.pers_data { float:left; }*/
        #person_container, #person1, #person2 { float: left }
        /*#profiles { float: left; }*/
        #profiles input[type=checkbox] { margin-top: 10px; }
        #result {float: left; /*margin-right: 30px;*/ padding: 20px 10px;}
        .chartholder { float: left; width: 500px; minwidth: 200px;
                       minheight: <?=MIN_CHART_HEIGHT?>px; margin: 0 10px; }
        #result table {float: left; margin: 20px 10px 10px 10px; width: 250px; }
        #wrong_sum1, #wrong_sum2 {color: darkred; font-weight: bold;}
        #elems { margin: 5px 5px; }
        #mode { margin-right: 20px;}
        #btn_submit {float: right; cursor: hand;}
        .makeimagelink { display: block; text-align: right; }
    </style>
    <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
    <script src="jquery.min.js"></script>
    <script src="jquery.jqplot.min.js"></script>
    <script src="jqplot.barRenderer.min.js"></script>
    <script src="jqplot.categoryAxisRenderer.min.js"></script>
    <script src="jqplot.pointLabels.min.js"></script>
    <link rel="stylesheet" type="text/css" href="jquery.jqplot.min.css" />
    <script>
        $(document).ready( function(){
                onmodechanged( $('#mode').prop("value") );
        
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
            //console.log(data);
            $.jqplot(holderID, data, {
                // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
                animate: false, //!$.jqplot.use_excanvas,
                seriesDefaults:{
                    renderer: $.jqplot.BarRenderer,
                    pointLabels: { show: true, location: 'e', edgeTolerance: -15 },
                    shadowAngle: 135,
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
        function calc_sum(no) {
            var sum = parseFloat(0.0);
            $('#elems' + no + ' input[type="number"]:enabled').each(function(index, element){
                sum += parseFloat(element.value) });
            return sum; // { console.log('Wrong sum: ' + sum); }
        }
        function sum_changed(no) {
            var sum = calc_sum(no);
            var labid = '#wrong_sum' + no;
            if (sum != 1.0){ $(labid).text(sum); $(labid).show();
            } else {
                $(labid).hide(); //console.log('Good sum');
            }
        }
        function elem_state_changed(no, elemid, checked) {
            $("#" + elemid.replace('elem', 'value').replace('_chk', '')).prop("disabled", !checked);
            sum_changed(no);
        }
        function elem_value_changed(no) {
            sum_changed(no);
        }
        function select_all_profs() {
            $('#profiles input[type="checkbox"]').each( function() { this.checked = true } );
        }
        function unselect_all_profs() {
            $('#profiles input[type="checkbox"]').each( function() { this.checked = false } );
        }        
        function onmodechanged(newValue) {
            $('#person2 fieldset').each( function(){this.disabled = (newValue != 'two')} )
        }
        function makeimage(no) {
           var hID = "#chart_" + Profiles[no].id;
           var cr = $(hID)[0].getClientRects()[0];
           var dataStr = $(hID).jqplotToImageStr({})
           var initStr = 'width=' + (cr.width + 50).toString() + ', height=' + (cr.height + 50).toString() + ', left=' + cr.top + ', top=' + cr.top + ', location=no';var titleStr = "Картинка для сохранения: " + Profiles[no].name;           
           var myWindow = window.open("", titleStr, initStr);
           myWindow.document.write('<html><head><title>' + titleStr + '</title></head><body><img src="' + dataStr + '"/></body></html>');
        }
    </script>
</head>
<body>
    <section id="formholder">
        <form action="index.php" method="post">
            <section id="formtoolbar">
                <select id="mode" name="mode" required value="one" onchange="onmodechanged(this.value)">
                    <option value="one">Один клиент</option>
                    <option value="two">Два клиента</option>
                    <!--<option value="">Клиент и группа</option>-->
                </select><input type="submit" value="Рассчитать" />
            </section> 
            <section id="person_container">
                <?php write_person_section(1);
                      write_person_section(2); 
                ?>
            </section>
            <section id="profiles">    
                <fieldset><legend>Срезы</legend>
                    <button type="button" onclick="select_all_profs()">выбрать все</button>
                    <button type="button" onclick="unselect_all_profs()">снять все</button>
                    <button type="submit" id="btn_submit">Рассчитать</button><br/>
                    <?php
                        foreach($dictionary->profiles as $id=>$data) {
                            echo '<input type="checkbox" id="prof' . $id . '" name="prof' . $id . '">' . $data[0] . "<br/>\n";
                        }
                    ?>
                </fieldset>
            </section>          
        </form>
    </section>
    <?php if ($mainData->complete) {
        fill_form_data($mainData);  
        $createProfileStrings = Array();
        echo '<section id="result">' . "\n";
        foreach (array_values($mainData->profiles) as $pid) {
            echo '<div id="profile_' . $pid . '"><table>';
            $profName = $dictionary->get_profile_name($pid);
            echo "<tr><td>" . $profName . '</td>';
            write_data_cells($mainData, $pid, 0);
            echo "</tr>\n";
            $props = $dictionary->get_profile_properties($pid);            
            $names = Array();
            foreach ($props as $propid) {
                $prop_name = $dictionary->get_property_name($propid);                
                $names[] = $prop_name;
                echo "<tr><td>" . $prop_name . "</td>"; // title of property
                write_data_cells($mainData, $pid, $propid);
                echo "</tr>\n";
            }
            $profileStr = ' { id: ' . $pid . ', name: "' . $profName . '"' . ', data: [' . get_data_string($mainData, $pid)
                        . '], names: ["' . implode('","', array_reverse($names)) . '"]}';
            $i = array_push($createProfileStrings, $profileStr);
            $holderID = 'chart_' . $pid;
            echo '</table><div class="chartholder"><a class="makeimagelink" href="#" onclick="makeimage(' .
                ($i-1) . ')">Снимок среза &laquo' . $profName . '&raquo</a><div id="' . $holderID . '" style="height: ' .
                 calc_row_height($mainData, count($props)) . 'px;"></div></div>';
        }
        echo "</section>\n"; // end of div #result
        echo '<script> var Profiles = [' . implode(',', $createProfileStrings) . ']; </script>';
    } ?>
</body>
</html>