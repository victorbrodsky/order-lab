// ./assets/js/components/Users.js

//http://127.0.0.1/order/index_dev.php/dashboards/api/users

import React, {Component} from 'react';
import axios from 'axios';
import ReactDOM from 'react-dom';
//import { Routes ,Route, Redirect, Link, withRouter } from 'react-router-dom';

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
        //console.log("get react charts");
        this.getCharts(thisSitename);
    }

    addErrorLine(msg, type) {
        let error
        if( type == 'error' ) {
            error = <div className={'alert alert-danger'}>{msg}</div>
        }
        if( type == 'warning' ) {
            error = <div className={'alert alert-warning'}>{msg}</div>
        }
        ReactDOM.render(error, document.getElementById('error-message'));
    }

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

    getCharts(thisSitename) {

        var l = Ladda.create($('#filter-btn').get(0));

        //var url = "http://127.0.0.1/order/index_dev.php/dashboards/api/charts";
        //var url = "http://127.0.0.1/order/dashboards/api/users";
        //var url = "http://jsonplaceholder.typicode.com/users";
        //var url = "/api/charts";

        //Get chart id from the chart filter, then get chart data, then build chart
        //var url = Routing.generate('dashboard_api_charts'); //use FOSJsRoutingBundle
        var url = Routing.generate(thisSitename+'_single_chart');

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

        var totalChartCount = chartTypes.length;
        //console.log("totalChartCount="+totalChartCount);

        if( totalChartCount == 0 ) {
            const element = <div>Please select chart</div>;
            ReactDOM.render(element, document.getElementById('error-message'));
        }

        var retrievedChartCount = 0;
        l.start();

        var chartDataArr = [];

        var i;
        for (i = 0; i < totalChartCount; i++) {
            var chartIndex = chartTypes[i];
            //console.log("chartIndex="+chartIndex);
            //https://blog.logrocket.com/how-to-make-http-requests-like-a-pro-with-axios/
            // axios.get(url).then(charts => {
            //     this.setState({ charts: charts.data, loading: false})
            // })

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
                //console.log(result);
                //chartDataArr.push(result);
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

                //const element = <div>{errorMsg}</div>;
                //ReactDOM.render(element, document.getElementById('error-message'));

            });
        }

        // var url = Routing.generate('dashboard_api_charts'); //use FOSJsRoutingBundle
        // axios.get(url).then(charts => {
        //     this.setState({ charts: charts.data, loading: false})
        // })
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

    render_ORIG() {
        const loading = this.state.loading;
        return(
            <div>
                <section className="row-section">
                    <div className="container">
                        <div className="row">
                            <h2 className="text-center"><span>List of charts</span>Created with <i
                                className="fa fa-heart"></i> by yemiwebby</h2>
                        </div>
                        {loading ? (
                            <div className={'row text-center'}>
                                <span className="fa fa-spin fa-spinner fa-4x"></span>
                            </div>
                        ) : (
                            <div className={'row'}>
                                { this.state.charts.map(chart =>
                                    <div className="col-md-10 offset-md-1 row-block" key={chart.id}>
                                        <ul id="sortable">
                                            <li>
                                                <div className="media">
                                                    <div className="media-left align-self-center">
                                                        <img className="rounded-circle"
                                                                src={chart.imageURL}/>
                                                    </div>
                                                    <div className="media-body">
                                                        <h4>{chart.name}</h4>
                                                        <p>{chart.description}</p>
                                                    </div>
                                                    <div className="media-right align-self-center">
                                                        <a href="#" className="btn btn-default">Contact Now</a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </section>
            </div>
        )
    }
}
export default Charts;
