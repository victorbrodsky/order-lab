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
 * Created by oli2002 on 9/25/14.
 */

function validateUser(btnEl,origuserid) {

    //console.log("starting validateUser");

    var lbtn = Ladda.create(btnEl);
    lbtn.start();

    var actionFlag = 'new';

    if( typeof origuserid != "undefined" && origuserid != "" ) {
        var actionFlag = 'update';
    }
    console.log("actionFlag="+actionFlag+", origuserid="+origuserid);

    removeAllErrorAlerts();

    var firstName = $('.user-firstName').val();
    firstName = trimWithCheck(firstName);
    
    var lastName = $('.user-lastName').val();
    lastName = trimWithCheck(lastName);

    var userType = $('.user-keytype-field').select2('val');
    userType = trimWithCheck(userType);
    
    var primaryPublicUserId = $('#oleg_userdirectorybundle_user_primaryPublicUserId').val();
    primaryPublicUserId = trimWithCheck(primaryPublicUserId);
    
    var preferredEmail = $('.user-email').val(); 
    preferredEmail = trimWithCheck(preferredEmail);

    if( userdirectoryValidateEmail(preferredEmail) == false ) {
        $('#userinfo').collapse('show');
        addErrorAlert("Preferred Email is not valid");
        $('.user-keytype-field').parent().addClass("has-error");
        lbtn.stop();
        //console.log('Validation error preferredEmail='+preferredEmail);
        return false;
    }
    //console.log('Validation ok preferredEmail='+preferredEmail);

    if( userType == "" ) {
        $('#userinfo').collapse('show');
        addErrorAlert("Primary Public User ID Type is empty");
        $('.user-keytype-field').parent().addClass("has-error");
        lbtn.stop();
        return false;
    }

    if( primaryPublicUserId == "" ) {
        $('#userinfo').collapse('show');
        addErrorAlert("Primary Public User ID is empty");
        $('#oleg_userdirectorybundle_user_primaryPublicUserId').parent().addClass("has-error");
        lbtn.stop();
        return false;
    }        

    //password
    var passwordFirst = $('#oleg_userdirectorybundle_user_password_first').val();
    var passwordSecond = $('#oleg_userdirectorybundle_user_password_second').val();
    if( passwordFirst != passwordSecond ) {
        $('#userinfo').collapse('show');
        addErrorAlert("Please make sure the passwords match");
        $('#oleg_userdirectorybundle_user_password_first').parent().addClass("has-error");
        $('#oleg_userdirectorybundle_user_password_second').parent().addClass("has-error");
        lbtn.stop();
        return false;
    }

    //console.log("firstName="+firstName);
    if( firstName == "" ) {
        $('#userinfo').collapse('show');
        addErrorAlert("First Name is empty");
        $('.user-firstName').parent().addClass("has-error");
        lbtn.stop();
        return false;
    }

    //console.log("lastName="+lastName);
    if( lastName == "" ) {
        $('#userinfo').collapse('show');
        addErrorAlert("Last Name is empty");
        $('.user-lastName').parent().addClass("has-error");
        lbtn.stop();
        return false;
    }
    
    if( preferredEmail == "" ) {
        $('#userinfo').collapse('show');
        addErrorAlert("Preferred Email is empty");
        $('.user-email').parent().addClass("has-error");
        lbtn.stop();
        return false;
    }

    //field with required attributes (location Name can not be empty)
    if( validateSimpleRequiredAttrFields() == false ) {
        lbtn.stop();
        return false;
    }

    //check if CWID exists in LDAP active directory
    var userTypeText = $('.user-keytype-field').select2('data').text;
    //if( userTypeText == "WCM CWID" ) {
    if( userTypeText != "Local User" && userTypeText != "External Authentication" ) {
        if( isValidCWID(primaryPublicUserId,userTypeText) == false ) {
            $('#userinfo').collapse('show');
            $('#oleg_userdirectorybundle_user_primaryPublicUserId').parent().addClass("has-error");        

            var alert = 'An employee with the provided User ID Type "'+userTypeText+
                '" and User ID "'+primaryPublicUserId+'" does not exists in LDAP directory.' +
                " Please correct the new employee's User ID Type and/or User ID.";
            addErrorAlert(alert);
            lbtn.stop();
            return false;
        }
    }

    //Check if local user and password empty
    //console.log("userTypeText="+userTypeText+"; passwordFirst="+passwordFirst);
    //alert('stop');
    // if( userTypeText == "Local User" ) {
    //     if( !passwordFirst ) {
    //         $('#userinfo').collapse('show');
    //         $('#user-password-box').show();
    //         addErrorAlert("Please make sure the password is not empty for a local user");
    //         $('#oleg_userdirectorybundle_user_password_first').parent().addClass("has-error");
    //         $('#oleg_userdirectorybundle_user_password_second').parent().addClass("has-error");
    //         lbtn.stop();
    //         return false;
    //     }
    // }

    //check usertype + userid combination
    var user = checkUsertypeUserid(userType,primaryPublicUserId);
    var userid = user.id;
    //it is not possible to edit usertype and userid, thereofore check this combination only for a new user
    if( userid && actionFlag == 'new' ) {

        $('#userinfo').collapse('show');
        $('#oleg_userdirectorybundle_user_primaryPublicUserId').parent().addClass("has-error");

        var userTypeText = $('.user-keytype-field').select2('data').text;

        var alert = 'An employee with the provided User ID Type "'+userTypeText+'" and User ID "'+primaryPublicUserId+'" already exists: ' +
            getUserUrl(userid,user.firstName+" "+user.lastName) +
            "Please correct the new employee's User ID Type and User ID or edit the existing employee's information.";
        addErrorAlert(alert);
        lbtn.stop();
        return false;
    }      


    //check duplicate SSN
    var ssn = $('#oleg_userdirectorybundle_user_credentials_ssn').val();
    ssn = trimWithCheck(ssn);
    //console.log("ssn="+ssn);
    var user = checkDuplicateIdentifier(ssn,'ssn');
    var userid = user.id;
    if( userid && (actionFlag == 'new' || userid != origuserid && actionFlag == 'update') ) {

        $('#Credentials').collapse('show');
        $('#personalinfo').collapse('show');
        $('#oleg_userdirectorybundle_user_credentials_ssn').parent().addClass("has-error");

        var alert = "An employee with the provided Social Security Number (SSN) "+ssn+" already exists: " +
            getUserUrl(userid,user.firstName+" "+user.lastName) +
            "Please correct the new employee's Social Security Number (SSN) or edit the existing employee's information.";

        addErrorAlert(alert);

        lbtn.stop();
        return false;
    }

    //check existing MRN identifier
    if( validateMrntypeIdentifier() == false ) {
        //console.log('Validation Mrntype Identifier failed');
        lbtn.stop();
        return false;
    }

    //return false; //testing
    $("#user-profile-form").submit();
}

