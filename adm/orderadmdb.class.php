<?php
require_once('../class/order/common/orderdb.class.php');
   
class OrderAdmDB extends OrderDB {

    const ADM_OPTIONS_FILE = 'adm.ini';

    protected function getIniFileName() {
        return '/adm/' . self::ADM_OPTIONS_FILE;
    }
    protected function getDefaultUserName() {
        return 'wx_admin';

    } 
    protected function getDefaultDatabaseName() {
        return 'wuxing';
    }
}
