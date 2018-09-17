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
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/6/14
 * Time: 4:17 PM
 * To change this template use File | Settings | File Templates.
 */

///////////////////// DEFAULT MASKS //////////////////////////
var _mrnplaceholder = "NOMRNPROVIDED-";
var _accplaceholder = "NOACCESSIONIDPROVIDED-";
var _maskErrorClass = "has-warning"; //"maskerror"
var _repeatBig = 25;
var _repeatSmall = 13;

function getMrnDefaultMask() {
    var mrns = [
        //{ "mask": "9[999999999999]" },
//        { "mask": "9" },
        //{ "mask": "m" },
//        {"mask": "*[m][m][m][m][m][m][m][m][m][m][m][*]"} //13 total: alfa-numeric leading and ending, plus 11 alfa-numeric and dash in the middle
        { "mask": getRepeatMask(_repeatSmall,"m") }
    ];

    var mask = {
        "mask": mrns
//        "repeat": _repeatSmall,
//        "greedy": false
    };

    return mask;
}

function getAgeDefaultMask() {
    return "f[9][9]";
}

function getAccessionDefaultMask() {
    //console.log('get default accession mask');
    var accessions = [
//        { "mask": "AA99-f[9][9][9][9][9]" },
//        { "mask": "A99-f[9][9][9][9][9]" }
        { "mask": "AAf9-f[9][9][9][9][9]" }, //SS10 but not SS00
        { "mask": "Af9-f[9][9][9][9][9]" }   //S10 but not S0
    ];
    return accessions;
}
///////////////////// END OF DEFAULT MASKS //////////////////////

//holder - element holding all fields to apply masking
function fieldInputMask( holder ) {

    console.log("masking.js: field Input Mask");

    Inputmask.extendDefinitions({
        'f': {  //masksymbol
            "validator": "[1-9]",
            "cardinality": 1,
            'prevalidator': null
        }
    });

    //any alfa-numeric without leading or ending '-'
    Inputmask.extendDefinitions({
        "m": {
            "validator": "[A-Za-z0-9-]",
            "cardinality": 1,
            'prevalidator': null
        }
    });

    Inputmask.extendDefinitions({
        "n": {
            "validator": "[0-9( )+,#ex//t-]",
            "cardinality": 1,
            'prevalidator': null
        }
    });

    Inputmask.extendDefaults({
        "onincomplete": function(result){
            makeErrorField($(this),false);
        },
        "oncomplete": function(){ clearErrorField($(this)); },
        "oncleared": function(){ clearErrorField($(this)); },
        "onKeyValidation": function(result) {
            makeErrorField($(this),false);
        },
        "onKeyDown": function(result) {
            makeErrorField($(this),false);
        },
        placeholder: " ",
        clearMaskOnLostFocus: true  //clear mask after hovering over the field
    });

    if( !holder || typeof holder === 'undefined' || holder.length == 0 ) {
        var maskField = $(":input");
        maskField.inputmask();
    } else {
        var maskField = holder.find(":input");
        maskField.inputmask();

    }

    //console.log("cycle="+cycle);
    if( cycle == "new" || cycle == "create" ) {

        if( !holder || typeof holder === 'undefined' || holder.length == 0 ) {
            //console.log("Set default mask for all");
            $(".accession-mask").inputmask( { "mask": getAccessionDefaultMask() } );
            $(".patientmrn-mask").inputmask( getMrnDefaultMask() );

        } else {
            //console.log("Set default mask for holder");
            //console.log(holder);
            holder.find(".accession-mask").inputmask( { "mask": getAccessionDefaultMask() } );
            holder.find(".patientmrn-mask").inputmask( getMrnDefaultMask() );

        }

    } else {

        //set mrn for amend
        if( !holder || typeof holder === 'undefined' || holder.length == 0 ) {
            var mrnkeytypeField = $('.mrntype-combobox').not("*[id^='s2id_']");
        } else {
            var mrnkeytypeField = holder.find('.mrntype-combobox').not("*[id^='s2id_']");
        }
        mrnkeytypeField.each( function() {
            setMrntypeMask($(this),false);
        });

        //set accession for amend: do this in selectAjax.js when accession is loaded by Ajax
    }

    $(".patientage-mask").inputmask( { "mask": getAgeDefaultMask() });

    //masking for datepicker. This will overwrite datepicker format even if format is mm/yyyy
    //$(".datepicker").inputmask( "mm/dd/yyyy" );
    //fix to not select the first character on tab/enter pressing
    $(".datepicker").on('focus', function (e) {
        //console.log("datepicker on focus mask");
        $(this).inputmask("mm/dd/yyyy");
    });

    //$('.phone-mask').inputmask("mask", {"mask": "+9 (999) 999-9999"});
    $('.phone-mask').inputmask("mask", {
        "mask": "[n]", "repeat": 50, "greedy": false
    });

    //$('.email-mask').inputmask('Regex', { regex: "[a-zA-Z0-9._%-]+@[a-zA-Z0-9-]+\\.[a-zA-Z]{2,4}" });

    $('.digit-mask-seven').inputmask("mask", {
        "mask": "9", "repeat": 7, "greedy": false
    });

    accessionTypeListener();
    mrnTypeListener();

}

