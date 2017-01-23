/**
 * Created by DevServer on 4/10/15.
 */


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

//get search input field with id=calllogsearchform-search and redirect to path /patients/search?searchtype=search
function setCallLogSearchtypeAction() {
    //console.log('searchtype='+key);
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



function setNavBar(sitename) {



    if( typeof sitename === 'undefined' ) {
        sitename = getSitename();
    }

    if( sitename == "employees" ) {
        sitename = "directory";
    }

    console.log('sitename='+sitename);

    if( sitename == "scan" ) {
        setScanNavBar();
    }
    else if( sitename == "fellowship-applications" ){
        setFellappNavBar();
    }
    else if( sitename == "deidentifier" ){
        setDeidentificatorNavBar();
    }
    else if( sitename == "vacation-request" ){
        setVacReqNavBar();
    }
    else if( sitename == "call-log-book" ){
        setCallLogNavBar();
    }
    else {
        setDirectoryNavBar();
    }
}

function setCallLogNavBar() {

    calllogsearchNavbarBoxInit();

    var id = 'callloghome';

    var full = window.location.pathname;

    if( full.indexOf("/call-log-book/alerts/") !== -1 ) {
        id = 'alerts';
    }

    if( full.indexOf("/call-log-book/entry/") !== -1 ) {
        id = 'callentry';
    }

    if( full.indexOf("/call-log-book/complex-patient-list/") !== -1 ) {
        id = 'complexpatientlist';
    }

    if( full.indexOf("/call-log-book/resources/") !== -1 ) {
        id = 'resources';
    }

    if( full.indexOf("/call-log-book/event-log/event-log-per-user-per-event-type/") !== -1 ) {
        id = 'myentrees';
    }

    if( full.indexOf("/call-log-book/merge-patient-records") !== -1 ||
        full.indexOf("/call-log-book/un-merge-patient-records") !== -1
    ) {
        id = 'dataquality';
    }

    id = commonNavBar(full,id)

    //if( full.indexOf("/call-log-book/re-identify") !== -1 ) {
    //    id = null;
    //}

    $('#nav-bar-'+id).addClass('active');
}

function setVacReqNavBar() {
    var id = 'vacreqhome';

    var full = window.location.pathname;

    if( full.indexOf("/vacation-request/my-requests/") !== -1 ) {
        id = 'myrequests';
    }

    if( full.indexOf("/vacation-request/incoming-requests/") !== -1 ) {
        id = 'incomingrequests';
    }

    if( full.indexOf("/vacation-request/groups/") !== -1 || full.indexOf("/vacation-request/organizational-institution-management/") !== -1 ) {
        id = 'approvers';
    }

    if( full.indexOf("/vacation-request/away-calendar/") !== -1 ) {
        id = 'awaycalendar';
    }

    if( full.indexOf("/vacation-request/my-group/") !== -1 || full.indexOf("/vacation-request/carry-over-vacation-days/") !== -1 ) {
        id = 'mygroup';
    }

    if( full.indexOf("/vacation-request/carry-over-request/") !== -1 ) {
        id = 'carryoverrequest';
    }

    id = commonNavBar(full,id)

    if( full.indexOf("/vacation-request/re-identify") !== -1 ) {
        id = null;
    }

    $('#nav-bar-'+id).addClass('active');
}

function setDeidentificatorNavBar() {

    var id = 'deidentifierhome';

    var full = window.location.pathname;

    id = commonNavBar(full,id)

    if( full.indexOf("/deidentifier/re-identify") !== -1 ) {
        id = null;
    }

    $('#nav-bar-'+id).addClass('active');
}

function setFellappNavBar() {

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

    if( full.indexOf("/fellowship-applications/my-interviewees/") !== -1 ) {
        id = 'myinterviewees';
    }

    id = commonNavBar(full,id)


    $('#nav-bar-'+id).addClass('active');
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

    id = commonNavBar(full,id)


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

    id = commonNavBar(full,id)
    
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
