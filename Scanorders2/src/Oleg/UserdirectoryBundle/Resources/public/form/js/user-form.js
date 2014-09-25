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
