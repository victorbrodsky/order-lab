/**
 * Created by DevServer on 4/10/15.
 */

//Old:set hidden input searchtype with id=ordersearchform-searchtype and submit form with id=ordersearchform

//get search input field with id=ordersearchform-search and redirect to path /patients/search?searchtype=search
function setSearchtypeAction(key) {

    console.log('searchtype='+key);

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


