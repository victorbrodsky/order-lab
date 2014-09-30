/**
 * Created by oli2002 on 9/17/14.
 */


function getComboboxTreeByPid( parentElement, fieldClass, parentId ) {

    //console.log( "onchange=" + fieldClass );

    var holder = parentElement.closest('.user-collection-holder');

    var targetId = '#' + holder.find("."+fieldClass).not("*[id^='s2id_']").attr('id');
    //console.log( "targetId="+targetId );

    if( typeof parentId === "undefined" ) {
        parentId = parentElement.select2('val');
    }
    //console.log( "parentId="+parentId );

    if( parentId ) {

        var fieldName = fieldClass.replace("ajax-combobox-", "");
        //console.log( "fieldName="+fieldName+", parentid="+parentId );
        var url = getCommonBaseUrl("util/common/"+fieldName,"employees"); //always use "employees" to get children
        url = url + "?pid="+parentId;
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            populateSelectCombobox( targetId, data, "Select an option or type in a new value" );
            $(targetId).select2("readonly", false);
            loadChildren($(targetId),holder,fieldClass);
        });

    } else {
        //console.log( "clear combobox, targetId="+targetId);

        //clear combobox
        populateSelectCombobox( targetId, null, "Select an option or type in a new value" );
        $(targetId).select2("readonly", true);

        clearChildren(holder,fieldClass);
    }
}

function populateParentChildTree(target, data, placeholder, multipleFlag, childClass) {

    var targetElements = $(target);

    targetElements.each( function() {

        var selectId = '#'+$(this).attr('id');

        populateSelectCombobox( selectId, data, placeholder, multipleFlag );

        //children
        getComboboxTreeByPid($(this),childClass);

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
        //console.log( "load Children="+childrenTargetClass );
        getComboboxTreeByPid(parentElement,childrenTargetClass);
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

    }

}







////////////////// mixed functions ////////////////////
function initTreeSelect() {

    $('.ajax-combobox-institution').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-department');
    });

    $('.ajax-combobox-department').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-division');
    });

    $('.ajax-combobox-division').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-service');
    });

    //comments type and subtypes
    $('.ajax-combobox-commenttype').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-commentsubtype');
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





///////////////// Comments Types Tree ///////////////////
function setCommentTypeTreeChildren(holder) {

    if( typeof holder == 'undefined' ) {
        holder = $('body');
    }

    //subTypes
    populateSelectCombobox( holder.find(".ajax-combobox-commentsubtype"), null, "Select an option or type in a new value", false );
}
///////////////// EOF Comments Types ///////////////////



