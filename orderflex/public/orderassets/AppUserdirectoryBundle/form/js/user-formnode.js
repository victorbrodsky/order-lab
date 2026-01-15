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
 * Created by ch3 on 12/16/2016.
 */

var _holderNamespace = "App\\OrderformBundle\\Entity";
var _holderName = "MessageCategory";
var _formnode = [];
var _saprefix = "fffsa"; //section array prefix flag. Must be the same as in getArraySectionPrefix()

var _formnodeProcessing = false;
var _TIMEOUT = 50; // waitfor test rate [msec]
//var _holderLevel = 0; //MessageCategory level
//var _maxHolderLevel = 4; //max MessageCategory level

function treeSelectAdditionalJsAction(comboboxEl) {
    //printF( comboboxEl, "treeSelect AdditionalJsAction: combobox on change:" );

    //don't run this function if the div where to put the results does not exists in the page.
    if( $("#form-node-holder").length == 0 ) {
        return;
    }

    if( !comboboxEl.hasClass("ajax-combobox-messageCategory") ) {
        //console.log('this combobox is not message category');
        return;
    }

    var messageCategoryId = null;
    var thisData = comboboxEl.select2('data');
    if( thisData ) {
        messageCategoryId = thisData.id;
    }
    //console.log("treeSelect AdditionalJsAction: messageCategoryId="+messageCategoryId);

    if( typeof messageCategoryId === 'undefined' || !messageCategoryId ) {
        //console.log("return: messageCategoryId does not exists: "+messageCategoryId);
        return;
    }

    var formnodeTopHolderId = $('#formnodeTopHolderId').val();
    //console.log('formnodeTopHolderId='+formnodeTopHolderId);

    //don't run this function if formnodetrigger is 0
    //console.log('formnodetrigger='+thisData.text+" ("+messageCategoryId+")");
    if( $('#formnodetrigger') ) {
        if( $('#formnodetrigger').val() == '0' || $('#formnodetrigger').val() == 0 ) {
            //console.log('formnodetrigger is false=' + $('#formnodetrigger').val());
            //console.log('formnodetrigger='+thisData.text+" ("+messageCategoryId+")");
            //if( messageCategoryId == 31 || messageCategoryId == '31' ) {
            if( messageCategoryId == formnodeTopHolderId ) {
                //if messageCategory is a top one "Encounter Note (31)", then trigger a top to bottom combobox processing
                $('#formnodetrigger').val(1);
                //process comboboxEls with associated formnodes from top to bottom
                processFormNodeHoldersTopToBottom();
            }
            return;
        }
    }

    //testing: do nothing if the fields were populated by controller
    //var holderId = "formnode-holder-"+messageCategoryId;
    //var holderEl = document.getElementById(holderId);
    //if( holderEl && !(identifier in _formnode) ) {
    //    return;
    //}

    //var holderNamespace = "App\\OrderformBundle\\Entity";
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
    var _entityNamespace = $('#entityNamespace').val();   //"App\\OrderformBundle\\Entity";
    var _entityName = $('#entityName').val();             //"Message";
    var _entityId = $('#entityId').val();                 //"Message ID";
    console.log("_entityNamespace="+_entityNamespace);
    console.log("_entityName="+_entityName);
    console.log("_entityId="+_entityId);

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
    _formnodeProcessing = true;

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
        _formnode[identifier] = null;
        alert("Error getting field(s) for "+_holderName+" "+messageCategoryId);
    }).done(function() {
        //console.log("update patient title done");
        _formnodeProcessing = false;
    });
}

//process comboboxEl from top to bottom
function processFormNodeHoldersTopToBottom() {
    var messageHolder = $('.ajax-combobox-messageCategory').closest('.composite-tree-holder');
    var messageCategories = messageHolder.find('.treenode');
    //console.log('messageCategories len='+messageCategories.length);
    messageCategories.each( function(e){

        var comboboxEl =$(this).find('input.ajax-combobox-messageCategory');
        var comboboxText = null;
        if( comboboxEl && comboboxEl.select2('data') ) {
            comboboxText = comboboxEl.select2('data').text;
            //console.log('comboboxEl=' + comboboxText);
        } else {
            //console.log('comboboxEl is NULL');
        }

        //treeSelectAdditionalJsAction(comboboxEl);

        //wait until _formnodeProcessing is false
        // Wait until idle (busy must be false)
        //var sourceStr = null;
        var sourceStr = 'continue next: '+comboboxText;
        waitfor(_isFormnodeProcessing, false, _TIMEOUT, 0, sourceStr, function() {
            treeSelectAdditionalJsAction(comboboxEl);
        });

    });
}
// Test a flag
function _isFormnodeProcessing() {
    return _formnodeProcessing;
}

