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


function initRankModal() {
    $('.btn-resapp-rank-modal').click(function(ev) {
        var resappId = $(this).data('id');
        rankModalCreation( this, resappId )
    });
}


//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function rankModalCreation( btnEl, resappId ) {

    var url = getCommonBaseUrl("rank/edit/"+resappId);
    //console.log('url='+url);

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
            //$('#resapp_rank_'+resappId).replaceWith( response );
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
            $('#resapp_rank_'+resappId).modal({show:true});
        }
    });

}

function submitRank(btn,resappId) {

    var rankValue = $('#oleg_resappbundle_rank_rank').val();

    var url = getCommonBaseUrl("rank/update-ajax/"+resappId);

    var rank_modal = $('#resapp_rank_'+resappId);

    $.ajax({
        type: 'PUT',
        url: url,
        data: {rankValue: rankValue},
        timeout: _ajaxTimeout,
        success: function(data){
            //console.log("OK submit a new rank");
            if( data == 'ok' ) {
                rank_modal.modal('hide');
                rank_modal.remove();
                //cleanRankModal();
                //if( _reload_page_after_modal == '1' ) {
                    window.parent.location.reload();
                //}
            } else {
                console.log("error: data="+data);
            }
        },
        error: function(){
            console.log("error: data="+data);
        }
    });
}

