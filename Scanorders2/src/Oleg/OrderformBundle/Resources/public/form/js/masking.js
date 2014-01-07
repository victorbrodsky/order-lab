/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/6/14
 * Time: 4:17 PM
 * To change this template use File | Settings | File Templates.
 */


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
        "onincomplete": function(){makeErrorField($(this));},
        "oncomplete": function(){makeOKField($(this));},
        placeholder: "",
        clearMaskOnLostFocus: true
    });

    $(":input").inputmask();

    var accessions = [
        { "mask": "AA99-f[99999]" },
        { "mask": "A99-f[99999]" },
        { "mask": "NOACCESSIONPROVIDED-999999999f"}
    ];

    $(".accession-mask").inputmask( {"mask": accessions });

    $(".patientmrn-mask").inputmask( {"mask": ["f999999[9]", "NOMRNPROVIDED-999999999f"] } );

    //$(".patientdob-mask").inputmask( {"mask": "mm/dd/yyyy"});

    $(".patientage-mask").inputmask( {"mask": "f[9][9]" });

    //$(".partname-mask").inputmask( {"mask": "A[A]" });
    //$(".blockname-mask").inputmask( {"mask": "f[9]" });

    accessionTypeListener();
}

function accessionTypeListener() {

    $('.accessiontype-combobox').change(function(e) {
        var elem = $(this);
        console.log("accession type changed = " + elem.attr("id") + ", class=" + elem.attr("class") );

        var accField = elem.closest('.row').find('.accession-mask');
        accField.inputmask( {"mask": ["99", "NOACCESSIONPROVIDED-999999999f"] } );

    });

}


function makeErrorField(element) {
    //console.log("make red field id="+element.attr("id")+", class="+element.attr("class"));
    var value =  element.val().trim();
    //console.log("value="+value);
    if( value != "" ) {
        element.parent().addClass("has-error");
    } else {
        makeOKField(element);
    }
}

function makeOKField( element ) {
    //console.log("make ok field id="+element.attr("id")+", class="+element.attr("class"));
    element.parent().removeClass("has-error");
}

function validateMaskFields( element ) {

    var errors = 0;
    $('.maskerror-added').remove();

    if( element ) { //if element is provided, then validate only element's input field
        //var errorFields = $(".has-error");
        //get input field with partial id "-mask"
        var parent = element.closest('.row');
        var errorFields = parent.find(".has-error");
    } else {
        var errorFields = $(".has-error");
    }

    errorFields.each(function() {
        var elem = $(this).find('input');
        //console.log("error id=" + elem.attr("id") + ", class=" + elem.attr("class") );

        var fieldName = "field marked in red above";
        if( elem.hasClass("accession-mask") ) {
            fieldName = "accession number";
        }
        if( elem.hasClass("patientmrn-mask") ) {
            fieldName = "MRN above";
        }

        //Please correct the invalid accession number
        var errorHtml =
            '<div class="maskerror-added alert alert-danger">' +
            'Please correct the invalid ' + fieldName + '.' +
            '</div>';

        $('#validationerror').append(errorHtml);

        errors++;
    });


    //console.log("number of errors =" + errors );
    return errors;
}