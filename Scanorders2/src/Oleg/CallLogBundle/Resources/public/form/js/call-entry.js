/**
 * Created by ch3 on 7/25/2016.
 */


var _patients = [];
var _mrntype_original = null;

function addnewCalllogPatient(holderId) {

    var holder = getHolder(holderId);

    var mrntype = holder.find(".mrntype-combobox").select2('val');
    mrntype = trimWithCheck(mrntype);

    var mrn = holder.find(".patientmrn-mask").val();
    mrn = trimWithCheck(mrn);

    var dob = holder.find(".patient-dob-date").val();
    dob = trimWithCheck(dob);

    var lastname = holder.find(".encounter-lastName").val();
    lastname = trimWithCheck(lastname);

    var firstname = holder.find(".encounter-firstName").val();
    firstname = trimWithCheck(firstname);

    var middlename = holder.find(".encounter-middleName").val();
    middlename = trimWithCheck(middlename);

    var suffix = holder.find(".encounter-suffix").val();
    suffix = trimWithCheck(suffix);

    var sex = holder.find(".encountersex-field").select2('val');
    sex = trimWithCheck(sex);

    //check if "Last Name" field + DOB field, or "MRN" fields are not empty
    //if( !mrn || !mrntype || !lastname || !dob ) {
    if( mrntype && mrn || lastname && dob ) {
        //if( mrntype && mrn || lastname ) {
        //ok
    } else {
        holder.find('#calllog-danger-box').html("Please enter at least an MRN or Last Name and Date of Birth.");
        //holder.find('#calllog-danger-box').html("Please enter at least an MRN or Last Name.");
        holder.find('#calllog-danger-box').show();
        return false;
    }


    //"Are You sure you would like to create a new patient registration record for
    //MRN: Last Name: First Name: Middle Name: Suffix: Sex: DOB: Alias(es):
    var confirmMsg = "Are You sure you would like to create a new patient registration record for";

    if( mrn )
        confirmMsg += " MRN:"+mrn;
    if( lastname )
        confirmMsg += " Last Name:"+lastname;
    if( firstname )
        confirmMsg += " First Name:"+firstname;
    if( middlename )
        confirmMsg += " Middle Name:"+middlename;
    if( suffix )
        confirmMsg += " Suffix:"+suffix;
    if( sex )
        confirmMsg += " Sex:"+sex;
    if( dob )
        confirmMsg += " DOB:"+dob;

    if( confirm(confirmMsg) == true ) {
        //x = "You pressed OK!";
    } else {
        //x = "You pressed Cancel!";
        return false;
    }

    //Clicking "Ok" in the Dialog confirmation box should use the variables
    // to create a create the new patient on the server via AJAX/Promise,
    // then lock the Patient Info fields, and change the title of the "Find Patient" button to "Re-enter Patient"
    //ajax
    var url = Routing.generate('calllog_create_patient');
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: true,
        data: {mrntype: mrntype, mrn: mrn, dob: dob, lastname: lastname, firstname: firstname, middlename: middlename, suffix: suffix, sex: sex  },
    }).success(function(data) {
        //console.log("output="+data);
        if( data == "OK" ) {
            //console.log("Patient has been created");
            //hide find patient and add new patient
            holder.find('#search_patient_button').hide();
            holder.find('#addnew_patient_button').hide();
            //show Re-enter Patient
            holder.find('#reenter_patient_button').show();
            //clean error message
            holder.find('#calllog-danger-box').html('');
            holder.find('#calllog-danger-box').hide();

            //disable all fields
            disableAllFields(true,holderId);

            //show edit patient info button
            holder.find('#edit_patient_button').show();
            //hide "No single patient is referenced by this entry or I'll add the patient info later" link
            holder.find('#callentry-nosinglepatient-link').hide();

        } else {
            //console.log("Patient has not been created");
            holder.find('#calllog-danger-box').html(data);
            holder.find('#calllog-danger-box').show();
        }
    });


}



