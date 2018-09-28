/**
 * Created by ch3 on 1/30/2018.
 */


// function transresUpdateInvoice(invoiceOid) {
//     console.log("transresUpdateInvoice: invoiceOid="+invoiceOid);
//
//     //var form = $("#change_invoice_form_"+invoiceOid);
//     //var paid = form.find("#invoice-paid").val();
//
//     var paid = $("#invoice-paid-"+invoiceOid).val();
//     console.log("paid="+paid);
//
//     var discountNumeric = $("#invoice-discountNumeric-"+invoiceOid).val();
//     var discountPercent = $("#invoice-discountPercent-"+invoiceOid).val();
//
//     var comment = $("#invoice-comment-"+invoiceOid).val();
//     console.log("comment="+comment);
//
//     var url = Routing.generate('translationalresearch_invoice_update_ajax');
//
//     $.ajax({
//         url: url,
//         timeout: _ajaxTimeout,
//         type: "POST",
//         data: {invoiceOid:invoiceOid, paid:paid, comment:comment, discountNumeric:discountNumeric, discountPercent:discountPercent},
//         async: false,
//     }).success(function(response) {
//         //console.log(response);
//         if( response == "OK" ) {
//             //reload parent page
//             window.location.reload(true);
//         }
//     }).done(function() {
//         //lbtn.stop();
//     }).error(function(jqXHR, textStatus, errorThrown) {
//         console.log('Error : ' + errorThrown);
//     });
//
// }

