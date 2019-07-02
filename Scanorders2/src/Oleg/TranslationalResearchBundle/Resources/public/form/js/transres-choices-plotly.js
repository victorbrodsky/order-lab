
$(document).ready(function() {

    //$('#filter-btn').click();
    document.getElementById("filter-btn").click();

});

function transresGetCharts() {
    //console.log("get charts");

    var l = Ladda.create($('#filter-btn').get(0));
    l.start();

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

    var i;
    for (i = 0; i < chartTypes.length; i++) {

        var chartIndex = chartTypes[i];
        //console.log("chartType="+chartIndex);

        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            type: "GET",
            data: {startDate:startDate, endDate:endDate, projectSpecialty:projectSpecialty, showLimited:showLimited, quantityLimit:quantityLimit, chartType:chartIndex, productservice:productservice },
            dataType: 'json',
            async: false //use synchronous => wait for response.
        }).success(function(chartData) {
            //console.log('chartData=');
            //console.log(chartData);
            transresAddChart(chartIndex,chartData);
        }).done(function() {
            //
        }).error(function(jqXHR, textStatus, errorThrown) {
            console.log('Error : ' + errorThrown);
            transresAddErrorLine("Unexpected Error. Please make sure that your session is not timed out and you are still logged in.",'error');
        });

    }

    l.stop();
}

function transresAddChart(chartIndex,chartData) {

    if( !chartData ) {
        return false;
    }

    if( chartData['error'] ) {
        //console.log("newline");
        transresAddErrorLine(chartData['error'],'error');
        return false;
    }
    if( chartData['warning'] ) {
        //console.log("newline");
        transresAddErrorLine(chartData['warning'],'warning');
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

function transresAddErrorLine( msg, type ) {
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
