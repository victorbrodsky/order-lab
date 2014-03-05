/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

var combobox_width = '100%'; //'element'
//var urlCommon = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/util/";
//var urlCommon = "http://collage.med.cornell.edu/order/util/";
var urlBase = $("#baseurl").val();
var urlCommon = "http://"+urlBase+"/util/";
//var type = $("#formtype").val();
var cicle = $("#formcicle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();
var proxyuser_name = $("#proxyuser_name").val();
var proxyuser_id = $("#proxyuser_id").val();
//console.log("urlCommon="+urlCommon);
var orderinfoid = $(".orderinfo-id").val();

var accessiontype = new Array();
var partname = new Array();
var blockname = new Array();
var stain = new Array();
var scanregion = new Array();
var procedure = new Array();
var organ = new Array();
var delivery = new Array();
var returnslide = new Array();
var pathservice = new Array();
var userpathservice = new Array();
var userpathserviceflag = false;

var asyncflag = true;

function regularCombobox() {
    //resolve
    $("select.combobox").select2({
        width: combobox_width,
        dropdownAutoWidth: true,
        placeholder: "Choose an option",
        allowClear: true
        //selectOnBlur: true
        //readonly: true
        //selectOnBlur: true,
        //containerCssClass: 'combobox-width'
    });

    //set amd make provider read only
    $("#s2id_oleg_orderformbundle_orderinfotype_provider").select2("readonly", true);
    $("#s2id_oleg_orderformbundle_orderinfotype_provider").select2('data', {id: user_id, text: user_name});

    //preselect with current user
    if( proxyuser_id ) {
//        proxyuser_id = user_id;
//        proxyuser_name = user_name;
        $("#s2id_oleg_orderformbundle_orderinfotype_proxyuser").select2('data', {id: proxyuser_id, text: proxyuser_name});
    }

}

function customCombobox() {

    //console.log("cicle="+cicle);

    if( cicle && urlBase && cicle != 'edit_user' && cicle != 'accountreq' ) {
        getComboboxAccessionType(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxPartname(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxBlockname(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxScanregion(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxStain(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxSpecialStain(urlCommon,new Array("0","0","0","0","0","0","0"),false);
        getComboboxProcedure(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxOrgan(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxDelivery(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxReturn(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxPathService(urlCommon,new Array("0","0","0","0","0","0"));
        slideType(new Array("0","0","0","0","0","0"));
    }

    if( cicle && urlBase && ( cicle == 'edit_user' || cicle == 'accountreq' )  ) {
        getComboboxPathService(urlCommon,new Array("0","0","0","0","0","0"));
    }
}

function populateSelectCombobox( targetid, data, placeholder, multiple ) {

    if( placeholder ) {
        var allowClear = true;
    } else {
        var allowClear = false;
    }

    if( multiple ) {
        var multiple = true;
    } else {
        var multiple = false;
    }

    $(targetid).select2({
        placeholder: placeholder,
        allowClear: allowClear,
        width: combobox_width,
        dropdownAutoWidth: true,
        selectOnBlur: true,
        dataType: 'json',
        quietMillis: 100,
        multiple: multiple,
        data: data,
        createSearchChoice:function(term, data) {
            //if( term.match(/^[0-9]+$/) != null ) {
            //    console.log("term is digit");
            //}
            return {id:term, text:term};
        }
    });
}


//#############  stains  ##############//
function getComboboxStain(urlCommon, ids) {

    var url = urlCommon+"stain";

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default";
    }

    //console.log("stain.length="+stain.length);
    if( stain.length == 0 ) {
        //console.log("stain 0");
        $.ajax({
            url: url,
            async: asyncflag,
            timeout: _ajaxTimeout
        }).success(function(data) {
            stain = data;
            populateSelectCombobox( ".ajax-combobox-stain", stain, null );
            populateSelectCombobox( ".ajax-combobox-staintype", stain, null );
        });
    } else {
        //console.log("stain exists");
        populateSelectCombobox( ".ajax-combobox-stain", stain, null );
        populateSelectCombobox( ".ajax-combobox-staintype", stain, null );
    }

    if( cicle == "new"  ) {
        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
        var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
        var targetid = id+"stain_0_field";
        $(targetid).select2('val', '1');
    }

}

function getComboboxSpecialStain(urlCommon, ids, preset) {

    var url = urlCommon+"stain";

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default";
    }

    var targetid = "";
    if( cicle == "new" || (cicle == "amend" && preset) || (cicle == "edit" && preset) ) {
        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
        var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
        targetid = id+"specialStains_"+ids[6]+"_staintype";
        //console.log("targetid="+targetid);
    }

    if( stain.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                stain = data;
                populateSelectCombobox( ".ajax-combobox-staintype", stain, null );
            });
    } else {
        populateSelectCombobox( targetid, stain, null );
    }

    //console.log("special stain preset="+preset);
    if( targetid != "" ) {
        $(targetid).select2('val', '1');
    }
}

//#############  scan regions  ##############//
function getComboboxScanregion(urlCommon,ids) {

    var url = urlCommon+"scanregion";
    //console.log("scanregion.length="+scanregion.length);

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( scanregion.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            scanregion = data;
            populateSelectCombobox( ".ajax-combobox-scanregion", scanregion, null );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-scanregion", scanregion, null );
    }

    if( cicle == "new"  ) {
        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
        var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
        var targetid = id+"scan_0_scanregion";
        $(targetid).select2('data', {id: 'Entire Slide', text: 'Entire Slide'});
    }
}

//#############  source organs  ##############//
function getComboboxOrgan(urlCommon,ids) {
//    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3];   //+'_block_'+ids[4]+'_slide_'+ids[5];
//    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"organ";

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default";
    }

    if( organ.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            organ = data;
            populateSelectCombobox( ".ajax-combobox-organ", organ, "Source Organ" );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-organ", organ, "Source Organ" );
    }

}


//#############  procedure types  ##############//
function getComboboxProcedure(urlCommon,ids) {
//    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1];    //+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
//    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"procedure";
//    var targetid = id+"name_0_field";

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default";
    }

    if( procedure.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            procedure = data;
            populateSelectCombobox( ".ajax-combobox-procedure", procedure, "Procedure Type" );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-procedure", procedure, "Procedure Type" );
    }

}

//#############  Accession Type  ##############//
function getComboboxAccessionType(urlCommon,ids) {

    var url = urlCommon+"accessiontype";

    //console.log("orderformtype="+orderformtype);

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default&type="+orderformtype;
    }

    if( accessiontype.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                accessiontype = data;
                populateSelectCombobox( ".accessiontype-combobox", accessiontype, null );
                setAccessionMask();
            });
    } else {
        populateSelectCombobox( ".accessiontype-combobox", accessiontype, null );
    }

    if( cicle == "new"  ) {
        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2];
        var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
        var targetid = id+"accession_0_accessiontype";
        //console.log("targetid="+targetid);
        $(targetid).select2('val', 1);
    }
}