function userdirectoryValidateEmail(inputText)
{
    //console.log('Validation inputText='+inputText);
    var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    if(inputText.match(mailformat))
    {
        //alert("Valid email address!");
        //document.form1.text1.focus();
        return true;
    }
    return false;
}


function getUserUrl(userid,username) {
    var dataholder = document.querySelector('#form-prototype-data');
    var url = dataholder.dataset.userurllink;
    url = url.replace("user_replacement_id",userid);
    url = url.replace("user_replacement_username",username);
    return url;
}

function addErrorAlert(text) {
    var alert = '<div class="alert alert-danger user-error-alert" role="alert">'+
        text +
        '</div>';
    $('#user-errors').append(alert);
}

function removeAllErrorAlerts() {
    $('.user-error-alert').remove();
    $('.has-error').removeClass('has-error');
}

function checkDuplicateIdentifier(number,name) {
    var user = new Array();
    var url = getCommonBaseUrl("util/"+name,"employees");
    $.ajax({
        url: url,
        type: 'GET',
        data: {number: number},
        timeout: _ajaxTimeout,
        async: false
    }).success(function(data) {
        if( data.length > 0 ) {
            user = data[0];
        } else {
            user['id'] = null;
        }
    });
    return user;
}

function checkUsertypeUserid(userType,userId) {
    var user = new Array();
    var url = getCommonBaseUrl("util/"+"usertype-userid","employees");
    $.ajax({
        url: url,
        type: 'GET',
        data: {userType: userType, userId: userId},
        timeout: _ajaxTimeout,
        async: false
    }).success(function(data) {
        if( data.length > 0 ) {
            user = data[0];
        } else {
            user['id'] = null;
        }
    });
    return user;
}

