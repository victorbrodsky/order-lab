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
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

var asyncflag = true;
//var combobox_width = '100%'; //'element'
//var urlCommon = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/util/";
//var urlCommon = "http://collage.med.cornell.edu/order/util/";
var urlBase = $("#baseurl").val();
//var urlCommon = urlBase+"util/";
//var type = $("#formtype").val();
var cycle = $("#formcycle").val();
//var user_keytype = $("#user_keytype").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();
var proxyuser_name = $("#proxyuser_name").val();
var proxyuser_id = $("#proxyuser_id").val();
//console.log("urlCommon="+urlCommon);
var messageid = $(".message-id").val();

var _mrntype = [];
var _accessiontype = [];
var _partname = [];
var _parttitle = [];
var _blockname = [];
var _stain = [];
var _scanregion = [];
var _procedure = [];
var _organ = [];
var _delivery = [];
var _returnLocation = [];
var _projectTitle = [];
var _courseTitle = [];
var _account = [];
var _urgency = [];
var _proxyuser = [];
var _encounterReferringProvider = [];
var _encounterAttendingPhysician = [];
var _referringProviderSpecialty = [];
var _locationName = [];

//generic select2 fields
var _labtesttype = [];
var _amendmentReason = [];

var _embedderinstruction = [];

var _buildings = [];
var _floors = [];
var _cities = [];
var _rooms = [];
var _suites = [];

var _patientLists = [];


//function regularCombobox() {
//    //resolve
//    $("select.combobox").select2({
//        width: combobox_width,
//        dropdownAutoWidth: true,
//        placeholder: "Select an option or type in a new value",
//        allowClear: true,
//        selectOnBlur: false
//        //readonly: true
//        //containerCssClass: 'combobox-width'
//    });
//}

function setResearchEducational() {
    //preselect with current user
//    if( proxyuser_id ) {
//        $("#s2id_oleg_orderformbundle_messagetype_proxyuser").select2('data', {id: proxyuser_id, text: proxyuser_name});
//    }

    //research
    //populateSelectCombobox( ".combobox-research-setTitle", null, "Select an option or type in a new value", false );
    //$(".combobox-research-setTitle").select2("readonly", true);
    populateSelectCombobox( ".combobox-optionaluser-research", null, "Select an option or type in a new value", false );
    $(".combobox-optionaluser-research").select2("readonly", true);

    //educational
    //multiple is set to false to make the width of the field to fit the form; otherwise, the data is not set and the width is too small to fit placeholder
//    populateSelectCombobox( ".combobox-educational-lessonTitle", null, "Select an option or type in a new value", false );
//    $(".combobox-educational-lessonTitle").select2("readonly", true);
    //multiple is set to false to make the width of the field to fit the form; otherwise, the data is not set and the width is too small to fit placeholder
    populateSelectCombobox( ".combobox-optionaluser-educational", null, 'Select an option or type in a new value', false );
    $(".combobox-optionaluser-educational").select2("readonly", true);

}

