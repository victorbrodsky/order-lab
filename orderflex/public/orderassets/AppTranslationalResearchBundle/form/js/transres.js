
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

//defined functions that used outside the script (i.e. in FormType and onclick, onsubmit ...)
Window.prototype.transresValidateProjectForm = transresValidateProjectForm;
Window.prototype.transresSubmitBtnRegister = transresSubmitBtnRegister;

//export transresValidateProjectForm = transresValidateProjectForm;
//export transresSubmitBtnRegister;

var _transresprojecttypes = [];
//_transresitemcodes = [];

$(document).ready(function() {

    var cycle = $("#formcycle").val();
    var specialProjectSpecialty = $("#specialProjectSpecialty").val();

    //console.log('transres form ready, cycle='+cycle);
    transresHumanTissueListener(specialProjectSpecialty); //transresIrbApprovalLetterListener
    transresRequireTissueProcessingListener();
    transresRequireArchivalProcessingListener();

    transresNeedStatSupportListener();
    transresNeedInfSupportListener();
    //transresIrbStatusListListener();
    transresCollDivsListener();

    transresProjectFundedListener();

    //console.log("cycle="+cycle);
    if( cycle == "new" || cycle == "edit") {
        transresNewUserListener();

        //CONSTRUCT MODAL with Preloaded
        if(1) {
            var url = Routing.generate('employees_new_simple_user');
            var comboboxValue = "[[lastName]]";
            $.ajax({
                url: url,
                timeout: _ajaxTimeout,
                type: "GET",
                //type: "POST",
                data: {comboboxValue: comboboxValue},
                //dataType: 'json',
                async: asyncflag
            }).success(function (response) {
                //console.log(response);
                //newUserFormHtml,fieldId,sitename,otherUserParam,appendHolder
                constructAddNewUserModalByForm(response, "[[fieldId]]", "translationalresearch", "[[otherUserParam]]", "#add-new-user-modal-prototype",false);
            }).done(function () {
                //lbtn.stop();
            }).error(function (jqXHR, textStatus, errorThrown) {
                console.log('Error : ' + errorThrown);
            });
        }
    }

    getComboboxGeneric(null,'transresprojecttypes',_transresprojecttypes,false);

    transresIrbExemptListener('transres-project-exemptIrbApproval');

    transresIrbExemptListener('transres-project-exemptIACUCApproval');

    transresIrbStatusListListener('transres-project-irbStatusList');

    transresShowHideProjectDocument();

    //transresReSubmitReviewBtnListener();
    
});

// function transresReSubmitReviewBtnListener() {
//     $('.transres-reSubmitReview').on("click", function(e) {
//         var lbtn = Ladda.create($(this).get(0));
//         lbtn.start();
//     });
// }

function transresIrbExemptListener( classname ) {
    $("."+classname).on("change", function(e) {
        transresIrbExemptChange($(this),classname);
    });
}
function transresIrbExemptChange( exemptEl, classname ) {
    var exemptData = exemptEl.select2('data');
    var exemptText = exemptData.text;
    //console.log("change: exemptText="+exemptText);
    if( exemptText == "Exempt" ) {
        $("."+classname+"-panel").hide('slow');
        //$("."+classname+"-panel").fadeOut(2000);
    }
    if( exemptText == "Not Exempt" ) {
        $("."+classname+"-panel").show('slow');
        //$("."+classname+"-panel").fadeIn(2000);
    }
}

function transresIrbStatusListListener( classname ) {
    $("."+classname).on("change", function(e) {
        transresIrbStatusListChange($(this),classname);
    });
}
function transresIrbStatusListChange( exemptEl, classname ) {
    var exemptData = exemptEl.select2('data');
    var exemptText = null;
    if( exemptData ) {
        exemptText = exemptData.text;
    }
    //console.log("change: IrbStatusList="+exemptText);
    if( exemptText != "Not applicable" ) {
        $("#"+"irbStatusList").hide('slow');
    }
    if( exemptText == "Not applicable" ) {
        //$("."+classname+"-panel").show('slow');
        $("#"+"irbStatusList").show('slow');
    }
}