function isValidCWID(userId) {
    var valid = true;
    var url = getCommonBaseUrl("util/"+"ldap-usertype-userid","employees");
    $.ajax({
        url: url,
        type: 'GET',
        data: {userId: userId},
        timeout: _ajaxTimeout,
        async: false
    }).success(function(data) {
        if( data == "notok" ) {
            valid = false;
        } 
    });
    return valid;
}

function validateSimpleRequiredAttrFields() {

    var errorCount = 0;

    $('input,textarea,select').filter('[required]').each( function() {
        var value = $(this).val();
        if( value == "" ) {
            $(this).parent().addClass("has-error");

            var msg = "Required Field is empty";

            if( $(this).hasClass('user-location-name-field') ) {
                $('#Locations').collapse('show');
                msg = "Location Name is empty";
            }

            addErrorAlert(msg);

            //attach on change listener
            $(this).change(function() {
                removeAllErrorAlerts();
            });

            errorCount++;
        }
    });

    if( errorCount == 0 ) {
        return true;
    } else {
        return false;
    }
}


//generate error for identifier field:
//If the number is not found, display a validation warning error well saying "The supplied MRN was not found."
// both next to the "Save" button and next to the Identifer field
//(it should still allow the user to save it even if the MRN was not found).
function validateMrntypeIdentifier() {

    var identifierKeytypemrn = $('.identifier-keytypemrn-field-holder');

    identifierKeytypemrn.each( function(e){
        var holder = $(this).closest('.user-identifiers');
        var keytypemrn = holder.find('.identifier-keytypemrn-field').select2('data');
        var keytypemrnVal = null;
        var keytypemrnText = null;
        if( keytypemrn ) {
            keytypemrnVal = holder.find('.identifier-keytypemrn-field').select2('val');
            keytypemrnText = holder.find('.identifier-keytypemrn-field').select2('data').text;
        }
        //console.log('keytypemrn='+keytypemrn);
        var identifier = holder.find('.identifier-field-field').val();
        //console.log('keytypemrn='+keytypemrn+", identifier="+identifier);

        if( keytypemrnVal && keytypemrnVal != "" && identifier && identifier != "" ) {

            var url = getCommonBaseUrl("util/common/mrntype-identifier","employees");
            var valid = true;

            $.ajax({
                url: url,
                type: 'GET',
                data: {mrntype: keytypemrnVal, identifier:identifier},
                timeout: _ajaxTimeout,
                async: false
            }).success(function(data) {
                if( data != 'OK' ) {
                    valid = false;
                }
            });

            if( valid == false ) {
                var alertid = "mrntype_identifier-"+keytypemrnVal+"-"+identifier;
                if( $('#'+alertid).length == 0 ) {
                    var msg = 'The supplied MRN "'+keytypemrnText+': '+identifier+'" was not found.'+
                              ' <input class="ignore-checkbox" type="checkbox" name="ignore" value="ignore"> Ignore this warning';
                    var alert = '<div id="'+alertid+'" class="alert alert-warning with-ignore" role="alert">'+msg+'</div>';
                    $('#user-errors').append(alert);
                }
                $('#Credentials').collapse('show');
                $('#identifiers').collapse('show');
                holder.find('.identifier-field-field').parent().addClass("has-error");
            }

        } //if

    });

    //removed old error boxes
    var withIgnore = $('.with-ignore');
    withIgnore.each( function() {
        var checkboxId = $(this).attr('id');
        var idArr = checkboxId.split("-");
        var boxMrntypeVal = idArr[1];
        var boxFieldVal = idArr[2];

        //now check if this keytype and field exists in any identifier object
        var exists = false;
        $('.user-identifiers').each( function(){
            var field = $(this).find('.identifier-field-field').val();
            var keytypemrn = $(this).find('.identifier-keytypemrn-field').select2('data');
            var keytypemrnVal = null;
            if( keytypemrn ) {
                keytypemrnVal = $(this).find('.identifier-keytypemrn-field').select2('val');
            }
            //check only identifiers with mrntype and field
            if( keytypemrnVal && keytypemrnVal != "" && field && field != "" ) {
                if( boxMrntypeVal == keytypemrnVal && boxFieldVal == field ) {
                    exists = true;
                    return;
                }
            }
        });

        if( exists == false ) {
            $(this).remove();
        }
    });

    var withIgnore = $('.with-ignore');
    if( withIgnore.length > 0 ) {
        //check for ignore checkboxes
        var ignored = 0;
        withIgnore.each( function(){
            var ignore = $(this).find('.ignore-checkbox');
            //console.log(ignore);
            if( ignore.is(':checked') ) {
                ignored++;
            }
        });

        //console.log('withIgnore.length='+withIgnore.length+", ignored="+ignored);
        if( withIgnore.length == ignored ) {
            return true;
        } else {
            return false;
        }
    }

    return true;
}