function customCombobox() {

    //console.log("custom Combobox cycle="+cycle);
    //console.log("custom Combobox urlBase="+urlBase);

    if( cycle && urlBase && cycle != 'edit_user' && cycle != 'accountreq' ) {
        //console.log("custom Combobox: urlBase="+urlBase+"; cycle="+cycle);
        getComboboxMrnType();
        getComboboxAccessionType();
        getComboboxPartname();
        getComboboxBlockname();
        getComboboxScanregion();
        getComboboxStain();
        getComboboxSpecialStain(["0","0","0","0","0","0"],true);
        getComboboxProcedure();
        getComboboxOrgan();
        getComboboxDelivery();
        //getComboboxReturn(new Array("0","0","0","0","0","0"));
        slideType(["0","0","0","0","0","0"]);
        //getProjectTitle();
        //getCourseTitle();

        getComboboxAccount();
        getComboboxReturnLocations();

        //holder,name,globalDataArray,multipleFlag,urlprefix,sitename,force
        getComboboxGeneric(null,'proxyuser',_proxyuser,true,'','scan');
        getComboboxGeneric(null,'parttitle',_parttitle,false,null,'scan',true);
        getComboboxGeneric(null,'embedderinstruction',_embedderinstruction,false,null,'scan');
        getComboboxGeneric(null,'encounterReferringProvider',_encounterReferringProvider,false,'','scan');
        getComboboxGeneric(null,'encounterAttendingPhysician',_encounterAttendingPhysician,false,'','scan');
        getComboboxGeneric(null,'referringProviderSpecialty',_referringProviderSpecialty,false);
        getComboboxGeneric(null,'locationName',_locationName,false,'');
    }

    getComboboxGeneric(null,'labtesttype',_labtesttype,false,null,'scan');
    getComboboxGeneric(null,'amendmentReason',_amendmentReason,false,null,'scan');

    //for test patient's contact information
    getComboboxGeneric(null,'building',_buildings,false,'');
    getComboboxGeneric(null,'room',_rooms,false);
    getComboboxGeneric(null,'suite',_suites,false);
    getComboboxGeneric(null,'floor',_floors,false);
    getComboboxGeneric(null,'city',_cities,false);


    getComboboxGeneric(null,'patientlists',_patientLists,true,'');
}

//function initDefaultServiceManually() {
//
//    var targetid = ".ajax-combobox-service";
//
//    if( $(targetid).length == 0 ) {
//        return;
//    }
//
//    var url = getCommonBaseUrl("util/"+"default-service");
//
//    var instid = $('.combobox-institution').select2('val');
//
//    //use curid to add current object.
//    var curid = null;
//    var curValue = $(targetid).select2('val');
//    //console.log("curValue="+curValue);
//    if( isInt(curValue) ) {
//        curid = curValue;
//    }
//
//    $.ajax({
//        url: url,
//        type: 'GET',
//        //data: {id: curid, instid: instid, orderid: messageid},
//        data: {id: curid, instid: instid},
//        timeout: _ajaxTimeout,
//        async: asyncflag
//    }).done(function(data) {
//        populateSelectCombobox( targetid, data, "Select an option or type in a new value" );
//    });
//}


//#############  stains  ##############//
function getComboboxStain(holder,force) {

    var url = getCommonBaseUrl("util/"+"stain", 'scan');

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "new" || cycle == "create" ) {
        url = url + "&opt=default";
    }

    var targetid = ".ajax-combobox-staintype";
    targetid = getElementTargetByHolder(holder,targetid);

    if( typeof force === 'undefined' ) {
        force = false;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    //console.log("_stain.length="+_stain.length);
    if( _stain.length == 0 ) {
        //console.log("stain 0");
        $.ajax({
            url: url,
            async: asyncflag,
            timeout: _ajaxTimeout
        }).done(function(data) {
            _stain = data;
            populateSelectCombobox( targetid, _stain, null );
        });
    } else {
        //console.log("stain exists");
        populateSelectCombobox( targetid, _stain, null );
    }

    if( cycle == "new"  ) {
//        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
//        var id= "#oleg_orderformbundle_messagetype_"+uid+"_";
//        var targetid = id+"stain_0_field";
        setElementToId( targetid, _stain );
    }

}

function getComboboxSpecialStain(ids, preset, setId) {

    var url = getCommonBaseUrl("util/"+"stain", 'scan');    //urlCommon+"stain";

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "new" || cycle == "create" ) {
        url = url + "&opt=default";
    }

    var targetid = "";
    if( cycle == "new" || (cycle == "amend" && preset) || (cycle == "edit" && preset) ) {
        //oleg_orderformbundle_messagetype_patient_0_encounter_0_procedure_0_accession_0_part_0_block_0_specialStains_0_staintype
        var uid = 'patient_'+ids[0]+'_encounter_'+ids[1]+'_procedure_'+ids[2]+'_accession_'+ids[3]+'_part_'+ids[4]+'_block_'+ids[5];
        var id= "#oleg_orderformbundle_messagetype_"+uid+"_";
        targetid = id+"specialStains_"+ids[6]+"_staintype";
        //console.log("targetid="+targetid);
    }

    if( $(targetid).length == 0 ) {
        return;
    }

    if( _stain.length == 0 ) {
        //console.log("_stain.length is zero");
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
                _stain = data;
                populateSelectCombobox( ".ajax-combobox-staintype", _stain, "Stain Type" );
            });
    } else {
        //console.log("populate _stain.length="+_stain.length);
        populateSelectCombobox( targetid, _stain, "Stain Type" );
    }

    //console.log("special stain preset="+preset);
    if( targetid != "" && typeof setId !== 'undefined' && setId ) {
        setElementToId( targetid, _stain, setId );
    }
}

