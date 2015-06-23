/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/27/14
 * Time: 12:55 PM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function() {

    //console.log('user form ready');

    $(this).scrollTop(0);

    setNavBar("employees");

    fieldInputMask();

    //tooltip
    $(".element-with-tooltip").tooltip();

    initConvertEnterToTab();

    initDatepicker();

    expandTextarea();

    $('.panel-collapse').collapse({'toggle': false});

    regularCombobox();

    initTreeSelect();

    //nstitution tree as combobox select2 view
    getComboboxInstitution();

    //jstree in admin page for Institution tree
    getJstree('Institution');

    getComboboxResidencyspecialty();

    getComboboxCommentType();

    //init generic comboboxes
    initAllComboboxGeneric();

    processEmploymentStatusRemoveButtons();

    positionTypeListener();

    initUpdateExpectedPgy();

    initFileUpload();

    windowCloseAlert();

    confirmDeleteWithExpired();

    initDatetimepicker();

    userCloneListener();

    identifierTypeListener();

    researchLabListener();

    grantListener();

    initTypeaheadUserSiteSearch();

    degreeListener();

});


