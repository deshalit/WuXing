<?php
function getHTMLhead($title = 'OnLine сервис диагностики потенциала человека', $links = '') {
$code = "<!DOCTYPE html>\n<html>\n<head>\n  <meta charset='utf-8' />\n";
$code .= '  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>' . "\n";
$code .= "  <title>$title</title>\n";
$code .= '  <link href="css/basic.css" rel="stylesheet" type="text/css" />' . "\n";
$code .= '  <link href="css/header.css" rel="stylesheet" type="text/css" />' . "\n";
$code .= $links . "\n";
$code .= '  <link href="css/basic-media.css" rel="stylesheet" type="text/css" />' . "\n";
$code .= '  <link href="css/header-media.css" rel="stylesheet" type="text/css" />' . "\n" .
          "</head>\n";    
return $code;  
}
    
function getTemplateHeader() {
return <<<'HEADER'
<header class="headblock">
    <img src="/img/logo.png" />
    <h1>OnLine сервис диагностики потенциала человека</h1>
    <a id='imgwrap' href="http://5psy.biz/" target="_blank"><img class="headblock" src="/img/home.png"/></a>
    <a class="headblock" href="http://5psy.biz/kontakty" target="_blank">Контакты</a>
</header>
HEADER;
}
?>