//#############  scan regions  ##############//
function getComboboxScanregion(holder,force) {

    var url = getCommonBaseUrl("util/"+"scanregion", 'scan'); //urlCommon+"scanregion";
    //console.log("scanregion.length="+scanregion.length);

    var targetid = ".ajax-combobox-scanregion";
    targetid = getElementTargetByHolder(holder,targetid);

    if( typeof force === 'undefined' ) {
        force = false;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "&opt="+messageid;
    }

    if( _scanregion.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
            _scanregion = data;
            populateSelectCombobox( targetid, _scanregion, null );
        });
    } else {
        populateSelectCombobox( targetid, _scanregion, null );
    }

    if( cycle == "new"  ) {
//        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
//        var id= "#oleg_orderformbundle_messagetype_"+uid+"_";
//        var targetid = id+"scan_0_scanregion";
        //$(targetid).select2('data', {id: 'Entire Slide', text: 'Entire Slide'});
        setElementToId( targetid, _scanregion );
    }
}

//#############  source organs  ##############//
function getComboboxOrgan(holder,force) {
    var url = getCommonBaseUrl("util/"+"organ", 'scan');   //urlCommon+"organ";

    var targetid = ".ajax-combobox-organ";
    targetid = getElementTargetByHolder(holder,targetid);

    if( typeof force === 'undefined' ) {
        force = false;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "new" || cycle == "create" ) {
        url = url + "&opt=default";
    }

    if( _organ.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
                _organ = data;
            populateSelectCombobox( targetid, _organ, "Source Organ" );
        });
    } else {
        populateSelectCombobox( targetid, _organ, "Source Organ" );
    }

}


//#############  procedure types  ##############//
function getComboboxProcedure(holder,force) {
    var url = getCommonBaseUrl("util/"+"procedure", 'scan'); //urlCommon+"procedure";

    var targetid = ".ajax-combobox-procedure";
    targetid = getElementTargetByHolder(holder,targetid);

    if( typeof force === 'undefined' ) {
        force = false;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "new" || cycle == "create" ) {
        url = url + "&opt=default";
    }

    if( _procedure.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
            _procedure = data;
            populateSelectCombobox( targetid, _procedure, "Procedure Type" );
        });
    } else {
        populateSelectCombobox( targetid, _procedure, "Procedure Type" );
    }

}

//#############  Accession Type  ##############//
function getComboboxAccessionType(holder,force) {

    var url = getCommonBaseUrl("util/"+"accessiontype", 'scan');    //urlCommon+"accessiontype";
    //console.log("getComboboxAccessionType url="+url);

    var targetid = ".accessiontype-combobox";
    targetid = getElementTargetByHolder(holder,targetid);

    //console.log($(targetid));
    if( $(targetid).hasClass("skip-server-populate") ) {
        return;
    }

    if( typeof force === 'undefined' ) {
        force = false;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "new" || cycle == "create" ) {
        url = url + "&opt=default&type="+orderformtype;
    }

    //console.log("run url="+url);

    if( _accessiontype.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
                _accessiontype = data;
                populateSelectCombobox( targetid, _accessiontype, null );
                setAccessionMask();
            });
    } else {
        populateSelectCombobox( targetid, _accessiontype, null );
    }

    if( cycle == "new"  ) {
//        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2];
//        var id= "#oleg_orderformbundle_messagetype_"+uid+"_";
//        var targetid = id+"accession_0_accessiontype";
        //console.log("targetid="+targetid);
        //$(targetid).select2('val', 1);
        setElementToId( targetid, _accessiontype );
    }
}

