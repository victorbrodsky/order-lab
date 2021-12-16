/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// yarn encore dev --watch

// any CSS you import will output into a single css file (app.css in this case)
//import '../css/app.css';

//https://www.twilio.com/blog/building-a-single-page-application-with-symfony-php-and-react
//https://stackoverflow.com/questions/63124161/attempted-import-error-switch-is-not-exported-from-react-router-dom
//react-router-dom v6

import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import '../css/app.css';
import Home from './components/Home';
import Charts from './components/Charts';


// function plotlyGetChartsReact( thsiSitename ) {
//     console.log("app.js: plotlyGetChartsReact");
// }

// ReactDOM.render(<Router><Home /></Router>, document.getElementById('root'));

ReactDOM.render(<Router><Charts /></Router>, document.getElementById('root'));

const REACT_VERSION = React.version;
// ReactDOM.render(
// <div>React version: {REACT_VERSION}</div>,
//     document.getElementById('reactid')
// );

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';

console.log('Hello Webpack Encore! Edit me in assets/js/app.js. REACT_VERSION='+REACT_VERSION);
