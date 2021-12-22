// ./assets/js/components/Users.js

//http://127.0.0.1/order/index_dev.php/dashboards/api/users

import React, {Component} from 'react';
import axios from 'axios';
import ReactDOM from 'react-dom';
//import { Routes ,Route, Redirect, Link, withRouter } from 'react-router-dom';
import SingleChart from './SingleChart';

class Charts extends Component {
    constructor() {
        super();
        this.state = { charts: [], loading: true};

        //https://stackoverflow.com/questions/31612598/call-a-react-component-method-from-outside/46150660
        window.ChartsComponent = this;
    }

    componentDidMount() {
        //this.getCharts('dashboard');
    }

    alertMessage(){
        console.log("Called from outside");
    }

    plotlyGetChartsReact(thisSitename) {
        console.log("get react charts");
        //$("#error-message").empty();
        //$("#error-message").html('');

        $("#hidden-filter").show();
        this.getCharts(thisSitename);
    }

    addErrorLine(msg, type) {
        let error;
        if( type == 'error' ) {
            error = <div className={'alert alert-danger'}>{msg}</div>
        }
        if( type == 'warning' ) {
            error = <div className={'alert alert-warning'}>{msg}</div>
        }
        ReactDOM.render(error, document.getElementById('error-message'));
    }



    getCharts(thisSitename) {

        //console.log("getCharts thisSitename="+thisSitename);

        document.getElementById("charts").innerHTML = "";

        let startDate = $("#filter_startDate").val();
        //console.log("startDate="+startDate);

        let endDate = $("#filter_endDate").val();
        //console.log("endDate="+endDate);

        let projectSpecialty = $("#filter_projectSpecialty").select2("val");
        //console.log("projectSpecialty="+projectSpecialty);

        let chartTypes = $("#filter_chartType").select2("val");

        //filter_chartType
        let productservice = $("#filter_category").select2("val");
        //console.log("productservice:");
        //console.log(productservice);

        //var showLimited = $("#filter_showLimited:checked").val();
        //console.log("showLimited="+showLimited);

        let showLimited = 0;
        if( $("#filter_showLimited").is(":checked") ) {
            showLimited = 1;
        }
        //console.log("showLimited="+showLimited);

        let quantityLimit = $("#filter_quantityLimit").val();

        let totalChartCount = chartTypes.length;
        //console.log("totalChartCount="+totalChartCount);

        if( totalChartCount == 0 ) {
            const element = <div>Please select chart</div>;
            ReactDOM.render(element, document.getElementById('error-message'));
        }

        let retrievedChartCount = 0;
        //l.start();

        let chartDataArr = [];

        let chartHolder = document.getElementsByClassName("some_class")

        for(let i = 0; i < totalChartCount; i++) {
            let chartIndex = chartTypes[i];
            //console.log("chartIndex="+chartIndex);
            //https://blog.logrocket.com/how-to-make-http-requests-like-a-pro-with-axios/
            // axios.get(url).then(charts => {
            //     this.setState({ charts: charts.data, loading: false})
            // })

            //create chart div
            let divId = 'chart-' + chartIndex;
            let div = document.createElement("div");
            div.style.float = "left";
            div.style.margin = "10px";
            div.setAttribute('id', divId);
            document.getElementById("charts").appendChild(div);

            ReactDOM.render(
                <SingleChart
                    startDate={startDate}
                    endDate={endDate}
                    projectSpecialty={projectSpecialty}
                    productservice={productservice}
                    showLimited={showLimited}
                    quantityLimit={quantityLimit}
                    chartIndex={chartIndex}
                    chartDivId={divId}
                    chartDiv={div}
                />,
                document.getElementById(divId)
            )
        }

        // var url = Routing.generate('dashboard_api_charts'); //use FOSJsRoutingBundle
        // axios.get(url).then(charts => {
        //     this.setState({ charts: charts.data, loading: false})
        // })
    }