//Will this project involve human tissue?
function transresHumanTissueListener(specialProjectSpecialty) {
    $(".involveHumanTissue").on("change", function(e) {
        var involveHumanTissue = $(".involveHumanTissue").find('input[name="oleg_translationalresearchbundle_project[involveHumanTissue]"]:checked').val();
        //console.log("change: checked value involveHumanTissue="+involveHumanTissue);
        transresShowHideHumanTissueUploadSection(involveHumanTissue,specialProjectSpecialty);
    });
}
function transresShowHideHumanTissueUploadSection(involveHumanTissue,specialProjectSpecialty) {
    //console.log("involveHumanTissue="+involveHumanTissue);
    if( involveHumanTissue == "Yes" ) {
        //console.log("humanTissueForms Yes => show");
        //$(".user-humanTissueForms").show('slow');
        $("#transres-project-humanTissueForms").show('slow');

        //only for specialProjectSpecialty (i.e. CP) => entire accordion is automatically hidden
        if( specialProjectSpecialty == 1 || specialProjectSpecialty == true ) {
            $("#transres-project-humanRequestDetails").show('slow');
        }
    }

    if( involveHumanTissue == "No" ) {
        //console.log("humanTissueForms NO => hide");
        //$(".user-humanTissueForms").hide('slow');
        $("#transres-project-humanTissueForms").hide('slow');

        //only for specialProjectSpecialty (i.e. CP) => entire accordion is automatically hidden
        if( specialProjectSpecialty == 1 || specialProjectSpecialty == true ) {
            $("#transres-project-humanRequestDetails").hide('slow');
        }
    }

    //only for specialProjectSpecialty (i.e. CP)
    //console.log("specialProjectSpecialty="+specialProjectSpecialty);
    if( specialProjectSpecialty == 1 || specialProjectSpecialty == true ) {
        //console.log("use specialProjectSpecialty");
        changeHumanTissueRelatedSections(involveHumanTissue);
    }
}
function changeHumanTissueRelatedSections(value) {
    if( value == 'Yes' ) {
        //If Yes is selected => don't change requireTissueProcessing and requireTissueProcessing
        //$("input[name='oleg_translationalresearchbundle_project[requireTissueProcessing]'][value='Yes']").prop("checked",true).trigger("change");
        //$("input[name='oleg_translationalresearchbundle_project[requireArchivalProcessing]'][value='Yes']").prop("checked",true).trigger("change");
        //set to default => unselect all and show all
        $("input[name='oleg_translationalresearchbundle_project[requireTissueProcessing]'][value='Yes']").prop("checked",false).trigger("change");
        $("input[name='oleg_translationalresearchbundle_project[requireTissueProcessing]'][value='No']").prop("checked",false).trigger("change");
        $("input[name='oleg_translationalresearchbundle_project[requireArchivalProcessing]'][value='Yes']").prop("checked",false).trigger("change");
        $("input[name='oleg_translationalresearchbundle_project[requireArchivalProcessing]'][value='No']").prop("checked",false).trigger("change");
        //$("#tissueprocurement").show('slow');
        //$("#archivalspecimens").show('slow');
    }
    if( value == 'No' ) {
        //TODO: only for CP
        //If “No (this project will only involve human fluids or no human tissue at all)” is selected,
        // automatically answer “No” to “Will this project require tissue procurement/processing?:”
        // and make sure that entire accordion is automatically hidden.
        $("input[name='oleg_translationalresearchbundle_project[requireTissueProcessing]'][value='No']").prop("checked",true).trigger("change");
        //$("input[name='oleg_translationalresearchbundle_project[requireTissueProcessing]'][value='Yes']").prop("checked",true).trigger("change");

        $("input[name='oleg_translationalresearchbundle_project[requireArchivalProcessing]'][value='No']").prop("checked",true).trigger("change");
        //$("input[name='oleg_translationalresearchbundle_project[requireArchivalProcessing]'][value='Yes']").prop("checked",true).trigger("change");
    }

}

function transresRequireTissueProcessingListener() {
    $(".requireTissueProcessing").on("change", function(e) {
        var requireTissueProcessing = $(".requireTissueProcessing").find('input[name="oleg_translationalresearchbundle_project[requireTissueProcessing]"]:checked').val();
        //console.log("change: checked value requireTissueProcessing="+requireTissueProcessing);
        transresShowHideRequireTissueProcessing(requireTissueProcessing);
    });
}
function transresShowHideRequireTissueProcessing(requireTissueProcessing) {
    if( requireTissueProcessing == "Yes" ) {
        //console.log("requireTissueProcessing show");
        $("#tissueprocurement").show('slow');
    }

    if( requireTissueProcessing == "No" ) {
        //console.log("requireTissueProcessing hide");
        $("#tissueprocurement").hide('slow');
    }
}

