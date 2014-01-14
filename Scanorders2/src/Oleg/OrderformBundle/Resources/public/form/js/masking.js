/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/6/14
 * Time: 4:17 PM
 * To change this template use File | Settings | File Templates.
 */

///////////////////// DEFAULT MASKS //////////////////////////
var _mrnplaceholder = "NOMRNPROVIDED-";
var _accplaceholder = "NO\\ACCESSIONPROVIDED-";
var _maskErrorClass = "has-warning"; //"maskerror"

function getMrnDefaultMask() {
    var mrns = [
        //{ "mask": "9[999999999999]" },
        //{ "mask": "9" },
        {"mask": "*[m][m][m][m][m][m][m][m][m][m][m][*]"} //13 total: alfa-numeric leading and ending, plus 11 alfa-numeric and dash in the middle
    ];

    var mask = {
        "mask": mrns
        //"repeat": 13,
        //"greedy": false
    };

    return mask;
}

function getAgeDefaultMask() {
    return "f[9][9]";
}

function getAccessionDefaultMask() {
    var accessions = [
        { "mask": "AA99-f[99999]" },
        { "mask": "A99-f[99999]" }
    ];
    return accessions;
}
///////////////////// END OF DEFAULT MASKS //////////////////////

function fieldInputMask() {

    $.extend($.inputmask.defaults.definitions, {
        'f': {  //masksymbol
            "validator": "[1-9]",
            "cardinality": 1,
            'prevalidator': null
        }
    });

    //not all zeros
//    $.extend($.inputmask.defaults.definitions, {
//        'm': {
//            //"validator": "/^(?!0+$)[A-Z0-9]{13}$/",    //"^\s*$",
//            "validator": "^(?!0+$)[a-zA-Z0-9]{1,13}$",
////            "validator": function (chrs, buffer, pos, strict, opts) {
////                var valExp2 = new RegExp("2[0-5]|[01][0-9]");
////                return valExp2.test(buffer[pos - 1] + chrs);
////            },
//            "cardinality": 1,
//            'prevalidator': null
//        }
//    });

    //any alfa-numeric without leading or ending '-'
    $.extend($.inputmask.defaults.definitions, {
        'm': {
            "validator": "[A-Za-z0-9-]",
            "cardinality": 1,
            'prevalidator': null
        }
    });

    $.extend($.inputmask.defaults, {
        "onincomplete": function(result){makeErrorField($(this),false);},
        "oncomplete": function(){ clearErrorField($(this)); },
        "oncleared": function(){ clearErrorField($(this)); },
        "onKeyValidation": function(result) {
            //console.log(result);
            makeErrorField($(this),false);
        },
        "onKeyDown": function(result) {
            //console.log(result);
            makeErrorField($(this),false);
        },
        placeholder: " ",
        clearMaskOnLostFocus: true
    });

    $(":input").inputmask();

    if( cicle == "new" || cicle == "create" ) {

        $(".accession-mask").inputmask( { "mask": getAccessionDefaultMask() } );
        $(".patientmrn-mask").inputmask( getMrnDefaultMask() );
        //$(".patientmrn-mask").inputmask( { "mask": "*", "repeat": 13, "greedy": false } );
        //$(".patientmrn-mask").inputmask( { "mask": "*" } );

    } else {
        //set mrn for amend
        var mrnkeytypeField = $('.mrntype-combobox').not("*[id^='s2id_']");
        mrnkeytypeField.each( function() {
            setMrntypeMask($(this),false);
        });

        //set accession for amend: do this in selectAjax.js when accession is loaded by Ajax
    }

    $(".patientage-mask").inputmask( { "mask": getAgeDefaultMask() });

    //$(".partname-mask").inputmask( {"mask": "A[A]" });
    //$(".blockname-mask").inputmask( {"mask": "f[9]" });

    accessionTypeListener();
    mrnTypeListener();
    //maskfieldListener();

    console.log($.inputmask.defaults.definitions);
}

//element is check button
function setDefaultMask( element ) {
    maskField = element.closest('.row').find("*[class$='-mask']");

    clearErrorField(element);

    if( maskField.hasClass('patientmrn-mask') ) {
        maskField.inputmask( getMrnDefaultMask() );
    }

    if( maskField.hasClass('accession-mask') ) {
        maskField.inputmask( { "mask": getAccessionDefaultMask() } );
    }

}


function mrnTypeListener() {
    $('.mrntype-combobox').on("change", function(e) {
        //console.log("mrn type change listener!!!");
        setMrntypeMask($(this),true);
    });
}

