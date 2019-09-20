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
 * Generic functions to add and remove collections
 *
 * Created by oli2002 on 8/21/14.
 */


function addNewObject(btn,classname,callback) {
//    var btnEl = $(btn);

    //console.log("classname="+classname);

    var holder = $('.'+classname+'-holder');

    var titles = holder.find('.'+classname);
    //console.log('titles='+titles.length);

    var newForm = getBaseTitleForm( classname );

    newForm = $(newForm);

    var lastcollHolder = titles.last();

    if( titles.length == 0 ) {
        var addedInst = $('.'+classname+'-holder').prepend(newForm);
    } else {
        var addedInst = lastcollHolder.after(newForm);
    }

    //printF(newForm,"added el:");
    //console.log(newForm);

    initBaseAdd(newForm,callback);
    processEmploymentStatusRemoveButtons(btn,'add');

}

function initBaseAdd(newForm,callback) {
    expandTextarea();
    regularCombobox();

    //tooltip
    //$(".element-with-tooltip").tooltip();
    initTooltips();

    initTreeSelect();

    initDatepicker(newForm);

    fieldInputMask(newForm);

    //init comboboxes
    getComboboxCompositetree(newForm);   //init composite tree for administrative and appointnment titles
    //getComboboxCommentType(newForm);
    getComboboxResidencyspecialty(newForm);

    //init generic comboboxes
    initAllComboboxGeneric(newForm);

    initFileUpload(newForm);

    confirmDeleteWithExpired(newForm);

    identifierTypeListener(newForm);

    researchLabListener(newForm);

    grantListener(newForm);

    degreeListener(newForm);

    listenerFellAppRank(newForm);

    if( callback ) {
        callback();
    }
}

//get input field only
function getBaseTitleForm( elclass ) {

    //console.log('elclass='+elclass);

    var dataholder = "#form-prototype-data"; //fixed data holder

    var holderClass = elclass+'-holder';
    //console.log('holderClass='+holderClass);

    var elementsHolder = $('.'+holderClass);

    //var elements = elementsHolder.find('.'+elclass);
    //console.log('elements='+elements.length);

    var identLowerCase = elclass.toLowerCase();

    //console.log("identLowerCase="+identLowerCase);

    var collectionHolder = $(dataholder);
    var prototype = collectionHolder.data('prototype-'+identLowerCase);
    //console.log("prototype="+prototype);

    //grant __documentContainers__ => 0
    prototype = prototype.replace("__documentContainers__", "0");

    //var newForm = prototype.replace(/__administrativetitles__/g, elements.length);

    var classArr = identLowerCase.split("-"); //user-fieldname
    //console.log("classArr[1]="+classArr[1]);

    var regex = new RegExp( '__' + classArr[1] + '__', 'g' );

    //var elementCount = elements.length;
    var elementCount = getNextElementCount(elementsHolder,elclass);
    //console.log("elementCount="+elementCount);

    var newForm = prototype.replace(regex, elementCount);

    //console.log("newForm="+newForm);
    return newForm;
}

function removeExistingObject(btn,classname,id) {

    var btnEl = $(btn);

    if( btnEl.hasClass('confirm-delete-with-expired') ) {
        return;
    }

    var r = confirm("Are you sure you want to remove this record?");
    if (r == true) {
        //for location only: check if this location is used by somewhere else
        if( objectIsDeleteable(btn,classname,id) == false ) {
            return;
        }
    } else {
        return;
    }

    processEmploymentStatusRemoveButtons(btn,'remove');

    var element = btnEl.closest('.'+classname);
    element.remove();
}

function objectIsDeleteable(btn,classname,id) {
    var btnEl = $(btn);
    var element = btnEl.closest('.'+classname);
    var idField = element.find('.user-object-id-field');
    var locationid = idField.val();

    if( !locationid || locationid == "" ) {
        //alert("Error: this location is new or invalid");
        return true;
    }
    //console.log('object id='+locationid);

    var removable = false;

    var url = getCommonBaseUrl("util/common/"+"location/delete/"+locationid,"employees");
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: false
    }).success(function(data) {
        if( data == 'ok' ) {
            //console.log('ok to delete');
            removable = true;
        } else {
            alert("This object is used by another objects");
        }

    });

    return removable;
}


//$(function() {
//    $('.checked').click(function(e) {
//        e.preventDefault();
//        var dialog = $('<p>Are you sure?</p>').dialog({
//            buttons: {
//                "Yes": function() {alert('you chose yes');},
//                "No":  function() {alert('you chose no');},
//                "Cancel":  function() {
//                    alert('you chose cancel');
//                    dialog.dialog('close');
//                }
//            }
//        });
//    });
//});

