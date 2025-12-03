/*
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by DevServer on 4/10/15.
 */

//import { getSitename } from '/public/orderassets/AppUserdirectoryBundle/form/js/user-common.js';
//Window.prototype.setNavBar = setNavBar;

function ordersearchNavbarBoxInit() {

    //set searchtype
//    var currentSearchType = getSearchType();
//    var searchtypeValue = $('#ordersearchform-searchtype').val();
//    //console.log('currentSearchType='+currentSearchType+ ", searchtypeValue="+searchtypeValue);
//    if( currentSearchType != searchtypeValue ) {
//        if( searchtypeValue && searchtypeValue != "" ) {
//            var searchtypeButton = $('#ordersearch-searchtype-button');
//            searchtypeButton.html(searchtypeValue+' <span class="caret"></span>');
//        }
//    }

    //listen on enter
    //$("#ordersearchform-search").bind("keypress", function(event) {
    $("#ordersearchform-search").on( "keydown", function(event) {
        if(event.which == 13) {
            event.preventDefault();
            //var searchtypeValue = $('#ordersearchform-searchtype').val();
            setSearchtypeAction();
        }
    });
}

function calllogsearchNavbarBoxInit() {
    //console.log('calllogsearchNavbarBox Init');
    $("#search_search").on( "keydown", function(event) {
        //console.log('calllogsearchform event='+event.which);
        if(event.which == 13) {
            event.preventDefault();
            $('#calllogsearchform').submit();
        }
    });
}

function crnsearchNavbarBoxInit() {
    //console.log('crnsearchNavbarBox Init');
    $("#search_search").on( "keydown", function(event) {
        //console.log('crnsearchform event='+event.which);
        if(event.which == 13) {
            event.preventDefault();
            $('#crnsearchform').submit();
        }
    });
}

//get search input field with id=calllogsearchform-search and redirect to path /patients/search?searchtype=search
function setCallLogSearchtypeAction() {
    //console.log('searchtype='+key);

    ////set metaphone
    //var metaphone = $('#search_metaphone:checked').val();
    ////console.log('metaphone='+metaphone);
    //if( metaphone ) {
    //    $('.navbar-search-metaphone').prop('checked', true);
    //}

    $('#calllogsearchform').submit();
    return;
}

//get search input field with id=ordersearchform-search and redirect to path /patients/search?searchtype=search
function setSearchtypeAction(searchType) {

    //console.log('searchtype='+key);

    if( typeof searchType === 'undefined' || searchType == "" ) {
        searchType = $('#ordersearchform-searchtype').val();
    }

    //override searchtype in dropdown menu
    var searchtypeButton = $('#ordersearch-searchtype-button');
    searchtypeButton.html(searchType+' <span class="caret"></span>');
    $('#ordersearchform-searchtype').val(searchType);

    //console.log('searchType='+searchType);

    var searchValue = $('#ordersearchform-search').val();

    if( searchValue == '' ) {
        //alert('Please specify a search criterion');
        return false;
    }

    //$('#ordersearchform').submit();

    var searchUrl = getCommonBaseUrl("patients/search?"+searchType+'='+searchValue);

    window.location = searchUrl;
}

