/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

var _institution = new Array();
var _commenttype = new Array();
var _identifiers = new Array();
var _fellowshiptype = new Array();
var _researchlabs = new Array();
var _locations = new Array();
var _buildings = new Array();

var _rooms = new Array();
var _suites = new Array();
var _floors = new Array();
var _mailboxes = new Array();
var _efforts = new Array();
var _addmintitles = new Array();
var _apptitles = new Array();


function initAllComboboxGeneric(newForm) {

    getComboboxGeneric(newForm,'identifierkeytype',_identifiers);
    getComboboxGeneric(newForm,'fellowshiptype',_fellowshiptype);
    getComboboxGeneric(newForm,'researchlabtitle',_researchlabs);
    getComboboxGeneric(newForm,'location',_locations,'');

    getComboboxGeneric(newForm,'room',_rooms);
    getComboboxGeneric(newForm,'suite',_suites);
    getComboboxGeneric(newForm,'floor',_floors);
    getComboboxGeneric(newForm,'mailbox',_mailboxes);
    getComboboxGeneric(newForm,'effort',_efforts);
    getComboboxGeneric(newForm,'administrativetitletype',_addmintitles);
    getComboboxGeneric(newForm,'appointmenttitletype',_apptitles);

    getComboboxBuidlings(newForm);

}


function setElementToId( target, dataarr, setId ) {
    if( dataarr == undefined || dataarr.length == 0 ) {
        return;
    }

    if( typeof setId === "undefined" ) {
        var firstObj = dataarr[0];
        var setId = firstObj.id;
    }

    //console.log("setId="+setId+", target="+target);
    $(target).select2('val', setId);

    return setId;
}

function getDataIdByText(arr,text) {
    var id = null;
    for(var k in arr) {
        if( arr[k].text == text ) {
            id = arr[k].id;
            break;
        }
    }
    return id;
}

//Generic ajax combobox
function getComboboxGeneric(holder,name,globalDataArray,urlprefix) {

    var targetid = ".ajax-combobox-"+name;

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder && holder.length > 0 ) {
        targetid = holder.find(targetid);

        if( targetid.length == 0 )
            return;
    }

    if( typeof urlprefix === 'undefined' ) {
        urlprefix = "generic/";
    }

    var url = getCommonBaseUrl("util/common/"+urlprefix+name,"employees");

    if( globalDataArray.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            globalDataArray = data;
            populateSelectCombobox( targetid, globalDataArray, "Select an option or type in a new value", false );
        });
    } else {
        populateSelectCombobox( targetid, globalDataArray, "Select an option or type in a new value", false );
    }

}




//#############  institution  ##############//
function getComboboxInstitution(holder) {

    setInstitutionTreeChildren(holder);

    var targetid = ".ajax-combobox-institution";

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);

        if( targetid.length == 0 )
            return;
    }

    var url = getCommonBaseUrl("util/common/"+"institution","employees"); //always use "employees" to get institution

    //console.log('cycle='+cycle);

    if( _institution.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _institution = data;
            populateParentChildTree( targetid, _institution, "Select an option or type in a new value", false, 'ajax-combobox-department' );
        });
    } else {
        populateParentChildTree( targetid, _institution, "Select an option or type in a new value", false, 'ajax-combobox-department' );
    }

}

function getComboboxCommentType(holder) {

    setCommentTypeTreeChildren(holder);

    var targetid = ".ajax-combobox-commenttype";

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);

        if( targetid.length == 0 )
            return;
    }

    var url = getCommonBaseUrl("util/common/"+"commenttype","employees"); //always use "employees" to get commenttype

    if( _commenttype.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _commenttype = data;
            populateParentChildTree( targetid, _commenttype, "Select an option or type in a new value", false, 'ajax-combobox-commentsubtype' );
        });
    } else {
        populateParentChildTree( targetid, _commenttype, "Select an option or type in a new value", false, 'ajax-combobox-commentsubtype' );
    }

}


function getComboboxBuidlings(holder) {

    getComboboxGeneric(holder,'building',_buildings,'');

    //add listener for: Pull in the address of the building into the address fields once the building is selected
    $('.ajax-combobox-building').on("change", function(e) {

        var holder = $(this).closest('.user-collection-holder');
        //console.log(holder);

        setGeoLocation( holder, _buildings, $(this).select2('val') );
    });

}



