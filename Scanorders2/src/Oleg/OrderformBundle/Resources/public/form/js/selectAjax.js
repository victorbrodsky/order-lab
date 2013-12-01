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
var type = $("#formtype").val();
var cicle = $("#formcicle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();
var proxyuser_name = $("#proxyuser_name").val();
var proxyuser_id = $("#proxyuser_id").val();
//console.log("urlCommon="+urlCommon);

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
    if( !proxyuser_id ) {
        proxyuser_id = user_id;
        proxyuser_name = user_name;
    }
    $("#s2id_oleg_orderformbundle_orderinfotype_proxyuser").select2('data', {id: proxyuser_id, text: proxyuser_name});
}

function customCombobox() {

    if( cicle != "show" && urlBase ) {
        getComboboxPartname(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxBlockname(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxScanregion(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxStain(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxProcedure(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxOrgan(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxDelivery(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxReturn(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxPathService(urlCommon,new Array("0","0","0","0","0","0"));
        slideType(new Array("0","0","0","0","0","0"));
    }

}

//#############  stains  ##############//
function getComboboxStain(urlCommon, ids) {
    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    //var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"stain";

    //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_block_0_slide_0_stain_0_name
    //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_block_0_slide_0_stain_0_name
    //console.log("stain id="+id);

    $.ajax(url).success(function(data) {
        //json = eval(data);
        var targetid = id+"stain_0_field";
        if( type == "single" ) {
            targetid = "#oleg_orderformbundle_staintype_field";
        }
        //var target = "#oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_block_0_slide_0_stain_0_name";
        //console.log("targetid="+targetid);
        $(targetid).select2({
            //placeholder: "Search",
            width: combobox_width,
            dropdownAutoWidth: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                //console.log("data="+data['text']);
                //console.log("data="+data[0].text);
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {
                    return {id:term, text:term};
                }
            }

        });

        //console.log("targetid="+targetid);
        $(targetid).select2('data', {id: 1, text: 'H&E'});

    });
}

//#############  scan regions  ##############//
function getComboboxScanregion(urlCommon,ids) {
    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"scanregion";
    $.ajax(url).success(function(data) {
        var targetid = id+"scan_0_scanregion";
        if( type == "single" ) {
            targetid = "#oleg_orderformbundle_scantype_scanregion";
        }
        //console.log("targetid="+targetid);
        $(targetid).select2({
            //placeholder: "Region to scan",
            width: combobox_width,
            dropdownAutoWidth: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }

        });
        $(id+"scan_0_scanregion").select2('data', {id: 1, text: 'Entire Slide'});
        //single form: s2id_oleg_orderformbundle_staintype_name
        $("#s2id_oleg_orderformbundle_scantype_scanregion").select2('data', {id: 1, text: 'Entire Slide'});
    });
}

//#############  procedure types  ##############//
function getComboboxProcedure(urlCommon,ids) {
    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1];    //+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"procedure";
    $.ajax(url).success(function(data) {
        //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_name
        var targetid = id+"name_0_field";
        if( type == "single" ) {
            targetid = "#oleg_orderformbundle_proceduretype_name";
        }
        //console.log("proceduretype targetid="+targetid);
        //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_name_0_field
        //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_name
        $(targetid).select2({
            placeholder: "Procedure Type",
            width: combobox_width,
            dropdownAutoWidth: true,
            allowClear: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }

        });
    });
}


//#############  source organs  ##############//
function getComboboxOrgan(urlCommon,ids) {
    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3];   //+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"organ";
    $.ajax(url).success(function(data) {

        //oleg_orderformbundle_orderinfotype_patient_0_procedure_1_accession_0_part_0_sourceOrgan
        var targetid = id+"sourceOrgan_0_field";
        if( type == "single" ) {
            targetid = "#oleg_orderformbundle_parttype_sourceOrgan";
        }
        $(targetid).select2({
            placeholder: "Source Organ",
            width: combobox_width,
            dropdownAutoWidth: true,
            allowClear: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }

        });

        //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_primaryOrgan
        //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_diseaseType_0_primaryOrgan
        var targetid = id+"diseaseType_0_primaryOrgan";
        if( type == "single" ) {
            targetid = "#oleg_orderformbundle_parttype_primaryOrgan";
        }
        $(targetid).select2({
            placeholder: "Source Organ",
            width: combobox_width,
            dropdownAutoWidth: true,
            allowClear: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }

        });

    });
}



//#############  slide delivery  ##############//
function getComboboxDelivery(urlCommon,ids) {
    //var uid = "";   //'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_";
    var url = urlCommon+"delivery";
    $.ajax(url).success(function(data) {
        //oleg_orderformbundle_orderinfotype_slideDelivery
        var targetid = id+"slideDelivery";
        $(targetid).select2({
            //placeholder: "Slide Delivery",
            width: combobox_width,
            dropdownAutoWidth: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }
        });
        $(".ajax-combobox-delivery").select2('data', {id: 1, text: "I'll give slides to Noah - ST1015E (212) 746-2993"});
        //$(".ajax-combobox-delivery").select2('val', 0);
    });
}

