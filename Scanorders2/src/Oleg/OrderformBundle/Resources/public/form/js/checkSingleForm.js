/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/18/13
 * Time: 2:05 PM
 * To change this template use File | Settings | File Templates.
 */


//click check button and verify for errors
function clickSingleBtn( btn ) {

    return new Q.promise(function(resolve, reject) {

        //printF(btn,"######## Button for click single button func: ");

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

    promiseValidateMask().
    then(promiseNoMainSingleBtn).
    then(
        function(response) {
            //console.log("validation promises success!", response);
            return clickSingleBtn( $('.checkbtn.accessionbtn') );
        }
    ).
    then(
        function(response) {
            //console.log("Accession success!", response);
            return clickSingleBtn( $('.checkbtn.partbtn') );
        }
    ).
    then(
        function(response) {
            //console.log("Part success!", response);
            return clickSingleBtn( $('.checkbtn.blockbtn') );
        }
    ).
    then(
        function(response) {
            //console.log("Block success!", response);
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

                //console.log("All Success!", response);

                if( $('.maskerror-added').length == 0 ) {
                    collapseElementFix($('#optional_param'));
                    finalStepCheck();
                } else {
                    //return false;
                }

                initOptionalParam();

            } else {
                //console.log("Response is false: ", response);
            }

        }
    ).
    then(
        function(response) {
            console.log("All check single form chaining with parent OK:", response);
            lbtn.stop();
        },
        function(error) {
            console.error("Single form chaining chaining with parent Error", error);
            lbtn.stop();
        }
    );

}


//Remove form single
function removeFormSingle( elem ) {

    //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);

    var btn = $(elem);

    btn.button('loading');

//    var blockBtnObj = new btnObject( $('.blockbtn') );
//    var partBtnObj = new btnObject( $('.partbtn') );
//    var accBtnObj = new btnObject( $('.accessionbtn') );
//    var patientBtnObj = new btnObject( $('.patientmrn') );


    //working!
    executeClick( new btnObject( $('.blockbtn') ) ).
//    Q.fcall(
//        function() {
//            var blockBtnObj = new btnObject( $('.blockbtn') );
//            return executeClick( blockBtnObj );
//        }
//    ).
    then(
        function(response) {
            console.log(" Block Success!", response);
            var partBtnObj = new btnObject( $('.partbtn') );
            return executeClick( partBtnObj );
        }
    ).
    then(
        function(response) {
            console.log("Part Success!", response);
            var accBtnObj = new btnObject( $('.accessionbtn') );
            return executeClick( accBtnObj );
        }
    ).
    then(
        function(response) {
            console.log("Acc Success!", response);
            if( $('.patientmrn').hasClass('removebtn') ) {
                var patientBtnObj = new btnObject( $('.patientmrn') );
                executeClick( patientBtnObj );
                finalStepDelete();
                return "patient is deleted";
            }
            return "patient was empty";
        }
    ).
    then(
        function(response) {
            console.log("All delete chaining with parent OK:", response);
            finalStepDelete();
        },
        function(error) {
            console.error("Single form delete chaining with parent Error", error);
        }
    ).done(
        function(response) {
            console.log("Done ", response);
            btn.button('reset');
        }
    );

    return;


//
//    var promiseValidateMask = function() {
//        return new Q.promise(function(resolve, reject) {
//            if( validateMaskFields() > 0 ) {
//                console.log("errors > 0 => return");
//                reject('mask errors');
//            } else {
//                console.log("mask ok");
//                resolve('mask ok');
//            }
//        });
//    }
//
//    var checkFormPatient = checkForm( $('.patientmrn'), 'none' );
//
//    var checkFormAcc = checkForm( $('.accessionbtn'), 'none' );
//
//    var checkFormPart = checkForm( $('.partbtn'), 'none' );
//
//    var checkFormBlock = checkForm( $('.blockbtn'), 'none' );
//
//    promiseValidateMask().
//    then(
//        function(response) {
//            //return checkForm( $('.partbtn'), 'none' );
//            return checkFormBlock;
//            //return "Block OK";
//        }
//    ).
//    then(
//        function(response) {
//            //return checkForm( $('.partbtn'), 'none' );
//            return  checkFormPart;
//            //return "Part OK";
//        }
//    ).
//    then(
//        function(response) {
//            //return checkForm( $('.accessionbtn'), 'none' );
//            return  checkFormAcc;
//            //return "Acc OK";
//        }
//    ).
//    then(
//        function(response) {
//            if( $('.patientmrn').hasClass('removebtn') ) {
//                //console.log("no patient delete");
//                //checkForm( $('.patientmrn'), 'none' );
//                return  checkFormPatient;
//                //return "Patient OK";
//            }
//            return "patient processing ok";
//        }
//    ).
//    then(
//        function(response) {
//            //console.log("All delete chaining with parent OK:", response);
//            //return true;
//            //console.log("delete stop ajax");
//
//            $('#part-single').css( "width", "25%" );
//            $('#block-single').css( "width", "25%" );
//            $('#maincinglebtn').hide();
//            collapseElementFix($('#optional_param'));   //close optional info
//
//            btn.button('reset');
//        },
//        function(error) {
//            console.error("Single form delete chaining with parent Error", error);
//            //return false;
//            //console.log("delete stop ajax");
//            btn.button('reset');
//        }
//    );

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

function finalStepCheck() {
    $('#part-single').css( "width", "20%" );
    $('#block-single').css( "width", "20%" );
    $('#maincinglebtn').show();
    //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);
}

function finalStepDelete() {
    $('#part-single').css( "width", "25%" );
    $('#block-single').css( "width", "25%" );
    $('#maincinglebtn').hide();
    collapseElementFix($('#optional_param'));   //close optional info
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




