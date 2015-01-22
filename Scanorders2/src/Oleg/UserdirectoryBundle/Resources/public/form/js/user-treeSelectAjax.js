/**
 * Created by oli2002 on 9/17/14.
 */


function getComboboxTreeByPid( parentElement, fieldClass, parentId, clearFlag ) {

    //console.log( "onchange=" + fieldClass );

    var holder = parentElement.closest('.user-collection-holder');
    if( typeof holder === "undefined" || holder.length == 0 ) {
        //console.log( "holder is not found! class="+fieldClass );
        return;
    }
    //console.log( holder );

    var targetEl = holder.find("."+fieldClass).not("*[id^='s2id_']");
    if( typeof targetEl === "undefined" || targetEl.length == 0 ) {
        //console.log( "target is not found!" );
        return;
    }

    var targetId = '#' + targetEl.attr('id');
    //console.log( "targetId="+targetId );

    if( typeof parentId === "undefined" || parentId == null ) {
        parentId = parentElement.select2('val');
    }
    //console.log( "parentId="+parentId );

    if( typeof clearFlag === "undefined" ) {
        clearFlag = true;
    }

//    if( clearFlag ) {
//        //clear combobox
//        //oleg_userdirectorybundle_user_administrativeTitles_0_service
//        //oleg_userdirectorybundle_user_administrativeTitles_0_service
//        console.log( "clear combobox, targetId="+targetId);
//        populateSelectCombobox( targetId, null, "Select an option or type in a new value" );
//        setElementToId( targetId );
//        $(targetId).select2("readonly", true);
//        clearChildren(holder,fieldClass);
//    }

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
            //type: 'POST',
            data: {id: curid, pid: parentId},
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            //console.log('success: data:');
            //console.log(data);

            if( !data || data.length == 0 ) {
                //console.log('data is null');
                clearTreeToDown(targetId,holder,fieldClass,parentId);
                $(targetId).select2("readonly", false);
            } else {
                //console.log('data ok');
                populateSelectCombobox( targetId, data, "Select an option or type in a new value" );
                //$(targetId).select2("readonly", false);

                //get parent id
                var thisParentId = null;
                var thisData = $(targetId).select2('data');
                if( thisData ) {
                    //console.log( "thisData is ok" );
                    var thisParentId = thisData.parentid;
                } else {
                    //console.log( "thisData is null" );
                }
                //console.log( "thisParentId="+thisParentId );
                if( thisParentId != parentId ) {
                    //console.log( "clear and populate this thisParentId="+thisParentId );
                    //clear tree
                    clearTreeToDown(targetId,holder,fieldClass,parentId);
                    //re-populate this select box
                    populateSelectCombobox( targetId, data, "Select an option or type in a new value" );
                } {
                    //console.log( "load children this thisParentId="+thisParentId );
                    loadChildren($(targetId),holder,fieldClass);
                }
                $(targetId).select2("readonly", false);
            }

//            //test value
//            console.log(fieldClass+': after value='+$(targetId).select2('val'));
//            if( $(targetId).select2('data') ) {
//                console.log('after text='+$(targetId).select2('data').text);
//            }
        });

    }
    else {

        if( clearFlag ) {
            //console.log( "clear combobox, targetId="+targetId);

            //clear combobox
            //populateSelectCombobox( targetId, null, "Select an option or type in a new value" );
            //setElementToId( targetId );
            //$(targetId).select2("readonly", true);

            //clearChildren(holder,fieldClass);
            clearTreeToDown(targetId,holder,fieldClass,parentId);
        }
    }

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


function clearTreeToDown(targetId,holder,fieldClass,parentId) {

    //console.log( "clear tree to down: targetId="+targetId+", fieldClass="+fieldClass+", parentId="+parentId+", cleanThis="+cleanThis );

    if( $(targetId).length == 0 ) {
        //console.log( "clear tree to down: element with targetId does not exists" );
        return;
    }

    var thisParentId = null;
    var thisData = $(targetId).select2('data');
    if( thisData ) {
        //console.log( "thisData is ok" );
        var thisParentId = thisData.parentid;
    } else {
        //console.log( "thisData is null" );
    }
    //console.log( "thisParentId="+thisParentId );

    if( thisParentId == null || parentId == null || thisParentId != parentId ) {
        $(targetId).val('');
        populateSelectCombobox( targetId, null, "Select an option or type in a new value" );
        $(targetId).select2("readonly", true);
    }

    //var holder = parentElement.closest('.user-collection-holder');
    var childrenTargetClass = getChildrenTargetClass(fieldClass);

    if( childrenTargetClass ) {

        //console.log( "clear Children="+childrenTargetClass );
        var childrenTargetId = '#' + holder.find("."+childrenTargetClass).not("*[id^='s2id_']").attr('id');

        if( $(childrenTargetId).select2('data') && $(childrenTargetId).select2('data').parentid != thisParentId ) {
            $(childrenTargetId).val('');
            populateSelectCombobox( childrenTargetId, null, "Select an option or type in a new value" );
            $(childrenTargetId).select2("readonly", true);
        }

        clearTreeToDown(childrenTargetId,holder,childrenTargetClass,thisParentId);

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
        var clearFlag = true; //clear children and default service
        getComboboxTreeByPid($(this),'ajax-combobox-department',null,clearFlag);
    });
}



