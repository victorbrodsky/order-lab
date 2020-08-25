/**
 * Created by ch3 on 5/15/2019.
 */

function resappStatusNotificationConfirmAction() {
    //resappStatusNotificationConfirmActionOriginal();
    resappStatusNotificationConfirmActionDynamic();
}


//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function resappStatusNotificationConfirmActionOriginal() {

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
//dynamically get email subject, body, warning
function resappStatusNotificationConfirmActionDynamic() {

    $('a[resapp-data-confirm]').click(function(ev) {

        //var href = $(this).attr('href');
        //console.log("resapp-data-confirm clicked");

        var modalEl = $(this);

        //var btn = modalEl.closest(".btn-group").find("button").get(0);
        var btn = modalEl.get(0);
        //console.log(btn);
        var lbtn = Ladda.create( btn );
        lbtn.start();

        var resappId = $(this).attr('resapp-data-email-resappid');
        var emailType = $(this).attr('resapp-data-email-type');

        if( !resappId ) {
            var emailSubject = "Error getting email subject";
            var emailBody = "Error getting email body";
        }
        //var emailBody = $(this).attr('resapp-data-email-body');

        var url = Routing.generate('resapp_get_notification_email_infos');

        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: true,
            data: {id: resappId, emailType: emailType},
            dataType: 'json',
        }).success(function(data) {
            //console.log("data:");
            //console.log(data);
            if( data == "NOTOK" ) {
                resappStatusNotificationConfirmModal(modalEl,null,emailSubject,emailBody);
            } else {
                //console.log("warning="+data.warning);
                //console.log("subject="+data.subject);
                //console.log("body="+data.body);
                resappStatusNotificationConfirmModal(modalEl,data.warning,data.subject,data.body);
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
    }); //resapp-data-confirm click

}
function resappStatusNotificationConfirmModal(modalEl,emailWarning,emailSubject,emailBody) {

    var confirmText = "<p>"+modalEl.attr('resapp-data-confirm')+"</p>";
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
        '<div id="resappDataConfirmModal" class="modal fade resapp-data-confirm-modal">' +
        '<div class="modal-dialog">' +
        '<div class="modal-content">' +
        '<div class="modal-header text-center">' +
        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
        '<h3 id="dataConfirmLabel">Confirmation</h3>' +
        '</div>' +
        '<div class="modal-body text-center">' + confirmText + '</div>' +
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


    $('#resappDataConfirmModal').modal({show:true});

    var callbackfn = modalEl.attr('resapp-data-callback');
    var href1 = modalEl.attr('resapp-data-href1');
    var href2 = modalEl.attr('resapp-data-href2');
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
        var footer = modalEl.closest('.modal-footer');
        footer.html('Please wait ...');
    });

    $('#resappDataConfirmModal').on('hidden.bs.modal', function () {
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

function clearAllCheckboxBtn() {
    //console.log('Clear all checkboxes');
    var checkboxes = document.getElementsByClassName('filter-status-checkbox');
    for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = false;
    }

}
function checkAllCheckboxBtn() {
    //console.log('Check all checkboxes');
    var checkboxes = document.getElementsByClassName('filter-status-checkbox');
    for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = true;
    }
}
