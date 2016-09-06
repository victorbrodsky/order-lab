/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/3/14
 * Time: 10:40 AM
 * To change this template use File | Settings | File Templates.
 */

var _maskErrorClass = "has-warning"; //"maskerror"

function getAgeDefaultMask() {
    return "f[9][9]";
}


//holder - element holding all fields to apply masking
function fieldInputMask( holder ) {

    //console.log("user-masking.js: field Input Mask");

    $.extend($.inputmask.defaults.definitions, {
        'f': {  //masksymbol
            "validator": "[1-9]",
            "cardinality": 1,
            'prevalidator': null
        }
    });

    //any alfa-numeric without leading or ending '-'
    $.extend($.inputmask.defaults.definitions, {
        "m": {
            "validator": "[A-Za-z0-9-]",
            "cardinality": 1,
            'prevalidator': null
        }
    });

    $.extend($.inputmask.defaults.definitions, {
        "n": {
            "validator": "[0-9( )+,#ex//t-]",
            "cardinality": 1,
            'prevalidator': null
        }
    });

    //only digits and periods. Use for percent
    $.extend($.inputmask.defaults.definitions, {
        "o": {
            "validator": "[0-9.]",
            "cardinality": 1,
            'prevalidator': null
        }
    });

    $.extend($.inputmask.defaults, {
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
        clearMaskOnLostFocus: true
    });

    if( !holder || typeof holder === 'undefined' || holder.length == 0 ) {
        $(":input").inputmask();
    } else {
        holder.find(":input").inputmask();
    }

    $(".age-mask").inputmask( { "mask": getAgeDefaultMask() });

    //masking for datepicker. This will overwrite datepicker format even if format is mm/yyyy
    $(".datepicker").inputmask( "mm/dd/yyyy" );

    $('.phone-mask').inputmask("mask", {
        "mask": "[n]", "repeat": 50, "greedy": false
    });

    $('.digit-mask').inputmask("mask", {
        "mask": "9", "repeat": 50, "greedy": false
    });

    //$('.email-mask').inputmask('Regex', { regex: "[a-zA-Z0-9._%-]+@[a-zA-Z0-9-]+\\.[a-zA-Z]{2,4}" });

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
        //createErrorMessage( element, null, appendWell );
    }

}


function noMaskError( element ) {
    //console.log( "complete="+ element.inputmask("isComplete")+", !allZeros="+!allZeros(element) );

    if(
        element.hasClass('datepicker') ||
        element.hasClass('age-mask') ||
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

function clearErrorField( element ) {
    //console.log("user-masking: clear error fields");
    //check if not all zeros
    if( allZeros(element) ) {
        //console.log("all zeros!");
        return;
    }

    //console.log("make ok field id="+element.attr("id")+", class="+element.attr("class"));
    element.parent().removeClass(_maskErrorClass);
    $('.maskerror-added').remove();

    return;
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