//element is check button
function setDefaultMask( btnObj ) {

    clearErrorField(btnObj.btn);

    if( btnObj.name == "patient" ) {
        //console.log("Set default mask for MRN");
        btnObj.typeelement.inputmask( getMrnDefaultMask() );
    }

    if( btnObj.name == "accession" ) {
        //console.log("Set default mask for Accession");
        btnObj.typeelement.inputmask( { "mask": getAccessionDefaultMask() } );
    }

}


function mrnTypeListener() {
    //console.log("mrn Type Listener");
    $('.mrntype-combobox').on("change", function(e) {
        //console.log("mrn type change listener!!!");
        setMrntypeMask($(this),true);

        setTypeTooltip($(this));
    });
}


function getMrnAutoGenMask() {
    var placeholderStr = getCleanMaskStr( _mrnplaceholder );
    var mask = {"mask": placeholderStr+"9999999999999" };
    return mask;
}

//elem is a keytype element (select box)
function setMrntypeMask( elem, clean ) {
    //console.log("mrn type changed = " + elem.attr("id") + ", class=" + elem.attr("class") );

    var mrnField = getKeyGroupParent(elem).find('.patientmrn-mask');
    //printF(mrnField,"mrnField=");
    var value = elem.select2("val");
    //console.log("value=" + value);
    var text = elem.select2("data").text;
    console.log("text=" + text + ", value=" + value);

    //clear input field
    if( clean ) {
        mrnField.val('');
        clearErrorField(mrnField);
    }

    //mrnField.inputmask('remove');

    switch( text )
    {
        case "Auto-generated MRN":
            mrnField.inputmask( getMrnAutoGenMask() );
            var checkbtn = elem.closest('.patientmrn').find('#check_btn');
            var inputValue = getButtonParent(elem).find('.keyfield').val();
            if( checkbtn.hasClass('checkbtn') && inputValue == '' ) {   //don't press check if input value is set
                checkbtn.trigger("click");
            }
            //console.log('Auto-generated MRN !!!');
            break;
        case "Existing Auto-generated MRN":
            mrnField.inputmask( getMrnAutoGenMask() );
            break;
        case "New York Hospital MRN":
        case "Epic Ambulatory Enterprise ID Number":
        case "Weill Medical College IDX System MRN":
        case "Uptown Hospital ID":
        case "NYH Health Quest Corporate Person Index":
        case "New York Downtown Hospital":
            mrnField.inputmask( getMrnDefaultMask() );
            break;
        case "California Tumor Registry Patient ID":
        case "Specify Another Patient ID Issuer":
        case "De-Identified NYH Tissue Bank Research Patient ID":
            var repeatStr = getRepeatMask(_repeatBig,"m");
            mrnField.inputmask( { "mask": repeatStr } );
            break;
        case "De-Identified Personal Educational Slide Set Patient ID":
            var placeholderStr = user_name+"-EMRN-";
            var repeatmrn = getRepeatNum( placeholderStr, _repeatBig );
            var placeholderStr = getCleanMaskStr( placeholderStr );
            var repeatStr = getRepeatMask(repeatmrn,"m");
            mrnField.inputmask( { "mask": placeholderStr+repeatStr } );
            break;
        case "De-Identified Personal Research Project Patient ID":
            var placeholderStr = user_name+"-RMRN-";
            var repeatmrn = getRepeatNum( placeholderStr, _repeatBig );
            var placeholderStr = getCleanMaskStr( placeholderStr );
            var repeatStr = getRepeatMask(repeatmrn,"m");
            mrnField.inputmask( { "mask": placeholderStr+repeatStr } );
            break;
        case "Enterprise Master Patient Index":
            mrnField.inputmask('remove');
        default:
            mrnField.inputmask('remove');
    }
}