function clearCalllogPatient(holderId) {
    var holder = getHolder(holderId);
    populatePatientInfo(null,null,true,holderId);

    //change the "Re-enter Patient" to "Find Patient"
    holder.find('#reenter_patient_button').hide();
    holder.find('#search_patient_button').show();

    calllogHideAllAlias(true,true,holderId);
}

function findCalllogPatient(holderId) {

    var holder = getHolder(holderId);

    //clear no matching box
    holder.find('#calllog-danger-box').hide();
    holder.find('#calllog-danger-box').html("");

    var searchedStr = "";

    var mrntype = holder.find(".mrntype-combobox").select2('val');
    mrntype = trimWithCheck(mrntype);

    //set _mrntype_original
    if( _mrntype_original == null && _mrntype && _mrntype.length > 0 ) {
        _mrntype_original = _mrntype[0].id;
    }

    var mrn = holder.find(".patientmrn-mask").val();
    mrn = trimWithCheck(mrn);

    var dob = holder.find(".patient-dob-date").val();
    dob = trimWithCheck(dob);

    var lastname = holder.find(".encounter-lastName").val();
    lastname = trimWithCheck(lastname);

    var firstname = holder.find(".encounter-firstName").val();
    firstname = trimWithCheck(firstname);

    //console.log('mrntype='+mrntype+", mrn="+mrn+", dob="+dob+", lastname="+lastname+", firstname="+firstname);

    if( mrn && mrntype || dob && lastname || dob && lastname && firstname || lastname ) {
        //ok
        if( mrn && mrntype ) {
            searchedStr = " (searched for MRN Type: "+holder.find(".mrntype-combobox").select2('data').text+"; MRN: "+mrn+")";
        }
        if( dob && lastname ) {
            var firstnameStr = "";
            if( firstname ) {
                firstnameStr = "; First Name: "+firstname;
            }
            searchedStr = " (searched for DOB: "+dob+"; Last Name: "+lastname+firstnameStr+")";
        }
        if( lastname ) {
            var firstnameStr = "";
            if( firstname ) {
                firstnameStr = "; First Name: "+firstname;
            }
            searchedStr = " (searched for Last Name: "+lastname+firstnameStr+")";
        }
    } else {
        //holder.find('#calllog-danger-box').html("Please enter at least an MRN or Last Name and Date of Birth.");
        holder.find('#calllog-danger-box').html("Please enter at least an MRN or Last Name.");
        holder.find('#calllog-danger-box').show();
        return false;
    }

    //ajax
    var url = Routing.generate('calllog_search_patient');
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: true,
        data: {mrntype: mrntype, mrn: mrn, dob: dob, lastname: lastname, firstname: firstname },
    }).success(function(data) {
        //console.log("data.length="+data.length);
        //console.log(data);
//            if( data.length > 0 ) {
//                console.log("Patient found");
        populatePatientsInfo(data,searchedStr,holderId);
//            } else {
//                populatePatientInfo(null);
//                console.log("Patient not found");
//            }
    });

}

function populatePatientsInfo(patients,searchedStr,holderId) {

    var holder = getHolder(holderId);

    var patLen = patients.length;
    //console.log('patLen='+patLen);

    //clear matching patient section
    holder.find('#calllog-matching-patients').hide();
    holder.find('#calllog-matching-patients').html('');

    //clear no matching box
    holder.find('#calllog-danger-box').hide();
    holder.find('#calllog-danger-box').html("");

    //hide edit patient info button
    holder.find('#edit_patient_button').hide();
    //hide "No single patient is referenced by this entry or I'll add the patient info later" link
    holder.find('#callentry-nosinglepatient-link').show();

    _patients = patients;

    if( patLen == 1 ) {
        populatePatientInfo(patients[0],null,true,holderId);
        disableAllFields(true,holderId);

        //show edit patient info button
        holder.find('#edit_patient_button').show();
        //hide "No single patient is referenced by this entry or I'll add the patient info later" link
        holder.find('#callentry-nosinglepatient-link').hide();

        //change the "Find or Add Patient" button title to "Re-enter Patient"
        holder.find('#reenter_patient_button').show();
        holder.find('#search_patient_button').hide();
        holder.find('#addnew_patient_button').hide();

    } else if( patLen == 0 ) {
        //console.log("No matching patient records found.");
        //"No matching patient records found." and unlock fields
        holder.find('#calllog-danger-box').html("No matching patient records found"+searchedStr+".");
        holder.find('#calllog-danger-box').show();
        populatePatientInfo(null,true,false,holderId);
        disableAllFields(false,holderId);

        //un-hide/show a button called "Add New Patient Registration"
        holder.find('#addnew_patient_button').show();

    } else if( patLen > 1 ) {
        //console.log("show table with found patients");
        //show table with found patients
        //populatePatientInfo(patients[0],null);
        populatePatientInfo(null,null,false,holderId);
        disableAllFields(false,holderId);

        createPatientsTableCalllog(patients,holderId);

    } else {
        //console.log('This condition should not be reached');
    }

}

