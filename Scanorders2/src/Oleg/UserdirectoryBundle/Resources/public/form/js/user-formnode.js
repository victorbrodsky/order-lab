/**
 * Created by ch3 on 12/16/2016.
 */

var _holderNamespace = "Oleg\\OrderformBundle\\Entity";
var _holderName = "MessageCategory";
var _formnode = [];

function treeSelectAdditionalJsAction(comboboxEl) {
    //printF( comboboxEl, "treeSelectAdditionalJsAction: combobox on change:" );

    if( !comboboxEl.hasClass("ajax-combobox-messageCategory") ) {
        //console.log('this combobox is not message category');
        return;
    }

    var thisData = comboboxEl.select2('data');
    var messageCategoryId = thisData.id;
    //console.log("treeSelectAdditionalJsAction: messageCategoryId="+messageCategoryId);

    if( typeof messageCategoryId === 'undefined' || !messageCategoryId ) {
        //console.log("return: messageCategoryId doesnot exists: "+messageCategoryId);
        return;
    }

    //testing: do nothing if the fields were populated by controller
    //var holderId = "formnode-holder-"+messageCategoryId;
    //var holderEl = document.getElementById(holderId);
    //if( holderEl && !(identifier in _formnode) ) {
    //    return;
    //}

    //var holderNamespace = "Oleg\\OrderformBundle\\Entity";
    //var holderName = "MessageCategory";

    var identifier = _holderName+"-"+messageCategoryId;

    //console.log("########## identifier="+identifier);
    //console.log("_formnode[identifier]="+_formnode[identifier]);

    if( identifier in _formnode && _formnode[identifier] ) {
        //console.log("return: identifier already exists: " + identifier);
        //$('#formnode-holder-'+_formnode[identifier]['formNodeId']).show();
        calllogDisabledEnabledFormNode('enable',messageCategoryId);
        return;
    } else {
        //console.log("GET url: identifier does not exists: " + identifier);
        _formnode[identifier] = 1;  //set flag meaning that this identifier will be set after ajax is completed
    }

    var _formcycle = $('#formcycle').val();
    var _entityNamespace = $('#entityNamespace').val();   //"Oleg\\OrderformBundle\\Entity";
    var _entityName = $('#entityName').val();             //"Message";
    var _entityId = $('#entityId').val();                 //"Message ID";
    //console.log("_entityNamespace="+_entityNamespace);
    //console.log("_entityName="+_entityName);
    //console.log("_entityId="+_entityId);

    var dataParam =
    {
        holderNamespace: _holderNamespace,
        holderName: _holderName,
        holderId: messageCategoryId,
        entityNamespace: _entityNamespace,
        entityName: _entityName,
        entityId: _entityId,
        cycle: _formcycle
    };

    var url = Routing.generate('employees_formnode_fields');
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        async: asyncflag,
        data: dataParam,
    }).success(function(data) {
        //console.log("data length="+data.length);
        //console.log(data);

        if (data.length > 0 && data[0]['formNodeId']) { //make sure we have at least one formNode for formNode Holder

            //console.log("data[0]['formNodeHolderId']="+data[0]['formNodeHolderId']);

            calllogAppendFormNodes(data);

            //console.log("ajax identifier="+identifier);
            _formnode[identifier] = data;   //data[0]['formNodeHolderId'];

        } else {
            _formnode[identifier] = null;
            //console.log("No data: data.length="+data.length);
        }

        //$.bootstrapSortable(true);

        //if( data != "ERROR" ) {
        //    //holder.find('.calllog-patient-panel-title').html(data);
        //} else {
        //    //holder.find('.calllog-patient-panel-title').html("Patient Info");
        //}
    }).fail(function() {
        alert("Error getting field(s) for "+_holderName+" "+messageCategoryId);
    }).done(function() {
        //console.log("update patient title done");
    });
}

