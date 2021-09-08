/*
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/9/14
 * Time: 4:58 PM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function() {


    if( $('.select2-list-type').select2('val') == "default" ) {
        $(".select2-list-original").select2("readonly", true);
        $('.select2-list-original').select2('val',null);
    }

    $('.select2-list-type').on("change", function(e) {

        //console.log("type change listener, val="+$('.select2-list-type').select2('val'));
        if( $('.select2-list-type').select2('val') == "default" ) {
            //console.log("default");
            $(".select2-list-original").select2("readonly", true);
            $('.select2-list-original').select2('val',null);
        } else {
            $(".select2-list-original").select2("readonly", false);
        }

    });

    //Fee price list
    newPriceListListener();
    // if(0) {
    //     console.log("listmacros cycle=" + cycle);
    //     if (cycle == 'edit' || cycle == 'new') {
    //         if ($(".user-prices-holder").length > 0) {
    //             console.log(".user-prices-holder exists");
    //
    //             $('select.field-priceList').on("change", function (e) {
    //
    //                 $('#oleg_userdirectorybundle_genericlist_submit').show();
    //
    //                 var value = $(this).select2('val');
    //                 var data = $(this).select2('data');
    //                 if (data) {
    //                     var thisId = data.id;
    //                 } else {
    //                     return;
    //                 }
    //
    //                 console.log("priceList change listener, val=" + value + ", id=" + thisId);
    //
    //                 var priceListArr = [];
    //
    //                 $("select.field-priceList").each(function () {
    //
    //                     var priceListData = $(this).select2('data');
    //                     if (priceListData) {
    //                         var priceListDataId = priceListData.id;
    //                         var priceListDataText = priceListData.text;
    //                         //priceListArr.push(priceListDataId);
    //                         if (priceListArr[priceListDataId]) {
    //                             //alert("Duplicate priceListDataId="+priceListDataId+", priceListDataText="+priceListDataText);
    //                             $('#oleg_userdirectorybundle_genericlist_submit').hide();
    //                         } else {
    //                             priceListArr[priceListDataId] = true;
    //                         }
    //                     }
    //                 });
    //
    //                 //$('#oleg_userdirectorybundle_genericlist_submit').show();
    //                 //alert("EOF validation");
    //             });//on change
    //
    //             //remove-pricelist-btn
    //             // $( ".remove-pricelist-btn" ).click(function() {
    //             //     console.log("remove-pricelist-btn click");
    //             //     $('#oleg_userdirectorybundle_genericlist_submit').show();
    //             // });//on click
    //
    //             //Remove button clicked => price list removed
    //             $(".remove-pricelist-btn").on("remove", function () {
    //                 //alert("Element was removed");
    //                 console.log("remove-pricelist-btn click");
    //                 $('#oleg_userdirectorybundle_genericlist_submit').show();
    //             });
    //
    //             //Add new price list
    //
    //         }//if user-prices-holder
    //     }//if new edit
    // }


});

function newPriceListListener(holder) {
    console.log("newPriceListListener");

    var targetClass = 'select.field-priceList';

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        priceListEl = holder.find(targetClass);
    } else {
        var priceListEl = $(targetClass);
    }

    console.log('total priceList count=' + priceListEl.length);

    if( priceListEl.length == 0 ) {
        return;
    }

    console.log('_cycleShow='+_cycleShow);
    if( _cycleShow ) {
        return;
    }

    if( $(".user-prices-holder").length == 0 ) {
        return;
    }

    console.log(".user-prices-holder exists");

    priceListEl.on("change", function(e) {

        $('#oleg_userdirectorybundle_genericlist_submit').show();
        $('#pricelist-error').text("");
        $('#pricelist-error').hide();

        var value = $(this).select2('val');
        var data = $(this).select2('data');
        if( data ) {
            var thisId = data.id;
        } else {
            return;
        }

        console.log("priceList change listener, val="+value+", id="+thisId);

        var priceListArr = [];

        $("select.field-priceList").each(function(){

            var priceListData = $(this).select2('data');
            if( priceListData ) {
                var priceListDataId = priceListData.id;
                var priceListDataText = priceListData.text;
                //priceListArr.push(priceListDataId);
                if( priceListArr[priceListDataId] ) {
                    //alert("Duplicate priceListDataId="+priceListDataId+", priceListDataText="+priceListDataText);
                    $('#oleg_userdirectorybundle_genericlist_submit').hide();

                    //“More than one “initial” or “additional” unit price provided for
                    // the same item on the same price list. Please make sure this item
                    // has only one pair of the “initial” and “additional” unit price values per price list.”
                    var error = "More than one 'initial' or 'additional' unit price provided for the same " +
                        "item on the same price list. Please make sure this item " +
                        "has only one pair of the 'initial' and 'additional' unit price values per price list.";
                    $('#pricelist-error').text(error);
                    $('#pricelist-error').show();

                } else {
                    priceListArr[priceListDataId] = true;
                }
            }
        });

        //$('#oleg_userdirectorybundle_genericlist_submit').show();
        //alert("EOF validation");
    });//on change

    //Remove button clicked => price list removed
    $(".remove-pricelist-btn").on("remove", function () {
        //alert("Element was removed");
        console.log("remove-pricelist-btn click");
        $('#oleg_userdirectorybundle_genericlist_submit').show();
        $('#pricelist-error').text("");
        $('#pricelist-error').hide();
    });

}
