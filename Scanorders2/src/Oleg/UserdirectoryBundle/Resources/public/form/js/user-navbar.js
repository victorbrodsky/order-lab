/**
 * Created by DevServer on 4/10/15.
 */

//Old:set hidden input searchtype with id=ordersearchform-searchtype and submit form with id=ordersearchform

//get search input field with id=ordersearchform-search and redirect to path /patients/search?searchtype=search
function setSearchtypeAction(key) {

    console.log('searchtype='+key);

    if( key == 'undefined') {
        //return false;
        key = 'MRN';
    }

    $('#ordersearchform-searchtype').val(key);

    var searchValue = $('#ordersearchform-search').val();

    if( searchValue == '' ) {
        //alert('Please specify a search criterion');
        return false;
    }

    //$('#ordersearchform').submit();

    var searchUrl = getCommonBaseUrl("patients/search?"+key+'='+searchValue);

    window.location = searchUrl;
}



function setNavBar(sitename) {

    if( typeof sitename === 'undefined' ) {
        sitename = getSitename();
    }

    if( sitename == "employees" ) {
        sitename = "directory";
    }

    if( sitename == "scan" ) {
        setScanNavBar();
    } else {
        setDirectoryNavBar();
    }
}


function setScanNavBar() {

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

    //Admin
    if( full.indexOf("/user/listusers") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/admin/") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/incoming-scan-orders") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/incoming-slide-return-requests") !== -1 ) {
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
    if( full.indexOf("/users") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/event-log") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/settings") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/about") !== -1 ) {
        id = 'user';
    }

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

    var id = 'userhome';

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
    if( full.indexOf("/user/") !== -1 ) {
        id = 'user';
    }
    if( full.indexOf("/event-log") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/settings") !== -1 ) {
        id = 'admin';
    }

    if( full.indexOf("/user/new") !== -1 ) {
        id = 'add';
    }
    if( full.indexOf("/locations/new") !== -1 ) {
        id = 'add';
    }

    if( full.indexOf("/users") !== -1 ) {
        id = 'userlist';
    }

    if( full.indexOf("/users/previous") !== -1 ) {
        id = 'userlist-previous';
    }

    if( full.indexOf("/about") !== -1 ) {
        id = 'user';
    }

    if( full.indexOf("scan/user/") !== -1 || full.indexOf("/users/") !== -1 || full.indexOf("/edit-user-profile/") !== -1 ) {
        if( $('#nav-bar-admin').length > 0 ) {
            id = 'admin';
        } else {
            id = 'user';
        }
    }

    //console.log("user id="+id);
    //console.info("full="+window.location.pathname+", id="+id + " ?="+full.indexOf("multi/clinical"));

    $('#nav-bar-'+id).addClass('active');
}

