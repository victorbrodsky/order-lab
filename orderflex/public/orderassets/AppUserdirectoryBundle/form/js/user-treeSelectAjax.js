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
 * Created by oli2002 on 9/17/14.
 */

////////////////////////////// TREE //////////////////////////////////

//attach parent combobox above current
function setParentComboboxree(targetid, bundleName, entityName, rowElHtml) {

    var comboboxEl = $(targetid);
    //console.log('set parent: this combobox len='+comboboxEl.length);

    var treeHolder = comboboxEl.closest('.composite-tree-holder');
    //console.log(treeHolder);

    var thisData = comboboxEl.select2('data');
    //console.log(thisData);

    if( !thisData ) {
        clearElementsIdName(treeHolder);
        return;
    }

    //console.log('thisData.pid='+thisData.pid);

    //exit if no parent
    if( thisData.pid == 0 ) {
        clearElementsIdName(treeHolder);
        return;
    }

    getChildrenByParent(bundleName,entityName,comboboxEl,thisData.pid,null).
    then(function (treeArr) {
        var newElementsAppended = createNewTreenodeCombobox( bundleName,entityName, treeHolder, comboboxEl, treeArr, rowElHtml, 'top' );
        if( newElementsAppended ) {
            newElementsAppended.select2('val',thisData.pid);
            setParentComboboxree(newElementsAppended,bundleName,entityName,rowElHtml);
        }
    });

}

//TODO: separate change to select and unselect select2 event
function comboboxTreeListener( target, bundleName, entityName, rowElHtml ) {

    $(target).on('change', function(e){
    //$(target).on("select2-selecting", function (e) {

        //console.log("comboboxTreeListener: change", e);
        //printF( $(this), "comboboxTreeListener: combobox on select:" );

        var comboboxEl = $(this);
        var thisData = comboboxEl.select2('data');
        //if( thisData ) {
            //console.log("0 combobox on change, id=" + thisData.id);
        //}

        var treeHolder = comboboxEl.closest('.composite-tree-holder');

        //console.log( thisData );

        /////////////////// create and set id if node is new ///////////////////
        setTreeNode( bundleName, entityName, treeHolder, comboboxEl, thisData );
        var thisData = comboboxEl.select2('data');
        //if( thisData ) {
            //console.log("1 combobox on change, id=" + thisData.id);
        //}
        /////////////////// EOF create and set id if node is new ///////////////////

        //additional actions of combobox chaged before remove (change form fields for calllog)
        //removedEl = e.removed.id
        if( 'removed' in e && e.removed ) {
            treeSelectAdditionalJsActionRemove(comboboxEl,e.removed.id);
        }

        //first remove all siblings after this combobox
        var allNextSiblings = comboboxEl.closest('.row').nextAll().remove();
        clearElementsIdName(treeHolder);

        //run function to init node by data-compositetree-initnode-function
        var initNodeFunctionStr = comboboxEl.attr("data-compositetree-initnode-function"); //i.e. getOptionalUserResearch or
        if( initNodeFunctionStr ) {
            var initNodeFunction = window[initNodeFunctionStr];
            initNodeFunction(treeHolder,thisData);
        }

        //check if combobox cleared; if none => do nothing
        //console.log( thisData );
        if( !thisData ) {
            return;
        }

        //for cycle=show don't show empty children
        //console.log("_cycleShow="+_cycleShow);
        if( _cycleShow ) {
            //console.log("don't populate children");
            return;
        }

        getChildrenByParent(bundleName,entityName,comboboxEl,null,thisData.id).
        then(function (treeArr) {
            //console.log( 'treeArr:' );
            //console.log( treeArr );

            var newElementsAppended = createNewTreenodeCombobox( bundleName, entityName, treeHolder, comboboxEl, treeArr, rowElHtml, 'bottom' );
            //console.log( newElementsAppended );
            if( newElementsAppended ) {
                //remove id and name for all inputs preceding the input with selected node
                clearElementsIdName(treeHolder);
            }
            //return newElementsAppended;
        });
//        .then(function (newElementsAppended) {
//            if( newElementsAppended ) {
//                //run function to init node by data-compositetree-initnode-function
//                initNodeFunction(thisData);
//            }
//        });

    }); //select

    //$(target).on("select2-selecting", function (e) {
    //    console.log("select2-selecting", e);
    //    printF( $(this), "comboboxTreeListener: select2-selecting:" );
    //});

    //$(target).on("select2-removed", function (e) {
    //    console.log("select2:unselect", e);
    //    printF( $(this), "comboboxTreeListener: combobox on removed:" );
    //
    //    var comboboxEl = $(this);
    //    var thisData = comboboxEl.select2('data');
    //    if( thisData ) {
    //        console.log("0 combobox on unselect, id=" + thisData.id);
    //    }
    //
    //    var treeHolder = comboboxEl.closest('.composite-tree-holder');
    //
    //    var comboboxId = e.choice.id;
    //
    //    //additional actions of combobox chaged before remove (change form fields for calllog)
    //    treeSelectAdditionalJsActionRemove(comboboxEl,comboboxId);
    //
    //    //remove all siblings after this combobox
    //    var allNextSiblings = comboboxEl.closest('.row').nextAll().remove();
    //    clearElementsIdName(treeHolder);
    //});

}


