/**
 * Created by ch3 on 5/15/2019.
 */


function fellappSendRejectionEmails() {
    console.log("fellappSendRejectionEmails");

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

    var url = Routing.generate('fellapp_send_rejection_emails_action');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "POST",
        async: true,
        //data: {ids: ids, idsArr:idsArr},
        data: {ids:ids},
    }).success(function(data) {
        console.log("output="+data);
        if( data != "ERROR" ) {
            console.log("send rejection emails");
            window.location = data;
        } else {
            console.log("Error sending rejection emails");
        }
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    }).done(function() {
        //console.log("send rejection emails");
    });
}