function createPatientsTableCalllog( patients, holderId ) {

    var holder = getHolder(holderId);

    //Matching Patients
    var matchingPatientsHtml = '<div class="table-responsive"><table id="calllog-matching-patients-table-'+holderId+'" class="table table-bordered">' +
        '<thead><tr>' +
        '<th>MRN</th>' +
        '<th>Last Name</th>' +
        '<th>First Name</th>' +
        '<th>Middle Name</th>' +
        '<th>Suffix</th>' +
        '<th>Sex</th>' +
        '<th>DOB</th>' +
        '<th>Contact Info</th>' +
        '</tr></thead>' +
        '<tbody>';

    for( var i = 0; i < patients.length; i++ ) {
        var patient = patients[i];
        //console.log('patient id='+patient.id);

        matchingPatientsHtml = matchingPatientsHtml +
            '<tr class="clickable-row" id="'+i+'-'+holderId+'">' +
            '<td>'+patient.mrn+' ('+patient.mrntypestr+')</td>'+
            '<td>'+patient.lastname+'</td>'+
            '<td>'+patient.firstname+'</td>'+
            '<td>'+patient.middlename+'</td>'+
            '<td>'+patient.suffix+'</td>'+
            '<td>'+patient.sexstr+'</td>'+
            '<td>'+patient.dob+'</td>'+
            '<td>'+patient.contactinfo+'</td>'+
            '</tr>';

    }

    matchingPatientsHtml = matchingPatientsHtml + "</tbody></table></div>";

    matchingPatientsHtml = matchingPatientsHtml +
        '<p data-toggle="tooltip" title="Please select the patient"><button type="button"'+
        //' id="matchingPatientBtn-'+holderId+'"'+
        ' class="btn btn-lg span4 matchingPatientBtn" align="center"'+
        ' disabled'+
        ' onclick="matchingPatientBtnClick(\''+holderId+'\')"'+
        '>Select Patient</button></p>';

    holder.find('#calllog-matching-patients').html(matchingPatientsHtml);
    holder.find('#calllog-matching-patients').show();


    holder.find('.matchingPatientBtn').parent().tooltip();

    holder.find('#calllog-matching-patients-table-'+holderId).on('click', '.clickable-row', function(event) {
        $(this).addClass('active').addClass('success').siblings().removeClass('active').removeClass('success');
        //enable button
        holder.find('.matchingPatientBtn').prop('disabled', false);
        holder.find('.matchingPatientBtn').parent().tooltip('destroy');
    });

}

function matchingPatientBtnClick(holderId) {
    console.log('holderId='+holderId);
    var holder = getHolder(holderId);

    var index = holder.find('#calllog-matching-patients-table-'+holderId).find('.active').attr('id');
    //remove holderId from index
    index = index.replace("-"+holderId, "");
    console.log('index='+index);
    populatePatientInfo(_patients[index],null,true,holderId);
    disableAllFields(true,holderId);

    //show edit patient info button
    holder.find('#edit_patient_button').show();
    //hide "No single patient is referenced by this entry or I'll add the patient info later" link
    holder.find('#callentry-nosinglepatient-link').hide();

    //change the "Find or Add Patient" button title to "Re-enter Patient"
    holder.find('#reenter_patient_button').show();
    holder.find('#search_patient_button').hide();

    //remove and hide matching patients table
    holder.find('#calllog-matching-patients-table-'+holderId).remove();
    holder.find('#calllog-matching-patients').html('');
    holder.find('#calllog-matching-patients').hide();
}