function transresRequireArchivalProcessingListener() {
    $(".requireArchivalProcessing").on("change", function(e) {
        var requireArchivalProcessing = $(".requireArchivalProcessing").find('input[name="oleg_translationalresearchbundle_project[requireArchivalProcessing]"]:checked').val();
        //console.log("change: checked value requireArchivalProcessing="+requireArchivalProcessing);
        transresShowHideRequireArchivalProcessing(requireArchivalProcessing);
    });
}
function transresShowHideRequireArchivalProcessing(requireArchivalProcessing) {
    if( requireArchivalProcessing == "Yes" ) {
        //console.log("requireArchivalProcessing show");
        $("#archivalspecimens").show('slow');
    }

    if( requireArchivalProcessing == "No" ) {
        //console.log("requireArchivalProcessing hide");
        $("#archivalspecimens").hide('slow');
    }
}


function transresNeedStatSupportListener() {
    $(".needStatSupport").on("change", function(e) {
        var needStatSupport = $(".needStatSupport").find('input[name="oleg_translationalresearchbundle_project[needStatSupport]"]:checked').val();
        //console.log("change: checked value needStatSupport="+needStatSupport);
        transresShowHideNeedStatSupport(needStatSupport);
    });
}
function transresShowHideNeedStatSupport(needStatSupport) {
    if( needStatSupport == true ) {
        //console.log("needStatSupport show");
        $("#needstatsupport").show('slow');
    }

    if( needStatSupport == false ) {
        //console.log("needStatSupport hide");
        $("#needstatsupport").hide('slow');
    }
}

function transresCollDivsListener() {
    $(".collDivs").on("change", function(e) {
        var showCollLabs = false;
        var showCompTypes = false;
        $('input[name="oleg_translationalresearchbundle_project[collDivs][]"]').each(function () {
            //var sThisVal = (this.checked ? $(this).parent().text().trim() : "");
            if( this.checked ) {
                var collDiv = $(this).parent().text().trim();
                //console.log("change=" + collDiv);
                if( collDiv == "Clinical Pathology" ) {
                    showCollLabs = true;
                }
                if( collDiv == "Computational Pathology" ) {
                    showCompTypes = true;
                }
            }
        });
        transresShowHideCollLabs(showCollLabs);
        transresShowHideCompTypes(showCompTypes);
    });
}
function transresShowHideCollLabs(collDivs) {
    if( collDivs == true ) {
        //console.log("collDivs show");
        $("#collLabs").show('slow');
    }

    if( collDivs == false ) {
        //console.log("collDivs hide");
        $("#collLabs").hide('slow');
        //uncheck all answers
        $('#collLabs').find('input:checkbox').removeAttr('checked');
    }
}
function transresShowHideCompTypes(compTypes) {
    if( compTypes == true ) {
        //console.log("compTypes show");
        $("#compTypes").show('slow');
    }

    if( compTypes == false ) {
        //console.log("compTypes hide");
        $("#compTypes").hide('slow');
        //uncheck all answers
        $('#compTypes').find('input:checkbox').removeAttr('checked');
    }
}

function transresNeedInfSupportListener() {
    $(".needInfSupport").on("change", function(e) {
        var needInfSupport = $(".needInfSupport").find('input[name="oleg_translationalresearchbundle_project[needInfSupport]"]:checked').val();
        // console.log("change: checked value needInfSupport="+needInfSupport);
        transresShowHideNeedInfSupport(needInfSupport);
    });
}
function transresShowHideNeedInfSupport(needInfSupport) {
    if( needInfSupport == true ) {
        //console.log("needInfSupport show");
        $("#needInfSupport").show('slow');
    }

    if( needInfSupport == false ) {
        //console.log("needInfSupport hide");
        $("#needInfSupport").hide('slow');
    }
}

