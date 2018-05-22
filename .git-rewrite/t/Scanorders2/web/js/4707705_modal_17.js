/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/5/14
 * Time: 12:29 PM
 * To change this template use File | Settings | File Templates.
 */


var _reload_page_after_modal = 0;

$(document).ready(function() {

   $('.comment_modal_close').on( "click", function(){
       cleanModal();
   });

    _reload_page_after_modal = $('#reload_page_after_modal').val();

    //admin: copy combobox to textfield
    $('.modal').find('.combobox').on("change", function(e) {

        var data = $(this).select2('data');
        //console.log('data='+data);
        if( data ) {
            var text = $(this).closest(".modal-body").find('textarea').val();
            //console.log('text='+text);
            if( text != '' ) {
                text = text + '\n';
            }
            text = text + data.text+'.';
            $(this).closest(".modal-body").find('textarea').val(text);
        } else {
            //var text = '';
        }

    });


//    $('.comments-nav').on( "click", function(){
//        $('.order-status-filter').select2('val','With Comments');
//        $('.order-filter-btn').trigger("click");
//    });

});

function submitNewComment(id) {

    var urlBase = $("#baseurl").val();
    var urlCommentSubmit = "http://"+urlBase+"/history/order/create/";

    var text = $('#addComment_'+id).find('.textarea').val();

    if( $('#modal-processor-comment').select2('data') ) {
        var selectednote = $('#modal-processor-comment').select2('data').text;
    } else {
        var selectednote = "";
    }

    //console.log("urlCommentSubmit="+urlCommentSubmit+", text="+text + ", selectednote="+selectednote);

    var comment_modal = $('#addComment_'+id);

    $.ajax({
        url: urlCommentSubmit,
        type: 'POST',
        data: {id: id, selectednote: selectednote, text: text},
        success: function (data) {
            //console.log("OK submit a new comment");
            comment_modal.modal('hide');
            cleanModal();
            if( _reload_page_after_modal == '1' ) {
                window.parent.location.reload();
            }
        },
        error: function () {
            //console.log("Error submit a new comment");
            var errormsg = '<div class="alert alert-danger">Error submitting a new comment</div>';
            $('#modal_error_'+id).html(errormsg);
            return false;
            //comment_modal.modal('hide');
        }
    });

}

function cleanModal() {
    $(".modal_error_div").html('');
    $(".modal-body").find('textarea').val('');
    $('#modal-processor-comment').select2('data',null);
    //console.log("close: clean modal");
    //$(this).closest('.modal').find('.modal_error_div').html('');
}