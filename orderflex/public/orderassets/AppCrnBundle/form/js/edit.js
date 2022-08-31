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
 * Created by App Ivanov on 8/26/2016.
 */

//overwrite function
var matchingPatientForEditBtnClick = function(holderId,formtype) {
    //console.log('matching PatientForEdit BtnClick: holderId='+holderId);

    var patientToPopulate = getCrnPatientToPopulate(holderId);

    var url = Routing.generate('crn_patient_edit',{'id':patientToPopulate.id});
    //alert("url="+url);
    window.location.href = url;
}

//JS: NOT USED
var matchingPatientForEditBtnClick_ORIG = function(holderId,formtype) {
    console.log('matching PatientForEdit BtnClick: holderId='+holderId);

    editPatientBtn(holderId);

    var holder = getHolder(holderId);
    var patientToPopulate = getCrnPatientToPopulate(holderId);
    //console.log('patientToPopulate='+patientToPopulate.id);

    populatePatientInfo(patientToPopulate,null,true,holderId);
    disableAllFields(false,holderId);

    //show edit patient info button
    holder.find('#edit_patient_button').show();
    //hide "No single patient is referenced by this entry or I'll add the patient info later" link
    holder.find('#crnentry-nosinglepatient-link').hide();

    //change the "Find or Add Patient" button title to "Re-enter Patient"
    holder.find('#reenter_patient_button').show();
    holder.find('#search_patient_button').hide();

    //remove and hide matching patients table
    holder.find('#crn-matching-patients-table-'+holderId).remove();
    holder.find('#crn-matching-patients').html('');
    holder.find('#crn-matching-patients').hide();
}



