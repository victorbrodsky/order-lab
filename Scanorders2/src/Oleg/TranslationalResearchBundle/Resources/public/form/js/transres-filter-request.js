
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

_genericusers = [];

$(document).ready(function() {

    console.log('transres-filter-request.js');

    initTypeaheadTransresProjectSearch();

    //console.log("load users");
    //make only one ajax query
    //getComboboxGeneric(
    // holder,
    // name,
    // globalDataArray,
    // multipleFlag,
    // urlprefix,
    // sitename,
    // force,
    // placeholder,
    // thisAsyncflag
    // )
    var holder = $('#transres-filter-request');
    //getComboboxGeneric(newForm,'locationusers',_locationusers,false,'');
    getComboboxGeneric(
        holder,
        'submitter',
        _genericusers,      //globalDataArray
        false,              //multipleFlag
        'genericusers/',    //urlprefix
        "employees",        //sitename
        null,               //force
        null,               //placeholder
        false               //thisAsyncflag. Async - get array of users (_genericusers) asynchronous.
    );
    //Now use _genericusers to populate other user select boxes
    //populateSelectCombobox( targetid, globalDataArray, placeholder, multipleFlag );
    populateSelectCombobox( $('.ajax-combobox-billingcontact'), _genericusers, true, false );
    populateSelectCombobox( $('.ajax-combobox-pis'), _genericusers, true, true );
    populateSelectCombobox( $('.ajax-combobox-completedby'), _genericusers, true, false );
});


