
import React, {Component} from 'react';
import axios from 'axios';
import ReactDOM from 'react-dom';
//import { Routes ,Route, Redirect, Link, withRouter } from 'react-router-dom';

class SingleChart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            chartData: [],
            loading: true
        };

        //console.log("SingleChart constructor");
        //this.getChartData('dashboard');
    }

    componentDidMount() {
        //console.log("1 loading="+this.state.loading);
        this.getChartData('dashboard');
    }

    addErrorLine(msg, type) {
        let error
        if( type == 'error' ) {
            error = <div id="dashboard-alert-msg" className={'alert alert-danger dashboard-alert-msg added-by-singlechart-js'}>{msg}</div>
        }
        if( type == 'warning' ) {
            error = <div id="dashboard-alert-msg" className={'alert alert-warning dashboard-alert-msg added-by-singlechart-js'}>{msg}</div>
        }
        ReactDOM.render(error, document.getElementById('error-message'));
    }

    plotlyAddChartReact(resultData, chartDiv) {

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

        this.createChart(chartData['chartId'],chartData, chartDiv);

    }

    createChart( chartIndex, chartData, plotElement ) {
        let chartId = chartData['chartId'];
        let layout = chartData['layout'];
        let data = chartData['data'];
        let favoriteFalg = chartData['favorite'];
        //console.log("data:");
        //console.log(data);

        //var chartId = data[0]['id'];
        //console.log("chartId="+chartId);

        /*//var divId = 'chart-' + chartIndex;
        var chartDivId = 'chart-' + chartId;
        var div = document.createElement("div");
        div.style.float = "left";
        div.style.margin = "10px";
        div.setAttribute('id', divId);
        document.getElementById("charts").appendChild(div);*/

        //create favorite icon glyphicon glyphicon-heart
        //show a toggle button with a heart glyphicon under each chart (right-justified)
        let favoriteDivId = 'chart-favorite-' + chartIndex;

        //let divId = this.props.chartDivId;
        //console.log("2 Chart chartDivId="+plotElement.id);

        //let plotElement = this.props.chartDiv; //document.getElementById(chartDivId)

        //Plotly.newPlot(plotElement, data, layout);
        Plotly.react(plotElement, data, layout);

        //let plotElement = document.getElementById(chartDivId);

        plotElement.on('plotly_click', function(data){
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
        $(plotElement).find('.modebar').prepend( favoriteEl );
    }

    getChartData(thisSitename) {
        let url = Routing.generate(thisSitename+'_single_chart');
        //console.log("url="+url);

        let startDate = this.props.startDate; //$("#filter_startDate").val();
        //console.log("startDate="+startDate);

        let endDate = this.props.endDate; //$("#filter_endDate").val();
        //console.log("endDate="+endDate);

        let projectSpecialty = this.props.projectSpecialty; //$("#filter_projectSpecialty").select2("val");
        //console.log("projectSpecialty="+projectSpecialty);

        //filter_chartType
        let productservice = this.props.category; //$("#filter_category").select2("val");
        //console.log("productservice:");
        //console.log(productservice);

        let showLimited = this.props.showLimited;
        //console.log("showLimited="+showLimited);

        let quantityLimit = this.props.quantityLimit; //$("#filter_quantityLimit").val();

        let chartIndex = this.props.chartIndex;
        //console.log("SingleChart: chartIndex="+chartIndex);

        if( chartIndex === null ) {
            //console.log("SingleChart: chartIndex is null");
            const element = <div>Logical error: chart is not defined</div>;
            ReactDOM.render(element, document.getElementById('error-message'));
        }

        //console.log("SingleChart: chartIndex="+chartIndex);
        //https://stackoverflow.com/questions/51143730/axios-posting-empty-request: set header to 'Content-Type': 'application/x-www-form-urlencoded'

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
            },
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then((result) => {
            //console.log(result);
            this.setState({ chartData: result, loading: false})
            //console.log("2 loading="+this.state.loading);
        }, (error) => {
            //console.log(error);
            var errorMsg = "Unexpected Error. " +
                "Please make sure that your session is not timed out and you are still logged in, " +
                "or select a smaller time period for this chart. "+error;
            this.addErrorLine(errorMsg,'error');
        });

    }

    /*let renderObject1 = (
     <div>
     <section className="row-section">
     <div className="container">
     {loading ? (
     <div className={'row text-center'}>
     <span className="fa fa-spin fa-spinner fa-4x"></span>
     </div>
     ) : (
     <div className={'row'}>
     {this.plotlyAddChartReact(this.state.chartData,chartDivId)}
     </div>
     )}
     </div>
     </section>
     </div>
     );*/

    render() {
        const loading = this.state.loading;
        //console.log("loading="+loading);

        const chartDiv = this.props.chartDiv;

        let renderObject = (
            <div className="container">
                {loading ? (
                    <div className={'row text-center'}>
                        <span className="fa fa-spin fa-spinner fa-4x"></span>
                    </div>
                ) : (
                    <div>
                        {this.plotlyAddChartReact(this.state.chartData,chartDiv)}
                    </div>
                )}
            </div>
        );

        return (renderObject);
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
export default SingleChart;
