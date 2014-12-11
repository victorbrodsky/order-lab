/**
 * Created by oli2002 on 9/25/14.
 */

function validateUser(origuserid) {

    var actionFlag = 'new';

    if( typeof origuserid != "undefined" && origuserid != "" ) {
        var actionFlag = 'update';
    }
    //console.log("actionFlag="+actionFlag+", origuserid="+origuserid);

    removeAllErrorAlerts();

    var firstName = $('#oleg_userdirectorybundle_user_firstName').val();
    firstName = trimWithCheck(firstName);
    var lastName = $('#oleg_userdirectorybundle_user_lastName').val();
    lastName = trimWithCheck(lastName);

    var userType = $('.user-keytype-field').select2('val');
    userType = trimWithCheck(userType);
    var primaryPublicUserId = $('#oleg_userdirectorybundle_user_primaryPublicUserId').val();
    primaryPublicUserId = trimWithCheck(primaryPublicUserId);

    if( userType == "" ) {
        $('#userinfo').collapse('show');
        addErrorAlert("Primary Public User ID Type is empty");
        $('.user-keytype-field').parent().addClass("has-error");
        return false;
    }

    if( primaryPublicUserId == "" ) {
        $('#userinfo').collapse('show');
        addErrorAlert("Primary Public User ID is empty");
        $('#oleg_userdirectorybundle_user_primaryPublicUserId').parent().addClass("has-error");
        return false;
    }

    if( firstName == "" ) {
        $('#userinfo').collapse('show');
        addErrorAlert("First Name is empty");
        $('#oleg_userdirectorybundle_user_firstName').parent().addClass("has-error");
        return false;
    }

    if( lastName == "" ) {
        $('#userinfo').collapse('show');
        addErrorAlert("Last Name is empty");
        $('#oleg_userdirectorybundle_user_lastName').parent().addClass("has-error");
        return false;
    }

    //field with required attributes (location Name can not be empty)
    if( validateSimpleRequiredAttrFields() == false ) {
        return false;
    }

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
        return false;
    }


    //check duplicate SSN
    var ssn = $('#oleg_userdirectorybundle_user_credentials_ssn').val();
    ssn = trimWithCheck(ssn);
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

        return false;
    }

    //check existing MRN identifier
    if( validateMrntypeIdentifier() == false ) {
        console.log('Validation Mrntype Identifier failed');
        return false;
    }

    //return false; //testing
    $("form:first").submit();
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
