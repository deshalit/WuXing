<?php
require_once('tpl.header.php');
include_once('usreport.inc.php');

$report = null;
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
   $hash = $_GET['code'];    
   if ($hash) {
       $report = $userReportManager->loadReport($hash);
   }
}    
if (!$report) {
    header('Location: http://diag.5psy.biz/');
}
$links = 
         '<script src="lib/jquery.min.js"></script>' . "\n" .
         '<script src="lib/jqplot/jquery.jqplot.min.js"></script>' . "\n" .
         '<script src="lib/jqplot/plugins/jqplot.barRenderer.min.js"></script>' . "\n" .
         '<script src="lib/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>' . "\n" .
         '<script src="lib/jqplot/plugins/jqplot.pointLabels.min.js"></script>' . "\n" .
         '<script src="lib/jqplot/plugins/jqplot.canvasOverlay.min.js"></script>' . "\n" .
         '<link rel="stylesheet" type="text/css" href="lib/jqplot/jquery.jqplot.min.css" />' . "\n" .
         '<script src="wx_chart.js"></script>' . "\n" .
         '<link rel="stylesheet" type="text/css" href="css/rpt.css"/>' . "\n" .
         '<link rel="stylesheet" type="text/css" href="css/rpt-media.css"/>' . "\n" .
         '<script>var chartData = ' . $report->generateChartData() . ';' . "\n" . 
<<<'CODE'
$(document).ready( function(){
    $.jqplot.config.enablePlugins = true;
    $(".chartholder").each( function(i, el) {
        $(this).css('height', calcRowHeight(chartData[i].names) + 'px')
    }); 
    readyCharts(chartData);
} );
</script>

CODE;
echo getHTMLhead('Заказ №' . $report->id, $links);
?>
<body>
<?=getTemplateHeader();?>
<header id="clinfo"><h2>Отчет</h2>
    <ul>
        <li>Заявка №<span class="value"><?=$report->orderId?></span></li>
        <li>Заказчик:<span class="value"><?=$report->firstName . ' ' . $report->lastName?></span></li>
        <li>Диагностируемый:<span class="value"><?=$report->targetName?></span></li>
        <li>Тип конституции:<span class="value"><?=$report->getConstitution();?></span></li>
    </ul>
<?php if ($report->orderNote) {
         echo '<div id="note"><h4>Комментарий заказчика:</h4><p>' . $report->orderNote . '</p></div>';
}?>   
<div class="br"></div>
</header>
<hr>
<section id="chart">
<?php
    $s = '';
    foreach ($report->data as $id=>$pinfo) {
        $s .= '<div class="chartholder" id="chart_' . $id . '"></div>' .
              '<div class="comm"><h4>' . $report->getProfileName($id) . '</h4>' . "\n" .
              '<p>' . $report->getProfileText($id) . '</p></div><div class="br"></div>' . "\n";
    }    
    echo $s;
?>
</section>
<?php   $riskCount = $report->getRiskCount();
        if ($riskCount > 0) {
            echo '<section id="risk"><header><h3>Потенциал и риски</h3></header>';
            $rKeys = array_keys($report->risk);
            for ($i=0; $i < ($riskCount - ($riskCount % 2)); $i++) {
                $rid = $rKeys[$i];
         //   foreach(array_keys($report->risk) as $rid) {
                echo '<section><header><h4>' . $report->getRiskName($rid) . '</h4></header><p>' . $report->getRiskText($rid) . '</p></section>';
            }
            if ($riskCount % 2 == 1) {
                $rid = $rKeys[$riskCount-1];
                echo '<section class="odd"><header><h4>' . $report->getRiskName($rid) . '</h4></header><p>' . $report->getRiskText($rid) . '</p></section>';
            }
            echo '</section>';
        }
?>            
<hr>
<footer>Спасибо, что воспользовались нашим сервисом. Чтобы задать вопрос или получить дополнительные комментарии, пройдите по ссылке &laquo;Контакты&raquo; вверху страницы и заполните контактную форму.</footer>
</body>
</html>    