    //NOT USED
    plotlyAddChartReact(resultData) {

        //const loading = this.state.loading;

        if( !resultData ) {
            return false;
        }

        let chartData = resultData['data'];

        if( !chartData ) {
            this.addErrorLine("Missing chart data",'error');
            return false;
        }

        if( chartData['error'] ) {
            //console.log("chartData error");
            this.addErrorLine(chartData['error'],'error');
            return false;
        }
        if( chartData['warning'] ) {
            //console.log("chartData warning");
            this.addErrorLine(chartData['warning'],'warning');
            return false;
        }

        //plotlyAddChart(chartData['chartId'],chartData);

        this.createChart(chartData['chartId'],chartData);

    }

    //NOT USED
    createChart(chartIndex,chartData) {
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

    //NOT USED
    getCharts_ORIG(thisSitename) {

        console.log("getCharts thisSitename="+thisSitename);

        var l = Ladda.create($('#filter-btn').get(0));

        //Get chart id from the chart filter, then get chart data, then build chart
        //var url = Routing.generate('dashboard_api_charts'); //use FOSJsRoutingBundle
        let url = Routing.generate(thisSitename+'_single_chart');

        document.getElementById("charts").innerHTML = "";

        let startDate = $("#filter_startDate").val();
        //console.log("startDate="+startDate);

        let endDate = $("#filter_endDate").val();
        //console.log("endDate="+endDate);

        let projectSpecialty = $("#filter_projectSpecialty").select2("val");
        //console.log("projectSpecialty="+projectSpecialty);

        let chartTypes = $("#filter_chartType").select2("val");

        //filter_chartType
        let productservice = $("#filter_category").select2("val");
        //console.log("productservice:");
        //console.log(productservice);

        //var showLimited = $("#filter_showLimited:checked").val();
        //console.log("showLimited="+showLimited);

        let showLimited = 0;
        if( $("#filter_showLimited").is(":checked") ) {
            showLimited = 1;
        }
        //console.log("showLimited="+showLimited);

        let quantityLimit = $("#filter_quantityLimit").val();

        let totalChartCount = chartTypes.length;
        //console.log("totalChartCount="+totalChartCount);

        if( totalChartCount == 0 ) {
            const element = <div>Please select chart</div>;
            ReactDOM.render(element, document.getElementById('error-message'));
        }

        let retrievedChartCount = 0;
        l.start();

        let chartDataArr = [];

        for(let i = 0; i < totalChartCount; i++) {
            let chartIndex = chartTypes[i];
            //console.log("chartIndex="+chartIndex);

            axios({
                method: 'post',
                url: url,
                data: {
                    startDate:startDate,
                    endDate:endDate,
                    projectSpecialty:projectSpecialty,
                    showLimited:showLimited,
                    quantityLimit:quantityLimit,
                    chartType:chartIndex,
                    productservice:productservice
                }
            })
            .then((result) => {
                this.plotlyAddChartReact(result);

                retrievedChartCount++;
                if( totalChartCount == retrievedChartCount ) {
                    l.stop();
                }

            }, (error) => {
                //console.log(error);
                var errorMsg = "Unexpected Error. " +
                    "Please make sure that your session is not timed out and you are still logged in, " +
                    "or select a smaller time period for this chart. "+error;
                //errorMsg = errorMsg + " responseText="+jqXHR.responseText+", textStatus="+textStatus+", errorThrown="+errorThrown;
                this.addErrorLine(errorMsg,'error');

                l.stop();
            });
        }
    }

    render() {
        return false;
    }

    render_TEST() {
        const loading = this.state.loading;

        var renderObject = (
            <div>
                Hello!

                {loading ? (
                    <div className={'row text-center'}>
                        <span className="fa fa-spin fa-spinner fa-4x"></span>
                    </div>
                ) : (
                    <div className={'row'}>

                    </div>
                )}

            </div>
        );

        return (renderObject);
    }
}
export default Charts;
