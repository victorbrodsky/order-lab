/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


var urlBase = $("#baseurl").val();
var urlCheck = "http://"+urlBase+"/check/";

var keys = new Array("mrn", "accession", "partname", "blockname");
var arrayFieldShow = new Array("clinicalHistory","age","diffDisident"); //,"disident"); //display as array fields "sex"
var selectStr = 'input[type=file],input.form-control,div.patientsexclass,div.diseaseType,div.select2-container,[class^="ajax-combobox-"],[class^="combobox"],textarea,select';  //div.select2-container, select.combobox, div.horizontal_type

var orderformtype = $("#orderformtype").val();

var dataquality_message1 = new Array();
var dataquality_message2 = new Array();

//var _autogenAcc = 8;
//var _autogenMrn = 13;

//add disident to a single form array field
$(document).ready(function() {

    if( orderformtype == "single") {
        arrayFieldShow.push("disident")
    }

    $("#save_order_onidletimeout_btn").click(function() {
        $(this).attr("clicked", "true");
    });

    //validation on form submit
    $("#scanorderform").on("submit", function () {
        return validateForm();
    });

    addKeyListener();

});

//  0         1              2           3   4  5  6   7
//oleg_orderformbundle_orderinfotype_patient_0_mrn_0_field
var fieldIndex = 3;     //get 'key'
var holderIndex = 5;    //get 'patient'
//console.log("urlCheck="+urlCheck);

//needed by a single slide form
var asseccionKeyGlobal = "";
var asseccionKeytypeGlobal = "";
var partKeyGlobal = "";
var blockKeyGlobal = "";
var mrnKeyGlobal = "";
var mrnKeytypeGlobal = "";

function addKeyListener() {
    //remove has-error class from mrn and accession inputs
    $('.accessionaccession').find('.keyfield').parent().keypress(function() {
        $(this).removeClass('has-error');
    });
    $('.patientmrn').find('.keyfield').parent().keypress(function() {
        //console.log("remove has-error on keypress");
        $(this).removeClass('has-error');
    });

//    $('.keyfield').parent().keypress(function() {
//        //console.log("remove has-error on keypress");
//        $(this).removeClass('has-error');
//        //$(this).siblings('.maskerror-added').remove();
//    });

    $('.ajax-combobox-partname').on("change", function(e) {
        //console.log("remove maskerror-added on change");
        $(this).siblings('.maskerror-added').remove();
    });
    $('.ajax-combobox-blockname').on("change", function(e) {
        //console.log("remove maskerror-added on change");
        $(this).siblings('.maskerror-added').remove();
    });

}


function checkForm( btnel ) {
    
    var btn = $(btnel);
    
    var btnObj = getBtnObj( btn );
    
}

function getBtnObj( btn ) {
    
    //var parent = element.parent().parent().parent().parent().parent().parent();
    var parent = getButtonParent(btn);

    //var elements = parent.find('input,select').not("*[id^='s2id_']");
    var elements = parent.find('.keyfield').not("*[id^='s2id_']");

    //console.log("elements.length=" + elements.length);

    var keyElement = null;
    var name = "";
    for (var i = 0; i < elements.length; i++) {
        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");
        //printF(elements.eq(i)," found element under button parent:");
        //console.log("id=" + id + ", class=" + elements.eq(i).attr('class'));
        if( id && type != "hidden" ) {
            var idsArr = id.split("_");
            var field = idsArr[idsArr.length-fieldIndex];
            //console.log("set key value: field=(" + field + ")");
            if( $.inArray(field, keys) != -1 && id.indexOf('_keytype') == -1 ) {
                //console.log("set key value: found key=(" + field + "), id="+elements.eq(i).attr("id"));
                name = field;
                keyElement = elements.eq(i);
                break;
            }
        }
    }

    //printF(keyElement,"found input key field:");
    //console.log("keyElement val=" + keyElement.val());

    //find extra key: keytype
    var extra = null;
    var extraname = null;
    if( name == "mrn" || name == "accession" ) {
        //var keytype = element.closest('.row').find( "."+name+"type-combobox").not("*[id^='s2id_']");
        var keytype = getKeyGroupParent(element).find( "."+name+"type-combobox").not("*[id^='s2id_']");
        //console.log("find key element: keytype.length="+keytype.length);
        if( keytype.length > 0 ) {
            extra = keytype.select2("val");
            extraname = keytype.select2("data").text;
            //console.log("find key element: keytype id="+keytype.attr("id")+", class="+keytype.attr("class")+", extra="+extra);
        }
    }

    if( name == "partname" ) {
        var accessionNumberElement = getAccessionNumberElement(element,single);
        //printF(accessionNumberElement,"partname: accessionNumberElement=");
        var accessionValue = accessionNumberElement.val();    //i.e. Accession #
        //get extra for accession: keytype
        var keytype = getKeyGroupParent(accessionNumberElement).find('.accessiontype-combobox');
        extra = keytype.select2("val");
        extraname = keytype.select2("data").text;
    }

    if( name == "blockname" ) {
        var accessionNumberElement = getAccessionNumberElement(element, single);
        //printF(accessionNumberElement,"blockname: accessionNumberElement=");
        var accessionValue = accessionNumberElement.val();    //i.e. Accession #
        var keytype = getKeyGroupParent(accessionNumberElement).find('.accessiontype-combobox');
        extra = keytype.select2("val");
        extraname = keytype.select2("data").text;
        var partNumberElement = getPartNumberElement(element, single);
        var partValue = partNumberElement.select2("val"); //i.e. Part #
    }

    var res = new Array;
    res.element = keyElement;
    res.name = name;
    res.extra = extra;  //mrn type
    res.extraname = extraname;  //mrn type
    res.accession = accessionValue;
    res.partname = partValue;

    return res;
}

