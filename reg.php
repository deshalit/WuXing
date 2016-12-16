<?php
//require_once('tpl.header.php');
require_once("class/dictionary/dictionary.class.php");
require_once("class/order/common/order.class.php");
require_once("class/order/orderregister.class.php");
include_once("class/dictionary/dictxml.inc.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderMan = new OrderRegister();
    $options = new UploadOptions();
    $res = '';
    if ($order = $orderMan->createOrder($options)) {
        if ($newId = $orderMan->saveOrder($order)) {
            $res = 'OK.' . $newId;
        }    
    }
    if ($res == '') {
        $res = 'ER.' . $orderMan->getError();
    }   
    exit($res);
}
function buildProfiles(Dictionary $dictionary) {
    foreach($dictionary->profiles as $pKey=>$pInfo) {
       echo '<li><label><input type="checkbox" id="prof' . $pKey . '" >' . $pInfo[0] . '</label></li>' . "\n";     
    }    
}    

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявка</title>
    <style>
        label, input, textarea {
            display: block; 
        }    
        input, textarea {
            width: 100%;    
            margin-bottom: 20px;
            font-size: large;
        }    
        ul {
            list-style-type: none;
            letter-spacing: 1px;
            margin-left: 100px;
        } 
/*        
        a {
            font-size: large;  
            text-decoration: none;
            border-color: burlywood;
            border-width: medium;
            display: inline-block;
            border-style: solid;
            margin-top: 5px;
            margin-bottom: 20px;
            border-radius: 10px;
            padding: 5px 10px;
            background-color: ivory;
        }    
*/        
        form {
            font-family: "Trebuchet MS",Arial,Helvetica,sans-serif;
            width: 50%;
            max-width: 600px;
            margin: auto;
            border-width: medium;
            border-style: outset;
            padding: 15px 17px 0 12px;
            background-color: #EAEBDB;
            border-radius: 5px;
        }    
        span.param {
            font-size: large; 
            margin-left: 5px;
            margin-right: 5px;
            font-weight: bold;
        }    
        p { 
            margin: auto;
            margin-top: 50px;
            font-size: large;
            font-size: larger;
            border-style: solid;
            background-color: beige;
            border-width: thin;
            width: 500px;
            line-height: 1.5;
            border-radius: 7px;
            padding: 50px;
            border-color: #a5b99c;
            font-family: "Trebuchet MS",Arial,Helvetica,sans-serif;
        }
        input[type=submit], input[type=button] { 
            height: 40px; 
        }
        table {
            border-spacing: 0px;    
            width: 100%;
        }    
        table label { display: inline; }
        
        .c1 {
            width: 50%;
        }    
        
        span.note {
            font-size: small;   
            padding-left: 5px;
        }    
        
        td:last-of-type {
            padding-left: 4px;
        }    
        tr > td:first-of-type {
            padding-right: 4px;
        }    
        .c2 {
            padding-left: 4px;
            padding-right: 4px;
        }
