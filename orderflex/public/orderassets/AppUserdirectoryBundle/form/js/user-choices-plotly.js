
var _totalChartCount = 0;
var _retrievedChartCount = 0;

$(document).ready(function() {

    //$('if( document.getElementById("filter-btn") ) {
    //     document.getElementById("filter-btn").click(); //chart-filter-btn
    // }#filter-btn').click();
    //

    //click filter button if number of charts less or equal 3
    var useWarning = $("#useWarning").val();
    var chartTypesLen = $('#filter_chartType').select2('data').length;
    if( !useWarning ) {
        chartTypesLen = 0;
    }
    //console.log("chartTypesLen="+chartTypesLen);
    if (chartTypesLen < 4) {
        if (document.getElementById("filter-btn")) {
            document.getElementById("filter-btn").click(); //chart-filter-btn
        }
    }

    //var favoriteEl = '<div> <span class="star glyphicon glyphicon-star-empty"></span> </div>';
    //var favoriteEl = '<div> <span class="favorite-icon glyphicon glyphicon-heart-empty" style="color:orangered;"></span> </div>';
    //$( favoriteEl ).appendTo( "#"+divId );
    //$( favoriteEl ).appendTo( "#charts" );

    //favoriteToggleButtonInit();

});

function plotlyGetChartsReact(thisSitename) {
    window.ReactHomeComponent.sayHello();
}

