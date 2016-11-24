<?php
require_once ("../dict.class.php");
require_once("../order.const.php");
require_once("../order.class.php");
require_once("orderreader.php");
require_once("report.class.php");

include_once("../dictxml.inc.php");

class ReportBuilder {

    /**
     * @var Report
     */
    private $report = null;
    private $order;
    private $orderReader;
    private $dictionary;

    public function __construct(Dictionary $dict, OrderReader $orderReader) {
        $this->orderReader = $orderReader;
        $this->dictionary = $dict;
    }
    public function analyzeParams() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (!empty($_GET[PARAM_ORDER_ID])) {
                $id = $_GET[PARAM_ORDER_ID];
            }
        }
        $this->order = $this->orderReader->getOrderById($id);
        if (!$this->order) {
            die("Error reading information about order #" . $id);
        }
        $this->report = new Report($this->order);
    }
    public function generateElemList() {
        $res = '[';
        $elems = Array();
        foreach (Dictionary::$elemNames as $id=>$value) {
            $s = '{id: "' . $id . '", name: "' . $value . '", mask: ' . Dictionary::$elemHash[$id] . '}';
            array_push($elems, $s);
        }
        $res .= implode(',', $elems) . '];';
        return $res;
    }
    private function getReportElemList() {
        $res = Array();
        if (is_array($this->report->elems)) {
            foreach ($this->report->elems as $id => $value) {
                array_push($res, '{id: "' . $id . '", data: ' . $value . '}');
            }
        }
        return implode(',', $res);
    }
    private function getReportProfileList() {
        if (is_array($this->report->data)) {
            return implode(',', array_keys($this->report->data));
        } else return '';
    }
    private function getReportRiskList() {
        if (is_array($this->report->risk)) {
            return implode(',', $this->report->risk);
        } else return '';
    }
    private function getReportxCommentList() {
        $res = Array();
        if (is_array($this->report->xComments)) {
            foreach($this->report->xComments as $cid=>$text) {
                $s = '{id: ' . $cid . ', data: "' . htmlspecialchars($text) . '"}';
                array_push($res, $s);
            }
            return implode(',', $res);
        } else return '';
    }
    private function getReportxRiskList() {
        $res = Array();
        if (is_array($this->report->xRisk)) {
            foreach($this->report->xRisk as $rid=>$text) {
                $s = '{id: ' . $rid . ', data: "' . htmlspecialchars($text) . '"}';
                array_push($res, $s);
            }
            return implode(',', $res);
        } else return '';
    }
    public function generateReportObject() {
        $res = '{ ';
        $res .= 'id: ' . $this->report->id . ', orderId: ' . $this->report->orderId .
                ', firstName: "' . $this->report->firstName . '", lastName: "' . $this->report->lastName .
                '", email: "' . $this->report->email . '", note: "' . $this->report->note . '", ' .
                'userNote: "' . $this->report->orderNote . '", ' .
                'elems: [' . $this->getReportElemList() . '], mask: ' . $this->report->getMask() .
                ',  profiles: [' . $this->getReportProfileList() . '], risk: [' . $this->getReportRiskList() .
                '], xComments: [' . $this->getReportxCommentList() . ']' .
                ', xRisk: [' . $this->getReportxRiskList() . ']';
        $res .= ' }';
        return $res;
    }
    public function generateProfileList() {
        $res = '[ ';
        $profS = Array();
        foreach ($this->dictionary->profiles as $pid=>$profileData) {
            $s = '{id: ' . $pid . ', name: "' . $profileData[0] . '", props: [';
            $props = [];
            foreach ($profileData[1] as $propId) {
               $sp = '{id:' . $propId . ', name:"' . $this->dictionary->properties[$propId][0] . '"}';
               array_push($props, $sp);
            }
            $s .= implode(',', $props) . '], textData: [';
            $props = [];
            foreach ($this->dictionary->comments[$pid] as $hash=>$text) {
                $sp = '{mask: ' . $hash . ', data: "' . htmlspecialchars($text) . '"}';
                array_push($props, $sp);
            }
            $s .= implode(',', $props) . ']}';
            array_push($profS, $s);
        }
        $res .= implode(',', $profS) . ' ]';
        return $res;
    }
    public function generateRiskList() {
        $res = '[ ';
        $risk = Array();
        foreach ($this->dictionary->risk as $rid=>$riskData) {
            $s = '{id: ' . $rid . ', name: "' . $riskData[0] . '", textData: [';
            $props = [];
            foreach ($riskData[1] as $hash=>$value) {
                $sp = '{mask:' . $hash . ', data:"' . htmlspecialchars($value) . '"}';
                array_push($props, $sp);
            }
            $s .= implode(',', $props) . ']}';
            array_push($risk, $s);
        }
        $res .= implode(',', $risk) . ' ]';
        return $res;
    }
}