//this function is called by getComboboxAccessionType() in selectAjax.js when accession type is initially populated by ajax
function setAccessionMask() {
    var acckeytypeField = $('.accessiontype-combobox').not("*[id^='s2id_']");
    acckeytypeField.each( function() {
        setAccessiontypeMask($(this),false);
    });
}

function accessionTypeListener() {
    $('.accessiontype-combobox').on("change", function(e) {
        //console.log("accession type listener!!!");
        setAccessiontypeMask($(this),true);

        //enable optional_button for single form
        if( orderformtype == "single" ) {
            var accTypeText = $(this).select2('data').text;
            if( accTypeText == 'TMA Slide' ) {
                $("#optional_button").hide();
            } else {
                $("#optional_button").show();
            }
            
            if( accTypeText == 'Auto-generated Accession Number' ) {
                //console.log("click on order info");
                //checkFormSingle($('#optional_button'));
                if( !$('#message_param').is(':visible') ) {
                    $('#next_button').trigger("click");
                }
                $('#optional_button').trigger("click");
            }
        }

        setTypeTooltip($(this));

    });
}

function getAccessionAutoGenMask() {
    var placeholderStr = getCleanMaskStr( _accplaceholder );
    var mask = {"mask": placeholderStr+"9999999999999" };
    return mask;
}

//elem is a keytype element (select box)
function setAccessiontypeMask(elem,clean) {
    //console.log("Accession type changed = " + elem.attr("id") + ", class=" + elem.attr("class") );

    var accField = getKeyGroupParent(elem).find('.accession-mask');
    //printF(accField,"Set Accession Mask:");
    //console.log(accField);

    //var value = elem.select2("val");
    //console.log("value=" + value);
    if( elem.hasClass("combobox") ) { //&& elem.select2("data")
        var text = elem.select2("data").text;
    } else {
        var text = elem.val();
    }
    //console.log("text=" + text);

    //clear input field
    if( clean ) {
        //console.log("clean accession: value=" + value);
        accField.val('');
        clearErrorField(accField);
    }

    swicthMaskAccessionTypeText(elem,accField,text);
}

function swicthMaskAccessionTypeText(elem,accField,text) {
    switch( text )
    {
        case "Auto-generated Accession Number":
            accField.inputmask( getAccessionAutoGenMask() );
            if( elem ) {
                var checkbtn = elem.closest('.accessionaccession').find('#check_btn');
                var inputValue = getButtonParent(elem).find('.keyfield').val();
                //console.log("in value="+inputValue);
                if( checkbtn.hasClass('checkbtn') && inputValue == '' ) {
                    checkbtn.trigger("click");
                }
            }
            //console.log('Auto-generated Accession !!!');
            //printF(checkbtn,"checkbtn to click:");
            break;
        case "Existing Auto-generated Accession Number":
            accField.inputmask( getAccessionAutoGenMask() );
            break;
        case "NYH CoPath Anatomic Pathology Accession Number":
            accField.inputmask( {"mask": getAccessionDefaultMask() } );
            break;
        case "De-Identified Personal Educational Slide Set Specimen ID":
            var placeholderStr = user_name+"-E-";
            var repeatnum = getRepeatNum( placeholderStr, _repeatBig );
            var placeholderStr = getCleanMaskStr( placeholderStr );
            var repeatStr = getRepeatMask(repeatnum,"m");
            accField.inputmask( { "mask": placeholderStr+repeatStr } );
            break;
        case "De-Identified Personal Research Project Specimen ID":
            var placeholderStr = user_name+"-R-";
            var repeatnum = getRepeatNum( placeholderStr, _repeatBig );
            var placeholderStr = getCleanMaskStr( placeholderStr );
            var repeatStr = getRepeatMask(repeatnum,"m");
            accField.inputmask( { "mask": placeholderStr+repeatStr } );
            break;
        case "De-Identified NYH Tissue Bank Research Specimen ID":
        case "California Tumor Registry Specimen ID":
        case "Specify Another Specimen ID Issuer":
        case "TMA Slide":
            var repeatStr = getRepeatMask(_repeatBig,"m");
            accField.inputmask( { "mask": repeatStr } );
            break;
        case "Deidentifier ID":
            //console.log('Deidentifier ID accession type');
            var placeholderStr = "DID-";
            var repeatnum = getRepeatNum( placeholderStr, _repeatBig );
            var placeholderStr = getCleanMaskStr( placeholderStr );
            var repeatStr = getRepeatMask(repeatnum,"m");
            accField.inputmask( { "mask": placeholderStr+repeatStr } );
            break;
        default:
            //console.log('default => remove');
            //console.log(accField);
            accField.inputmask('remove');
    }
}