//DeidentifierNavbarSearch
function setDeidentifierNavbarSearchtypeAction(searchTypeId,searchTypeStr) {

    //console.log('searchtype='+key);

    if( typeof searchTypeStr === 'undefined' || searchTypeStr == "" ) {
        searchTypeId = $('#deidentifier-searchtype-button').attr("data-id");
        searchTypeStr = $('#deidentifier-searchtype-button').attr("data-str");
    }

    //override searchtype in dropdown menu
    var searchtypeButton = $('#deidentifier-searchtype-button');
    searchtypeButton.html(searchTypeStr+' <span class="caret"></span>');
    $('#accessionTypeId').val(searchTypeId);

    //console.log('searchType='+searchType);

    //change masking
    //setAccessiontypeMask( $('#deidentifier-searchtype-button'), true );
    //manual change mask
    var parent = $('#deidentifier-searchtype-button').closest('.accession-holder');
    var accField = parent.find('.accession-mask');
    //console.log(accField);
    //clean field
    accField.val('');
    clearErrorField(accField);
    //assign new mask
    swicthMaskAccessionTypeText(null,accField,searchTypeStr);

    //"DID-" in the search box in nav bar is invisible unless you hover the cursor over it - it should be visible without hovering.
    focusMaskField(accField);

    return false;
}
function initDeidentifierNavbarSearchMask() {

    //init search span btn on enter press
    //console.log('ordersearchNavbarBoxInit');
    $("#deidentifiersearchform-searchtype").on( "keydown", function(event) {
        //console.log('event.which='+event.which);
        if(event.which == 13) {
            event.preventDefault();
            //click on span btn
            $(this).parent().find('span').click();
        }
    });

    //init accession mask
    var parent = $('#deidentifier-searchtype-button').closest('.accession-holder');
    var accField = parent.find('.accession-mask');
    //console.log(accField);
    //console.log("accField.val()="+accField.val());
    //searchTypeStr
    var searchTypeStr = $('#deidentifier-searchtype-button').attr("data-str");
    //console.log("searchTypeStr="+searchTypeStr);
    //assign new mask
    swicthMaskAccessionTypeText(null,accField,searchTypeStr);

    //set original accessionNumber
    var accessionNumber = accField.attr("data-accessionNumber");
    accField.val(accessionNumber);

    //"DID-" in the search box in nav bar is invisible unless you hover the cursor over it - it should be visible without hovering.
    focusMaskField(accField);
}
function focusMaskField(field) {
    //printF(field,"focus Mask Field:");
    field.mouseover();
}
//function setDIDPlaceholder(accField,searchTypeStr) {
//    //"DID-" in the search box in nav bar is invisible unless you hover the cursor over it - it should be visible without hovering.
//    if( searchTypeStr == "Deidentifier ID" ) {
//        accField.attr("placeholder","DID-");
//    } else {
//        accField.attr("placeholder","");
//    }
//}


//function getSearchType_TODEL() {
//    var searchType = $('#ordersearch-searchtype-button').html();
//    //remove <span class="caret"></span>
//    searchType = searchType.replace(' <span class="caret"></span>', '');
//    //console.log('searchType='+searchType);
//    return searchType;
//}

function userAddActiveClass( id ) {
    //remove active fromm all other li in .navbar
    $('li.active').removeClass('active');

    //console.log("activeId =" + '#nav-bar-'+id);
    $('#nav-bar-'+id).addClass('active');

}

//sitename is the portion of the url i.e. call-log-book
function setNavBar(sitename) {
    //console.log('setNavBar: 1sitename='+sitename);
    if( typeof sitename === 'undefined' ) {
        sitename = getSitename();
    }

    if( sitename == "employees" ) {
        sitename = "directory";
    }

    //console.log('setNavBar: 2sitename='+sitename);

    if( sitename == "scan" ) {
        setScanNavBar();
    }
    else if( sitename == "fellowship-applications" ){
        setFellappNavBar();
    }
    else if( sitename == "residency-applications" ){
        setResappNavBar();
    }
    else if( sitename == "deidentifier" ){
        setDeidentificatorNavBar();
    }
    else if( sitename == "time-away-request" ){
        setVacReqNavBar();
    }
    else if( sitename == "call-log-book" ){
        setCallLogNavBar();
    }
    else if( sitename == "critical-result-notifications" ){
        setCrnNavBar();
    }
    else if( sitename == "translational-research" ){
        setTranslationalResearchNavBar();
    }
    else {
        setDirectoryNavBar();
    }
}

