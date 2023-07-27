/**
 * Created by Oleg Ivanov on 7/18/2023.
 */

function userInitDatepickerYears( inputSelect ) {
    //var target = ".datepicker-only-year";
    var target = inputSelect;

    //Show as "2024, 2023, 2025"
    var datefilter = $(target).datepicker( {
        autoclose: true,
        format: " yyyy",
        viewMode: "years",
        minViewMode: "years",
        orientation: 'auto',
        //startView: "years",
        multidate: true,
        clearBtn: true,
        multidateSeparator: ","
    });

    userUtilChangeDateListner(datefilter,inputSelect);
}

function userUtilChangeDateListner( datefilter, inputSelect ) {
    datefilter.on('changeDate', function(e){
        if( e == null || e.date == null ) {
            return null;
        }
        var newyear = e.date.getFullYear();
        newyear = newyear.toString();
        newyear = newyear.trim();
        var years = $(inputSelect).val();
        years = years.trim();
        years = years.replace(/ /g, '');
        //console.log('newyear=['+newyear+"], years=["+years+"]");
        var yearsArr = years.split(',');
        //console.log("original yearsArr=",yearsArr);
        var count = userUtilGetOccurrences(years,newyear);
        //console.log("count="+count);
        if( count == 2 ) {
            //remove newyear from yearsArr
            years = userUtilRemoveItemAll(yearsArr,newyear);
            //console.log("after remove all years=",years);
        } else {
            //remove duplicate
            years = userUtilGetUniqArr(yearsArr);
            //console.log("unique years=",years);
        }

        //console.log("new years=",years);
        $(inputSelect).val(years);
        datefilter.datepicker('update');
    });
}

function userUtilGetUniqArr(a) {
    var prims = {"boolean":{}, "number":{}, "string":{}}, objs = [];

    return a.filter(function(item) {
        var type = typeof item;
        if(type in prims)
            return prims[type].hasOwnProperty(item) ? false : (prims[type][item] = true);
        else
            return objs.indexOf(item) >= 0 ? false : objs.push(item);
    });
}
//https://stackoverflow.com/questions/4009756/how-to-count-string-occurrence-in-string
/** Function that count occurrences of a substring in a string;
 * @param {String} string               The string
 * @param {String} subString            The sub string to search for
 * @param {Boolean} [allowOverlapping]  Optional. (Default:false)
 *
 * @author Vitim.us https://gist.github.com/victornpb/7736865
 * @see Unit Test https://jsfiddle.net/Victornpb/5axuh96u/
 * @see https://stackoverflow.com/a/7924240/938822
 */
function userUtilGetOccurrences(string, subString, allowOverlapping) {

    string += "";
    subString += "";
    if (subString.length <= 0) return (string.length + 1);

    var n = 0,
        pos = 0,
        step = allowOverlapping ? 1 : subString.length;

    while (true) {
        pos = string.indexOf(subString, pos);
        if (pos >= 0) {
            ++n;
            pos += step;
        } else break;
    }
    return n;
}
function userUtilRemoveItemAll(arr, value) {
    var i = 0;
    while (i < arr.length) {
        if (arr[i] === value) {
            arr.splice(i, 1);
        } else {
            ++i;
        }
    }
    return arr;
}

