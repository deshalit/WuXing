<?php
const MIN_CHART_HEIGHT = 175;

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

    function get_data_cells(RequestData $mainData, $profile_id, $property_id) {
        echo '<td>';
        if ($property_id) {
            echo $mainData->client1->profiles[$profile_id][$property_id];  
            if ($mainData->mode == MODE_TWO) {
                echo '</td><td>' . $mainData->client1->profiles[$profile_id][$property_id];
            } 
        } else {
            if ($mainData->mode == MODE_TWO) { 
                echo '</td><td>'; 
            }           
        }
        echo '</td>';
    }
    
    function get_data_string(RequestData $mainData, $profile_id) {
        return implode(',', array_reverse( array_values($mainData->client1->profiles[$profile_id]))); 
    }

    function fill_form_data(RequestData $mainData) { 
        echo '<script> $("#mode").value = "' . $mainData->mode . '"; ';    
        fill_client_form_data($mainData->client1, 1);
        if ($mainData->mode == MODE_TWO) { fill_client_form_data($mainData->client2, 2); }
        foreach (array_values($mainData->profiles) as $pid) {
            echo 'document.getElementById("prof' . $pid . '").checked = true;';
        }    
        echo '</script>';    
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестируем алгоритм</title>
    <style>
        #formholder {float: left; margin-left: 50px; padding: 20px 20px;}
        /*.pers_data { float:left; }*/
        #person_container, #person1, #person2 { float: left }
        /*#profiles { float: left; }*/
        #profiles input[type=checkbox] { margin-top: 10px; }
        #result {float: left; margin-right: 50px; padding: 20px 20px;}
        .chartholder { float: left; width: 500px; minwidth: 200px;
                       minheight: <?=MIN_CHART_HEIGHT?>px; margin: 30px; }
        #result table {float: left; margin: 20px 10px 20px 20px; width: 250px; }
        #wrong_sum1, #wrong_sum2 {color: darkred; font-weight: bold;}
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
                onmodechanged( $('#mode').value );         
        
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
            $.jqplot(holderID, [data], {
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
        function onmodechanged(newvalue) {
            $('#person2 fieldset').each( function(){this.disabled = (newvalue != 'two')} )
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
                <section id="person1">
                    <fieldset class="pers_data">
                        <legend>Персона</legend>
                        <label for="firstname1">Имя:</label><br />
                        <input id="firstname1" type="text" name="fname1" placeholder="Иван" /><br />
                        <label for="lastname">Фамилия:</label><br />
                        <input id="lastname1" type="text" name="lname1" placeholder="Иванов"/><br />
                        <label for="email1">Email:</label><br />
                        <input id="email1" type="email" name="email1" placeholder="test@test.net"/><br />
                    </fieldset>
                    <fieldset class="pers_data">
                        <legend>Элементы</legend><table id="elems1">
                        <label id="wrong_sum1" hidden></label><br/>
                        <?php  foreach(Dictionary::$elemNames as $key=>$name) {
                            echo '<tr><td><label for="elem_' . $key . '1_chk">' . mb_substr($name, 0, 1, 'UTF-8') . '</label></td>' .
                                '<td><input id="elem_' . $key . '1_chk" type="checkbox" onchange="elem_state_changed(1, this.id, this.checked)" /></td>' .
                                '<td><input class="elvalue" id="value_' . $key . '1" name="el1_' . $key . '" type="number" min="0.0" max="0.95" value="0.5" step="0.05" disabled onchange="elem_value_changed(1)"/></td></tr>';
                        } ?>
                        </table>
                    </fieldset>
                </section>   
                <section id="person2">
                    <fieldset class="pers_data">
                        <legend> Персона 2 </legend>
                        <label for="firstname2">Имя:</label><br />
                        <input id="firstname2" type="text" name="fname2" placeholder="Иван" /><br />
                        <label for="lastname2">Фамилия:</label><br />
                        <input id="lastname2" type="text" name="lname2" placeholder="Иванов"/><br />
                        <label for="email2">Email:</label><br />
                        <input id="email2" type="email" name="email2" placeholder="test@test.net"/><br />
                    </fieldset>
                    <fieldset class="pers_data">
                        <legend>Элементы</legend><table id="elems2">
                        <label id="wrong_sum2" hidden></label><br/>
                        <?php  foreach(Dictionary::$elemNames as $key=>$name) {
                            echo '<tr><td><label for="elem_' . $key . '2_chk">' . mb_substr($name, 0, 1, 'UTF-8') . '</label></td>' .
                                '<td><input id="elem_' . $key . '2_chk" type="checkbox" onchange="elem_state_changed(2, this.id, this.checked)" /></td>' .
                                '<td><input class="elvalue" id="value_' . $key . '2" name="el2_' . $key . '" type="number" min="0.0" max="0.95" value="0.5" step="0.05" disabled onchange="elem_value_changed(2)"/></td></tr>';
                        } ?>
                        </table>
                    </fieldset>
                </section>              
            </section>
            <section id="profiles">    
                <fieldset><legend>Срезы</legend>
                    <button type="button" onclick="select_all_profs()">выбрать все</button>
                    <button type="button" onclick="unselect_all_profs()">снять все</button><br/>
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
            echo "<tr><td>" . $dictionary->get_profile_name($pid) . '</td>';
            get_data_cells($mainData, $pid, 0);
            echo "</tr>\n";
            $props = $dictionary->get_profile_properties($pid);            
            $names = Array();
            foreach ($props as $propid) {
                $prop_name = $dictionary->get_property_name($propid);                
                $names[] = $prop_name;
                echo "<tr><td>" . $prop_name . "</td>"; // title of property
                get_data_cells($mainData, $pid, $propid);
                echo "</tr>\n";
            }
            $createProfileStrings[] = ' { id: ' . $pid . ', data: [' . get_data_string($mainData, $pid)
                                    . '], names: ["' . implode('","', array_reverse($names)) . '"]}';
            $holderID = 'chart_' . $pid;
            $h = count($props)*30;
            $h = ($h < MIN_CHART_HEIGHT) ? MIN_CHART_HEIGHT : $h;
            echo '</table><div class="chartholder" id="' . $holderID . '" style="height: ' . $h . 'px;"></div>';
        }
        echo "</section>\n"; // end of div #result
        echo '<script> var Profiles = [' . implode(',', $createProfileStrings) . ']; </script>';
    } ?>
</body>
</html>