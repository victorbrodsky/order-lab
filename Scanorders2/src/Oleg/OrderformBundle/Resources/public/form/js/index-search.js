
//Currently not used.
//It might be used if we want to append search results dynamically via ajax

$(document).ready(function() {

    //buildSearchedObjects()

});

function buildSearchedObjects() {

//    var routeName = $("#routeName").val();
//    var service = $("#service").val();
//    var filter = $("#filter").val();
//    var search = $("#search").val();
//    var page = $("#page").val();

    //var parameters = $('#scanorder-search-parameters');
    var parameters = document.querySelector('#scanorder-search-parameters');
    var routename = parameters.dataset.routename;
    var service = parameters.dataset.service;
    var filter = parameters.dataset.filter;
    var search = parameters.dataset.search;
    var page = parameters.dataset.page;

    //console.log("routename="+routename+", service="+service+",filter="+filter+", search="+search);

    var searchObjects = [
        "message.oid",
//        "educational.courseTitleStr",
//        "educational.lessonTitleStr",
//        "directorUser.username",
//        "directorUser.displayName",
//        "research.projectTitleStr",
//        "research.setTitleStr",
        "accession"
    ];

    for (var i = 0; i < searchObjects.length; i++) {

        //console.log("searchObjects[i]="+searchObjects[i]);

        //http://stackoverflow.com/questions/9516412/load-view-using-ajax-symfony2
        $.ajax({
            url: getCommonBaseUrl("scanorder-complex-search"),	//urlBase+"scanorder-complex-search",
            type: "POST",
            cache: false,
            dataType: "html",
            //dataType: "json",
            data: {routename:routename, service:service, filter:filter, search:search, searchobject:searchObjects[i], page:page },
            timeout: _ajaxTimeout,
            success: function (data) {
                //console.debug("data="+data);
                $(".scanorder-search").append(data);
                //$(".scanorder-search").html(data['html']);
            },
            error: function ( x, t, m ) {
                if( t === "timeout" ) {
                    getAjaxTimeoutMsg();
                }
                console.debug("error data="+data);
            }
        });

    }
}