function transresProjectFundedListener() {
    //oleg_translationalresearchbundle_project_funded
    $(".transres-funded").on("change", function(e) {
        //var funded = $(".transres-funded").find('input[name="oleg_translationalresearchbundle_project[transres-funded]"]:checked').val();
        //console.log("change: checked value funded="+funded);
        transresShowHideProjectDocument();
        transresShowHideProjectAdditionalDetails();
        transresShowHideYes();
    });
}
function transresShowHideProjectDocument() {
    //collapse it (“hide it”) when the user puts a checkmark into the “Funded” field (uncollapse this accordion when the checkmark is removed)
    //funded (checked) => hide
    //not-funded (un-checked) => show
    if($("#oleg_translationalresearchbundle_project_funded").prop('checked') == true){
        //console.log("funded hide");
        $('#transres-project-documents').collapse('hide');
    } else {
        //console.log("funded show");
        $('#transres-project-documents').collapse('show');
    }
}
function transresShowHideProjectAdditionalDetails() {
    //collapse it (“hide it”) when the user puts a checkmark into the “Funded” field (uncollapse this accordion when the checkmark is removed)
    //funded (checked) => hide
    //not-funded (un-checked) => show
    if($("#oleg_translationalresearchbundle_project_funded").prop('checked') == true){
        //console.log("funded hide");
        $('#transres-project-additional-details').hide('slow');
    } else {
        //console.log("funded show");
        $('#transres-project-additional-details').show('slow');
    }
}
function transresShowHideYes() {
    //var label = 'Has this project been funded?:';
    if($("#oleg_translationalresearchbundle_project_funded").prop('checked') == true){
        console.log("transresShowHideYes Yes");
        //label = 'Has this project been funded? Yes:';
        //$('.transres-funded').closest('row').find('label').html('Has this project been funded? Yes:');
        //$('label[for="foo"]').html();
        $('#label-funded').html('Yes');
    } else {
        console.log("transresShowHideYes No");
        //label = 'Has this project been funded?:';
        //$('.transres-funded').closest('row').find('label').html('Has this project been funded?:');
        $('#label-funded').html('');
    }
    //$('label[for="oleg_translationalresearchbundle_project_funded"]').html(label);
}


