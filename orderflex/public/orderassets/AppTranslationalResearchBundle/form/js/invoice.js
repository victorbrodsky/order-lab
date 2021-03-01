/**
 * Created by Oleg Ivanov on 1/30/2018.
 */

function transresUpdateInvoiceStatus(invoiceId,status) {
    //console.log("transresUpdateInvoice: invoiceId="+invoiceId);

    $("#modal-invoice-error-"+invoiceId).hide();
    $("#modal-invoice-error-"+invoiceId).html(null);

    //remove all buttons
    //$(".updateInvoiceBtn").hide();
    //insert new text to the updateInvoiceBtnHolder
    //$("#updateInvoiceBtnHolder-"+invoiceId).html("Please wait ...");
    $("#updateInvoiceBtnHolder-"+invoiceId).hide();

    //var form = $("#change_invoice_form_"+invoiceId);
    //var paid = form.find("#invoice-paid").val();

    var paid = $("#invoice-paid-"+invoiceId).val();
    //console.log("paid="+paid);

    var comment = $("#invoice-comment-"+invoiceId).val();
    //console.log("comment="+comment);

    var discountNumeric = $("#invoice-discountNumeric-"+invoiceId).val();
    var discountPercent = $("#invoice-discountPercent-"+invoiceId).val();
    var administrativeFee = $("#invoice-administrativeFee-"+invoiceId).val();
    var total = $("#invoice-total-"+invoiceId).val();
    var due = $("#invoice-due-"+invoiceId).val();

    //console.log("status="+status);

    if( status == "Paid Partially" ) {

        //If “paid partially” is pressed but no amount is typed into the “paid” field,
        // a red error well should be displayed stating “Please enter the partial amount into the “paid” field.”
        if( !paid ) {
            var error = "Please enter the partial amount into the 'Paid' field.";
            $("#modal-invoice-error-"+invoiceId).show();
            $("#modal-invoice-error-"+invoiceId).html(error);
            $("#updateInvoiceBtnHolder-"+invoiceId).show();
            return false;
        }

        //If any amount typed into the “Paid” field is equal to the amount in the “Due” field and “Paid Partially”
        // button is pressed, a red error well should be displayed stating “The amount entered into the “paid” field
        // is equal to the amount due. If the invoice has been paid in full, please press the “Paid in Full” button.
        // If the invoice has been paid partially, please enter the partial amount paid and press the “Paid Partially” button.”
        var paidFloat = 0;
        if( paid ) {
            var paidFloat = parseFloat(paid);
        }
        var paidTotal = 0;
        if( total ) {
            var paidTotal = parseFloat(total);
        }
        if( paidFloat == paidTotal ) {
            var error = "The amount entered into the 'Paid' field is equal to the amount due in the 'Total' field."+
                " If the invoice has been paid in full, please press the 'Paid in Full' button."+
                " If the invoice has been paid partially, please enter the partial amount paid and press the 'Paid Partially' button.";
            $("#modal-invoice-error-"+invoiceId).show();
            $("#modal-invoice-error-"+invoiceId).html(error);
            $("#updateInvoiceBtnHolder-"+invoiceId).show();
            return false;
        }
    }

    //If any amount typed into the “Paid” field is less than the amount in the “Due” field
    // and “Paid in Full” button is pressed,
    // a red error well should be displayed stating “The amount entered into the “paid” field
    // does not equal the amount due. If the invoice has been paid in full, please delete
    // the value in the “Paid” field and press the “Paid in Full” button.
    // If the invoice has been paid partially, please enter the amount paid and press the “Paid Partially” button.”
    if( status == "Paid in Full" && paid ) {
        var paidFloat = 0;
        if( paid ) {
            var paidFloat = parseFloat(paid);
        }
        var paidTotal = 0;
        if( total ) {
            var paidTotal = parseFloat(total);
        }
        //console.log(status+":"+"paid="+paid+"; total="+total);
        //console.log(status+":"+"paidFloat="+paidFloat+"; paidTotal="+paidTotal);
        if( paidFloat > 0 && paidFloat < paidTotal ) {
            //console.log(status+": error");
            var error = "The amount entered into the 'Paid' field does not equal the amount due in the 'Total' field."+
                " If the invoice has been paid in full, please delete the value in the 'Paid' field"+
                " and press the 'Paid in Full' button. If the invoice has been paid partially,"+
                " please enter the amount paid and press the 'Paid Partially' button.";
            //console.log("error="+error);
            $("#modal-invoice-error-"+invoiceId).show();
            $("#modal-invoice-error-"+invoiceId).html(error);
            $("#updateInvoiceBtnHolder-"+invoiceId).show();
            return false;
        }
    }

    $("#updateInvoiceBtnHolder-"+invoiceId).show();
    $("#updateInvoiceBtnHolder-"+invoiceId).html("Please wait ...");
    //return false; //testing

    var url = Routing.generate('translationalresearch_invoice_update_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "POST",
        data: {
            invoiceId: invoiceId,
            discountNumeric: discountNumeric,
            discountPercent: discountPercent,
            administrativeFee: administrativeFee,
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

// (function($){
//     $.event.special.destroyed = {
//         remove: function(o) {
//             if (o.handler) {
//                 o.handler()
//             }
//         }
//     }
// })(jQuery)

function removeInvoiceItemExistingObject( delBtn, classname ) {
    removeExistingObject(delBtn,classname);
    //console.log("removeInvoiceItemExistingObject");

    var thisEl = $('#oleg_translationalresearchbundle_invoice_subTotal');
    transresUpdateSubTotal(thisEl);
}


function transresInvoiceItemListeneres(){

    $('.invoiceitem-quantity').on('input', function(event) {
        var invoiceItemRow = $(this).closest('.user-collection-holder');
        transresAdjustQuantity(invoiceItemRow);

        transresCalculateTotals(invoiceItemRow);

        //console.log("transres UpdateSubTotal: triggered by claculated row total");
        transresUpdateSubTotal(this);
    });

    $('.invoiceitem-quantity, .invoiceitem-additionalQuantity').on('input', function(event) {
        var invoiceItemRow = $(this).closest('.user-collection-holder');
        transresValidateQuantity(invoiceItemRow);

        transresCalculateTotals(invoiceItemRow);

        //console.log("transres UpdateSubTotal: triggered by claculated row total");
        transresUpdateSubTotal(this);
    });

    $('.invoiceitem-unitPrice, .invoiceitem-additionalUnitPrice').on('input', function(event) {

        var invoiceItemRow = $(this).closest('.user-collection-holder');

        transresCalculateTotals(invoiceItemRow);

        //console.log("transres UpdateSubTotal: triggered by claculated row total");
        transresUpdateSubTotal(this);
    });

    // $('.invoiceitem-quantity, .invoiceitem-additionalQuantity, .invoiceitem-unitPrice, .invoiceitem-additionalUnitPrice').on('input', function(event) {
    //
    //     var invoiceItemRow = $(this).closest('.user-collection-holder');
    //
    //     transresCalculateTotals(invoiceItemRow);
    //
    //     //console.log("transres UpdateSubTotal: triggered by claculated row total");
    //     transres UpdateSubTotal(this);
    // });

    $('.invoice-subTotal').on('input', function(event) {
        transresUpdateTotal(this);
    });

    //total update => update subtotal and total
    $('.invoiceitem-total').on('input', function(event) {
        //console.log("transres UpdateSubTotal: triggered by manually update row total");
        //var holder = $(this).closest('.invoice-financial-fields');
        transresUpdateSubTotal(this);
    });

    $('.invoice-discountNumeric').on('input', function(event) {
        transresDiscountNumericUpdate(this);
    });
    $('.invoice-discountPercent').on('input', function(event) {
        //console.log("discountPercent updated");
        transresDiscountPercentUpdate(this);
    });
    $('.invoice-administrativeFee').on('input', function(event) {
        transresAdministrativeFeeUpdate(this);
    });

    $('.invoice-paid').on('input', function(event) {
        //var holder = $(this).closest('.invoice-financial-fields');
        //console.log("paid updated");
        transresUpdateDue(this);
    });

    // $('.invoiceitem-total1').on('destroyed', function(event) {
    //     console.log("invoiceItems destroyed");
    //     var thisEl = $('#oleg_translationalresearchbundle_invoice_subTotal');
    //     transresUpdateSubTotal(thisEl);
    // });
}

//If the user edits initial quantity, when the cursor leaves the form field, update the remaining quantity for the same item
function transresAdjustQuantity(invoiceItemRow) {
    var quantity = invoiceItemRow.find(".invoiceitem-quantity").val();
    var additionalQuantity = invoiceItemRow.find(".invoiceitem-additionalQuantity").val();
    var totalQuantity = invoiceItemRow.find(".original-total-quantity").val();

    if( totalQuantity == 0 ) {
        return;
    }

    var newAdditionalQuantity = parseInt(totalQuantity) - parseInt(quantity);

    if( newAdditionalQuantity != additionalQuantity ) {
        invoiceItemRow.find(".invoiceitem-additionalQuantity").val(newAdditionalQuantity);
    }
}

function transresValidateQuantity(invoiceItemRow) {
    var warningMessage = invoiceItemRow.find('.invoiceitem-warning-message');
    warningMessage.html("").hide();

    var totalQuantity = invoiceItemRow.find(".original-total-quantity").val();
    //console.log("totalQuantity="+totalQuantity);
    if( !totalQuantity ) {
        return;
    }

    var quantity = invoiceItemRow.find(".invoiceitem-quantity").val();
    var additionalQuantity = invoiceItemRow.find(".invoiceitem-additionalQuantity").val();
    //console.log("quantity="+quantity +", additionalQuantity="+additionalQuantity);

    if( !quantity ) {
        quantity = 0;
    }

    if( !additionalQuantity ) {
        additionalQuantity = 0;
    }

    var newTotalQuantity = parseInt(quantity) + parseInt(additionalQuantity);
    //console.log("totalQuantity="+totalQuantity +", newTotalQuantity="+newTotalQuantity);

    if( newTotalQuantity && totalQuantity != newTotalQuantity ) {
        var invoiceitemProductId = invoiceItemRow.find('.invoiceitem-product-id').val();
        var warning = "The total quantity for item "+invoiceitemProductId+
            " is not equal to the completed or requested quantity of "+totalQuantity+". " +
            "Please ensure the quantities on this invoice are correct.";
        warningMessage.html(warning).show();
    }

    //invoiceitem-warning-message
    //if cycle new or edit
    //console.log("cycle="+cycle);
    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    if( cycle == "new" || cycle == "edit" ) {
        var visibleWarningMessages = $('.invoiceitem-warning-message:visible');
        if (visibleWarningMessages.length > 0) {
            //invoice-warning-message "I have verified the listed quantities."
            $("#invoice-confirmation-warning-message").show();
            $('#submit-buttons-section').hide();
        } else {
            $("#invoice-confirmation-warning-message").hide();
            $('#submit-buttons-section').show();
        }
    }

}

function transresInvoiceConfirmationListener() {
    $('#invoice-confirmation-submit').on('input', function(event) {
        //console.log("invoice-confirmation-submit button clicked");

        if( $(this).is(':checked') ) {
            //console.log("ok confirmed");
            $('#submit-buttons-section').show();
        } else {
            $('#submit-buttons-section').hide();
        }

        return true;
    });
}

function transresCalculateTotals( invoiceItemRow ) {
    //var invoiceItemRow = invoiceItemRowEl.closest('.user-collection-holder');
    var quantity = invoiceItemRow.find(".invoiceitem-quantity").val();
    var additionalQuantity = invoiceItemRow.find(".invoiceitem-additionalQuantity").val();
    var unitPrice = invoiceItemRow.find(".invoiceitem-unitPrice").val();
    var additionalUnitPrice = invoiceItemRow.find(".invoiceitem-additionalUnitPrice").val();
    //console.log("row quantity="+quantity+"; unitPrice="+unitPrice);
    //console.log("row additionalQuantity="+additionalQuantity+"; additionalUnitPrice="+additionalUnitPrice);
    var invoiceItemTotalEl = invoiceItemRow.find(".invoiceitem-total");
    var totalEl1 = invoiceItemRow.find(".invoiceitem-total1");
    var totalEl2 = invoiceItemRow.find(".invoiceitem-total2");

    var total1 = 0;
    var total2 = 0;

    if( quantity && unitPrice ) {
        total1 = parseFloat(quantity) * parseFloat(unitPrice);
        total1 = transresRoundDecimal(total1);
        //console.log("row total1="+total1);
        totalEl1.val(total1);
    } else {
        totalEl1.val(null);
    }

    if( additionalQuantity && additionalUnitPrice ) {
        total2 = parseFloat(additionalQuantity) * parseFloat(additionalUnitPrice);
        total2 = transresRoundDecimal(total2);
        //console.log("row total2="+total2);
        totalEl2.val(total2);
    } else {
        totalEl2.val(null);
    }

    var total = parseFloat(total1) + parseFloat(total2);
    if( total ) {
        total = transresRoundDecimal(total);
        //console.log("total="+total);
        invoiceItemTotalEl.val(total);
    } else {
        invoiceItemTotalEl.val(null);
    }
}

function transresDiscountNumericUpdate(thisEl) {
    var holder = $(thisEl).closest('.invoice-financial-fields');
    //console.log("transres DiscountNumericUpdate holder:");
    //console.log(holder);
    holder.find('.invoice-discountPercent').val(null);
    transresUpdateTotal(thisEl);
}

function transresDiscountPercentUpdate(thisEl) {
    var holder = $(thisEl).closest('.invoice-financial-fields');
    holder.find('.invoice-discountNumeric').val(null);
    transresUpdateTotal(thisEl);
}

function transresAdministrativeFeeUpdate(thisEl) {
    transresUpdateTotal(thisEl);
}

function transresUpdateSubTotal(thisEl) { //invoiceItemTotalEl
    //console.log("update subtotal and total");
    //var totals = invoiceItemTotalEl.closest('.invoice-financial-fields').find(".invoiceitem-total");

    if( !thisEl ) {
        var thisEl = $('#oleg_translationalresearchbundle_invoice_subTotal');
    }

    var holder = $(thisEl).closest('.invoice-financial-fields');

    var invoiceItemRows = holder.find('.user-collection-holder');
    invoiceItemRows.each(function() {
        transresCalculateTotals($(this));
    });

    var totals = holder.find(".invoiceitem-total");
    var subTotal = 0;
    totals.each(function() {
        var total = $(this).val();
        //console.log("1 get total="+total);
        if( !total ) {
            total = 0;
        }
        //console.log("2 get total="+total);
        subTotal = subTotal + parseFloat(total);
        //console.log("get subTotal="+subTotal);
    });
    subTotal = transresRoundDecimal(subTotal);
    //console.log("subTotal="+subTotal);
    holder.find(".invoice-subTotal").val(subTotal);
    transresUpdateTotal(thisEl);
}

function transresUpdateTotal(thisEl) {
    var holder = $(thisEl).closest('.invoice-financial-fields');
    //console.log("transres UpdateTotal holder:");
    //console.log(holder);
    var total = 0;
    var discount = 0;
    var discountNumeric = holder.find(".invoice-discountNumeric").val();
    var discountPercent = holder.find(".invoice-discountPercent").val();
    var administrativeFee = holder.find(".invoice-administrativeFee").val();
    var subTotal = holder.find(".invoice-subTotal").val();

    //console.log("count="+$(".invoice-discountNumeric").length);
    //console.log("subTotal="+subTotal+", transres UpdateTotal: discountNumeric="+discountNumeric+"; discountPercent="+discountPercent+"; subTotal="+subTotal+", administrativeFee="+administrativeFee);

    if( subTotal ) {
        if( discountNumeric ) {
            discount = parseFloat(discountNumeric);
        }
        if( discountPercent ) {
            discount = subTotal * (parseFloat(discountPercent)/100);
        }
    }

    //if( subTotal && discount && subTotal > 0 && discount > 0 ) {
        total = parseFloat(subTotal) - parseFloat(discount);
    //}

    if( administrativeFee ) {
        total = parseFloat(total) + parseFloat(administrativeFee);
    }

    total = transresRoundDecimal(total);
    //console.log("total="+total);
    holder.find(".invoice-total").val(total);

    transresUpdateDue(thisEl);

    //update subsidy
    var defaultTotal = $('#invoice-default-total').val();
    //console.log("total="+total+", defaultTotal="+defaultTotal);
    if( total && defaultTotal ) {
        var subsidy = defaultTotal - total;
        //console.log("subsidy="+subsidy);
        if( subsidy > 0 ) {
            subsidy = transresRoundDecimal(subsidy);
        } else {
            subsidy = 0;
        }
        $("#invoice-subsidy-info").html(subsidy);
    }

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

function transresDisableWheelQuantity() {
    $('form').on('focus', 'input[type=number]', function (e) {
        $(this).on('wheel.disableScroll', function (e) {
            e.preventDefault()
        })
    })
    $('form').on('blur', 'input[type=number]', function (e) {
        $(this).off('wheel.disableScroll')
    })
}



