/**
 * Created by ch3 on 5/15/2019.
 */


//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function callLogStatusNotificationConfirmAction() {

    $('a[resapp-data-confirm]').click(function(ev) {

        var href = $(this).attr('href');
        //console.log("href="+href);

        if( !$('#resappDataConfirmModal').length ) {
            var modalHtml =
                '<div id="resappDataConfirmModal" class="modal fade resapp-data-confirm-modal">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-header text-center">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                '<h3 id="dataConfirmLabel">Confirmation</h3>' +
                '</div>' +
                '<div class="modal-body text-center">' +
                '</div>' +
                '<div class="modal-footer">' +
                '<a class="btn btn-primary resapp-data-confirm-ok resapp-data-confirm-ok-statusnotify" id="dataConfirmStatusNotify">Change status and notify applicant</a>' +
                '<a class="btn btn-primary resapp-data-confirm-ok resapp-data-confirm-ok-statuswithoutnotify" id="dataConfirmStatusWithoutNotify">Change status without notification</a>' +
                //'<a class="btn btn-primary resapp-data-confirm-ok" id="dataConfirmOK-statusnotify">OK</a>' +
                //'<a class="btn btn-primary resapp-data-confirm-ok" id="dataConfirmOK-statuswithoutnotify">OK</a>' +
                //'<button style="display: none;" class="btn btn-primary data-comment-ok">OK</button>' +
                '<button class="btn btn-default resapp-data-confirm-cancel" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';

            $('body').append(modalHtml);
        }

        var confirmText = "<p>"+$(this).attr('resapp-data-confirm')+"</p>";
        var emailSubject = $(this).attr('resapp-data-email-subject');
        var emailBody = $(this).attr('resapp-data-email-body');
        if( emailSubject ) {
            confirmText = confirmText + "<p>Subject: " + emailSubject + "</p>";
        }
        if( emailBody ) {
            confirmText = confirmText + "<p>Body: " + emailBody + "</p>";
        }

        $('#resappDataConfirmModal').find('.modal-body').html( confirmText );

        //var callbackfn = $(this).attr('resapp-data-callback');
        // if( callbackfn ) {
        //     var onclickStr = callbackfn+'("'+href+'"'+',this'+')';
        //     $('#dataConfirmOK').attr('onclick',onclickStr);
        // } else {
        //     $('#dataConfirmOK').attr('href', href);
        // }


        $('#resappDataConfirmModal').modal({show:true});

        var callbackfn = $(this).attr('resapp-data-callback');
        var href1 = $(this).attr('resapp-data-href1');
        var href2 = $(this).attr('resapp-data-href2');
        //console.log("href1="+href1);
        //console.log("href2="+href2);

        if( callbackfn ) {
            var onclickStr1 = callbackfn+'("'+href1+'"'+',this'+')';
            //console.log("onclickStr1="+onclickStr1);
            $('#dataConfirmStatusNotify').attr('onclick',onclickStr1);

            var onclickStr2 = callbackfn+'("'+href2+'"'+',this'+')';
            //console.log("onclickStr2="+onclickStr2);
            $('#dataConfirmStatusWithoutNotify').attr('onclick',onclickStr2);
        } else {
            $('#dataConfirmStatusNotify').attr('href', href1);
            $('#dataConfirmStatusWithoutNotify').attr('href', href2);
        }

        $('.resapp-data-confirm-ok').on('click', function(event){
            //alert("on modal js: dataConfirmOK clicked");
            var footer = $(this).closest('.modal-footer');
            footer.html('Please wait ...');
        });

        //add listnere to ok button to "Please wait ..." and disable button on click
        // $('.resapp-data-confirm-ok').on('click', function(event){
        //     //alert("on modal js: dataConfirmOK clicked");
        //     var footer = $(this).closest('.modal-footer');
        //     footer.html('Please wait ...');
        // });

        return false;
    }); //resapp-data-confirm click

}


// function refreshpage2(href,btnEl) {
//     console.log('refreshpage2 href='+href);
//
//     if( !href ) {
//         return;
//     }
//
//     //add listnere to ok button to "Please wait ..." and disable button on click
//     var footer = $(btnEl).closest('.modal-footer');
//     footer.html('Please wait ...');
//
//     var url = getCommonBaseUrl(href,"residency-applications");
//     console.log('url='+url);
//
//
//     $.ajax({
//         type: "GET",
//         url: url,
//         async: false,
//         success: function(data) {
//             //console.log('result='+data);
//         }
//     });
//
//     //alert("before reload");
//
//     location.reload();
// }