//form with multiple buttons don't use form.submit(); because it does not pass buttons in the post.
//Therefore use button 'onclick'=>'transresValidateProjectForm();' in php form type
//
function transresValidateProjectForm() {

    //console.log("Validate project");

    transresHideBtn();

    //e.preventDefault();
    //e.stopImmediatePropagation();

    var validated = true;
    var label = null;
    var value = null;

    $("#projectError").hide();
    $("#projectError").html(null);

    //required class loop
    $("form[name=oleg_translationalresearchbundle_project]").find(".required").each(function( index ) {
        //console.log( "Required: "+index + ": " + $( this ).text() );
        label = $( this ).text();   //$(this).find("label").text();
        value = null;

        var holder = $(this).closest(".row");

        //input
        var inputField = holder.find(".form-control");
        if( inputField.length > 0 ) {
            value = inputField.val();
            //console.log("label="+label+"; value="+value);
            if( !value ) {
                //console.log("Error Input form-control. label="+label);
                validated = false;
                transresShowBtn();
                return false;
            }
        }

        //select combobox
        var selectField = holder.find("select.combobox");
        if( selectField.length > 0 ) {
            value = selectField.val();
            //console.log("select combobox: label="+label+"; value="+value);
            if( !value ) {
                //console.log("Error Select select combobox. label="+label);
                validated = false;
                transresShowBtn();
                return false;
            }
        }

        //input combobox
        var inputSelectField = holder.find("input.combobox");
        if( inputSelectField.length > 0 ) {
            value = inputSelectField.val();
            //console.log("input combobox: label="+label+"; value="+value);
            if( !value ) {
                //console.log("Error Select input combobox. label="+label);
                validated = false;
                transresShowBtn();
                return false;
            }
        }

    });

    if( validated == false ) {
        //console.log("Error: required value is NULL! label="+label+"; value="+value);
        var msg = "The required field '" + label + "' is empty";
        $("#projectError").show();
        $("#projectError").html(msg);

        transresShowBtn();
        return false;
    }

    //involveHumanTissue
    var involveHumanTissue = $(".involveHumanTissue").find('input[name="oleg_translationalresearchbundle_project[involveHumanTissue]"]:checked').val();
    //console.log("involveHumanTissue="+involveHumanTissue);
    if( !involveHumanTissue ) {
        //console.log("Error: involveHumanTissue is NULL!");
        //var msg = "Please upload a completed human tissue form";
        var msg = "Please answer the required question: 'Will this project involve human tissue?'";
        $("#projectError").show();
        $("#projectError").html(msg);

        //validated = false;
        transresShowBtn();
        return false;
    }

    var involveHumanTissue = $(".involveHumanTissue").find('input[name="oleg_translationalresearchbundle_project[involveHumanTissue]"]:checked').val();
    if( involveHumanTissue == "Yes" ) {
        var showlink = $(".user-humanTissueForms").find(".dz-preview");
        if( !showlink || showlink.length == 0 ) {
            var msg = "Please upload a completed human tissue form";
            $("#projectError").show();
            $("#projectError").html(msg);

            transresShowBtn();
            return false;
        }
    }

    //requireTissueProcessing
    var requireTissueProcessing = $(".requireTissueProcessing").find('input[name="oleg_translationalresearchbundle_project[requireTissueProcessing]"]:checked').val();
    //console.log("requireTissueProcessing="+requireTissueProcessing);
    if( !requireTissueProcessing ) {
        //console.log("Error: requireTissueProcessing is NULL!");
        //var msg = "Please upload a completed human tissue form";
        var msg = "Please answer the required question: 'Will this project require tissue procurement/processing?'";
        $("#projectError").show();
        $("#projectError").html(msg);

        //validated = false;
        transresShowBtn();
        return false;
    } else {
        //validate fields
    }

    //requireArchivalProcessing
    var requireArchivalProcessing = $(".requireArchivalProcessing").find('input[name="oleg_translationalresearchbundle_project[requireArchivalProcessing]"]:checked').val();
    //console.log("requireArchivalProcessing="+requireArchivalProcessing);
    if( !requireArchivalProcessing ) {
        //console.log("Error: requireArchivalProcessing is NULL!");
        //var msg = "Please upload a completed human tissue form";
        var msg = "Please answer the required question: 'Will this project require archival specimens?'";
        $("#projectError").show();
        $("#projectError").html(msg);

        //validated = false;
        transresShowBtn();
        return false;
    } else {
        //validate fields
    }

    // //needStatSupport
    // var needStatSupport = $(".needStatSupport").find('input[name="oleg_translationalresearchbundle_project[needStatSupport]"]:checked').val();
    // //console.log("needStatSupport="+needStatSupport);
    // if( !needStatSupport ) {
    //     //console.log("Error: needStatSupport is NULL!");
    //     //var msg = "Please upload a completed human tissue form";
    //     var msg = "Please answer the required question: 'Will you need departmental statistical support?'";
    //     $("#projectError").show();
    //     $("#projectError").html(msg);
    //
    //     //validated = false;
    //     transresShowBtn();
    //     return false;
    // } else {
    //     //validate fields
    // }
    //
    // var needInfSupport = $(".needInfSupport").find('input[name="oleg_translationalresearchbundle_project[needInfSupport]"]:checked').val();
    // //console.log("needInfSupport="+needInfSupport);
    // if( !needInfSupport ) {
    //     //console.log("Error: needInfSupport is NULL!");
    //     //var msg = "Please upload a completed human tissue form";
    //     var msg = "Please answer the required question: 'Will you need informatics support?'";
    //     $("#projectError").show();
    //     $("#projectError").html(msg);
    //
    //     //validated = false;
    //     transresShowBtn();
    //     return false;
    // } else {
    //     //validate fields
    // }

    //transres-project-exemptIrbApproval=="Not Exempt" => irbStatusList=="Not applicable" => irbStatusExplain is empty
    var exemptIrbApproval = $(".transres-project-exemptIrbApproval").select2('data');
    var irbStatusList = $(".transres-project-irbStatusList").select2('data');
    var exemptIrbApprovalValue = exemptIrbApproval.text;
    //console.log("exemptIrbApprovalValue=" + exemptIrbApprovalValue);
    if( exemptIrbApprovalValue == "Not Exempt" && irbStatusList ) {
        //var irbStatusList = $(".transres-project-irbStatusList").select2('data');
        if( irbStatusList ) {
            var irbStatusListValue = irbStatusList.text; //might be null: irbStatusList is null
            //console.log("irbStatusListValue=" + irbStatusListValue);
            if (irbStatusListValue == "Not applicable") {
                var irbStatusExplain = $(".transres-project-irbStatusExplain").val();
                //console.log("irbStatusExplain=" + irbStatusExplain);
                if (!irbStatusExplain) {
                    var msg = "Please explain why the IRB submission is not applicable";
                    $("#projectError").show();
                    $("#projectError").html(msg);

                    //validated = false;
                    transresShowBtn();
                    return false;
                }
            } else {
                //validate fields
            }
        }
    }

    //get original and new (current) state
    var projectOriginalStateValue = $("#projectOriginalStateValue").val();
    var projectOriginalState = $("#projectOriginalState").val(); //Closed
    var projectCurrentStateData = $("#oleg_translationalresearchbundle_project_state").select2('data');
    var projectCurrentStateValue = projectCurrentStateData.text; //Closed

    //"Closed" -> Any except "Canceled" => check exp date (only non-funded projects)
    var projectFundedValue = $("#oleg_translationalresearchbundle_project_funded").is(":checked");
    console.log("projectFundedValue="+projectFundedValue);
    if( !projectFundedValue ) {
        //var projectOriginalState = $("#projectOriginalState").val(); //Closed
        var projectOriginalExpDateStr = $("#projectOriginalExpDateStr").val();
        var projectCurrentExpDateStr = $("#oleg_translationalresearchbundle_project_expectedExpirationDate").val();
        //var projectCurrentStateData = $("#oleg_translationalresearchbundle_project_state").select2('data');
        //var projectCurrentStateValue = projectCurrentStateData.text; //Closed
        //console.log("projectOriginalState="+projectOriginalState+", projectOriginalExpDateStr="+projectOriginalExpDateStr);
        console.log("projectCurrentStateValue="+projectCurrentStateValue+", projectCurrentExpDateStr="+projectCurrentExpDateStr);
        if (projectOriginalState != projectCurrentStateValue) {
            if (projectOriginalState == "Closed" && projectCurrentStateValue != "Canceled") {
                //Create exp date object
                var timestamp = Date.parse(projectCurrentExpDateStr);
                var projectCurrentExpDateObject = new Date(timestamp);
                //Create date + 7 days object
                var today = new Date();
                var todayPlusSevenDaysObject = new Date();
                todayPlusSevenDaysObject.setDate(today.getDate() + 7);
                console.log("projectCurrentExpDateObject="+projectCurrentExpDateObject.toString()+", todayPlusSevenDaysObject="+todayPlusSevenDaysObject.toString());
                if (todayPlusSevenDaysObject >= projectCurrentExpDateObject) {
                    var msg = "Please update the expected expiration date " + projectCurrentExpDateStr + " to a future date, at least 7 days ahead";
                    console.log("show msg="+msg);
                    $("#projectError").show();
                    $("#projectError").html(msg);
                    transresShowBtn();
                    return false;
                }
            }
        }
    }

    console.log("projectCurrentStateValue="+projectCurrentStateValue+", projectOriginalState="+projectOriginalState);
    var projectChangeStateData = $('#project-change-state-data');
    if( projectChangeStateData.length && projectOriginalState == "Closed" && projectOriginalState != projectCurrentStateValue ) {
        //If a project status is changed from 'Closed' to another and Update button is pressed on that page, show the same confirmation in 8 above
        //Are you sure you would like to change the status of this project from 'Closed' to ‘….’?
        //Your request to change the status will be sent to the designated reviewer for approval and the status will be changed once approved.

        //show state change modal
        console.log("show state change modal");

        //change project state back to the original
        var modalTitle = "Are you sure you would like to change the status of this project from "
            + "'" + projectOriginalState + "' to '"+projectCurrentStateValue+"'?";
        projectChangeStateData.attr('trp-closure-title-data', modalTitle);

        var title = $('#project-change-state-data').attr('trp-closure-title-data');
        console.log("new title="+title);
        
        trpConstructClosureProjectModal(projectChangeStateData,false,'afterFunctionEditPage');
        transresShowBtn();

        //change state back to the original projectOriginalStateValue
        //resubmit with the original state by afterFunctionEditPage() => this modal will not be shown since the state is set to the original
        $("#oleg_translationalresearchbundle_project_state").select2('val', projectOriginalStateValue);

        return false;


        // if (todayPlusSevenDaysObject >= projectCurrentExpDateObject) {
        //     var msg = "Please update the expected expiration date " + projectCurrentExpDateStr + " to a future date, at least 7 days ahead";
        //     $("#projectError").show();
        //     $("#projectError").html(msg);
        //     transresShowBtn();
        //     return false;
        // }
    }

    if( 0 ) {
        var msg = "Test error";
        $("#projectError").show();
        $("#projectError").html(msg);
        transresShowBtn();
        return false;
    }

    //transresShowBtn();

    //console.log("No Error");
    //return false; //testing

    //projectForm[0].submit(); //submit by regular js (without jquery)
    //var form = document.getElementsByName("oleg_translationalresearchbundle_project");
    //var form = document.getElementById("transresProjectForm");
    //form.submit();
    //projectForm.submit(); // If all the validations succeeded
}