function confirmDeleteWithExpired( holder ) {

    var targetid = ".confirm-delete-with-expired";

    if( $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {

        targetid = holder.find(targetid);

        if( targetid.length == 0 )
            return;
    }

    var dialogStr = "<p>You are about to permanently delete this record. If this record is not erroneous, please mark it \"Expired\" as of today's date instead. " +
        "This will maintain a historical list of previously valid employment periods, titles, and research labs.</p>";

    $(targetid).click(function(e) {
        e.preventDefault();

        var btn = this;
        var btnEl = $(btn);
        var classname = 'user-collection-holder';
        var collectionHolder = btnEl.closest('.'+classname);

        var dialog = $( dialogStr ).dialog({
            modal: true,
            title: "Confirmation",
            open: function() {
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                "Mark as \"Expired\" as of today": function() {
                    //console.log('expired');
                    //place today's date into the "End" field of the element expired-end-date
                    var today = new Date();
                    var datefieldEl = collectionHolder.find('.user-expired-end-date');
                    datefieldEl.datepicker( 'setDate', today );
                    datefieldEl.datepicker( 'update');
                    dialog.dialog('close');
                },
                "Delete this erroneous record":  function() {
                    //console.log('delete');
                    //delete
                    var deleted = deleteObjectFromDB(btn);
                    if( deleted ) {
                        processEmploymentStatusRemoveButtons(btn,'remove');
                        collectionHolder.remove();
                    }
                    dialog.dialog('close');
                },
                "Cancel":  function() {
                    //console.log('cancel');
                    //do nothing
                    dialog.dialog('close');
                }
            }
        });
    });

}


function collapseObject( button ) {
    var buttonEl = $(button);
    //console.log(checkboxEl);

    var holder = buttonEl.closest('.panel');
    //console.log(holder);

    if( buttonEl.hasClass('active') ) {
        holder.find('.collapse-non-empty-enddate').hide();
    } else {
        holder.find('.collapse-non-empty-enddate').show();
    }
}


function getNextElementCount( holder, elclass ) {
    //console.log(holder);
    //console.log('elclass='+elclass);

    var elements = holder.find('.'+elclass);
    //console.log('elements count='+elements.length);

    var maxCount = 0;

    elements.each( function() {
        //console.log("this:");
        //console.log($(this));
        //find valid input field with valid id
        var inputEl = null;
        //var inputElements = $(this).find('input[type=text]').not("*[id^='s2id_']");
        //use hidden id input field to calculate id
        var inputElements = $(this).find('input[type=text],input[type=hidden]').not("*[id^='s2id_']");
        //console.log("inputElements count=" + inputElements.length);

        inputElements.each(function () {

            //console.log("inputElement:");
            //console.log($(this));

            var id = $(this).attr('id');
            //console.log("id=" + id);
            if (id) {
                var counter = getElementCounter($(this));
                if (counter) {
                    inputEl = $(this);
                    return;
                } else {
                    //console.log('no inputEl found');
                }
            }
        });

        //printF( inputEl, 'element:');
        var counter = getElementCounter(inputEl);
        //console.log("counter=" + counter + ", maxCount=" + maxCount);
        if (counter) {
            if (parseInt(counter) > parseInt(maxCount)) {
                maxCount = counter;
            }
        } else {
            maxCount++;
        }
//        if( counter == null ) {
//            //console.log("maxCount++ ="+maxCount);
//            maxCount++;
//        }
    });

    var elementCount = parseInt(maxCount) + 1;

    //console.log("elementCount="+elementCount);

    return elementCount;
}

function getElementCounter( element ) {

    if( !element ) {
        //console.log("Error: element is null");
        return null;
    }

    var id = element.attr('id');
    //console.log("ok id="+id);

    //  0           1           2       3          4
    //oleg_userdirectorybundle_user_publicComments_0
    //oleg_userdirectorybundle_user_credentials_identifiers_1_link
    //oleg_userdirectorybundle_roles_permissions_1_id

    var idArr = id.split("_");

    for( var i = 0; i < idArr.length; ++i ) {
        //console.log( "value="+idArr[i]+" integer?="+isInt(idArr[i]) );
        if( isInt(idArr[i]) ) {
            return idArr[i];
        }
    }

    //var bundleName = idArr[1];
    //var commentType = idArr[3];
    //var elementCount = idArr[4];

    return null;
}

//function isInt(n) {
//    return Number(n)===n && n%1===0;
//}
function isInt(n) {
    return n % 1 === 0;
}