/*        
        #warnManyFiles {
            color: red;
            font-weight: bold;
            display: block;
            display: none;
        }   
*/        
        .file_input {
            display: none;
        }   
        #filecount {
             margin-bottom: 5px;   
        }    
        textarea {
            margin-top: 10px;
        }    
        #promo {
            width: 90px;
        }    
        #promocode > * {
            display: inline;   
        }    
        #profiles {
            display: block;
            clear: both;    
            padding-top: 20px;
        }    
        #profiles label {
            cursor: pointer;   
        }    
        #profiles > * {
           display: inline-block;   
        }    
        #profiles input {
            width: 20px;   
            display: inline-block;  
            cursor: pointer;    
        }    
        #ticket {
            background-color: floralwhite;
            padding: 5px;
            color: maroon;
            border-radius: 20%;
            border-width: thin;
            border-color: darkkhaki;
            border-style: groove;
        }    
    </style>
   <style>
        .foto {
            width: 100px;
            height: 100px;
            display: none;
        }    
        .fotoholder {
            cursor: pointer;
            background-color: lightgray;
            border-width: 1px;
            border-style: solid;
            border-radius: 7px; 		
            min-height: 100px;
            min-width: 100px;
            float: left;
            color: black;
            margin-right: 20px;
        }    
        .fotoholder label {
            display: block;
            padding: 10px;
            cursor: pointer;
            line-height: 100px;
            height: 100px;
        }    
        #holder1 {
            margin-left: 60px;   
        }    
        a.remove {
            display: none;    
            clear: both;
            background-color: transparent;
            color: black;
            text-align: center;
            margin-bottom: 5px;
        }    
        progress {
            display: none;    
            width: 100px;
        } 
        #ptotal {
            display: inline-block;
            clear: both;
            width: 100%;
            margin-top: 5px;
        }    
    </style>    
    <script src="lib/jquery.min.js"></script>
   
    <script>
        $(document).ready( function(){
            $('#ptotal').hide();
        } );
        
        const MAX_FILE_COUNT = <?=UploadOptions::MAX_PHOTO_COUNT?>; 
        
        function getFileCount() {
            var fCount = 0;
             //$('#foto input').each( function (index, el) { fCount += el.files.length; } );
            for (var i=0; i<MAX_FILE_COUNT; i++) {
                if (Canvas[i].dataUrl != '') {
                    fCount += 1;
                }        
            }     
            return fCount;
        }    
        
        function updateFileCount() {
            $('#filecount span').html(getFileCount());
        }    
        function rebuildPage(code) {
            var data = code.substr(0,2);
            if (data == 'OK') {
                var newID = code.substr(3, code.length);
                var s = '<p>Данные успешно внесены в реестр под номером<span id="ticket" class="param">' + newID + '</span>. ' +
                        'Пожалуйста, сохраните этот номер и обязательно укажите его в реквизитах при оплате услуги. ' +
                        'Ваша заявка будет обработана в течение 3 рабочих дней с момента получения оплаты. ' +
                        'Большое Вам спасибо!</p>'; 
            } else {
                    s = '<p class="error">Приносим извинения за неудобства: в процессе записи Вашей заявки произошел сбой. Пожалуйста, повторите попытку позже или обратитесь к администратору проекта. Спасибо.</p>'
            }
            $('body').html(s);
        }    
        function onformsubmit() { 
            fcname="<?=OrderRegister::PARAM_PHOTO?>[]";
            var fileCount = getFileCount(); //($('#file_input')[0].files.length > MAX_FILE_COUNT) ? MAX_FILE_COUNT : $('#file_input')[0].files.length;
            if (fileCount == 0) {
                alert('Выберите хотя бы одно фото!'); return;
            }
            var profs = $('#profiles input:checked');
            if (profs.length == 0) {
                alert('Выберите хотя бы один срез!'); return;
            }    
            var elems = $('form')[0].elements;
            for (var i=0; i<elems.length; i++) {
                if (!elems[i].reportValidity()) return;
            }              
            $('#ptotal').css('display', '');
            progress = $('#ptotal')[0];
            progress.max = MAX_FILE_COUNT;
            var fd = new FormData();
            for (var i=0; i<MAX_FILE_COUNT; i++) {
                if (Canvas[i].dataUrl != '') {
                    fd.append(fcname, //$('#file_input')[0].files[i]);
                                     dataURLToBlob( Canvas[i].dataUrl ));
                }                     
                progress.value = i+1;                  
            }    
            for (var i=0; i<elems.length; i++) {
                var elemName = elems[i].name;
                if (elemName) {
                    fd.append(elemName, elems[i].value.trim());
                }    
            }    
            profs.each( function() { 
                fd.append("<?=OrderRegister::PARAM_PROFILES?>[" + this.id.replace('prof', '') + "]", "on");
            });
            
            var xhr = new XMLHttpRequest();          
            xhr.open("POST", $('form').prop('action'), true);
            xhr.onprogress = function(event) {
                    if (event.lengthComputable) {
                        progress.max = event.total;
                        progress.value = event.loaded;
                        $('#percent').html(progress.value);
                    }
                };
            xhr.onloadstart = function(event) {
                    progress.max = 1;
                    progress.value = 1;
                    $('#percent').html("");
                };
            xhr.onloadend = function(event) {
                    //var contents = event.target.result,
                    error = event.target.error;
                    if (error != null) {
                        console.error("File could not be read! Code " + error.code);
                        $(progress).hide();
                    } else {
                        //console.log("Contents: " + contents);
                    }
                    //$(progress).hide();
                };
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                     rebuildPage(xhr.responseText); 
                }
            };
            xhr.send(fd);
        }    
        $(document).ready( function(){
            if (!window.FormData) {
               alert('К сожалению, в Вашем браузере работа данной страницы невозможна'); 
            }    
        } );        
    </script>
   <script>
        const MAX_SIZE = 800;
        var Canvas = [];
        for (var i=0; i<MAX_FILE_COUNT; i++) {
            obj = {};
            obj.canvas = document.createElement('canvas');
            obj.dataUrl = '';
            Canvas.push( obj );
        }    
        function acceptPhoto(file, index){
            // Read in file
            //var file = event.target.files[0];

            // Ensure it's an image
            if(file.type.match(/image.*/)) {
                console.log('An image has been loaded');

                // Load the image
                var reader = new FileReader();
                
                progress = $('#p' + index)[0];
                reader.onprogress = function(event) {
                    if (event.lengthComputable) {
                        progress.max = event.total+2;
                        progress.value = event.loaded;
                    }
                };
                reader.onloadstart = function(event) {
                        progress.max = 1;
                        progress.value = 1;
                        $(progress).css('display', 'block');
                };
                reader.onloadend = function(event) {
                    //var contents = event.target.result,
                    error = event.target.error;
                    if (error != null) {
                        console.error("File could not be read! Code " + error.code);
                        $(progress).hide();
                    } else {
                        //console.log("Contents: " + contents);
                    }
                    //$(progress).hide();
                };
                
                reader.onload = function (readerEvent) {
                    var image = new Image();
                    image.onload = function (imageEvent) {

                        // Resize the image
                        var canvas = Canvas[index-1].canvas; 
                            max_size = MAX_SIZE,// TODO : pull max size from a site config
                            width = image.width,
                            height = image.height;
                        if (width > height) {
                            if (width > max_size) {
                                height *= max_size / width;
                                width = max_size;
                            }
                        } else {
                            if (height > max_size) {
                                width *= max_size / height;
                                height = max_size;
                            }
                        }
                        canvas.width = width;
                        canvas.height = height;
                        canvas.getContext('2d').drawImage(image, 0, 0, width, height);
                        Canvas[index-1].dataUrl = canvas.toDataURL('image/jpeg', .6);
                        progress.value = progress.value + 1;
                        //var resizedImage = dataURLToBlob(dataUrl);
                        progress.value = progress.value + 1;
                        $(progress).hide();
                        updateFileCount();
                    /*    
                        $.event.trigger({
                            type: "imageResized",
                            blob: resizedImage,
                            url: dataUrl
                        });
                    */    
                    }
                    image.src = readerEvent.target.result;
                }
                reader.readAsDataURL(file);
            }
        };    
        /* Utility function to convert a canvas to a BLOB */
        var dataURLToBlob = function(dataURL) {
            var BASE64_MARKER = ';base64,';
            if (dataURL.indexOf(BASE64_MARKER) == -1) {
                var parts = dataURL.split(',');
                var contentType = parts[0].split(':')[1];
                var raw = parts[1];

                return new Blob([raw], {type: contentType});
            }

            var parts = dataURL.split(BASE64_MARKER);
            var contentType = parts[0].split(':')[1];
            var raw = window.atob(parts[1]);
            var rawLength = raw.length;

            var uInt8Array = new Uint8Array(rawLength);

            for (var i = 0; i < rawLength; ++i) {
                uInt8Array[i] = raw.charCodeAt(i);
            }

            return new Blob([uInt8Array], {type: contentType});
        }
    </script>    
   <script>
        //var objURL = [];
        function changePhoto(sender) {
            var holderid = sender.parentElement.id;
            //console.log(holderid);
            $('#' + holderid).find('input').click();
            //$('#file_input').attr('data-holder', holderid).click();           
        }    
        function changeFile(input) {
            var holder = $('#' + input.parentElement.id)[0];
            if (input.files.length == 1) {
                var imgid = holder.id.replace('holder', 'foto');
                var objURL = window.URL.createObjectURL(input.files[0]);
                $('#' + imgid).on('load', function() {
                    window.URL.revokeObjectURL(objURL); }).attr('src', objURL).show();
                $(holder).find('label').hide();
                $(holder).find('a').css('display', 'block');
                acceptPhoto(input.files[0], parseInt(imgid.replace('foto', '')));
            } 
        }
        function removePhoto(holderId) {
            console.log();
            var holder = $('#' + holderId);
            var img = holder.find('img');
            $(img).attr('src', '').hide();
            holder.find('label').css('display', 'block');
            holder.find('a').hide();
            var no = parseInt(holderId.replace('holder', '')) -1;
            Canvas[no].dataUrl = '';
            updateFileCount();
        }    
    </script>    
