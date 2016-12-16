<?php
require_once('common/orderdb.class.php');
   
class OrderRegDB extends OrderDB {

    protected function getIniFileName() {
        return 'reg.ini';
    }
    protected function getDefaultUserName() {
        return 'wx_user';

    } 
    protected function getDefaultDatabaseName() {
        return 'wuxing';
    }
}
