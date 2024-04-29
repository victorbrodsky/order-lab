//Reference python/pages/frontend/src

import '../css/index.css'
//import '../css/pagination.css'

import React from 'react'
import ReactDOM from "react-dom/client"
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import PageList from "./components/PageList";
//import ScrollList from "./components/ScrollList";
//import App from "./components/App";



// order-lab\orderflex\vendor\twbs\bootstrap\dist\js\bootstrap.js
//import '../../../vendor/twbs/bootstrap/dist/js/bootstrap.js';
//import '../../../vendor/twbs/bootstrap/dist/css/bootstrap.css';
//import 'bootstrap'; // adds functions to jQuery

var _cycle = $('#antibodies-cycle').val();
console.log("_cycle="+_cycle);

const root = ReactDOM.createRoot(document.getElementById("root"));

//const element = <h1>Hello, world</h1>;
//root.render(element);

if(1) {
    root.render(
        <React.StrictMode>
            <Router>
                <PageList />
            </Router>
        </React.StrictMode>
    );
}
if(0) {
    root.render(
        <React.StrictMode>
            <Router>
                <ScrollList />
            </Router>
        </React.StrictMode>
    );
}
if(0) {
    root.render(
        <React.StrictMode>
            <Router>
                <App />
            </Router>
        </React.StrictMode>
    );
}