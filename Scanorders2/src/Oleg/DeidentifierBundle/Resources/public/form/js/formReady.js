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
 * Created by ch3 on 1/19/16.
 */

$(document).ready(function() {

    setNavBar("deidentifier");

    fieldInputMask();

    customCombobox();

    regularCombobox();

    initConvertEnterToTab();

    initDatetimepicker();

    initDeidentifierNavbarSearchMask();

    setOriginalSearchParameters();

    generalConfirmAction();

});

//only used to set generated parameters on the index.html.twig page /generate/
function setOriginalSearchParameters() {

    var dataholder = $('#deidentifier-data-holder');

    //console.log("dataholder.length="+dataholder.length);
    if( dataholder.length == 0 ) {
        return;
    }

    var holder = $('#deidentifier-generate');

    var institution = dataholder.attr("data-institution");
    //console.log("institution="+institution);
    if( institution ) {
        //console.log("set institution="+institution);
        holder.find('.combobox-institution').select2('val',institution);
    }

    var accessionType = dataholder.attr("data-accessionType");
    if( accessionType ) {
        var accessionTypeField = holder.find('.accessiontype-combobox');
        accessionTypeField.select2('val',accessionType);
        setAccessiontypeMask(accessionTypeField,true);
    }

    var accessionNumber = dataholder.attr("data-accessionNumber");
    if( accessionNumber ) {
        //console.log("set accessionNumber="+accessionNumber);
        holder.find('.accession-mask').val(accessionNumber);
    }
}
