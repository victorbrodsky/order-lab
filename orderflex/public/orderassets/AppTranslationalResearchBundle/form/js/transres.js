
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

_transresprojecttypes = [];
//_transresitemcodes = [];

$(document).ready(function() {

    var cycle = $("#formcycle").val();

    //console.log('transres form ready, cycle='+cycle);
    transresIrbApprovalLetterListener();
    transresRequireTissueProcessingListener();
    transresRequireArchivalProcessingListener();

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

    transresShowHideProjectDocument();
    
});

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

//Will this project involve human tissue?
function transresIrbApprovalLetterListener() {
    $(".involveHumanTissue").on("change", function(e) {
        var involveHumanTissue = $(".involveHumanTissue").find('input[name="oleg_translationalresearchbundle_project[involveHumanTissue]"]:checked').val();
        //console.log("change: checked value involveHumanTissue="+involveHumanTissue);
        transresShowHideHumanTissueUploadSection(involveHumanTissue);
    });
}
function transresShowHideHumanTissueUploadSection(involveHumanTissue) {
    if( involveHumanTissue == "Yes" ) {
        //console.log("humanTissueForms show");
        $(".user-humanTissueForms").show('slow');
    }

    if( involveHumanTissue == "No" ) {
        //console.log("humanTissueForms hide");
        $(".user-humanTissueForms").hide('slow');
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

function transresProjectFundedListener() {
    //oleg_translationalresearchbundle_project_funded
    $(".transres-funded").on("change", function(e) {
        //var funded = $(".transres-funded").find('input[name="oleg_translationalresearchbundle_project[transres-funded]"]:checked').val();
        //console.log("change: checked value funded="+funded);
        transresShowHideProjectDocument();
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


//form with multiple buttons don't use form.submit(); because it does not pass buttons in the post.
//Therefore use button 'onclick'=>'transresValidateProjectForm();' in php form type
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

    //"Closed" -> Any except "Canceled" => check exp date (only non-funded projects)
    var projectFundedValue = $("#oleg_translationalresearchbundle_project_funded").is(":checked");
    //console.log("projectFundedValue="+projectFundedValue);
    if( !projectFundedValue ) {
        var projectOriginalState = $("#projectOriginalState").val(); //Closed
        var projectOriginalExpDateStr = $("#projectOriginalExpDateStr").val();
        var projectCurrentExpDateStr = $("#oleg_translationalresearchbundle_project_expectedExpirationDate").val();
        var projectCurrentStateData = $("#oleg_translationalresearchbundle_project_state").select2('data');
        var projectCurrentStateValue = projectCurrentStateData.text; //Closed
        //console.log("projectOriginalState="+projectOriginalState+", projectOriginalExpDateStr="+projectOriginalExpDateStr);
        //console.log("projectCurrentStateValue="+projectCurrentStateValue+", projectCurrentExpDateStr="+projectCurrentExpDateStr);
        if (projectOriginalState != projectCurrentStateValue) {
            //if( projectOriginalExpDateStr == projectCurrentExpDateStr ) { //exp date left unchanged
            if (projectOriginalState == "Closed" && projectCurrentStateValue != "Canceled") {
                //Create exp date object
                var timestamp = Date.parse(projectCurrentExpDateStr);
                var projectCurrentExpDateObject = new Date(timestamp);
                //Create date + 7 days object
                var today = new Date();
                var todayPlusSevenDaysObject = new Date();
                todayPlusSevenDaysObject.setDate(today.getDate() + 7);
                //console.log("projectCurrentExpDateObject="+projectCurrentExpDateObject.toString()+", todayPlusSevenDaysObject="+todayPlusSevenDaysObject.toString());
                if (todayPlusSevenDaysObject >= projectCurrentExpDateObject) {
                    var msg = "Please update the expected expiration date " + projectCurrentExpDateStr + " to a future date, at least 7 days ahead";
                    $("#projectError").show();
                    $("#projectError").html(msg);
                    transresShowBtn();
                    return false;
                }
            }
            //}
        }
    }
    // if( 1 ) {
    //     var msg = "Test error";
    //     $("#projectError").show();
    //     $("#projectError").html(msg);
    //     transresShowBtn();
    //     return false;
    // }

    //transresShowBtn();

    //console.log("No Error");
    //return false; //testing

    //projectForm[0].submit(); //submit by regular js (without jquery)
    //var form = document.getElementsByName("oleg_translationalresearchbundle_project");
    //var form = document.getElementById("transresProjectForm");
    //form.submit();
    //projectForm.submit(); // If all the validations succeeded
}

function transresHideBtn() {
    //console.log("hide submit buttons");
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