function plotlyGetCharts( thisSitename ) {
    //console.log("get charts");

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

    var url = Routing.generate(thisSitename+'_single_chart');

    _totalChartCount = chartTypes.length;

    if( _totalChartCount > 0 ) {
        l.start();
    }

    var i;
    for (i = 0; i < _totalChartCount; i++) {

        //l.start();

        var chartIndex = chartTypes[i];
        //console.log("chartType="+chartIndex);

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

    //console.log("_totalChartCount="+_totalChartCount+", _retrievedChartCount="+_retrievedChartCount);
    if( _totalChartCount != _retrievedChartCount ) {
      return false;
    }

    //console.log("chartDataArr:");
    //console.log(chartDataArr);

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

    //console.log("plotlyAddChart="+chartIndex);

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

    var chartId = chartData['chartId'];
    var layout = chartData['layout'];
    var data = chartData['data'];
    var favoriteFalg = chartData['favorite'];
    //console.log("data:");
    //console.log(data);

    //var chartId = data[0]['id'];
    //console.log("chartId="+chartId);

    //var divId = 'chart-' + chartIndex;
    var divId = 'chart-' + chartId;
    var div = document.createElement("div");
    div.style.float = "left";
    div.style.margin = "10px";
    div.setAttribute('id', divId);
    document.getElementById("charts").appendChild(div);

    //create favorite icon glyphicon glyphicon-heart
    //show a toggle button with a heart glyphicon under each chart (right-justified)
    var favoriteDivId = 'chart-favorite-' + chartIndex;
    // var favoriteDiv = document.createElement("div");
    // favoriteDiv.style.float = "left";
    // favoriteDiv.style.margin = "10px";
    // favoriteDiv.setAttribute('id', favoriteDivId);
    // favoriteDiv.innerHTML = "Favorite Div";
    // document.getElementById(divId).appendChild(favoriteDiv);

    // var favoriteEl = '<button type="button" class="btn btn-default" ' +
    //     'onClick="favoriteChart(\''+favoriteDivId+'\');" style="float:right;">' +
    //     '<span class="glyphicon glyphicon-heart-empty"></span></button>';
    //var favoriteEl = '<span class="favorite-icon glyphicon glyphicon-heart-empty" onClick="favoriteChart(\''+favoriteDivId+'\');" style="float:right;"></span>';
    //var favoriteEl = '<span id="'+favoriteDivId+'" class="glyphicon glyphicon-heart" aria-hidden="true" style="float:right;"> Favorite</span>';
    //var favoriteEl = '<div> <span class="star glyphicon glyphicon-star-empty"></span> </div>';
    // var favoriteEl = '<div> <span id="'+favoriteDivId+'" class="favorite-icon glyphicon glyphicon-heart-empty" ' +
    //     'style="color:orangered;" onClick="favoriteChart(\''+favoriteDivId+'\');"></span> </div>';
    // var favoriteEl = '<div><span id="'+favoriteDivId+'" ' +
    //     'class="favorite-icon glyphicon glyphicon-heart-empty" ' +
    //     'style="color:orangered; float:right;" ' +
    //     'onClick="favoriteChart(this,'+chartId+');"></span></div>';
    // //$( favoriteEl ).appendTo( "#"+divId );
    // //$( favoriteEl ).appendTo( "#start-test" );
    // $( "#"+divId ).append( favoriteEl );

    // var trace1 = {
    //     x: ['giraffes', 'orangutans', 'monkeys'],
    //     y: [20, 14, 23],
    //     name: 'SF Zoo',
    //     type: 'bar'
    // };
    // var trace2 = {
    //     x: ['giraffes', 'orangutans', 'monkeys'],
    //     y: [12, 18, 29],
    //     name: 'LA Zoo',
    //     type: 'bar'
    // };
    // var data = [trace1, trace2];

    //var dataX = ['Sun','Mon','Tue','Sun','Mon','Tue']; //6
    //var dataY1 = ["16.99", "10.34", "21.01", "23.68", "24.59", "25.29", "8.77", "26.88", "15.04"]; //9 //[2.1,3.3,1.4,4.1,2.8,3.2,5.2,1.1,1.1,2.5,2,3,4,5,6,7,1];
    //var dataY2 = ["16.99", "10.34", "21.01", "23.68", "24.59", "25.29", "8.77", "26.88", "15.04"]; //9 //[2,3,1,4,2,3,5,1,1,2,3,4,5,2,3,4,1,1,32,43,4,43,2];

    //var dataX = ['Sun','Mon','Tue','Sun','Mon','Tue','Sun','Mon','Tue']; //9
    //var dataY1 = ["16.99", "10.34", "21.01", "16.99", "10.34", "21.01", "21.01", "10.34", "21.01"]; //9
    //var dataY2 = ["16.99", "10.34", "21.01", "16.99", "10.34", "21.01", "21.01", "10.34", "21.01"];

    // if(0) {
    //     var data = [{
    //         type: 'violin',
    //         y: dataY1, //unpack(rows, 'total_bill'),
    //         points: 'none',
    //         box: {
    //             visible: true
    //         },
    //         boxpoints: false,
    //         line: {
    //             color: 'black'
    //         },
    //         fillcolor: '#8dd3c7',
    //         opacity: 0.6,
    //         meanline: {
    //             visible: true
    //         },
    //         x0: "Total Bill"
    //     }];
    //     var layout = {
    //         title: "",
    //         yaxis: {
    //             zeroline: false
    //         }
    //
    //     };
    // }

    // need to fix data

    // if(0) {
    //     var dataX = ['Sun', 'Mon', 'Tue', 'Sun', 'Mon', 'Tue']; //6
    //     var dataY1 = ["16.99", "10.34", "21.01", "23.68", "24.59", "25.29", "8.77", "26.88", "15.04"];
    //     var dataY2 = ["16.99", "10.34", "21.01", "23.68", "24.59", "25.29", "8.77", "26.88", "15.04"];
    //
    //     var data = [{
    //         type: 'violin',
    //         x: dataX, //unpack(rows, 'day'),
    //         y: dataY1,//unpack(rows, 'total_bill'),
    //         legendgroup: 'M',
    //         scalegroup: 'M',
    //         name: 'M',
    //         box: {
    //             visible: true
    //         },
    //         line: {
    //             color: 'blue',
    //         },
    //         meanline: {
    //             visible: true
    //         }
    //     },
    //         {
    //             type: 'violin',
    //             x: dataX, //(rows, 'day'),
    //             y: dataY2, //unpack(rows, 'total_bill'),
    //             legendgroup: 'F',
    //             scalegroup: 'F',
    //             name: 'F',
    //             box: {
    //                 visible: true
    //             },
    //             line: {
    //                 color: 'pink',
    //             },
    //             meanline: {
    //                 visible: true
    //             }
    //         }
    //     ];
    //
    //     var layout = {
    //         title: "Grouped Violin Plot",
    //         yaxis: {
    //             zeroline: false
    //         },
    //         violinmode: 'group'
    //     };
    // }


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

    //add favorite icon to .infolayer .g-gtitle before <text>
    //var favoriteFalg = data[0]['favorite'];
    //console.log("favoriteFalg="+favoriteFalg);
    var glyphiconFavorite = "glyphicon-heart-empty";
    if( favoriteFalg ) {
        glyphiconFavorite = "glyphicon-heart";
    }
    var favoriteEl = '<div class="modebar-group">' +
        '<span id="'+favoriteDivId+'" ' +
        'class="favorite-icon glyphicon '+glyphiconFavorite+'" ' +
        'style="color:orangered;" ' +
        //' data-tooltip="Favorite Chart" ' +
        'onClick="favoriteChart(this,'+chartId+');"></span></div>';
    //var favoriteEl = '<div>!!!!!!!!!!!!</div>';
    //$( favoriteEl ).appendTo( "#"+divId );
    //$( favoriteEl ).appendTo( "#start-test" );
    //$(myPlot).find('.infolayer').find('.g-gtitle').append( favoriteEl );
    //$('.modebar').append( favoriteEl );
    $('#'+divId).find('.modebar').prepend( favoriteEl );
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

function favoriteChart(favoriteEl,chartId) {
    //console.log("favorite clicked");
    printF($(favoriteEl),"favorite clicked");
    //$(favoriteEl).toggleClass("glyphicon-heart glyphicon-heart-empty");

    //console.log("chartId="+chartId);

    //toggle favorite
    $(favoriteEl).toggleClass("glyphicon-heart glyphicon-heart-empty");

    var url = Routing.generate('dashboard_toggle_favorite');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "POST",
        data: {
            chartId: chartId
        },
        async: true,
    }).success(function(response) {
        var result = response['result'];
        var favorite = response['favorite'];
        //console.log("result="+result+", favorite="+favorite);
        if( result == "OK" ) {
            //$(favoriteEl).toggleClass("glyphicon-heart glyphicon-heart-empty");
            //set exact favorite
            if( favorite ) {
                $(favoriteEl).removeClass("glyphicon-heart-empty").addClass("glyphicon-heart");
            } else {
                $(favoriteEl).removeClass("glyphicon-heart").addClass("glyphicon-heart-empty");
            }
        }
    }).done(function() {
        //lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    });
    
    return;
}
// function favoriteChart_ORIG(chartId) {
//     console.log("chartId="+chartId);
//     $('#'+chartId).toggleClass("glyphicon-heart glyphicon-heart-empty");
//     return;
// }
// function favoriteToggleButtonInit() {
//     $(".favorite-icon.glyphicon").click(function() {
//         $(this).toggleClass("glyphicon-heart glyphicon-heart-empty");
//     });
//
//     $(".star.glyphicon").click(function() {
//         console.log("star clicked");
//         $(this).toggleClass("glyphicon-star glyphicon-star-empty");
//     });
//
//     $(".heart.fa").click(function() {
//         $(this).toggleClass("fa-heart fa-heart-o");
//     });
// }