function transresSubmitBtnRegister(btnName) {
    var btnNameId = "oleg_translationalresearchbundle_project_"+btnName;
    _clickedSubmitBtnId = btnNameId;
    console.log("_clickedSubmitBtnId="+_clickedSubmitBtnId);
}

function transresHideBtn() {
    //console.log("hide submit buttons");
    // $(":submit").each(function () {
    //     var lbtn = Ladda.create($(this).get(0));
    //     lbtn.start();
    // });
    $(":submit").hide();
    $('#please-wait').show();
}
function transresShowBtn() {
    //console.log("show submit buttons");
    $(":submit").show();
    $('#please-wait').hide();
}

//New User functionality
function transresNewUserListener() {
    $('.add-new-user-on-enter').find('.select2-search-field > input.select2-input').on('keyup', function (e) {
        if (e.keyCode === 13)
        {
            //console.log("1 value="+this.value);
            //console.log($(this));
            transresOpenNewUserModal(this);
        }
    });

    //case for single select2: it does not work
    // $('.add-new-user-on-enter').find('.select2-search > input.select2-input').on('keyup', function (e) {
    //     if (e.keyCode === 13)
    //     {
    //         console.log("2 value="+this.value);
    //         console.log(this);
    //         console.log($(this));
    //         transresOpenNewUserModal(this);
    //     }
    // });

    //Special cases for single select2
    $('#s2id_oleg_translationalresearchbundle_project_principalIrbInvestigator').find('.select2-search > input.select2-input').on('keyup', function (e) {
        if (e.keyCode === 13)
        {
            //console.log("principalIrbInvestigator value="+this.value);
            transresOpenNewUserModal($('#s2id_oleg_translationalresearchbundle_project_principalIrbInvestigator'),this.value);
        }
    });
    $('#s2id_oleg_translationalresearchbundle_project_billingContact').find('.select2-search > input.select2-input').on('keyup', function (e) {
        if (e.keyCode === 13)
        {
            //console.log("principalIrbInvestigator value="+this.value);
            transresOpenNewUserModal($('#s2id_oleg_translationalresearchbundle_project_billingContact'),this.value);
        }
    });


}
function transresOpenNewUserModal(thisElement,comboboxValue) {
    //console.log("value="+thisElement.value);

    //select2-no-results select2-drop
    $('.select2-drop').hide();

    var btnDom = $(thisElement).closest('.row').find('a'); //.click();
    var sitename = "translationalresearch";
    var otherUserParam = null;

    //constructAddNewUserModalByAjax(btnDom,sitename,otherUserParam,thisElement);

    if( comboboxValue === undefined ) {
        //var comboboxValue = null;
        if (thisElement !== undefined) {
            comboboxValue = thisElement.value;
            //console.log("thisElement exists: comboboxValue="+comboboxValue);
        }
    }

    constructNewUserModal(btnDom,sitename,otherUserParam,comboboxValue);
}

