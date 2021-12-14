// ./assets/js/components/Home.js

import React, {Component} from 'react';
// import {Route, Switch, Redirect, Link, withRouter} from 'react-router-dom';
import { Routes ,Route, Redirect, Link, withRouter } from 'react-router-dom';
// import { Routes ,Route, Navigate, Link, withRouter } from 'react-router-dom';
import Users from './Users';
import Posts from './Posts';
import Charts from './Charts';

class Home extends Component {

    constructor() {
        super();
        this.testflag = $('#testflag').val();

        //https://stackoverflow.com/questions/31612598/call-a-react-component-method-from-outside/46150660
        window.ReactHomeComponent = this;
    }

    sayHello() {
        alert('Hello!');
    }

// <button
// id="react-filter-btn"
// type="button" class="btn btn-default"
// onclick={this.sayHello}
// data-spinner-color="{{ spinnerColor }}"
// >Filter</button>
    
    render() {

        var optionParam;
        if( this.testflag ) {
            optionParam = <li className="nav-item">{this.testflag}</li>
        } else {
            optionParam = null;
        }

        var returnEl = (
            <div>
                <nav className="navbar navbar-expand-lg navbar-dark bg-dark">
                    <Link className={"navbar-brand"} to={"order/index_dev.php/dashboards/react"}> Symfony React Project </Link>
                    <div className="collapse navbar-collapse" id="navbarText">
                        <ul className="navbar-nav mr-auto">
                            <li className="nav-item">
                                <Link className={"nav-link"} to={"/charts"}> Charts </Link>
                            </li>
                            <li className="nav-item">
                                <Link className={"nav-link"} to={"/posts"}> Posts </Link>
                            </li>
                            <li className="nav-item">
                                <Link className={"nav-link"} to={"/users"}> Users </Link>
                            </li>
                            {optionParam}
                        </ul>
                        <button onClick={this.sayHello}
                            data-spinner-color="{{ spinnerColor }}"
                        >Get Chart</button>
                    </div>
                </nav>
                <Routes >
                    <Route from="/" to="/users" />
                    <Route path="/charts" element={<Charts/>} />
                    <Route path="/users" element={<Users/>} />
                    <Route path="/posts" element={<Posts/>} />
                </Routes >
            </div>
        );

        return (returnEl);
    }
}

export default Home;

