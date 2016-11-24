<?php
require_once ("dict.class.php");
require_once ('group.class.php');
require_once ("calc.php");
require_once ("wxChart.php");

include_once ("dictxml.inc.php");

class wxReport {
    private $dict;

    public $profileIDs = [];
    public $elems = [];
    public $risks;
    public $comments;
    public $firstName = '';
    public $lastName = '';
    public $email = '';
    public $data = null;

    public function __construct(Dictionary $dict) {
        $this->dict = $dict;
    }

    public function analyze_params($input) {
        $this->elems = [];
        $this->comments = [];
        $this->risks = [];
        $this->profileIDs = [];

        foreach (array_keys(Dictionary::$elemNames) as $key) {
            if (!empty($input[$key])) {
                if (!$this->elems[$key]) {
                    $this->elems[$key] = $input[$key];
                    if (count($this->elems) == 2) break;
                }
            }
        }
        $this->profileIDs = explode(',', $input[PARAM_PROFILES]);
        $this->firstName = $input[PARAM_FIRSTNAME];
        $this->lastName = $input[PARAM_LASTNAME];
        $this->email = $input[PARAM_EMAIL];

        if ((count($this->profileIDs) > 0) and validate_elements($this->elems)) {
            //var_dump($this->profileIDs);
            //$elKeys = array_keys($this->elems);
            //$hash = Dictionary::get_elem_hash2($elKeys[0], $elKeys[1]);
            foreach ($this->profileIDs as $profileId) {
                //$comm = $this->dict->comments[$profileId][$hash];
                foreach($this->dict->get_profile_properties($profileId) as $propId) {
                    $this->data[$profileId][$propId] = calculate_prop($this->dict, $this->elems, $propId);
                }
            }
        }
    }

    public function initJSComment() {
        $elKeys = array_keys($this->elems);
        $hash = Dictionary::get_elem_hash2($elKeys[0], $elKeys[1]);
        $cmStrings = Array();
        foreach ($this->profileIDs as $profileID) {
            $itemStr = ' { id: ' . $profileID . ', data: "' . htmlspecialchars($this->dict->comments[$profileID][$hash],
                           ENT_COMPAT | ENT_HTML5, 'UTF-8') . '" }';
            array_push($cmStrings, $itemStr);
        }
        //var_dump($cm);
        return  ' var Comment = [' . implode(',', $cmStrings) . '];';
    }
    public function initJSrisk() {
        $elKeys = array_keys($this->elems);
        $hash = Dictionary::get_elem_hash2($elKeys[0], $elKeys[1]);
        $createRiskStrings = Array();
        foreach ($this->dict->risk as $riskID=>$riskInfo) {
            $riskName = $riskInfo[0];
            $riskText = htmlspecialchars($riskInfo[1][$hash], ENT_COMPAT | ENT_HTML5, 'UTF-8');
            $itemStr = ' { id: ' . $riskID . ', name: "' . $riskName . '", data: "' . $riskText . '" }';
            array_push($createRiskStrings, $itemStr);
        }
        return ' var Potent = [' . implode(',', $createRiskStrings) . '];';
    }
}

if (!$report) {
    $report = new wxReport($dictionary);
}
if ($_SERVER["REQUEST_METHOD"] == 'GET') {
    $report->analyze_params($_GET);
} else exit();

