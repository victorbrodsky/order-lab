// ./assets/js/components/Users.js

//http://127.0.0.1/order/index_dev.php/dashboards/api/users

import React, {Component} from 'react';
import axios from 'axios';

class Charts extends Component {
    constructor() {
        super();
        this.state = { charts: [], loading: true};

        //https://stackoverflow.com/questions/31612598/call-a-react-component-method-from-outside/46150660
        window.ChartsComponent = this;
    }

    componentDidMount() {
        this.getCharts();
    }

    alertMessage(){
        console.log("Called from outside");
    }

    plotlyGetChartsReact(thsiSitename) {
        console.log("get react charts");
    }

    getCharts() {
        //var url = "http://127.0.0.1/order/index_dev.php/dashboards/api/charts";
        //var url = "http://127.0.0.1/order/dashboards/api/users";
        //var url = "http://jsonplaceholder.typicode.com/users";
        //var url = "/api/charts";
        //Get chart id from the chart filter, then get chart data, then build chart
        var url = Routing.generate('dashboard_api_charts'); //use FOSJsRoutingBundle
        axios.get(url).then(charts => {
            this.setState({ charts: charts.data, loading: false})
        })
    }

    render() {
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