//#############  Mrn Type  ##############//
function getComboboxMrnType(holder,force) {

    var url = getCommonBaseUrl("util/"+"mrntype", 'scan');    //urlCommon+"mrntype";

    var targetid = ".mrntype-combobox";
    targetid = getElementTargetByHolder(holder,targetid);

    if( typeof force === 'undefined' ) {
        force = false;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "new" || cycle == "create" ) {
        url = url + "&opt=default&type="+orderformtype;
    }

    if( $(targetid).hasClass("mrntype-exception-autogenerated") ) {
        url = url + "&exception=autogenerated";
    }
    if( $(targetid).hasClass("mrntype-exception-existingautogenerated") ) {
        url = url + "&exception=existingautogenerated";
    }

    if( _mrntype.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
                _mrntype = data;
                //console.log('1 populate mrn type');
                populateSelectCombobox( targetid, _mrntype, null ); //'Please select MRN type'
                setAccessionMask();
            });
    } else {
        //console.log('2 populate mrn type');
        populateSelectCombobox( targetid, _mrntype, null );
    }

    if( cycle == "new"  ) {
        //oleg_orderformbundle_messagetype_patient_0_mrn_0_keytype
//        var uid = 'patient_'+ids[0];
//        var id= "#oleg_orderformbundle_messagetype_"+uid+"_";
//        var targetid = id+"mrn_0_mrntype";
        //console.log("targetid="+targetid);
        setElementToId( targetid, _mrntype );
    }
}

//#############  partname types  ##############//
function getComboboxPartname(holder,force) {

    var url = getCommonBaseUrl("util/"+"partname", 'scan');  //urlCommon+"partname";

    var targetid = ".ajax-combobox-partname";
    targetid = getElementTargetByHolder(holder,targetid);

    if( typeof force === 'undefined' ) {
        force = false;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "&opt="+messageid;
    }

    if( _partname.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
            _partname = data;
            populateSelectCombobox( targetid, _partname, "Part ID" );
            //setOnlyNewComboboxes( targetid, _partname, "Part Name" );
        });
    } else {
        populateSelectCombobox( targetid, _partname, "Part ID" );
        //setOnlyNewComboboxes( targetid, _partname, "Part Name" );
    }

}

//function setOnlyNewComboboxes( targetClass, datas, placeholder ) {
//
//    //don't repopulate already populated comboboxes. This is a case when typed value will be erased
//
//    $(targetClass).each(function() {
//        var optionLength = $(this).children('option').length;
//        //console.log( "optionLength="+optionLength );
//        if( optionLength == 0 ) {
//            //console.log( 'data is not set' );
//            var id = $(this).attr('id');
//            var targetid = '#'+id;
//            populateSelectCombobox( targetid, datas, placeholder );
//        }
//    });
//}

//#############  blockname types  ##############//
function getComboboxBlockname(holder,force) {

    var url = getCommonBaseUrl("util/"+"blockname", 'scan'); //urlCommon+"blockname";

    var targetid = ".ajax-combobox-blockname";
    targetid = getElementTargetByHolder(holder,targetid);

    if( typeof force === 'undefined' ) {
        force = false;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "&opt="+messageid;
    }

    if( _blockname.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
                _blockname = data;
            populateSelectCombobox( targetid, _blockname, "Block ID" );
        });
    } else {
        populateSelectCombobox( targetid, _blockname, "Block ID" );
    }

}

//#############  slide delivery  ##############//
function getComboboxDelivery(holder) {
    //var uid = "";   //'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
//    var id= "#oleg_orderformbundle_messagetype_";
    var url = getCommonBaseUrl("util/"+"delivery");    //urlCommon+"delivery";
    var targetid = ".ajax-combobox-delivery";
    targetid = getElementTargetByHolder(holder,targetid);

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "&opt="+messageid;
    }

    //console.log("scanregion.length="+organ.length);
    if( _delivery.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
                _delivery = data;
            populateSelectCombobox( ".ajax-combobox-delivery", _delivery, null );
            // if( cycle == "new"  ) {
            //     setElementToId( targetid, _delivery );
            // }
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-delivery", _delivery, null );
        // if( cycle == "new"  ) {
        //     setElementToId( targetid, _delivery );
        // }
    }

}