//serach by project oid and pis
function initTypeaheadTransresProjectSearch() {

    if( $('.multiple-datasets-typeahead-search-project').length == 0 ) {
        return;
    }

    //console.log('typeahead search');

    var suggestions_limit = 10;
    var rateLimitBy = 'debounce'; //Can be either debounce or throttle. Defaults to debounce
    var rateLimitWait = 300; //The time interval in milliseconds that will be used by rateLimitBy. Defaults to 300
    var prefetchmin = "prefetchmin";

    //Bloodhound: Prefetched data is fetched and processed on initialization.
    //If the browser supports local storage, the processed data will be cached there to prevent additional network requests on subsequent page loads.
    //Don't use prefetch because the results will be stored in the browser cache and will not be updated on the user search

    var oidDBprefetch = null;
    var titleDBprefetch = null;
    var pisDBprefetch = null;

    var searchLimit = 100;

    //oidDBprefetch = getCommonBaseUrl("util/common/user-data-search/user/"+searchLimit+"/prefetchmin","employees");
    //titleDBprefetch = getCommonBaseUrl("util/common/user-data-search/institution/"+searchLimit+"/prefetchmin","employees");

    var searchProject = Routing.generate('translationalresearch_project_typeahead_search');
    //oidDBprefetch = searchProject + "/" + "oid" + "/" + searchLimit + "/" + prefetchmin;
    //console.log("oidDBprefetch="+oidDBprefetch);

    //var searchTitle = Routing.generate('translationalresearch_project_typeahead_search');
    //titleDBprefetch = searchProject + "/" + "title" + "/" + searchLimit + "/" + prefetchmin;

    //var searchPis = Routing.generate('translationalresearch_project_typeahead_search');
    //pisDBprefetch = searchProject + "/" + "pis" + "/" + searchLimit + "/" + prefetchmin;

    var complex = true; //false;
    var complex = false;

    if( complex ) {
        var oidDB = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('oid'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            //prefetch: oidDBprefetch,   //getCommonBaseUrl("util/common/user-data-search/user/"+suggestions_limit+"/prefetchmin","employees"),
            //remote: getCommonBaseUrl("util/common/user-data-search/user/" + suggestions_limit + "/%QUERY", "employees"),
            remote: searchProject + "/oid/" + suggestions_limit + "/%QUERY",
            dupDetector: duplicationDetector,
            limit: suggestions_limit,
            rateLimitBy: rateLimitBy,
            rateLimitWait: rateLimitWait
        });

        var titleDB = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            //prefetch: titleDBprefetch,   //getCommonBaseUrl("util/common/user-data-search/institution/prefetchmin","employees"),
            //remote: getCommonBaseUrl("util/common/user-data-search/institution/" + suggestions_limit + "/%QUERY", "employees"),
            remote: searchProject + "/title/" + suggestions_limit + "/%QUERY",
            dupDetector: duplicationDetector,
            limit: suggestions_limit,
            rateLimitBy: rateLimitBy,
            rateLimitWait: rateLimitWait
        });

        var pisDB = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('pis'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            //prefetch: pisDBprefetch,   //getCommonBaseUrl("util/common/user-data-search/institution/prefetchmin","employees"),
            //remote: getCommonBaseUrl("util/common/user-data-search/institution/" + suggestions_limit + "/%QUERY", "employees"),
            remote: searchProject + "/pis/" + suggestions_limit + "/%QUERY",
            dupDetector: duplicationDetector,
            limit: suggestions_limit,
            rateLimitBy: rateLimitBy,
            rateLimitWait: rateLimitWait
        });

        oidDB.initialize();
        titleDB.initialize();
        pisDB.initialize();

    } else {

        var singleDb = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('oid'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            //prefetch: oidDBprefetch, //getCommonBaseUrl("util/common/user-data-search/single/"+suggestions_limit+"/prefetchmin","employees"),
            //remote: getCommonBaseUrl("util/common/user-data-search/single/" + suggestions_limit + "/%QUERY", "employees"),
            remote: searchProject + "/all/" + suggestions_limit + "/%QUERY",
            dupDetector: duplicationDetector,
            limit: suggestions_limit,
            rateLimitBy: rateLimitBy,
            rateLimitWait: rateLimitWait
        });

        singleDb.initialize();
    }


    if( complex ) {
        //limit project title by limit = 20;
        var myTypeahead = $('.multiple-datasets-typeahead-search-project .typeahead').typeahead({
                highlight: true
            },
            {
                name: 'oid',
                //displayKey: 'title',
                display: function(item){
                    var title = item.title;
                    if( title.length > 20 ) {
                        title = title.substring(0,20);
                    }
                    return item.oid+', '+title+', PI '+item.pis;
                },
                source: oidDB.ttAdapter(),
                templates: {
                    header: '<h3 class="search-name">Project ID</h3>',
                    // suggestion: function(item) {
                    //     var title = item.title;
                    //     if( title.length > 20 ) {
                    //         title = title.substring(0,20);
                    //     }
                    //     var res = item.oid+', '+title+', PI '+item.pis;
                    //     return '<div>' + res + '</div>'
                    // }
                }
            },
            {
                name: 'title',
                //displayKey: 'title',
                display: function(item){
                    var title = item.title;
                    if( title.length > 20 ) {
                        title = title.substring(0,20);
                    }
                    return item.oid+', '+title+', PI '+item.pis;
                },
                source: titleDB.ttAdapter(),
                templates: {
                    header: '<h3 class="search-name">Project Title</h3>',
                    // suggestion: function(item) {
                    //     var title = item.title;
                    //     if( title.length > 20 ) {
                    //         title = title.substring(0,20);
                    //     }
                    //     var res = item.oid+', '+title+', PI '+item.pis;
                    //     return '<div>' + res + '</div>'
                    // }
                }
            },
            {
                name: 'pis',
                //displayKey: 'title',
                display: function(item){
                    var title = item.title;
                    if( title.length > 20 ) {
                        title = title.substring(0,20);
                    }
                    return item.oid+', '+title+', PI '+item.pis;
                },
                source: pisDB.ttAdapter(),
                templates: {
                    header: '<h3 class="search-name">Project PIs</h3>',
                    // suggestion: function(item) {
                    //     var title = item.title;
                    //     if( title.length > 20 ) {
                    //         title = title.substring(0,20);
                    //     }
                    //     var res = item.oid+', '+title+', PI '+item.pis;
                    //     return '<div>' + res + '</div>'
                    // }
                }
            }
        );
    } else {
        var myTypeahead = $('.multiple-datasets-typeahead-search-project .typeahead').typeahead({
                highlight: true
            },
            {
                name: 'single',
                //displayKey: 'title',
                display: function(item){
                    var title = item.title;
                    if( title.length > 20 ) {
                        title = title.substring(0,20);
                    }
                    return item.oid+', '+title+', PI '+item.pis;
                },
                source: singleDb.ttAdapter(),
                // templates: {
                //     header: '<h3 class="search-name">Project</h3>'
                // }
            }
        );
    }

}

function duplicationDetector(remoteMatch, localMatch) {
    //console.log('dup check');
    if( remoteMatch.id === localMatch.id ) {
        return true;
    }
    return false;
}
