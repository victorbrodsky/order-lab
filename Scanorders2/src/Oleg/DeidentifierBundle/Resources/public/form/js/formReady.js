/**
 * Created by ch3 on 1/19/16.
 */

$(document).ready(function() {

    setNavBar("deidentifier");

    fieldInputMask();

    customCombobox();

    regularCombobox();

    initConvertEnterToTab();

    initDatetimepicker();

    initDeidentifierNavbarSearchMask();

    setOriginalSearchParameters();

});

function setOriginalSearchParameters() {

    var dataholder = $('#deidentifier-data-holder');

    console.log("dataholder.length="+dataholder.length);
    if( dataholder.length > 0 ) {
        return;
    }

    var institution = dataholder.attr("data-institution");
    console.log("institution="+institution);
    if( institution ) {
        console.log("set institution="+institution);
        $('.combobox-institution').select2('val',institution);
    }

    var accessionType = dataholder.attr("data-accessionType");
    if( accessionType ) {
        $('.accessiontype-combobox').select2('val',accessionType);
    }

    var accessionNumber = dataholder.attr("data-accessionNumber");
    if( accessionNumber ) {
        $('.accession-mask').val(accessionNumber);
    }
}