function getComboboxNodeLabel(comboboxEl) {
    var label = comboboxEl.closest('.treenode').find('label').html();
    label = label.replace(':','');
    label = trimWithCheck(label);
//    var prefixData = comboboxEl.data("label-prefix");
//    if( prefixData ) {
//        label = label.replace(prefixData,'');
//    }
    return label;
}

function createNewTreenodeCombobox( bundleName, entityName, treeHolder, comboboxEl, treeArr, rowElHtml, attachflag ) {

    if( treeArr == null ) {
        //console.log('do nothing if new element was enetered');
        return false;
    }

    if( treeArr.length > 0 ) {

        var label = treeArr[0].leveltitle;
        //console.log( 'label='+ label );

        //get combobox label
        label = constructComboboxLabel(comboboxEl,label);
        //console.log( 'label='+ label );

        //readonly combobox
        //var readonly = "";
        //if( cycle.indexOf("show") != -1 ) {
        //    readonly = "readonly";
        //}

        //add readonly if class exists 'combobox-compositetree-readonly-parent'
        if( comboboxEl.hasClass('combobox-compositetree-readonly-parent') ) {
            //console.log('rowElHtml='+rowElHtml);
            var level = treeArr[0].level;
            var readonlyParentLevel = comboboxEl.data("readonly-parent-level");
            //console.log( label+': level='+ level + " ?= readonlyParentLevel="+readonlyParentLevel );
            if( parseInt(level) <= parseInt(readonlyParentLevel) ) {
                //console.log("add readonly!!!");
                var origReplaceStr = 'data-readonly-parent-level=';
                var toReplaceStr = 'readonly="readonly" '+origReplaceStr;
                rowElHtml = rowElHtml.replace(origReplaceStr, toReplaceStr);
                //console.log('rowElHtml='+rowElHtml);
            }
        }

        //remove readonly classes for excluded levels indicated by data-read-only-exclusion-after-level
        if( comboboxEl.hasClass('combobox-compositetree-read-only-exclusion') ) {
            //console.log('rowElHtml='+rowElHtml);
            var level = treeArr[0].level;
            var readonlyExclusionAfterLevel = comboboxEl.data("read-only-exclusion-after-level");
            //console.log( label+': level='+ level + " ?= readonlyExclusionAfterLevel="+readonlyExclusionAfterLevel );
            if( parseInt(level) >= parseInt(readonlyExclusionAfterLevel) ) {
                //console.log("remove readonly!!!");
                var origReplaceStr = 'readonly="readonly"';
                var toReplaceStr = '';
                rowElHtml = rowElHtml.replace(origReplaceStr, toReplaceStr);
                //console.log('rowElHtml='+rowElHtml);
            }
        }

        //attach data-label-postfix-value-level after level data-specified label-postfix-level
        if( comboboxEl.hasClass('combobox-compositetree-postfix-level') ) {
            //console.log('rowElHtml='+rowElHtml);
            var level = treeArr[0].level;
            var readonlyExclusionAfterLevel = comboboxEl.data("label-postfix-level"); //'3' or '3,4'
            console.log("readonlyExclusionAfterLevel="+readonlyExclusionAfterLevel);
            var readonlyExclusionAfterLevelArr = [];
            if( readonlyExclusionAfterLevel.indexOf(',') !== -1 ) {
                readonlyExclusionAfterLevelArr = readonlyExclusionAfterLevel.split(",");
                console.log("readonlyExclusionAfterLevel has comma");
            } else {
                readonlyExclusionAfterLevelArr.push(readonlyExclusionAfterLevel);
                console.log("readonlyExclusionAfterLevel is single value");
            }
            //var readonlyExclusionAfterLevelArr = readonlyExclusionAfterLevel.split(",");
            console.log("readonlyExclusionAfterLevelArr:");
            console.log(readonlyExclusionAfterLevelArr);
            for( var index = 0; index < readonlyExclusionAfterLevelArr.length; ++index ) {
                readonlyExclusionAfterLevel = readonlyExclusionAfterLevelArr[index];
                console.log( label+': level='+ level + " ?= readonlyExclusionAfterLevel="+readonlyExclusionAfterLevel );
                if( parseInt(level) == parseInt(readonlyExclusionAfterLevel) ) {
                    var postfixValueLevel = comboboxEl.data("label-postfix-value-level");
                    label = label + "" + postfixValueLevel;
                }
            }
            // readonlyExclusionAfterLevelArr.forEach(
            //     function(readonlyExclusionAfterLevel) {
            //         console.log( label+': level='+ level + " ?= readonlyExclusionAfterLevel="+readonlyExclusionAfterLevel );
            //         if( parseInt(level) == parseInt(readonlyExclusionAfterLevel) ) {
            //             var postfixValueLevel = comboboxEl.data("label-postfix-value-level");
            //             label = label + "" + postfixValueLevel;
            //         }
            //     }
            // );
            // //console.log( label+': level='+ level + " ?= readonlyExclusionAfterLevel="+readonlyExclusionAfterLevel );
            // if( parseInt(level) >= parseInt(readonlyExclusionAfterLevel) ) {
            //     var postfixValueLevel = comboboxEl.data("label-postfix-value-level");
            //     label = label + "" + postfixValueLevel;
            // }
        }

        var comboboxHtml = rowElHtml;

        //var comboboxHtml = '<input id="new-tree" class="ajax-combobox-compositetree" type="text"/>';

        //var treeHolder = comboboxEl.closest('.composite-tree-holder');
        //console.log( treeHolder );

        //treeHolder.append(comboboxHtml);
        if( attachflag == 'bottom' ) {
            var newElementsAppendedRaw = $(comboboxHtml).appendTo(treeHolder);
        }

        if( attachflag == 'top' ) {
            var newElementsAppendedRaw = $(comboboxHtml).insertBefore(comboboxEl.closest('.treenode'));
            //label = "Top "+label;
        }

        //change label
        newElementsAppendedRaw.find('label').html(label+":");

        ///////////// initialize the node
        var newElementsAppended = newElementsAppendedRaw.find('.ajax-combobox-compositetree');
        //console.log( 'newElementsAppended.id='+newElementsAppended.attr('id') );
        populateSelectCombobox( newElementsAppended, treeArr, "Select an option");

        //add listener to this element
        comboboxTreeListener( newElementsAppended, bundleName, entityName, rowElHtml );

        return newElementsAppended;
    } //if

    return false;
}

