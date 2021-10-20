
var _totalChartCount = 0;
var _retrievedChartCount = 0;

$(document).ready(function() {

    //$('#filter-btn').click();
    if( document.getElementById("filter-btn") ) {
        document.getElementById("filter-btn").click(); //chart-filter-btn
    }

});

function plotlyGetCharts() {
    console.log("get charts");

    _totalChartCount = 0;
    _retrievedChartCount = 0;
    var chartDataArr = [];

    var l = Ladda.create($('#filter-btn').get(0));
    //l.start();

    document.getElementById("charts").innerHTML = "";

    var startDate = $("#filter_startDate").val();
    //console.log("startDate="+startDate);

    var endDate = $("#filter_endDate").val();
    //console.log("endDate="+endDate);

    var projectSpecialty = $("#filter_projectSpecialty").select2("val");
    //console.log("projectSpecialty="+projectSpecialty);

    var chartTypes = $("#filter_chartType").select2("val");

    //filter_chartType
    var productservice = $("#filter_category").select2("val");
    //console.log("productservice:");
    //console.log(productservice);

    //var showLimited = $("#filter_showLimited:checked").val();
    //console.log("showLimited="+showLimited);

    var showLimited = 0;
    if( $("#filter_showLimited").is(":checked") ) {
        showLimited = 1;
    }
    //console.log("showLimited="+showLimited);

    var quantityLimit = $("#filter_quantityLimit").val();

    var url = Routing.generate('translationalresearch_single_chart');

    _totalChartCount = chartTypes.length;

    if( _totalChartCount > 0 ) {
        l.start();
    }

    var i;
    for (i = 0; i < _totalChartCount; i++) {

        //l.start();

        var chartIndex = chartTypes[i];
        console.log("chartType="+chartIndex);

        $.ajax({
            url: url,
            //timeout: _ajaxTimeout,
            timeout: 300000, //milliseconds, 600000 => 10 min, 3000 => 3 second timeout
            type: "GET",
            data: {startDate:startDate, endDate:endDate, projectSpecialty:projectSpecialty, showLimited:showLimited, quantityLimit:quantityLimit, chartType:chartIndex, productservice:productservice },
            dataType: 'json',
            async: true //false //use synchronous => wait for response.
            //async: true
        }).success(function(chartData) {
            //console.log('chartData=');
            //console.log(chartData);
            //plotlyAddChart(chartIndex,chartData);

            //create array of charts
            //chartDataArr[chartIndex] = chartData;
            chartDataArr.push(chartData);

            // tryToBuildCharts(chartDataArr);
            // if( _totalChartCount == _retrievedChartCount ) {
            //     l.stop();
            // }

        }).done(function() {
            //l.stop();

            tryToBuildCharts(chartDataArr);
            if( _totalChartCount == _retrievedChartCount ) {
                l.stop();
            }

        }).error(function(jqXHR, textStatus, errorThrown) {
            l.stop();

            //alert(jqXHR.responseText);
            //alert(textStatus);
            //alert(errorThrown);

            console.log('Error : ' + errorThrown);

            var errorMsg = "Unexpected Error. Please make sure that your session is not timed out and you are still logged in, or select a smaller time period for this chart.";
            //errorMsg = errorMsg + " responseText="+jqXHR.responseText+", textStatus="+textStatus+", errorThrown="+errorThrown;
            plotlyAddErrorLine(errorMsg,'error');

            _totalChartCount--;

            ////plotlyAddErrorLine("Unexpected Error. Please make sure that your session is not timed out and you are still logged in, or select a smaller time period for this chart. ",'error');
        });

    }

    // if( _totalChartCount == _retrievedChartCount ) {
    //     l.stop();
    // }
    //l.stop();
}

//or use promise: https://www.cognizantsoftvision.com/blog/handling-sequential-ajax-calls-using-jquery/
function tryToBuildCharts(chartDataArr) {

    _retrievedChartCount++;

    console.log("_totalChartCount="+_totalChartCount+", _retrievedChartCount="+_retrievedChartCount);
    if( _totalChartCount != _retrievedChartCount ) {
      return false;
    }

    console.log("chartDataArr:");
    console.log(chartDataArr);

    // for (var chartIndex in chartDataArr) {
    //     console.log("chartIndex=" + chartIndex);
    //     plotlyAddChart(chartIndex,chartDataArr[chartIndex]);
    // }

    var chartIndex = 0;
    chartDataArr.forEach(function(element) {
        //console.log(element)
        chartIndex++;
        plotlyAddChart(chartIndex,element);
    })
}

function plotlyAddChart(chartIndex,chartData) {

    console.log("plotlyAddChart="+chartIndex);

    //console.log("_totalChartCount="+_totalChartCount+", _retrievedChartCount="+_retrievedChartCount);
    //if( _totalChartCount != _retrievedChartCount ) {
    //   return false;
    //}

    if( !chartData ) {
        return false;
    }

    if( chartData['error'] ) {
        //console.log("chartData error");
        plotlyAddErrorLine(chartData['error'],'error');
        return false;
    }
    if( chartData['warning'] ) {
        //console.log("chartData warning");
        plotlyAddErrorLine(chartData['warning'],'warning');
        return false;
    }


    var divId = 'chart-' + chartIndex;
    var div = document.createElement("div");
    div.style.float = "left";
    div.style.margin = "10px";
    div.setAttribute('id', divId);
    document.getElementById("charts").appendChild(div);

    var layout = chartData['layout'];
    var data = chartData['data'];

    //console.log("data:");
    //console.log(data);

    Plotly.newPlot(divId, data, layout);

    //console.log("_totalChartCount="+_totalChartCount+", _retrievedChartCount="+_retrievedChartCount);
    //if( _totalChartCount != _retrievedChartCount ) {
    //    return;
    //}

    var myPlot = document.getElementById(divId);

    myPlot.on('plotly_click', function(data){
        //console.log("data:");
        //console.log(data);
        var index = 0;
        var link = null;
        for(var i=0; i < data.points.length; i++){
            index = data.points[i].i; //try pie case
            if( typeof index === 'undefined' ) { //bar case
                index = data.points[i].pointIndex;
            }
            //index = 2;
            //console.log("index="+index);
            if( data.points[i].data.links ) {
                link = data.points[i].data.links[index];
                //console.log("get link="+link);
            }
        }
        //alert('Closest point clicked:\n\n'+pts);
        if( link ) {
            //console.log("open link="+link);
            window.open(link);
        }
    });
}

function plotlyAddErrorLine( msg, type ) {
    //console.log("newline");
    var divEl = document.createElement("div");
    divEl.style.float = "left";
    divEl.style.width = "100%";
    if( type == 'error' ) {
        divEl.className = "alert alert-danger";
    }
    if( type == 'warning' ) {
        divEl.className = "alert alert-warning";
    }
    divEl.innerHTML = msg;
    //newline.setAttribute('id', divId);
    document.getElementById("charts").appendChild(divEl);
}
