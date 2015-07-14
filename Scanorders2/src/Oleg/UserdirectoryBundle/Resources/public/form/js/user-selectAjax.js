/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

var _institution = new Array();
//var _institutionRoot = new Array();
var _treenodedata = new Array();
//var _userpositions = new Array();

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
var _medicaltitles = new Array();

//trainings 6 from 8
var _residencySpecialtys = new Array();
var _fellowshipSubspecialtys = new Array();
var _trainingmajors = new Array();
var _trainingminors = new Array();
var _traininghonors = new Array();
var _fellowshipTitles = new Array();
var _traininginstitution = new Array();
var _locationusers = new Array();

//grants
var _sourceorganization = new Array();
var _grants = new Array();

var _cities = new Array();
var _organizations = new Array();



function initAllComboboxGeneric(newForm) {

    getComboboxGeneric(newForm,'identifierkeytype',_identifiers,false);
    getComboboxGeneric(newForm,'fellowshiptype',_fellowshiptype,false);
    getComboboxGeneric(newForm,'researchlab',_researchlabs,false);
    getComboboxGeneric(newForm,'location',_locations,false,'');
    getComboboxGeneric(newForm,'building',_buildings,false,'');

    getComboboxGeneric(newForm,'room',_rooms,false);
    getComboboxGeneric(newForm,'suite',_suites,false);
    getComboboxGeneric(newForm,'floor',_floors,false);
    getComboboxGeneric(newForm,'mailbox',_mailboxes,false);
    getComboboxGeneric(newForm,'effort',_efforts,false);
    getComboboxGeneric(newForm,'administrativetitletype',_addmintitles,false);
    getComboboxGeneric(newForm,'appointmenttitletype',_apptitles,false);
    getComboboxGeneric(newForm,'medicaltitletype',_medicaltitles,false);

    //trainings
    getComboboxGeneric(newForm,'trainingmajors',_trainingmajors,true);
    getComboboxGeneric(newForm,'trainingminors',_trainingminors,true);
    getComboboxGeneric(newForm,'traininghonors',_traininghonors,true);
    getComboboxGeneric(newForm,'trainingfellowshiptitle',_fellowshipTitles,false);
    getComboboxGeneric(newForm,'traininginstitution',_traininginstitution,false);
    getComboboxGeneric(newForm,'locationusers',_locationusers,false,'');
    //getComboboxGeneric(newForm,'residencyspecialty',_residencySpecialtys,false);
    //getComboboxGeneric(newForm,'fellowshipsubspecialty',_fellowshipSubspecialtys,false);

    //grants
    getComboboxGeneric(newForm,'sourceorganization',_sourceorganization,false);
    getComboboxGeneric(newForm,'grant',_grants,false);

    getComboboxGeneric(newForm,'city',_cities,false);
    getComboboxGeneric(newForm,'organization',_organizations,false);

    //getComboboxGeneric(newForm,'userpositions',_userpositions,true);

    setBuidlingListener(newForm);

}


