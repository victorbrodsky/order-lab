//Reference python/pages/frontend/src

//import React from 'react'
//import ReactDOM from "react-dom/client"
//import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
//import '../css/index.css'
//import ScrollList from './components/ScrollList'

//import React, { Component, Fragment } from "react";
//import Header from "./components/Header";
//import Home from "./components/Home";
//import ScrollList from "./components/ScrollList";

import React from 'react'
import ReactDOM from "react-dom/client"
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import '../css/index.css'
import ScrollList from "./components/ScrollList";
import App from "./components/App";
//import BasicCard from "./components/BasicCard";
//import MediaCard from "./components/MediaCard";

// order-lab\orderflex\vendor\twbs\bootstrap\dist\js\bootstrap.js
//import '../../../vendor/twbs/bootstrap/dist/js/bootstrap.js';
//import '../../../vendor/twbs/bootstrap/dist/css/bootstrap.css';
//import 'bootstrap'; // adds functions to jQuery

var _cycle = $('#antibodies-cycle').val();
console.log("_cycle="+_cycle);

const root = ReactDOM.createRoot(document.getElementById("root"));

//const element = <h1>Hello, world</h1>;
//root.render(element);

root.render(
    <React.StrictMode>
        <Router>
            <App />
        </Router>
    </React.StrictMode>
);

// root.render(
//     <React.StrictMode>
//         <Router>
//             <ScrollList />
//         </Router>
//     </React.StrictMode>
// );
