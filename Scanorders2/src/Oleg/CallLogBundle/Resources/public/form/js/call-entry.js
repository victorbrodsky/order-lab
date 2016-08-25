/**
 * Created by ch3 on 7/25/2016.
 */


var _patients = [];
var _mrntype_original = null;

function initCallLogPage() {
    listnereAccordionMasterPatientParent();
}

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

function findCalllogPatient(holderId,formtype) {

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

    //var currentUrl = window.location.href;

    //ajax
    var url = Routing.generate('calllog_search_patient');
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: true,
        data: {mrntype: mrntype, mrn: mrn, dob: dob, lastname: lastname, firstname: firstname, formtype: formtype },
    }).success(function(data) {
        populatePatientsInfo(data,searchedStr,holderId);
    });

}

function populatePatientsInfo(patients,searchedStr,holderId) {

    var holder = getHolder(holderId);

    //var patLen = patients.length;
    var patLen = getPatientsLength(patients);
    console.log('patLen='+patLen);

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
    console.log("_patients:");
    console.log(_patients);

    var processed = false;

    if( patLen == 1 ) {

        //var patient = patients[0];
        var patient = getFirstPatient(patients);
        if (patient == null) {
            alert("No first patient found in the patient array");
        }
        //console.log('single found patient id=' + patient.id);

        var patMergedLen = getMergedPatientInfoLength(patient);
        console.log('patMergedLen='+patMergedLen);

        if( patMergedLen == 0 && processed == false ) {

            populatePatientInfo(patient, null, true, holderId);
            disableAllFields(true, holderId);

            //show edit patient info button
            holder.find('#edit_patient_button').show();
            //hide "No single patient is referenced by this entry or I'll add the patient info later" link
            holder.find('#callentry-nosinglepatient-link').hide();

            //change the "Find or Add Patient" button title to "Re-enter Patient"
            holder.find('#reenter_patient_button').show();
            holder.find('#search_patient_button').hide();
            holder.find('#addnew_patient_button').hide();

            processed = true;
        }

    }

    if( patLen == 0 && processed == false ) {

        //console.log("No matching patient records found.");
        //"No matching patient records found." and unlock fields
        holder.find('#calllog-danger-box').html("No matching patient records found"+searchedStr+".");
        holder.find('#calllog-danger-box').show();
        populatePatientInfo(null,true,false,holderId);
        disableAllFields(false,holderId);

        //un-hide/show a button called "Add New Patient Registration"
        holder.find('#addnew_patient_button').show();

    }

    if( patLen >= 1 && processed == false ) {

        //console.log("show table with found patients");
        //show table with found patients
        //populatePatientInfo(patients[0],null);
        populatePatientInfo(null,null,false,holderId);
        disableAllFields(false,holderId);

        createPatientsTableCalllog(patients,holderId);

    }

    if( processed == false ){
        console.log("Logical error. Search patients not processed. patLen="+patLen);
    }

}