function constructComboboxLabel(comboboxEl,label) {
    //data-label-prefix
    var prefixData = comboboxEl.data("label-prefix");
    if( prefixData ) {
        label = prefixData + ' ' + label;
    }

    //data-label-postfix
    var postfixData = comboboxEl.data("label-postfix");
    if( postfixData ) {
        label = label + ' ' + postfixData;
    }
    return label;
}

//modify all id and name by attaching a prefix "newelement_" to all ajax-combobox-compositetree element prior to the last not empty combobox
function clearElementsIdName(treeHolder) {
    //console.log('treeHolder=');
    //console.log(treeHolder);

    var lastNode = treeHolder.find('.treenode').last();
    //printF(lastNode,'clear el by lastNode:');
    //console.log('lastNode:');
    //console.log(lastNode);

    clearRecursivelyIdName(lastNode);
}
function clearRecursivelyIdName(treenode) {
    //console.log('process treenode:');
    //console.log(treenode);

    if( treenode.length == 0 ) {
        //console.log('treenode is null');
        return;
    }

    var lastComboboxEl = treenode.find('div.ajax-combobox-compositetree');
    var lastInputEl = treenode.find('input.ajax-combobox-compositetree');
    if( lastComboboxEl.length == 0 || lastInputEl.length == 0 ) {
        //console.log('lastComboboxEl or lastInputEl is null');
        return;
    }

    if( !lastComboboxEl.attr('id') ) {
        //console.log('lastComboboxEl id is null');
        return;
    }

    var comboboxData = lastComboboxEl.select2('data');
    //console.log('comboboxData:');
    //console.log(comboboxData);

    if( comboboxData == null ) {
        //console.log('comboboxData is null');
        //unmap element
        mapTreeNode(lastInputEl,false);
        clearRecursivelyIdName(treenode.prev());
        return;
    }

    //console.log('lastComboboxEl:');
    //console.log(lastComboboxEl);

    //console.log('comboboxData.id='+comboboxData.id+', text='+comboboxData.text);

    if( comboboxData && comboboxData.id ) {
        //unmap all previous siblings
        treenode.prevAll().each( function(){
            var inputEl = $(this).find('input.ajax-combobox-compositetree');
            mapTreeNode(inputEl,false);
        });
        //map element
        mapTreeNode(lastInputEl,true);
        return;
    }

    return;
}

