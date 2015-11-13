/**
 * Created by oli2002 on 11/11/15.
 */


//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function interviewModalAction( fellappId ) {

    $('a[fellapp-interview-modal]').click(function(ev) {

        console.log('interviewModalAction fellappId='+fellappId);

        ev.preventDefault();

        var href = $(this).attr('href');

        if( !$('#generalDataConfirmModal').length ) {
            var modalHtml =
                '<div id="generalDataConfirmModal" class="modal fade general-data-confirm-modal">' +
                    '<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header text-center">' +
                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                    '<h3 id="dataConfirmLabel">Confirmation</h3>' +
                    '</div>' +
                    '<div class="modal-body text-center">' +
                    '</div>' +
                    '<div class="modal-footer">' +
                    '<button class="btn btn-primary general-data-confirm-cancel" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
                    '<a class="btn btn-primary general-data-confirm-ok" id="dataConfirmOK">OK</a>' +
                    '<button style="display: none;" class="btn btn-primary data-comment-ok">OK</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

            $('body').append(modalHtml);
        }


        $('#generalDataConfirmModal').find('.modal-body').text( $(this).attr('general-data-confirm') );

        var callbackfn = $(this).attr('general-data-callback');

        if( callbackfn ) {
            var onclickStr = callbackfn+'("'+href+'"'+',this'+')';
            $('#dataConfirmOK').attr('onclick',onclickStr);
        } else {
            $('#dataConfirmOK').attr('href', href);
        }

//        /////////////// add comment /////////////////////
//        if( $(this).hasClass("status-with-comment") ) {
//            //do it by listening .general-data-confirm-ok
//            //console.log('add comment!');
//            var commentHtml = '<br><br>Please provide a comment:' +
//                '<p><textarea id="'+$(this).attr('id')+'" name="addcomment" type="textarea" class="textarea form-control addcomment_text" maxlength="5000" required></textarea></p>';
//            $('#generalDataConfirmModal').find('.modal-body').append(commentHtml);
//            //replace href link <a> with button
//            $('.general-data-confirm-ok').hide();
//            $('.data-comment-ok').show();
//        } else {
//            //$('#dataConfirmOK').attr('href', href); //do it automatically
//        }
//        /////////////// EOF add comment /////////////////////

        ////////// assign correct confirmation text and button's text //////////
        var okText = $(this).attr('data-ok');
        var cancelText = $(this).attr('data-cancel');
        if( typeof okText === 'undefined' ){
            okText = 'OK';
        }
        if( typeof cancelText === 'undefined' ){
            cancelText = 'Cancel';
        }
        $('#generalDataConfirmModal').find('.general-data-confirm-cancel').text( cancelText );

        //console.log('okText='+okText);
        if( okText != 'hideOkButton' ) {
            $('#generalDataConfirmModal').find('.general-data-confirm-ok').text( okText );
            $('.general-data-confirm-ok').show();
        } else {
            $('.general-data-confirm-ok').hide();
        }
        ////////// EOF of assigning text //////////

        $('#generalDataConfirmModal').modal({show:true});

        //add listnere to ok button to "Please wait ..." and disable button on click
        $('.general-data-confirm-ok').on('click', function(event){
            //alert("on modal js: dataConfirmOK clicked");
            var footer = $(this).closest('.modal-footer');
            footer.html('Please wait ...');
        });

        return false;
    }); //general-data-confirm click

}

