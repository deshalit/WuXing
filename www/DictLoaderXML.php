<?php

/**
 * Created by PhpStorm.
 * User: BOSS
 * Date: 29.09.2016
 * Time: 12:00
 */
require_once("dict.inc.php");

const S_EXCEPTION_FILE_NOT_EXISTS = 'File does not exist';
const S_EXCEPTION_FILE_CORRUPTED = 'File is corrupted or locked';

const S_NODE_ELEMENT  = 'elem';
const S_NODE_PROPERTY = 'property';
const S_NODE_PROFILE  = 'profile';
const S_NODE_RISK     = 'riskgroup';
const S_ATTR_EL1      = 'el1';
const S_ATTR_EL2      = 'el2';
const S_NODE_COMMENT  = 'comment';
const S_ATTR_PROFILE  = 'profile';
const S_NODE_RATIO    = 'ratio';

class DictLoaderXML extends DictLoader {
    protected $fileName = '';
    protected $xml;

    function __construct($fname) {
       $this->xml = NULL;
       $this->fileName = $fname;
    }

    private function load_basetypes(Dictionary $dict)
    {
        $dict->basetypes = Array();
        // Lets find all the 'ratio' nodes
        $elements = $this->xml->getElementsByTagName(S_NODE_RATIO);
        if (!isset($elements)) {
            throw new ErrorException(S_EXCEPTION_FILE_CORRUPTED . ' (node <' . S_NODE_RATIO . '> not found');
        }
        // <ratio property="1">...</ratio>
        // filling $dict->basetypes
        // $dict->basetypes[1] = ['F'=> 1, 'M' => 5, ...];
        for ($i=0; $i < $elements->length; $i++) {
            $elem = $elements->item($i);
            // each node has single attribute "property", get its value
            $propid = $elem->attributes->item(0)->nodeValue;
            $data = Array();

        }
    }

    private function load_comments(Dictionary $dict) {
        $dict->comments = Array();
        // Lets find all the 'comment' nodes
        $elements = $this->xml->getElementsByTagName(S_NODE_COMMENT);
        if (!isset($elements)) {
            throw new ErrorException(S_EXCEPTION_FILE_CORRUPTED . ' (node <' . S_NODE_COMMENT . '> not found');
        }
        // <comment el1="F" el2="E" profile="1"><![CDATA[Повышенная эмоциона...]]></comment>
        // filling $dict->comments
        // $dict->comments[1] = [hash1=> "Способность бла-бла-бла...",...];
        for ($i=0; $i < $elements->length; $i++) {
            $elem = $elements->item($i);
            // each node has three attributes (el1, el2, profile), get its values
            $text = $elem->firstChild->nodeValue;  // <!CDATA[[]]>
            $att = $elem->attributes;
            if ($att->length == 3) {
                $el1 = $att->getNamedItem(S_ATTR_EL1);
                $el2 = $att->getNamedItem(S_ATTR_EL2);
                $profile_id = $att->getNamedItem(S_ATTR_PROFILE)->value;
                $hash = $dict->elem_hash($el1->value, $el2->value);
                if (empty($dict->comments[$profile_id])) {
                    $dict->comments[$profile_id] = Array($hash => $text);
                } else {
                    $dict->comments[$profile_id][$hash] = $text;
                }
            }
        }
        var_dump($dict->comments[1]);
        return count($dict->comments) > 0;
    }

    private function load_elements(Dictionary $dict) {
        $dict->elements = Array();
        // Lets find all the "elem' nodes
        $elements = $this->xml->getElementsByTagName(S_NODE_ELEMENT);
        if (!isset($elements)) {
            throw new ErrorException(S_EXCEPTION_FILE_CORRUPTED . ' (node <' . S_NODE_ELEMENT . '> not found');
        }
        // <elem id="F">Огонь</elem>
        // filling $dict->elements
        // $dict->elements["F"] = "Огонь";
        for ($i=0; $i < $elements->length; $i++) {
            $elem = $elements->item($i);
            // each node has a single attribute, get its value
            $id = $elem->attributes->item(0)->nodeValue;
            $dict->elements[$id] = $elem->nodeValue;
        }
        return count($dict->elements) > 0;
    }