function mapTreeNode( element, mapped ) {
    //console.log('modify element:');
    //console.log(element.prevObject);
    //printF(element,'modify id and name:');
    var activeNodeClass = 'active-tree-node';
    var prefix = 'newnode_';
    var elId = element.attr('id');
    var elName = element.attr('name');
    //console.log('modify element: prefix='+prefix+', id='+elId+', elName='+elName);

    if( !elId || !elName ) {
        return;
    }

    if( mapped ) {
        //active node => remove prefix
        elId = elId.replace(prefix, "");
        elName = elName.replace(prefix, "");
        element.addClass(activeNodeClass);
    } else {
        //not active node => add prefix
        if( elId.indexOf(prefix) == -1 ) {
            elId = prefix+elId;
        }
        if( elName.indexOf(prefix) == -1 ) {
            elName = prefix+elName;
        }
        element.removeClass(activeNodeClass);
    }

    element.attr('id',elId);
    element.attr('name',elName);
}

function setTreeNode( bundleName, entityName, treeHolder, node, data ) {
    //3 case: data has id and text (both equal to a node name), but does not have pid - new node => pid is previous select box
    //generate new node in DB
    if( data && data.id && !data.hasOwnProperty("pid") ) {
        //console.log("3 case: new node");

        var prevNodeData = node.closest('.row').prev().find('.ajax-combobox-compositetree').select2('data');
        //console.log(prevNodeData);

        var conf = "Are you sure you want to create " + "'" + data.id + "'?";
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

        var newnodeid = jstree_action_node(bundleName, entityName, 'create_node', null, data.id, thisPid, null, null, null, null, 'combobox');
        if( newnodeid ) {
            node.select2("data", {id: newnodeid, text: data.id});
            //node.trigger('change');
        } else {
            //newnodeid = 0;
            node.select2('data', null);
        }
        //console.log(node.select2('data'));
    }
}

function treeSelectAdditionalJsActionRemove(comboboxEl,comboboxId) {
    return;
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
//        //app_userdirectorybundle_user_administrativeTitles_0_service
//        //app_userdirectorybundle_user_administrativeTitles_0_service
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

            //remove tooltip
            $(targetId).parent().tooltip('destroy');

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


////////////////// mixed functions ////////////////////
function initTreeSelect(clearFlag) {

    //console.log( "init Tree Select" );

    if( typeof clearFlag === "undefined" ) {
        clearFlag == true;
    }

    //residencyspecialty and fellowshipsubspecialty
    $('.ajax-combobox-residencyspecialty').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-fellowshipsubspecialty',null,clearFlag);

        //add tooltip if disabled
        var holder = $(this).closest('.user-collection-holder');
        var thisFellowshipSubSpecialtyEl = holder.find('.ajax-combobox-fellowshipsubspecialty');
        if( thisFellowshipSubSpecialtyEl.length > 0 && thisFellowshipSubSpecialtyEl.hasClass('select2-container-disabled') ) {
            //console.log('init tooltip');
            attachTooltipToSelectCombobox('.ajax-combobox-fellowshipsubspecialty',thisFellowshipSubSpecialtyEl);
        }
    });

    //init tooltip for ajax-combobox-fellowshipsubspecialty
    if( cycle && cycle.indexOf("show") == -1 ) {
        attachTooltipToSelectCombobox('.ajax-combobox-fellowshipsubspecialty',null);
    }
}

function getChildrenTargetClass(fieldClass) {

    //console.log( "get children target class: fieldClass="+fieldClass );

    var childrenTargetClass = null;

//    if( fieldClass == "ajax-combobox-institution" ) {
//        childrenTargetClass = "ajax-combobox-department";
//    }
//    if( fieldClass == "ajax-combobox-department" ) {
//        childrenTargetClass = "ajax-combobox-division";
//    }
//    if( fieldClass == "ajax-combobox-division" ) {
//        childrenTargetClass = "ajax-combobox-service";
//    }
//
//    //comments type and subtypes
//    if( fieldClass == "ajax-combobox-commenttype" ) {
//        childrenTargetClass = "ajax-combobox-commentsubtype";
//    }

    return childrenTargetClass;
}
////////////////// EOF mixed functions ////////////////////




