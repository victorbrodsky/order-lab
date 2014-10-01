/**
 * Created by oli2002 on 8/22/14.
 */

//do not show the [X] (delete) button in the right upper corner of "Employment Period(s)"
// if it is the only one being displayed.
// When the user adds another one, then show an [X] next to each one.
function processEmploymentStatusRemoveButtons(btn) {

    if( cicle == "show_user" ) {
        return;
    }

    if( !btn && typeof btn != "undefined" ) {
        var btnEl = $(this);
        if( !btnEl.hasClass('btn-remove-minimumone-collection') && !btnEl.hasClass('btn-add-minimumone-collection') ) {
            return;
        }
    }

    var remBtns = $('.btn-remove-minimumone-collection');
    //console.log('remBtns.length='+remBtns.length);

    if( remBtns.length > 1 ) {
        //more than one element: show all remove buttons
        remBtns.show();

    } else {
        //0 or 1 element: hide remove buttons
        remBtns.hide();
    }

}

//on user load take care of hidden wells
function positionTypeListener() {
    $('.appointmenttitle-position-field').not("*[id^='s2id_']").each(function(e) {
        positionTypeAction(this);
    });
}

//In the section "Academic Appointment Title(s)", if "Resident" is selected in the "Position Type" dropdown menu,
// unfold a second drop down under it with a field called "Residency Track:" and show three choices: "AP", "CP", and "AP/CP".
function positionTypeAction(element) {
    var fieldEl = $(element);
    //console.log(fieldEl);

    var holder = fieldEl.closest('.user-collection-holder');
    //console.log(holder);

    if( !holder.hasClass('user-appointmentTitles') ) {
        return;
    }

    //printF(fieldEl,'field el:');

    var value = fieldEl.select2('val');
    //console.log('value='+value);

    holder.find('.appointmenttitle-residencytrack-field').hide();
    holder.find('.appointmenttitle-fellowshiptype-field').hide();

    if( value == 'Resident' ) {
        holder.find('.appointmenttitle-residencytrack-field').show();
    }

    if( value == 'Fellow' ) {
        holder.find('.appointmenttitle-fellowshiptype-field').show();
    }
}
