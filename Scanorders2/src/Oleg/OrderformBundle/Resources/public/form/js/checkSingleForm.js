/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/18/13
 * Time: 2:05 PM
 * To change this template use File | Settings | File Templates.
 */

var _max_tab_height = 0;

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
        console.log("check single form: count="+count);
        //checkButton.trigger("click");   //click only once
        var checkres = checkForm( checkButton, true );
    }

    console.log("error length="+$('.maskerror-added').length);
    if( $('.maskerror-added').length > 0 || !checkres ) {
        //fieldsArr = null;
        return false;
    }

    return true;

//    setTimeout( function(){
//        //console.log( "testValue="+testValue+", isCheckBtn="+isCheckBtn);
//        if( testValue && testValue != "" && !isCheckBtn ) {
//            console.log("ok!!! limit="+limit);
//            count++;
//            if( count < fieldsArr.length ) {
//                waitWhenReady( fieldsArr, count, 0 );   //process next button
//            }   else {
//                finalStepCheck();
//            }
//
//            return 1;
//        }
//        else{
//            if( limit > 20 ) {
//                return false;
//            }
//            limit++;
//            console.log("not ready ... limit="+limit);
//            waitWhenReady( fieldsArr, count, limit );  //process the same button
//        }
//    }, 300);
//    return true;
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
    
//    console.log("cancheck="+cancheck);
//    if( !cancheck ) {         
//        return;
//    }

    //$('#optional_param').collapse('toggle');    //open. Need to open to populate patient (if existed) linked to accession

    var fieldsArr = new Array();
    fieldsArr[0] = '#accession-single';
    fieldsArr[1] = '#part-single';
    fieldsArr[2] = '#block-single';

    //console.log("start");

    if( !waitWhenReady( fieldsArr, 0, 0 ) ) {
        return false;
    }

    if( !waitWhenReady( fieldsArr, 1, 0 ) ) {
        return false;
    }

    if( !waitWhenReady( fieldsArr, 2, 0 ) ) {
        return false;
    }

    if( $('.maskerror-added').length == 0 ) {
        $('#optional_param').collapse('toggle');
        finalStepCheck();
    } else {
        return false;
    }

    setMaxHeightSingleForm();

    initOptionalParam();
    //console.log("ready!!!");

    return true;
}


//Remove form single
function removeFormSingle( elem ) {

    //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);    

    console.log("trigger blockbtn: class="+$('.blockbtn').attr("class"));
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

//open a tab with max height
function initOptionalParam() {

    $('#optional_param_tab a').click(function (e) {

        e.preventDefault();

        var elem = $(this);

        elem.tab('show');

        window.setTimeout(
            function(){
                setMaxHeightSingleForm_one();
            },
            500
        );

//        setMaxHeightSingleForm_one(elem);

    });

}

var _countChange = 0;
function setMaxHeightSingleForm_one() {
    //removeMaxHeightSingleForm();
    //printF(element);

    var menuH = $('#optional_param_tab').height();
    console.log("menuH="+menuH);

    //var elementRownH = $('#optional_param_tab_body').find('.row').height();

    var element = $('#optional_param_tab_body').find('.option_info_panel').find('.active');

    printF(element);

    //var optionHeight = element.closest('.row').find('.option_info_panel').find('.active').height();
    var optionHeight = element.height();

    //console.log("elementRownH="+elementRownH+", optionHeight="+optionHeight);
    //var offset = elementRownH - optionHeight;   //8*menuH;
    var offset = 7*menuH;
    console.log("offset="+offset);

    var disEl = element.find('.partdiseasetype');
    if( disEl.length > 0 ) {
        console.log('disEl height='+disEl.height());
        if( _countChange == 0 ) {
            optionHeight = optionHeight + offset;
            offset = 0;
        }
        _countChange++;
    }

    console.log("1 optionHeight="+optionHeight+", _max_tab_height="+_max_tab_height);

    if( optionHeight > _max_tab_height ) {

        if( _countChange > 1 ) {
            optionHeight = optionHeight - offset;
        }

        _max_tab_height = optionHeight;

//        var partEl = element.find('.partdiseasetype');
//        if( partEl.length > 0 ) {
//
//            //if( _countChange == 0 ) {
//                optionHeight = optionHeight;
//            //}
//            _countChange++;
//
//        } else {
//
//            _max_tab_height = optionHeight;
//
//        }

        console.log("set!!!!!!: optionHeight="+optionHeight+", _max_tab_height="+_max_tab_height);
        $('.option_info_panel .tab-pane').height(_max_tab_height);
        //element.height(_max_tab_height);

    }

}

function setMaxHeightSingleForm() {

    removeMaxHeightSingleForm();

    var element = $('#optional_param_tab_body').find('.option_info_panel').find('.active');
    $('.option_info_panel .tab-pane').height(element.height());

    return false;   //testing

    var menuH = $('#optional_param_tab').height();

    $('.option_info_panel').find('.tab-pane').each( function() {
        $(this).removeAttr('style');
        $(this).css({
                position:   'absolute', // Optional if #myDiv is already absolute
                visibility: 'hidden',
                display:    'block'
            });

        var optionHeight = $(this).height();
        var totalH = optionHeight;

        //if( $(this).find('.partdiseasetype') ) {
            //var totalH = optionHeight + 4*menuH;
        //}

        if( totalH > _max_tab_height ) {
            _max_tab_height = totalH;
        }
        //console.log("optionHeight="+optionHeight+", menuH="+menuH);

        $(this).removeAttr('style');

    });

    //console.log( 'set max height='+_max_tab_height );
    $('.option_info_panel .tab-pane').height(_max_tab_height);
}

function removeMaxHeightSingleForm() {
    _max_tab_height = 0;
    _countChange = 0;
    $('.option_info_panel').find('.tab-pane').removeAttr('style');
}