function setTranslationalResearchNavBar() {

    var id = 'translationalresearch-home';

    var full = window.location.pathname;

    //if( full.indexOf("/translational-research/project/new/hemepath-project") !== -1 ) {
    //    id = 'translationalresearch-new-project-hemepath-project';
    //}
    //if( full.indexOf("/translational-research/project/new/ap-cp-project") !== -1 ) {
    //    id = 'translationalresearch-new-project-ap-cp-project';
    //}
    // if( full.indexOf("/translational-research/projects/") !== -1 ) {
    //     id = 'translationalresearch-projects';
    // }
    if( full.indexOf("/translational-research/project/new") !== -1 ) {
        id = 'translationalresearch-projects';
    }
    if( full.indexOf("/translational-research/my-projects") !== -1 ) {
        id = 'translationalresearch-projects';
    }
    if( full.indexOf("/translational-research/projects-assigned-to-me-for-review") !== -1 ) {
        id = 'translationalresearch-projects';
    }
    if( full.indexOf("/translational-research/projects-where-i-am-requester") !== -1 ) {
        id = 'translationalresearch-projects';
    }
    if( full.indexOf("/translational-research/projects-pending-my-review") !== -1 ) {
        id = 'translationalresearch-projects';
    }
    if( full.indexOf("/translational-research/") !== -1 && full.indexOf("projects") !== -1 ) {
        id = 'translationalresearch-projects';
    }
    if( full.indexOf("/translational-research/active-project-requests-with-approval-expiring-soon") !== -1 ) {
        id = 'translationalresearch-projects';
    }
    if( full.indexOf("/translational-research/approved-project-requests-funded") !== -1 ) {
        id = 'translationalresearch-projects';
    }


    if( full.indexOf("/translational-research/workflow") !== -1 ) {
        id = 'translationalresearch-configuration';
    }
    if( full.indexOf("/translational-research/list/translational-research-work-request-products-and-services") !== -1 ) {
        id = 'translationalresearch-configuration';
    }
    if( full.indexOf("/translational-research/list/antibodies") !== -1 ) {
        id = 'translationalresearch-configuration';
    }
    if( full.indexOf("/translational-research/request/fee-schedule") !== -1 ) {
        id = 'translationalresearch-configuration';
    }
    if( full.indexOf("/translational-research/default-reviewers/") !== -1 ) {
        id = 'translationalresearch-configuration';
    }
    if( full.indexOf("/translational-research/substitute-user/") !== -1 ) {
        id = 'translationalresearch-configuration';
    }
    if( full.indexOf("/translational-research/antibodies/") !== -1 ) {
        id = 'translationalresearch-configuration';
    }

    if( full.indexOf("/translational-research/work-request/new/") !== -1 ) {
        id = 'translationalresearch-allmenu-requests';
    }
    if( full.indexOf("/translational-research/work-requests") !== -1 ) {
        id = 'translationalresearch-allmenu-requests';
    }

    if( full.indexOf("/translational-research/orderables/") !== -1 ) {
        id = 'translationalresearch-allmenu-queues';
    }

    if( full.indexOf("/translational-research/invoice/list") !== -1 ) {
        id = 'translationalresearch-allmenu-invoices';
    }

    if( full.indexOf("/translational-research/site-parameters/") !== -1 ) {
        id = 'translationalresearch-default-siteparameters';
    }

    if( full.indexOf("/translational-research/dashboard/") !== -1 ) {
        id = 'translationalresearch-dashboard';
    }

    //home page
    if( full.indexOf("/translational-research/projects/") !== -1 ) {
        id = 'translationalresearch-home';
    }

    id = commonNavBar(full,id);

    //if( full.indexOf("/translational-research/event-log/event-log-per-user-per-event-type/") !== -1 ) {
    //    id = 'mycalllogentrees';
    //}

    userAddActiveClass(id);
}

