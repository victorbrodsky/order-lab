/**
 * Generic functions to add and remove collections
 *
 * Created by oli2002 on 8/21/14.
 */


function addBaseTitle(btn,classname) {
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
    initFileUpload(newForm);
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

function removeBaseTitle(btn,classname) {

    var btnEl = $(btn);

    if( btnEl.hasClass('confirm-delete-with-expired') ) {
        //confirmDeleteWithExpired(btnEl);
        return;
    }

    var r = confirm("Are you sure you want to remove this record?");
    if (r == true) {
        //txt = "You pressed OK!";
    } else {
        return;
    }

    var element = btnEl.closest('.'+classname);
    element.remove();

    processEmploymentStatusRemoveButtons(btn);
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


$(function() {

    var dialogStr = "<p>You are about to permanently delete this record. If this record is not erroneous, please mark it \"Expired\" as of today's date instead. " +
        "This will maintain a historical list of previously valid employment periods, titles, and research labs.</p>";

    $('.confirm-delete-with-expired').click(function(e) {
        e.preventDefault();
        var dialog = $( dialogStr ).dialog({
            modal: true,
            title: "Confirmation",
            buttons: {
                "Mark as \"Expired\" as of today": function() {
                    console.log('expired');
                    //place today's date into the "End" field of the element
                },
                "Delete this erroneous record":  function() {
                    console.log('delete');
                    //delete
                    var btnEl = $(this);
                    var classname = 'user-collection-holder';
                    var element = btnEl.closest('.'+classname);
                    element.remove();
                    processEmploymentStatusRemoveButtons(btn);
                },
                "Cancel":  function() {
                    console.log('cancel');
                    //do nothing
                    dialog.dialog('close');
                }
            }
        });
    });
});


function confirmDeleteWithExpired(btn) {

    console.log('remove dialog');

    var btnEl = $(btn);

    var dialogStr = "<p>\"You are about to permanently delete this record. If this record is not erroneous, please mark it \"Expired\" as of today's date instead. " +
        "This will maintain a historical list of previously valid employment periods, titles, and research labs.\"</p>";

    //$(".confirm-delete-with-expired").dialog({
    btnEl.dialog({
        resizable: true,
        height:200,
        modal: true,
        title: "Confirmation",
        open: function() {
            var markup = dialogStr;
            $(this).html(markup);
        },
        buttons: {
            "Mark as \"Expired\" as of today": function() {
                console.log('expired');
                //place today's date into the "End" field of the element
            },
            "Delete this erroneous record":  function() {
                console.log('delete');
                //delete
                var classname = 'user-collection-holder';
                var element = btnEl.closest('.'+classname);
                element.remove();
                processEmploymentStatusRemoveButtons(btn);
            },
            "Cancel":  function() {
                console.log('cancel');
                //do nothing
                $( this ).dialog('close');
            }
        }
    });
}