function calllogAppendFormNodes( data ) {
    for( var index = 0; index < data.length; ++index ) {

        var formNodeHolderId = data[index]['formNodeHolderId'];
        var parentFormNodeId = data[index]['parentFormNodeId'];
        var formNodeId = data[index]['formNodeId'];
        var formNodeHtml = data[index]['formNodeHtml'];
        var arraySectionCount = data[index]['arraySectionCount'];

        var appendedEl = calllogAppendElement(formNodeHolderId,parentFormNodeId,formNodeId,formNodeHtml,arraySectionCount);

        if(
            data[index]['formNodeObjectType'] == "Form Field - Dropdown Menu" ||
            data[index]['formNodeObjectType'] == "Form Field - Dropdown Menu - Allow Multiple Selections" ||
            data[index]['formNodeObjectType'] == "Form Field - Month" ||
            data[index]['formNodeObjectType'] == "Form Field - Day of the Week" ||
            data[index]['formNodeObjectType'] == "Form Field - Date"
        ) {
            regularCombobox($('#formnode-'+formNodeId));
        }

        //Allow New Entries: for select2 v 3.* select is represented by hidden input field
        if(
            data[index]['formNodeObjectType'] == "Form Field - Dropdown Menu - Allow New Entries" ||
            data[index]['formNodeObjectType'] == "Form Field - Dropdown Menu - Allow Multiple Selections - Allow New Entries"
        ) {
            var targetCombobox = $('#formnode-'+formNodeId).find('.combobox');
            var dataOptions = targetCombobox.data("options");
            var dataPlaceholder = targetCombobox.data("placeholder");
            if( !dataPlaceholder ) {
                dataPlaceholder = "Select an option or type in a new value";
            }
            var multiple = false;
            if( data[index]['formNodeObjectType'] == "Form Field - Dropdown Menu - Allow Multiple Selections - Allow New Entries" ) {
                multiple = true;
            }
            populateSelectCombobox(targetCombobox, dataOptions, dataPlaceholder, multiple);
        }

        if(
            data[index]['formNodeObjectType'] == "Form Field - Full Date" ||
            data[index]['formNodeObjectType'] == "Form Field - Full Date and Time"
        ) {
            initDatepicker($('#formnode-'+formNodeId));
        }

        if(
            data[index]['formNodeObjectType'] == "Form Field - Time, with Time Zone" ||
            data[index]['formNodeObjectType'] == "Form Field - Full Date and Time, with Time Zone"
        ) {
            initDatepicker($('#formnode-'+formNodeId));
            regularCombobox($('#formnode-'+formNodeId));
        }

        if( data[index]['formNodeObjectType'] == "Form Field - Year" ) {
            initDatepicker($('#formnode-'+formNodeId));
            //$('#formnode-'+formNodeId).find('input.datepicker').datepicker( {
            //    autoclose: true,
            //    format: " yyyy",
            //    viewMode: "years",
            //    minViewMode: "years",
            //    orientation: 'auto'
            //});
        }

        if(
            data[index]['formNodeObjectType'] == "Form Field - Free Text" ||
            data[index]['formNodeObjectType'] == "Form Field - Free Text, RTF"
        ) {
            expandTextarea($('#formnode-'+formNodeId));
        }

        if(
            data[index]['formNodeObjectType'] == "Form Field - Free Text, HTML"
        ) {
            // expandTextarea($('#formnode-'+formNodeId));
            //$('.summernote').summernote();
            //$('#oleg_userdirectorybundle_formnode_'+formNodeId).summernote();
            richTextInit(formNodeId);
        }

        //https://jsfiddle.net/7vddjcwu/29/
        //allow only digits in this field and save the value to the database as a positive integer
        if( data[index]['formNodeObjectType'] == 'Form Field - Free Text, Single Line, Numeric, Unsigned Positive Integer' ) {
            $('#formnode-'+formNodeId).find('input').inputmask("Regex", {regex: "^[0-9]{0,30}?$"});
        }

        //allow only digits as well as "+" and "-" signs in this field and save the value to the database as a signed integer
        if( data[index]['formNodeObjectType'] == 'Form Field - Free Text, Single Line, Numeric, Signed Integer' ) {
            $('#formnode-'+formNodeId).find('input').inputmask("Regex", {regex: "[-+]?[0-9]{0,30}"});
        }

        //allow only digits in this field as well as ".", "+", and "-" signs and save the value to the database as a floating point number
        if( data[index]['formNodeObjectType'] == 'Form Field - Free Text, Single Line, Numeric, Signed Float' ) {
            //$('#formnode-'+formNodeId).find('input').inputmask("Regex", {regex: "[-+.]?[0-9]{1,30}(\\.\\d{1,30})?$"});
            $('#formnode-'+formNodeId).find('input').inputmask("Regex", {regex: "[+-]?([0-9]*[.])?[0-9]{0,30}"});
        }

        if( appendedEl ) {
            formNodeCCICalculationListener(appendedEl,'.cci-pre-transfusion-platelet-count');
            formNodeCCICalculationListener(appendedEl,'.cci-post-transfusion-platelet-count');
            formNodeCCICalculationListener(appendedEl,'.cci-bsa');
            formNodeCCICalculationListener(appendedEl,'.cci-unit-platelet-count');
        }

    }//for
}
//find the latest parent formnode holder element by parentFormNodeId id
function calllogAppendElement( formNodeHolderId, parentFormNodeId, formNodeId, formNodeHtml, arraySectionCount ) {

    //console.log("calllog AppendElement: formNodeHolderId="+formNodeHolderId+"; parentFormNodeId="+parentFormNodeId+"; formNodeId="+formNodeId+"; arraySectionCount="+arraySectionCount);

    //check if parent formnode exists and append this formnode to the parent formnode
    var parentId = "formnode-"+parentFormNodeId;
    var parentEl = document.getElementById(parentId);
    //calllogGetFormNodeElement

    //check if this element does not exist
    var formNodeElId = "formnode-"+formNodeId;
    var formNodeEl = document.getElementById(formNodeElId);
    if( formNodeEl ) {

        //TODO: check if arraySectionCount is equal to data-sectionid
        var sectionid = $(formNodeEl).data("sectionid");
        //console.log("formNodeElId: sectionid=" + sectionid);

        if( arraySectionCount == null || arraySectionCount == sectionid ) {
            //console.log("EXIT: formnode-holder-" + formNodeId + " already exists!");
            //calllogDisabledEnabledFormNode('enable',formNodeHolderId);

            if (parentEl) {
                //if already exists, make sure that it is visible
                calllogDisabledEnabledSingleFormNode('enable', parentFormNodeId);
            }

            //enable formnode
            calllogDisabledEnabledSingleFormNode('enable', formNodeId);

            return null;
        }
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
    //for show use reverse array (don't use it for top to bottom combobox  processing)
    //var formcycle = $('#formcycle').val();
    //if( formcycle == 'show' ) {
    //    var attachType = 'prepend';
    //}
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

function richTextInit(formNodeId) {
    //https://summernote.org/
    //https://summernote.org/getting-started/#simple-example
    //https://stackoverflow.com/questions/56015767/how-to-set-summernote-set-image-to-take-full-width-always

    //$('.summernote').summernote();

    //Fontstyle: Bold, Italic, Underline, Superscript, Subscript,
    // Color, Forecolor, Backcolor, Clear font style,
    // Paragraph Style: ol (toggle ordered list), ul (toggle unordered list),
    // Insert: table,
    // Misc: Undo, Redo, Full Screen, Codeview .
    // Do not show the other buttons.
    $('#oleg_userdirectorybundle_formnode_'+formNodeId).summernote({
        //stripTags: true,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['superscript', 'subscript']], //'strikethrough', 
            //['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol']],
            //['height', ['height']],
            //['insert', ['link', 'picture', 'video']],
            ['table', ['table']],
            ['view', ['fullscreen', 'codeview', 'undo', 'redo', 'help']]
        ],
        callbacks: {
            //use this for v0.7.0+: https://stackoverflow.com/questions/30993836/paste-content-as-plain-text-in-summernote-editor/31019586#31019586
            onPaste: function (e) {
                var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                e.preventDefault();
                document.execCommand('insertText', false, bufferText);
            }
        }
    });

    //$('#oleg_userdirectorybundle_formnode_'+formNodeId).find(".note-editable").css({"text-align": "justify"});
    $(".note-editable").css({"text-align": "justify", "min-height": "140px"});
}

function treeSelectAdditionalJsActionRemove(comboboxEl,comboboxId) {
    //console.log("treeSelect AdditionalJsActionRemove: comboboxId="+comboboxId);
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

    for( var index = 0; index < data.length; ++index ) {

        //var formNodeHolderId = data[index]['formNodeHolderId'];
        var parentFormNodeId = data[index]['parentFormNodeId'];
        var formNodeId = data[index]['formNodeId'];
        //var formNodeHtml = data[index]['formNodeHtml'];
        var simpleFormNode = data[index]['simpleFormNode']; //real field is a simple (single) field: simpleFormNode=true, section: simpleFormNode=false

        if( simpleFormNode ) {
            if( parentFormNodeId && disableEnable == 'enable' ) {
                calllogDisabledEnabledSingleFormNode(disableEnable, parentFormNodeId);
            }
            calllogDisabledEnabledSingleFormNode(disableEnable, formNodeId);

            formnodeDisableEnableParentSections(formNodeId);
        } else {
            //section will be enabled/disabled by the children form field
        }

    }

}
function formnodeDisableEnableParentSections( formNodeId ) {
    var formNodeEl = calllogGetFormNodeElement(formNodeId);
    //traverse all parent with class .panel-body until #form-node-holder
    formNodeEl.parentsUntil("#form-node-holder",".panel-body").each( function(){
        formnodeDisableEnableSingleSection($(this)); //$(this) - panel-body element
    });
}
function formnodeDisableEnableSingleSection(panelBodyEl) {
    var siblings = panelBodyEl.find('.formnode-holder:not(.formnode-formtype-section)');
    var disabledSiblings = panelBodyEl.find('.formnode-holder-disabled');
    //console.log(panelBodyEl.parent().attr('id')+": Siblings length=" + siblings.length + " ?= " + disabledSiblings.length);
    if( siblings.length == disabledSiblings.length ) {
        //console.log("disable section =" + panelBodyEl.parent().attr('id'));
        //console.log("disable section");
        panelBodyEl.closest('.formnode-holder').hide();
    } else {
        //console.log("enable section =" + panelBodyEl.parent().attr('id'));
        //console.log("enable section");
        panelBodyEl.closest('.formnode-holder').show();
    }
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





///////////////////////////// Array Section Functions. Use _saprefix saprefix_0-0-1_saprefix //////////////////////

//formnode[arraysection][90][node][91]
function formNodeAddSameSection( btn, formNodeCleanId, formNodeId ) {
    //console.log('########## add form node section: formNodeCleanId='+formNodeCleanId+'; formNodeId='+formNodeId+" ##########");

    //get level of this array section using formNodeId's '-' count: 100=>0, 101_0-0=>1, 214_0-0-0=>2
    var sectionLevel = (formNodeId.match(/-/g) || []).length;
    //console.log('sectionLevel='+sectionLevel);

    var maxCounter = 0;
    //get next counter by class="formnode-arraysection-holder-{{ formNode.id }}"
    $('.formnode-arraysection-holder-id-'+formNodeCleanId).each(function(){
        var sectionidFull = $(this).data("sectionid"); //fffsa_0_1_fffsa
        //console.log('sectionidFull='+sectionidFull);
        var sectionid = formnodeGetLastSectionArrayIndex(sectionidFull,'-',sectionLevel); //1 if sectionLevel=1
        sectionid = parseInt(sectionid);
        //console.log('sectionid='+sectionid);
        if( sectionid > maxCounter ) {
            maxCounter = sectionid;
        }
    });
    var nextCounter = maxCounter + 1;
    //console.log('nextCounter='+nextCounter);

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
        //sectionHtml = formnodeReplaceIndexByName(sectionHtml, 'arraysectioncount', nextCounter, sectionLevel);
        sectionHtml = formnodeReplaceAllIndex(sectionHtml, nextCounter, sectionLevel);

        //var sectionUniqueClass = "formnode-arraysection-holder-" + formNodeId + "-" + nextCounter;

        //replace the last index
        var thisArraySectionIndex = targetSection.data("sectionid");
        //console.log('thisArraySectionIndex='+thisArraySectionIndex);

        var newArrSecIndex = formnodeReplaceSectionarrayIndex(thisArraySectionIndex,nextCounter,sectionLevel);
        //console.log('newArrSecIndex='+newArrSecIndex);

        //fffsa_0_1_fffsa => 317_fffsa_0_1_fffsa
        var newFormNodeId = formNodeCleanId + '_' + newArrSecIndex;
        //console.log('newFormNodeId='+newFormNodeId);

        sectionHtml = '<div id="formnode-arraysection-holder-' + newFormNodeId + '"'
            + ' class="formnode-arraysection-holder formnode-arraysection-holder-' + newFormNodeId + ' formnode-arraysection-holder-id-' + formNodeCleanId + '"'
            + ' data-sectionid="'
            + newArrSecIndex + '">'
            + sectionHtml + '</div>';

        //prepend as the last element formnode-holder-90
        var attachEl = $(btn).closest('#formnode-' + formNodeId);

        //attachEl.append(sectionHtml);

        //pressing [+] should insert the empty section immediately below the section whose [+] was pressed (not "always as the last new section")
        //var thisSection = $(btn).closest('.formnode-arraysection-holder');
        //console.log(thisSection);
        //targetSection.after(sectionHtml);
        var appendedEl = $(sectionHtml).insertAfter(targetSection);

        //init appended element
        //var appendedEl = attachEl.find("[data-sectionid='" + newArrSecIndex + "']");
        //var appendedEl = attachEl.find("#formnode-arraysection-holder-"+newFormNodeId);

        //console.log("appendedEl:");
        //console.log(appendedEl);
        regularCombobox(appendedEl);
        initDatepicker(appendedEl);
        expandTextarea(appendedEl);

        //init again select2 in targetSection
        regularCombobox(targetSection);

        //show remove button
        //formnode-arraysection-holder-211
        //formnode-remove-section-211
        formNodeProcessRemoveSectionBtn(formNodeId);
        //var sections = $('.formnode-remove-section-'+formNodeId);
        //console.log('sections='+sections.length);
        //if( sections.length > 1  ) {
        //    sections.show();
        //} else {
        //    sections.hide();
        //}
        //if( attachEl.find('.formnode-remove-section').length > 1  ) {
        //    attachEl.find('.formnode-remove-section').show();
        //} else {
        //    attachEl.find('.formnode-remove-section').hide();
        //}

        //CCI
        if( appendedEl ) {
            formNodeCCICalculationListener(appendedEl, '.cci-pre-transfusion-platelet-count');
            formNodeCCICalculationListener(appendedEl, '.cci-post-transfusion-platelet-count');
        }

    }

}

//"arraysectioncount",3: formnode[90][arraysectioncount][2][node][91] => formnode[90][arraysectioncount][3][node][91]
//function formnodeReplaceIndexByName_OLD( input, fieldname, index ) {
//    //replace all occurrence [arraysectioncount][2] by [arraysectioncount][3]
//
//    //http://jsfiddle.net/gz2tX/44/
//    //http://jsfiddle.net/gz2tX/49/
//    //http://jsfiddle.net/gz2tX/50/
//      http://jsfiddle.net/gz2tX/52/
//
//    //var fieldname = 'arraysectioncount';
//    //var index = '333';
//    ////var seacrh = new RegExp(/\[arraysectioncount\]\[[0-9]\]/, 'g');
//    //
//    //var str = /\[arraysectioncount\]\[[0-9]\]/;
//    ////var str = "["+fieldname+"]\[[0-9]]";
//    //console.log("str="+str);
//    //var seacrh = new RegExp(str, 'g');
//    ////var seacrh = new RegExp(/\[arraysectioncount\]\[[0-9]\]/, 'g');
//    //return input.replace(seacrh, '['+fieldname+']['+index+']');
//
//    //var seacrh = new RegExp(/\[arraysectioncount\]\[[0-9]\]/, 'g'); //works
//
//    //replace [arraysectioncount][index]
//    var searchStr = /\[arraysectioncount\]\[[0-9]\]/;
//    var seacrh = new RegExp(searchStr, 'g');
//    input = input.replace(seacrh, '[arraysectioncount]['+index+']');
//
//    //replace arraysectioncount-index
//    var searchStr2 = /arraysectioncount-[0-9]/;
//    var seacrh2 = new RegExp(searchStr2, 'g');
//    input = input.replace(seacrh2, 'arraysectioncount-'+index);
//
//    return input;
//}
function formnodeReplaceIndexByName( input, fieldname, index, sectionLevel ) {
    input = formnodeReplaceIndexSeparator(input, fieldname, index, '][', sectionLevel);
    input = formnodeReplaceIndexSeparator(input, fieldname, index, '_', sectionLevel);
    return input;
    function formnodeReplaceIndexSeparator(input, fieldname, index, separator, sectionLevel) {
        var arrSecIndex = formnodeGetSectionarrayIndex(input,separator,fieldname);
        //console.log('arrSecIndex='+arrSecIndex);

        if( separator == '][') {
            // \[        // FIND   left bracket (literal) '['
            // \]  // MATCH  right bracket (literal) ']'
            var searchStr = /arraysectioncount\]\[[^\]]*?\]\[/;
        } else {
            var searchStr = fieldname + separator + arrSecIndex + separator;
        }
        //console.log('searchStr='+searchStr);

        //replace the index at the sectionLevel
        var newArrSecIndex = formnodeReplaceSectionarrayIndex(arrSecIndex,index,sectionLevel);

        var replaceStr = fieldname + separator + newArrSecIndex + separator;
        //console.log('replaceStr='+replaceStr);

        var seacrh = new RegExp(searchStr, 'g');
        //var seacrh = searchStr;

        input = input.replace(seacrh, replaceStr);

        return input;
    }
    //document.write(foo('formnode[90][arraysectioncount][1-2][node][91] <br> formnode[90][arraysectioncount][0-2][node][92]','][') + '<br><br>');
    //document.write(foo('formnode_90_arraysectioncount_1-2_node_91','_') + '<br>');
}