///////////////// Institution Tree ///////////////////
//function setInstitutionTreeChildren(holder) {
//
//    //console.log( "set Institution Tree Children" );
//
//    if( typeof holder == 'undefined' ) {
//        holder = $('body');
//    }
//
//    //department
//    populateSelectCombobox( holder.find(".ajax-combobox-department"), null, "Select an option or type in a new value", false );
//
//    //division
//    populateSelectCombobox( holder.find(".ajax-combobox-division"), null, "Select an option or type in a new value", false );
//
//    //service
//    populateSelectCombobox( holder.find(".ajax-combobox-service"), null, "Select an option or type in a new value", false );
//
//}
///////////////// EOF Institution Tree ///////////////////



///////////////// Comments Types Tree - initialize the children to null ///////////////////
//function setCommentTypeTreeChildren(holder) {
//
//    if( typeof holder == 'undefined' ) {
//        holder = $('body');
//    }
//
//    var targetId = holder.find(".ajax-combobox-commentsubtype");
//
//    //subTypes
//    populateSelectCombobox( targetId, null, "Select an option or type in a new value", false );
//}
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
//function editTreeNode(btn) {
//    var holder = $(btn).closest('.tree-node-holder');
//    //console.log(holder);
//
//    //get node id
//    var inputEl = holder.find('input.combobox:text').not("*[id^='s2id_']");
//    //console.log(inputEl);
//    var nodeid = inputEl.select2('val');
//    var res = getInstitutionNodeInfo(inputEl);
//    var nodename = res['name'];
//    //console.log('nodeid='+nodeid+', nodename='+nodename);
//
//    if( nodename == null ) {
//        return;
//    }
//    //redirect to edit page
//    var url = getCommonBaseUrl("admin/list/"+nodename+"s/"+nodeid,"employees");
//    //console.log("url="+url);
//
//    window.open(url);
//    //window.location.href = url;
//}

//redirect to correct controller with node id and parent
//function addTreeNode(btn) {
//    var holder = $(btn).closest('.tree-node-holder');
//    //console.log(holder);
//
//    //get node id
//    var inputEl = holder.find('input.combobox:text').not("*[id^='s2id_']");
//    //console.log(inputEl);
//    var nodeid = inputEl.select2('val');
//
//    //get parent id
//    var res = getInstitutionNodeInfo(inputEl);
//    var parentClass = res['parentClass'];
//    var nodename = res['name'];
//
//    if( nodename == null ) {
//        return;
//    }
//
//    if( parentClass ) {
//        //console.log('parentClass='+parentClass);
//        var treeHolder = $(btn).closest('.user-collection-holder');
//        var parentEl = treeHolder.find('.'+parentClass);
//        //console.log(parentEl);
//        var parentid = parentEl.select2('val');
//        if( !parentid || parentid == "" ) {
//            alert("Parent is not specified");
//            return;
//        }
//        var url = getCommonBaseUrl("admin/list/"+nodename+"/new/parent/"+parentid,"employees");
//    } else {
//        var url = getCommonBaseUrl("admin/list/institutions/new","employees");
//    }
//    //redirect to add page
//    window.open(url);
//    //window.location.href = url;
//}

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
//
//function getInstitutionNodeInfo(nodeInputElement) {
//
//    var name = null;
//    var parentClass = null;
//
//    if( nodeInputElement.hasClass("ajax-combobox-institution") ) {
//        name = "institution";
//    }
//    if( nodeInputElement.hasClass("ajax-combobox-department") ) {
//        name = "department";
//        parentClass = "ajax-combobox-institution";
//    }
//    if( nodeInputElement.hasClass("ajax-combobox-division") ) {
//        name = "division";
//        parentClass = "ajax-combobox-department";
//    }
//    if( nodeInputElement.hasClass("ajax-combobox-service") ) {
//        name = "service";
//        parentClass = "ajax-combobox-division";
//    }
//
//    var res = new Array();
//    res['name'] = name;
//    res['parentClass'] = parentClass;
//
//    return res;
//}
///////////////// EOF Tree managemenet ///////////////////
