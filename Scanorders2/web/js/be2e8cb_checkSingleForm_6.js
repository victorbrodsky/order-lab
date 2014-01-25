/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/18/13
 * Time: 2:05 PM
 * To change this template use File | Settings | File Templates.
 */

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
    var isCheckBtn = checkButton.find("i").hasClass('checkbtn');

    if( limit == 0 ) {
        //printF(checkButton,"click button:");
        //console.log("count="+count);
        checkButton.trigger("click");   //click only once
    }

    setTimeout( function(){
        //console.log( "testValue="+testValue+", isCheckBtn="+isCheckBtn);
        if( testValue && testValue != "" && !isCheckBtn ) {
            //console.log("ok!!! limit="+limit);
            count++;
            if( count < fieldsArr.length ) {
                waitWhenReady( fieldsArr, count, 0 );   //process next button
            }   else {
                finalStepCheck();
            }

            return 1;
        }
        else{
            if( limit > 20 ) {
                return 0;
            }
            limit++;
            //console.log("not ready ... limit="+limit);
            waitWhenReady( fieldsArr, count, limit ); //process the same button
        }
    }, 300);
}

function finalStepCheck() {
    $('#part-single').css( "width", "20%" );
    $('#block-single').css( "width", "20%" );
    $('#maincinglebtn').show();
    //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);
}

//Check form single
function checkFormSingle( elem ) {

    if( validateMaskFields() > 0 ) {
        //console.log("errors > 0 => return");
        return false;
    }      

    if( $('#maincinglebtn').is(":visible") ) {
        //console.log("maincinglebtn is visible => return");
        return false;
    }

    //data-target="#optional_param"
    $('#optional_param').collapse('toggle');
    
//    console.log("cancheck="+cancheck);
//    if( !cancheck ) {         
//        return;
//    }

    var fieldsArr = new Array();
    fieldsArr[0] = '#accession-single';
    fieldsArr[1] = '#part-single';
    fieldsArr[2] = '#block-single';

    //console.log("start");

    waitWhenReady( fieldsArr, 0, 0 );

    //console.log("ready!!!");

    return;
}


//Remove form single
function removeFormSingle( elem ) {

    //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);    

    //console.log("trigger blockbtn: class="+$('.blockbtn').attr("class"));
    $('.blockbtn').trigger("click");

    $('.partbtn').trigger("click");

    $('.accessionbtn').trigger("click");
    
    if( $('.patientmrn').find('i').hasClass('removebtn') ) {
        $('.patientmrn').trigger("click");
    }  

    $('#part-single').css( "width", "25%" );
    $('#block-single').css( "width", "25%" );
    $('#maincinglebtn').hide();
    $('#optional_param').collapse('toggle'); //close optional info

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

        $('#orderinfo_param').collapse('toggle');
    }
    return true;
}


function getAccessionInfoDebug(text) {
    var accTypeText = $('.accessiontype-combobox').select2('data').text;
    var accTypeVal = $('.accessiontype-combobox').select2('val');
    var accNum = $('.accession-mask').val();
    console.log("############ "+text+": Accession #="+accNum+", type text="+accTypeText+", type id="+accTypeVal);
}
