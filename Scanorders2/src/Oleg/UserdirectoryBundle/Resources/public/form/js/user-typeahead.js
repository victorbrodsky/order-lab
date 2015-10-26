/**
 * Created by oli2002 on 10/29/14.
 */


function initTypeaheadUserSiteSearch() {

    if( $('.multiple-datasets-typeahead-search').length == 0 ) {
        return;
    }

    //console.log('typeahead search');

    var suggestions_limit = 5;

    var userDB = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: getCommonBaseUrl("util/common/user-data-search/user/"+suggestions_limit+"/min","employees"),
        remote: getCommonBaseUrl("util/common/user-data-search/user/"+suggestions_limit+"/%QUERY","employees"),
        dupDetector: duplicationDetector,
        limit: suggestions_limit
    });

    var institutionDB = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        //prefetch: getCommonBaseUrl("util/common/user-data-search/service/min","employees"),
        remote: getCommonBaseUrl("util/common/user-data-search/institution/"+suggestions_limit+"/%QUERY","employees"),
        dupDetector: duplicationDetector,
        limit: suggestions_limit
    });

    var cwidDB = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        //prefetch: getCommonBaseUrl("util/common/user-data-search/cwid/min","employees"),
        remote: getCommonBaseUrl("util/common/user-data-search/cwid/"+suggestions_limit+"/%QUERY","employees"),
        dupDetector: duplicationDetector,
        limit: suggestions_limit
    });

    var admintitleDB = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        //prefetch: getCommonBaseUrl("util/common/user-data-search/admintitle/min","employees"),
        remote: getCommonBaseUrl("util/common/user-data-search/admintitle/"+suggestions_limit+"/%QUERY","employees"),
        dupDetector: duplicationDetector,
        limit: suggestions_limit
    });

//    var academictitleDB = new Bloodhound({
//        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
//        queryTokenizer: Bloodhound.tokenizers.whitespace,
//        //prefetch: getCommonBaseUrl("util/common/user-data-search/academictitle/min","employees"),
//        remote: getCommonBaseUrl("util/common/user-data-search/academictitle/"+suggestions_limit+"/%QUERY","employees"),
//        dupDetector: duplicationDetector,
//        limit: suggestions_limit
//    });

//    var medicaltitleDB = new Bloodhound({
//        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
//        queryTokenizer: Bloodhound.tokenizers.whitespace,
//        //prefetch: getCommonBaseUrl("util/common/user-data-search/medicaltitle/min","employees"),
//        remote: getCommonBaseUrl("util/common/user-data-search/medicaltitle/"+suggestions_limit+"/%QUERY","employees"),
//        dupDetector: duplicationDetector,
//        limit: suggestions_limit
//    });

    userDB.initialize();
    institutionDB.initialize();
    cwidDB.initialize();
    admintitleDB.initialize();
    //academictitleDB.initialize();
    //medicaltitleDB.initialize();

    var myTypeahead = $('.multiple-datasets-typeahead-search .typeahead').typeahead({
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
            name: 'admintitle',
            displayKey: 'text',
            source: admintitleDB.ttAdapter(),
            templates: {
                header: '<h3 class="search-name">Administrative Title</h3>'
            }
        },
//        {
//            name: 'academictitle',
//            displayKey: 'text',
//            source: academictitleDB.ttAdapter(),
//            templates: {
//                header: '<h3 class="search-name">Academic Title</h3>'
//            }
//        },
//        {
//            name: 'medicaltitle',
//            displayKey: 'text',
//            source: medicaltitleDB.ttAdapter(),
//            templates: {
//                header: '<h3 class="search-name">Medical Title</h3>'
//            }
//        },
        {
            name: 'institution',
            displayKey: 'text',
            source: institutionDB.ttAdapter(),
            templates: {
                header: '<h3 class="search-name">Organization</h3>'
            }
        },
        {
            name: 'cwid',
            displayKey: 'text',
            source: cwidDB.ttAdapter(),
            templates: {
                header: '<h3 class="search-name">CWID</h3>'
            }
        }
    );
        
    //var _typeaheadSearchInput = $('#user-typeahead-search-form input');

    // Attach initialized event to it
    myTypeahead.on('typeahead:selected',function(event, suggestion) {
        //show user by id
        //console.log('selected event');
        if( suggestion.id != "" ) {
            
            //stop default event
            event.preventDefault();
            //remove attached listeners by replacing the element with its clone
            var el = document.getElementById('user-typeahead-search-form');
            if( el ) {
                var elClone = el.cloneNode(true);
                el.parentNode.replaceChild(elClone, el);
            }          
            var el = document.getElementById('navbar-user-typeahead-search-form');
            if( el ) {
                var elClone = el.cloneNode(true);
                el.parentNode.replaceChild(elClone, el);
            }            
            
            //console.log('user chosen with id='+suggestion.id);
            //var url = 'user/'+suggestion.id;
            var url = getCommonBaseUrl('user/'+suggestion.id,"employees");
            window.open(url,"_self");
                                              
            return false;
            
        } 
        
    });
    

    //navbar search on enter keydown: typeahead submit-on-enter-field form-control
    $('.user-typeahead-search-form input').keydown(function(event) {
        if(event.keyCode == 13) {
            event.preventDefault();
            if( $(this).val() != "" ) {
                //console.log('enter pressed => submit form');
                $('.user-typeahead-search-form').submit();
            }
        }
    });

}





function initTypeaheadOrderSiteSearch() {
    if( $('#multiple-datasets-typeahead-ordersearch').length == 0 ) {
        return;
    }



}







function duplicationDetector(remoteMatch, localMatch) {
    //console.log('dup check');
    if( remoteMatch.username === localMatch.username && remoteMatch.keytypeid === localMatch.keytypeid ) {
        return true;
    }
    return false;
}

