/**
 * Created by ch3 on 5/15/2019.
 */


//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function callLogStatusNotificationConfirmAction() {

    $('a[fellapp-data-confirm]').click(function(ev) {

        var href = $(this).attr('href');
        console.log("href="+href);

        if( !$('#fellappDataConfirmModal').length ) {
            var modalHtml =
                '<div id="fellappDataConfirmModal" class="modal fade fellapp-data-confirm-modal">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-header text-center">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                '<h3 id="dataConfirmLabel">Confirmation</h3>' +
                '</div>' +
                '<div class="modal-body text-center">' +
                '</div>' +
                '<div class="modal-footer">' +
                '<a class="btn btn-primary fellapp-data-confirm-ok fellapp-data-confirm-ok-statusnotify" id="dataConfirmStatusNotify">Change status and notify applicant</a>' +
                '<a class="btn btn-primary fellapp-data-confirm-ok fellapp-data-confirm-ok-statuswithoutnotify" id="dataConfirmStatusWithoutNotify">Change status without notification</a>' +
                //'<a class="btn btn-primary fellapp-data-confirm-ok" id="dataConfirmOK-statusnotify">OK</a>' +
                //'<a class="btn btn-primary fellapp-data-confirm-ok" id="dataConfirmOK-statuswithoutnotify">OK</a>' +
                //'<button style="display: none;" class="btn btn-primary data-comment-ok">OK</button>' +
                '<button class="btn btn-default fellapp-data-confirm-cancel" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';

            $('body').append(modalHtml);
        }

        var confirmText = "<p>"+$(this).attr('fellapp-data-confirm')+"</p>";
        var emailSubject = $(this).attr('fellapp-data-email-subject');
        var emailBody = $(this).attr('fellapp-data-email-body');
        if( emailSubject ) {
            confirmText = confirmText + "<p>Subject: " + emailSubject + "</p>";
        }
        if( emailBody ) {
            confirmText = confirmText + "<p>Body: " + emailBody + "</p>";
        }

        $('#fellappDataConfirmModal').find('.modal-body').html( confirmText );

        //var callbackfn = $(this).attr('fellapp-data-callback');
        // if( callbackfn ) {
        //     var onclickStr = callbackfn+'("'+href+'"'+',this'+')';
        //     $('#dataConfirmOK').attr('onclick',onclickStr);
        // } else {
        //     $('#dataConfirmOK').attr('href', href);
        // }

        ////////// assign correct confirmation text and button's text //////////
        // var okText = $(this).attr('data-ok');
        // var cancelText = $(this).attr('data-cancel');
        // if( typeof okText === 'undefined' ){
        //     okText = 'OK';
        // }
        // if( typeof cancelText === 'undefined' ){
        //     cancelText = 'Cancel';
        // }
        // $('#fellappDataConfirmModal').find('.fellapp-data-confirm-cancel').text( cancelText );
        //
        // //console.log('okText='+okText);
        // if( okText != 'hideOkButton' ) {
        //     $('#fellappDataConfirmModal').find('.fellapp-data-confirm-ok').text( okText );
        //     $('.fellapp-data-confirm-ok').show();
        // } else {
        //     $('.fellapp-data-confirm-ok').hide();
        // }
        ////////// EOF of assigning text //////////

        $('#fellappDataConfirmModal').modal({show:true});

        var href1 = $(this).attr('fellapp-data-href1');
        var href2 = $(this).attr('fellapp-data-href2');
        console.log("href1="+href1);
        console.log("href2="+href2);

        $('.fellapp-data-confirm-ok').on('click', function(event){

            $('#dataConfirmStatusNotify').attr('href', href1);
            $('#dataConfirmStatusWithoutNotify').attr('href', href2);

            //alert("on modal js: dataConfirmOK clicked");
            var footer = $(this).closest('.modal-footer');
            footer.html('Please wait ...');
        });

        //add listnere to ok button to "Please wait ..." and disable button on click
        // $('.fellapp-data-confirm-ok').on('click', function(event){
        //     //alert("on modal js: dataConfirmOK clicked");
        //     var footer = $(this).closest('.modal-footer');
        //     footer.html('Please wait ...');
        // });

        return false;
    }); //fellapp-data-confirm click

}
