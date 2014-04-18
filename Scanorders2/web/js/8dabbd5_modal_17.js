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

    //confirm
    confirmAction();

});

function submitNewComment(id) {

    var urlBase = $("#baseurl").val();
    var urlCommentSubmit = "http://"+urlBase+"/scan-order/progress-and-comments/create";

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
        timeout: _ajaxTimeout,
        success: function (data) {
            //console.log("OK submit a new comment");
            comment_modal.modal('hide');
            cleanModal();
            if( _reload_page_after_modal == '1' ) {
                window.parent.location.reload();
            }
        },
        error: function ( x, t, m ) {

            if( t === "timeout" ) {
                getAjaxTimeoutMsg();
            }

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


//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function confirmAction() {

    $('a[data-confirm]').click(function(ev) {

        var href = $(this).attr('href');
        if( !$('#dataConfirmModal').length ) {
            var modalHtml =
                '<div id="dataConfirmModal" class="modal fade data-confirm-modal">' +
                    '<div class="modal-dialog">' +
                        '<div class="modal-content">' +
                            '<div class="modal-header text-center">' +
                                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                                '<h3 id="dataConfirmLabel">Confirmation</h3>' +
                            '</div>' +
                            '<div class="modal-body text-center"></div>' +
                            '<div class="modal-footer">' +
                                '<button class="btn btn-primary data-confirm-cancel" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
                                '<a class="btn btn-primary data-confirm-ok" id="dataConfirmOK">OK</a>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            $('body').append(modalHtml);
        }

        $('#dataConfirmModal').find('.modal-body').text( $(this).attr('data-confirm') );
        $('#dataConfirmOK').attr('href', href);

        ////////// assign correct confirmation text and button's text //////////
        var okText = $(this).attr('data-ok');
        var cancelText = $(this).attr('data-cancel');
        if( typeof okText === 'undefined' ){
            okText = 'OK';
        }
        if( typeof cancelText === 'undefined' ){
            cancelText = 'Cancel';
        }
        $('#dataConfirmModal').find('.data-confirm-cancel').text( cancelText );
        $('#dataConfirmModal').find('.data-confirm-ok').text( okText );
        ////////// EOF of assigning text //////////

        $('#dataConfirmModal').modal({show:true});

        return false;
    });

    //TODO: to hide modal, make button onclick function firts close modal, then redirect to href
    $('.data-confirm-ok').click(function() {
        console.log('ok clicked!');
        //$('.data-confirm-cancel').trigger('click');
        //$('.data-confirm-modal').modal({show:false});
        $('.data-confirm-modal').modal('hide');
    });

}

function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