////////////////// mixed functions ////////////////////
function initTreeSelect(clearFlag) {

    //console.log( "init Tree Select" );

    if( typeof clearFlag === "undefined" ) {
        clearFlag == true;
    }

    $('.ajax-combobox-institution,.ajax-combobox-institution-preset').on('change', function(e){
        //console.log( "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! institution on change" );
        getComboboxTreeByPid($(this),'ajax-combobox-department',null,clearFlag);
    });

    $('.ajax-combobox-department').on('change', function(e){
        //console.log( "department on change" );
        getComboboxTreeByPid($(this),'ajax-combobox-division',null,clearFlag);
    });

    $('.ajax-combobox-division').on('change', function(e){
        //console.log( "division on change" );
        getComboboxTreeByPid($(this),'ajax-combobox-service',null,clearFlag);
    });

    //comments type and subtypes
    $('.ajax-combobox-commenttype').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-commentsubtype',null,clearFlag);
    });

    //residencyspecialty and fellowshipsubspecialty
    $('.ajax-combobox-residencyspecialty').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-fellowshipsubspecialty',null,clearFlag);
    });
}

function getChildrenTargetClass(fieldClass) {

    //console.log( "get children target class: fieldClass="+fieldClass );

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


///////////////// Residency Specialty Tree - initialize the children to null ///////////////////
function setResidencyspecialtyTreeChildren(holder) {

    if( typeof holder == 'undefined' ) {
        holder = $('body');
    }

    var targetId = holder.find(".ajax-combobox-fellowshipsubspecialty");

    //subTypes
    populateSelectCombobox( targetId, null, "Select an option or type in a new value", false );
}
///////////////// EOF Comments Types ///////////////////


///////////////// Tree managemenet ///////////////////
//redirect to correct controller with node id and parent
function editTreeNode(btn) {
    var holder = $(btn).closest('.tree-node-holder');
    //console.log(holder);

    //get node id
    var inputEl = holder.find('input.combobox:text').not("*[id^='s2id_']");
    //console.log(inputEl);
    var nodeid = inputEl.select2('val');
    var res = getInstitutionNodeInfo(inputEl);
    var nodename = res['name'];
    //console.log('nodeid='+nodeid+', nodename='+nodename);

    if( nodename == null ) {
        return;
    }
    //redirect to edit page
    var url = getCommonBaseUrl("admin/list/"+nodename+"s/"+nodeid,"employees");
    //console.log("url="+url);

    window.open(url);
    //window.location.href = url;
}

//redirect to correct controller with node id and parent
function addTreeNode(btn) {
    var holder = $(btn).closest('.tree-node-holder');
    //console.log(holder);

    //get node id
    var inputEl = holder.find('input.combobox:text').not("*[id^='s2id_']");
    //console.log(inputEl);
    var nodeid = inputEl.select2('val');

    //get parent id
    var res = getInstitutionNodeInfo(inputEl);
    var parentClass = res['parentClass'];
    var nodename = res['name'];

    if( nodename == null ) {
        return;
    }

    if( parentClass ) {
        //console.log('parentClass='+parentClass);
        var treeHolder = $(btn).closest('.user-collection-holder');
        var parentEl = treeHolder.find('.'+parentClass);
        //console.log(parentEl);
        var parentid = parentEl.select2('val');
        if( !parentid || parentid == "" ) {
            alert("Parent is not specified");
            return;
        }
        var url = getCommonBaseUrl("admin/list/"+nodename+"/new/parent/"+parentid,"employees");
    } else {
        var url = getCommonBaseUrl("admin/list/institutions/new","employees");
    }
    //redirect to add page
    window.open(url);
    //window.location.href = url;
}

//function getNodeParentClass(nodeInputElement) {
//
//    var parentClass = null;
//
//    if( nodeInputElement.hasClass("ajax-combobox-department") ) {
//        parentClass = "ajax-combobox-institution";
//    }
//    if( nodeInputElement.hasClass("ajax-combobox-division") ) {
//        parentClass = "ajax-combobox-department";
//    }
//    if( nodeInputElement.hasClass("ajax-combobox-service") ) {
//        parentClass = "ajax-combobox-division";
//    }
//
//    return parentClass;
//}

function getInstitutionNodeInfo(nodeInputElement) {

    var name = null;
    var parentClass = null;

    if( nodeInputElement.hasClass("ajax-combobox-institution") ) {
        name = "institution";
    }
    if( nodeInputElement.hasClass("ajax-combobox-department") ) {
        name = "department";
        parentClass = "ajax-combobox-institution";
    }
    if( nodeInputElement.hasClass("ajax-combobox-division") ) {
        name = "division";
        parentClass = "ajax-combobox-department";
    }
    if( nodeInputElement.hasClass("ajax-combobox-service") ) {
        name = "service";
        parentClass = "ajax-combobox-division";
    }

    var res = new Array();
    res['name'] = name;
    res['parentClass'] = parentClass;

    return res;
}
///////////////// EOF Tree managemenet ///////////////////
