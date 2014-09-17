/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

var asyncflag = true;
var combobox_width = '100%'; //'element'
var urlBase = $("#baseurl").val();
var cicle = $("#formcicle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();
var orderinfoid = $(".orderinfo-id").val();

var _projectTitle = new Array();
var _courseTitle = new Array();

//var _department = new Array();
var _institution = new Array();

//var userpathserviceflag = false;


function regularCombobox() {
    //select.combobox
    $("select.combobox").select2({
        width: combobox_width,
        dropdownAutoWidth: true,
        placeholder: "Select an option",
        allowClear: true,
        selectOnBlur: false
        //readonly: true
        //containerCssClass: 'combobox-width'
    });
}

function populateSelectCombobox( target, data, placeholder, multipleFlag ) {

    //console.log("target="+target);

    //clear the value
    $(target).select2('val','');

    if( placeholder ) {
        var allowClear = true;
    } else {
        var allowClear = false;
    }

    if( multipleFlag ) {
        var multiple = true;
    } else {
        var multiple = false;
    }

    if( !data ) {
        data = new Array();
    }

    $(target).select2({
        placeholder: placeholder,
        allowClear: allowClear,
        width: combobox_width,
        dropdownAutoWidth: true,
        selectOnBlur: false,
        dataType: 'json',
        quietMillis: 100,
        multiple: multiple,
        data: data,
        createSearchChoice:function(term, data) {
            //if( term.match(/^[0-9]+$/) != null ) {
            //    //console.log("term is digit");
            //}
            return {id:term, text:term};
        }
    });
}



//#############  Research Project  ##############//
function getProjectTitle(ids) {

    var url = getCommonBaseUrl("util/"+"projecttitle");  //urlCommon+"projecttitle";

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( _projectTitle.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _projectTitle = data;
                populateSelectCombobox( ".combobox-research-projectTitle", _projectTitle, "Research Project Title", false );

                //get id if set
                var projectTitleVal = $(".combobox-research-projectTitle").select2('val');
                //console.log("finished: projectTitleVal="+projectTitleVal);
                if( projectTitleVal != "" ) {
                    getSetTitle();
                    getOptionalUserResearch();
                }

            });
    } else {
        populateSelectCombobox( ".combobox-research-projectTitle", _projectTitle, "Research Project Title", false );
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

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "&orderoid="+orderinfoid;
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
function getCourseTitle(ids) {

    var url = getCommonBaseUrl("util/"+"coursetitle"); //urlCommon+"coursetitle";

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( _courseTitle.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _courseTitle = data;
                populateSelectCombobox( ".combobox-educational-courseTitle", _courseTitle, "Course Title", false );

                //get id if set
                var courseTitleVal = $(".combobox-educational-courseTitle").select2('val');
                if( courseTitleVal != "" ) {
                    getLessonTitle();
                    getOptionalUserEducational();
                }

            });
    } else {
        populateSelectCombobox( ".combobox-educational-courseTitle", _courseTitle, "Course Title", false );
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

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "&orderoid="+orderinfoid;
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


////#############  department  ##############//
//function getComboboxDepartment(ids) {
//
//    var url = getCommonBaseUrl("util/"+"department");  //urlCommon+"department";
//
//    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
//        url = url + "?opt="+orderinfoid;
//    }
//
//    if( _department.length == 0 ) {
//        $.ajax({
//            url: url,
//            timeout: _ajaxTimeout,
//            async: asyncflag
//        }).success(function(data) {
//            _department = data;
//            populateSelectCombobox( ".ajax-combobox-department", _department, "Select an option or type in a new value" );
//            if( cicle == "new"  ) {
//                setElementToId( ".ajax-combobox-department", _department );
//            }
//        });
//    } else {
//        populateSelectCombobox( ".ajax-combobox-department", _department, "Select an option or type in a new value" );
//        if( cicle == "new"  ) {
//            setElementToId( ".ajax-combobox-department", _department );
//        }
//    }
//
//}

//#############  institution  ##############//
function getComboboxInstitution(holder) {

    var targetid = ".ajax-combobox-institution";

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
    }

    var url = getCommonBaseUrl("util/"+"institution");

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( _institution.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _institution = data;
            populateSelectCombobox( targetid, _institution, "Select an option or type in a new value" );
            if( cicle == "new"  ) {
                setElementToId( targetid, _institution );
            }
        });
    } else {
        populateSelectCombobox( targetid, _institution, "Select an option or type in a new value" );
        if( cicle == "new"  ) {
            setElementToId( targetid, _institution );
        }
    }

}


function setResearchEducational() {
    //preselect with current user
//    if( proxyuser_id ) {
//        $("#s2id_oleg_orderformbundle_orderinfotype_proxyuser").select2('data', {id: proxyuser_id, text: proxyuser_name});
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