function calllogAppendFormNodes( data ) {
    for( var index = 0; index < data.length; ++index ) {

        var formNodeHolderId = data[index]['formNodeHolderId'];
        var parentFormNodeId = data[index]['parentFormNodeId'];
        var formNodeId = data[index]['formNodeId'];
        var formNodeHtml = data[index]['formNodeHtml'];

        calllogAppendElement(formNodeHolderId,parentFormNodeId,formNodeId,formNodeHtml);

        if(
            data[index]['formNodeObjectType'] == "Form Field - Dropdown Menu" ||
            data[index]['formNodeObjectType'] == "Form Field - Dropdown Menu - Allow Multiple Selections" ||
            data[index]['formNodeObjectType'] == "Form Field - Month" ||
            data[index]['formNodeObjectType'] == "Form Field - Day of the Week"
        ) {
            regularCombobox($('#formnode-'+formNodeId));
        }

        if(
            data[index]['formNodeObjectType'] == "Form Field - Date" ||
            data[index]['formNodeObjectType'] == "Form Field - Full Date" ||
            data[index]['formNodeObjectType'] == "Form Field - Full Date and Time" ||
            data[index]['formNodeObjectType'] == "Form Field - Year" ||
            data[index]['formNodeObjectType'] == "Form Field - Month" ||
            data[index]['formNodeObjectType'] == "Form Field - Day of the Week"
        ) {
            initDatepicker($('#formnode-'+formNodeId));
        }

        if(
            data[index]['formNodeObjectType'] == "Form Field - Free Text" ||
            data[index]['formNodeObjectType'] == "Form Field - Free Text, RTF" ||
            data[index]['formNodeObjectType'] == "Form Field - Free Text, HTML"
        ) {
            expandTextarea($('#formnode-'+formNodeId));
        }
    }
}
//find the latest parent formnode holder element by parentFormNodeId id
function calllogAppendElement( formNodeHolderId, parentFormNodeId, formNodeId, formNodeHtml ) {

    //console.log("calllog AppendElement: formNodeHolderId="+formNodeHolderId+"; parentFormNodeId="+parentFormNodeId+"; formNodeId="+formNodeId);

    //check if parent formnode exists and append this formnode to the parent formnode
    var parentId = "formnode-"+parentFormNodeId;
    var parentEl = document.getElementById(parentId);
    //calllogGetFormNodeElement

    //check if this element does not exist
    var formNodeElId = "formnode-"+formNodeId;
    var formNodeEl = document.getElementById(formNodeElId);
    if( formNodeEl ) {
        //console.log("EXIT: formnode-holder-"+formNodeId+" already exists!");
        //calllogDisabledEnabledFormNode('enable',formNodeHolderId);

        if( parentEl ) {
            //if already exists, make sure that it is visible
            calllogDisabledEnabledSingleFormNode('enable', parentFormNodeId);
        }

        //enable formnode
        calllogDisabledEnabledSingleFormNode('enable', formNodeId);

        return null;
    }

    //check if parent formnode exists and append this formnode to the parent formnode
    //var parentId = "formnode-holder-"+parentFormNodeId;
    //var parentEl = document.getElementById(parentId);
    if( parentEl ) {
        //console.log("parentId found="+parentId);
        //console.log('form-nodes-holder count='+$(parentEl).find('.form-nodes-holder').length);
        //console.log($(parentEl));
        appendEl = $(parentEl).find('.form-nodes-holder').first(); //get the panel-body of the parent section
        //console.log(appendEl);

        //if already exists, make sure that it is visible
        calllogDisabledEnabledSingleFormNode('enable', parentFormNodeId);

        appendEl.append(formNodeHtml); //Insert content, specified by the parameter, to the end of each element in the set of matched elements.
        //appendEl.after(formNodeHtml);
        return appendEl;
    } else {
        //console.log("parentId not found");
    }

    //regular append to the end (for new,edit) or beginning (for show) of the global form
    var appendEl = $("#form-node-holder");
    //printF(appendEl,"!!! appendEl global:");
    //console.log(appendEl);
    //appendEl.append(formNodeHtml);
    var attachType = 'append';
    var formcycle = $('#formcycle').val();
    if( formcycle == 'show' ) {
        var attachType = 'prepend';
    }
    calllogAttachHtml(appendEl,formNodeHtml,attachType);
    return appendEl;
}
function calllogAttachHtml(element,html,type) {
    if( type == 'append' ) {
        element.append(html);
    }
    if( type == 'prepend' ) {
        element.prepend(html);
    }
}