function setElementToId( target, dataarr, setId ) {
    if( typeof dataarr === "undefined" || dataarr == undefined || dataarr.length == 0 ) {
        $(target).select2('data', null);
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

//this function is used for form institution hierarchy using select2, not jstree
function getComboboxInstitution(holder) {

    if( typeof cycle === 'undefined' ) {
        var cycle = 'edit';
    }
    //console.log('inst cycle='+cycle);

    var targetid = ".ajax-combobox-institution";
    if( $(targetid).length == 0 ) {
        return;
    }
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
        if( targetid.length == 0 ) {
            return;
        }
    }

    var entityName = 'Institution';
    _treenodedata[entityName] = new Array();

    $(targetid).each( function(e){
        getComboboxSingleInstitution($(this),entityName);
    });
}

function getComboboxSingleInstitution(comboboxEl,entityName) {

    //console.log('getComboboxSingleInstitution:');
    //console.log(comboboxEl);

    var thisid = comboboxEl.val();
    //console.log('thisid='+thisid);

    if( !thisid ) {
        thisid = 0;
    }


    if( _treenodedata[entityName].hasOwnProperty(thisid) ) {
        populateComboboxData(entityName,comboboxEl,_treenodedata[entityName][thisid]);
        return;
    }


    getChildrenByParent(entityName,comboboxEl,thisid,null).
    then(function (optionData) {
        populateComboboxData(entityName,comboboxEl,optionData);
    });

}

function populateComboboxData(entityName,comboboxEl,optionData) {
    //console.log('populate combobox data:');
    //console.log(optionData);

    var rowElHtml = comboboxEl.closest('.row')[0].outerHTML;

    //console.log('populate combobox');
    populateSelectCombobox( comboboxEl, optionData, "Select an option" );

    if( !comboboxEl.hasClass('show-as-single-node') ) {
        comboboxTreeListener( comboboxEl, entityName, rowElHtml );
        comboboxEl.trigger('change');
        setParentComboboxree(comboboxEl, entityName, rowElHtml);
    }
}

function getChildrenByParent( entityName, thiselement, thisid, parentid, opt ) {

    return Q.promise(function(resolve, reject, treedata) {

        //console.log('entityName='+entityName+', thisid='+thisid+", parentid="+parentid);

        //do nothing if new element was enetered. In this case pid will be a string with a new element name.
        if( !isInt(thisid) && !isInt(parentid) ) {
            //console.log('thisid and pid not int');
            reject('id and pid null');
            return;
        }

        //console.log('_treenodedata:');
        //console.log(_treenodedata);
        if( thisid != null && _treenodedata[entityName].hasOwnProperty(thisid) ) {
            //console.log('_treenodedata exists for thisid='+thisid);
            resolve(_treenodedata[entityName][thisid]);
            return;
        }

        //console.log('get treenode data by ajax');

        var opt = 'combobox';
        //var treeHolder = thiselement.closest('.composite-tree-holder');
    //    if( treeHolder.hasClass('institution-with-userpositions') ) {
    //        opt = opt + ',userpositions';
    //    }

        //current userid
        var userid = null;
        var dataElement = document.getElementById("form-prototype-data");
        //console.log(dataElement);
        if( dataElement ) {
            userid = dataElement.getAttribute('data-userid');
            //console.log('userid='+userid);
        }

        //employees_get_institution
        var treeUrl = Routing.generate('employees_get_composition_tree');
        //console.log('treeUrl='+treeUrl);
        treeUrl = treeUrl + '?thisid=' + thisid + '&id=' + parentid + '&classname=' + entityName + '&opt=' + opt + '&userid=' + userid;
        //console.log('final treeUrl='+treeUrl);

        if( !_ajaxTimeout ) {
            var _ajaxTimeout = 20000;
        }

//        console.log('continue:');
//        console.log('ajaxTimeout='+_ajaxTimeout);
//        console.log('asyncflag='+asyncflag);

        try {
            $.ajax({
                url: treeUrl,
                timeout: _ajaxTimeout,
                async: asyncflag
            }).success(function(data) {
                //console.log(data);
                _treenodedata[entityName][thisid] = data;
                resolve(data);
            }).error(function() {
                //console.log('error getting nodes');
                reject('error getting nodes');
            });
        }
        catch(err) {
            console.log("ajax tree retrival failed err="+err);
            throw new Error("ajax tree retrival failed err="+err);
        }

    });
}

//function setComboboxInstitution(holder) {
//
//    var targetid = ".ajax-combobox-institution";
//
//    if( $(targetid).length == 0 ) {
//        return;
//    }
//
//    if( typeof holder !== 'undefined' && holder.length > 0 ) {
//        targetid = holder.find(targetid);
//
//        if( targetid.length == 0 )
//            return;
//    }
//
//    var url = getCommonBaseUrl("util/common/"+"institution-all","employees"); //always use "employees" to get institution
//
//    //console.log('cycle='+cycle);
//
//    if( _institution.length == 0 ) {
//        $.ajax({
//            url: url,
//            timeout: _ajaxTimeout,
//            async: asyncflag
//        }).success(function(data) {
//            _institution = data;
//            populateSelectCombobox( targetid, _institution, null );
//        });
//    } else {
//        populateSelectCombobox( targetid, _institution, null );
//    }
//
//}
//
//function getComboboxInstitution_OLD(holder) {
//
//    setInstitutionTreeChildren(holder);
//
//    var targetid = ".ajax-combobox-institution";
//
//    if( $(targetid).length == 0 ) {
//        return;
//    }
//
//    if( typeof holder !== 'undefined' && holder.length > 0 ) {
//        targetid = holder.find(targetid);
//
//        if( targetid.length == 0 )
//            return;
//    }
//
//    var url = getCommonBaseUrl("util/common/"+"institution","employees"); //always use "employees" to get institution
//
//    //console.log('cycle='+cycle);
//
//    if( _institution.length == 0 ) {
//        $.ajax({
//            url: url,
//            timeout: _ajaxTimeout,
//            async: asyncflag
//        }).success(function(data) {
//            _institution = data;
//            populateParentChildTree( targetid, _institution, "Select an option or type in a new value", false, 'ajax-combobox-department' );
//        });
//    } else {
//        populateParentChildTree( targetid, _institution, "Select an option or type in a new value", false, 'ajax-combobox-department' );
//    }
//
//}

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

function getComboboxResidencyspecialty(holder) {

    //hide residency Specialty on view if fellowship Subspecialty is not empty => get all residencyspecialty and fellowshipsubspecialty
    if( cycle == "show_user" ) {
        getComboboxGeneric(holder,'residencyspecialty',_residencySpecialtys,false);
        getComboboxGeneric(holder,'fellowshipsubspecialty',_fellowshipSubspecialtys,false);
        return;
    }

    setResidencyspecialtyTreeChildren(holder);

    var targetid = ".ajax-combobox-residencyspecialty";

    var residencySpecialtyFound = false;

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);

        if( targetid.length == 0 ) {
            return;
        }
    }

    var url = getCommonBaseUrl("util/common/generic/"+"residencyspecialty","employees");

    //console.log('cycle='+cycle);

    if( _residencySpecialtys.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _residencySpecialtys = data;
            populateParentChildTree( targetid, _residencySpecialtys, "Select an option or type in a new value", false, 'ajax-combobox-fellowshipsubspecialty' );
        });
    } else {
        populateParentChildTree( targetid, _residencySpecialtys, "Select an option or type in a new value", false, 'ajax-combobox-fellowshipsubspecialty' );
    }

}


function setBuidlingListener(holder) {
    //add listener for: Pull in the address of the building into the address fields once the building is selected
    $('.ajax-combobox-building').on("change", function(e) {
        var holder = $(this).closest('.user-collection-holder');
        //console.log(holder);
        setGeoLocation( holder, $(this).select2('data') );
    });
}



