<?php
  require_once('dict.class.php');
  require_once('client.inc.php');
  require_once('group.class.php');

  include_once('group.inc.php');
  include_once('groupman.inc.php');

  error_reporting(E_ALL);


    function getElementArray() {
        return '["' . implode('","', array_keys(Dictionary::$elemNames)) . '"]';
    }

/*
  class GroupViewer {
      private $dict;
      public function __construct(Dictionary $dictionary) {
          $this->dict = $dictionary;
      }
      public function viewPage() {

      }
  }
*/
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Менеджер групп</title>
    <style>
        /*table {aligh: center}*/
        /*#group tbody tr>td[data-value]:nth-child(2) { background-color: silver }*/
        td {min-width: 50px; height: 30px; text-align: center}
        td[data-value]:hover {background-color: lightyellow}
        input {background-color: aquamarine;}
        #grouplist_holder { margin-bottom: 30px; }
        tbody tr > td:first-child {background-color: #BEE4BF}
        select {
           margin: 10px;
            border: 1px solid #111;
           background: transparent;
           /*width: 150px;*/
           padding: 5px 35px 5px 5px;
           font-size: 16px;
           border: 1px solid #ccc;
           height: 34px;
           -webkit-appearance: none;
           -moz-appearance: none;
           appearance: none;
        }
    </style>
    <script src="jquery.min.js"></script>
    <!--
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.js"></script>
    -->
    <script>
        var LastGroupID = -1;
        $(document).ready( function () {

            /*
            $('#group').DataTable(
                paging: false,
                searching: false,
                ordering:  false
            );
            */
            LastGroupID = -1;
            refreshGroupList();
        } );
    </script>
    <script>
        var Elements = <?php echo getElementArray()?>;
    
        function calcElement(index) {
            var res = 0;
           $('#group tbody td[data-value]:nth-child(' + index + ')').each( 
                function (i, el) { res += parseInt(el.dataset.value); } 
           );
           return res;
        }    
    
        function refreshGroupList() {
            var select = document.getElementById('grouplist');
            /*
            var groupID;
            if (select.selectedOptions.length == 0) { 
                groupID = 0;
            } else {
                groupID = parseInt(select.selectedOptions[0].getAttribute("value"));
            } 
            */            
            select.innerHTML = '';

            var rqst = new XMLHttpRequest();
            rqst.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        buildGroupList(this.responseXML);
                        //if (LastGroupID < 0) {
                            groupSelected();    
                        //}
                    }
            }
            rqst.open("GET", "grouplist.php", true);
            rqst.send();
        }
    
        function groupSelected() {
            //global LastGroupID;
            $("#group tbody").html("<tr><td colspan=6>Нет данных</td></tr>");
            select = document.getElementById('grouplist');
            if (select.selectedOptions.length > 0) {
                LastGroupID = parseInt(select.selectedOptions[0].getAttribute("value"));
            } else {
                if (LastGroupID < 0) { return; }
            }

            select.disabled = true;
            //console.log('LastGroupId = ' + LastGroupID);
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    buildMemberList(this.responseXML);
                }
                select.disabled = false;
            };
            xmlhttp.open("GET", "group.php?id=" + LastGroupID, true);
            xmlhttp.send();
        }

        function cellDataDoubleClick() {
            OriginalContent = $(this).text();
            //console.log(OriginalContent);
            w = $(this).width()-5;
            $(this).addClass("cellEditing");
            $(this).html("<input type='number' value='" + OriginalContent + "' />");
            inp = $(this).children().first();
            inp.css({width: w});
            inp.focus();
            inp.keyup(function (e) {
                if (e.which == 13) {
                    CommitValue($(this));
                } else
                if (e.which == 27) {
                    Rollback($(this));
                }
            });
            inp.blur(function(){
                Rollback(inp);
            });
            //$(this).find('input')
            inp.click(function(e){
                e.stopPropagation();
            });
        }

        function cellNameDoubleClick() {
            OriginalContent = $(this).text();
            //console.log(OriginalContent);
            w = $(this).width()-7;
            $(this).addClass("cellEditing");
            $(this).html("<input type='text' value='" + OriginalContent + "' />");
            inp = $(this).children().first();
            inp.css({width: w});
            inp.focus();
            inp.keyup(function (e) {
                if (e.which == 13) {
                    CommitName($(this));
                } else
                if (e.which == 27) {
                    Rollback($(this));
                }
            });
            inp.blur(function(){
                Rollback(inp);
            });
            //$(this).find('input')
            inp.click(function(e){
                e.stopPropagation();
            });
        }
        function buildGroupList(xml) {
            var groupNodes = xml.getElementsByTagName('<?=NODE_GROUPLIST_GROUP?>');
            var controlText = "";
            var node;
            var groupName;
            var gID;
            //global LastGroupID;
            for (var i = 0; i < groupNodes.length; i++) {
                node = groupNodes[i];
                gID = node.getElementsByTagName('<?=NODE_GROUPLIST_ID?>')[0].childNodes[0].nodeValue;
                groupName = node.getElementsByTagName('<?=NODE_GROUPLIST_NAME?>')[0].childNodes[0].nodeValue;
                controlText += '<option value="' + gID + '"';
                if (gID == LastGroupID) {
                   controlText += ' selected="true"';   
                } 
                controlText += '>' + groupName + '</option>';
            }
            $("#grouplist").html(controlText);
        }
        function updateFooter(colIndex = 0) {
            var totalSum = 0;
            var elemSum = [];
            var footerText = "<tr><td>Суммы:</td>";
            for (i=0; i < Elements.length; i++) {
                   elemSum[i] = calcElement(i+2); 
                   totalSum += elemSum[i];
                   footerText += '<td>' + elemSum[i] + '</td>';
            }    
            footerText += '</tr><tr><td>Доли от целого:</td>'; 
            for (i=0; i < Elements.length; i++) {
                  s = (totalSum == 0) ? "" : (elemSum[i] / totalSum).toPrecision(4);  
                  footerText += '<td>' + s + '</td>';
                } 
            footerText += '</tr>';
            $("#group tfoot").html(footerText);            
        }    
        function buildMemberList(xml) {
            var memberID;
            var node;
            var memberName;
            var tableText="";
            var itemNodes;
            var dataValues = {};
            var key;
            var memberNodes = xml.getElementsByTagName('<?=NODE_MEMBERLIST_MEMBER?>');
            for (var i = 0; i < memberNodes.length; i++) {
                node = memberNodes[i];
                memberID = node.getElementsByTagName('<?=NODE_MEMBERLIST_ID?>')[0].childNodes[0].nodeValue;
                memberName = node.getElementsByTagName('<?=NODE_MEMBERLIST_NAME?>')[0].childNodes[0].nodeValue;
                tableText += '<tr data-id="' + memberID + '"><td data-name="' + memberName + '">' + memberName + '</td>';
                node = node.getElementsByTagName('<?=NODE_MEMBERLIST_DATA?>')[0];
                for (var j=0; j < Elements.length; j++) {
                    dataValues[Elements[j]] = 0;
                }
                if (node.children.length > 0) {
                    itemNodes = node.getElementsByTagName('<?=NODE_MEMBERLIST_ITEM?>');
                    for (j = 0; j < itemNodes.length; j++) {
                        node = itemNodes[j];
                        key = node.getElementsByTagName('<?=NODE_MEMBERLIST_ELEMENT?>')[0].childNodes[0].nodeValue;
                        dataValues[key] = parseInt(node.getElementsByTagName('<?=NODE_MEMBERLIST_VALUE?>')[0].childNodes[0].nodeValue);
                    }
                }
                for (j=0; j < Elements.length; j++) {
                   v = dataValues[Elements[j]];
                   tableText += '<td data-value=' + v + '>' + ((v==0) ? "" : v) + '</td>';
                }
                tableText += '<td><button onclick="deleteMember(this.parentNode.parentNode.dataset.id)">удалить</button></td></tr>';
            }
            
            $("#group tbody").html(tableText);
            updateFooter();
            $("td[data-value]").click(cellDataDoubleClick);
            $("td[data-name]").click(cellNameDoubleClick);
        }
        
        var OriginalContent = "";

        function SaveName(memberId, newName) {
            //console.log('Member: ' + memberId + ', name: ' + newName);
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    //buildMemberList(this.responseXML);
                }
            };
            xmlhttp.open("GET", "updmember.php?id=" + memberId + '&name=' + encodeURI(newName), true);
            xmlhttp.send();
            return true;
        }
        
        function SaveValue(memberId, elementId, newValue, onSuccess) {
            //console.log('Member: ' + memberId + ', name: ' + newName);
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    onSuccess();
                }
            };
            xmlhttp.open("GET", "updval.php?id=" + memberId + '&elem=' + elementId + 
                            '&value=' + newValue, true);
            xmlhttp.send();
            return true;
        }

        function Commit(control) {
            var newContent = control.val();
            control.parent().text(newContent);
            control.parent().removeClass("cellEditing");
        }
        
        function ValidateValue() {
           return true;   
        }    
        
        function CommitValue(control) {
            var newValue = parseInt( control.val().trim() );  
            var td = control.parent();
            var elementID = Elements[ td[0].cellIndex-1 ];
            var memberID = td.parent()[0].dataset.id;
            if (ValidateValue()) {
               SaveValue(memberID, elementID, newValue, function () {
                   td.text( (newValue == 0) ? "" : newValue );
                   td.removeClass("cellEditing");
                   td[0].dataset.value = newValue;
                   updateFooter();
               } );
            } 
        }
        
        function CommitName(control) {
            var newContent = control.val().trim();
            if (newContent != "") {
                //console.log(control.parent().parent()[0].dataset.id);
                var td = control.parent();
                if (SaveName(td.parent()[0].dataset.id, newContent)) {
                    td.text(newContent);
                    td.removeClass("cellEditing");
                }
            }
        }
        function Rollback(control) {
            control.parent().text(OriginalContent);
            control.parent().removeClass("cellEditing");
        }

    </script>
    <!-- Операции с группой: добавить, удалить переименовать -->
    <script>
        function addNewGroup() {
            //global LastGroupID;
            newName = prompt('Введите имя новой группы');
            if (newName) {
                newName = newName.trim();
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                      //console.log(this.response);
                      var groupID = parseInt(this.response);
                        console.log(groupID);
                      if (groupID > 0) {
                          LastGroupID = groupID;
                          refreshGroupList();
                      }
                    }
                };
                xmlhttp.open("GET", "addgroup.php?name=" + encodeURI(newName), true);
                xmlhttp.send(); 
            }    
        }

        function getGroupName() {
            //global LastGroupID;
            return $("#grouplist option[value=" + LastGroupID + "]")[0].text;
        }

        function deleteGroup() {
            //global LastGroupID;
            if (confirm('Удалить группу ' + getGroupName() + '?')) {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        console.log(this.response);
                        if (this.response == 'ok') {
                            LastGroupID = -1;
                            refreshGroupList();
                        }
                    }
                };
                xmlhttp.open("GET", "delgroup.php?id=" + LastGroupID, true);
                xmlhttp.send();
            }
        }
        function renameGroup() {
            //global LastGroupID;
            if (LastGroupID <= 0) { return; }
            oldName = getGroupName();
            newName = prompt('Введите новое имя для группы "' + oldName + '"', oldName).trim();
            if (!newName || (newName == oldName)) {
                alert("введите другое имя!");
                return;
            }
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    //console.log(this.response);
                    if (this.response == 'ok') {
                        refreshGroupList();
                    }
                }
            };
            xmlhttp.open("GET", "renamegroup.php?name=" + encodeURI(newName) + "&id=" + LastGroupID, true);
            xmlhttp.send();
        }
    </script>
    <!-- Операции с участниками группы: добавить, удалить -->
    <script>
        function getMemberName(id) {
            return $("#group tbody tr[data-id=" + id + "] td")[0].textContent;
        }

        function addNewMember() {
            if (newName = prompt('Введите имя нового участника:').trim()) {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        console.log(this.response);
                        var memberID = parseInt(this.response);
                        if (memberID > 0) {
                            groupSelected();
                        }
                    }
                };
                xmlhttp.open("GET", "addmember.php?name=" + encodeURI(newName) + "&group=" + LastGroupID, true);
                xmlhttp.send();
            }
        }
        function deleteMember(id) {
            //console.log(id);
            if (confirm('Удалить участника ' + getMemberName(id) + '?')) {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        console.log(this.response);
                        if (this.response == 'ok') {
                            deleteRow(id);
                        }
                    }
                };
                xmlhttp.open("GET", "delmember.php?id=" + id, true);
                xmlhttp.send();
            }
        }

        function deleteRow(id) {
            $("#group tbody tr[data-id=" + id + "]").detach();
            updateFooter();
        }
    </script>
</head>
<body>
    <section id="groups">
        <div id="grouplist_holder">
            <label for="grouplist">Выберите группу:</label>
            <select id="grouplist" onchange="groupSelected(this)"></select>
            <button type="button" onclick="addNewGroup()">Новая группа</button>
            <button type="button" onclick="deleteGroup()">Удалить группу</button>
            <button type="button" onclick="renameGroup()">Переименовать группу</button>
        </div>
    </section>
    <section>
        <table id="group">
            <thead>
                <tr>
                    <th>Имя участника</th>
                    <?php
                    $s = '';
                    foreach (array_values(Dictionary::$elemNames) as $elName) {
                        $s .= '<th>' . $elName . '</th>' . "\n";
                    }
                    echo $s;
                    ?>
                </tr>
            </thead><tfoot></tfoot>
            <tbody></tbody>
        </table>
        <button type="button" onclick="addNewMember()">Новый участник</button>
    </section>
</body>
</html>
