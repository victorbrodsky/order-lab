function saveNotesAndComments(btn, fellappId) {
    console.log('saveNotesAndComments clicked');

    // If fellappId is not passed explicitly (e.g. from modal), try to read it from the form
    if (!fellappId) {
        fellappId = $('#fellapp_id').val();
    }

    var $btn;
    var notesVal;

    // Prefer context from passed-in button (used by applicantInterviewsInfo macro)
    if (btn) {
        $btn = $(btn);
        var $panel = $btn.closest('.fellapp-notes-panel');
        if ($panel.length) {
            notesVal = $panel.find('.fellapp-notes-textarea').first().val();
        }
    }

    // Fallback to legacy selectors used on the main Notes tab
    if (!notesVal) {
        $btn = $('#saveNotesAndComments-button');
        var notesPanel = $('#notes');
        notesVal = notesPanel.find('textarea').first().val();
    }

    if (!fellappId) {
        alert('Missing application ID.');
        return;
    }

    if (!$btn || !$btn.length) {
        alert('Save button not found.');
        return;
    }

    $btn.prop('disabled', true).text('Saving...');

    var url = Routing.generate('fellapp-set-notes');

    $.ajax({
        type: 'POST',
        url: url,
        timeout: _ajaxTimeout,
        data: { id: fellappId, notes: notesVal }
    }).done(function (data) {
        // expect JSON {status: 'ok'}
        if (data && data.status === 'ok') {
            $btn.text('Saved');
            setTimeout(function(){
                $btn.text('Save notes and comments');
            }, 1500);
        } else {
            alert('Failed to save notes.');
            $btn.text('Save notes and comments');
        }
    }).fail(function (xhr) {
        var msg = 'Save failed';
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
            msg += ': ' + xhr.responseJSON.error;
        }
        alert(msg);
        $btn.text('Save notes and comments');
    }).always(function(){
        $btn.prop('disabled', false);
    });
}