//replace all occurence of saprefix_1-2_saprefix by saprefix_1-3_saprefix (index=3, sectionLevel=2)
//http://jsfiddle.net/gz2tX/58/
function formnodeReplaceAllIndex( input, index, sectionLevel ) {
    //console.log('index='+index+'; sectionLevel='+sectionLevel );

    //var fieldname = 'arraysectioncount';
    //var separator = '][';
    //var arrSecIndex = formnodeGetSectionarrayIndex(input,separator,fieldname);
    //console.log('arrSecIndex='+arrSecIndex );

    //var matchRes = input.match( _saprefix+"_(.*?)_"+_saprefix );

    //1) find all occurence of saprefix_*_saprefix
    var searchStr = _saprefix+"_(.*?)_"+_saprefix;
    var seacrh = new RegExp(searchStr, 'g');
    var matchRes = input.match( seacrh );

    //2) replace all
    var matchArr = [];
    for( var i = 0; i < matchRes.length; i++ ) {
        var match = matchRes[i];
        if( matchArr.indexOf(match) == -1 ) {
            matchArr.push(match);
            //console.log('match='+match);

            //2a) get new Index: saprefix_1-2_saprefix by saprefix_1-3_saprefix
            var cleanIndex = getCleanSectionArrayIndex(match); //0-1-1
            var newIndex = formnodeReplaceSectionarrayIndex(cleanIndex,index,sectionLevel);

            //2b) replace all in input
            var seacrh = new RegExp(match, 'g');
            input = input.replace(seacrh, newIndex);
        }
    }

    //replaceAll(input,);

    return input;
}

