/**
 * Created by oli2002 on 1/13/2023.
 */

function importHolidayDates(btn) {
    //console.log("url:",$(btn).closest('.row').find('.holidayDatesUrl'));
    var holidayDatesUrl = $(btn).closest('.row').find('.holidayDatesUrl').val();
    console.log("holidayDatesUrl="+holidayDatesUrl);

    var l = Ladda.create($(btn).get(0));
    l.start();

    var url = Routing.generate('vacreq_import_holiday_dates');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        type: "GET",
        data: {holidayDatesUrl: holidayDatesUrl },
        //dataType: 'json',
        async: asyncflag
    }).success(function(response) {
        console.log(response);
        if( response == "OK" ) {
            console.log("response OK");
        } else {
            console.log("response not OK");
        }
    }).done(function() {
        l.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    });
}
