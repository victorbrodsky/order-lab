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
                //console.log("Success!", response);
                if( $('.maskerror-added').length > 0 ) {
                    console.log("Validation error");
                    //reject(Error("Validation error"));
                    return false;
                } else {
                    //console.log("Chaining with parent OK: "+response);
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
                //console.log("mask ok");
                resolve('mask ok');
            }
        });
    }

    var promiseNoMainSingleBtn = function(response) {
        return new Q.promise(function(resolve, reject) {
            if( $('#maincinglebtn').is(":visible") ) {
                //console.log("maincinglebtn is visible => return");
                reject('maincinglebtn is visible => return');
            } else {
                //console.log("start ajax");
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
            finalStepCheck();
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
                    //ok
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
            //console.log("All check single form chaining with parent OK:", response);
            lbtn.stop();

            //scroll page to optional_param
            var x = $('#optional_param').offset().top;
            $('html,body').animate( {scrollTop: x}, 400 );
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

    //working!
    executeClick( new btnObject( $('.blockbtn') ) ).
    then(
        function(response) {
            //console.log(" Block Success!", response);
            var partBtnObj = new btnObject( $('.partbtn') );
            return executeClick( partBtnObj );
        }
    ).
    then(
        function(response) {
            //console.log("Part Success!", response);
            var accBtnObj = new btnObject( $('.accessionbtn') );
            return executeClick( accBtnObj );
        }
    ).
    then(
        function(response) {
            //console.log("Acc Success!", response);
            finalStepDelete();  //acc succ, so show delete button
            if( $('.patientmrnbtn').hasClass('removebtn') ) {
                var patientBtnObj = new btnObject( $('.patientmrnbtn') );
                return executeClick( patientBtnObj );
            }
            return "patient was empty";
        }
    ).
    then(
        function(response) {
            //console.log("All delete chaining with parent OK:", response);
        },
        function(error) {
            console.error("Single form delete chaining with parent Error", error);
        }
    ).done(
        function(response) {
            //console.log("Done ", response);
            btn.button('reset');
//            var docloc = document.location;
//            console.log("remove docloc="+docloc);
//            docloc = docloc.replace("#optional_param_tab_body", "");
//            docloc = docloc.replace("#message_param", "");
//            console.log("remove final docloc="+docloc);
//            document.location = docloc;
        }
    );

    return;

}

function finalStepCheck() {
    $('#part-single').css( "width", "20%" );
    $('#block-single').css( "width", "20%" );
    $('#maincinglebtn').show();
    collapseElementFix($('#optional_param'),'show');
    //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);
}

function finalStepDelete() {
    $('#part-single').css( "width", "25%" );
    $('#block-single').css( "width", "25%" );
    $('#maincinglebtn').hide();

    collapseElementFix($('#optional_param'),'hide');   //close optional info
}

function checkSingleFormOnNext( elem ) {
    //data-target="#message_param"
    if( validateMaskFields() > 0 ) {
        return false;
    } else {
        //console.log("no masking errors");
        $("#next_button").hide();

        var accTypeText = $('.accessiontype-combobox').first().select2('data').text;
        if( accTypeText != 'TMA Slide' ) {
            $("#optional_button").show();
        }

        collapseElementFix($('#message_param'),'show');
    }

    //scroll page to message_param
    var x = $('#message_param').offset().top;
    $('html,body').animate( {scrollTop: x}, 400 );

    return true;
}

function collapseElementFix(elem,param) {
    //console.log("collapse Element Fix");
    $(document).ready(function() {
        elem.collapse(param);
        //elem.collapse('toggle');
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

        //e.preventDefault();

        var elem = $(this);

        elem.tab('show');

    });

}




