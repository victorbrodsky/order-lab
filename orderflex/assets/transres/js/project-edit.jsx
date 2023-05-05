//import React from 'react'
//import ReactDOM from "react-dom/client"
//import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'

//import $ from 'jquery';

console.log("using transres project-edit.jsx");

import '/public/orderassets/AppTranslationalResearchBundle/form/js/transres.js';
import '/public/orderassets/AppTranslationalResearchBundle/form/js/transres-project-change-state.js';


// import {
//     transresHumanTissueListener,transresRequireTissueProcessingListener,
//     transresRequireArchivalProcessingListener,transresProjectFundedListener,
//     transresNeedStatSupportListener,transresNeedInfSupportListener,transresNewUserListener,
//     transresIrbExemptListener,transresIrbStatusListListener,transresShowHideProjectDocument
// }
//     from '/public/orderassets/AppTranslationalResearchBundle/form/js/transres-test.js';
// var _transresprojecttypes = [];
// //_transresitemcodes = [];
//
// $(document).ready(function() {
//
//     var cycle = $("#formcycle").val();
//     var specialProjectSpecialty = $("#specialProjectSpecialty").val();
//
//     //var transresProject = new transresProject();
//
//     //console.log('transres form ready, cycle='+cycle);
//     transresHumanTissueListener(specialProjectSpecialty); //transresIrbApprovalLetterListener
//     transresRequireTissueProcessingListener();
//     transresRequireArchivalProcessingListener();
//
//     transresNeedStatSupportListener();
//     transresNeedInfSupportListener();
//     //transresIrbStatusListListener();
//
//     transresProjectFundedListener();
//
//     //console.log("cycle="+cycle);
//     if( cycle == "new" || cycle == "edit") {
//         transresNewUserListener();
//
//         //CONSTRUCT MODAL with Preloaded
//         if(1) {
//             var url = Routing.generate('employees_new_simple_user');
//             var comboboxValue = "[[lastName]]";
//             $.ajax({
//                 url: url,
//                 timeout: _ajaxTimeout,
//                 type: "GET",
//                 //type: "POST",
//                 data: {comboboxValue: comboboxValue},
//                 //dataType: 'json',
//                 async: asyncflag
//             }).success(function (response) {
//                 //console.log(response);
//                 //newUserFormHtml,fieldId,sitename,otherUserParam,appendHolder
//                 constructAddNewUserModalByForm(response, "[[fieldId]]", "translationalresearch", "[[otherUserParam]]", "#add-new-user-modal-prototype",false);
//             }).done(function () {
//                 //lbtn.stop();
//             }).error(function (jqXHR, textStatus, errorThrown) {
//                 console.log('Error : ' + errorThrown);
//             });
//         }
//     }
//
//     getComboboxGeneric(null,'transresprojecttypes',_transresprojecttypes,false);
//
//     transresIrbExemptListener('transres-project-exemptIrbApproval');
//
//     transresIrbExemptListener('transres-project-exemptIACUCApproval');
//
//     transresIrbStatusListListener('transres-project-irbStatusList');
//
//     transresShowHideProjectDocument();
//
// });
