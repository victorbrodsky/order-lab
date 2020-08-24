/**
 * Created by ch3 on 5/15/2019.
 */

function fellappStatusNotificationConfirmAction() {
    //fellappStatusNotificationConfirmActionOriginal();
    fellappStatusNotificationConfirmActionDynamic();
}

//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function fellappStatusNotificationConfirmActionOriginal() {

    $('a[fellapp-data-confirm]').click(function(ev) {

        //var href = $(this).attr('href');
        //console.log("href="+href);

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


        $('#fellappDataConfirmModal').modal({show:true});

        var callbackfn = $(this).attr('fellapp-data-callback');
        var href1 = $(this).attr('fellapp-data-href1');
        var href2 = $(this).attr('fellapp-data-href2');
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

        $('.fellapp-data-confirm-ok').on('click', function(event){
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
//dynamically get email subject, body, warning
function fellappStatusNotificationConfirmActionDynamic() {

    $('a[fellapp-data-confirm]').click(function(ev) {

        //var href = $(this).attr('href');
        //console.log("fellapp-data-confirm clicked");

        var modalEl = $(this);

        //var btn = modalEl.closest(".btn-group").find("button").get(0);
        var btn = modalEl.get(0);
        //console.log(btn);
        var lbtn = Ladda.create( btn );
        lbtn.start();

        var fellappId = $(this).attr('fellapp-data-email-fellappid');
        var emailType = $(this).attr('fellapp-data-email-type');
        
        if( !fellappId ) {
            var emailSubject = "Error getting email subject";
            var emailBody = "Error getting email body";
        }
        //var emailBody = $(this).attr('fellapp-data-email-body');

        var url = Routing.generate('fellapp_get_notification_email_infos');

        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: true,
            data: {id: fellappId, emailType: emailType},
            dataType: 'json',
        }).success(function(data) {
            //console.log("data:");
            //console.log(data);
            if( data == "NOTOK" ) {
                fellappStatusNotificationConfirmModal(modalEl,null,emailSubject,emailBody);
            } else {
                //console.log("warning="+data.warning);
                //console.log("subject="+data.subject);
                //console.log("body="+data.body);
                fellappStatusNotificationConfirmModal(modalEl,data.warning,data.subject,data.body);
            }
        }).done(function() {
            //console.log("Finish getting subject and body");
            //calllogStopBtn(lbtn);
            lbtn.stop();
            //$('button').prop('disabled',false);
        }).error(function(jqXHR, textStatus, errorThrown) {
            console.log('Error : ' + errorThrown);
        });

        // }

        return false;
    }); //fellapp-data-confirm click

}
function fellappStatusNotificationConfirmModal(modalEl,emailWarning,emailSubject,emailBody) {

    var confirmText = "<p>"+modalEl.attr('fellapp-data-confirm')+"</p>";
    //console.log("1confirmText="+confirmText);

    if( emailWarning ) {
        confirmText = "<p>"+emailWarning + "</p>" + confirmText;
    }
    if( emailSubject ) {
        confirmText = confirmText + "<p>Subject: " + emailSubject + "</p>";
    }
    if( emailBody ) {
        confirmText = confirmText + "<p>Body: " + emailBody + "</p>";
    }
    //console.log("2confirmText="+confirmText);

    var modalHtml =
        '<div id="fellappDataConfirmModal" class="modal fade fellapp-data-confirm-modal">' +
        '<div class="modal-dialog">' +
        '<div class="modal-content">' +
        '<div class="modal-header text-center">' +
        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
        '<h3 id="dataConfirmLabel">Confirmation</h3>' +
        '</div>' +
        '<div class="modal-body text-center">' + confirmText + '</div>' +
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


    $('#fellappDataConfirmModal').modal({show:true});

    var callbackfn = modalEl.attr('fellapp-data-callback');
    var href1 = modalEl.attr('fellapp-data-href1');
    var href2 = modalEl.attr('fellapp-data-href2');
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

    $('.fellapp-data-confirm-ok').on('click', function(event){
        //alert("on modal js: dataConfirmOK clicked");
        var footer = modalEl.closest('.modal-footer');
        footer.html('Please wait ...');
    });

    $('#fellappDataConfirmModal').on('hidden.bs.modal', function () {
        //console.log("hidden.bs.modal");
        // $( '.modal' ).modal( 'hide' ).data( 'bs.modal', null );
        // $( '.modal' ).remove();
        // $( '.modal-backdrop' ).remove();
        // $( 'body' ).removeClass( "modal-open" );

        $(this).modal( 'hide' ).data( 'bs.modal', null );
        $(this).remove();
        $('.modal-backdrop').remove();
        $('body').removeClass( "modal-open" );
    });

    return false;
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
//     var url = getCommonBaseUrl(href,"fellowship-applications");
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