//elem is a keytype element (select box)
function setMrntypeMask( elem, clean ) {
    //console.log("mrn type changed = " + elem.attr("id") + ", class=" + elem.attr("class") );

    var mrnField = elem.closest('.row').find('.patientmrn-mask');
    var value = elem.select2("val");
    var text = elem.select2("data").text;
    console.log("text=" + text + ", value=" + value);

    //clear input field
    if( clean ) {
        mrnField.val('');
        clearErrorField(mrnField);
    }

    switch( text )
    {
        case "Auto-generated MRN":
            mrnField.inputmask( {"mask": _mrnplaceholder+"9999999999" } );
            var parent = elem.closest('.patientmrn');
            parent.find('#check_btn').trigger("click");
            //console.log('Auto-generated MRN !!!');
            break;
        case "Existing Auto-generated MRN":
            mrnField.inputmask( {"mask": _mrnplaceholder+"9999999999" } );
            break;
        case "New York Hospital MRN":
            mrnField.inputmask( getMrnDefaultMask() );
            break;
        case "De-Identified NYH Tissue Bank Research Patient ID":
            console.log( 'set mrn mask: ' + text );
            //mrnField.inputmask( {"mask": "*[m][m][m][m][m][m][m][m][m][m][m][m][m][m][m][m][m][m][m][m][m][m][m][*]"} );    //25 total: 2 all + 23 m
            mrnField.inputmask( {"mask": "*[m][m][m][m][m][m][m][*]"} );    //25 total: 2 all + 23 m
            break;
        case "De-Identified Personal Educational Slide Set Patient ID":
            mrnField.inputmask( getMrnDefaultMask() );
            break;
        default:
            mrnField.inputmask('remove');
    }
}

//this function is called by getComboboxAccessionType() in selectAjax.js when accession type is populated by ajax
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
    });
}

//elem is a keytype element (select box)
function setAccessiontypeMask(elem,clean) {
    //console.log("accession type changed = " + elem.attr("id") + ", class=" + elem.attr("class") );
    var accField = elem.closest('.row').find('.accession-mask');
    var value = elem.select2("val");
    var text = elem.select2("data").text;
    //console.log("text=" + text + ", value=" + value);

    //clear input field
    if( clean ) {
        accField.val('');
        clearErrorField(accField);
    }

    switch( text )
    {
        case "Auto-generated Accession Number":
            accField.inputmask( {"mask": _accplaceholder+"9999999999" } );
            elem.closest('.accessionaccession').find('#check_btn').trigger("click");
            //console.log('Auto-generated Accession !!!');
            break;
        case "Existing Auto-generated Accession Number":
            accField.inputmask( {"mask": _accplaceholder+"9999999999" } );
            break;
        case "De-Identified Personal Educational Slide Set Specimen ID":
            accField.inputmask( {"mask": ["vib9020-E-*"] } );
            break;
        case "NYH CoPath Anatomic Pathology Accession Number":
            accField.inputmask( {"mask": getAccessionDefaultMask() } );
            break;
        default:
            accField.inputmask('remove');
    }
}

function makeErrorField(element, appendWell) {
    //console.log("make red field id="+element.attr("id")+", class="+element.attr("class"));

    if( element.inputmask("isComplete") && !allZeros(element) ) {
        clearErrorField(element);
        return;
    }

    var value =  element.val().trim();
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
    //console.log("element.val()="+element.val());
    var res = element.val().trim().match(/^[0]+$/);
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

        var parent = element.closest('.row');
        var errorFields = parent.find("."+_maskErrorClass);

        if( fieldName == "partname" ) { //if element is provided, then validate only element's input field. Check parent => accession

            var parent = element.closest('.panel-procedure').find('.accessionaccession');
            //console.log("parent id=" + parent.attr("id") + ", class=" + parent.attr("class") );
            var errorFields = parent.find("."+_maskErrorClass);
            //console.log("count errorFields=" + errorFields.length );

            if( errorFields.length > 0 ) {
                var partname = element.closest('.row').find("*[class$='-mask']");   //find("input").not("*[id^='s2id_']");
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

    if( element.inputmask("hasMaskedValue") && element.inputmask("isComplete") && !allZeros(element) ) {
        //clearErrorField(element);
        return;
    }

    if( !fieldName ) {
        var fieldName = "field marked in red above";
        if( element.hasClass("accession-mask") ) {
            fieldName = "Accession Number";
        }
        if( element.hasClass("patientmrn-mask") ) {
            fieldName = "MRN";
        }
    }

    var errorHtml =
        '<div class="maskerror-added alert alert-danger">' +
            'Please correct the invalid ' + fieldName + '.' +
            '</div>';

    //console.log("element id="+element.attr("id")+", class="+element.attr("class"));

    if( appendWell ) {
        element.after(errorHtml);
    }

    return errorHtml;
}

function changeMaskToNoProvided( combobox, fieldName ) {
    if( fieldName == "mrn" ) {
        var mrnField = combobox.closest('.row').find('.patientmrn-mask');
        mrnField.inputmask( {"mask": _mrnplaceholder+"9999999999" } );
    }
    if( fieldName == "accession" ) {
        var accField = combobox.closest('.row').find('.accession-mask');
        accField.inputmask( {"mask": _accplaceholder+"9999999999" } );
    }
}