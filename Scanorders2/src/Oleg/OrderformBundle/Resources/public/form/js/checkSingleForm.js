/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/18/13
 * Time: 2:05 PM
 * To change this template use File | Settings | File Templates.
 */

var _max_tab_height = 0;

$(document).ready(function() {
//    $('a, button').click(function() {
//        $(this).toggleClass('active');
//    });

//    $('.has-spinner').click(function() {
//        console.log('button pressed');
//        $(this).toggleClass('active');
//    });

    // Bind normal buttons
    Ladda.bind( '.btntest', { timeout: 2000 } );

});

function checkButtonPromise( fieldsArr, count, limit ) {

    var ajaxOK, promise;

    promise = new Promise();

    ajaxOK = waitWhenReady(fieldsArr, count, limit);

    if( ajaxOK ) {
        promise.resolve(ajaxOK);
    } else {
        promise.reject(ajaxOK);
    }

    return promise;
}

//inputField - input field element which is tested for value is being set
function waitWhenReady( fieldsArr, count, limit ) {

    var inputId = fieldsArr[count];

    var inputField = $(inputId).find('.keyfield').not("*[id^='s2id_']");

    if( inputField.hasClass('combobox')  ) {
        var testValue = inputField.select2('data').text;
    } else {
        var testValue = inputField.val();
    }

    var checkButton = $(inputId).find('#check_btn');
    //var isCheckBtn = checkButton.find("i").hasClass('checkbtn');

    if( limit == 0 ) {
        //printF(checkButton,"click button:");
        //console.log("check single form: count="+count);
        //checkButton.trigger("click");   //click only once
        var checkres = checkForm( checkButton, true );
    }

    //console.log("error length="+$('.maskerror-added').length);
    if( $('.maskerror-added').length > 0 || !checkres ) {
        //fieldsArr = null;
        return false;
    }

    return true;
}

function finalStepCheck() {
    $('#part-single').css( "width", "20%" );
    $('#block-single').css( "width", "20%" );
    $('#maincinglebtn').show();
    //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);
}

//var _lbtn = Ladda.create( document.querySelector( '.singleform-optional-button' ) );

function addAsync(a, b) {
    console.log("addAsync");
    var deferred = Q.defer();
    deferred.reject(Error("Network Error"));
    // Wait 2 seconds and then add a + b
    setTimeout(function() {
        console.log("deferred.resolve");
        deferred.resolve(a + b);
    }, 2000);
    console.log("deferred.promise");
    return deferred.promise;
}

//Check form single
function checkFormSingle( elem ) {

//    (function () {
//        "use strict";
//
//        var deferredAnimate = Q.async(function* (element) {
//            for (var i = 0; i < 100; ++i) {
//                element.style.marginLeft = i + "px";
//                yield Q.delay(20);
//            }
//        });
//
//        Q.spawn(function* () {
//            yield deferredAnimate(document.getElementById("box"));
//            alert("Done!");
//        });
//    }());
//    return false;

    Q.all([
            addAsync(1, 1),
            addAsync(2, 2),
            addAsync(3, 3)
        ]).then(
            function(result1, result2, result3) {
                console.log(result1, result2, result3);
            },
            function(error) {
                console.log("error="+error);
            }
        );

//    addAsync(3, 4).then(
//        function(result) {
//            console.log(result);
//        },
//        function(error) {
//            console.log("error="+error);
//        }
//    );

    console.log("chaining");
    var promise = Q.promise(function(resolve, reject) {
        resolve(1);
    });

    promise.then(function(val) {
        console.log(val); // 1
        return val + 2;
    }).then(function(val) {
            console.log(val); // 3
        });

    console.log("finished");
    return false;

    if( validateMaskFields() > 0 ) {
        //console.log("errors > 0 => return");
        return false;
    }      

    if( $('#maincinglebtn').is(":visible") ) {
        //console.log("maincinglebtn is visible => return");
        return false;
    }

//    console.log("cancheck="+cancheck);
//    if( !cancheck ) {         
//        return;
//    }

    //$('#optional_param').collapse('toggle');    //open. Need to open to populate patient (if existed) linked to accession

    console.log("start ajax");
    //var lbtn = Ladda.create( document.querySelector( '.singleform-optional-button' ) );
    //_lbtn.start();
    //Ladda.bind(elem);

    //Ladda.bind( '.singleform-optional-button', { timeout: 2000 } );
    //return false;
    //btn.button('loading');
    //$("elem").toggleClass('active');
    var _lbtn = Ladda.create(elem);
    _lbtn.start();

    //$('.singleform-optional-button').append('<img class="spinner-image" src="http://collage.med.cornell.edu/order/bundles/olegorderform/form/img/select2-spinner.gif"/></div>');

    //alert('pause');

    //window.setTimeout( callWaitStack, 300 );
    //console.log('before');
    //console.log('after');

    var ajaxOK = callWaitStack();


    console.log("stop ajax");
//    //btn.button('reset');
    _lbtn.stop();

    if( !ajaxOK ) {
        return false;
    }

    if( $('.maskerror-added').length == 0 ) {
        collapseElementFix($('#optional_param'));
        finalStepCheck();
    } else {
        return false;
    }

    initOptionalParam();

    //console.log("ready!!!");

    return true;
}


