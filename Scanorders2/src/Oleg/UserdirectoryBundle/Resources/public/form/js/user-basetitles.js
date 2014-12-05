/**
 * Generic functions to add and remove collections
 *
 * Created by oli2002 on 8/21/14.
 */


function addNewObject(btn,classname) {
//    var btnEl = $(btn);

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

    initBaseAdd(newForm);
    processEmploymentStatusRemoveButtons(btn);

}

function initBaseAdd(newForm) {
    expandTextarea();
    regularCombobox();
    initDatepicker();
    //tooltip
    $(".element-with-tooltip").tooltip();
    initTreeSelect();

    fieldInputMask(newForm);

    //init comboboxes
    getComboboxInstitution(newForm);   //init institution for administrative and appointnment titles
    getComboboxCommentType(newForm);
    getComboboxIdentifier(newForm);
    getComboboxFellowshipType(newForm);
    getComboboxResearchLabs(newForm);
    getComboboxLocations(newForm);
    getComboboxBuidlings(newForm);
    initFileUpload(newForm);

    confirmDeleteWithExpired(newForm);
}

//get input field only
function getBaseTitleForm( elclass ) {

    var dataholder = "#form-prototype-data"; //fixed data holder

    var holderClass = elclass+'-holder';
    //console.log('holderClass='+holderClass);

    var elements = $('.'+holderClass).find('.'+elclass);
    //console.log('elements='+elements.length);

    var identLowerCase = elclass.toLowerCase();

    //console.log("identLowerCase="+identLowerCase);

    var collectionHolder =  $(dataholder);
    var prototype = collectionHolder.data('prototype-'+identLowerCase);
    //console.log("prototype="+prototype);

    //var newForm = prototype.replace(/__administrativetitles__/g, elements.length);

    var classArr = identLowerCase.split("-"); //user-fieldname

    var regex = new RegExp( '__' + classArr[1] + '__', 'g' );
    var newForm = prototype.replace(regex, elements.length);

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

    var element = btnEl.closest('.'+classname);
    element.remove();

    processEmploymentStatusRemoveButtons(btn);
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
            alert("This location is used by another objects");
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
                    collectionHolder.remove();
                    processEmploymentStatusRemoveButtons(btn);
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
