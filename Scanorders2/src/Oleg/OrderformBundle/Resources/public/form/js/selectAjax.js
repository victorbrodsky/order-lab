/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

var urlCommon = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/util/";

function regularCombobox() {
    //resolve
    $("select.combobox").select2({
        width: 'element',
        dropdownAutoWidth: true
        //selectOnBlur: true,
        //containerCssClass: 'combobox-width'
    });
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

    getComboboxScanregion(urlCommon,new Array("0","0","0","0","0","0"));
    getComboboxStain(urlCommon,new Array("0","0","0","0","0","0"));
    getComboboxProcedure(urlCommon,new Array("0","0","0","0","0","0"));
    getComboboxOrgan(urlCommon,new Array("0","0","0","0","0","0"));
    getComboboxDelivery(urlCommon,new Array("0","0","0","0","0","0"));
    getComboboxReturn(urlCommon,new Array("0","0","0","0","0","0"));


}

//    //console.log("Stains="+dataStore.getStains());
//
//    $(".ajax-combobox111").select2({
//        placeholder: "Search",
//        width: 'element',
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
    var id= "#s2id_oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"stain";

    //s2id_oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_block_0_slide_0_stain_0_name
    console.log("stain id="+id);

    $.ajax(url).success(function(data) {
        json = eval(data);
        //stainsData = eval(data);
        var target = ".ajax-combobox-stain";
        var targetid = id+"stain_0_name";
        $(target).select2({
            //placeholder: "Search",
            width: 'element',
            dropdownAutoWidth: true,
            selectOnBlur: true,
            dataType: 'json',
            quietMillis: 100,
            data: data,
            createSearchChoice:function(term, data) {
                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {
                    return {id:term, text:term};
                }
            }

        });

        $(targetid).select2('data', {id: 1, text: 'H&E'});

        //single form: s2id_oleg_orderformbundle_staintype_name
        $("#s2id_oleg_orderformbundle_staintype_name").select2('data', {id: 1, text: 'H&E'});
    });
}

//#############  scan regions  ##############//
function getComboboxScanregion(urlCommon,ids) {
    var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#s2id_oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"scanregion";
    $.ajax(url).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-scanregion").select2({
            //placeholder: "Region to scan",
            width: 'element',
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
    var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#s2id_oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"procedure";
    $.ajax(url).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-procedure").select2({
            placeholder: "Procedure Type",
            width: 'element',
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
    var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#s2id_oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"organ";
    $.ajax(url).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-organ").select2({
            placeholder: "Source Organ",
            width: 'element',
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
    var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#s2id_oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"delivery";
    $.ajax(url).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-delivery").select2({
            //placeholder: "Slide Delivery",
            width: 'element',
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
    });
}

//#############  return slides to  ##############//
function getComboboxReturn(urlCommon,ids) {
    var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#s2id_oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"return";
    $.ajax(url).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-return").select2({
            //placeholder: "Return Slides to",
            width: 'element',
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



function initComboboxJs(ids) {

    //var urlCommon = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/util/";
    getComboboxStain(urlCommon,ids);
    getComboboxScanregion(urlCommon,ids);
    getComboboxProcedure(urlCommon,ids);
    getComboboxOrgan(urlCommon,ids);
//    getComboboxDelivery(urlCommon,ids);
//    getComboboxReturn(urlCommon,ids);

    //s2id_oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_block_0_slide_1_stain_0_name
    //s2id_oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_block_0_slide_1_scan_0_scanregion
    //console.log("target id="+id);
    //var uid = 'patient_'+ids[0]+'_specimen_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    //var id= "#s2id_oleg_orderformbundle_orderinfotype_"+uid+"_";

    //$(id+"stain_0_name").select2('data', {id: 1, text: 'H&E'});
    //$(id+"scan_0_scanregion").select2('data', {id: 1, text: 'Entire Slide'});
    //$(id+"delivery").select2('data', {id: 1, text: "I'll give slides to Noah - ST1015E (212) 746-2993"});
    //$(id+"return").select2('data', {id: 1, text: "Filing Room"});
}