////NOT USED
//function calllogAppendFormNodes_Old( data ) {
//    for( var index = 0; index < data.length; ++index ) {
//        var idBreadcrumbsArr = data[index]['idBreadcrumbsArr'];
//        var formNodeHtml = data[index]['formNodeHtml'];
//        var formNodeId = data[index]['formNodeId'];
//        calllogAppendElement(idBreadcrumbsArr,formNodeHtml,formNodeId);
//    }
//}
////NOT USED
////find the latest parent formnode holder element by breadcrumb ids
//function calllogAppendElement_Old( idBreadcrumbsArr, formNodeHtml, formNodeId ) {
//    var appendEl = $("#form-node-holder");
//
//    for( var index = 0; index < idBreadcrumbsArr.length; ++index ) {
//        console.log(index+": idBreadcrumb="+idBreadcrumbsArr[index]);
//        var holderId = "formnode-holder-"+idBreadcrumbsArr[index]+"-"+formNodeId;
//        var parentEl = document.getElementById(holderId);
//        if( parentEl ) {
//            console.log("parent holderId found="+holderId);
//            //printF(parentEl,"parent found");
//            //appendEl = $(parentEl).find('.form-nodes-holder');
//            appendEl = $(parentEl).find('.row').last();
//            //if( appendEl.length > 0 ) {
//            //    console.log("form-nodes-holder found in ="+holderId);
//            //    //appendEl = $(parentEl).find('.form-nodes-holder');
//            //} else {
//            //    console.log("form-nodes-holder not found!!! in ="+holderId);
//            //    appendEl = $(parentEl).find('.row').parent();
//            //}
//            printF(appendEl,"idBreadcrumbsArr: appendEl found:");
//            console.log(appendEl);
//            //return $(parentEl).find('.row').last();
//
//            //appendEl.after(formNodeHtml);
//            //console.log("0 formNodeHtml="+formNodeHtml);
//            //formNodeHtml = "<br>"+formNodeHtml;
//            //console.log("1 formNodeHtml="+formNodeHtml);
//            //appendEl.append(formNodeHtml);
//            appendEl.after(formNodeHtml);
//            return appendEl;
//        }
//    }
//
//    printF(appendEl,"appendEl found:");
//    console.log(appendEl);
//    appendEl.append(formNodeHtml);
//    return appendEl;
//}

function treeSelectAdditionalJsActionRemove(comboboxEl,comboboxId) {
    //console.log("treeSelectAdditionalJsActionRemove: comboboxId="+comboboxId);
    //calllogTreeSelectRemove(comboboxEl,comboboxId);

    //1) check all comboboxId sinblings: find if any '.formnode-holder' visible data-formnodeholderid > comboboxId
    calllogHideAllSiblings(comboboxId);

    //2) disable this form node holder
    calllogDisabledEnabledFormNode('disable',comboboxId);

    return;
}

function calllogHideAllSiblings( comboboxId ) {

    //var formNodeEl = calllogGetFormNodeElement(comboboxId);
    //if( !formNodeEl ) {
    //    console.log("formNodeEl not found =" + formNodeEl);
    //    return null;
    //}
    //console.log("formNodeEl.parent():");
    //console.log(formNodeEl.parent());
    //var visibleFormNodeElements = formNodeEl.parent().find('.formnode-holder:visible');
    //console.log("0 visibleFormNodeElements.length=" + visibleFormNodeElements.length);

    var visibleFormNodeElements = $('.formnode-holder:visible');
    //console.log("visibleFormNodeElements.length=" + visibleFormNodeElements.length);

    visibleFormNodeElements.each( function() {
        var thisFormNodeId = $(this).data("formnodeholderid");
        //console.log("sibling thisFormNodeId=" + thisFormNodeId + " ?= " + comboboxId);
        if( parseInt(thisFormNodeId) > parseInt(comboboxId) ) {
            //console.log("hide sibling thisFormNodeId=" + thisFormNodeId);
            calllogDisabledEnabledFormNode('disable',thisFormNodeId);
        }
    });
}

