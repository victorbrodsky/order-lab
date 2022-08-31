/**
 * Created by ch3 on 10/1/2021.
 */

var _clickedSubmitBtnId = null;

function trpConstructClosureProjectModal(actionBtn,asyncType,afterFunctionReload) { //newUserFormHtml,fieldId,sitename,otherUserParam,appendHolder) {
    //fieldId = "'"+fieldId+"'";
    //sitename = "'"+sitename+"'";
    //otherUserParam = "'"+otherUserParam+"'";

    if( afterFunctionReload ) {
        afterFunctionReload = "'"+afterFunctionReload+"'";
    }

    //remove any existing modal
    $( '.modal' ).modal( 'hide' ).data( 'bs.modal', null );
    $( '.modal' ).remove();
    $( '.modal-backdrop' ).remove();
    $( 'body' ).removeClass( "modal-open" );
    $('#trp-project-status-modal').remove();

    //var href = $(actionBtn).attr('href');
    //var callbackfn = $(this).attr('general-data-callback');
    var projectId = $(actionBtn).attr('trp-closure-data-confirm');
    var title = $(actionBtn).attr('trp-closure-title-data');
    var reasonTitle = $(actionBtn).attr('trp-closure-reason-title-data');
    var note = $(actionBtn).attr('trp-closure-note-data');
    var routename = $(actionBtn).attr('trp-closure-routename-data');
    routename = "'"+routename+"'";
    console.log('constructClosureProjectModal: projectId='+projectId+", title="+title);

    var noteHtml = "";
    if( note ) {
        noteHtml = '<p>'+note+'</p>';
    }

    var modalHtml =
        '<div id="trp-project-status-modal" class="modal fade">' + //id="user-add-new-user"
        '<div class="modal-dialog">' +
        '<div class="modal-content">' +
        '<div class="modal-header text-center">' +
        '<button id="user-add-btn-dismiss" type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
        //'<h3 id="dataConfirmLabel">Add name and contact info for an unlisted person</h3>' +
        '<h3 id="dataConfirmLabel">'+title+'</h3>' +
        noteHtml +
        '</div>' +
        '<div class="modal-body text-center">' +
        //newUserFormHtml +

        '<div class="row">' +
        '<div class="col-xs-6" align="right">' +
        '<label for="trp-project-reason">'+reasonTitle+'</label>' +
        '</div>' +
        '<div class="col-xs-6" align="left">' +
        '<input type="text" id="trp-project-reason" name="trp-project-reason" class="form-control">' +
        '</div>' +
        '</div>' +

        '<div id="add-user-danger-box" class="alert alert-danger" style="display: none; margin: 10px;"></div>' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button id="user-add-btn-cancel" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
        '<a class="btn btn-primary trp-project-status-ok-btn" id="trp-project-status-ok-btn" ' +
        'onclick="trpCloseReactivationProjectAction(this,'+projectId+','+routename+','+asyncType+','+afterFunctionReload+')">OK</a>' +
        //'<a class="btn btn-primary add-user-btn-add" id="add-user-btn-add" onclick="addNewUserAction(this,'+fieldId+','+sitename+','+otherUserParam+')">Add</a>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

    var appendHolder = 'body';
    $(appendHolder).append(modalHtml);

    $('#trp-project-status-modal').modal({show:true});
}
function trpCloseReactivationProjectAction( okbtn, projectId, routename, asyncType, afterFunctionName ) {

    var holder = $(okbtn).closest(".modal");

    holder.find('#add-user-danger-box').hide();
    holder.find('#add-user-danger-box').html(null);

    var btn = document.getElementById("trp-project-status-ok-btn");
    //var btn = btnEl.get(0);
    var lbtn = Ladda.create( btn );
    lbtn.start();
    //holder.find("#user-add-btn-dismiss").hide();
    //holder.find("#user-add-btn-cancel").hide();

    // var callBackFunction = null;
    // if( afterFunctionName ) {
    //     console.log("create callBackFunction="+afterFunctionName);
    //     callBackFunction = window[afterFunctionName];
    // }

    var transTime = 500;

    //console.log("add New UserAction: Add New User Ajax");

    var reason = holder.find("#trp-project-reason").val();
    console.log("reason="+reason);

    var errorMsg = null;

    if( !reason ) {
        errorMsg = "Please enter a reason for project closure";
    }

    if( errorMsg ) {
        holder.find('#add-user-danger-box').html(errorMsg);   //"Please enter a new user email address");
        holder.find('#add-user-danger-box').show(transTime);

        lbtn.stop();
        holder.find("#user-add-btn-dismiss").show();
        holder.find("#user-add-btn-cancel").show();

        return false;
    }

    //console.log("add New UserAction: call ajax to check if user exists");

    //2) try to create a new user
    //var url = Routing.generate(sitename+'_add_new_user_ajax');
    var url = Routing.generate('translationalresearch_close_reactivation_project_ajax');
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        type: "POST",
        data: {
            projectId: projectId,
            routename: routename,
            reason: reason,
        },
        dataType: 'json',
        async: asyncType
    }).done(function(response) {
        //console.log(response);

        if( response.flag == "NOTOK" ) {
            //console.log('NOTOK');
            lbtn.stop();
            holder.find("#user-add-btn-dismiss").show();
            holder.find("#user-add-btn-cancel").show();

            holder.find('#add-user-danger-box').html(response.error);
            holder.find('#add-user-danger-box').show(transTime);
        } else {
            //console.log('OK');
            //updateUserComboboxes(response,fieldId);
            //$("#user-add-btn-dismiss").click();
            //document.getElementById("user-add-btn-dismiss").click();
            holder.find("#user-add-btn-dismiss").click();
        }

    }).always(function() {

        console.log("create callBackFunction="+afterFunctionName);
        if( afterFunctionName ) {
            //console.log("callBackFunction");
            window[afterFunctionName]();
        }

        //lbtn.stop();
        //holder.find("#user-add-btn-dismiss").show();
        //holder.find("#user-add-btn-cancel").show();
    }).error(function(jqXHR, textStatus, errorThrown) {
        //console.log('jqXHR:');
        //console.log(jqXHR);
        console.log('Error: ' + errorThrown);
        //console.log('errorThrown: ' + errorThrown);
        lbtn.stop();
        holder.find("#user-add-btn-dismiss").show();
        holder.find("#user-add-btn-cancel").show();

        holder.find('#add-user-danger-box').html('Error : ' + errorThrown);
        holder.find('#add-user-danger-box').show(transTime);
    });

}

function afterFunctionReload() {
    location.reload();
}

function afterFunctionEditPage() {
    //Resubmit form by clicking the same submit button.
    // $(":submit").show();
    // $('#please-wait').hide();
    transresShowBtn();
    //transresValidateProjectForm();
    $("#"+_clickedSubmitBtnId).click();
}