function formnodeGetSectionarrayIndex(input,separator,fieldname) {
    var res = input.split(separator);
    for( var i=0; i < res.length; i++ ) {
        if( res[i] == fieldname ) {
            return res[i+1];
        }
    }
    return 0;
}
//index=2-1, newIndex=2, output:2-2
//index=0-0-1, newIndex=1, sectionLevel=0 => 1-0-1
function formnodeReplaceSectionarrayIndex(index,newIndex,sectionLevel) {
    index = index + '';
    index = getCleanSectionArrayIndex(index); //saprefix_1-2_saprefix => 1-2
    if( index.indexOf('-') !== -1 ) {
        var res = index.split('-');
        //var lastIndex = res[res.length-1];
        //console.log('lastIndex='+lastIndex);
        var newIndexArr = [];
        //replace last index by newIndex
        for( var i=0; i < res.length; i++ ) {
            //console.log('i='+i+' ?= '+ sectionLevel );
            //if( i == res.length-1 ) {
            if( i == sectionLevel ) {
                newIndexArr.push(newIndex);
            } else {
                newIndexArr.push(res[i]);
            }
        }
        newIndex = newIndexArr.join('-');
    }
    newIndex = _saprefix + '_' + newIndex + '_' + _saprefix;
    return newIndex;
}
//input: saprefix_1-2_saprefix, output: 2
function formnodeGetLastSectionArrayIndex(index,separator,sectionLevel) {
    index = index + '';
    index = getCleanSectionArrayIndex(index); //saprefix_1-2_saprefix => 1-2
    var lastIndex = index;
    if( index.indexOf(separator) !== -1 ) {
        var res = index.split(separator);
        lastIndex = res[res.length - 1];
    }
    //console.log('lastIndex=' + lastIndex);
    return lastIndex;
}