function disableAllFields(disable,holderId) {

    var holder = getHolder(holderId);

    disableField(holder.find(".mrntype-combobox"),disable);

    disableField(holder.find(".patientmrn-mask"),disable);

    disableField(holder.find(".patient-dob-date"),disable);

    //disableField(holder.find(".patient-dob-date"),disable);

    disableField(holder.find(".encounter-lastName"),disable);

    disableField(holder.find(".encounter-firstName"),disable);

    disableField(holder.find(".encounter-middleName"),disable);

    disableField(holder.find(".encounter-suffix"),disable);

    //disableSelectFieldCalllog(holder.find(".encountersex-field"),true);
    disableField(holder.find(".encountersex-field"),disable);
}
function disableField(fieldEl,disable) {
    if( disable ) {
        fieldEl.prop('disabled', true);
        fieldEl.closest('.input-group').find('input').prop('disabled', true);
        if( fieldEl.hasClass('datepicker') ) {
            var elementDatepicker = fieldEl.closest('.input-group.date');
            elementDatepicker.datepicker("remove");
        }
    } else {
        //unlock field
        fieldEl.prop('disabled', false);
        fieldEl.closest('.input-group').find('input').prop('disabled', false);
        if( fieldEl.hasClass('datepicker') ) {
            var elementDatepicker = fieldEl.closest('.input-group.date');
            initSingleDatepicker(elementDatepicker);
        }
    }
}
//    function disableSelectFieldCalllog(fieldEl,disable) {
//        if( disable ) {
//            fieldEl.prop('disabled', true);
//        } else {
//            fieldEl.prop('disabled', false);
//        }
//    }

function populatePatientInfo( patient, showinfo, modify, holderId ) {

    var holder = getHolder(holderId);

    populateInputFieldCalllog(holder.find(".calllog-patient-id-radio"),patient,'id',modify);
    disableField(holder.find(".calllog-patient-id-radio"),false);

    populateInputFieldCalllog(holder.find(".calllog-patient-id"),patient,'id',modify);
    holder.find(".calllog-patient-id").trigger('change');
    holder.find(".calllog-patient-id").change();

    processMrnFieldsCalllog(patient,modify,holderId);

    populateInputFieldCalllog(holder.find(".patient-dob-date"),patient,'dob',modify);

    populateInputFieldCalllog(holder.find(".encounter-lastName"),patient,'lastname',modify);

    populateInputFieldCalllog(holder.find(".encounter-firstName"),patient,'firstname',modify);

    populateInputFieldCalllog(holder.find(".encounter-middleName"),patient,'middlename');

    populateInputFieldCalllog(holder.find(".encounter-suffix"),patient,'suffix');

    populateSelectFieldCalllog(holder.find(".encountersex-field"),patient,'sex');

    //console.log('middlename='+middlename+'; suffix='+suffix+'; sex='+sex);
    if( patient && patient.id || showinfo ) {
        holder.find('#encounter-info').collapse("show");
    } else {
        holder.find('#encounter-info').collapse("hide");
    }

//        //change the "Find or Add Patient" button title to "Re-enter Patient"
//        if( patient && patient.id && patient.lastname && patient.firstname && patient.dob ) {
//            holder.find('#search_patient_button').html('Re-enter Patient');
//        } else {
//            holder.find('#search_patient_button').html('Find Patient');
//        }
}

