/**
 * Created by oli2002 on 10/29/14.
 */

function initTypeaheadSerach() {

    if( $('#multiple-datasets-typeahead-search').length == 0 ) {
        return;
    }

    console.log('typeahead search');

    var userDB = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: getCommonBaseUrl("util/common/user-data-search/user/min","employees"),
        remote: getCommonBaseUrl("util/common/user-data-search/user/%QUERY.json","employees")
    });

    var titleDB = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: getCommonBaseUrl("util/common/user-data-search/title/min","employees"),
        remote: getCommonBaseUrl("util/common/user-data-search/title/%QUERY.json","employees")
    });

    userDB.initialize();
    titleDB.initialize();

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
            name: 'title',
            displayKey: 'text',
            source: titleDB.ttAdapter(),
            templates: {
                header: '<h3 class="search-name">Administrative Title</h3>'
            }
        }
    );


    // Attach initialized event to it
    myTypeahead.on('typeahead:selected',function(event, suggestion, dataset){
        console.log('on select');
        console.log(suggestion);
    });


}

function onSelectFunction(event, suggestion, dataset) {
    console.log('on select');
    console.log(suggestion);
}




function initTypeaheadSerach_Simple() {

    var substringMatcher = function(strs) {
        return function findMatches(q, cb) {
            var matches, substrRegex;

    // an array that will be populated with substring matches
            matches = [];

    // regex used to determine if a string contains the substring `q`
            substrRegex = new RegExp(q, 'i');

    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
            $.each(strs, function(i, str) {
                if (substrRegex.test(str)) {
    // the typeahead jQuery plugin expects suggestions to a
    // JavaScript object, refer to typeahead docs for more info
                    matches.push({ value: str });
                }
            });

            cb(matches);
        };
    };

    var states = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
        'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii',
        'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana',
        'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota',
        'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
        'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota',
        'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island',
        'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
        'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
    ];

    $('#multiple-datasets-typeahead-search .typeahead').typeahead(
        {
            hint: true,
            highlight: true,
            minLength: 1
        },
        {
            name: 'states',
            displayKey: 'value',
            source: substringMatcher(states)
        }
    );
}