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

var _mrntype = new Array();
var _accessiontype = new Array();
var _partname = new Array();
var _blockname = new Array();
var _stain = new Array();
var _scanregion = new Array();
var _procedure = new Array();
var _organ = new Array();
var _delivery = new Array();
var _returnLocation = new Array();
var _projectTitle = new Array();
var _courseTitle = new Array();
var _account = new Array();
var _urgency = new Array();
var _proxyuser = new Array();

//generic select2 fields
var _labtesttype = new Array();



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
    populateSelectCombobox( ".combobox-research-setTitle", null, "Select an option or type in a new value", false );
    $(".combobox-research-setTitle").select2("readonly", true);
    populateSelectCombobox( ".combobox-optionaluser-research", null, "Select an option or type in a new value", false );
    $(".combobox-optionaluser-research").select2("readonly", true);

    //educational
    //multiple is set to false to make the width of the field to fit the form; otherwise, the data is not set and the width is too small to fit placeholder
    populateSelectCombobox( ".combobox-educational-lessonTitle", null, "Select an option or type in a new value", false );
    $(".combobox-educational-lessonTitle").select2("readonly", true);
    //multiple is set to false to make the width of the field to fit the form; otherwise, the data is not set and the width is too small to fit placeholder
    populateSelectCombobox( ".combobox-optionaluser-educational", null, 'Select an option or type in a new value', false );
    $(".combobox-optionaluser-educational").select2("readonly", true);

}

function customCombobox() {

    //console.log("cycle="+cycle);

    if( cycle && urlBase && cycle != 'edit_user' && cycle != 'accountreq' ) {
        getComboboxMrnType(new Array("0","0","0","0","0","0"));
        getComboboxAccessionType(new Array("0","0","0","0","0","0"));
        getComboboxPartname(new Array("0","0","0","0","0","0"));
        getComboboxBlockname(new Array("0","0","0","0","0","0"));
        getComboboxScanregion(new Array("0","0","0","0","0","0"));
        getComboboxStain(new Array("0","0","0","0","0","0"));
        getComboboxSpecialStain(new Array("0","0","0","0","0","0"),true);
        getComboboxProcedure(new Array("0","0","0","0","0","0"));
        getComboboxOrgan(new Array("0","0","0","0","0","0"));
        getComboboxDelivery(new Array("0","0","0","0","0","0"));
        //getComboboxReturn(new Array("0","0","0","0","0","0"));
        slideType(new Array("0","0","0","0","0","0"));
        getProjectTitle(new Array("0","0","0","0","0","0"));
        getCourseTitle(new Array("0","0","0","0","0","0"));

        getComboboxAccount(new Array("0","0","0","0","0","0"));
        getComboboxReturnLocations();

        getComboboxGeneric(null,'proxyuser',_proxyuser,true,'','scan');
    }

    //scan
    getComboboxGeneric(null,'labtesttype',_labtesttype,false);

}

function initDefaultServiceManually() {

    var targetid = ".ajax-combobox-service";

    if( $(targetid).length == 0 ) {
        return;
    }

    var url = getCommonBaseUrl("util/"+"default-service");

    var instid = $('.combobox-institution').select2('val');

    //use curid to add current object.
    var curid = null;
    var curValue = $(targetid).select2('val');
    //console.log("curValue="+curValue);
    if( isInt(curValue) ) {
        curid = curValue;
    }

    $.ajax({
        url: url,
        type: 'GET',
        //data: {id: curid, instid: instid, orderid: messageid},
        data: {id: curid, instid: instid},
        timeout: _ajaxTimeout,
        async: asyncflag
    }).success(function(data) {
        populateSelectCombobox( targetid, data, "Select an option or type in a new value" );
    });
}


//#############  stains  ##############//
function getComboboxStain(ids,holder) {

    var url = getCommonBaseUrl("util/"+"stain");

    if( cycle == "new" || cycle == "create" ) {
        url = url + "?opt=default";
    }

    var targetid = ".ajax-combobox-stain";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    //console.log("_stain.length="+_stain.length);
    if( _stain.length == 0 ) {
        //console.log("stain 0");
        $.ajax({
            url: url,
            async: asyncflag,
            timeout: _ajaxTimeout
        }).success(function(data) {
                _stain = data;
            populateSelectCombobox( targetid, _stain, null );
            //populateSelectCombobox( ".ajax-combobox-staintype", _stain, null );
        });
    } else {
        //console.log("stain exists");
        populateSelectCombobox( targetid, _stain, null );
        //populateSelectCombobox( ".ajax-combobox-staintype", _stain, null );
    }

    if( cycle == "new"  ) {
//        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
//        var id= "#oleg_orderformbundle_messagetype_"+uid+"_";
//        var targetid = id+"stain_0_field";
        setElementToId( targetid, _stain );
    }

}

