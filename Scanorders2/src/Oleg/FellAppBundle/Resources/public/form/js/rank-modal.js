/**
 * Created by oli2002 on 11/11/15.
 */

//console.log('include interview-modal.js');

function initRankModal() {
    $('.btn-fellapp-rank-modal').click(function(ev) {
        var fellappId = $(this).data('id');
        rankModalCreation( this, fellappId )
    });
}


//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function rankModalCreation( btnEl, fellappId ) {

    //console.log('interviewModalAction fellappId='+fellappId);

    var url = getCommonBaseUrl("rank/edit/"+fellappId);
    //console.log('url='+url);

//    return;
    var waitModal = true;
    if( waitModal ) {
        var waitHtml =
            '<div class="modal fade" id="wait-rank-modal" tabindex="-1" role="dialog" aria-labelledby="myWaitModalLabel">'+
            '<div class="modal-dialog">'+
            '<div class="modal-content text-center col-xs-12">'+
            '<br><br><br><h4>Please wait ...</h4>' +
            '<br><br><br>'+
            '</div>'+
            '</div>'+
            '</div>';
        $('#wait-rank-modal').remove();
        $('body').append(waitHtml);
        $('#wait-rank-modal').modal({show:true});
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
//            $('#fellapp_rank_'+fellappId).modal({show:true});
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
                var $modal2 = $("#wait-rank-modal").detach().modal();
                $modal2.modal("hide");
            } else {
                lbtn.stop();
            }

            $('body').append(response);
            //$('#fellapp_rank_'+fellappId).replaceWith( response );
            success = true;
        },
        error: function(){
            //handle error
            if( waitModal ) {
                $('#wait-rank-modal').find('h4').html('Failed to load applicant information');
            } else {
                lbtn.stop();
            }
        }
    })
    .then( function() {
        if(success)
        {
            $('[data-toggle="tooltip"]').tooltip({html: true});
            $('#fellapp_rank_'+fellappId).modal({show:true});
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
            }
        }
    });
}

