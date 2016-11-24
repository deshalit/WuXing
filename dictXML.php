<?php

require_once("dict.class.php");

const S_EXCEPTION_FILE_NOT_EXISTS = 'File does not exist';
const S_EXCEPTION_FILE_CORRUPTED = 'File is corrupted or locked';

const S_NODE_ELEMENT  = 'elem';
const S_NODE_PROPERTY = 'property';
const S_NODE_PROFILE  = 'profile';
const S_NODE_RISK     = 'riskgroup';
const S_NODE_COMMENT  = 'comment';
const S_NODE_RATIO    = 'ratio';

const S_ATTR_ID       = 'id';
const S_ATTR_EL1      = 'el1';
const S_ATTR_EL2      = 'el2';
const S_ATTR_PROFILE  = 'profile';
const S_ATTR_TITLE    = 'title';
const S_ATTR_NAME     = 'name';


class DictLoaderXML extends DictLoader {
    protected $fileName = '';
    protected $xml;

    function __construct($fname)
    {
       $this->xml = NULL;
       $this->fileName = $fname;
    }

    private function read_node_basetype(Dictionary $dict, $elem)
    {
        $propid = $elem->attributes->item(0)->nodeValue;

        $data = Array();
        foreach ($elem->childNodes as $x) {
            if ($x->attributes->length == 1) {
                // <value el="F">5</value>
                $el = $x->attributes->item(0)->nodeValue;
                $data[$el] = (int)$x->nodeValue;
            }
        }
        $dict->basetypes[$propid] = $data;
    }

    private function read_node_comment(Dictionary $dict, $elem)
    {
        // <comment el1="F" el2="E" profile="1"><![CDATA[..]]></comment>
        // filling $dict->comments
        // $dict->comments[1] = [hash1=> "...",...];
        $text = $elem->firstChild->nodeValue;  // <!CDATA[[]]>
        $att = $elem->attributes;
        $el1 = $att->getNamedItem(S_ATTR_EL1);
        $el2 = $att->getNamedItem(S_ATTR_EL2);
        $profile_id = $att->getNamedItem(S_ATTR_PROFILE)->value;
        $hash = $dict->get_elem_hash2($el1->value, $el2->value);
        $dict->comments[$profile_id][$hash] = $text;
    }

    private function read_node_element(Dictionary $dict, $elem)
    {
        // <elem id="F">Огонь</elem>
        // filling $dict->elements
        // $dict->elements["F"] = "Огонь";
         $id = $elem->attributes->item(0)->nodeValue;
         $dict->elements[$id] = $elem->nodeValue;
    }

    private function read_node_profile(Dictionary $dict, $elem)
    {
        // <profile id="1" name="....">
        // filling $dict->profiles
        // $dict->profiles[1] = ["Эмоциональная сфера", [1,3,7,8,11,24,39,31]];
        $id = $elem->attributes->getNamedItem(S_ATTR_ID)->value;
        $name = $elem->attributes->getNamedItem(S_ATTR_NAME)->value;
        $props = Array();
        foreach ($elem->childNodes as $x) {
            $value = (int)$x->nodeValue;
            if ($value) {
                array_push($props, $value);
            }
        }
        $dict->profiles[$id] = Array($name, $props);
    }

    private function read_node_property(Dictionary $dict, $elem)
    {
        // <property id="28" title="������"><![CDATA[]]></property>
        // filling $dict->properties
        // $dict->properties[28] = ["������", "����������� ���-���-���..."];
        $id = $elem->attributes->getNamedItem(S_ATTR_ID)->value;   // id
        $title = $elem->attributes->getNamedItem(S_ATTR_TITLE)->value;   // title
        $note = $elem->firstChild->nodeValue;  // <!CDATA[[]]>
        $dict->properties[$id] = Array($title, $note);
    }

    private function read_node_risk(Dictionary $dict, $elem)
    {
        // <riskgroup id="1" title="������������ �����">
        // filling $dict->risk
        // $dict->risk[1] = ["title", [hash1=>"risk1", hash2=>"risk2", ...]];
        $id = $elem->attributes->getNamedItem(S_ATTR_ID)->value;  // id
        $title = $elem->attributes->getNamedItem(S_ATTR_TITLE)->value;  // title
            // reading <risk el1="F" el2="E"><![CDATA[�������, �����. �-��...]]></risk>
        $risk = Array();
        foreach ($elem->childNodes as $x) {
            if ($x->attributes->length == 2) {
                $el1 = $x->attributes->getNamedItem(S_ATTR_EL1);
                $el2 = $x->attributes->getNamedItem(S_ATTR_EL2);
                $hash = $dict->get_elem_hash2($el1->value, $el2->value);
                $risk[$hash] = $x->firstChild->nodeValue; // CDATA
            }
        }
        if (count($risk) > 0) {
            $dict->risk[$id] = Array($title, $risk);
        }
    }

    private function load_array(Dictionary $dict, $nodeName, callable $func)
    {
        $elements = $this->xml->getElementsByTagName($nodeName);
        if (!isset($elements)) {
            throw new ErrorException(S_EXCEPTION_FILE_CORRUPTED . " (node <$nodeName> not found");
        }
        foreach ($elements as $elem) { 
            call_user_func($func, $dict, $elem);
        }
        return true;
    }

    private function load_basetypes(Dictionary $dict)
    {
        $dict->basetypes = Array();
        return $this->load_array($dict, S_NODE_RATIO, Array($this, 'read_node_basetype'));
    }

    private function load_comments(Dictionary $dict)
    {
        $dict->comments = Array();
        return $this->load_array($dict, S_NODE_COMMENT, Array($this, 'read_node_comment'));
    }

    private function load_elements(Dictionary $dict)
    {
        $dict->elements = Array();
        return $this->load_array($dict, S_NODE_ELEMENT, Array($this, 'read_node_element'));
    }

    private function load_profiles(Dictionary $dict)
    {
        $dict->profiles = Array();
        return $this->load_array($dict, S_NODE_PROFILE, Array($this, 'read_node_profile'));
    }

    private function load_properties(Dictionary $dict)
    {
        $dict->properties = Array();
        return $this->load_array($dict, S_NODE_PROPERTY, Array($this, 'read_node_property'));
    }

    private function load_risk(Dictionary $dict)
    {
        $dict->risk = Array();
        return $this->load_array($dict, S_NODE_RISK, Array($this, 'read_node_risk'));
    }

    function Load(Dictionary $dict)
    {
        if (!file_exists($this->fileName)) {
        //echo $this->fileName;
            throw new ErrorException(S_EXCEPTION_FILE_NOT_EXISTS);
        }
        $this->xml = new DomDocument('1.0', 'utf-8');
        if (!$this->xml->load($this->fileName)) {
            unset($this->xml);
            throw new ErrorException(S_EXCEPTION_FILE_CORRUPTED);
        }
        $this->load_elements($dict);
        $this->load_properties($dict);
        $this->load_profiles($dict);
        $this->load_risk($dict);
        $this->load_comments($dict);
        $this->load_basetypes($dict);
    }
}