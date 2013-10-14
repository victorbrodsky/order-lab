/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

var combobox_width = '100%'; //'element'
var urlCommon = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/util/";
//var urlCommon = "http://collage.med.cornell.edu/order/util/";
var type = $("#formtype").val();
var cicle = $("#formcicle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();
var proxyuser_name = $("#proxyuser_name").val();
var proxyuser_id = $("#proxyuser_id").val();
//console.log("type="+type+", cicle="+cicle);

function regularCombobox() {
    //resolve
    $("select.combobox").select2({
        width: combobox_width,
        dropdownAutoWidth: true,
        readonly: true
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
  
    //initAjaxData();
    //var url = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/multi/getdata/stain";
    
//    var comboboxes = new Array("stain","procedure","organ","scanregion","delivery","return");
//    
//    for( var i in comboboxes ) {   
//        
//        var prefix = comboboxes[i];
//        var target = ".ajax-combobox-"+prefix;
//        
//        var url = "http://localhost/scanorder/Scanorders2/web/app_dev.php/util/"+prefix;
//        
//        console.log("prefix="+prefix+", target="+target);
//        
//        $.ajax(url).success(function(data) {
//            json = eval(data);
//            stainsData = eval(data);
//            console.log("prefix2="+prefix+", target2="+target);
//            $(target).select2({
//                placeholder: "Search",
//                width: 'element',
//                dropdownAutoWidth: true,
//                selectOnBlur: true,
//                dataType: 'json',
//                quietMillis: 100,
//                data: data,
//                createSearchChoice:function(term, data) {
//                    //console.log(data.length);
//                    if ($(data).filter(function() {
//                        return this.text.localeCompare(term)===0;
//                    }).length===0) {
//    //                    var newitem = [];
//    //                    newitem['id']=data.length;    //= array("id":data.length, "text":term);
//    //                    newitem['text']=term;
//    //                    data.push(newitem);
//                        return {id:term, text:term};
//                    }
//                }
//
//            });
//
//            //$(".ajax-combobox-"+comb).select2('data', {id: 1, text: 'H&E'});
//        });
//    
//    } //for

    //var urlCommon = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/util/";

    if( cicle != "show" ) {
        getComboboxScanregion(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxStain(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxProcedure(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxOrgan(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxDelivery(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxReturn(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxPathService(urlCommon,new Array("0","0","0","0","0","0"));
    }

}

//    //console.log("Stains="+dataStore.getStains());
//
//    $(".ajax-combobox111").select2({
//        placeholder: "Search",
//        width: combobox_width,
//        dropdownAutoWidth: true,
//        selectOnBlur: true,
//        //data: dataStore.getStains(),
//        createSearchChoice:function(term, data) {
//            if ($(data).filter(function() {
//                return this.text.localeCompare(term)===0;
//            }).length===0) {return {id:term, text:term};}
//        },
//        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
//            url: "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/multi/getdata/stain",
//            dataType: 'json',
//            data: function (term) {
//                //console.log("term="+term);
//                return {
//                    name: term // search term
//                };
//            },
//            results: function (data) { // parse the results into the format expected by Select2.
//                // since we are using custom formatting functions we do not need to alter remote JSON data
//                //console.log("data="+data[0].text);
//                return {results: data};
//            }
//        },
//        formatResult: function (name) {
//            console.log("name="+name.text);
//            Stains.push(name.text);
//            return name.text;
//        }
//        //formatSelection: movieFormatSelection, // omitted for brevity, see the source of this page
//        //dropdownCssClass: "bigdrop" // apply css that makes the dropdown taller
//        //escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
//    });
//
//}

//var dataStore = (function(){
//    var Stains;
//
//    $.ajax({
//        type: "GET",
//        url: "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/multi/getdata/stain",
//        dataType: "json",
//        success : function(data) {
//            Stains = data;
//        }
//    });
//
//    return {getStains : function()
//    {
//        if (Stains) return Stains;
//        // else show some error that it isn't loaded yet;
//    }};
//})();

//function initAjaxData() {
//    $.ajax("http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/multi/getdata/stain").success(function(data) {
//
//        json = eval(data);
//        Stains = json;
//        //console.log("Stains="+Stains);
//
////        console.log("id="+data[0].id+",data="+data[0].text);
////        //Stains.push(name.text);
////        //Stains = data;
////
////        for( item in data ) {
////            console.log('item='+item);
////            console.log("id="+item['id']+",text="+item['text']);
////            Stains.push(item.text);
////        }
//
//    });
//}


//#############  stains  ##############//
function getComboboxStain(urlCommon, ids) {
    var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    //var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"stain";

    //oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_block_0_slide_0_stain_0_name
    //oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_block_0_slide_0_stain_0_name
    //console.log("stain id="+id);

    $.ajax(url).success(function(data) {
        //json = eval(data);
        var targetid = id+"stain_0_name";
        if( type == "single" ) {
            targetid = "#oleg_orderformbundle_staintype_name";
        }
        //var target = "#oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_block_0_slide_0_stain_0_name";
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
                console.log("data="+data['text']);
                console.log("data="+data[0].text);
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
    var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
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
    var uid = 'patient_'+ids[0]+'_specimen_'+ids[1];    //+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"procedure";
    $.ajax(url).success(function(data) {
        //oleg_orderformbundle_orderinfotype_patient_0_specimen_0_proceduretype
        var targetid = id+"proceduretype";
        if( type == "single" ) {
            targetid = "#oleg_orderformbundle_specimentype_proceduretype";
        }
        //console.log("targetid="+targetid);
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
    var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3];   //+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"organ";
    $.ajax(url).success(function(data) {

        //oleg_orderformbundle_orderinfotype_patient_0_specimen_1_accession_0_part_0_sourceOrgan
        var targetid = id+"sourceOrgan";
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

        //oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_primaryOrgan
        var targetid = id+"primaryOrgan";
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
    //var uid = "";   //'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
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
    //var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
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
    //var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
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
//            console.log("userpathservice="+data['id']);
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

function initComboboxJs(ids) {

    //var urlCommon = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/util/";
    getComboboxStain(urlCommon,ids);
    getComboboxScanregion(urlCommon,ids);
    getComboboxProcedure(urlCommon,ids);
    getComboboxOrgan(urlCommon,ids);
    getComboboxPathService(urlCommon,ids);
//    getComboboxDelivery(urlCommon,ids);
//    getComboboxReturn(urlCommon,ids);

    //oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_block_0_slide_1_stain_0_name
    //oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_block_0_slide_1_scan_0_scanregion
    //console.log("target id="+id);
    //var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    //var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";

    //$(id+"stain_0_name").select2('data', {id: 1, text: 'H&E'});
    //$(id+"scan_0_scanregion").select2('data', {id: 1, text: 'Entire Slide'});
    //$(id+"delivery").select2('data', {id: 1, text: "I'll give slides to Noah - ST1015E (212) 746-2993"});
    //$(id+"return").select2('data', {id: 1, text: "Filing Room"});
}