function setCallLogNavBar() {

    calllogsearchNavbarBoxInit();

    var id = 'callloghome';

    var full = window.location.pathname;

    if( full.indexOf("/call-log-book/alerts/") !== -1 ) {
        id = 'alerts';
    }

    if( full.indexOf("/call-log-book/entry/new") !== -1 ) {
        id = 'callentry';
    }

    if( full.indexOf("/call-log-book/entry/view") !== -1 ) {
        id = 'callentry-dummy';
    }

    if( full.indexOf("/patient-list/") !== -1 ) {
        id = 'patientlist';
    }
    if( full.indexOf("/recent-patients") !== -1 ) {
        id = 'patientlist';
    }
    if( full.indexOf("/accession-list/") !== -1 ) {
        id = 'patientlist';
    }
    if( full.indexOf("/recent-accessions") !== -1 ) {
        id = 'patientlist';
    }

    if( full.indexOf("/call-log-book/resources/") !== -1 ) {
        id = 'resources';
    }

    if( full.indexOf("/call-log-book/tasks/") !== -1 ) {
        id = 'tasks';
    }

    if( full.indexOf("/call-log-book/merge-patient-records") !== -1 ||
        full.indexOf("/call-log-book/un-merge-patient-records") !== -1 ||
        full.indexOf("/call-log-book/edit-patient-record") !== -1 ||
        full.indexOf("/call-log-book/find-and-edit-patient-record") !== -1 ||
        full.indexOf("/call-log-book/set-master-patient-record") !== -1
    ) {
        id = 'dataquality';
    }

    id = commonNavBar(full,id);

    if( full.indexOf("/call-log-book/event-log/event-log-per-user-per-event-type/") !== -1 ) {
        id = 'mycalllogentrees';
    }

    //if( full.indexOf("/call-log-book/re-identify") !== -1 ) {
    //    id = null;
    //}

    //$('#nav-bar-'+id).addClass('active');
    userAddActiveClass(id);
}

function setCrnNavBar() {

    crnsearchNavbarBoxInit();

    var id = 'crnhome';

    var full = window.location.pathname;

    if( full.indexOf("/critical-result-notifications/alerts/") !== -1 ) {
        id = 'alerts';
    }

    if( full.indexOf("/critical-result-notifications/entry/new") !== -1 ) {
        id = 'callentry';
    }

    if( full.indexOf("/critical-result-notifications/entry/view") !== -1 ) {
        id = 'callentry-dummy';
    }

    if( full.indexOf("/patient-list/") !== -1 ) {
        id = 'patientlist';
    }
    if( full.indexOf("/recent-patients") !== -1 ) {
        id = 'patientlist';
    }
    if( full.indexOf("/accession-list/") !== -1 ) {
        id = 'patientlist';
    }
    if( full.indexOf("/recent-accessions") !== -1 ) {
        id = 'patientlist';
    }

    if( full.indexOf("/critical-result-notifications/resources/") !== -1 ) {
        id = 'resources';
    }

    if( full.indexOf("/critical-result-notifications/tasks/") !== -1 ) {
        id = 'tasks';
    }

    if( full.indexOf("/critical-result-notifications/merge-patient-records") !== -1 ||
        full.indexOf("/critical-result-notifications/un-merge-patient-records") !== -1 ||
        full.indexOf("/critical-result-notifications/edit-patient-record") !== -1 ||
        full.indexOf("/critical-result-notifications/find-and-edit-patient-record") !== -1 ||
        full.indexOf("/critical-result-notifications/set-master-patient-record") !== -1
    ) {
        id = 'dataquality';
    }

    id = commonNavBar(full,id);

    if( full.indexOf("/critical-result-notifications/event-log/event-log-per-user-per-event-type/") !== -1 ) {
        id = 'mycalllogentrees';
    }

    //if( full.indexOf("/critical-result-notifications/re-identify") !== -1 ) {
    //    id = null;
    //}

    //$('#nav-bar-'+id).addClass('active');
    userAddActiveClass(id);
}

