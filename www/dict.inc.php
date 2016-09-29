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
        'F' => 'E',  // Огонь - Земля
        'F' => 'M',  // Огонь - Металл
        'F' => 'W',  // Огонь - Вода
        'F' => 'T',  // Огонь - Дерево
        'E' => 'M',  // Огонь - Металл
        'E' => 'W',  // Земля - Вода
        'E' => 'T',  // Земля - Дерево
        'M' => 'W',  // Металл - Вода
        'M' => 'T',  // Металл - Дерево
        'W' => 'T'   // Вода - дерево
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