/**
 * Created by oli2002 on 9/17/14.
 */


////////////////////////////// TREE //////////////////////////////////

function setTreeByClickingParent(targetid, entityName) {

    var comboboxEl = $(targetid);
    //console.log('combobox len='+comboboxEl.length);


    var treeHolder = comboboxEl.closest('.composite-tree-holder');
    //console.log(treeHolder);

    var breadcrumbs = treeHolder.find('.tree-node-breadcrumbs').val();
    console.log('breadcrumbs='+breadcrumbs);

    if( !breadcrumbs ) {
        return;
    }

    var breadcrumbsArr = breadcrumbs.split(",");

    //var thisId = treeHolder.find('.tree-node-id').val();
    //var thisPid = treeHolder.find('.tree-node-parent').val();

    if( breadcrumbsArr.length > 0 ) {
        //var setid = breadcrumbsArr[0];
        //console.log('set id='+setid);
        //comboboxEl.select2('val',setid, true);

        var nextRowSiblings = comboboxEl.closest('.row');
        for( var i = 0; i < breadcrumbsArr.length; i++ ) {
            comboboxEl = nextRowSiblings.find('.ajax-combobox-institution');
            console.log('set id='+breadcrumbsArr[i]);
            comboboxEl.select2('val',breadcrumbsArr[i]);
            comboboxEl.trigger('change');
            var nextRowSiblings = nextRowSiblings.next();
        }
    }

}

//TODO: set hidden real institution field in the form every time the combobox is chnaged.
//Then, use this real institution field and parent field in controller to create a new instituition.
function comboboxTreeListener( target, entityName ) {

    $(target).on('change', function(e){

        printF( $(this), "combobox on change:" );

        var comboboxEl = $(this);
        var thisData = comboboxEl.select2('data');

        var treeHolder = comboboxEl.closest('.composite-tree-holder');

        //console.log( thisData );

        /////////////////// set id and parent ///////////////////
        setNodeIdPid( entityName, treeHolder, comboboxEl, thisData );
        /////////////////// EOF set id and parent ///////////////////

        //first remove all siblings after this combobox
        var allNextSiblings = comboboxEl.closest('.row').nextAll().remove();

        //check if combobox cleared; if none => do nothing
        if( !thisData ) {
            return;
        }

        var treeArr = getChildrenByParent(entityName,thisData.id);
        //console.log( treeArr );
        //console.log( 'treeArr.length=' + treeArr.length );

        //do nothing if new element was enetered
        if( treeArr == null ) {
            return;
        }

        if( treeArr.length > 0 ) {

            var label = treeArr[0].leveltitle;
            var newid = "newnode-" + label;

            //readonly combobox
            var readonly = "";
            if( cycle.indexOf("show") != -1 ) {
                readonly = "readonly";
            }

            var collen = '6';
            var userpositions = '';
            if( treeHolder.hasClass('institution-with-userpositions') ) {
                collen = '4';
                userpositions =
                    '<div class="col-xs-2" align="left">' +
                        '<input id="userposition-'+newid+'" class="combobox ajax-combobox-userpositions" type="hidden" ' + readonly + '/>' +
                    '</div>';
            }
            console.log('collen='+collen);

            // 1) construct and attach a new select2 combobox below comboboxEl (parent combobox)
            var comboboxHtml =
                '<p><div class="row">' +
                    '<div class="col-xs-6" align="right">' +
                        '<strong>'+label+':</strong>' +
                    '</div>' +
                    '<div class="col-xs-'+collen+'" align="left">' +
                        '<input id="institution-'+newid+'" class="ajax-combobox-institution" type="hidden" ' + readonly + '/>' +
                    '</div>' +
                    userpositions +
                '</div></p>';

            var comboboxHtml = getNewTreeNode(treeHolder);    //treeHolder.find('#node-userpositions-data');

            //var comboboxHtml = '<input id="new-tree" class="ajax-combobox-institution" type="text"/>';

            //var treeHolder = comboboxEl.closest('.composite-tree-holder');
            //console.log( treeHolder );

            treeHolder.append(comboboxHtml);

            //console.log( 'newid='+newid );
            var newElementsAppended = treeHolder.find('#institution-'+newid);
            //var newElementsAppended = treeHolder.find('.ajax-combobox-institution');
            console.log( 'newElementsAppended.id='+newElementsAppended.attr('id') );
            populateSelectCombobox( newElementsAppended, treeArr, "Select an option");

            var newUserposition = treeHolder.find('#userposition-'+newid);
            //var newElementsAppended = treeHolder.find('.ajax-combobox-institution');
            //console.log( 'newUserposition.id='+newUserposition.attr('id') );
            //populateSelectCombobox( newUserposition, null, "Select an option", true );
            regularCombobox(treeHolder);

            //wait
            comboboxTreeListener( newElementsAppended, entityName );

            // 2) replace id and name of newly created combobox with the parent

        } //if

    });

}