//#############  return slides to  ##############//
function getComboboxReturn(urlCommon,ids) {
    //var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_";
    var url = urlCommon+"return";
    $.ajax(url).success(function(data) {
        //oleg_orderformbundle_orderinfotype_returnSlide
        var targetid = id+"returnSlide";
        $(targetid).select2({
            //placeholder: "Return Slides to",
            width: combobox_width,
            dropdownAutoWidth: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }

        });
        $(".ajax-combobox-return").select2('data', {id: 1, text: "Filing Room"});
    });
}

//#############  pathology service  ##############//
function getComboboxPathService(urlCommon,ids) {

    //******************* order pathology service *************************//
    //var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_";
    var url = urlCommon+"pathservice";
    $.ajax(url).success(function(data) {
        //oleg_orderformbundle_orderinfotype_pathologyService
        var targetid = id+"pathologyService";
        $(targetid).select2({
            placeholder: "Pathology Service",
            allowClear: true,
            width: combobox_width,
            dropdownAutoWidth: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }
        });

//        $.ajax(urlCommon+"userpathservice").success(function(data) {
//            //console.log("userpathservice="+data['id']);
//            $(targetid).select2('val', data['id']);
//        });

    });

    //******************* user pathology service *************************//
    //console.log("user_name="+user_name);
    var url = urlCommon+"pathservice";
    $.ajax(url).success(function(data) {
        //oleg_orderformbundle_user_pathologyServices
        var targetid = "#oleg_orderformbundle_user_pathologyServices";
        $(targetid).select2({
            placeholder: "Pathology Service",
            allowClear: true,
            multiple: true,
            width: combobox_width,
            dropdownAutoWidth: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }
        });

        //console.log("user_name="+user_name);
        $.ajax({
            url: urlCommon+"userpathservice",
            type: 'POST',
            data: {username: user_name},
            dataType: 'json',
            success: function(data) {
                //console.log("userpathservice="+data[0]['text']);
                $(targetid).select2('data', data);
            }
        });

    });

}

//#############  partname types  ##############//
function getComboboxPartname(urlCommon,ids) {
    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3];
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"partname";
    $.ajax(url).success(function(data) {
        var targetid = id+"partname_0_field";
        if( type == "single" ) {
            targetid = "#oleg_orderformbundle_partname_name";
        }
        $(targetid).select2({
            placeholder: "Part Name",
            width: combobox_width,
            dropdownAutoWidth: true,
            allowClear: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }

        });
    });
}

//#############  blockname types  ##############//
function getComboboxBlockname(urlCommon,ids) {
    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4];
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"blockname";
    $.ajax(url).success(function(data) {
        var targetid = id+"blockname_0_field";
        if( type == "single" ) {
            targetid = "#oleg_orderformbundle_blockname_name";
        }
        $(targetid).select2({
            placeholder: "Block Name",
            width: combobox_width,
            dropdownAutoWidth: true,
            allowClear: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {return {id:term, text:term};}
            }

        });
    });
}

function initComboboxJs(ids) {

    if( urlBase ) {
        getComboboxPartname(urlCommon,ids);
        getComboboxBlockname(urlCommon,ids);
        getComboboxStain(urlCommon,ids);
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
    
    //TODO: what to do with other slides in the same block?
    //Solution1: Onlye the first slide has slide type combobox.
    //Solution2: ecery slides under the same block has synchronized slide type.
//    if( ids[5] != '0' ) {
//        return;
//    }
    
    $(id).change(function(e) {   //.slidetype-combobox
        console.log("slidetype-combobox changed: this id="+$(this).attr('id')+",class="+$(this).attr('class'));
        //e.preventDefault();
        var parent = $(this).parent().parent().parent().parent().parent().parent().parent().parent();
        console.log("parent: id="+parent.attr('id')+",class="+parent.attr('class'));
        var blockValue = parent.find('.element-title').first();
        console.log("slidetype-combobox: id="+parent.find('.slidetype-combobox').first().attr('id')+",class="+parent.find('.slidetype-combobox').first().attr('class'));
        var slideType = parent.find('.slidetype-combobox').first().select2('val');
        console.log("blockValue: id="+blockValue.attr('id')+",class="+blockValue.attr('class')+",slideType="+slideType);
        var keyfield = parent.find('#check_btn');
        if( slideType == 3 ) {   //'Cytopathology'
            console.log("Cytopathology is chosen = "+slideType);
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