//#############  partname types  ##############//
function getComboboxPartname(urlCommon,ids) {

    var url = urlCommon+"partname";

//    if( cicle == "new" || cicle == "create" ) {
//        url = url + "?opt=default";
//    }

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( partname.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            partname = data;
            populateSelectCombobox( ".ajax-combobox-partname", partname, "Part Name" );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-partname", partname, "Part Name" );
    }

}

//#############  blockname types  ##############//
function getComboboxBlockname(urlCommon,ids) {

    var url = urlCommon+"blockname";

//    if( cicle == "new" || cicle == "create" ) {
//        url = url + "?opt=default";
//    }
    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( blockname.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            blockname = data;
            populateSelectCombobox( ".ajax-combobox-blockname", blockname, "Block Name" );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-blockname", blockname, "Block Name" );
    }

}




//#############  slide delivery  ##############//
function getComboboxDelivery(urlCommon,ids) {
    //var uid = "";   //'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
//    var id= "#oleg_orderformbundle_orderinfotype_";
    var url = urlCommon+"delivery";
//    var targetid = id+"slideDelivery";

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    //console.log("scanregion.length="+organ.length);
    if( delivery.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            delivery = data;
            populateSelectCombobox( ".ajax-combobox-delivery", delivery, null );
            if( cicle == "new"  ) {
                $(".ajax-combobox-delivery").select2('data', {id: "I'll give slides to Noah - ST1015E (212) 746-2993", text: "I'll give slides to Noah - ST1015E (212) 746-2993"});
            }
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-delivery", delivery, null );
        if( cicle == "new"  ) {
            $(".ajax-combobox-delivery").select2('data', {id: "I'll give slides to Noah - ST1015E (212) 746-2993", text: "I'll give slides to Noah - ST1015E (212) 746-2993"});
        }
    }

}

//#############  return slides to  ##############//
function getComboboxReturn(urlCommon,ids) {
    //var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    //var id= "#oleg_orderformbundle_orderinfotype_";
    var url = urlCommon+"return";
    //var targetid = id+"returnSlide";

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    //console.log("scanregion.length="+organ.length);
    if( returnslide.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            returnslide = data;
            populateSelectCombobox( ".ajax-combobox-return", returnslide, null );
            if( cicle == "new"  ) {
                $(".ajax-combobox-return").select2('data', {id: "Filing Room", text: "Filing Room"});
            }
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-return", returnslide, null );
        if( cicle == "new"  ) {
            $(".ajax-combobox-return").select2('data', {id: "Filing Room", text: "Filing Room"});
        }
    }

}

