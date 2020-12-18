/**
 * Created by ch3 on 5/15/2019.
 */


function resappSendRejectionEmails() {
    console.log("resappSendRejectionEmails");

    var year = $('#filter_startDates').val();
    //var confirmText = "Would you like to send the following rejection notification e-mail to the following other "+year+" applicants?";
    var confirmText = "Would you like to send the following rejection notification e-mail to the selected "+year+" applicants?";
    //TODO: add list of checked applicants
    if( confirm(confirmText) ) {
        //txt = "You pressed OK!";
    } else {
        //txt = "You pressed Cancel!";
        return false;
    }

    var btnEl = $("#send-rejection-emails").get(0);
    var lbtn = Ladda.create( btnEl );
    btnEl.disabled = true;
    lbtn.start();

    var checkboxes = document.getElementsByName('notificationemail');
    //var checkboxes = document.querySelector('.notificationemail').checked;
    //var ids = "";
    var ids = [];
    for (var i=0, n=checkboxes.length;i<n;i++)
    {
        if (checkboxes[i].checked)
        {
            //ids += ","+checkboxes[i].value;
            ids.push(checkboxes[i].value);
        }
    }
    //console.log("ids:");
    //console.log(ids);
    //alert(ids);

    //if (ids) ids = ids.substring(1);

    //console.log("ids:");
    //console.log(ids);
    //alert(ids);

    var url = Routing.generate('resapp_send_rejection_emails_action');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "POST",
        async: true,
        //data: {ids: ids, idsArr:idsArr},
        data: {ids:ids},
    }).success(function(data) {
        //lbtn.stop();
        //btnEl.disabled = false;
        console.log("output="+data);
        if( data != "ERROR" ) {
            console.log("send rejection emails");
            window.location = data;
        } else {
            console.log("Error sending rejection emails");
            lbtn.stop();
            btnEl.disabled = false;
        }
    }).error(function(jqXHR, textStatus, errorThrown) {
        lbtn.stop();
        btnEl.disabled = false;
        console.log('Error : ' + errorThrown);
    }).done(function() {
        //lbtn.stop();
        //btnEl.disabled = false;
        //console.log("send rejection emails");
    });
}

