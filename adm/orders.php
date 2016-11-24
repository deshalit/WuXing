<?php
require_once("../order.const.php");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Новые заявки</title>
    <style>
        table {
            border-spacing: 0;    
        }    
        td {
            padding-left: 10px;
            padding-right: 10px;            
        }    
        tbody td {
            font-family: monospace;
            font-size: large;
            text-align: center;
            font-weight: bold;
        }
        tbody > tr:hover {
            background-color:  blanchedalmond;
            cursor: pointer;
        }
/*        
        tbody > tr > td: hover { 
            border-style: solid;
            border-width: 1px;
            border-color: black;
        }
*/        
        td.avatars:hover { border-style: none; }
        img {
            margin-left: 5px;
            margin-right: 5px;
        }    
        header {
           margin: 20px;
        }      
        header input {
            font-size: large;
            cursor: pointer;   
        }    
    </style>
    <script src="../lib/jquery.min.js"></script>
    <script>
        function deleteOrders() {
            sentenced = $('tbody input:checked');
            if (sentenced.length > 0) {
                if (!confirm("Вы хотите удалить " + sentenced.length + " заявок?")) {
                    return;
                }        
            }    
            sentenced.each( function (index, el) {  
                var row = el.parentNode.parentNode;
                var orderId = row.dataset.id;
                $.get('deleteorder.php?id=' + orderId, function(data, status){
                    if (status == 'success') {
                        row.remove();
                    }
                });    
            } );
        }    
        function getStatusText(status) {
            switch (status) {
                case 0: return 'ожидает оплаты';
                case 10: return 'оплачен, не выдан';
                case -1: return 'отвергнут';
                case 100: return 'выдан заказчику';
            default: return 'код ' + status;    
            }        
        }    
        function selectAll() {
           $('tbody input').prop('checked', $('thead input').prop('checked'));    
        }    
        function writeTableBody(orders, maxImageCount) {
            if (orders.length == 0) return;
            text = '';
            for (var i=0; i < orders.length; i++) {
                order = orders[i];
                text += '<tr data-id="' + order.id + '"><td><input type="checkbox"></td><td>' + order.id + '</td><td>' + 
                        order.dateTime + '</td><td>' + order.promo + '</td><td>' + getStatusText(order.status) + '</td><td>' +
                        order.name + '</td><td>' + order.lastName + '</td><td>' + order.targetName + '</td><td>' + 
                        order.email + '</td><td>' + order.note + '</td><td><td class="avatars">';
                for (var j=0; j < order.images.length; j++) {
                    var img = order.images[j];        
                    text += '<a href="getphoto.php?id=' + img.id + '" target="_blank"><img src="data:' + img.imageType + ';base64,' + img.image + '"/></a>';
                }    
                text += '</td></tr>';
            }    
            $('#orders tbody').html(text).find('tr').click(
                function (e) {
                //console.log(e);
                        if (e.target.tagName != 'IMG' && e.target.tagName != 'INPUT') {
                             $('#report').prop('href', 'newreport.php?<?=PARAM_ORDER_ID?>=' + this.dataset.id)[0].click();
                        }
                }
            );
        }    
    </script>
    <script>
        $(document).ready( function(){
            $.get('getneworders.php', function (xml, status) {
                if (status == 'success') {
                    var Orders = [];
                    var maxImageCount = 0;
                    $(xml).find("<?=NODE_ORDER_ITEM?>").each( function () {
                        obj = {};
                        obj.id = $(this).find("<?=NODE_ORDER_ID?>").text();
                        obj.status = parseInt($(this).find("<?=NODE_ORDER_STATUS?>").text());
                        obj.name = $(this).find("<?=NODE_ORDER_NAME?>").text();
                        obj.lastName = $(this).find("<?=NODE_ORDER_LASTNAME?>").text();
                        obj.email = $(this).find("<?=NODE_ORDER_EMAIL?>").text();
                        obj.dateTime = new Date($(this).find("<?=NODE_ORDER_DATE?>").text()).toLocaleString();
                        obj.note = $(this).find("<?=NODE_ORDER_NOTE?>").text();
                        obj.promo = $(this).find("<?=NODE_ORDER_PROMO?>").text();
                        obj.targetName = $(this).find("<?=NODE_ORDER_TARGETNAME?>").text();
                        obj.eyes = $(this).find("<?=NODE_ORDER_EYES?>").text();
                        obj.hair = $(this).find("<?=NODE_ORDER_HAIR?>").text();
                        obj.height = $(this).find("<?=NODE_ORDER_HEIGHT?>").text();
                        var imageNodes = $(this).find("<?=NODE_ORDER_IMAGE?>");
                        if (imageNodes.length > maxImageCount) maxImageCount = imageNodes.length;
                        obj.images = [];
                        imageNodes.each( function () { 
                            imgObj = {};
                            imgObj.id = $(this).prop('id');
                            imgObj.imageType = $(this).prop('type');
                            imgObj.image = $(this).text();
                            obj.images.push(imgObj);
                        } );
                        Orders.push(obj);
                    } );   
                    writeTableBody(Orders, maxImageCount);    
                }    
            });
        } );
    </script>
</head>
<body>
    <a id="report" style="display: none" target="_blank"></a>
    <header>
        <input type="button" value="удалить отмеченные" onclick="deleteOrders()"/>
    </header>
    <table id="orders">
        <thead>        
            <tr>
                <th><input type="checkbox" onclick="selectAll()"></th>
                <th>Номер</th>
                <th>Дата создания</th>
                <th>Промо</th>
                <th>Статус</th>
                <th>Имя заказчика</th>
                <th>Фамилия заказчика</th>
                <th>Имя</th>
                <th>Email</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</body>
</html>    