function transresUpdateInvoiceStatus(invoiceOid,status) {
    console.log("transresUpdateInvoice: invoiceOid="+invoiceOid);

    //remove all buttons
    $(".updateInvoiceBtn").remove();
    //insert new text to the updateInvoiceBtnHolder
    $(".updateInvoiceBtnHolder").html("Please wait ...");

    //var form = $("#change_invoice_form_"+invoiceOid);
    //var paid = form.find("#invoice-paid").val();

    var paid = $("#invoice-paid-"+invoiceOid).val();
    console.log("paid="+paid);

    var comment = $("#invoice-comment-"+invoiceOid).val();
    console.log("comment="+comment);

    var discountNumeric = $("#invoice-discountNumeric-"+invoiceOid).val();
    var discountPercent = $("#invoice-discountPercent-"+invoiceOid).val();
    var total = $("#invoice-total-"+invoiceOid).val();
    var due = $("#invoice-due-"+invoiceOid).val();

    var url = Routing.generate('translationalresearch_invoice_update_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "POST",
        data: {
            invoiceOid: invoiceOid,
            discountNumeric: discountNumeric,
            discountPercent: discountPercent,
            paid: paid,
            total: total,
            due: due,
            comment: comment,
            status: status
        },
        async: false,
    }).success(function(response) {
        //console.log(response);
        if( response == "OK" ) {
            //reload parent page
            window.location.reload(true);
        }
    }).done(function() {
        //lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    });

}




function transresInvoiceItemListeneres(){
    //quantity or unit price update => update total
    $('.invoiceitem-quantity, .invoiceitem-unitPrice').on('input', function(event) {
        //console.log("update row total");
        var invoiceItemRow = $(this).closest('.user-collection-holder');
        var quatity = invoiceItemRow.find(".invoiceitem-quantity").val();
        var unitPrice = invoiceItemRow.find(".invoiceitem-unitPrice").val();
        //console.log("row quatity="+quatity+"; unitPrice="+unitPrice);
        var invoiceItemTotalEl = invoiceItemRow.find(".invoiceitem-total");
        if( quatity && unitPrice ) {
            var total = parseFloat(quatity) * parseFloat(unitPrice);
            total = transresRoundDecimal(total);
            //console.log("row total="+total);
            invoiceItemTotalEl.val(total);
        } else {
            invoiceItemTotalEl.val(null);
        }
        //console.log("transres UpdateSubTotal: triggered by claculated row total");
        transresUpdateSubTotal(this);
    });

    //total update => update subtotal and total
    $('.invoiceitem-total').on('input', function(event) {
        //console.log("transres UpdateSubTotal: triggered by manually update row total");
        //var holder = $(this).closest('.invoice-financial-fields');
        transresUpdateSubTotal(this);
    });

    $('.invoice-discountNumeric').on('input', function(event) {
        // $('.invoice-discountPercent').val(null);
        // transresUpdateTotal();
        //var holder = $(this).closest('.invoice-financial-fields');
        //console.log("discountNumeric updated");
        //console.log(holder);
        transresDiscountNumericUpdate(this);
    });
    $('.invoice-discountPercent').on('input', function(event) {
        // $('.invoice-discountNumeric').val(null);
        // transresUpdateTotal();
        //var holder = $(this).closest('.invoice-financial-fields');
        //console.log("discountPercent updated");
        transresDiscountPercentUpdate(this);
    });

    $('.invoice-paid').on('input', function(event) {
        //var holder = $(this).closest('.invoice-financial-fields');
        //console.log("paid updated");
        transresUpdateDue(this);
    });
}

function transresDiscountNumericUpdate(thisEl) {
    var holder = $(thisEl).closest('.invoice-financial-fields');
    console.log("transres DiscountNumericUpdate holder:");
    console.log(holder);
    holder.find('.invoice-discountPercent').val(null);
    transresUpdateTotal(thisEl);
}

function transresDiscountPercentUpdate(thisEl) {
    var holder = $(thisEl).closest('.invoice-financial-fields');
    holder.find('.invoice-discountNumeric').val(null);
    transresUpdateTotal(thisEl);
}

function transresUpdateSubTotal(thisEl) { //invoiceItemTotalEl
    //console.log("update subtotal and total");
    //var totals = invoiceItemTotalEl.closest('.invoice-financial-fields').find(".invoiceitem-total");
    var holder = $(thisEl).closest('.invoice-financial-fields');
    var totals = holder.find(".invoiceitem-total");
    var subTotal = 0;
    totals.each(function() {
        var total = $(this).val();
        //console.log("total="+total);
        if( !total ) {
            total = 0;
        }
        subTotal = subTotal + parseFloat(total);
    });
    subTotal = transresRoundDecimal(subTotal);
    //console.log("subTotal="+subTotal);
    holder.find(".invoice-subTotal").val(subTotal);
    transresUpdateTotal(thisEl);
}

function transresUpdateTotal(thisEl) {
    var holder = $(thisEl).closest('.invoice-financial-fields');
    console.log("transresUpdateTotal holder:");
    console.log(holder);
    var discount = 0;
    var discountNumeric = holder.find(".invoice-discountNumeric").val();
    var discountPercent = holder.find(".invoice-discountPercent").val();
    var subTotal = holder.find(".invoice-subTotal").val();

    console.log("count="+$(".invoice-discountNumeric").length);
    console.log("transresUpdateTotal: discountNumeric="+discountNumeric+"; discountPercent="+discountPercent+"; subTotal="+subTotal);

    if( subTotal ) {
        if( discountNumeric ) {
            discount = parseFloat(discountNumeric);
        }
        if( discountPercent ) {
            discount = subTotal * (parseFloat(discountPercent)/100);
        }
    }

    var total = subTotal - discount;

    total = transresRoundDecimal(total);
    holder.find(".invoice-total").val(total);

    transresUpdateDue(thisEl);
}

function transresRoundDecimal(value) {
    return Number(Math.round(value+'e2')+'e-2').toFixed(2); //1.005 => 1.01
}

//update Bill To
function transresInvoicePiListeneres(){
    $('.transres-invoice-principalInvestigator').on("change", function(e) {
        var piId = $(this).select2('val');
        //console.log("transres-invoice-principalInvestigator change: piId="+piId);
        //$('.transres-invoice-invoiceTo').val(piId);
        transresUpdateBillTo(piId);
    });
}
function transresUpdateBillTo(userId) {
    $(".transres-alert").find(".alert").html("");
    $(".transres-alert").hide();

    var url = Routing.generate('translationalresearch_invoice_get_billto_info');
    //url = url + "/" + projectId + "/" + irbExpDate

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        type: "GET",
        data: {userId: userId },
        //dataType: 'json',
        async: asyncflag
    }).success(function(response) {
        //console.log(response);
        if( response == "NotOK" ) {
            $(".transres-alert").find(".alert").html(response);
            $(".transres-alert").show();
        } else {
            //populate textarea
            $('.transres-invoice-invoiceTo').val(response);
            var height = $('.transres-invoice-invoiceTo').prop('scrollHeight');
            //console.log('height='+height);
            $('.transres-invoice-invoiceTo').height(height);
        }
    }).done(function() {
        //lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
        $(".transres-alert").find(".alert").html(errorThrown);
        $(".transres-alert").show();
    });
}

//"Please Wait" for any clicked btn
//        function transresInvoiceBtnOnClick() {
//            $('.btn-with-wait').on("click", function(e) {
//                //console.log("on click .btn-with-wait");
//                $(this).html('Please Wait ...');
//                //$(this).attr("disabled", true);
//            });
//        }

function transresUpdateDue(thisEl) {
    var holder = $(thisEl).closest('.invoice-financial-fields');
    var total = holder.find(".invoice-total").val();
    var paid = holder.find(".invoice-paid").val();
    var due = parseFloat(total);

    if( total && paid ) {
        due = parseFloat(total) - parseFloat(paid);
    }

    due = transresRoundDecimal(due);
    holder.find(".invoice-due").val(due);
}

