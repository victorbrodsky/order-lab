/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

var Stains = [];

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
    //#############  stains  ##############//
    var stainUrl = "http://localhost/scanorder/Scanorders2/web/app_dev.php/util/getstain";
    $.ajax(stainUrl).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-stain").select2({
            placeholder: "Search",
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

        $(".ajax-combobox-stain").select2('data', {id: 1, text: 'H&E'});
    });

    //#############  procedure types  ##############//
    var stainUrl = "http://localhost/scanorder/Scanorders2/web/app_dev.php/util/getprocedure";
    $.ajax(stainUrl).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-procedure").select2({
            placeholder: "Procedure Type",
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
    });
    
    
    //#############  source organs  ##############//
    var stainUrl = "http://localhost/scanorder/Scanorders2/web/app_dev.php/util/getorgan";
    $.ajax(stainUrl).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-organ").select2({
            placeholder: "Source Organ",
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
    });
    
    
    //#############  scan regions  ##############//
    var stainUrl = "http://localhost/scanorder/Scanorders2/web/app_dev.php/util/getscanregion";
    $.ajax(stainUrl).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-scanregion").select2({
            placeholder: "Region to scan",
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
        $(".ajax-combobox-scanregion").select2('data', {id: 1, text: 'Entire Slide'});
    });
    
    
    //#############  slide delivery  ##############//
    var stainUrl = "http://localhost/scanorder/Scanorders2/web/app_dev.php/util/getslidedelivery";
    $.ajax(stainUrl).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-delivery").select2({
            placeholder: "Slide Delivery",
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
    
    //#############  return slides to  ##############//
    var stainUrl = "http://localhost/scanorder/Scanorders2/web/app_dev.php/util/getreturnslide";
    $.ajax(stainUrl).success(function(data) {
        json = eval(data);
        $(".ajax-combobox-return").select2({
            placeholder: "Return Slides to",
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
