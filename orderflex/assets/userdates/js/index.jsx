import React from 'react';
import ReactDOM from "react-dom/client";
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import '../css/index.css';
import UserTable from './components/UserTable';

// ReactDOM.render(
// <React.StrictMode>
// <App />
// </React.StrictMode>,
//     document.getElementById('root')
// );

var _cycle = $('#user-dates-cycle').val();
console.log("_cycle="+_cycle);

const root = ReactDOM.createRoot(document.getElementById("root"));

// root.render(
//     <UserTable cycle={_cycle}/>
// );

root.render(
    <React.StrictMode>
        <Router>
            <App cycle={_cycle}/>
        </Router>
    </React.StrictMode>
);

// const matchinginfo = ReactDOM.createRoot(document.getElementById("matching-info"));
// matchinginfo.render(
//     <React.StrictMode>
//         <MatchInfo />
//     </React.StrictMode>
// );

//https://react-bootstrap.github.io/getting-started/introduction/
//https://www.tutsmake.com/react-17-bootstrap-datepicker-example/
//https://stackoverflow.com/questions/37560863/react-datepicker-bootstrap-up-to-date
