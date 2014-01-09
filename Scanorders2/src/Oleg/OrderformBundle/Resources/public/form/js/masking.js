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

function getMrnMask() {
    return "f999999[9]";
}

function getAgeMask() {
    return "f[9][9]";
}

function getAccessionMask() {
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

//    $.extend($.inputmask.defaults.definitions, {
//        'e': {  //masksymbol
//            "validator": "^$",    //"^\s*$",
//            "cardinality": 1,
//            'prevalidator': null
//        }
//    });

    $.extend($.inputmask.defaults, {
        "onincomplete": function(){ makeErrorField($(this),false); },
        "oncomplete": function(){ clearErrorField($(this)); },
        "oncleared": function(){ clearErrorField($(this)); },
        "onKeyValidation": function(result) {
            //console.log(result);
            if( !$(this).inputmask("isComplete") ) {
                makeErrorField($(this),false);
            }
        },
        placeholder: " ",
        clearMaskOnLostFocus: true
    });

    $(":input").inputmask();

    $(".accession-mask").inputmask( { "mask": getAccessionMask() });

    $(".patientmrn-mask").inputmask( { "mask": getMrnMask() } );

    //$(".patientdob-mask").inputmask( {"mask": "mm/dd/yyyy"});

    $(".patientage-mask").inputmask( { "mask": getAgeMask() });

    //$(".partname-mask").inputmask( {"mask": "A[A]" });
    //$(".blockname-mask").inputmask( {"mask": "f[9]" });

    accessionTypeListener();
    mrnTypeListener();

    console.log($.inputmask.defaults.definitions);
}

//element is check button
function setDefaultMask( element ) {
    maskField = element.closest('.row').find("*[class$='-mask']");

    if( maskField.hasClass('patientmrn-mask') ) {
        maskField.inputmask( { "mask": getMrnMask() } );
    }

    if( maskField.hasClass('accession-mask') ) {
        maskField.inputmask( { "mask": getAccessionMask() } );
    }

}

function mrnTypeListener() {

    $('.mrntype-combobox').on("change", function(e) {

        var elem = $(this);
        console.log("mrn type changed = " + elem.attr("id") + ", class=" + elem.attr("class") );

        var mrnField = elem.closest('.row').find('.patientmrn-mask');
        //var value = elem.select2("val");
        var text = elem.select2("data").text;
        console.log("text=" + text);

        //clear input field
        mrnField.val('');
        clearErrorField(mrnField);

        switch( text )
        {
            case "Auto-generated MRN":
                mrnField.inputmask( {"mask": _mrnplaceholder+"9999999999" } );
                var parent = elem.closest('.patientmrn');
                parent.find('#check_btn').trigger("click");
                console.log('Auto-generated MRN !!!');
                break;
            case "Existing pre-generated MRN":
                mrnField.inputmask( {"mask": _mrnplaceholder+"9999999999" } );
                break;
            case "New York Hospital MRN":
                mrnField.inputmask( {"mask": getMrnMask() } );
                break;
            default:
                mrnField.inputmask('remove');
        }


    });

}

function accessionTypeListener() {

    $('.accessiontype-combobox').change(function(e) {
        var elem = $(this);
        console.log("accession type changed = " + elem.attr("id") + ", class=" + elem.attr("class") );

        var accField = elem.closest('.row').find('.accession-mask');
        //var value = elem.select2("val");
        var text = elem.select2("data").text;

        //clear input field
        accField.val('');
        clearErrorField(accField);

        switch( text )
        {
            case "Auto-generated Accession Number":
                accField.inputmask( {"mask": _accplaceholder+"9999999999" } );
                elem.closest('.accessionaccession').find('#check_btn').trigger("click");
                break;
            case "Existing pre-generated Accession Number":
                accField.inputmask( {"mask": _accplaceholder+"9999999999" } );
                break;
            case "De-Identified Personal Educational Slide Set Specimen ID":
                accField.inputmask( {"mask": ["vib9020-E-*"] } );
                break;
            case "NYH CoPath Anatomic Pathology Accession Number":
                accField.inputmask( {"mask": getAccessionMask() } );
                break;
            default:
                accField.inputmask('remove');
        }

    });

}

function makeErrorField(element, appendWell) {
    //console.log("make red field id="+element.attr("id")+", class="+element.attr("class"));

    clearErrorField(element);

    var value =  element.val().trim();
    //console.log("value="+value);
    if( value != "" ) {
        element.parent().addClass("has-error");
        createErrorMessage( element, null, appendWell );
    }

}

function clearErrorField( element ) {
    //console.log("make ok field id="+element.attr("id")+", class="+element.attr("class"));
    element.parent().removeClass("has-error");
    $('.maskerror-added').remove();
}

function validateMaskFields( element, fieldName ) {

    var errors = 0;
    $('.maskerror-added').remove();

    //console.log("element id=" + element.attr("id") + ", class=" + element.attr("class") );
    //console.log("fieldName="+fieldName);

    if( element ) {

        var parent = element.closest('.row');
        var errorFields = parent.find(".has-error");

        if( fieldName == "partname" ) { //if element is provided, then validate only element's input field. Check parent => accession

            var parent = element.closest('.panel-procedure').find('.accessionaccession');
            //console.log("parent id=" + parent.attr("id") + ", class=" + parent.attr("class") );
            var errorFields = parent.find(".has-error");
            //console.log("count errorFields=" + errorFields.length );

            var partname = element.closest('.row').find("*[class$='-mask']");   //find("input").not("*[id^='s2id_']");
            createErrorMessage( partname, "Accession Number above", true );   //create warning well under partname
        }

    } else {
        var errorFields = $(".has-error");
    }

    errorFields.each(function() {
        var elem = $(this).find("*[class$='-mask']");
        //console.log("error id=" + elem.attr("id") + ", class=" + elem.attr("class") );

        //Please correct the invalid accession number
        var errorHtml = createErrorMessage( elem, null, true );

        $('#validationerror').append(errorHtml);

        errors++;
    });


    console.log("number of errors =" + errors );
    return errors;
}

function createErrorMessage( element, fieldName, appendWell ) {

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

    //console.log("inputField id="+inputField.attr("id")+", class="+inputField.attr("class"));

    if( appendWell ) {
        element.after(errorHtml);
    }

    return errorHtml;
}