function getNewTreeNode(treeHolder) {
    var datael = treeHolder.find('#node-userpositions-data');

    if( !datael ) {
        return;
    }

    var prototype = datael.data('prototype-'+'user-userpositions');

    //var index = 0;
    var index = getNextElementCount(treeHolder,'ajax-combobox-institution');

    //prototype = prototype.replace("__documentContainers__", "0");
    prototype = prototype.replace(/__userpositions__/g, index);

    console.log( "prototype=" + prototype );

    return prototype;
}

function setNodeIdPid( entityName, treeHolder, node, data ) {

    //console.log( data );

    //treeHolder.find('.tree-node-id').val(data.id);
    //treeHolder.find('.tree-node-parent').val(data.pid);

    //1 case: data has id and pid - existing node (pid might be 0)
    if( data && data.id && data.hasOwnProperty("pid") ) {
        //console.log("1 case: data has id and pid - existing node");
        treeHolder.find('.tree-node-id').val(data.id);
        treeHolder.find('.tree-node-parent').val(data.pid);
    }

    //2 case: data is null - clear node => set as previous select box
    if( !data ) {
        //console.log("2 case: data is null - clear node");
        var prevNodeData = node.closest('.row').prev().find('.ajax-combobox-institution').select2('data');
        //console.log(prevNodeData);
        var thisId = 0;
        var thisPid = 0;
        if( prevNodeData ) {
            thisId = prevNodeData.id;
            thisPid = prevNodeData.pid;
        }
        //console.log( 'id='+thisId+", pid="+thisPid );
        treeHolder.find('.tree-node-id').val(thisId);
        treeHolder.find('.tree-node-parent').val(thisPid);
    }

    //3 case: data has id and text (both equal to a node name), but does not have pid - new node => pid is previous select box
    //generate new node in DB
    if( data && data.id && !data.hasOwnProperty("pid") ) {
        //console.log("3 case: new node");

        var prevNodeData = node.closest('.row').prev().find('.ajax-combobox-institution').select2('data');
        //console.log(prevNodeData);

        var conf = "Are you sure you want to create " + "'" + data.id + "?";
        if( prevNodeData && data.hasOwnProperty("leveltitle") ) {
            conf = "Are you sure you want to create " + "'" + data.id + "' under " + prevNodeData.leveltitle + "?";
        }
        if( !window.confirm(conf) ) {
            //treeHolder.find('.tree-node-id').val(0);
            node.select2('data', null);
            return;
        }

        var thisPid = 0;
        if( prevNodeData ) {
            thisPid = prevNodeData.id;
        }

        var newnodeid = jstree_action_node(entityName, 'create_node', null, data.id, thisPid, null, null, null, null, 'combobox');

        if( newnodeid ) {
            treeHolder.find('.tree-node-id').val(newnodeid);
            treeHolder.find('.tree-node-parent').val(prevNodeData.id);
        } else {
            treeHolder.find('.tree-node-id').val(0);
            treeHolder.find('.tree-node-parent').val(prevNodeData.id);
        }

    }

    //set id and pid only
//    if( data && data.id && !data.hasOwnProperty("pid") ) {
//        console.log("3 case: new node");
//        var prevNodeData = node.closest('.row').prev().find('.ajax-combobox-institution').select2('data');
//        console.log(prevNodeData);
//        var thisId = data.id;
//        var thisPid = 0;
//        if( prevNodeData ) {
//            //thisId = prevNodeData.id
//            thisPid = prevNodeData.id;
//        }
//        console.log( 'id='+thisId+", pid="+thisPid );
//        treeHolder.find('.tree-node-id').val(thisId);
//        treeHolder.find('.tree-node-parent').val(thisPid);
//    }

    //console.log( 'id='+treeHolder.find('.tree-node-id').val()+", pid="+treeHolder.find('.tree-node-parent').val() );
}

////////////////////////////// EOF TREE //////////////////////////////////













////////////////////////////// OLD TREE: TO DELETE //////////////////////////////////

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
