/**
 * Created by oli2002 on 9/17/14.
 */


function getComboboxTreeByPid( parentElement, fieldClass, parentId, clearFlag ) {

    console.log( "onchange=" + fieldClass );

    var holder = parentElement.closest('.user-collection-holder');
    if( typeof holder === "undefined" || holder.length == 0 ) {
        console.log( "holder is not found! class="+fieldClass );
        return;
    }
    //console.log( holder );

    var targetEl = holder.find("."+fieldClass).not("*[id^='s2id_']");
    if( typeof targetEl === "undefined" || targetEl.length == 0 ) {
        console.log( "target is not found!" );
        return;
    }

    var targetId = '#' + targetEl.attr('id');
    //console.log( "targetId="+targetId );

    if( typeof parentId === "undefined" || parentId == null ) {
        parentId = parentElement.select2('val');
    }
    console.log( "parentId="+parentId );

    if( typeof clearFlag === "undefined" ) {
        clearFlag = true;
    }

    if( clearFlag ) {
        //clear combobox
        //console.log( "clear combobox, targetId="+targetId);
        populateSelectCombobox( targetId, null, "Select an option or type in a new value" );
        $(targetId).select2("readonly", true);
        clearChildren(holder,fieldClass);
    }

    if( parentId ) {

        var fieldName = fieldClass.replace("ajax-combobox-", "");
        //console.log( "fieldName="+fieldName+", parentid="+parentId );
        var url = getCommonBaseUrl("util/common/"+fieldName,"employees"); //always use "employees" to get children

        //url = url + "?pid="+parentId;

        var curid = null;
        //use curid to add current object. However, it causes the problems by showing not correct children list
        //var curid = targetEl.select2('val');
        //console.log("curid="+curid);
        //if( isInt(curid) ) {
        //    url = url + "&id="+curid;
        //}

        $.ajax({
            url: url,
            type: 'POST',
            data: {id: curid, pid: parentId},
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            populateSelectCombobox( targetId, data, "Select an option or type in a new value" );
            $(targetId).select2("readonly", false);
            loadChildren($(targetId),holder,fieldClass);
        });

    }
//    else {
//
//        if( clearFlag ) {
//            //console.log( "clear combobox, targetId="+targetId);
//
//            //clear combobox
//            populateSelectCombobox( targetId, null, "Select an option or type in a new value" );
//            $(targetId).select2("readonly", true);
//
//            clearChildren(holder,fieldClass);
//        }
//    }

}

function populateParentChildTree(target, data, placeholder, multipleFlag, childClass) {

    var targetElements = $(target);

    targetElements.each( function() {

        var selectId = '#'+$(this).attr('id');

        populateSelectCombobox( selectId, data, placeholder, multipleFlag );

        //children
        //console.log('################################# populate Parent Child Tree childClass='+childClass);
        getComboboxTreeByPid($(this),childClass,null,true);

    });

}

//If default value will be set "Weill Cornell Medical College", then saving the user data will save default value but user might not be aware of that.
function setDeafultData(target,data,text) {
    //set default to "Weill Cornell Medical College"
    var value = $(target).select2('val');
    //console.log('value='+value);
    if( !value ) {
        var setId = getDataIdByText(data,text);
        setElementToId( target, data, setId );
    }
}


function loadChildren(parentElement,holder,fieldClass) {

    var childrenTargetClass = getChildrenTargetClass(fieldClass);

    var parentId = parentElement.select2('val');

    if( childrenTargetClass && parentId ) {
        //console.log( "################################# load Children="+childrenTargetClass );
        getComboboxTreeByPid(parentElement,childrenTargetClass,null,true);
    }

}

function clearChildren(holder,fieldClass) {
    //var holder = parentElement.closest('.user-collection-holder');
    var childrenTargetClass = getChildrenTargetClass(fieldClass);

    if( childrenTargetClass ) {

        //console.log( "clear Children="+childrenTargetClass );
        var childrenTargetId = '#' + holder.find("."+childrenTargetClass).not("*[id^='s2id_']").attr('id');

        populateSelectCombobox( childrenTargetId, null, "Select an option or type in a new value" );
        $(childrenTargetId).select2("readonly", true);

        clearChildren(holder,childrenTargetClass);

    } else {
        //console.log( "don't clear="+fieldClass );
    }

}

//This function executes twice (?)
var _initInstitutionManuallyCount = 0;
function initInstitutionManually() {
    if( _initInstitutionManuallyCount > 0 ) {
        return;
    }
    _initInstitutionManuallyCount = 1;

    $('.ajax-combobox-institution-preset').each(function(e){
        //console.log( "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! init inst manually" );
        var clearFlag = true; //don't clear children and default service
        getComboboxTreeByPid($(this),'ajax-combobox-department',null,clearFlag);
    });
}



////////////////// mixed functions ////////////////////
function initTreeSelect(clearFlag) {

    console.log( "init Tree Select" );

    if( typeof clearFlag === "undefined" ) {
        clearFlag == true;
    }

    $('.ajax-combobox-institution,.ajax-combobox-institution-preset').on('change', function(e){
        //console.log( "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! institution on change" );
        getComboboxTreeByPid($(this),'ajax-combobox-department',null,clearFlag);
    });

    $('.ajax-combobox-department').on('change', function(e){
        //console.log( "department on change!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!" );
        getComboboxTreeByPid($(this),'ajax-combobox-division',null,clearFlag);
    });

    $('.ajax-combobox-division').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-service',null,clearFlag);
    });

    //comments type and subtypes
    $('.ajax-combobox-commenttype').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-commentsubtype',null,clearFlag);
    });
}

function getChildrenTargetClass(fieldClass) {
    var childrenTargetClass = null;

    if( fieldClass == "ajax-combobox-institution" ) {
        childrenTargetClass = "ajax-combobox-department";
    }
    if( fieldClass == "ajax-combobox-department" ) {
        childrenTargetClass = "ajax-combobox-division";
    }
    if( fieldClass == "ajax-combobox-division" ) {
        childrenTargetClass = "ajax-combobox-service";
    }

    //comments type and subtypes
    if( fieldClass == "ajax-combobox-commenttype" ) {
        childrenTargetClass = "ajax-combobox-commentsubtype";
    }

    return childrenTargetClass;
}
////////////////// EOF mixed functions ////////////////////




///////////////// Institution Tree ///////////////////
function setInstitutionTreeChildren(holder) {

    //console.log( "set Institution Tree Children" );

    if( typeof holder == 'undefined' ) {
        holder = $('body');
    }

    //department
    populateSelectCombobox( holder.find(".ajax-combobox-department"), null, "Select an option or type in a new value", false );

    //division
    populateSelectCombobox( holder.find(".ajax-combobox-division"), null, "Select an option or type in a new value", false );

    //service
    populateSelectCombobox( holder.find(".ajax-combobox-service"), null, "Select an option or type in a new value", false );

}
///////////////// EOF Institution Tree ///////////////////





///////////////// Comments Types Tree - initialize the children to null ///////////////////
function setCommentTypeTreeChildren(holder) {

    if( typeof holder == 'undefined' ) {
        holder = $('body');
    }

    var targetId = holder.find(".ajax-combobox-commentsubtype");

    //subTypes
    populateSelectCombobox( targetId, null, "Select an option or type in a new value", false );
}
///////////////// EOF Comments Types ///////////////////



