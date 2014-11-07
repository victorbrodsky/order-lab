/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/27/14
 * Time: 12:55 PM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function() {

    setNavBar();

    fieldInputMask();

    //tooltip
    $(".element-with-tooltip").tooltip();

    initConvertEnterToTab();

    initDatepicker();

    expandTextarea();

    $('.panel-collapse').collapse({'toggle': false});

    regularCombobox();

    initTreeSelect();

    getComboboxInstitution();

    getComboboxCommentType();
    getComboboxIdentifier();
    getComboboxFellowshipType();
    getComboboxResearchLabs();
    getComboboxLocations();

    processEmploymentStatusRemoveButtons();

    positionTypeListener();

    initUpdateExpectedPgy();

    initFileUpload();

    windowCloseAlert();

    confirmDeleteWithExpired();

    initDatetimepicker();

});