// function verifyMobilePhoneNumber(userId,preferredMobilePhone) {
//     var valid = true;
//     var url = getCommonBaseUrl("util/"+"ldap-usertype-userid","employees");
//     $.ajax({
//         url: url,
//         type: 'GET',
//         data: {userId: userId},
//         timeout: _ajaxTimeout,
//         async: false
//     }).success(function(data) {
//         if( data == "notok" ) {
//             valid = false;
//         }
//     });
//     return valid;
// }

function sendVerificationCode(phoneNumber) {

    if( phoneNumber ) {
        //phoneNumber = phoneNumber.replace("-", "");
        //phoneNumber = phoneNumber.replace(" ", "");
        //phoneNumber = phoneNumber.replace("_", "");
        phoneNumber = phoneNumber.replace(/-/g, '');
        phoneNumber = phoneNumber.replace(/ /g, '');
        phoneNumber = phoneNumber.replace(/_/g, '');
    } else {
        alert("Please enter your mobile phone number");
        return false;
    }

    console.log('sendVerificationCode: phoneNumber=' + phoneNumber);
    var btn = document.getElementById("send-verification-code-button-modal");
    var lbtn = Ladda.create( btn );
    lbtn.start();

    var url = Routing.generate('employees_verify_mobile_phone_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        type: "POST",
        data: {phoneNumber: phoneNumber},
        //dataType: 'json',
        async: asyncflag
    }).done(function(response) {
        console.log(response);
        if( response == 'OK' ) {
            lbtn.stop();
            //document.getElementById('send-verification-code-button').title = 'Verification Code sent to '+phoneNumber;
            var confStatusHtml =
                '<p class="text-success">A text message with a code was just sent to '
                +phoneNumber+
                '. Please enter the code below and click "Verify" button.</p>';
            $("#phone-number-verify-status-modal").html(confStatusHtml);
            $("#send-verification-code-button-modal").html('Resend the text message with the verification code');
            //$("#send-verification-code-button-modal").html('Verification Code sent to +'+phoneNumber);
            //$("#send-verification-code-button-modal").prop('disabled', true);
            //$("#send-verification-code-button").attr('disabled','disabled');
        } else {
            lbtn.stop();
            alert(response);
        }
    }).always(function() {
        //lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        lbtn.stop();
        console.log('Error : ' + errorThrown);
        alert('Error : ' + errorThrown);
    });

    return true;
}
function verifyPhoneNumberCode(phoneNumber,verificationCode) {
    //testing
    //$("#phone-number-verify-status-modal").html('<p class="text-success">Mobile phone number verified</p>');
    //$("#send-verification-code-button-modal").html('Re-send Verification Code to +'+phoneNumber);
    //$("#send-verification-code-button-modal").prop('disabled', false);
    //$('#verify-phone-number-button').remove();
    //$('#phone-number-verify-status').html('<span class="text-success">Verified</span>');
    //$('#phone-number-verify-status').text('Verified');
    //$('#phone-number-verify-status').html('Verified');
    //return;

    if( !verificationCode ) {
        alert('Please enter your verification code');
        return false;
    }

    //console.log('phoneNumber=' + phoneNumber+"; verificationCode="+verificationCode);
    var btn = document.getElementById("verify-code-button");
    var lbtn = Ladda.create( btn );
    lbtn.start();

    var url = Routing.generate('employees_verify_code_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        type: "POST",
        data: {phoneNumber: phoneNumber, verificationCode: verificationCode},
        //dataType: 'json',
        async: asyncflag
    }).done(function(response) {
        console.log(response);
        if( response == 'OK' ) {
            lbtn.stop();
            //document.getElementById('send-verification-code-button').title = 'Verification Code sent to '+phoneNumber;
            $("#phone-number-verify-status-modal").html('<p class="text-success">Mobile phone number verified</p>');
            $("#send-verification-code-button-modal").html('Resend the text message with the verification code');
            $("#send-verification-code-button-modal").prop('disabled', false);

            $('.verify-phone-number-button').remove();
            $('.phone-number-verify-status').html('<span class="text-success">Verified</span>');
        } else {
            lbtn.stop();
            alert(response);
        }
    }).always(function() {
        //lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        lbtn.stop();
        console.log('Error : ' + errorThrown);
    });

    return true;
}