//function calllogTreeSelectRemove(comboboxEl,comboboxId) {
//    //printF( comboboxEl, "0 combobox on remove:" );
//    var messageCategoryId = comboboxId;
//    //var messageCategoryId = comboboxEl.select2('val');
//    console.log("remove messageCategoryId="+messageCategoryId);
//    //var messageCategoryId = comboboxEl.val();
//    //console.log("01 remove messageCategoryId="+messageCategoryId);
//    calllogDisabledEnabledFormNode('disable',messageCategoryId);
//
//    //hide all siblings after this combobox
//    var allNextSiblings = comboboxEl.closest('.row').nextAll();
//    allNextSiblings.each( function(){
//
//        //if( $(this).hasClass('active-tree-node') ) {
//            //printF($(this), "sibling combobox on remove:");
//            var messageCategoryId = $(this).find(".ajax-combobox-messageCategory").select2('val');
//            console.log("sibling remove messageCategoryId=" + messageCategoryId);
//
//            //hide all '#formnode-holder-'+messageCategoryId with the messageCategoryId > this messageCategoryId
//            calllogHideAllSiblings(messageCategoryId);
//        //}
//
//    });
//}
//function calllogHideAllSiblings( messageCategoryId ) {
//    //hide all '#formnode-holder-'+messageCategoryId with the messageCategoryId > this messageCategoryId
//    $(".formnode-holder").each( function() {
//
//        var thisMessageCategoryId = $(this).data("formnodeholderid");
//
//        console.log("compare: " + thisMessageCategoryId + " ?= " + messageCategoryId);
//        if( parseInt(thisMessageCategoryId) > parseInt(messageCategoryId) ) {
//            console.log("hide sibling thisMessageCategoryId=" + thisMessageCategoryId);
//            calllogDisabledEnabledFormNode('disable',thisMessageCategoryId);
//        }
//
//    });
//}

function calllogDisabledEnabledFormNode( disableEnable, messageCategoryId ) {

    var identifier = _holderName+"-"+messageCategoryId;
    var data = _formnode[identifier];

    if( !data ) {
        //console.log("calllogDisabledEnabledFormNode: data is null");
        return null;
    }

    var sectionFormNodeId = null;

    for( var index = 0; index < data.length; ++index ) {

        //var formNodeHolderId = data[index]['formNodeHolderId'];
        var parentFormNodeId = data[index]['parentFormNodeId'];
        var formNodeId = data[index]['formNodeId'];
        //var formNodeHtml = data[index]['formNodeHtml'];
        var simpleFormNode = data[index]['simpleFormNode'];

        if( simpleFormNode ) {
            if( parentFormNodeId && disableEnable == 'enable' ) {
                calllogDisabledEnabledSingleFormNode(disableEnable, parentFormNodeId);
            }
            calllogDisabledEnabledSingleFormNode(disableEnable, formNodeId);
        } else {
            sectionFormNodeId = formNodeId;
        }
    }

    //disable fieldNode Section if no simple fields are visible under this section
    if( sectionFormNodeId && disableEnable == 'disable' ) {
        var sectionFormNodeEl = calllogGetFormNodeElement(sectionFormNodeId);
        if( !sectionFormNodeEl ) {
            //console.log("sectionFormNodeEl not found =" + sectionFormNodeEl);
            return null;
        }
        var visibleSiblings = sectionFormNodeEl.find('.formnode-holder:visible');
        //console.log("visibleSiblings.length=" + visibleSiblings.length);
        if( visibleSiblings.length == 0 ) {
            //console.log("disable section sectionFormNodeId=" + sectionFormNodeId);
            calllogDisabledEnabledSingleFormNode(disableEnable, sectionFormNodeId);
        }
    }

    //enable parent

}

function calllogDisabledEnabledSingleFormNode( disableEnable, formNodeId ) {

    var formNodeEl = calllogGetFormNodeElement(formNodeId);
    if( !formNodeEl ) {
        return null;
    }

    if( disableEnable == 'disable' ) {
        //printF(formNodeEl,"disable:");
        formNodeEl.addClass("formnode-holder-disabled");
        formNodeEl.hide();

        //siblings
        //formNodeEl.each(function(){
        //    var siblings = $(this).find('.formnode-holder');
        //    siblings.addClass("formnode-holder-disabled");
        //    siblings.hide();
        //});

    } else {
        //printF(formNodeEl,"enable:");
        formNodeEl.show();
        formNodeEl.removeClass("formnode-holder-disabled");
    }

    return formNodeEl;
}

