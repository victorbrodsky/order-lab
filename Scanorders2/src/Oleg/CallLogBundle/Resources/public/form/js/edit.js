/**
 * Created by ch3 on 8/26/2016.
 */

//overwrite function
var matchingPatientForEditBtnClick = function(holderId,formtype) {
    //console.log('matching PatientForEdit BtnClick: holderId='+holderId);

    var patientToPopulate = getCalllogPatientToPopulate(holderId);

    var url = Routing.generate('calllog_patient_edit',{'id':patientToPopulate.id});
    //alert("url="+url);
    window.location.href = url;
}

//JS: NOT USED
var matchingPatientForEditBtnClick_ORIG = function(holderId,formtype) {
    console.log('matching PatientForEdit BtnClick: holderId='+holderId);

    editPatientBtn(holderId);

    var holder = getHolder(holderId);
    var patientToPopulate = getCalllogPatientToPopulate(holderId);
    //console.log('patientToPopulate='+patientToPopulate.id);

    populatePatientInfo(patientToPopulate,null,true,holderId);
    disableAllFields(false,holderId);

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



