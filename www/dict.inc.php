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

    static $combinations = Array(
        Array('F' => 'E'),  // Огонь - Земля
        Array('F' => 'M'),  // Огонь - Металл
        Array('F' => 'W'),  // Огонь - Вода
        Array('F' => 'T'),  // Огонь - Дерево
        Array('E' => 'M'),  // Огонь - Металл
        Array('E' => 'W'),  // Земля - Вода
        Array('E' => 'T'),  // Земля - Дерево
        Array('M' => 'W'),  // Металл - Вода
        Array('M' => 'T'),  // Металл - Дерево
        Array('W' => 'T')   // Вода - дерево
    );

    static $elemNames = Array ('F' => 'Огонь', 'E' => 'Земля', 'M' => 'Металл', 'W' => 'Вода', 'T' => 'Дерево');

    public $profiles = Array();
    public $properties = Array();
    public $elements = Array();
    public $risk = Array();
    public $comments = Array();
    public $basetypes = Array();    // $basetypes[ $property_id ] = Array( $element_id => int )

    public function Load(DictLoader $loader) {
        if (isset($loader)) {
            $loader->Load($this);
            return true;
        } else return false;
    }

    static function get_elem_hash($elems) {
        $res = 0;
        foreach ((array)$elems as $x) {
            $res |= self::$elemHash[$x];
        }
        return $res;
    }
    
    static function get_elem_hash2($el1, $el2) {
        return self::get_elem_hash(Array($el1, $el2));
    }        
}