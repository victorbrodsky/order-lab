/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/11/13
 * Time: 5:17 PM
 * To change this template use File | Settings | File Templates.
 */

//use for new: add listeners for disease type holder for Single Form
function primaryOrganOption() {

    //console.log("listen for metastatic");

    var holder = "#primaryOrgan";

    $(holder).collapse({
        toggle: false
    })

    $('#oleg_orderformbundle_parttype_origin_0').on('click', function(e) {
        //console.log("0 close?????????????????");
        if( $(holder).is(':visible') ) {
            $(holder).collapse('hide');
        }
    });

    $('#oleg_orderformbundle_parttype_origin_1').on('click', function(e) {
        //console.log("1 toggle!!!!!!!!!!!!!!!!!");
        $(holder).collapse('show');
    });

//    $('#oleg_orderformbundle_parttype_origin_placeholder').on('click', function(e) {
//        console.log("placeholder close?????????????????");
//        if( $(holder).is(':visible') ) {
//            $(holder).collapse('hide');
//        }
//    });

    var checked = $('form input[type=radio]:checked').val();
    if( checked == 1 ) {
        $(holder).collapse('toggle');
    }

}

//use for new: add listeners for origin type holder for Multy Form
function primaryOrganOptionMulti( ids ) { //patient, procedure, accession, part ) {

    var patient = ids[0];
    var procedure = ids[1];
    var accession = ids[2];
    var part = ids[3];

    var uid = 'patient_'+patient+'_procedure_'+procedure+'_accession_'+accession+'_part_'+part;
    var holder = '#primaryOrgan_option_multi_'+uid+'_primaryOrgan';

    $('#oleg_orderformbundle_orderinfotype_'+uid+'_origin').change(function(e) {
        var curid = $(this).attr('id');
        //id: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_1_part_0_origin
        //console.log("click id="+curid);

        var arr1 = curid.split("oleg_orderformbundle_orderinfotype_");
        var arr2 = arr1[1].split("_");
        //get ids
        var patient = arr2[1];
        var procedure = arr2[3];
        var accession = arr2[5];
        var part = arr2[7];

        uid = 'patient_'+patient+'_procedure_'+procedure+'_accession_'+accession+'_part_'+part;

        //primaryOrgan_option_multi_patient_0_procedure_0_accession_0_part_0_primaryOrgan
        holder = '#primaryOrgan_option_multi_'+uid+'_primaryOrgan';

        //find holder by 4th chldren id: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_1_part_0_primaryOrgan
        var childId = "#oleg_orderformbundle_orderinfotype_"+uid+"_primaryOrgan";
        var originElement = $(childId).parent().parent().parent().parent();

        //console.log(holder);

        e.preventDefault();

        if( $("#oleg_orderformbundle_orderinfotype_"+uid+"_origin_0").is(':checked') ) {
            //console.log("1 close?????????????????");
            if( $(originElement).is(':visible') ) {
                //console.log("1 close?????????????????");
                $(originElement).collapse('hide');
            }
        }

        if( $("#oleg_orderformbundle_orderinfotype_"+uid+"_origin_1").is(':checked') ) {
            //console.log("toggle id="+holder);
            $(originElement).collapse('show');
        }


//        if( $("#oleg_orderformbundle_orderinfotype_"+uid+"_origin_placeholder").is(':checked') ) {
//            console.log("placeholder close?????????????????");
//            $(originElement).collapse('hide');
//        }

    });

}

//use for show: toggle origin well when Metastatic is selected
function checkValidatePrimaryOrgan() {

    function add() {

        //console.log("primamry organ add id="+curid);

        //origin_option_multi_patient_0_procedure_0_accession_0_part_0_origintag
        if( this.name.indexOf("origin") != -1 ) {

            var curid = $(this).attr('id');


            if($('#'+curid).is(':checked')) {
                //console.log("Metastatic checked! add id="+curid);

                //1) oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_diseaseType
                var arr1 = curid.split("oleg_orderformbundle_orderinfotype_");
                //2) patient_0_procedure_0_accession_0_part_0_diseaseType
                var arr2 = arr1[1].split("_");
                //3) get ids
                var patient = arr2[1];
                var procedure = arr2[3];
                var accession = arr2[5];
                var part = arr2[7];
                uid = 'patient_'+patient+'_procedure_'+procedure+'_accession_'+accession+'_part_'+part;
                //holder = '#origin_option_multi_'+uid+'_origintag';

                if( curid.indexOf("origin_1") != -1 ) {
                    //use parent of this symfony's origin id=primaryOrgan_option_multi_patient_0_procedure_0_accession_0_part_0_primaryOrgan
                    var holder = '#oleg_orderformbundle_orderinfotype_'+uid+'_primaryOrgan';
                    var originElement = $(holder).parent().parent().parent().parent();
                    //console.log("loop validate toggle="+holder);
                    $(originElement).collapse('show');
                }
            }

        }
    }

    var form = $('#multy_form'), remaining = {}, errors = [];

    form.find(':radio').each(add);

}