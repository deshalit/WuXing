<?php

    include_once ('group.inc.php');
    include_once ('chart.inc.php');

    function fill_subject_form_data(SubjectData $subject, $no) {
        foreach($subject->elems as $elkey=>$elvalue) {
            echo 'document.getElementById("elem_' . $elkey . $no .'_chk").checked = "on"; ' .
                'el = document.getElementById("value_' . $elkey . $no .'"); if (el) { el.disabled = false; el.value = ' . $elvalue . '}; ';
        }
    }

    function fill_client_form_data(ClientData $client, $no) {
        //echo 'console.log("no='.$no.', count=' . count($client->elems) . '");';
        fill_subject_form_data($client, $no);
        echo 'document.getElementById("firstname' . $no . '").value = "' . $client->Name . '"; ';
        echo 'document.getElementById("lastname' . $no . '").value = "' . $client->lastName . '"; '
           . 'document.getElementById("email' . $no . '").value = "' . $client->email . '"; ';
    }

    function fill_group_form_data(GroupData $groupData) {
        echo ' GroupID = ' . $groupData->id . '; ';
        fill_subject_form_data($groupData, 2);
    }
    function get_data_string(RequestData $requestData, $profile_id) {
        $res = '[' . implode(',', array_reverse( array_values($requestData->client1->profiles[$profile_id]))) . ']';
        if ($requestData->mode != MODE_ONE_CLIENT) {
            if ($requestData->mode == MODE_TWO_CLIENTS) {
                $obj = $requestData->client2;
            } else {
                $obj = $requestData->group;
            }    
            return $res . ', [' . implode(',', array_reverse( array_values($obj->profiles[$profile_id]))) .']';
        } else {
            return $res;
        }
    }
    function fill_form_data(RequestData $requestData) {
        echo '<script> $("#mode").prop("value", "' . $requestData->mode . '"); ';
        fill_client_form_data($requestData->client1, 1);
        echo 'console.log("' . $requestData->mode . '");';
        switch ($requestData->mode) {
            case MODE_TWO_CLIENTS:  fill_client_form_data($requestData->client2, 2); break;
            case MODE_CLIENT_GROUP: fill_group_form_data($requestData->group);
        }
        foreach (array_values($requestData->profiles) as $pid) {
            echo 'document.getElementById("prof' . $pid . '").checked = true;';
        }    
        echo '</script>';    
    }
    function write_data_cells(RequestData $requestData, $profile_id, $property_id) {
        echo '<td>';
        if ($property_id) {
            echo $requestData->client1->profiles[$profile_id][$property_id];
            if ($requestData->mode != MODE_ONE_CLIENT) {
                echo '</td><td>';
                if ($requestData->mode == MODE_TWO_CLIENTS) {
                    $subj = $requestData->client2;
                } else { $subj = $requestData->group; }
                echo $subj->profiles[$profile_id][$property_id];
            } 
        } else {
            if ($requestData->mode != MODE_ONE_CLIENT) { echo '</td><td>'; }
        }
        echo '</td>';
    }

    function generateElementSection($no) {
        $res = <<<CODE
<fieldset class="pers_data">
    <legend>Элементы</legend>
    <table id="elems{$no}">
        <label id="wrong_sum{$no}" hidden></label><br/>
CODE;
        foreach(Dictionary::$elemNames as $key=>$name) {
            $res .= '<tr><td><label for="elem_' . $key . $no . '_chk">' . mb_substr($name, 0, 1, 'UTF-8') . '</label></td>' .
                '<td><input id="elem_' . $key . $no . '_chk" type="checkbox" onchange="elem_state_changed(' . $no . ', this.id, this.checked)" /></td>' .
                '<td><input class="elvalue" id="value_' . $key . $no . '" name="' . $key . '[' . $no . ']" type="number" min="0.0" max="0.95" value="0.5" step="0.05" disabled onchange="elem_value_changed(' . $no . ')"/></td></tr>';
        }
        return $res . '</table></fieldset>';
    }

    function write_person_section($no) {
        list($fname, $lname, $email) = array(PARAM_FIRSTNAME, PARAM_LASTNAME, PARAM_EMAIL);
         $s = <<<PERSON
<section id="person{$no}">
     <fieldset class="pers_data">
       <legend>Персона $no </legend>
       <label for="firstname{$no}">Имя:</label>
       <input id="firstname{$no}" type="text" name="$fname{$no}" placeholder="Иван" />
       <label for="lastname{$no}">Фамилия:</label>
       <input id="lastname{$no}" type="text" name="$lname{$no}" placeholder="Иванов"/>
       <label for="email{$no}">Email:</label>
       <input id="email{$no}" type="email" name="$email{$no}" placeholder="test@test.net"/>
       <input type="button" id="report{$no}" class="reportlink" form="rptform" value="Генерация отчёта" onclick="generateReport($no)">
     </fieldset>
PERSON;
        echo $s . generateElementSection($no) . '</section>';
    }

    function write_group_section()
    {   $param_id = PARAM_ID;
        echo <<<CODE
<section id="person2">
  <fieldset class="pers_data">
    <legend>Группа</legend>
    <label for="groupname">Группа:</label>
    <select id="groupname" name="$param_id">
    </select>
  </fieldset>
  {generateElementSection(2)}
</section>
CODE;
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестируем алгоритм</title>
    <style>
        fieldset.pers_data > * { display: block}
        fieldset.pers_data input { margin-bottom: 5px }
        #formholder {float: left; /*margin-left: 50px;*/ padding: 10px 10px 10px 10px;}
        /*.pers_data { float:left; }*/
        #person_container, #person1, #person2 { float: left }
        /*fieldset.pers_data:first-child { height: 150px; }*/
        /*#profiles { float: left; }*/
        #profiles input[type=checkbox] { margin-top: 10px; }
        #result {float: left; /*margin-right: 30px;*/ padding: 20px 10px;}
        .chartholder { float: left; width: 500px; min-width: 200px;
                       min-height: <?=MIN_CHART_HEIGHT?>px; margin: 0 10px; }
        #result table {float: left; margin: 20px 10px 10px 10px; width: 250px; }
        #wrong_sum1, #wrong_sum2 {color: darkred; font-weight: bold;}
        #elems1,#elems2 tbody tr td:first-child{ padding-left: 25px }
        #mode { margin-right: 20px;}
        .btn_submit {float: right; cursor: hand;}
        .makeimagelink { display: block; text-align: right; }
        .reportlink { width: 100%; margin-top: 10px; font-size: large; color: #666666; display: inline-block;}
        header a { padding: 10px; margin-left: 20px; }
            
            
    </style>
    <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
    <script src="jquery.min.js"></script>
    <script src="jquery.jqplot.min.js"></script>
    <script src="jqplot.barRenderer.min.js"></script>
    <script src="jqplot.categoryAxisRenderer.min.js"></script>
    <script src="jqplot.pointLabels.min.js"></script>
    <script src="jqplot.canvasOverlay.js"></script>
    <link rel="stylesheet" type="text/css" href="jquery.jqplot.min.css" />
    <script src="wx_chart.js"></script>
    <script>
        var GroupID;    
        $(document).ready( function(){
                onmodechanged();
        
                if (document.getElementById('result')) {
                    readyCharts(Profiles);
                }  
           } );
    </script>
    <!--  Обработчик нажатия кнопки "Генерация отчета" -->
    <script>
        function generateReport(no) {
            $('#rpt_fname').val( $('#firstname' + no).val() );  // заполняем поле "Имя"
            $('#rpt_lname').val( $('#lastname' + no).val() );   // заполняем поле "Фамилия"
            $('#rpt_email').val( $('#email' + no).val() );      // заполняем поле "Email"
            plist = "";                                         // формируем список срезов
            $('#profiles :checkbox:checked').each(
                function (x, el) {
                    plist += el.id.substr(4, 2) + ",";  // "prof1" > "1,"
                }
            );
            plist = plist.slice(0, -1);       // избавиться от лишней запятой в конце
            if (plist == "") {
                alert("Выберите срезы!");
                return;
            }
            $('#rpt_profiles').val( plist );  // Заполняем поле "Срезы"
            var elems = $('#elems' + no + ' input[type="number"]:enabled');  // Выбранные стихии на панели с номером "no"
            if (elems.length == 2) {         // Стихий должно быть две
                // Для каждой из них определяем имя и значение
                tmpFunc = function (n) {
                    el = $('#rpt_value_' + n).prop('name', elems.eq(n-1).prop('name').substr(0,1)); // "E[1]" > "E"
                    el.val( elems.eq(n-1).val() );
                };
                tmpFunc(1);
                tmpFunc(2);
            } else {
                alert("Выберите два элемента!");
                return;
            }
            $('#rptform').submit();
        }
    </script>
    <script>
/*
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
*/
        function clearValues(no) {
           $('#person' + no + ' [id^="value_"]').prop('disabled', 'true').val(0);
           $('#person' + no + ' [id$="_chk"]').prop('checked', false);   
        }    

        function calc_sum(no) {
            var sum = parseFloat(0.0);
            $('#elems' + no + ' input[type="number"]:enabled').each(function(index, element){
                sum += parseFloat(element.value) });
            return sum; // { console.log('Wrong sum: ' + sum); }
        }
        function sum_changed(no) {
            var sum = calc_sum(no);
            var el = $('#wrong_sum' + no).html(sum);
            if (sum == 1) {
                el.hide();
            } else {
                nextElement = $('#elems' + no + ' :input.elvalue').eq(0);
                margin = nextElement.height() + (nextElement.outerHeight() - nextElement.height()) / 2;
                el.css('margin-bottom', - margin);
                el.show();
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
            $('#profiles :checkbox').prop('checked', true);
        }
        function unselect_all_profs() {
            $('#profiles :checkbox').prop('checked', false);
        }        
        function buildGroupList(xml) {
            var groupNodes = xml.getElementsByTagName('<?=NODE_GROUPLIST_GROUP?>');
            var controlText = "";
            for (var i = 0; i < groupNodes.length; i++) {
                node = groupNodes[i];
                gID = node.getElementsByTagName('<?=NODE_GROUPLIST_ID?>')[0].childNodes[0].nodeValue;
                groupName = node.getElementsByTagName('<?=NODE_GROUPLIST_NAME?>')[0].childNodes[0].nodeValue;
                controlText += '<option value="' + gID + '"';
                if (gID == GroupID) {
                   controlText += ' selected="true"';   
                }    
                controlText += '>' + groupName + '</option>';
            }
            $("#grouplist").html(controlText);
            $('#person2 fieldset:first-child label').eq(0).show();
            groupSelected();
        }            
        function queryGroupList() {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    buildGroupList(this.responseXML);
                }
            };
            xmlhttp.open("GET", "grouplist.php", true);
            xmlhttp.send();
        }    
        function readGroupValues(xml) {
            var elemNodes = xml.getElementsByTagName('<?=NODE_VALUELIST_ITEM?>'); 
            clearValues(2);
            for (var i = 0; i < elemNodes.length; i++) { 
                node = elemNodes[i];
                elemID = node.getElementsByTagName('<?=NODE_VALUELIST_ID?>')[0].childNodes[0].nodeValue;
                value = node.getElementsByTagName('<?=NODE_VALUELIST_VALUE?>')[0].childNodes[0].nodeValue;
                $('#value_' + elemID + '2').prop('disabled', false).prop('step', "0.0001" ).val(value);
                $('#elem_' + elemID + '2_chk').prop('checked', true);
            }
            return true;
        }    
        function groupSelected() {
           //$("#group tbody").html("<tr><td colspan=6>Нет данных</td></tr>");
            select = document.getElementById('grouplist');
            if (select.selectedOptions.length > 0) {
                GroupID = parseInt(select.selectedOptions[0].getAttribute("value"));
            } else {
                if (!GroupID) { return; }
            }

            select.disabled = true;
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    if (readGroupValues(this.responseXML)) {
                          
                    }
                }
                select.disabled = false;
            };
            xmlhttp.open("GET", "groupval.php?id=" + GroupID, true);
            xmlhttp.send();            
        }    
        function onmodechanged(newValue) {
            if (!newValue) {
                newValue = $('#mode').val();
            } else {
                if (newValue == '<?=MODE_TWO_CLIENTS?>') clearValues(2);
            }
            console.log('Mode changed to ' + newValue);
            $('#person2 fieldset:first-child *').prop('disabled', newValue == '<?=MODE_ONE_CLIENT ?>' );
            /*
            if (newValue) {
                clearValues(2);
            } else newValue = $('#mode').prop("value");
            */
            if (newValue == '<?=MODE_CLIENT_GROUP?>') {
                   $('#person2 fieldset.pers_data:first-child').css('height', $('#person1 fieldset.pers_data:first-child').css('height'));
                   // hide client-oriented controls
                   $('#person2 fieldset:first-child').children().hide();
                   // create the SELECT element as a container for the group names
                   sel = '<select id="grouplist" name="<?=PARAM_ID?>" onchange="groupSelected()"></select>';
                   // insert it just after the first label ('Имя:')
                   $('#person2 fieldset:first-child label').eq(0).show().after(sel);
                   $('#person2 fieldset:first-child legend').html('Группа').show();
                   queryGroupList();
                   //$('#person2 fieldset:first-child legend');
            } else {
                  if (newValue == '<?=MODE_TWO_CLIENTS?>') {
                      $('#person2 [id^="value_"]').prop('step', '0.05').prop('value', '0.5');
                      $('#person2 fieldset:first-child select').remove();
                      // show client-oriented controls
                      $('#person2 fieldset:first-child').children().show();
                      //$('#person2 [id^="value_"]').prop('disabled', 'false')
                  }    
            }
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
    <header>
        <a href="groups.php" target="_blank">Менеджер групп</a>
        <a href="upload.php" target="_blank">Сделать заказ</a>
    </header>

    <form id="rptform" action="makereport.php" method="get">
        <input id="rpt_value_1" hidden type="number" required/>
        <input id="rpt_value_2" hidden type="number" required/>
        <input id="rpt_profiles" name="<?=PARAM_PROFILES?>" hidden required />
        <input id="rpt_fname" name="<?=PARAM_FIRSTNAME?>" hidden />
        <input id="rpt_lname" name="<?=PARAM_LASTNAME?>" hidden />
        <input id="rpt_email" name="<?=PARAM_EMAIL?>" hidden />
        <input id="rpt_mode" name="mode" value="new" hidden />
    </form>

    <section id="formholder">
        <form action="index.php" method="post">
            <section id="formtoolbar">
                <select id="mode" name="mode" required value="<?=MODE_DEFAULT ?>" onchange="onmodechanged(this.value)">
                    <option value="<?=MODE_ONE_CLIENT ?>">Один клиент</option>
                    <option value="<?=MODE_TWO_CLIENTS ?>">Два клиента</option>
                    <option value="<?=MODE_CLIENT_GROUP ?>">Клиент и группа</option>
                    <!--<option value="">Клиент и группа</option>-->
                </select><input class="btn_submit" type="submit" value="Рассчитать" />
            </section> 
            <section id="person_container">
                <?php write_person_section(1);
                      write_person_section(2); ?>
            </section>
            <section id="profiles">    
                <fieldset><legend>Срезы</legend>
                    <button type="button" onclick="select_all_profs()">выбрать все</button>
                    <button type="button" onclick="unselect_all_profs()">снять все</button>
                    <button type="submit" class="btn_submit">Рассчитать</button><br/>
                    <?php
                        foreach($dictionary->profiles as $id=>$data) {
                            echo '<input type="checkbox" id="prof' . $id . '" name="prof[' . $id . ']">' . $data[0] . "<br/>\n";
                        }
                    ?>
                </fieldset>
            </section>          
        </form>
    </section>
    <?php if ($requestData->complete) {
        fill_form_data($requestData);
        $createProfileStrings = Array();
        echo '<section id="result">' . "\n";
        foreach (array_values($requestData->profiles) as $pid) {
            echo '<div id="profile_' . $pid . '"><table>';
            $profName = $dictionary->get_profile_name($pid);
            echo "<tr><td>" . $profName . '</td>';
            write_data_cells($requestData, $pid, 0);
            echo "</tr>\n";
            $props = $dictionary->get_profile_properties($pid);            
            $names = Array();
            foreach ($props as $propid) {
                $prop_name = $dictionary->get_property_name($propid);                
                $names[] = $prop_name;
                echo "<tr><td>" . $prop_name . "</td>"; // title of property
                write_data_cells($requestData, $pid, $propid);
                echo "</tr>\n";
            }
            $profileStr = ' { id: ' . $pid . ', name: "' . $profName . '"' . ', data: [' . get_data_string($requestData, $pid)
                        . '], names: ["' . implode('","', array_reverse($names)) . '"]}';
            $i = array_push($createProfileStrings, $profileStr);
            $holderID = 'chart_' . $pid;
            echo '</table><div class="chartholder"><a class="makeimagelink" href="#" onclick="makeimage(' .
                ($i-1) . ')">Снимок среза &laquo' . $profName . '&raquo</a><div id="' . $holderID . '" style="height: ' .
                 calc_row_height($requestData->subjectCount(), count($props)) . 'px;"></div></div>';
        }
        echo "</section>\n"; // end of div #result
        echo '<script> Profiles = [' . implode(',', $createProfileStrings) . ']; </script>';
    } ?>
</body>
</html>