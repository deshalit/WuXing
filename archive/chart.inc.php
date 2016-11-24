<?php

const MIN_CHART_HEIGHT = 175;
const H_KOEF_1 = 30;
const H_KOEF_2 = 50;

function calc_row_height($subjCount, $propCount)
{
    if ($subjCount > 1) {
        $h = $propCount * H_KOEF_2;
    } else {
        $h = $propCount * H_KOEF_1;
    }
    return ($h < MIN_CHART_HEIGHT) ? MIN_CHART_HEIGHT : $h;
}