//#############  Research Educational Utils  ##############//

function setOptionalUserResearch(treeHolder,node) {
    var userComboboxClass = ".combobox-optionaluser-research";
    var utilUrlString = "optionaluserresearch";
    var tooltip = "Please enter the Research Project Title to access this field";
    setOptionalUserWrapperByNodeid(treeHolder,node,userComboboxClass,utilUrlString,tooltip);
}

function setOptionalUserEducational(treeHolder,node) {
    var userComboboxClass = ".combobox-optionaluser-educational";
    var utilUrlString = "optionalusereducational";
    var tooltip = "Please enter the Course Title to access this field";
    setOptionalUserWrapperByNodeid(treeHolder,node,userComboboxClass,utilUrlString,tooltip);
}

function setOptionalUserWrapperByNodeid(treeHolder,node,userComboboxClass,utilUrlString,tooltip) {

    //clear user combobox
    $(userComboboxClass).select2('data',null);

    if( node ) {
        //Case 1) nodeid is not null => new node is choosen => clear users and get new users for this node
        //console.log(userComboboxClass+": Case 1) nodeid is not null => new node is choosen => clear users and get new users for this node");
        getUserWrappersByNodeid( node.id, userComboboxClass, utilUrlString );
        //enable user combobox
        $(userComboboxClass).select2('readonly',false);
    } else {
        //Case 2) nodeid is null => node is cleared => clear users and disable user combobox
        //console.log(userComboboxClass+": Case 2) nodeid is null => node is cleared => clear users => check for parent node => get users or disable user combobox");

        //check if parent node is set
        var activeNode = treeHolder.find('.active-tree-node');
        if( activeNode.length > 0 ) {
            var activeNodeData = activeNode.select2('data');
            if( activeNodeData ) {
                //console.log(userComboboxClass+": Case 3) parent nodeid is not null => clear users and get new users for this node");
                getUserWrappersByNodeid( activeNodeData.id, userComboboxClass, utilUrlString );
                $(userComboboxClass).select2('readonly',false);
            } else {
                //console.log(userComboboxClass+": Case 4) parent nodeid is null => node is cleared => clear users => disable user combobox");
                $(userComboboxClass).select2('readonly',true);
            }
        } else {
            $(userComboboxClass).select2('readonly',true);
        }

    }

    checkForTooltipByElement( userComboboxClass, treeHolder.find('.ajax-combobox-compositetree'), tooltip );

}

//get users by node id with ajax
function getUserWrappersByNodeid( nodeid, userComboboxClass, utilUrlString ) {

    if( $(userComboboxClass).length == 0 ) {
        return;
    }

    var url = getCommonBaseUrl("util/"+utilUrlString);

    //var idInArr = getParentSelectId( nodeComboboxClass, _projectTitle, userComboboxClass, true );

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    url = url + "&opt=" + nodeid;
    //console.log('get UserWrappers By Nodeid url='+url);

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: asyncflag
    }).done(function(data) {
        if( data ) {
            populateSelectCombobox( userComboboxClass, data, "Select an option or type in a new value", true );
        }
    });

}
//#############  EOF Research Educational Utils  ##############//


//#############  account  ##############//
function getComboboxAccount(holder) {

    var url = getCommonBaseUrl("util/"+"account");  //urlCommon+"account";

    var targetid = ".ajax-combobox-account";
    targetid = getElementTargetByHolder(holder,targetid);

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "&opt="+messageid;
    }

    if( _account.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
            _account = data;
            populateSelectCombobox( targetid, _account, "Select an option or type in a new value" );
        });
    } else {
        populateSelectCombobox( targetid, _account, "Select an option or type in a new value" );
    }

}

