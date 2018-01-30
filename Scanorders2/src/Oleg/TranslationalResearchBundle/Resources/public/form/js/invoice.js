/**
 * Created by ch3 on 1/30/2018.
 */


function transresUpdateInvoice(invoiceOid) {
    console.log("transresUpdateInvoice: invoiceOid="+invoiceOid);

    //var form = $("#change_invoice_form_"+invoiceOid);
    //var paid = form.find("#invoice-paid").val();

    var paid = $("#invoice-paid-"+invoiceOid).val();
    console.log("paid="+paid);

    var comment = $("#invoice-comment-"+invoiceOid).val();
    console.log("comment="+comment);

    var url = Routing.generate('translationalresearch_invoice_update_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "POST",
        data: {invoiceOid: invoiceOid, paid: paid, comment: comment},
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
