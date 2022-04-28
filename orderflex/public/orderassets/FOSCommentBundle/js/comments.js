// $(document).ready(function() {
//
//     fosNewCommentListener();
//
// });

function foscommentNewComment() {

    var comment = $('#foscomment_new_comment').val();
    console.log('comment='+comment);

    comment = comment.trim();

    if( !comment ) {
        return;
    }

    var submitBtn = document.getElementById('fos_comment_submit_btn_oleg');
    var submitLaddaBtn = Ladda.create(submitBtn);
    submitLaddaBtn.start();
    submitBtn.disabled = true;

    var url = Routing.generate('user_thread_new_comment_ajax');

    var threadId = $('#fos_comment_thread').data('thread');
    console.log('threadId='+threadId);

    var parentId = null; //$('#foscomment_thread_parent').val();
    console.log('parentId='+parentId);
    
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "POST",
        data: {threadId: threadId, parentId: parentId, comment: comment },
        dataType: 'json',
        async: false //asyncflag
    }).success(function(response) {
        //console.log(response);
        if( response.error == false ) {
            $('#foscomment_new_comment').val('');
            $('#foscomment-comments').prepend(response.commentHtml);

            var noCommentMsg = $('#no-comment-msg');
            if( noCommentMsg ) {
                noCommentMsg.remove();
            }
        }
    }).done(function() {
        submitLaddaBtn.stop();
        submitBtn.disabled = false;
    }).error(function(jqXHR, textStatus, errorThrown) { //fail
        //submitLaddaBtn.stop();
        //submitBtn.disabled = false;
        console.log('Error=' + errorThrown + ", textStatus=" + textStatus);
    });

}

// function resetCursor(txtElement) {
//     console.log('resetCursor');
//     if (txtElement.setSelectionRange) {
//         console.log('resetCursor 1');
//         txtElement.focus();
//         txtElement.setSelectionRange(0, 0);
//     } else if (txtElement.createTextRange) {
//         console.log('resetCursor 2');
//         var range = txtElement.createTextRange();
//         range.moveStart('character', 0);
//         range.select();
//     }
// }

