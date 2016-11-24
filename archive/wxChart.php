<?php
require_once ("dict.class.php");
include_once ("dictxml.inc.php");

class wxChart
{
    const MIN_CHART_HEIGHT = 200;
    const H_KOEF_1 = 30;
    const H_KOEF_2 = 50;

    private $dataArray = [];
    private $profiles = [];
    public $dictionary = null;

    public function __construct(Dictionary $dict, $profiles, $data1, $data2 = null)
    {
        $this->dictionary = $dict;
        $this->profiles = $profiles; //var_dump($this->profiles);
        $this->dataArray = Array();
        array_push($this->dataArray, $data1); //var_dump($data1);
        if ($data2) {
            array_push($this->dataArray, $data2);
        }
    }

    public function calcRowHeight($propCount)
    {
        if (count($this->dataArray) > 1) {
            $h = $propCount * self::H_KOEF_2;
        } else {
            $h = $propCount * self::H_KOEF_1;
        }
        return ($h < self::MIN_CHART_HEIGHT) ? self::MIN_CHART_HEIGHT : $h;
    }

    private function getDataString($profile_id)
    {
        $res = '[' . implode(',', array_reverse(array_values($this->dataArray[0][$profile_id]))) . ']';
        if (count($this->dataArray) > 1) {
            $res .= ', [' . implode(',', array_reverse(array_values($this->dataArray[1][$profile_id]))) . ']';
        }
        return $res;
    }

    public function initCharts()
    {
        $createProfileStrings = Array();
        foreach (array_values($this->profiles) as $pid) {
            $profName = $this->dictionary->get_profile_name($pid);
            $props = $this->dictionary->get_profile_properties($pid);
            $names = Array();
            foreach ($props as $propid) {
                $prop_name = $this->dictionary->get_property_name($propid);
                $names[] = $prop_name;
            }
            $profileStr = ' { id: ' . $pid . ', name: "' . $profName . '"' . ', data: [' . $this->getDataString($pid)
                . '], names: ["' . implode('","', array_reverse($names)) . '"]}';
            array_push($createProfileStrings, $profileStr);
        }
        $res = 'var Profiles = [' . implode(',', $createProfileStrings) . '];';
        $res .= ' function calcRowHeight(propCount, clientCount = 1) { if (clientCount > 1) { ' .
            'h = propCount * ' . self::H_KOEF_2 . '; } else { h = propCount * ' . self::H_KOEF_1 . '; } ' .
            'return (h < ' . self::MIN_CHART_HEIGHT . ') ? ' . self::MIN_CHART_HEIGHT . ': h; }';
        return $res;
    }
}