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

//only used to set generated parameters on the index.html.twig page /generate/
function setOriginalSearchParameters() {

    var dataholder = $('#deidentifier-data-holder');

    //console.log("dataholder.length="+dataholder.length);
    if( dataholder.length == 0 ) {
        return;
    }

    var holder = $('#deidentifier-generate');

    var institution = dataholder.attr("data-institution");
    //console.log("institution="+institution);
    if( institution ) {
        //console.log("set institution="+institution);
        holder.find('.combobox-institution').select2('val',institution);
    }

    var accessionType = dataholder.attr("data-accessionType");
    if( accessionType ) {
        var accessionTypeField = holder.find('.accessiontype-combobox');
        accessionTypeField.select2('val',accessionType);
        setAccessiontypeMask(accessionTypeField,true);
    }

    var accessionNumber = dataholder.attr("data-accessionNumber");
    if( accessionNumber ) {
        //console.log("set accessionNumber="+accessionNumber);
        holder.find('.accession-mask').val(accessionNumber);
    }
}
