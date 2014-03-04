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
    //Ladda.bind( '.btntest', { timeout: 2000 } );

});


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
        var checkres = checkForm( checkButton );
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

function clickSingleBtn( btn ) {

    return new Q.promise(function(resolve, reject) {

        printF(btn,"######## Button for click single button func: ");

        checkForm( btn, 'none' ).
        then(
            function(response) {
                console.log("Success!", response);
                if( $('.maskerror-added').length > 0 ) {
                    console.log("Validation error");
                    //reject(Error("Validation error"));
                    return false;
                } else {
                    console.log("Chaining with parent OK: "+response);
                    //resolve("Chaining with parent OK: "+response);
                    return true;
                }
            }
        ).
        then(
            function(response) {
                if( response ) {
                    resolve("Chaining with parent OK: "+response);
                } else {
                    reject(Error("Validation error"));
                }
            },
            function(error) {
                console.error("Single check failed!", error);
                reject(Error("Single check failed, error="+error));
            }
        );

    });
}

//Check form single
function checkFormSingle( elem ) {

//    var promise = new Q.promise(function(resolve, reject) {
//        resolve(1);
//    });
//    promise.then(function(val) {
//        console.log(val); // 1
//        return val + 2;
//    }).then(function(val) {
//            console.log(val); // 3
//    });
//    //return false;

    var lbtn = Ladda.create(elem);

    var promiseValidateMask = function() {
        return new Q.promise(function(resolve, reject) {
            if( validateMaskFields() > 0 ) {
                console.log("errors > 0 => return");
                reject('mask errors');
            } else {
                console.log("mask ok");
                resolve('mask ok');
            }
        });
    }

    var promiseNoMainSingleBtn = function(response) {
        return new Q.promise(function(resolve, reject) {
            if( $('#maincinglebtn').is(":visible") ) {
                console.log("maincinglebtn is visible => return");
                reject('maincinglebtn is visible => return');
            } else {
                console.log("start ajax");
                lbtn.start();
                resolve('maincinglebtn is ok');
            }
        });
    }

    //$('.singleform-optional-button').append('<img class="spinner-image" src="http://collage.med.cornell.edu/order/bundles/olegorderform/form/img/select2-spinner.gif"/></div>');
    //var ajaxOK = callWaitStack();

    promiseValidateMask().
    then(promiseNoMainSingleBtn).
    then(
        function(response) {
            console.log("validation promises success!", response);
            return clickSingleBtn( $('.checkbtn.accessionbtn') );
        }
    ).
    then(
        function(response) {
            console.log("Accession success!", response);
            return clickSingleBtn( $('.checkbtn.partbtn') );
        }
    ).
    then(
        function(response) {
            console.log("Part success!", response);
            return clickSingleBtn( $('.checkbtn.blockbtn') );
        }
    ).
    then(
        function(response) {
            console.log("Block success!", response);
            if( $('.maskerror-added').length > 0 ) {
                return false;
            } else {
                return true;
            }
        }
    ).
    then(
        function(response) {

            if( response ) {

                console.log("All Success!", response);

                if( $('.maskerror-added').length == 0 ) {
                    collapseElementFix($('#optional_param'));
                    finalStepCheck();
                } else {
                    //return false;
                }

                initOptionalParam();

            } else {
                console.log("Response is false: ", response);
            }

        }
    ).
    then(
        function(response) {
            console.log("All chaining with parent OK:", response);
            //return true;
            console.log("stop ajax");
            lbtn.stop();
        },
        function(error) {
            console.error("All chaining with parent Error", error);
            //return false;
            console.log("stop ajax");
            lbtn.stop();
        }
    );

    //console.log("ready!!!");

    //return false;
}


//Remove form single
function removeFormSingle( elem ) {

    //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);

    var btn = $(elem);

    btn.button('loading');

    checkForm( $('.blockbtn'), 'none' ).
    then(
        function(response) {
            return checkForm( $('.partbtn'), 'none' );
        }
    ).
    then(
        function(response) {
            return checkForm( $('.accessionbtn'), 'none' );
        }
    ).
    then(
        function(response) {
            if( $('.patientmrn').hasClass('removebtn') ) {
                console.log("no patient delete");
                checkForm( $('.patientmrn'), 'none' );
            }
            return "patient processing ok";
        }
    ).
    then(
        function(response) {
            console.log("All delete chaining with parent OK:", response);
            //return true;
            console.log("delete stop ajax");

            $('#part-single').css( "width", "25%" );
            $('#block-single').css( "width", "25%" );
            $('#maincinglebtn').hide();
            collapseElementFix($('#optional_param'));   //close optional info

            btn.button('reset');
        },
        function(error) {
            console.error("All delete chaining with parent Error", error);
            //return false;
            console.log("delete stop ajax");
            btn.button('reset');
        }
    );

//    $("#remove_single_btn").button('loading');
//    console.log("start remove: trigger blockbtn: class="+$('.blockbtn').attr("class"));
//
//    $('.blockbtn').trigger("click");
//
//    $('.partbtn').trigger("click");
//
//    $('.accessionbtn').trigger("click");
//
//    if( $('.patientmrn').hasClass('removebtn') ) {
//        $('.patientmrn').trigger("click");
//    }

//    $('#part-single').css( "width", "25%" );
//    $('#block-single').css( "width", "25%" );
//    $('#maincinglebtn').hide();
//    collapseElementFix($('#optional_param'));   //close optional info
//
//    console.log("end of remove");
//    $("#remove_single_btn").button('reset');
//
//    btn.button('reset');

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