function formNodeRemoveSection( btn, formNodeId ) {
    //console.log("remove "+formNodeId);

    //Are You sure you would like to delete this form section?
    if( confirm('Are you sure you would like to delete this form section?') ) {
        // Continue
    } else {
        // Do nothing!
        return;
    }

    //formnode-arraysection-holder-90
    $(btn).closest('.formnode-arraysection-holder').remove();

    formNodeProcessRemoveSectionBtn(formNodeId);
}

//remove all _saprefix: saprefix_0-1-2_saprefix => 0-1-2
function getCleanSectionArrayIndex(index) {
    return formNodeReplaceAll(index,_saprefix,'');
}
function formNodeReplaceAll(input,searchStr,replaceStr) {
    var seacrh = new RegExp(searchStr, 'g');
    input = input.replace(seacrh, replaceStr);
    input = input.replace(/_/g, replaceStr);
    return input;
}

function formNodeProcessRemoveSectionBtn( formNodeId ) {

    //formNodeId: 100_fffsa_0_fffsa => 100
    var formNodeCleanIdArr = formNodeId.split("_");
    if( formNodeCleanIdArr.length > 0 ) {
        var formNodeCleanId = formNodeCleanIdArr[0];
    } else {
        var formNodeCleanId = formNodeId;
    }
    var sections = $('.formnode-remove-section-id-'+formNodeCleanId);
    //console.log(formNodeCleanId+': sections='+sections.length);
    if( sections.length > 1  ) {
        sections.show();
    } else {
        sections.hide();
    }
}


