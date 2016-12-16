<?php
function getHTMLhead($title, $cssPath, $links = '') {
$code = "<!DOCTYPE html>\n<html>\n<head>\n  <meta charset='utf-8' />\n";
$code .= '  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>' . "\n";
$code .= "  <title>$title</title>\n";
$code .= '  <link href="' . $cssPath . '/basic.css" rel="stylesheet" type="text/css" />' . "\n";
$code .= '  <link href="' . $cssPath . '/header.css" rel="stylesheet" type="text/css" />' . "\n";
$code .= $links . "\n";
$code .= '  <link href="' . $cssPath . '/basic-media.css" rel="stylesheet" type="text/css" />' . "\n";
$code .= '  <link href="' . $cssPath . '/header-media.css" rel="stylesheet" type="text/css" />' . "\n" .
          "</head>\n";    
return $code;  
}

function getTemplateHeader() {
return <<<'HEADER'
<header class="headblock">
    <a href="http://5psy.biz/" alt="Технологии оценки и реализации потенциала личности" title="Перейти на сайт 5psy.biz"><img src="/img/logo.png" /></a>
    <h1>OnLine сервис диагностики потенциала человека</h1>
    <a id='imgwrap' href="http://5psy.biz/" target="_blank"><img class="headblock" src="/img/home.png"/></a>
    <a class="headblock" href="http://5psy.biz/kontakty" target="_blank">Контакты</a>
</header>
HEADER;
}
?>