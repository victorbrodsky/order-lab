/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/18/13
 * Time: 2:05 PM
 * To change this template use File | Settings | File Templates.
 */

function checkMrn( name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    //get mrn field for this patient: oleg_orderformbundle_orderinfotype_patient_0_mrn
    var id = "oleg_orderformbundle_orderinfotype_"+name+"_"+patientid+"_mrn";

    var mrn = $("#"+id).val();
    console.log("mrn="+mrn);

//    if( mrn == "" ) {
//        $('#'+id).popover( {content:"Please fill out MRN field"} );
//        $('#'+id).popover('show');
//    }

    $("#check_div").html("Hello");

//    $.ajax(urlCommon+"checkmrn").success(function(data) {
//        //console.log(data['id']);
//        $('.ajax-combobox-pathservice').select2('val', data['id']);
//    });

    var urlCheck = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/check/";

    $('#check_div').load('http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/check/patient');

//    $.ajax({
//        url: urlCheck+"patient2",
//        type: 'GET',
//        data: {mrn: '9'},
//        contentType: 'application/json',
//        dataType: 'json',
//        success: function (data) {
//            console.debug("data="+ data);
//            //console.debug("data.id="+ data[0].id);
//            //console.debug("data['id']="+ data[0]['id']);
//            //console.debug("data.name="+ data[0].name);
//            //console.debug("data.id="+ data.id);
//            //console.debug("data.name="+ data.name);
//
////            $("#check_div").append(data);
//            //$("#check_div").html("Done");
//
//            console.log("status1="+data.status);
//            //data = $.parseJSON(data);
//            console.log("status2="+data.status);
//            $("#check_div").html(data.template);
//            alert("Success!");
//
//        },
//        error: function () {
//            $("#check_div").html("Failed");
//        }
//    });

}