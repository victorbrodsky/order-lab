import React from 'react'
import ReactDOM from "react-dom/client"
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
//import '../css/index.css'
//import UserTable from './components/UserTable'

// ReactDOM.render(
// <React.StrictMode>
// <App />
// </React.StrictMode>,
//     document.getElementById('root')
// );

//var _cycle = $('#user-dates-cycle').val();
var _cycle = 'show';
console.log("_cycle="+_cycle);

const root = ReactDOM.createRoot(document.getElementById("root"));

// root.render(
//     <UserTable cycle={_cycle}/>
// );

// root.render(
//     <React.StrictMode>
//         <Router>
//             <AntibodyList cycle={_cycle}/>
//         </Router>
//     </React.StrictMode>
// );