function populateInputFieldCalllog( fieldEl, data, index, modify ) {
    var value = null;
    if( data && data[index] ) {
        value = data[index];
        //lock field
//            fieldEl.prop('disabled', true);
//            fieldEl.closest('.input-group').find('input').prop('disabled', true);
//            if( fieldEl.hasClass('datepicker') ) {
//                var elementDatepicker = fieldEl.closest('.input-group.date');
//                elementDatepicker.datepicker("remove");
//            }
        disableField(fieldEl,true);
    } else {
        //unlock field
//            fieldEl.prop('disabled', false);
//            fieldEl.closest('.input-group').find('input').prop('disabled', false);
//            if( fieldEl.hasClass('datepicker') ) {
//                var elementDatepicker = fieldEl.closest('.input-group.date');
//                initSingleDatepicker(elementDatepicker);
//            }
        disableField(fieldEl,false);
    }
    //console.log(index+': value='+value);

    if( typeof modify === 'undefined' ){
        modify = true;
    }

    if( modify ) {
        fieldEl.val(value);
    }

    //attache alias
    //if( index == "lastname" || index == "firstname" || index == "middlename" || index == "suffix" ) {
    var statusIndex = index+"Status";
    if( data && statusIndex in data && data[statusIndex] == 'alias' ) {

//                var aliasHtml =
//                    '<span class="input-group-addon">'+
//                        '<input'+
//                            ' type="checkbox" id="oleg_calllogbundle_patienttype_encounter_0_patfirstname_0_alias"'+
//                            ' name="oleg_calllogbundle_patienttype[encounter][0][patfirstname][0][alias]"'+
//                            ' value="1"'+
//                        '>'+
//                        '<label style="margin:0;" for="oleg_calllogbundle_patienttype_encounter_0_patfirstname_0_alias">Alias</label>'+
//                '</span>';

        //show alias with checked checkbox
        var parentEl = fieldEl.parent();
        parentEl.find('.input-group-addon').show();
        parentEl.removeClass('input-group-hidden').addClass('input-group');
        parentEl.find('input[type=checkbox]').prop('checked', true);
    }

    //}

    return value;
}

function populateSelectFieldCalllog( fieldEl, data, index ) {
    var value = null;
    if( data && data[index] ) {
        value = data[index];
        //lock field
        fieldEl.prop('disabled', true);
    } else {
        //unlock field
        fieldEl.prop('disabled', false);
    }
    fieldEl.select2('val',value);
    return value;
}

function processMrnFieldsCalllog( patient, modify, holderId ) {

    var holder = getHolder(holderId);

    if( typeof modify === 'undefined' ){
        modify = true;
    }

    if( patient && patient.mrntype && patient.mrn ) {

        holder.find('.mrntype-combobox').prop('disabled', true);
        holder.find('.mrntype-combobox').select2('val',patient.mrntype);

        holder.find('.patientmrn-mask').prop('disabled', true);
        holder.find('.patientmrn-mask').val(patient.mrn);

    } else {

        holder.find('.mrntype-combobox').prop('disabled', false);

        if( modify ) {
            holder.find('.mrntype-combobox').select2('val', _mrntype_original);
        }

        holder.find('.patientmrn-mask').prop('disabled', false);

        if( modify ) {
            holder.find('.patientmrn-mask').val(null);
        }

    }
}

function editPatientBtn(holderId) {
    disableAllFields(false,holderId);

    //show all alias
    //holder.find('.alias-group').find('.input-group-addon').show();
    //holder.find('.alias-group').find('.input-group-hidden').removeClass('input-group-hidden').addClass('input-group');
    //holder.find('.alias-group').find('input[type=checkbox]').prop('checked', false);
    calllogHideAllAlias(false,false,holderId);
}

function calllogHideAllAlias(hide,clear,holderId) {
    var holder = getHolder(holderId);
    if( hide ) {
        //hide all alias
        holder.find('.alias-group').find('.input-group-addon').hide();
        holder.find('.alias-group').find('.input-group').removeClass('input-group').addClass('input-group-hidden');
    } else {
        //show all alias
        holder.find('.alias-group').find('.input-group-addon').show();
        holder.find('.alias-group').find('.input-group-hidden').removeClass('input-group-hidden').addClass('input-group');
    }
    if( clear ) {
        holder.find('.alias-group').find('input[type=checkbox]').prop('checked', false);
    }
}

function getHolder(holderId) {
    if( holderId ) {
        return $('#'+holderId);
    }
    return $('.calllog-patient-holder');
}
