/**
 * Created by DevServer on 4/10/15.
 */


function ordersearchNavbarBoxInit() {

    //set searchtype
    var currentSearchType = getSearchType();
    var searchtypeValue = $('#ordersearchform-searchtype').val();
    //console.log('currentSearchType='+currentSearchType+ ", searchtypeValue="+searchtypeValue);
    if( currentSearchType != searchtypeValue ) {
        if( searchtypeValue && searchtypeValue != "" ) {
            var searchtypeButton = $('#ordersearch-searchtype-button');
            searchtypeButton.html(searchtypeValue+' <span class="caret"></span>');
        }
    }
    
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

//get search input field with id=ordersearchform-search and redirect to path /patients/search?searchtype=search
function setSearchtypeAction(searchType) {

    //console.log('searchtype='+key);

    if( typeof searchType === 'undefined' || searchType == "" ) {
        searchType = getSearchType();
    }

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

function getSearchType() {
    var searchType = $('#ordersearch-searchtype-button').html();
    //remove <span class="caret"></span>
    searchType = searchType.replace(' <span class="caret"></span>', '');
    //console.log('searchType='+searchType);
    return searchType;
}



function setNavBar(sitename) {

    //console.log('sitename='+sitename);

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

