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
 * Created by oli2002 on 10/29/14.
 */


function initTypeaheadUserSiteSearch() {

    if( $('.multiple-datasets-typeahead-search').length == 0 ) {
        return;
    }       

    //console.log('typeahead search');

    var suggestions_limit = 5;
    var rateLimitBy = 'debounce'; //Can be either debounce or throttle. Defaults to debounce
    var rateLimitWait = 30; //The time interval in milliseconds that will be used by rateLimitBy. Defaults to 300

    //Bloodhound: Prefetched data is fetched and processed on initialization.
    //If the browser supports local storage, the processed data will be cached there to prevent additional network requests on subsequent page loads.
    //Don't use prefetch because the results will be stored in the browser cache and will not be updated on the user search

    var userDBprefetch = null;
    var institutionDBprefetch = null;
    var cwidDBprefetch = null;
    var admintitleDBprefetch = null;
    var singleDbprefetch = null;
    //if( document.getElementById("multiple-datasets-typeahead-search") ) { //navbar-multiple-datasets-typeahead-search present only on the other pages in he navbar
        //console.log('Home page');
        //it's cached, so it's safe to use it on all pages
        var searchLimit = 100;
        userDBprefetch = getCommonBaseUrl("util/common/user-data-search/user/"+searchLimit+"/prefetchmin","employees");
        institutionDBprefetch = getCommonBaseUrl("util/common/user-data-search/institution/"+searchLimit+"/prefetchmin","employees");
        cwidDBprefetch = getCommonBaseUrl("util/common/user-data-search/cwid/"+searchLimit+"/prefetchmin","employees");
        admintitleDBprefetch = getCommonBaseUrl("util/common/user-data-search/admintitle/"+searchLimit+"/prefetchmin","employees");
        singleDbprefetch = getCommonBaseUrl("util/common/user-data-search/single/"+searchLimit+"/prefetchmin","employees");
    //}

    var complex = true; //false;

    if( complex ) {
        var userDB = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: userDBprefetch,   //getCommonBaseUrl("util/common/user-data-search/user/"+suggestions_limit+"/prefetchmin","employees"),
            remote: getCommonBaseUrl("util/common/user-data-search/user/" + suggestions_limit + "/%QUERY", "employees"),
            dupDetector: duplicationDetector,
            limit: suggestions_limit,
            rateLimitBy: rateLimitBy,
            rateLimitWait: rateLimitWait
        });

        var institutionDB = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: institutionDBprefetch,   //getCommonBaseUrl("util/common/user-data-search/institution/prefetchmin","employees"),
            remote: getCommonBaseUrl("util/common/user-data-search/institution/" + suggestions_limit + "/%QUERY", "employees"),
            dupDetector: duplicationDetector,
            limit: suggestions_limit,
            rateLimitBy: rateLimitBy,
            rateLimitWait: rateLimitWait
        });

        var cwidDB = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: cwidDBprefetch,   //getCommonBaseUrl("util/common/user-data-search/cwid/prefetchmin","employees"),
            remote: getCommonBaseUrl("util/common/user-data-search/cwid/" + suggestions_limit + "/%QUERY", "employees"),
            dupDetector: duplicationDetector,
            limit: suggestions_limit,
            rateLimitBy: rateLimitBy,
            rateLimitWait: rateLimitWait
        });

        var admintitleDB = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: admintitleDBprefetch, //getCommonBaseUrl("util/common/user-data-search/admintitle/prefetchmin","employees"),
            remote: getCommonBaseUrl("util/common/user-data-search/admintitle/" + suggestions_limit + "/%QUERY", "employees"),
            dupDetector: duplicationDetector,
            limit: suggestions_limit,
            rateLimitBy: rateLimitBy,
            rateLimitWait: rateLimitWait
        });

//    var academictitleDB = new Bloodhound({
//        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
//        queryTokenizer: Bloodhound.tokenizers.whitespace,
//        //prefetch: getCommonBaseUrl("util/common/user-data-search/academictitle/prefetchmin","employees"),
//        remote: getCommonBaseUrl("util/common/user-data-search/academictitle/"+suggestions_limit+"/%QUERY","employees"),
//        dupDetector: duplicationDetector,
//        limit: suggestions_limit
//    });

//    var medicaltitleDB = new Bloodhound({
//        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
//        queryTokenizer: Bloodhound.tokenizers.whitespace,
//        //prefetch: getCommonBaseUrl("util/common/user-data-search/medicaltitle/prefetchmin","employees"),
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

    } else {

        var singleDb = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: singleDbprefetch, //getCommonBaseUrl("util/common/user-data-search/single/"+suggestions_limit+"/prefetchmin","employees"),
            remote: getCommonBaseUrl("util/common/user-data-search/single/" + suggestions_limit + "/%QUERY", "employees"),
            dupDetector: duplicationDetector,
            limit: suggestions_limit,
            rateLimitBy: rateLimitBy,
            rateLimitWait: rateLimitWait
        });

        singleDb.initialize();
    }


    if( complex ) {
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
                    header: '<h3 class="search-name">User ID</h3>'
                }
            }
        );
    } else {
        var myTypeahead = $('.multiple-datasets-typeahead-search .typeahead').typeahead({
                highlight: true
            },
            {
                name: 'single',
                displayKey: 'text',
                source: singleDb.ttAdapter(),
                templates: {
                    header: '<h3 class="search-name">User</h3>'
                }
            }
        );
    }
        
    //var _typeaheadSearchInput = $('#user-typeahead-search-form input');

    // Attach initialized event to it
    myTypeahead.on('typeahead:selected',function(event, suggestion) {
        //show user by id
        //console.log('selected event');
        if( suggestion.id != "" ) {            
            
            //stop default event
            event.preventDefault();
            //remove attached listeners by removing the element            
            $('.user-typeahead-search-form').remove();
            
            //console.log('user chosen with id='+suggestion.id);
            //var url = 'user/'+suggestion.id;
            var url = getCommonBaseUrl('user/'+suggestion.id,"employees");
            window.open(url,"_self");
                                              
            return;
            
        }//if 
        
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





//function initTypeaheadOrderSiteSearch() {
//    if( $('.multiple-datasets-typeahead-ordersearch').length == 0 ) {
//        return;
//    }
//
//
//
//}







function duplicationDetector(remoteMatch, localMatch) {
    //console.log('dup check');
    if( remoteMatch.username === localMatch.username && remoteMatch.keytypeid === localMatch.keytypeid ) {
        return true;
    }
    return false;
}