</head>
<body>
    <form enctype="multipart/form-data" action="reg.php" method="POST">
        <table>
            <tr><td><label for="fname">Имя заказчика:</label></td><td><label for="lname">Фамилия заказчика:</label></td></tr>
            <tr><td class="c1"><input id="fname" name="<?=OrderRegister::PARAM_FIRSTNAME?>" required/></td>
                <td class="c1"><input id="lname" name="<?=OrderRegister::PARAM_LASTNAME?>" required/></td></tr>
        </table>
        <table>
            <tr><td><label for="fname">Имя диагностируемого:</label></td>
                <td><label for="email">Email</label><span class="note">(на этот адрес будет выслан отчет)</span></td></tr>
            <tr><td class="c1"><input id="tname" name="<?=OrderRegister::PARAM_TARGETNAME?>" required/></td>
                <td class="c1"><input id="email" name="<?=OrderRegister::PARAM_EMAIL?>" required/></td></tr>
        </table>
        <table>
        <tr><td><label for="height">Рост:</label></td><td class="c2"><label for="eyecolor">Цвет глаз:</label></td><td><label for="haircolor">Цвет волос:</label></td></tr>
        <tr><td><input id="height" name="<?=OrderRegister::PARAM_HEIGHT?>" required/></td><td class="c2"><input id="eyecolor" name="<?=OrderRegister::PARAM_EYECOLOR?>" required/></td><td><input id="haircolor" name="<?=OrderRegister::PARAM_HAIRCOLOR?>" required/></td></tr>
        </table>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?=UploadOptions::MAX_PHOTO_UPLOAD_SIZE?>" />
              
        <section id="foto">
            <label id="filecount">Выбрано файлов:<span class="param">0</span></label>
            <script>
                for (var i=1; i<=MAX_FILE_COUNT; i++) {
                   var s = '<div class="fotoholder" id="holder' + i + '"><label onclick="changePhoto(this)" >Выбрать...</label>' +
                           '<input class="file_input" type="file" accept=".png,.jpg,.jpeg,.gif" onchange="changeFile(this)" />' + 
                           '<img class="foto" id="foto' + i + '" src="/img/sample1.png" onclick="changePhoto(this)"/>' +
                           '<progress id="p' + i + '"></progress>' +
                           '<a class="remove" onclick="removePhoto(this.parentElement.id)">убрать</a>'; 
                   $('#foto').append(s);   
                }    
            </script>
            <progress id="ptotal">Загрузка на сервер:<span class="param" id="percent">0</span>%</progress>
        </section>
        <section id="profiles">
            <label>Какие срезы Вас интересуют?</label>
            <ul><?=buildProfiles($dictionary);?></ul>
        </section>
        <textarea id="note" name="<?=OrderRegister::PARAM_NOTE?>" rows="5" placeholder="Ваши пожелания" ></textarea>
        <section id="promocode">
          <label for="promo">Для получения скидки введите номер Вашего купона:</label>
          <input id="promo" name="<?=OrderRegister::PARAM_PROMOCODE?>" size="5" pattern="[0-9]{5,5}" maxlength=5 placeholder="пять цифр"/>
        </section>  
        <input type="button" value="Отправить" onclick="onformsubmit()"/>
    </form>
</body>
</html>