function setVacReqNavBar() {
    var id = 'vacreqhome';

    var full = window.location.pathname;

    if( full.indexOf("/time-away-request/my-requests/") !== -1 ) {
        id = 'myrequests';
    }

    if( full.indexOf("/time-away-request/incoming-requests/") !== -1 ) {
        id = 'incomingrequests';
    }

    if( full.indexOf("/time-away-request/groups/") !== -1 || full.indexOf("/time-away-request/organizational-institution-management/") !== -1 ) {
        id = 'approvers';
    }

    if( full.indexOf("/time-away-request/away-calendar/") !== -1 ) {
        id = 'awaycalendar';
    }

    if( full.indexOf("/time-away-request/my-group/") !== -1 || full.indexOf("/time-away-request/carry-over-vacation-days/") !== -1 ) {
        id = 'mygroup';
    }

    if( full.indexOf("/time-away-request/carry-over-request/") !== -1 ) {
        id = 'carryoverrequest';
    }

    if( full.indexOf("/time-away-request/floating-day") !== -1 ) {
        id = 'floatingdayrequest';
    }

    if( full.indexOf("/time-away-request/observed-holidays") !== -1 ) {
        id = 'observedholidays';
    }

    if( full.indexOf("/manage-holiday-dates") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/manage-observed-holidays") !== -1 ) {
        id = 'admin';
    }

    id = commonNavBar(full,id);

    if( full.indexOf("/time-away-request/re-identify") !== -1 ) {
        id = null;
    }

    //console.log("id="+id);

    $('#nav-bar-'+id).addClass('active');
    //userAddActiveClass(id);
}

function setDeidentificatorNavBar() {

    var id = 'deidentifierhome';

    var full = window.location.pathname;

    id = commonNavBar(full,id);

    if( full.indexOf("/deidentifier/re-identify") !== -1 ) {
        id = null;
    }

    $('#nav-bar-'+id).addClass('active');
    //userAddActiveClass(id);
}

function setFellappNavBar() {
    //console.log("setFellappNavBar");

    var id = 'fellapphome';

    var full = window.location.pathname;

    if( full.indexOf("/fellowship-applications/new") !== -1 ) {
        id = 'fellappnew';
    }
    if( full.indexOf("/fellowship-applications/show") !== -1 ) {
        id = null;
    }
    if( full.indexOf("/fellowship-applications/edit") !== -1 ) {
        id = null;
    }

    if( full.indexOf("/fellowship-applications/fellowship-types-settings") !== -1 ) {
        id = 'fellappsettings';
    }
    if( full.indexOf("/fellowship-applications/fellowship-type") !== -1 ) {
        id = 'fellappsettings';
    }
    if( full.indexOf("/add-fellowship-application-type") !== -1 ) {
        id = 'fellappsettings';
    }

    if( full.indexOf("/fellowship-applications/my-interviewees/") !== -1 ) {
        id = 'myinterviewees';
    }

    if( full.indexOf("/fellowship-applications/send-rejection-emails") !== -1 ) {
        id = 'fellapprejectionemails';
    }

    if( full.indexOf("/fellowship-applications/accepted-fellows") !== -1 ) {
        id = 'fellappaccepted';
    }

    id = commonNavBar(full,id);


    $('#nav-bar-'+id).addClass('active');
    //userAddActiveClass(id);
}

function setResappNavBar() {

    var id = 'resapphome';

    var full = window.location.pathname;

    if( full.indexOf("/residency-applications/new") !== -1 ) {
        id = 'resappnew';
    }
    if( full.indexOf("/residency-applications/upload") !== -1 ) {
        id = 'resappnew';
    }

    if( full.indexOf("/residency-applications/show") !== -1 ) {
        id = null;
    }
    if( full.indexOf("/residency-applications/edit") !== -1 ) {
        id = null;
    }

    if( full.indexOf("/residency-applications/residency-types-settings") !== -1 ) {
        id = 'resappsettings';
    }
    if( full.indexOf("/residency-applications/residency-type") !== -1 ) {
        id = 'resappsettings';
    }
    if( full.indexOf("/add-residency-application-type") !== -1 ) {
        id = 'resappsettings';
    }

    if( full.indexOf("/residency-applications/my-interviewees/") !== -1 ) {
        id = 'myinterviewees';
    }

    if( full.indexOf("/residency-applications/group-emails") !== -1 ) {
        id = 'resapprejectionemails';
    }

    if( full.indexOf("/residency-applications/accepted-residents") !== -1 ) {
        id = 'resappaccepted';
    }

    id = commonNavBar(full,id);


    $('#nav-bar-'+id).addClass('active');
    //userAddActiveClass(id);
}