if (!$wxChart) {
    $wxChart = new wxChart($dictionary, $report->profileIDs, $report->data);

}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создание персонального отчета</title>
    <style>
        .chartholder { /*float: left;*/
            width: 450px;
            min-width: 200px;
            min-height: <?=wxChart::MIN_CHART_HEIGHT?>px;
            /*margin: 0 10px;*/
        }
        #datatable tbody td {
            min-height: <?=wxChart::MIN_CHART_HEIGHT?>px;
        }
        td[colspan] {
            text-align: center;
            padding-top: 30px;
        }
        h3 {

            display: inline;
        }
        table tbody td.text {
            width: 300px;
            padding-bottom: 20px;
            padding-top: 10px;
        }
        section {
            float: left;
            margin: 10px 10px;
        }
        textarea {
            height: 100%;
            width: 100%;
            display: block;
            font-size: large;
            background-color: aliceblue;
            border-style: groove;
            border-color: cornflowerblue;
        }
        #riskholder textarea {
            height: 200px;
        }
        .textChanged {
            background-color: antiquewhite;
        }
        #formsave label {
            width: 100px;
        }
        .smart {
            display: none;
        }
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
        <?php   echo $wxChart->initCharts() . "\n";
                echo $report->initJSComment() . "\n";
                echo ' var mainTitle = "' . Dictionary::$elemNames[array_keys($report->elems)[0]] . '+' .
                    Dictionary::$elemNames[array_keys($report->elems)[1]] . '";' . "\n";
                echo $report->initJSrisk() . "\n";
        ?>
        function checkTextChanged(Collection, control) {
            var itemId = control.parentNode.parentNode.dataset.id;
            if ( obj = Collection.find(function (el) { if (el.id == itemId) return true; } ) ) {
                $(control).toggleClass('textChanged', (obj.data != control.value.replace(/"/g, "&quot;")));
            }
        }
        function generateProfileContent() {
            code = '';
            for (var i=0; i < Profiles.length; i++) {
                h = calcRowHeight(Profiles[i].names.length);
                pid = Profiles[i].id;
                //console.log("h = " + h);
                code += '<tr><td colspan="2"><h3>' + Profiles[i].name + '</h3></td></tr>';
                code +='<tr data-id="' + pid + '"><td><div class="chartholder" id="chart_' + pid +
                    '" style="height: ' + h + 'px;"></div></td>' +
                    '<td class="text"><textarea name="comment[' + pid + ']">' + Comment[i].data +
                    '</textarea></td></tr>';
            }
             $('#datatable tbody').html(code);
             $('#datatable tbody textarea').on('input', function (ev) {checkTextChanged(Comment, ev.target); });
        }
        function generateRiskContent() {
            code = '';
            for (var i=0; i < Potent.length; i++) {
                /*
                code +='<tr data-id="' + Potent[i].id + '"><td><span>' + Potent[i].name + '</span></td>' +
                    '<td class="text"><textarea>' + Potent[i].data + '</textarea></td></tr>';
                */
                code += '<tr><td><h3>' + Potent[i].name + '</h3></td></tr>' +
                        '<tr data-id="' + Potent[i].id + '"><td class="text">' +
                        '<textarea name="risk[' + Potent[i].id + ']">' + Potent[i].data +
                        '</textarea></td></tr>';
            }
            $('#risktable tbody').html(code);
            $('#risktable tbody textarea').on('input', function (ev) { checkTextChanged(Potent, ev.target); });

        }
    </script>
    <script>
        $(document).ready( function(){
            $("h1").html(mainTitle);
            generateProfileContent();
            generateRiskContent();
            readyCharts(Profiles);
        } );
    </script>
    <script>
        function doSubmit() {
            $('#formsave .smart').remove();
            $('textarea.textChanged').each( function (x, el) {
               $('#formsave').append('<textarea class="smart" name="' + el.name + '">' + el.value + '</textarea>');
            });
            $('#formsave').submit();
        }
    </script>
</head>
<body>
    <header><h1></h1></header>
    <section id="toolbar">
        <form id="formsave" action="makereport.php" method="post">
            <label> Имя:<input type="text" name="<?=PARAM_FIRSTNAME?>" required /></label>
            <label> Фамилия:<input type="text" name="<?=PARAM_LASTNAME?>" required /></label>
            <label> Email:<input type="email" name="<?=PARAM_EMAIL?>" required /></label>
            <button type="button" onclick="doSubmit()">Сохранить</button>
            <input name="mode" value="save" hidden />
            <input name="id" value="0" hidden />
        </form>
    </section>
    <section id="profileholder">
        <table id="datatable">
            <thead>
                <tr><td colspan="2"><h2>Данные и комментарии по срезам</h2><td></tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>
    <section id="riskholder">
        <table id="risktable">
            <thead>
                <tr><td colspan="2"><h2>Потенциал и риски</h2></td></tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>
</body>
</html>