function formNodeCCICalculationListener(appendedEl,targetName){
    var targetEl = appendedEl.find(targetName);
    //console.log("######### formNode CCICalculation Listener: len="+targetEl.length+" for target "+targetName);
    //console.log(appendedEl);
    //console.log(targetEl);
    //console.log("#########");
    if( targetEl.length > 0 ) {
        //console.log(appendedEl);
        //console.log(targetEl);
        //console.log("appendedElVal has class "+targetName);
        targetEl.on('input', function () {
            //var appendedElVal = $(this).val() // get the current value of the input field.
            //console.log("appendedElVal=" + appendedElVal);
            var thisFormNodesHolder= $(this).closest('.form-nodes-holder');
            formNodeCCICalculation(thisFormNodesHolder);
        });
    }
}
//https://bitbucket.org/weillcornellpathology/call-logbook-plan/issues/17/create-a-function-to-calculate-cci
//CCI = ((postPlateletCount - prePlateletCount ) * BodySurfaceArea) / number_of_Platelets_in_Unit
function formNodeCCICalculation(appendedEl) {
    //console.log('cci input changed');
    var result = appendedEl.find(".cci-result");
    if( result ) {
        var pre = appendedEl.find(".cci-pre-transfusion-platelet-count").val();
        var post = appendedEl.find(".cci-post-transfusion-platelet-count").val();
        var bsa = $(".cci-bsa").val();
        var count = $(".cci-unit-platelet-count").val();
        if( pre && post && bsa && count ) {
            //console.log('cci calculating...:' + pre + "; " + post + "; " + bsa + "; " + count);
            var resultValue = ((post - pre) * bsa) / count;
            //console.log('resultValue=' + resultValue);
            result.val(resultValue);
        }
    }
}