function getComboboxSpecialStain(ids, preset, setId) {

    var url = getCommonBaseUrl("util/"+"stain");    //urlCommon+"stain";

    if( cycle == "new" || cycle == "create" ) {
        url = url + "?opt=default";
    }

    var targetid = "";
    if( cycle == "new" || (cycle == "amend" && preset) || (cycle == "edit" && preset) ) {
        //oleg_orderformbundle_messagetype_patient_0_encounter_0_procedure_0_accession_0_part_0_block_0_specialStains_0_staintype
        var uid = 'patient_'+ids[0]+'_encounter_'+ids[1]+'_procedure_'+ids[2]+'_accession_'+ids[3]+'_part_'+ids[4]+'_block_'+ids[5];
        var id= "#oleg_orderformbundle_messagetype_"+uid+"_";
        targetid = id+"specialStains_"+ids[6]+"_staintype";
        //console.log("targetid="+targetid);
    }

    if( _stain.length == 0 ) {
        //console.log("_stain.length is zero");
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
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
function getComboboxScanregion(ids,holder) {

    var url = getCommonBaseUrl("util/"+"scanregion"); //urlCommon+"scanregion";
    //console.log("scanregion.length="+scanregion.length);

    var targetid = ".ajax-combobox-scanregion";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "?opt="+messageid;
    }

    if( _scanregion.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
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
function getComboboxOrgan(ids,holder) {
    var url = getCommonBaseUrl("util/"+"organ");   //urlCommon+"organ";

    var targetid = ".ajax-combobox-organ";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    if( cycle == "new" || cycle == "create" ) {
        url = url + "?opt=default";
    }

    if( _organ.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _organ = data;
            populateSelectCombobox( targetid, _organ, "Source Organ" );
        });
    } else {
        populateSelectCombobox( targetid, _organ, "Source Organ" );
    }

}


//#############  procedure types  ##############//
function getComboboxProcedure(ids,holder) {
    var url = getCommonBaseUrl("util/"+"procedure"); //urlCommon+"procedure";

    var targetid = ".ajax-combobox-procedure";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    if( cycle == "new" || cycle == "create" ) {
        url = url + "?opt=default";
    }

    if( _procedure.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _procedure = data;
            populateSelectCombobox( targetid, _procedure, "Procedure Type" );
        });
    } else {
        populateSelectCombobox( targetid, _procedure, "Procedure Type" );
    }

}

//#############  Accession Type  ##############//
function getComboboxAccessionType(ids,holder) {

    var url = getCommonBaseUrl("util/"+"accessiontype");    //urlCommon+"accessiontype";

    var targetid = ".accessiontype-combobox";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    if( cycle == "new" || cycle == "create" ) {
        url = url + "?opt=default&type="+orderformtype;
    }

    if( _accessiontype.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
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
function getComboboxMrnType(ids,holder) {

    var url = getCommonBaseUrl("util/"+"mrntype");    //urlCommon+"mrntype";

    var targetid = ".mrntype-combobox";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    if( cycle == "new" || cycle == "create" ) {
        url = url + "?opt=default&type="+orderformtype;
    }

    if( _mrntype.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _mrntype = data;
                populateSelectCombobox( targetid, _mrntype, null );
                setAccessionMask();
            });
    } else {
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
function getComboboxPartname(ids,holder) {

    var url = getCommonBaseUrl("util/"+"partname");  //urlCommon+"partname";

    var targetid = ".ajax-combobox-partname";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        //oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_part_0_partname_0_field
//        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3];
//        var id= "#oleg_orderformbundle_messagetype_"+uid+"_";
//        var targetid = id+"partname_0_field";
        targetid = holder.find(targetid);
    }
    //console.log("part targetid="+targetid);
    //console.log("cycle="+cycle);

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "?opt="+messageid;
    }

    if( _partname.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _partname = data;
            populateSelectCombobox( targetid, _partname, "Part Name" );
            //setOnlyNewComboboxes( targetid, _partname, "Part Name" );
        });
    } else {
        populateSelectCombobox( targetid, _partname, "Part Name" );
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
function getComboboxBlockname(ids,holder) {

    var url = getCommonBaseUrl("util/"+"blockname"); //urlCommon+"blockname";

    var targetid = ".ajax-combobox-blockname";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
//        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4];
//        var id= "#oleg_orderformbundle_messagetype_"+uid+"_";
//        var targetid = id+"blockname_0_field";
        targetid = holder.find(targetid);
    }
    //console.log("block targetid="+targetid);

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "?opt="+messageid;
    }

    if( _blockname.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _blockname = data;
            populateSelectCombobox( targetid, _blockname, "Block Name" );
        });
    } else {
        populateSelectCombobox( targetid, _blockname, "Block Name" );
    }

}