function createPatientsTableCalllog( patients, holderId ) {

    var holder = getHolder(holderId);

    //Matching Patients
    var matchingPatientsHtml = '<div class="table-responsive"><table id="calllog-matching-patients-table-'+holderId+'" class="table table-bordered">' +
        '<thead><tr>' +
        '<th>&nbsp;</th>'+
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

    //for( var i = 0; i < patients.length; i++ ) {
    for( var i in patients ) {
        if (patients.hasOwnProperty(i)) {

            var patient = patients[i];
            //console.log('patient id='+patient.id);

            //var mergedPatientsInfoLength = getMergedPatientInfoLength(patient['mergedPatientsInfo']);
            //console.log('mergedPatientsInfoLength='+mergedPatientsInfoLength);
            //console.log('patient.mergedPatientsInfo:');
            //console.log(patient.mergedPatientsInfo);
            //var mergedPatientsInfoLength = (mergedPatientsInfoLength - 1);
            //var hasMergedPatients = "";
            //if( patient.mergedPatientsInfo && mergedPatientsInfoLength > 0 ) {
            //    hasMergedPatients = '<br><span class="label label-info">Has ' + mergedPatientsInfoLength + ' Merged Patients</span>';
            //}

            var masterId = patient['masterPatientId'];  //i+'-'+holderId
            //console.log('masterId='+masterId);

            matchingPatientsHtml = matchingPatientsHtml + constractPatientInfoRow(patient, masterId, "master");

            matchingPatientsHtml = matchingPatientsHtml + constractMergedPatientInfoRow(patient, masterId);
        }
    }

    matchingPatientsHtml = matchingPatientsHtml + "</tbody></table></div>";

    matchingPatientsHtml = matchingPatientsHtml +
        '<p data-toggle="tooltip" title="Please select the patient"><button type="button"'+
        //' id="matchingPatientBtn-'+holderId+'"'+
        ' class="btn btn-lg span4 matchingPatientBtn" align="center"'+
        ' disabled'+
        ' onclick="matchingPatientBtnClick(\''+holderId+'\')"'+
        '>Select Patient</button></p>';

    matchingPatientsHtml = matchingPatientsHtml +
            '<div id="calllog-select-patient-danger-box" class="alert alert-danger" style="display: none; margin: 5px;"></div>';

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
function constractPatientInfoRow( patient, masterId, type ) {
    //to test use: http://www.bootply.com/4lsCo5q101
    var patientsHtml = "";

    if( type == "master" ) {
        patientsHtml += '<tr id="'+patient.id+'" class="clickable-row">';
        patientsHtml += '<td>';
        if( patient['masterPatientId'] ) {
            patientsHtml += '<button type="button" class="btn btn-default btn-xs" onclick="clickMasterPatientBtn(this);" id="' + masterId + '">';
            patientsHtml += '<span class="glyphicon glyphicon-plus-sign"></span></button>';
        }
        patientsHtml += '</td>';
    } else {
        //masterId
        patientsHtml += '<tr id="'+patient.id+'" class="clickable-row collapseme'+masterId+' collapse out" style="background: #A9A9A9;">';
        patientsHtml += '<td>&nbsp;&nbsp;<span class="glyphicon glyphicon-link"></span></td>';
    }

    patientsHtml +=
        '<td id="calllog-patientid-'+patient.id+'">'+
        patient.patientInfoStr +
        patient.mrn+' ('+patient.mrntypestr+')'+
        //hasMergedPatients +
        '</td>'+
        '<td>'+patient.lastname+'</td>'+
        '<td>'+patient.firstname+'</td>'+
        '<td>'+patient.middlename+'</td>'+
        '<td>'+patient.suffix+'</td>'+
        '<td>'+patient.sexstr+'</td>'+
        '<td>'+patient.dob+'</td>'+
        '<td>'+ //class="rowlink-skip"
            patient.contactinfo+
        '</td>'+
        '</tr>';

    return patientsHtml;
}
function constractMergedPatientInfoRow( patient, masterId ) {
    var mergedPatientsHtml = "";
    var mergedPatients = patient['mergedPatientsInfo'];
    for( var mergedId in mergedPatients ) {
        if( mergedPatients.hasOwnProperty(mergedId) ) {
            //alert("Key is " + mergedId + ", value is" + targetArr[mergedId]);
            //count = count + mergedPatients[mergedId]['patientInfo'].length;
            var patientsInfo = mergedPatients[mergedId]['patientInfo'];
            for( var index in patientsInfo ) {
                var patientInfo = patientsInfo[index];
                console.log('merged Patient ID=' + patientInfo['id']);
                //console.log(patientInfo);
                //masterId = masterId + "-" + patientInfo['id'];
                mergedPatientsHtml = mergedPatientsHtml + constractPatientInfoRow(patientInfo, masterId, "alert alert-info");
            }
        }
    }
    return mergedPatientsHtml;
}

function listnereAccordionMasterPatientParent() {
    //testing
}
function clickMasterPatientBtn(btn) {
    var id = $(btn).attr('id');

    if( $(".collapseme"+id).hasClass("out") ) {
        //console.log('show');
        $(".collapseme"+id).show();
        $(".collapseme"+id).removeClass('out').addClass('in');
        $(btn).parent().find("span.glyphicon").removeClass("glyphicon-plus-sign").addClass("glyphicon-minus-sign");
    } else {
        //console.log('hide');
        $(".collapseme"+id).hide();
        $(".collapseme"+id).removeClass('in').addClass('out');
        $(btn).parent().find("span.glyphicon").removeClass("glyphicon-minus-sign").addClass("glyphicon-plus-sign");
    }
}

function getMergedPatientInfoLength( patient ) {
    if( patient['mergedPatientsInfo'] ) {
        var mergedPatientsInfo = patient['mergedPatientsInfo'][patient.id]['patientInfo'];
        return getPatientsLength(mergedPatientsInfo);
    } else {
        return 0;
    }
}
function getPatientsLength( patients ) {
    var count = 0;
    for( var k in patients ) {
        if( patients.hasOwnProperty(k) ) {
            //console.log("Key is " + k + ", value id is " + patients[k].id);
            count++;
        }
    }
    return count;
}
function getFirstPatient(patients) {
    for( var k in patients ){
        if( patients.hasOwnProperty(k) ) {
            //console.log("Key is " + k + ", value id is " + patients[k].id);
            return patients[k];
        }
    }
    return null;
}

var matchingPatientBtnClick = function(holderId) {
    //console.log('holderId='+holderId);
    var holder = getHolder(holderId);

    //var index = holder.find('#calllog-matching-patients-table-'+holderId).find('.active').attr('id');
    //console.log('id index='+index);
    ////remove holderId from index
    //index = index.replace("-"+holderId, "");
    //console.log('index='+index);

    //var patientToPopulate = _patients[index];
    //console.log('patientToPopulate='+patientToPopulate.id);
    //console.log(patientToPopulate);
    //console.log('patientToPopulate.masterPatientId='+patientToPopulate.masterPatientId);

    //check for master patient
    //if( patientToPopulate.masterPatientId ) {
    //    //console.log('reset to patientToPopulate.masterPatientId='+patientToPopulate.masterPatientId);
    //    //patientToPopulate = patientToPopulate.masterPatientId;
    //    //find index by patient id
    //    index = $('#calllog-patientid-'+patientToPopulate.masterPatientId).closest('tr').attr('id');
    //    index = index.replace("-"+holderId, "");
    //    //console.log('new index='+index);
    //    patientToPopulate = _patients[index];
    //}
    var patientToPopulate = getCalllogPatientToPopulate(holderId);
    //console.log('patientToPopulate='+patientToPopulate.id);

    populatePatientInfo(patientToPopulate,null,true,holderId);
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
//
var getCalllogPatientToPopulate = function(holderId) {
    //console.log("original replace Calllog PatientToPopulate");
    var holder = getHolder(holderId);
    var index = holder.find('#calllog-matching-patients-table-'+holderId).find('.active').attr('id');
    //console.log('patient id to populate='+index);
    //remove holderId from index
    //index = index.replace("-"+holderId, "");
    //console.log('index='+index);

    //find patient with id from _patients array
    var patientToPopulate = getPatientByIdFromPatients(index,_patients);

    //for call_entry return master record instead of the actual clicked patient record
    var masterPatientId = patientToPopulate['masterPatientId'];
    //console.log("Replace by masterPatientId=" + masterPatientId);
    if( masterPatientId ) {
        //console.log("masterPatientId=" + masterPatientId);
        patientToPopulate = getPatientByIdFromPatients(masterPatientId,_patients);
    }

    return patientToPopulate;
}
function getPatientByIdFromPatients(index,patients) {
    //console.log("Start: get patients by index="+index);
    for( var k in patients ) {
        if( patients.hasOwnProperty(k) ) {
            var patient = patients[k];
            var masterPatientId = patient['masterPatientId'];
            //console.log("Key is " + k + ", value id is " + patient.id);
            //console.log("masterPatientId=" + masterPatientId);

            //patient is a master patient or the patient without merged records
            if( k == index ) {
                return patients[k];
            }

            //if( patient['mergedPatientsInfo'] && patient['mergedPatientsInfo'].length > 0 ) {
            if( masterPatientId ) {
                var mergedPatients = patient['mergedPatientsInfo'][masterPatientId]['patientInfo'];
                //console.log("check merged patient");
                for( var mergedIndex in mergedPatients ) {
                    //console.log("mergedIndex="+mergedIndex);
                    if( mergedPatients.hasOwnProperty(mergedIndex) ) {
                        if( mergedIndex == index ) {
                            return mergedPatients[mergedIndex];
                        }
                    }
                }
            }
            //else {
            //    if( k == index ) {
            //        return patients[k];
            //    }
            //}
        }
    }
    return null;
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
