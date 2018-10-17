
$(document).ready(function() {

//            var data = [{
//                values: [19, 26, 55],
//                labels: ['Residential', 'Non-Residential', 'Utility'],
//                type: 'pie'
//            }];
//            var layout = {
//                height: 400,
//                width: 500
//            };

    //var charts = '{{ chartsArray|json_encode|raw }}';

    //console.log("charts.length="+charts.length);

    if( charts.length == 0 ) {
        document.getElementById("charts").innerHTML = "No Data to Display";
    }

    for( var i = 0; i < charts.length; i++ ) {

        if( charts[i]['newline'] && charts[i]['newline'] == true ) {
            //console.log("newline");
            var newline = document.createElement("div");
            newline.style.float = "left";
            newline.style.width = "100%";
            //newline.setAttribute('id', divId);
            document.getElementById("charts").appendChild(newline);
        } else {

            var divId = 'chart-' + i;
            var div = document.createElement("div");
            div.style.float = "left";
            div.style.margin = "10px";
            div.setAttribute('id', divId);
            document.getElementById("charts").appendChild(div);

            var layout = charts[i]['layout'];
            var data = charts[i]['data'];

            //console.log("data:");
            //console.log(data);

            Plotly.newPlot(divId, data, layout);

            if( 1 ) {
                var myPlot = document.getElementById(divId);
                myPlot.on('plotly_click', function(data){
                    console.log("data:");
                    console.log(data);
                    var index = 0;
                    var link = null;
                    for(var i=0; i < data.points.length; i++){
                        index = data.points[i].i;
                        if( data.points[i].data.links ) {
                            link = data.points[i].data.links[index];
                        }
                    }
                    //alert('Closest point clicked:\n\n'+pts);
                    if( link ) {
                        window.open(link);
                    }
                });
            }
        }
    }



});