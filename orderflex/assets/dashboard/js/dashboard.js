/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// yarn encore dev --watch
//yarn add select2

// any CSS you import will output into a single css file (app.css in this case)
//import '../css/app.css';

//https://www.twilio.com/blog/building-a-single-page-application-with-symfony-php-and-react
//https://stackoverflow.com/questions/63124161/attempted-import-error-switch-is-not-exported-from-react-router-dom
//react-router-dom v6

//import '/public/orderassets/AppUserdirectoryBundle/jquery/jquery-1.11.0.min.js';
//import $ from 'jquery';

////////////
//Problem: webpack compile order is not a listed order
//require('/public/orderassets/AppUserdirectoryBundle/bootstrap/js/bootstrap.min.js');

//Need?
//import '/public/orderassets/AppUserdirectoryBundle/ladda/spin.min.js';
//import '/public/orderassets/AppUserdirectoryBundle/ladda/ladda.min.js';
//require('/public/orderassets/AppUserdirectoryBundle/datepicker/js/bootstrap-datepicker.min.js');
//EOF Need?s

// require('/public/orderassets/AppUserdirectoryBundle/inputmask/jquery.inputmask.bundle.min.js');
// require('/public/orderassets/AppUserdirectoryBundle/q-1/q.js');
// require('/public/orderassets/AppUserdirectoryBundle/pnotify/pnotify.custom.min.js');
// require('/public/orderassets/AppUserdirectoryBundle/dropzone/dropzone.min.js');
// require('/public/orderassets/AppUserdirectoryBundle/idletimeout/jquery.idletimeout.js');
// require('/public/orderassets/AppUserdirectoryBundle/idletimeout/jquery.idletimer.js');
// require('/public/orderassets/AppUserdirectoryBundle/vakata-jstree/jstree.min.js');
// require('/public/orderassets/AppUserdirectoryBundle/typeahead/typeahead.bundle.min.js');


//my js
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-fileuploads.js');
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-navbar.js');
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-common.js');
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-form.js');
//
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-idleTimeout.js');
//
//
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-selectAjax.js');
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-treeSelectAjax.js');
//
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-jstree.js');
//
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-formnode.js');
//
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-formReady.js');
// //require('/public/orderassets/AppUserdirectoryBundle/form/js/user-formReady.js');
//
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-masking.js');
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-basetitles.js');
//
// require('/public/orderassets/AppOrderformBundle/form/js/modal.js');
// require('/public/orderassets/AppOrderformBundle/form/js/tooltips.js');
//
// require('/public/orderassets/AppUserdirectoryBundle/form/js/user-typeahead.js');

///////////


import React from 'react';
import ReactDOM from 'react-dom';

import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import '../css/app.css';
//import Home from './components/Home';
import Charts from './components/Charts';



// import '/public/orderassets/AppUserdirectoryBundle/select2/select2.js';
//require ('/public/orderassets/AppUserdirectoryBundle/charts/plotly/plotly.min.js');
//require ('/public/orderassets/AppUserdirectoryBundle/form/js/user-choices-plotly.js');


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