//#############  slide delivery  ##############//
function getComboboxDelivery(ids) {
    //var uid = "";   //'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
//    var id= "#oleg_orderformbundle_messagetype_";
    var url = getCommonBaseUrl("util/"+"delivery");    //urlCommon+"delivery";
    var target = ".ajax-combobox-delivery";

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "?opt="+messageid;
    }

    //console.log("scanregion.length="+organ.length);
    if( _delivery.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _delivery = data;
            populateSelectCombobox( ".ajax-combobox-delivery", _delivery, null );
            if( cycle == "new"  ) {
                setElementToId( target, _delivery );
            }
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-delivery", _delivery, null );
        if( cycle == "new"  ) {
            setElementToId( target, _delivery );
        }
    }

}


//#############  Research Project  ##############//
function getProjectTitle(ids,holder) {

    var url = getCommonBaseUrl("util/"+"projecttitle");  //urlCommon+"projecttitle";

    var targetid = ".combobox-research-projectTitle";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "?opt="+messageid;
    }

    if( _projectTitle.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _projectTitle = data;
                populateSelectCombobox( targetid, _projectTitle, "Research Project Title", false );

                //get id if set
                var projectTitleVal = $(targetid).select2('val');
                //console.log("finished: projectTitleVal="+projectTitleVal);
                if( projectTitleVal != "" ) {
                    getSetTitle();
                    getOptionalUserResearch();
                }

            });
    } else {
        populateSelectCombobox( targetid, _projectTitle, "Research Project Title", false );
    }

}

//#############  set title  ##############//
function getSetTitle() {

    var targetid = ".combobox-research-setTitle";
    var url = getCommonBaseUrl("util/"+"settitle"); //urlCommon+"settitle";

    //get ProjectTitle value and process children fields (readonly: true or false)
    var idInArr = getParentSelectId( ".combobox-research-projectTitle", _projectTitle, targetid, false );
    if( idInArr < 0 ) {
        return;
    }

    url = url + "?opt="+idInArr;

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "&orderoid="+messageid;
    }

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: asyncflag
    }).success(function(data) {
        if( data ) {
            //console.log("populate title: id="+data[0].id+", text="+data[0].text);
            populateSelectCombobox( targetid, data, "Select an option or type in a new value");
            //$(targetid).select2("readonly", false);
            //setElementToId( targetid, data );
        }
    });

}
//#############  EOF Research Project  ##############//


//#############  Educational Course  ##############//
function getCourseTitle(ids,holder) {

    var url = getCommonBaseUrl("util/"+"coursetitle"); //urlCommon+"coursetitle";

    var targetid = ".combobox-educational-courseTitle";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "?opt="+messageid;
    }

    if( _courseTitle.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _courseTitle = data;
                populateSelectCombobox( targetid, _courseTitle, "Course Title", false );

                //get id if set
                var courseTitleVal = $(targetid).select2('val');
                if( courseTitleVal != "" ) {
                    getLessonTitle();
                    getOptionalUserEducational();
                }

            });
    } else {
        populateSelectCombobox( targetid, _courseTitle, "Course Title", false );
    }

}

//#############  lesson title  ##############//
function getLessonTitle() {

    var targetid = ".combobox-educational-lessonTitle";
    var url = getCommonBaseUrl("util/"+"lessontitle");  //urlCommon+"lessontitle";

    //get CourseTitle value and process children fields (readonly: true or false)
    var idInArr = getParentSelectId( ".combobox-educational-courseTitle", _courseTitle, targetid, false );
    if( idInArr < 0 ) {
        return;
    }

    url = url + "?opt="+idInArr;

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "&orderoid="+messageid;
    }

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: asyncflag
    }).success(function(data) {
            if( data ) {
                populateSelectCombobox( targetid, data, "Select an option or type in a new value");
            }
    });

}
//#############  EOF Educational Course  ##############//

