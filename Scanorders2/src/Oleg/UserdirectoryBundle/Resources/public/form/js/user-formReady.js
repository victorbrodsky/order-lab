/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/27/14
 * Time: 12:55 PM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function() {

    //console.log('user form ready');
    
    //checkBrowserComptability();

    setCicleShow();

    //$(this).scrollTop(0);

    setNavBar();

    fieldInputMask();

    //tooltip
    //$(".element-with-tooltip").tooltip();
    initTooltips();

    initConvertEnterToTab();

    initDatepicker();

    expandTextarea();

    $('.panel-collapse').collapse({'toggle': false});

    regularCombobox();

    initTreeSelect();

    //composite tree as combobox select2 view
    getComboboxCompositetree();

    //jstree in admin page for Institution tree
    getJstree('UserdirectoryBundle','Institution');
    getJstree('UserdirectoryBundle','CommentTypeList');

    //home page institution with user leafs
    //displayInstitutionUserTree();
    //getJstree('UserdirectoryBundle','Institution_User','nomenu','nosearch','closeall');

    getComboboxResidencyspecialty();

    //getComboboxCommentType();

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

    userTypeListener();

    identifierTypeListener();

    researchLabListener();

    grantListener();

    initTypeaheadUserSiteSearch();

    degreeListener();

    generalConfirmAction();

});