function setScanNavBar() {

    ordersearchNavbarBoxInit();

    $('ul.li').removeClass('active');

    var full = window.location.pathname;
    //console.log("full="+full);

    var id = 'scanorderhome';

    if( full.indexOf("scan-order/multi-slide") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("scan-order/one-slide") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("scan-order/multi-slide-table-view") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("scan/slide-return-request") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("my-scan-orders") !== -1 ) {
        id = 'myrequesthistory';
    }

    if( full.indexOf("my-slide-return-requests") !== -1 ) {
        id = 'myrequesthistory';
    }

    if( full.indexOf("scan/patient/") !== -1 ) {
        id = 'patients';
    }

    if( full.indexOf("/incoming-scan-orders") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/incoming-slide-return-requests") !== -1 ) {
        id = 'admin';
    }

    id = commonNavBar(full,id);


    if( full.indexOf("/user/") !== -1 || full.indexOf("/edit-user-profile/") !== -1 ) {
        if( $('#nav-bar-admin').length > 0 ) {
            id = 'admin';
        } else {
            id = 'user';
        }
    }

    //console.log("scan id="+id);
    //console.info("full="+window.location.pathname+", id="+id + " ?="+full.indexOf("multi/clinical"));

    $('#nav-bar-'+id).addClass('active');
    //userAddActiveClass(id);
}



function setDirectoryNavBar() {

    $('ul.li').removeClass('active');

    var full = window.location.pathname;

    var id = null;

    if( full.indexOf("/user/new") !== -1 ) {
        id = 'add';
    }
    if( full.indexOf("/location/new") !== -1 ) {
        id = 'add';
    }

    if( full.indexOf("/employment-dates") !== -1 ) {
        id = 'employment-dates';
    }

    id = commonNavBar(full,id);
    
    if( full.indexOf("/users/previous") !== -1 ) {
        id = 'userlist-previous';
    }

    if( !id ) {
        if( full.indexOf("scan/user/") !== -1 || full.indexOf("/users/") !== -1 || full.indexOf("/edit-user-profile/") !== -1 ) {
            if( $('#nav-bar-admin').length > 0 ) {
                id = 'admin';
            } else {
                id = 'user';
            }
        }
    }
    
    if( !id ) {
        id = 'userhome';
    }

    //console.log("user id="+id);
    //console.info("full="+window.location.pathname+", id="+id + " ?="+full.indexOf("multi/clinical"));

    $('#nav-bar-'+id).addClass('active');
    //userAddActiveClass(id);
}

//common nav menues - mainly admin menue
function commonNavBar(full,id) {

    //Admin
    if( full.indexOf("/user/listusers") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/admin/") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/access-requests") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/account-requests") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/generated-users") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/listusers") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/user/") !== -1 && full.indexOf("/user/new") === -1 ) {
        id = 'user';
    }
    if( full.indexOf("/event-log") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/settings") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/authorized-users/") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/authorization-user-manager/") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/data-backup-management/") !== -1 ) {
        id = 'admin';
    }

    if( full.indexOf("/users") !== -1 ) {
        id = 'userlist';
    }
    if( full.indexOf("/about") !== -1 ) {
        id = 'user';
    }


    //no toggle if download
    if( full.indexOf("/thanks-for-downloading/") !== -1 ) {
        id = null;
    }

    return id;
}
