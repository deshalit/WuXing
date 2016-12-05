//var Profiles = [];

const H_KOEF_1 = 30;
const MIN_CHART_HEIGHT = 200;

function initPlot(data, names, holderID) {
    $.jqplot(holderID, data, {
        // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
        animate: !$.jqplot.use_excanvas,
        canvasOverlay: {show: true},
        seriesDefaults:{
            renderer: $.jqplot.BarRenderer,
            pointLabels: { show: true, location: 'e', edgeTolerance: -15 },
            //shadowAngle: 135,
            rendererOptions: {
                barDirection: 'horizontal'}
        },
        axes: {
            yaxis: {
                renderer: $.jqplot.CategoryAxisRenderer,
                ticks: names
            },
            xaxis: { max: 5.0 }
        },
        grid: {
                 background: 'transparent' 
             },
        highlighter: { show: true }
    });
}

function calcRowHeight(dataArray, subjCount = 1)
{   
    if (subjCount > 1) {
        //var h = $propCount * self::H_KOEF_2;
    } else {
        var h = dataArray.length * H_KOEF_1;
    }
    return (h < MIN_CHART_HEIGHT) ? MIN_CHART_HEIGHT : h;
}

function readyCharts(ProfileArray) {
    $.jqplot.config.enablePlugins = true;

    $.jqplot.postDrawHooks.push( function ()
    {
        $( ".jqplot-overlayCanvas-canvas" ).css( 'z-index', '0' ).css( 'background', 'linear-gradient(to right, rgb(52,41,209) 0%,rgb(52,41,209) 20%,rgb(83,188,60) 32%,rgb(244,234,39) 50%,rgb(219,126,72) 70%,rgb(255,140,140) 99%)');
        $( ".jqplot-series-canvas" ).css( 'z-index', '10' ); //send series canvas to front
    } );

    for (var i=0; i < ProfileArray.length; i++) {
        var holderID = 'chart_' + ProfileArray[i].id;
        $("#" + holderID).bind("jqplotDataClick",
            function (ev, seriesIndex, pointIndex, data) {
                $("#info1").html("series: " + seriesIndex + ", point: " + pointIndex + ", data: " + data);
            } );
        //console.log(ProfileArray[i].data);    
        initPlot(ProfileArray[i].data, ProfileArray[i].names, holderID);
    }
}