if (empty($orderReader)) {
    $orderReader = new OrderReader();
}
if (empty($reportBuilder)) {
    $reportBuilder = new ReportBuilder($dictionary, $orderReader);
}
$reportBuilder->analyzeParams();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Новая работа</title>
    <meta charset="utf-8">
    <style>
        label {
            display: block;
            margin-top: 5px;
            font-size: large;
            cursor: pointer;
        }
        input[type="checkbox"] {
            cursor: pointer;
        }
        #profiles {
            width: 20%; float: left;
        }
        #risk {
            width: 15%; float: left;
        }
        .pers_data { float:left; }
        #persona label * { display: block; }
        .pers_data input { margin-bottom: 5px; font-size: large; }
        #elems label {
            width: 70px; display: inline-block;
        }
        #wrong_sum { display: block; color: transparent; font-weight: bold; }
        #elems input {
            display: inline;
        }
        textarea {
            height: 100%;
            width: 100%;
            display: block;
            font-size: large;
            background-color: aliceblue;
            /*border-style: groove;
            border-color: cornflowerblue;*/
        }
        #output {
            clear: both;
        }
        .chartholder { /*float: left;*/
            width: 450px;
            min-width: 200px;
            min-height: 200px;
            /*margin: 0 10px;*/
        }
        #profileholder, #riskholder {
            float: left;
        }
        #profileholder tbody td {
            min-height: 200px;
        }
        #riskholder {
            margin-left: 50px;
        }
        #riskholder textarea {
            height: 200px;
        }
        td[colspan] {
            text-align: center;
            /*padding-top: 30px;*/
        }
        h3 {
            display: inline;
        }
        tbody td.text {
            width: 300px;
            padding-bottom: 20px;
            padding-top: 10px;
        }
        .textChanged {
            background-color: antiquewhite;
        }
        .bigbutton {
            cursor: hand;
            margin-top: 10px; font-size: large; color: #666666; display: inline-block;
        }
        #userpagelink { display: none }
    </style>
    <script src="../lib/jquery.min.js"></script>
    <script src="../lib/jqplot/jquery.jqplot.min.js"></script>
    <script src="../lib/jqplot/plugins/jqplot.barRenderer.min.js"></script>
    <script src="../lib/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
    <script src="../lib/jqplot/plugins/jqplot.pointLabels.min.js"></script>
    <script src="../lib/jqplot/plugins/jqplot.canvasOverlay.min.js"></script>
    <link rel="stylesheet" type="text/css" href="../lib/jqplot/jquery.jqplot.min.css" />
    <script src="../wx_chart.js"></script>
    <script>
        function cloneArray(src) {
            return JSON.parse( JSON.stringify(src));
        }

        var Report = <?=$reportBuilder->generateReportObject();?>;
        var Profiles = <?=$reportBuilder->generateProfileList();?>;
        var Risk = <?=$reportBuilder->generateRiskList();?>;
        var Elems = <?=$reportBuilder->generateElemList();?>;
        var newReport = cloneArray(Report);

        function findById(container, id) {
            return container.find( function (el) { return (el.id == id); } );
        }
        function calc_sum() {
            var sum = 0;//parseFloat(0.0);
            $('#elems input[type="number"]:enabled').each(function(index, element){
                sum += Number(element.value)*100; //parseFloat(element.value) 
            });
            return sum; // { console.log('Wrong sum: ' + sum); }
        }
        function sum_changed() {
            var sum = calc_sum();
            var col = (sum == 100) ? 'transparent' : 'red';
            $('#wrong_sum').html(sum/100).css('color', col);
        }

        function elem_state_changed(elemid, checked) {
            $("#" + elemid.replace('state', 'value')).prop("disabled", !checked);
            sum_changed();
        }

        function buildProfiles() {
            var chk = '';
            for (var i = 0; i < Profiles.length; i++) {
                chk += '<label><input type="checkbox" id="prof' + Profiles[i].id +
                          '" name="prof[' + Profiles[i].id + ']">' + Profiles[i].name + '</label>';
            }
            $('#profiles').append(chk);
        }
        function buildRisk() {
            var chk = '';
            for (var i = 0; i < Risk.length; i++) {
                chk += '<label><input type="checkbox" id="risk' + Risk[i].id +
                          '" name="risk[' + Risk[i].id + ']">' + Risk[i].name + '</label>';
            }
            $('#risk').append(chk);
        }
        function buildElements() {
            var holder = $('#elems');
            for (var i=0; i < Elems.length; i++) {
                chkId = Elems[i].id.replace(/./i, "state_$&");
                el = '<div><input type="checkbox" id=' + chkId + ' onchange="elem_state_changed(this.id, this.checked)"/><label for=' + chkId + '>' +  Elems[i].name + '</label>' +
                    '<input class="elvalue" id=' + Elems[i].id.replace(/./i, "value_$&") + ' name=' +
                    Elems[i].id.replace(/./i, "$&") + ' type="number" min="0.0" max="0.95" value="0.5" step="0.05" ' +
                    ' disabled onchange="sum_changed()"/></div>';
                holder.append(el);
            }
        }
        function ReportDataToControls() {
            $('#firstname').val(Report.firstName);
            $('#lastname').val(Report.lastName);
            $('#email').val(Report.email);
            $('#notes').val(Report.note);
            $('#usernotes').val(Report.userNote);
            // check the elements we need
            for (var i=0; i < Report.elems.length; i++) {
                var elemId = Report.elems[i].id;
                $('#state_' + elemId).prop('checked', true);
                $('#value_' + elemId).prop('value', Report.elems[i].data);
            }
            // check the risk items
            for (i=0; i < Report.risk.length; i++) {
                $('#risk' + Report.risk[i].id).prop('checked', true);
            }    
            // check the profiles
            for (i=0; i < Report.profiles.length; i++) {
                $('#prof' + Report.profiles[i]).prop('checked', true);
            } 
        }
    </script>
    <script>
        function getMaskText(obj, reportObj, xArray) {
            var textObj = findById(xArray, obj.id);
            if (!textObj) {
                textObj = obj.textData.find(
                    function (el) {
                        return (el.mask == reportObj.mask)
                    }
                );
            }
            if (textObj) {
                return textObj.data;
            } else
                return '';
        }

        function buildTables() {
            var s = '';
            newReport.profiles.forEach(
                function (profID) {
                    var p = findById(Profiles, profID);
                    var h = calcRowHeight(p.props);
                    s += '<tr><td colspan="2"><h3>' + p.name + '</h3></td></tr>';
                    s +='<tr data-id="' + p.id + '"><td><div class="chartholder" id="chart_' + p.id +
                    '" style="height: ' + h + 'px;"></div></td>' +
                    '<td class="text"><textarea name="comment[' + p.id + ']">' + getMaskText(p, newReport, newReport.xComments) +
                    '</textarea></td></tr>';
                } 
            );    
            $('#profileholder tbody').html(s).find('textarea').on('input',
                function (ev) {
                    checkTextChanged(Profiles, newReport.xComments, ev.target,
                        function(pObj) { return getMaskText(pObj, newReport, newReport.xComments); }
                    )
                }
            );
            s = '';
            newReport.risk.forEach(
                function (riskID) {
                    var r = findById(Risk, riskID);
                    s += '<tr><td><h3>' + r.name + '</h3></td></tr>';
                    s +='<tr data-id="' + r.id + '"><td class="text"><textarea name="risk[' + r.id + ']">' +
                        getMaskText(r, newReport, newReport.xRisk) +
                        '</textarea></td></tr>';
                }
            );
            $('#riskholder tbody').html(s).find('textarea').on('input',
                function (ev) {
                    checkTextChanged(Risk, ev.target, newReport.xRisk,
                        function(rObj) { return getMaskText(rObj, newReport, newReport.xRisk); }
                    )
                }
            );
        }    
        function getElementMask(elems) {
            var firstId = 0;
            var secondId = 0;
            var mostValue = 0.0;
            for (var i=0; i < elems.length; i++) {
                if (elems[i].data > mostValue) {
                    mostValue = elems[i].data;
                    firstId = elems[i].id;
                }        
            }
            mostValue = 0.0;
            for (var i=0; i < elems.length; i++) {
                if (elems[i].id == firstId) continue; 
                if (elems[i].data > mostValue) {
                    mostValue = elems[i].data;
                    secondId = elems[i].id;
                }        
            }    
            var M = function (id) { return findById(Elems, id).mask };
            if (firstId != 0 && secondId != 0) {
                return  M(firstId) | M(secondId);
            } else return 0;    
        }    
        function makeCalcQueryParams() {
            var queryParams = [];
            newReport.elems.forEach( function (el) {
                queryParams.push('elem[' + el.id + ']=' + el.data);
            });
            newReport.profiles.forEach(
                function (profId) {
                    queryParams.push("prof[]=" + profId);  // 1 > "prof[]=1"
                } );
            return queryParams.join('&'); // 'elem[F]=0.4&elem[T]=0.6&prof[]=1'
        }
        function checkTextChanged(Collection, xCollection, control, getDataFunc) {
            // textarea -> td -> tr
            var itemId = control.parentNode.parentNode.dataset.id;
            var xText = findById(xCollection, itemId);
            if (xText) {
                $(control).toggleClass('textChanged', (xText.data != control.value.replace(/"/g, "&quot;")));
            } else {
                var obj = Collection.find(
                    function (el) {
                        if (el.id == itemId)
                            return true;
                    });
                if (obj) {
                    $(control).toggleClass('textChanged', (getDataFunc(obj) != control.value.replace(/"/g, "&quot;")));
                }
            }
        }
        function controlsToReportData() {
            newReport.elems = [];
            newReport.mask = 0;
            var elemsEnabled = $('#elems input[type="number"]:enabled');
            elemsEnabled.each( function () {
                var elem = {};
                elem.id = this.id.replace('value_', '');
                elem.data = Number(this.value);
                newReport.elems.push(elem);
                //newReport.mask |= findById(Elems, elem.id).mask;
            });
            newReport.mask = getElementMask(newReport.elems);
            newReport.profiles = [];
            $('#profiles :checkbox:checked').each(
                function () {
                    newReport.profiles.push(parseInt(this.id.replace('prof', '')));  // "prof1" > 1
                }
            );
            newReport.risk = [];
            $('#risk :checkbox:checked').each(
                function () {
                    newReport.risk.push(parseInt(this.id.replace('risk', '')));  // "risk1" > 1
                }
            );
            newReport.xRisk = [];
            newReport.xComments = [];
            if (Report.mask == newReport.mask) {
                newReport.xRisk = cloneArray(Report.xRisk);
                newReport.xComments = cloneArray(Report.xComments);
            }
            $('#profileholder .textChanged').each( function () {
                var itemId = this.parentNode.parentNode.dataset.id;
                obj = findById(newReport.xComments, itemId);
                if (!obj) {
                    obj = {}; obj.id = itemId; obj.data = this.value.trim();
                    newReport.xComments.push(obj);
                }
            });
            $('#riskholder .textChanged').each( function () {
                var itemId = this.parentNode.parentNode.dataset.id;
                obj = findById(newReport.xRisk, itemId);
                if (!obj) {
                    obj = {}; obj.id = itemId; obj.data = this.value.trim();
                    newReport.xRisk.push(obj);
                }
            });

            newReport.firstName = $('#firstname').val().trim();
            newReport.lastName = $('#lastname').val().trim();
            newReport.email = $('#email').val().trim();
            newReport.note = $('#notes').val().trim();
        }
        function generateChartsAndText() {
            var query = makeCalcQueryParams();
            var chartData = [];
            $.get('calc.php?' + query, function (answer, status) {
                if (status == 'success') {
                    chartData = JSON.parse(answer);
                    chartData.forEach( function(obj) {
                        //console.log(obj.data[0]);
                        //obj.data[0].reverse(); 
                        obj.names = [];
                        var p = findById(Profiles, obj.id);
                        p.props.forEach( function (prop) { obj.names.push(prop.name); } );
                        //obj.names.reverse();
                    });
                    buildTables();
                    readyCharts(chartData);
                }
            });
        }    
    </script>
    <script>
        $(document).ready( function(){
            $.jqplot.config.enablePlugins = true;
            buildProfiles();
            buildRisk();
            buildElements();
            ReportDataToControls();
        } );
    </script>
    <script> /*
        function transformElemsForPost() {
            var res = [];
            newReport.elems.forEach( function (el) {

            });
            return res;
        } */

        function generatePostObject(res) {
            res.<?=Report::PARAM_FIRSTNAME?> = newReport.firstName;
            res.<?=Report::PARAM_LASTNAME?> = newReport.lastName;
            res.<?=Report::PARAM_EMAIL?> = newReport.email;
            res.<?=Report::PARAM_ID?> = newReport.id;
            res.<?=Report::PARAM_ELEMS?> = cloneArray(newReport.elems);
            res.<?=Report::PARAM_NOTES?> = newReport.note;
            res.<?=Report::PARAM_ORDERID?> = newReport.orderId;
            res.<?=Report::PARAM_RISK?> = cloneArray(newReport.risk);
            res.<?=Report::PARAM_XRISK?> = cloneArray(newReport.xRisk);
            res.<?=Report::PARAM_XCOMMENTS?> = cloneArray(newReport.xComments);
            res.<?=Report::PARAM_PROFILES?> = cloneArray(newReport.profiles);
            return res;
        }
        function openUserPage() {
            controlsToReportData();
            var postObj = generatePostObject({});
            console.log(postObj);
            $.post('savereport.php', postObj,
                function(data, status){
                    if (status == 'success') {
                        console.log(data);
                        location.assign('orders.php');
                        /*
                        var newId = parseInt(data);
                        Report.id = newId;
                        $('#userpagelink').prop('href', 'report.php?id=' + newId).click();
                        */
                    }
                }
            );
        }
    </script>
</head>
<body>
<section id="options">
    <fieldset id="profiles"><legend>Срезы</legend>
        <button type="button" onclick="$('#profiles :checkbox').prop('checked', true);">выбрать все</button>
        <button type="button" onclick="$('#profiles :checkbox').prop('checked', false);">снять все</button>
    </fieldset>
    <fieldset id="risk"><legend>Потенциал/риски</legend>
        <button type="button" onclick="$('#risk :checkbox').prop('checked', true);">выбрать все</button>
        <button type="button" onclick="$('#risk :checkbox').prop('checked', false);">снять все</button>
    </fieldset>
    <fieldset id="persona" class="pers_data"><legend>Персональные данные</legend>
        <label>Имя:<input id="firstname" type="text" name="fname" placeholder="Иван" /></label>
        <label>Фамилия:<input id="lastname" type="text" name="lname" placeholder="Иванов"/></label>
        <label>Email:<input id="email" type="email" name="email" placeholder="test@test.net"/></label>
    </fieldset>
    <fieldset id="elems" class="pers_data"><legend>Элементы</legend>
        <label id="wrong_sum">0</label>
    </fieldset>
    <fieldset id="notesholder"><legend>Примечания</legend>
        <label for="notes">Мои</label>
        <textarea id="notes" ></textarea>
        <label for="usernotes">Клиентские</label>
        <textarea id="usernotes" readonly></textarea>
    </fieldset>
    <button class="bigbutton" onclick="controlsToReportData(); generateChartsAndText(newReport)">Рассчитать</button>
    <a id="userpagelink" target="_blank" href="report.php"></a>
    <button class="bigbutton" onclick="openUserPage()">Сохранить</button>
</section>
<section id="output">
    <fieldset><legend>Результаты расчета</legend>
        <table id="profileholder">
            <thead>
                <tr><th colspan="2"><h2>Данные и комментарии по срезам</h2><th></tr>
            </thead>
            <tbody></tbody>
        </table>
        <table id="riskholder">
            <thead>
            <tr><th colspan="2"><h2>Потенциал и риски</h2><th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </fieldset>
</section>
</body>
</html>