function transresTestEmailNotations(specialtyId) {

    var modalElResult = $('#transres-email-test-result');
    var modalEl = $("#modal_test_email_naming_notation_"+specialtyId);
    if( modalEl|length ) {
        modalElResult = modalEl.find('#transres-email-test-result');
    }
    // else {
    //     modalElResult = $('#transres-email-test-result');
    // }

    modalElResult.html("");

    var invoiceId = null;
    var invoiceData = modalEl.find('#transres-invoice-list').select2('data');
    //console.log("transres TestEmailNotations: "+invoiceData);
    if( invoiceData ) {
        invoiceId = invoiceData.id;
    }
    //console.log("transres TestEmailNotations: invoiceId="+invoiceId);
    //alert("transres TestEmailNotations: invoiceId="+invoiceId);

    var url = Routing.generate('translationalresearch_test_email_notation_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "GET",
        data: {invoiceId: invoiceId, specialtyId: specialtyId },
        dataType: 'json',
        async: false //asyncflag
    }).success(function(response) {
        //console.log(response);

        //$('#transres-email-test-result').val(response);
        // if( modalEl|length ) {
        //     modalEl.find('#transres-email-test-result').html(response);
        // } else {
        //     $('#transres-email-test-result').html(response);
        // }
        modalElResult.html(response);

    }).done(function() {
        //
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    });

}