function calllogGetFormNodeElement( formNodeId ) {
    var formNodeElId = "formnode-"+formNodeId;
    //var formNodeEl = document.getElementById(formNodeElId);
    formNodeElId = "."+formNodeElId;
    //console.log("calllogGetFormNodeElement: find by formNodeId="+formNodeElId);
    var formNodeEl = $(formNodeElId);

    if( formNodeEl.length ) {
        return $(formNodeEl);
    } else {
        //console.log("calllogGetFormNodeElement: formNodeEl not found by formNodeElId="+formNodeElId);
        return null;
    }

    return null;
}



//formnode[arraysection][90][node][91]
function formNodeAddSameSection( btn, formNodeId ) {
    console.log('add form node section: formNodeId='+formNodeId);

    var maxCounter = 0;
    //get next counter by class="formnode-arraysection-holder-{{ formNode.id }}"
    $('.formnode-arraysection-holder-'+formNodeId).each(function(){
        var sectionid = $(this).data("sectionid");
        sectionid = parseInt(sectionid);
        //console.log('sectionid='+sectionid);
        if( sectionid > maxCounter ) {
            maxCounter = sectionid;
        }
    });
    var nextCounter = maxCounter + 1;
    console.log('nextCounter='+nextCounter);

    //var targetSection = $(".formnode-arraysection-holder-"+formNodeId).last();

    var targetSection = $(btn).closest('.formnode-arraysection-holder');
    //console.log(targetSection);

    //destroy select2
    //var selectEls = targetSection.find(".combobox");
    //console.log('selectEls.length='+selectEls.length);
    targetSection.find(".combobox").select2("destroy");

    //return;

    //var clonedSection = targetSection.clone();
    //clonedSection.find(".combobox").select2("destroy");
    //clonedSection.children("select").select2("destroy");
    var sectionHtml = targetSection.html();
    //console.log("sectionHtml="+sectionHtml);

    if( targetSection ) {

        //sectionHtml = sectionHtml.replace("formnode-arraysection-holder-" + maxCounter, "formnode-arraysection-holder-" + nextCounter);

        //replace "formnode[90][0][91]" by next counter "formnode[90][1][91]"
        sectionHtml = formnodeReplaceIndexByName(sectionHtml, 'arraysectioncount', nextCounter);

        //var sectionUniqueClass = "formnode-arraysection-holder-" + formNodeId + "-" + nextCounter;

        sectionHtml = '<div id="formnode-arraysection-holder-' + formNodeId + '" class="formnode-arraysection-holder formnode-arraysection-holder-' + formNodeId + ' '
            + '" data-sectionid="'
            + nextCounter + '">'
            + sectionHtml + '</div>';

        //prepend as the last element formnode-holder-90
        var attachEl = $(btn).closest('#formnode-' + formNodeId);

        //attachEl.append(sectionHtml);

        //pressing [+] should insert the empty section immediately below the section whose [+] was pressed (not "always as the last new section")
        //var thisSection = $(btn).closest('.formnode-arraysection-holder');
        //console.log(thisSection);
        targetSection.after(sectionHtml);

        //init appended element
        var appendedEl = attachEl.find("[data-sectionid='" + nextCounter + "']");
        //console.log(appendedEl);
        regularCombobox(appendedEl);
        initDatepicker(appendedEl);
        expandTextarea(appendedEl);

        //init again select2 in targetSection
        regularCombobox(targetSection);

        //show remove button
        if( attachEl.find('.formnode-remove-section').length > 1  ) {
            $('.formnode-remove-section').show();
        } else {
            $('.formnode-remove-section').hide();
        }
    }

}

