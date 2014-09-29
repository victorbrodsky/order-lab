/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */


//var urlBase = $("#baseurl").val();
//var cicle = $("#formcicle").val();
////var user_keytype = $("#user_keytype").val();
//var user_name = $("#user_name").val();
//var user_id = $("#user_id").val();
//var orderinfoid = $(".orderinfo-id").val();
//var _projectTitle = new Array();
//var _courseTitle = new Array();

var _institution = new Array();

var _identifiers = new Array();

//var userpathserviceflag = false;



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




//#############  institution  ##############//
function getComboboxInstitution(holder) {

    setInstitutionTreeChildren(holder);

    var targetid = ".ajax-combobox-institution";

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    var url = getCommonBaseUrl("util/common/"+"institution","employees"); //always use "employees" to get institution

    //console.log('cicle='+cicle);

    if( _institution.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _institution = data;
            //populateSelectCombobox( targetid, _institution, "Select an option or type in a new value", false );
            populateInstitutionTree( targetid, _institution, "Select an option or type in a new value", false );
//            if( cicle == "new"  ) {
//                setElementToId( targetid, _institution );
//            }
        });
    } else {
        //populateSelectCombobox( targetid, _institution, "Select an option or type in a new value", false );
        populateInstitutionTree( targetid, _institution, "Select an option or type in a new value", false );
//        if( cicle == "new"  ) {
//            setElementToId( targetid, _institution );
//        }
    }

}


function getComboboxIdentifier(holder) {

    var targetid = ".ajax-combobox-identifierkeytype";

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    var url = getCommonBaseUrl("util/common/"+"identifierkeytype","employees"); //always use "employees" to get institution

    //console.log('cicle='+cicle);

    if( _identifiers.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _identifiers = data;
            populateSelectCombobox( targetid, _identifiers, "Select an option or type in a new value", false );
        });
    } else {
        populateSelectCombobox( targetid, _identifiers, "Select an option or type in a new value", false );
    }

}
