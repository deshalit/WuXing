<?php
/**
 * Created by PhpStorm.
 * User: BOSS
 * Date: 29.09.2016
 * Time: 11:57
 */
abstract class DictLoader {
    abstract function Load(Dictionary $dict);
}
class Dictionary {
    static $elemHash = Array('F' => 1, 'E' => 2, 'M' => 4, 'W' => 8, 'T' => 16);
/*
    static $combinations = Array(
        'F' => 'E',  // ����� - �����
        'F' => 'M',  // ����� - ������
        'F' => 'W',  // ����� - ����
        'F' => 'T',  // ����� - ������
        'E' => 'M',  // ����� - ������
        'E' => 'W',  // ����� - ����
        'E' => 'T',  // ����� - ������
        'M' => 'W',  // ������ - ����
        'M' => 'T',  // ������ - ������
        'W' => 'T'   // ���� - ������
    );
*/
    public $profiles = Array();
    public $properties = Array();
    public $elements = Array();
    public $risk = Array();
    public $comments = Array();

    public function Load(DictLoader $loader) {
        if (isset($loader)) {
            $loader->Load($this);
            return true;
        } else return false;
    }

    static function elem_hash($el1, $el2) {
        return self::$elemHash[$el1] | self::$elemHash[$el2];
    }
}