/*
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by oli2002 on 11/11/15.
 */

//console.log('include interview-modal.js');

function initInterviewModal() {
    $('.btn-interview-info-modal').click(function(ev) {
        var resappId = $(this).data('id');
        interviewModalCreation( this, resappId )
    });
}


//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function interviewModalCreation( btnEl, resappId ) {

    //console.log('interviewModalAction resappId='+resappId);

    var url = getCommonBaseUrl("interview-modal/"+resappId);
    //console.log('url='+url);

//    $( "#interview-info-modal" ).load( url, function() {
//        console.log( "Load was performed. resappId="+resappId );
//        $("#interview-info-modal").find('#interview-info-'+resappId).modal({show:true});
//    });
//    return;
    var waitModal = true;
    if( waitModal ) {
        var waitHtml =
            '<div class="modal fade" id="wait-modal" tabindex="-1" role="dialog" aria-labelledby="myWaitModalLabel">'+
            '<div class="modal-dialog">'+
            '<div class="modal-content text-center col-xs-12">'+
            '<br><br><br><h4>Please wait ...</h4>' +
            '<br><br><br>'+
            '</div>'+
            '</div>'+
            '</div>';
        $('#wait-modal').remove();
        $('body').append(waitHtml);
        $('#wait-modal').modal({show:true});
    } else {
        var lbtn = Ladda.create( btnEl );
        lbtn.start();
    }


    //    $.ajax({
//        type: 'GET',
//        url: url,
//        success: function(response){
//            console.log('response ok');
//            $('body').append(response);
//            $('#interview-info-'+resappId).modal({show:true});
//        }
//    });
//    return;

    var success = false; //open modal only if success=true
    $.ajax({
        type: 'GET',
        url: url,
        //dataType:'json',//type of data you are returning from server
        //data: data, //better to pass it with data
        success: function(response){

            //remove wait modal
            if( waitModal ) {
                var $modal2 = $("#wait-modal").detach().modal();
                $modal2.modal("hide");
            } else {
                lbtn.stop();
            }

            $('body').append(response);
            //$('#interview-info-'+resappId).replaceWith( response );
            success = true;
        },
        error: function(){
            //handle error
            if( waitModal ) {
                $('#wait-modal').find('h4').html('Failed to load applicant information');
            } else {
                lbtn.stop();
            }
        }
    })
    .then( function() {
        if(success)
        {
            $('[data-toggle="tooltip"]').tooltip({html: true});
            $('#interview-info-'+resappId).modal({show:true});
        }
    });

}

function sendInviteInterviewersToRate(url,confirmMsg) {
    //console.log("inviteinterviewerstorate: url="+url);
    var r = confirm(confirmMsg);
    if( r == false ) {
        return;
    }
    $.ajax({
        type: 'GET',
        url: url,
        success: function(response){
            //console.log('response ok');
            if( response == "ok" ) {
                alert("Invitation email(s) have been successfully sent.");
                //$(".alert-info").text("Invitation email(s) have been successfully sent.");
            }
        }
    });
}

