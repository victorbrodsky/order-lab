
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

$(document).ready(function() {

    console.log('transres form ready');
    transresIrbApprovalLetterListener();

    // $('form[name="oleg_translationalresearchbundle_project"]').submit(function(ev) {
    //     //return; //testing
    //     ev.preventDefault(); // to stop the form from submitting
    //     /* Validations go here */
    //     transresValidateProjectForm(this);
    // });

});

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
        $(".user-humanTissueForms").show();
    }

    if( involveHumanTissue == "No" ) {
        //console.log("humanTissueForms hide");
        $(".user-humanTissueForms").hide();
    }
}

//form with multiple buttons don't use form.submit(); because it does not pass buttons in the post
function transresValidateProjectForm() {

    console.log("Validate project");

    var validated = true;
    var label = null;
    var value = null;

    $("#projectError").hide();
    $("#projectError").html(null);

    transresHideBtn();

    //required
    $( ".required" ).each(function( index ) {
        //console.log( "Required: "+index + ": " + $( this ).text() );
        label = $( this ).text();   //$(this).find("label").text();
        value = null;
        var holder = $(this).closest(".row");

        //input
        var inputField = holder.find(".form-control");
        if( inputField.length > 0 ) {
            value = inputField.val();
            console.log("label="+label+"; value="+value);
            if( !value ) {
                console.log("Error Input form-control");
                validated = false;
                transresShowBtn();
                return false;
            }
        }

        //select combobox
        var selectField = holder.find("select.combobox");
        if( selectField.length > 0 ) {
            value = selectField.val();
            console.log("label="+label+"; value="+value);
            if( !value ) {
                console.log("Error Select select combobox");
                validated = false;
                transresShowBtn();
                return false;
            }
        }

        //input combobox
        var inputSelectField = holder.find("input.combobox");
        if( inputSelectField.length > 0 ) {
            value = inputSelectField.val();
            console.log("label="+label+"; value="+value);
            if( !value ) {
                console.log("Error Select input combobox");
                validated = false;
                transresShowBtn();
                return false;
            }
        }

    });

    if( validated == false ) {
        console.log("Error: required value is NULL! label="+label+"; value="+value);
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
        console.log("Error: involveHumanTissue is NULL!");
        //var msg = "Please upload a completed human tissue form";
        var msg = "Please answer the required question: 'Will this project involve human tissue?'";
        $("#projectError").show();
        $("#projectError").html(msg);

        validated = false;
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

    //console.log("No Error");
    //return false; //testing

    //projectForm[0].submit(); //submit by regular js (without jquery)
    //var form = document.getElementsByName("oleg_translationalresearchbundle_project");
    //var form = document.getElementById("transresProjectForm");
    //form.submit();
    //projectForm.submit(); // If all the validations succeeded

}

function transresHideBtn() {
    $('button').hide();
    $('#please-wait').show();
}
function transresShowBtn() {
    $('button').show();
    $('#please-wait').hide();
}

