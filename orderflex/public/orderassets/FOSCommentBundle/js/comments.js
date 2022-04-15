// $(document).ready(function() {
//
//     fosNewCommentListener();
//
// });

function foscommentNewComment() {
    var url = Routing.generate('user_thread_new_comment_ajax');

    var threadId = $('#fos_comment_thread').data('thread');
    console.log('threadId='+threadId);

    var comment = $('#foscomment_new_comment').val();
    console.log('comment='+comment);

    var parentId = null; //$('#foscomment_thread_parent').val();
    console.log('parentId='+parentId);
    
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "PUT",
        data: {threadId: threadId, parentId: parentId, comment: comment },
        dataType: 'json',
        async: asyncflag
    }).success(function(response) {
        //console.log(response);


    }).done(function() {
        //
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    });
}