//"arraysectioncount",3: formnode[90][arraysectioncount][2][node][91] => formnode[90][arraysectioncount][3][node][91]
function formnodeReplaceIndexByName_OLD( input, fieldname, index ) {
    //replace all occurrence [arraysectioncount][2] by [arraysectioncount][3]

    //http://jsfiddle.net/gz2tX/44/
    //var fieldname = 'arraysectioncount';
    //var index = '333';
    ////var seacrh = new RegExp(/\[arraysectioncount\]\[[0-9]\]/, 'g');
    //
    //var str = /\[arraysectioncount\]\[[0-9]\]/;
    ////var str = "["+fieldname+"]\[[0-9]]";
    //console.log("str="+str);
    //var seacrh = new RegExp(str, 'g');
    ////var seacrh = new RegExp(/\[arraysectioncount\]\[[0-9]\]/, 'g');
    //return input.replace(seacrh, '['+fieldname+']['+index+']');

    //var seacrh = new RegExp(/\[arraysectioncount\]\[[0-9]\]/, 'g'); //works

    //replace [arraysectioncount][index]
    var searchStr = /\[arraysectioncount\]\[[0-9]\]/;
    var seacrh = new RegExp(searchStr, 'g');
    input = input.replace(seacrh, '[arraysectioncount]['+index+']');

    //replace arraysectioncount-index
    var searchStr2 = /arraysectioncount-[0-9]/;
    var seacrh2 = new RegExp(searchStr2, 'g');
    input = input.replace(seacrh2, 'arraysectioncount-'+index);

    return input;
}
function formnodeReplaceIndexByName( input, fieldname, index ) {
    input = formnodeReplaceIndexSeparator(input, fieldname, index, '][');
    input = formnodeReplaceIndexSeparator(input, fieldname, index, '_');
    return input;
    function formnodeReplaceIndexSeparator(input, fieldname, index, separator) {
        var arrSecIndex = fromnodeGetSectionarrayIndex(input,separator,fieldname);
        console.log('arrSecIndex='+arrSecIndex);

        if( separator == '][') {
            // \[        // FIND   left bracket (literal) '['
            // \]  // MATCH  right bracket (literal) ']'
            var searchStr = /arraysectioncount\]\[[^\]]*?\]\[/;
        } else {
            var searchStr = fieldname + separator + arrSecIndex + separator;
        }
        console.log('searchStr='+searchStr);

        //replace the last index
        var newArrSecIndex = fromnodeReplaceSectionarrayIndex(arrSecIndex,index);

        var replaceStr = fieldname + separator + newArrSecIndex + separator;
        console.log('replaceStr='+replaceStr);

        var seacrh = new RegExp(searchStr, 'g');
        //var seacrh = searchStr;

        input = input.replace(seacrh, replaceStr);

        return input;
    }
    function fromnodeGetSectionarrayIndex(input,separator,fieldname) {
        var res = input.split(separator);
        for( var i=0; i < res.length; i++ ) {
            if( res[i] == fieldname ) {
                return res[i+1];
            }
        }
        return 0;
    }
    function fromnodeReplaceSectionarrayIndex(index,newIndex) {
        if( index.indexOf('-') !== -1 ) {
            var res = index.split('-');
            var lastIndex = res[res.length];
            console.log('lastIndex='+lastIndex);
            var newIndexArr = [];
            for( var i=0; i < res.length; i++ ) {
                if( i == res.length ) {
                    newIndexArr.push(newIndex);
                } else {
                    newIndexArr.push(res[i]);
                }
                newIndex = newIndexArr.join('-');
            }
        }
        return newIndex;
    }
    //document.write(foo('formnode[90][arraysectioncount][1-2][node][91] <br> formnode[90][arraysectioncount][0-2][node][92]','][') + '<br><br>');
    //document.write(foo('formnode_90_arraysectioncount_1-2_node_91','_') + '<br>');
}


function formNodeRemoveSection( btn, formNodeId ) {
    console.log("remove "+formNodeId);

    var attachEl = $(btn).closest('#formnode-' + formNodeId);

    //formnode-arraysection-holder-90
    $(btn).closest('.formnode-arraysection-holder').remove();

    if( attachEl.find('.formnode-remove-section').length == 1  ) {
        attachEl.find('.formnode-remove-section').hide();
    }
}