function callWaitStack() {
    var fieldsArr = new Array();
    fieldsArr[0] = '#accession-single';
    fieldsArr[1] = '#part-single';
    fieldsArr[2] = '#block-single';

    var ajaxOK = true;

    Q.spread([
        waitWhenReady( fieldsArr, 0, 0 ),
        waitWhenReady( fieldsArr, 1, 0 ),
        waitWhenReady( fieldsArr, 2, 0 )
    ],
        function( acc, part, block ){
            if( !acc ) ajaxOK = false;
            if( !part ) ajaxOK = false;
            if( !block ) ajaxOK = false;
        }
    );

//    if( !waitWhenReady( fieldsArr, 0, 0 ) ) {
//        ajaxOK = false;
//    }

//    if( !waitWhenReady( fieldsArr, 1, 0 ) ) {
//        ajaxOK = false;
//    }
//
//    if( !waitWhenReady( fieldsArr, 2, 0 ) ) {
//        ajaxOK = false;
//    }

    return ajaxOK;
}


//Remove form single
function removeFormSingle( elem ) {

    //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);    

    $("#remove_single_btn").button('loading');
    console.log("start remove: trigger blockbtn: class="+$('.blockbtn').attr("class"));

    $('.blockbtn').trigger("click");

    $('.partbtn').trigger("click");

    $('.accessionbtn').trigger("click");
    
    if( $('.patientmrn').find('i').hasClass('removebtn') ) {
        $('.patientmrn').trigger("click");
    }  

    $('#part-single').css( "width", "25%" );
    $('#block-single').css( "width", "25%" );
    $('#maincinglebtn').hide();
    collapseElementFix($('#optional_param'));   //close optional info

    console.log("end of remove");
    $("#remove_single_btn").button('reset');

}

function checkSingleFormOnNext( elem ) {
    //data-target="#orderinfo_param"
    if( validateMaskFields() > 0 ) {
        return false;
    } else {
        //console.log("no masking errors");
        $("#next_button").hide();

        var accTypeText = $('.accessiontype-combobox').first().select2('data').text;
        if( accTypeText != 'TMA Slide' ) {
            $("#optional_button").show();
        }

        collapseElementFix($('#orderinfo_param'));
    }
    return true;
}

function collapseElementFix(elem) {
    $(document).ready(function() {
        elem.collapse('toggle');
    });
}


function getAccessionInfoDebug(text) {
    var accTypeText = $('.accessiontype-combobox').select2('data').text;
    var accTypeVal = $('.accessiontype-combobox').select2('val');
    var accNum = $('.accession-mask').val();
    console.log("############ "+text+": Accession #="+accNum+", type text="+accTypeText+", type id="+accTypeVal);
}

//open a tab with max height
function initOptionalParam() {

    $('#optional_param_tab a').click(function (e) {

        e.preventDefault();

        var elem = $(this);

        elem.tab('show');

    });

}