function noMaskError( element ) {
    //console.log( "complete="+ element.inputmask("isComplete")+", !allZeros="+!allZeros(element) );

    var keytypeText = getKeyGroupParent(element).find('.accessiontype-combobox').select2('data').text;

    //console.log( "no mask error: keytypeText="+ keytypeText );

     if( keytypeText == "NYH CoPath Anatomic Pathology Accession Number" && element.hasClass('accession-mask') ||
         element.hasClass('datepicker') ||
         element.hasClass('patientage-mask') ||
         //element.hasClass('phone-mask') ||
         element.hasClass('email-mask')
     ) {  //regular mask + non zero mask

         if( !allZeros(element) && element.inputmask("isComplete") ) {
             return true;
         } else {
             return false;
         }

    } else {   //non zero mask only

         if( !allZeros(element) ) {
             return true;
         } else {
             return false;
         }

    }
}

function makeErrorField(element, appendWell) {
    //console.log("make red field id="+element.attr("id")+", class="+element.attr("class"));

    if( noMaskError(element) ) {
        clearErrorField(element);
        return;
    }

    var value =  trimWithCheck(element.val());
    //console.log("error: value="+value);
    if( value != "" ) {
        element.parent().addClass(_maskErrorClass);
        createErrorMessage( element, null, appendWell );
    }

}

function clearErrorField( element ) {

    //check if not all zeros
    if( allZeros(element) ) {
        //console.log("all zeros!");
        return;
    }

    //console.log("make ok field id="+element.attr("id")+", class="+element.attr("class"));
    element.parent().removeClass(_maskErrorClass);
    $('.maskerror-added').remove();
}

function allZeros(element) {

    if( !element.inputmask("hasMaskedValue") ) {
        return false;
    }

    //console.log("element.val()="+element.val());
    //printF(element,"all zeros? :")
    var res = trimWithCheck(element.val());
    var res = res.match(/^[0]+$/);
    //console.log("res="+res);
    if( res ) {
        //console.log("all zeros!");
        return true;
    }
    return false;
}

function validateMaskFields( element, fieldName ) {

    var errors = 0;
    $('.maskerror-added').remove();

    //console.log("validate mask fields: fieldName="+fieldName);

    if( element ) {

        //console.log("validate mask fields: element id=" + element.attr("id") + ", class=" + element.attr("class") );

        var parent = getKeyGroupParent(element);
        var errorFields = parent.find("."+_maskErrorClass);

        if( fieldName == "partname" ) { //if element is provided, then validate only element's input field. Check parent => accession

            var parent = element.closest('.panel-procedure').find('.accessionaccession');
            //console.log("parent id=" + parent.attr("id") + ", class=" + parent.attr("class") );
            var errorFields = parent.find("."+_maskErrorClass);
            //console.log("count errorFields=" + errorFields.length );

            if( errorFields.length > 0 ) {
                var partname = getKeyGroupParent(element).find("*[class$='-mask']");   //find("input").not("*[id^='s2id_']");
                createErrorMessage( partname, "Accession Number above", true );   //create warning well under partname
            }
        }

    } else {
        var errorFields = $("."+_maskErrorClass);
    }

    errorFields.each(function() {
        var elem = $(this).find("*[class$='-mask']");
        //console.log("error id=" + elem.attr("id") + ", class=" + elem.attr("class") );

        //Please correct the invalid accession number
        var errorHtml = createErrorMessage( elem, null, true );

        $('#validationerror').append(errorHtml);

        errors++;
    });


    //console.log("number of errors =" + errors );
    return errors;
}