//#############  Research Educational Utils  ##############//
function getOptionalUserResearch() {

    var targetid = ".combobox-optionaluser-research";
    var url = getCommonBaseUrl("util/"+"optionaluserresearch"); //urlCommon+"optionaluserresearch";

    var idInArr = getParentSelectId( ".combobox-research-projectTitle", _projectTitle, targetid, true );

    url = url + "?opt=" + idInArr;

//    if( idInArr < 0 ) {
//
//        //new project entered: get default users
//        $.ajax({
//            url: url,
//            timeout: _ajaxTimeout,
//            async: asyncflag
//        }).success(function(data) {
//                if( data ) {
//                    populateSelectCombobox( targetid, data, "Select an option or type in a new value", true );
//                }
//            });
//
//        return;
//    }

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: asyncflag
    }).success(function(data) {
            if( data ) {
                populateSelectCombobox( targetid, data, "Select an option or type in a new value", true );
            }
    });


}

function getOptionalUserEducational() {

    var targetid = ".combobox-optionaluser-educational";
    var url = getCommonBaseUrl("util/"+"optionalusereducational"); //urlCommon+"optionalusereducational";

    var idInArr = getParentSelectId( ".combobox-educational-courseTitle", _courseTitle, targetid, true );

    url = url + "?opt=" + idInArr;

//    if( idInArr < 0 ) {
//
//        //new course entered: get default users
//        $.ajax({
//            url: url,
//            timeout: _ajaxTimeout,
//            async: asyncflag
//        }).success(function(data) {
//                if( data ) {
//                    populateSelectCombobox( targetid, data, "Select an option or type in a new value", true );
//                }
//            });
//
//        return;
//    }

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: asyncflag
    }).success(function(data) {
            if( data ) {
                populateSelectCombobox( targetid, data, "Select an option or type in a new value", true );
            }
    });

}

function getParentSelectId( ptarget, pArr, target, multiple ) {
    //get ProjectTitle value
    var parentVal = $(ptarget).select2('val');
    //console.log("parentVal="+parentVal);
    //console.log(_projectTitle);
    //var projectTitleData = $(".combobox-research-projectTitle").select2('data');
    //console.log("id="+projectTitleData.id+", text="+projectTitleData.text);

    if( parentVal == '' ) {
        //console.log('not in array');
        populateSelectCombobox( target, null, "Select an option or type in a new value", multiple );
        $(target).select2("readonly", true);
        return -1;
    }

    var idInArr = inArrayCheck( pArr, parentVal );
    //console.log('idInArr='+idInArr);

    if( idInArr == -1 ) {
        //console.log('not in array');
        populateSelectCombobox( target, null, "Select an option or type in a new value", multiple );
        $(target).select2("readonly", false);
        return -1;
    }

    $(target).select2("readonly", false);

    return idInArr;
}
//#############  EOF Research Educational Utils  ##############//


//#############  account  ##############//
function getComboboxAccount(ids,holder) {

    var url = getCommonBaseUrl("util/"+"account");  //urlCommon+"account";

    var targetid = ".ajax-combobox-account";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "?opt="+messageid;
    }

    if( _account.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
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

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);

        if( targetid.length == 0 )
            return;
    }

    var url = getCommonBaseUrl("util/"+"returnlocation"+"?providerid="+user_id+"&proxyid="+proxyuser_id);

    if( _returnLocation.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _returnLocation = data;
            populateSelectCombobox( targetid, _returnLocation, "Select an option or type in a new value", false );
        });
    } else {
        populateSelectCombobox( targetid, _returnLocation, "Select an option or type in a new value", false );
    }

}

function getUrgency() {

    var url = getCommonBaseUrl("util/"+"urgency");  //urlCommon+"urgency";

    if( cycle == "edit" || cycle == "show" || cycle == "amend" ) {
        url = url + "?opt="+messageid;
    }

    //console.log("scanregion.length="+organ.length);
    if( _urgency.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _urgency = data;
            populateSelectCombobox( ".ajax-combobox-urgency", _urgency, null );
            if( cycle == "new"  ) {
                setElementToId( ".ajax-combobox-urgency", _urgency );
            }
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-urgency", _urgency, null );
        if( cycle == "new"  ) {
            setElementToId( ".ajax-combobox-urgency", _urgency );
        }
    }

}


//flag - optional parameter to force use ids if set to true
function initComboboxJs(ids, holder) {

    if( urlBase ) {

        cycle = 'new';

        getComboboxMrnType(ids,holder);
        getComboboxAccessionType(ids,holder);
        getComboboxPartname(ids,holder);
        getComboboxBlockname(ids,holder);
        getComboboxProcedure(ids,holder);
        getComboboxOrgan(ids,holder);

        //slide
        getComboboxStain(ids,holder);
        getComboboxScanregion(ids,holder);

        slideType(ids);

        //exception field because it can be added dynamically, so we use ids
        getComboboxSpecialStain(ids,true);

        //order
        getProjectTitle(ids,holder);
        getCourseTitle(ids,holder);
        getComboboxAccount(ids,holder);
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
