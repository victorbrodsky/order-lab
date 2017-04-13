/*
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

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
    getJstree('UserdirectoryBundle','FormNode');
    getJstree('OrderformBundle','MessageCategory');

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

    userPreferencesHideListener();

    identifierTypeListener();

    researchLabListener();

    grantListener();

    initTypeaheadUserSiteSearch();

    degreeListener();

    generalConfirmAction();

    userPnotifyDisplay();
});


