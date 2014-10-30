/**
 * Created by oli2002 on 10/29/14.
 */


function initTypeaheadUserSiteSerach() {

    if( $('#multiple-datasets-typeahead-search').length == 0 ) {
        return;
    }

    //console.log('typeahead search');

    var userDB = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: getCommonBaseUrl("util/common/user-data-search/user/min","employees"),
        remote: getCommonBaseUrl("util/common/user-data-search/user/%QUERY","employees"),
        dupDetector: duplicationDetector
    });

    var cwidDB = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: getCommonBaseUrl("util/common/user-data-search/cwid/min","employees"),
        remote: getCommonBaseUrl("util/common/user-data-search/cwid/%QUERY","employees"),
        dupDetector: duplicationDetector
    });

    var admintitleDB = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        //prefetch: getCommonBaseUrl("util/common/user-data-search/admintitle/min","employees"),
        remote: getCommonBaseUrl("util/common/user-data-search/admintitle/%QUERY","employees"),
        dupDetector: duplicationDetector
    });

    userDB.initialize();
    cwidDB.initialize();
    admintitleDB.initialize();

    var myTypeahead = $('#multiple-datasets-typeahead-search .typeahead').typeahead({
            highlight: true
        },
        {
            name: 'user',
            displayKey: 'text',
            source: userDB.ttAdapter(),
            templates: {
                header: '<h3 class="search-name">Preferred Display Name</h3>'
            }
        },
        {
            name: 'cwid',
            displayKey: 'text',
            source: cwidDB.ttAdapter(),
            templates: {
                header: '<h3 class="search-name">CWID</h3>'
            }
        },
        {
            name: 'admintitle',
            displayKey: 'text',
            source: admintitleDB.ttAdapter(),
            templates: {
                header: '<h3 class="search-name">Administrative Title</h3>'
            }
        }
    );


    // Attach initialized event to it
    myTypeahead.on('typeahead:selected',function(event, suggestion, dataset){
        //console.log('on select');
        //console.log(suggestion);
        //$('#user-typeahead-id').val(suggestion.id);

        var input = $("<input>").attr("type", "hidden").attr("name", "userid").val(suggestion.id);
        $('#user-typeahead-serach-form').append($(input));

        //submit form
        if( suggestion.id != "" ) {
            //console.log('enter pressed => submit form');
            $('#user-typeahead-serach-form').submit();
        }
    });


    $('#user-typeahead-serach-form input').keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            if( $(this).val() != "" ) {
                //console.log('enter pressed => submit form');
                $('#user-typeahead-serach-form').submit();
            }
        }
    });

}

function duplicationDetector(remoteMatch, localMatch) {
    //console.log('dup check');
    return remoteMatch.value === localMatch.value;
}

