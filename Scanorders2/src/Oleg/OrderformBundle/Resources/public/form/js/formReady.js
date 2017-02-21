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

    // Prevent Dropzone from auto discovering this element
    if( typeof Dropzone !== 'undefined' ) {
        //Dropzone.options.scanorderform = false;
        Dropzone.autoDiscover = false;
    }

    setCicleShow();

    //checkBrowserComptability();

    initFileUpload();

    setNavBar("scan");

    if( $('#nav-bar-calllogsearch') ) {
        setNavBar("call-log-book");
    }

    initAdd();

    expandTextarea();

    fieldInputMask();

    setResearchEducational();   //init research and educational

    customCombobox();

    //setResearch();  //set research listener
    //setEducational(); //set educational listener

    //add diseaseType radio listener for new form
    diseaseTypeListener();
    //render diseaseType radio result for show form
    diseaseTypeRender();

    //take care of buttons for single form
    $("#message").hide();
    $("#optional_button").hide();

    //priority option
    priorityOption();

    //purpose option
    purposeOption();

    //multy form toggle button
    $('.form_body_toggle_btn').on('click', function(e) {
        //console.log('form_body_toggle_btn on click');
        var className = $(this).attr("class");
        var id = this.id;
        if( $(this).hasClass('glyphicon-folder-open') ) {
            $("#"+id).removeClass('glyphicon-folder-open');
            $("#"+id).addClass('glyphicon-folder-close');
        } else {
            $("#"+id).removeClass('glyphicon-folder-close');
            $("#"+id).addClass('glyphicon-folder-open');
        }
    });

    contentToggleHierarchyButton();

    //multy form delete button
    $('.delete_form_btn').on('click', function(e) {
        var id = this.id;
        deleteItem(id);
    });

//    //tooltip
//    $(".element-with-tooltip").tooltip();
//
//    $('.element-with-select2-tooltip').parent().tooltip({
//        title: function() {
//            var titleText = $(this).find('select.element-with-select2-tooltip').attr('title');
//            return titleText;
//        }
//    });
    initTooltips();

    initAllElements(); //init disable all fields

    //attachResearchEducationalTooltip();

    changeInstitution();

    windowCloseAlert();

    initConvertEnterToTab();

    //set composite tree
    getComboboxCompositetree();

    //jstree in admin page for Project Title and Course Title tree
    getJstree('OrderformBundle','ProjectTitleTree');
    getJstree('OrderformBundle','CourseTitleTree');
    getJstree('OrderformBundle','MessageCategory');
    getJstree('OrderformBundle','PatientListHierarchy');

    initDatetimepicker();

    //initTypeaheadOrderSiteSearch();

    generalConfirmAction();

    userPnotifyDisplay();

    userPlayroomHeaderInit();
});