//#############  return locations to  ##############//
function getComboboxReturnLocations(holder) {

    var targetid = ".ajax-combobox-location";
    targetid = getElementTargetByHolder(holder,targetid);

    if( $(targetid).length == 0 ) {
        return;
    }

    var url = getCommonBaseUrl("util/"+"returnlocation"+"?providerid="+user_id+"&proxyid="+proxyuser_id);

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "&cycle=" + cycle;

    if( _returnLocation.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
            _returnLocation = data;
            populateSelectCombobox( targetid, _returnLocation, "Select an option or type in a new value", false );
        });
    } else {
        populateSelectCombobox( targetid, _returnLocation, "Select an option or type in a new value", false );
    }

}

function getUrgency() {

    var targetid = ".ajax-combobox-urgency";
    if( $(targetid).length == 0 ) {
        return;
    }

    var url = getCommonBaseUrl("util/"+"urgency");  //urlCommon+"urgency";

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "&opt="+messageid;
    }

    //console.log("scanregion.length="+organ.length);
    if( _urgency.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
            _urgency = data;
            populateSelectCombobox( targetid, _urgency, null );
            if( cycle == "new"  ) {
                setElementToId( targetid, _urgency );
            }
        });
    } else {
        populateSelectCombobox( targetid, _urgency, null );
        if( cycle == "new"  ) {
            setElementToId( targetid, _urgency );
        }
    }

}


//flag - optional parameter to force use ids if set to true
function initComboboxJs(ids, holder) {

    if( urlBase ) {

        cycle = 'new';

        getComboboxMrnType(holder);
        getComboboxAccessionType(holder);
        getComboboxPartname(holder);
        getComboboxBlockname(holder);
        getComboboxProcedure(holder);
        getComboboxOrgan(holder);

        //slide
        getComboboxStain(holder);
        getComboboxScanregion(holder);

        slideType(ids);

        //exception field because it can be added dynamically, so we use ids
        getComboboxSpecialStain(ids,true);

        //order
        //getProjectTitle(holder);
        //getCourseTitle(holder);
        getComboboxAccount(holder);
        getComboboxReturnLocations(holder);
    }
}


function slideType(ids) {    
    
    //oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_part_0_block_1_slide_0_slidetype
    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_messagetype_"+uid+"_slidetype";

    $(id).change(function(e) {   //.slidetype-combobox
        //console.log("slidetype-combobox changed: this id="+$(this).attr('id')+",class="+$(this).attr('class'));
        //e.preventDefault();
        var parent = $(this).parent().parent().parent().parent().parent().parent().parent().parent();
        //console.log("parent: id="+parent.attr('id')+",class="+parent.attr('class'));
        var blockValue = parent.find('.element-title').first();
        //console.log("slidetype-combobox: id="+parent.find('.slidetype-combobox').first().attr('id')+",class="+parent.find('.slidetype-combobox').first().attr('class'));
        var slideTypeText = parent.find('.slidetype-combobox').first().select2('data').text;
        //console.log("slideTypeText="+slideTypeText);
        //console.log("blockValue: id="+blockValue.attr('id')+",class="+blockValue.attr('class')+",slideTypeText="+slideTypeText);
        var keyfield = parent.find('#check_btn');
        if( slideTypeText == 'Cytopathology' ) {
            //console.log("Cytopathology is chosen = "+slideTypeText);
            keyfield.attr('disabled','disabled'); 
            disableInElementBlock(parent.find('#check_btn').first(), true, "all", null, null);
            var htmlDiv = '<div class="element-skipped">Block is not used for cytopathology slide</div>';
            parent.find('.element-skipped').first().remove();
            blockValue.after(htmlDiv);
            blockValue.hide();
            parent.find('.form-btn-options').first().hide();
        } else {
            if( $('.element-skipped').length != 0 ) {
                //disableInElementBlock(parent.find('#check_btn').first(), false, "all", null, null);
                var btnEl = parent.find('#check_btn').first();
                if( btnEl.hasClass('checkbtn') ) {
                    disableInElementBlock(parent.find('#check_btn').first(), true, null, "notkey", null);
                } else {
                    disableInElementBlock(parent.find('#check_btn').first(), false, null, "notkey", null);
                }
                parent.find('.element-skipped').first().remove();
                blockValue.show();
                keyfield.removeAttr('disabled');
                parent.find('.form-btn-options').first().show();
            }
        }
        
    });   
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
}