function createErrorMessage( element, fieldName, appendWell ) {

    if( noMaskError(element) ) {
        return;
    }

    var extraStr = "";

    if( !fieldName ) {
        var fieldName = "field marked in red above";
        if( element.hasClass("accession-mask") ) {
            fieldName = "Accession Number";
            extraStr =  "Valid accession numbers must start with up to two letters followed by two digits, then followed by up to six digits with no leading zeros (e.g. SC14-231956). " +
                        "The CoPath accession number cannot contain any spaces or non-alphanumeric characters aside from the dash. " +
                        "To enter a non-CoPath Accession Number, please select the corresponding accession type above.";
        }
        if( element.hasClass("patientmrn-mask") ) {
            fieldName = "MRN";
        }
    }

    var errorHtml =
        '<div class="maskerror-added alert alert-danger">' +
            'Please correct the invalid ' + fieldName + '.' + extraStr +
            '</div>';

    //console.log("append to element id="+element.attr("id")+", class="+element.attr("class") + ", appendWell="+appendWell);

    //always append error well for datepicker
    if( element.hasClass('datepicker') ) {
        appendWell = true;
        element = element.closest('.input-group.date');
        var len = element.closest('.row').find('.maskerror-added').length;
        //console.log('length='+len );
        if( len > 0 ) {
            appendWell = false;
        }
    }

    if( appendWell ) {
        element.after(errorHtml);
    }

    return errorHtml;
}

function changeMaskToNoProvided( combobox, fieldName ) {
    if( fieldName == "mrn" ) {
        var mrnField = getKeyGroupParent(combobox).find('.patientmrn-mask');
        mrnField.inputmask( getMrnAutoGenMask() );
    }
    if( fieldName == "accession" ) {
        var accField = getKeyGroupParent(combobox).find('.accession-mask');
        //printF(accField,"change to noprovided: ");
        accField.inputmask( getAccessionAutoGenMask() );
    }
}

function getCleanMaskStr( str) {
    //console.log("str="+str);

    var defarr = Inputmask.extendDefinitions;   //$.inputmask.defaults.definitions;

    for( var index in defarr ) {
        index = trimWithCheck(index);
        if( index != "*" ) {
            //console.log( "index="+index);
            var replaceValue = "\\\\"+index;
            var regex = new RegExp( index, 'g' );
            str = str.replace(regex, replaceValue);
        }

    }

    //console.log( "str="+str);
    return str;
}

function getRepeatNum( placeholderStr, rnum ) {
    var origLength = placeholderStr.length;
    //console.log("origLength=" + origLength);
    var res = rnum - origLength;
    //console.log("res=" + res);
    return res;
}

//allsame - if true: use * as the first and last masking characters (no leading and ending dashes)
function getRepeatMask( repeat, char, allsame ) {
    if( allsame ) {
        var repeatStr = char;
    } else {
        var repeatStr = "*";
        repeat = repeat - 1;
    }

    for (var i=1; i<repeat; i++ ) {
        repeatStr = repeatStr + char;
    }

    if( allsame ) {
        //
    } else {
        repeatStr = repeatStr + "*";
    }

    return repeatStr;
}

//elem: button, combobox (keytype) or input field
function getKeyGroupParent(elem) {

    if( typeof orderformtype === 'undefined' ) {
        orderformtype = null;
    }

    //printF(elem, orderformtype+": @@@@@@@@@@@@@ Get parent for element:");
    if( orderformtype == "single" && elem.attr('class').indexOf("mrn") == -1 ) {
        //console.log('find by class singlemessage');
        var parent = $('.singlemessage');
    } else if( orderformtype == "deidentifier" ) {
        //console.log('find by class accession-holder');
        var parent = elem.closest('.accession-holder');
    } else if( orderformtype == "calllog" ) {
        //console.log('find by class calllog-patient-holder);
        var parent = elem.closest('.calllog-patient-holder');
    }
    else {
        //console.log('find by class row');
        var parent = elem.closest('.row');
    }

    return parent;
}

//elem is a keytype (combobox)
function getButtonParent(elem) {

    var parent = elem.closest('.row');


    if( orderformtype == "single") {
        if( elem.hasClass('mrntype-combobox') ) {
            var parent = $('#patient_0');
        }
        if( elem.hasClass('accessiontype-combobox') ) {
            var parent = $('#accession-single');
        }
        if( elem.hasClass('partbtn') ) {
            var parent = $('#part-single');
        }
        if( elem.hasClass('blockbtn') ) {
            var parent = $('#block-single');
        }
    }

    return parent;
}

