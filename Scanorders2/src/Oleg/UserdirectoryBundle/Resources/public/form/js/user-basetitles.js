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

    var r = confirm("Are you sure you want to remove this record?");
    if (r == true) {
        //txt = "You pressed OK!";
    } else {
        return;
    }

    var btnEl = $(btn);
    var element = btnEl.closest('.'+classname);
    element.remove();

    processEmploymentStatusRemoveButtons(btn);
}