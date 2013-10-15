/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/18/13
 * Time: 2:05 PM
 * To change this template use File | Settings | File Templates.
 */

function checkForm( elem ) {

    var element = $(elem);

    console.log( "element.id=" + element.attr('id') );

//    var elementParent = element.parent();
//    console.log("elementParent.class="+elementParent.attr('class')); //formcheck_patient_0_0_0_0_0_0_0_0

//    var elementInput = element.closest("div.input-group").find("input[name='oleg_orderformbundle_orderinfotype[patient][0][mrn]']");
    //var elementInput = element.closest("div.input-group").find("input[type=text]");

    var elementInput = element.parent().find("input");  //find("input[type=text]");
    console.log("elementInput.class="+elementInput.attr('class'));

//    var elementInput2 = element.find('input[type=text],textarea,select').filter(':visible:first');
//    console.log("elementInput2.class=" + elementInput2.attr('class'));

    //  0         1              2           3   4  5
    //oleg_orderformbundle_orderinfotype_patient_0_mrn
    var inputId = elementInput.attr('id');
    console.log("inputId="+inputId);

    var idsArr = inputId.split("_");

    var name = idsArr[3];
    var patient = idsArr[4];
    var key = idsArr[4];
//    var procedure = idsArr[3];
//    var accession = idsArr[4];
//    var part = idsArr[5];
//    var block = idsArr[6];
//    var slide = idsArr[7];
//    var scan = idsArr[8];
//    var stain = idsArr[9];

    //get mrn field for this patient: oleg_orderformbundle_orderinfotype_patient_0_mrn
    var id = "oleg_orderformbundle_orderinfotype_"+name+"_"+patient+"_mrn";

    var mrn = $("#"+inputId).val();
    console.log("mrn="+mrn);

//    if( mrn == "" || mrn == undefined) {
    if( !mrn ) {
        //console.log("mrn undefinded!");
        $('#'+inputId).popover( {content:"Please fill out MRN field"} );
        $('#'+inputId).popover('show');
        return;
    } else {
        //console.log("mrn definded="+mrn);
    }

    var urlCheck = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/check/";

    //var ids = new Array(0,0,0,0,0,0,0,0);
    //var formbody = getFormBody( "patient", ids[0], ids[1], ids[2], ids[3], ids[4], ids[5], ids[6], ids[7] );
    //$('#check_div').html(formbody);
    //$('#check_div').load('http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/check/patient');

    $.ajax({
        url: urlCheck+"patientdata",
        type: 'GET',
        data: {mrn: mrn},
        contentType: 'application/json',
        dataType: 'json',
        success: function (data) {
            if( data.id ) {
                console.debug("inmrn="+ data.inmrn);
                console.debug("data.id="+ data.id);
                console.debug("data.name="+ data.name);
                //oleg_orderformbundle_orderinfotype_patient_0_name
                $("#oleg_orderformbundle_orderinfotype_"+name+"_"+patient+"_mrn").val(data.mrn);
                $("#oleg_orderformbundle_orderinfotype_"+name+"_"+patient+"_name").val(data.name);
                $("#oleg_orderformbundle_orderinfotype_"+name+"_"+patient+"_sex").val(data.sex);
                $("#oleg_orderformbundle_orderinfotype_"+name+"_"+patient+"_age").val(data.age);
                $("#oleg_orderformbundle_orderinfotype_"+name+"_"+patient+"_clinicalHistory_0_clinicalHistory").val(data.clinicalHistory); //oleg_orderformbundle_orderinfotype_patient_0_clinicalHistory_0_clinicalHistory

                //$("#oleg_orderformbundle_orderinfotype_"+name+"_"+patient+"_dob").prop("disabled", false);
                $("#oleg_orderformbundle_orderinfotype_"+name+"_"+patient+"_dob").datepicker( 'setDate', new Date(data.dob.date) );
                $("#oleg_orderformbundle_orderinfotype_"+name+"_"+patient+"_dob").datepicker( 'update');
                disableAllElements(element, true);
            } else {
                console.debug("not found: inmrn="+ data.inmrn);
                disableAllElements(element, false);
            }
        },
        error: function () {
            console.debug("ajax error");
            disableAllElements(element, false);
        }
    });

}

var keys = new Array("mrn","accession","name");
function disableAllElements( element, disabled ) {

    var parent = element.parent().parent().parent().parent();
    console.log("parent.id=" + parent.attr('id'));

    var elements = parent.find('input,textarea,select');

    //console.log("elements.length=" + elements.length);

    for (var i = 0; i < elements.length; i++) {

        //console.log("element.id=" + elements.eq(i).attr("id"));
        //  0         1              2           3   4  5
        //oleg_orderformbundle_orderinfotype_patient_0_mrn  //length=6
        var id = elements.eq(i).attr("id");
        if( id ) {
            var idsArr = elements.eq(i).attr("id").split("_");
//        var name = idsArr[3];
//        var patient = idsArr[4];
            var field = idsArr[idsArr.length-1];
            //console.log("field=(" + field + ")");

            if( $.inArray(field, keys) == -1 ) {
                //console.log("disable!!!!");
                elements.eq(i).prop("disabled", disabled);
            } else {
                //console.log("In array. Additional check for field=("+field+")");
                if( field == "name" ) {
                    var holder = idsArr[idsArr.length-3];
                    //console.log("holder="+holder);
                    if( holder != "part" && holder != "block" ) {
                        //console.log("disable!!!!");
                        elements.eq(i).prop("disabled", disabled);
                    }
                }
            }
        }

    }
}

//TODO: add listener for key fields. If change, disable all element