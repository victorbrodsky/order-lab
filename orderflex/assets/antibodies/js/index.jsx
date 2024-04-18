//Reference python/pages/frontend/src

import React from 'react'
import ReactDOM from "react-dom/client"
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import '../css/index.css'
import ScrollList from './components/ScrollList'


var _cycle = $('#antibodies-cycle').val();
console.log("_cycle="+_cycle);

const root = ReactDOM.createRoot(document.getElementById("root"));

root.render(
    <React.StrictMode>
        <Router>
            <ScrollList cycle={_cycle}/>
        </Router>
    </React.StrictMode>
);