//#############  pathology service  ##############//
function getComboboxPathService(urlCommon,ids) {

    //******************* order pathology service *************************//
    //var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    //var id= "#oleg_orderformbundle_orderinfotype_";
    var targetid = ".ajax-combobox-pathservice";
    var url = urlCommon+"pathservice";

    if( cicle == "new" || cicle == "create" || cicle == "accountreq" ) {
        url = url + "?opt=default";
    }

    //console.log("cicle="+cicle+", url="+url+", targetid="+targetid);
    if( cicle == 'accountreq' ) {
        var multiple = true;
    } else {
        var multiple = false;
    }

    if( pathservice.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            pathservice = data;
            populateSelectCombobox( targetid, pathservice, "Pathology Service", multiple );
        });
    } else {
        populateSelectCombobox( targetid, pathservice, "Pathology Service", multiple );
    }

//    //******************* user pathology service *************************//
//    //var targetid = ".ajax-combobox-pathservice";    //"#oleg_orderformbundle_user_pathologyServices";
//    populateSelectCombobox( targetid, pathservice, "Pathology Service" );
//
//    //console.log("userpathservice.length="+userpathservice.length);
//    if( userpathservice.length == 0 && !userpathserviceflag ) {
//        $.ajax({
//            url: urlCommon+"userpathservice",
//            type: 'POST',
//            data: {username: user_name},
//            dataType: 'json',
//            async: asyncflag,
//            success: function(data) {
//                userpathserviceflag = true;
//                userpathservice = data;
//            }
//        });
//    }
//
//    if( cicle == "new" ) {
//        $(targetid).select2('data', userpathservice);
//    }

}


function initComboboxJs(ids) {

    if( urlBase ) {

        cicle = 'new';

        getComboboxAccessionType(urlCommon,ids);
        getComboboxPartname(urlCommon,ids);
        getComboboxBlockname(urlCommon,ids);
        getComboboxStain(urlCommon,ids);
        getComboboxSpecialStain(urlCommon,ids,false);
        getComboboxScanregion(urlCommon,ids);
        getComboboxProcedure(urlCommon,ids);
        getComboboxOrgan(urlCommon,ids);
        getComboboxPathService(urlCommon,ids);
        slideType(ids);
    }
}


function slideType(ids) {    
    
    //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_block_1_slide_0_slidetype
    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_slidetype";

    $(id).change(function(e) {   //.slidetype-combobox
        //console.log("slidetype-combobox changed: this id="+$(this).attr('id')+",class="+$(this).attr('class'));
        //e.preventDefault();
        var parent = $(this).parent().parent().parent().parent().parent().parent().parent().parent();
        //console.log("parent: id="+parent.attr('id')+",class="+parent.attr('class'));
        var blockValue = parent.find('.element-title').first();
        //console.log("slidetype-combobox: id="+parent.find('.slidetype-combobox').first().attr('id')+",class="+parent.find('.slidetype-combobox').first().attr('class'));
        var slideType = parent.find('.slidetype-combobox').first().select2('val');
        //console.log("blockValue: id="+blockValue.attr('id')+",class="+blockValue.attr('class')+",slideType="+slideType);
        var keyfield = parent.find('#check_btn');
        if( slideType == 3 ) {   //'Cytopathology'
            //console.log("Cytopathology is chosen = "+slideType);
            keyfield.attr('disabled','disabled'); 
            disableInElementBlock(parent.find('#check_btn').first(), true, "all", null, null);
            var htmlDiv = '<div class="element-skipped">Block is not used for cytopathology slide</div>';
            parent.find('.element-skipped').first().remove();
            blockValue.after(htmlDiv);
            blockValue.hide();
            parent.find('.form-btn-options').first().hide();
            //parent.find('.panel-body').first().css("border-color", "#C0C0C0");
        } else {    
            //disableInElementBlock(parent.find('#check_btn').first(), false, "all", null, null);
            disableInElementBlock(parent.find('#check_btn').first(), true, null, "notkey", null);
            parent.find('.element-skipped').first().remove();
            blockValue.show();
            keyfield.removeAttr('disabled'); 
            parent.find('.form-btn-options').first().show();
            //parent.find('.panel-body').first().css("border-color", "#1268B3");
        }
        
    });   
}