    private function load_properties(Dictionary $dict) {
        $dict->properties = Array();
        // Lets find all the "property' nodes
        $elements = $this->xml->getElementsByTagName(S_NODE_PROPERTY);
        if (!isset($elements)) {
            throw new ErrorException(S_EXCEPTION_FILE_CORRUPTED . ' (node <' . S_NODE_PROPERTY . '> not found');
        }
        // <property id="28" title="Анализ"><![CDATA[]]></property>
        // filling $dict->properties
        // $dict->properties[28] = ["Анализ", "Способность бла-бла-бла..."];
        for ($i=0; $i < $elements->length; $i++) {
            $elem = $elements->item($i);
            // each node has two attributes (id & title), get its values
            $id = $elem->attributes->item(0)->nodeValue;   // id
            $title = $elem->attributes->item(1)->nodeValue;   // title
            $note = $elem->firstChild->nodeValue;  // <!CDATA[[]]>
            $dict->properties[$id] = Array($title, $note);
        }
        return count($dict->properties) > 0;
    }

    private function load_profiles(Dictionary $dict) {
        $dict->profiles = Array();
        // Lets find all the "property' nodes
        $elements = $this->xml->getElementsByTagName(S_NODE_PROFILE);
        if (!isset($elements)) {
            throw new ErrorException(S_EXCEPTION_FILE_CORRUPTED . ' (node <' . S_NODE_PROFILE . '> not found');
        }
        // <profile id="1" name="Эмоциональная сфера">
        // filling $dict->profiles
        // $dict->profiles[1] = ["Эмоциональная сфера", ["Психол. открытость",  "Контактность", ...]];
        for ($i=0; $i < $elements->length; $i++) {
            $elem = $elements->item($i);
            // each node has two attributes (id & name), get its values
            $id = $elem->attributes->item(0)->nodeValue;
            $name = $elem->attributes->item(1)->nodeValue;
            $props = Array();
            foreach ($elem->childNodes as $x) {
                $value = (int)$x->nodeValue;
                if ($value) {
                    array_push($props, $value);
                }
            }
            $dict->profiles[$id] = Array($name, $props);
        }
        return count($dict->profiles) > 0;
    }

    private function load_risk(Dictionary $dict) {
        $dict->risk = Array();
        // Lets find all the 'riskgroup' nodes
        $elements = $this->xml->getElementsByTagName(S_NODE_RISK);
        if (!isset($elements)) {
            throw new ErrorException(S_EXCEPTION_FILE_CORRUPTED . ' (node <' . S_NODE_RISK . '> not found');
        }
        // <riskgroup id="1" title="Соматические риски">
        // filling $dict->risk
        // $dict->risk[1] = ["Соматические риски", [hash1: "Гастрит, язвен. б-нь", hash2: "...", ...]];
        for ($i=0; $i < $elements->length; $i++) {
            $elem = $elements->item($i);
            // each node has two attributes (id & title), get its values
            $id = $elem->attributes->item(0)->nodeValue;  // id
            $title = $elem->attributes->item(1)->nodeValue;  // title
            // reading <risk el1="F" el2="E"><![CDATA[Гастрит, язвен. б-нь...]]></risk>
            $risk = Array();
            foreach ($elem->childNodes as $x) {
                $text = $x->firstChild->nodeValue; // CDATA
                if ($x->attributes->length == 2) {
                    $el1 = $x->attributes->getNamedItem(S_ATTR_EL1);
                    $el2 = $x->attributes->getNamedItem(S_ATTR_EL2);
                    $hash = $dict->elem_hash($el1->value, $el2->value);
                    $risk[$hash] = $text;
                }
            }
            if (count($risk) > 0) {
                $dict->risk[$id] = Array($title, $risk);
            }
        }
        return count($dict->risk) > 0;
    }

    function Load(Dictionary $dict) {

        if (!file_exists($this->fileName)) {
            throw new ErrorException(S_EXCEPTION_FILE_NOT_EXISTS);
        }
        $this->xml = new DomDocument('1.0', 'utf-8');
        if (!$this->xml->load($this->fileName)) {
            unset($this->xml);
            throw new ErrorException(S_EXCEPTION_FILE_CORRUPTED);
        }
        //$this->load_elements($dict);
        //$this->load_properties($dict);
        //$this->load_profiles($dict);
        //$this->load_risk($dict);
        //$this->load_comments($dict);
        $this->load_basetypes($dict);

    }
}