function sendVerificationAccountRequestCode(phoneNumber,userRequestId,objectName) {

    if( phoneNumber ) {
        //phoneNumber = phoneNumber.replace("-", "");
        //phoneNumber = phoneNumber.replace(" ", "");
        //phoneNumber = phoneNumber.replace("_", "");
        phoneNumber = phoneNumber.replace(/-/g, '');
        phoneNumber = phoneNumber.replace(/ /g, '');
        phoneNumber = phoneNumber.replace(/_/g, '');
    } else {
        alert("Please enter your mobile phone number");
        return false;
    }

    console.log('phoneNumber=' + phoneNumber);
    var btn = document.getElementById("send-verification-code-button");
    var lbtn = Ladda.create( btn );
    lbtn.start();

    var url = Routing.generate('employees_verify_mobile_phone_account_request_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        type: "POST",
        data: {phoneNumber: phoneNumber, objectName: objectName, userRequestId: userRequestId},
        //dataType: 'json',
        async: asyncflag
    }).done(function(response) {
        console.log(response);
        if( response == 'OK' ) {
            lbtn.stop();
            //document.getElementById('send-verification-code-button').title = 'Verification Code sent to '+phoneNumber;
            var confStatusHtml =
                '<p class="text-success">A text message with a code was just sent to '
                +phoneNumber+
                '. Please enter the code below and click "Verify" button.</p>';
            $("#phone-number-verify-status").html(confStatusHtml);
            $("#send-verification-code-button").html('Resend the text message with the verification code');
        } else {
            alert(response);
            lbtn.stop();
        }
    }).always(function() {
        //lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        lbtn.stop();
        console.log('Error : ' + errorThrown);
        alert('Error : ' + errorThrown);
    });

    return true;
}
function verifyPhoneNumberAccountRequestCode(verificationCode,userRequestId,objectName) {

    if( !verificationCode ) {
        alert('Please enter your verification code');
        return false;
    }

    //console.log('phoneNumber=' + phoneNumber+"; verificationCode="+verificationCode);
    var btn = document.getElementById("verify-code-button");
    var lbtn = Ladda.create( btn );
    lbtn.start();

    var url = Routing.generate('employees_verify_code_account_request_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        type: "POST",
        data: {verificationCode: verificationCode, objectName: objectName, userRequestId: userRequestId},
        //dataType: 'json',
        async: asyncflag
    }).done(function(response) {
        console.log(response);
        if( response == 'OK' ) {
            lbtn.stop();
            //document.getElementById('send-verification-code-button').title = 'Verification Code sent to '+phoneNumber;
            $("#phone-number-verify-status").html('<p class="text-success">Mobile phone number verified</p>');
            $("#send-verification-code-button").html('Resend the text message with the verification code');
            $("#send-verification-code-button").prop('disabled', false);

            $('.verify-phone-number-button').remove();
            $('.phone-number-verify-status').html('<span class="text-success">Verified</span>');
            
            //add link to login page
            $('#loginhref').show();
            
        } else {
            lbtn.stop();
            alert(response);
        }
    }).always(function() {
        //lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        lbtn.stop();
        console.log('Error : ' + errorThrown);
    });

    return true;
}
