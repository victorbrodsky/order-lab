/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/12/14
 * Time: 3:36 PM
 * To change this template use File | Settings | File Templates.
 */

var _htableid = "#multi-dataTable";

var _accessiontypes_simple = new Array();
var _mrntypes_simple = new Array();
var _stains_simple = new Array();
var _procedures_simple = new Array();
var _organs_simple = new Array();
var _scanregions_simple = new Array();
var _slidetypes_simple = new Array();

var _slidetypes = new Array();

var _columnData_scanorder = [

    //header: 1
    { header:'ID', columns:{} },

    //accession: 2
    { header:'Accession Type', columns:{type:'autocomplete', source:_accessiontypes_simple, strict:false} },
    { header:'Accession Number', columns:{} },

    //part: 1
    { header:'Part Name', columns:{} },

    //block: 1
    { header:'Block Name', columns:{} },

    //slide: 4
    { header:'Stain', columns:{type:'autocomplete', source:_stains_simple, strict:false} },
    { header:'Magnification', columns:{type:'autocomplete', source:['20X','40X'], strict:false} },
    { header:'Diagnosis', columns:{} },
    { header:'Reason for Scan/Note', columns:{} },

    //patient: 7
    { header:'MRN Type', columns:{type:'autocomplete', source:_mrntypes_simple, strict:false} },
    { header:'MRN', columns:{} },
    { header:'Name', columns:{} },
    { header:'Sex', columns:{type:'autocomplete', source:['Female','Male','Unspecified'], strict:false} },
    { header:'DOB', columns:{type:'date', dateFormat: 'mm/dd/yy'} },
    { header:'Age', columns:{} },
    { header:'Clinical History', columns:{} },

    //procedure: 1
    { header:'Procedure Type', columns:{type:'autocomplete', source:_procedures_simple, strict:false} },

    //part: 6
    { header:'Source Organ', columns:{type:'autocomplete', source:_organs_simple, strict:false} },
    { header:'Gross Description', columns:{} },
    { header:'Differential Diagnoses', columns:{} },
    { header:'Type of Disease', columns:{type:'autocomplete', source:['Neoplastic','Non-Neoplastic','None','Unspecified'], strict:false} },
    { header:'Origin', columns:{type:'autocomplete', source:['Primary','Metastatic','Unspecified'], strict:false} },
    { header:'Primary Site of Origin', columns:{type:'autocomplete', source:_organs_simple, strict:false} },

    //slide: 6
    { header:'Title', columns:{} },
    { header:'Slide Type', columns:{type:'autocomplete', source:_slidetypes_simple, strict:false} },
    { header:'Microscopic Description', columns:{} },
    { header:'Results of Special Stains', columns:{type:'autocomplete', source:_stains_simple, strict:false} },
    { header:'Relevant Scanned Images', columns:{} },
    { header:'Region to scan', columns:{type:'autocomplete', source:_scanregions_simple, strict:false} }

];

$(document).ready(function() {

    getSlideTypes();

    var _TIMEOUT = 300; // waitfor test rate [msec]

    // Wait until idle (busy must be false)
    waitfor( ajaxFinishedCondition, true, _TIMEOUT, 0, 'play->busy false', function() {
        //console.log('The show can resume !');
        handsonTableInit();
    });


});

function ajaxFinishedCondition() {
    if(
            _accessiontype.length > 0 &&
            _mrntype.length > 0 &&
            _stain.length > 0 &&
            _procedure.length > 0 &&
            _organ.length > 0 &&
            _scanregion.length > 0 &&
            _slidetypes.length > 0
    ) {

        for(var i = 0; i < _accessiontype.length-1; i++) {
            _accessiontypes_simple.push( _accessiontype[i].text );
        }

        for(var i = 0; i < _mrntype.length-1; i++) {
            console.log('mrntype='+ _mrntype[i].text);
            _mrntypes_simple.push( _mrntype[i].text );
        }

        for(var i = 0; i < _stain.length-1; i++) {
            _stains_simple.push( _stain[i].text );
        }

        for(var i = 0; i < _procedure.length-1; i++) {
            _procedures_simple.push( _procedure[i].text );
        }

        for(var i = 0; i < _scanregion.length-1; i++) {
            _scanregions_simple.push( _scanregion[i].text );
        }

        for(var i = 0; i < _slidetypes.length-1; i++) {
            _slidetypes_simple.push( _slidetypes[i].text );
        }

        return true;

    } else {

        return false;
    }
}


function handsonTableInit() {

//    //total 29
//    var columnData = [
//
//        //header: 1
//        { header:'ID', columns:{} },
//
//        //accession: 2
//        { header:'Accession Type', columns:{type:'autocomplete', source:_accessiontypes_simple, strict:false} },
//        { header:'Accession Number', columns:{} },
//
//        //part: 1
//        { header:'Part Name', columns:{} },
//
//        //block: 1
//        { header:'Block Name', columns:{} },
//
//        //slide: 4
//        { header:'Stain', columns:{type:'autocomplete', source:_stains_simple, strict:false} },
//        { header:'Magnification', columns:{type:'autocomplete', source:['20X','40X'], strict:false} },
//        { header:'Diagnosis', columns:{} },
//        { header:'Reason for Scan/Note', columns:{} },
//
//        //patient: 7
//        { header:'MRN Type', columns:{type:'autocomplete', source:_mrntypes_simple, strict:false} },
//        { header:'MRN', columns:{} },
//        { header:'Name', columns:{} },
//        { header:'Sex', columns:{type:'autocomplete', source:['Female','Male','Unspecified'], strict:false} },
//        { header:'DOB', columns:{type:'date', dateFormat: 'mm/dd/yy'} },
//        { header:'Age', columns:{} },
//        { header:'Clinical History', columns:{} },
//
//        //procedure: 1
//        { header:'Procedure Type', columns:{type:'autocomplete', source:_procedures_simple, strict:false} },
//
//        //part: 6
//        { header:'Source Organ', columns:{type:'autocomplete', source:_organs_simple, strict:false} },
//        { header:'Gross Description', columns:{} },
//        { header:'Differential Diagnoses', columns:{} },
//        { header:'Type of Disease', columns:{type:'autocomplete', source:['Neoplastic','Non-Neoplastic','None','Unspecified'], strict:false} },
//        { header:'Origin', columns:{type:'autocomplete', source:['Primary','Metastatic','Unspecified'], strict:false} },
//        { header:'Primary Site of Origin', columns:{type:'autocomplete', source:_organs_simple, strict:false} },
//
//        //slide: 6
//        { header:'Title', columns:{} },
//        { header:'Slide Type', columns:{type:'autocomplete', source:_slidetypes_simple, strict:false} },
//        { header:'Microscopic Description', columns:{} },
//        { header:'Results of Special Stains', columns:{type:'autocomplete', source:_stains_simple, strict:false} },
//        { header:'Relevant Scanned Images', columns:{} },
//        { header:'Region to scan', columns:{type:'autocomplete', source:_scanregions_simple, strict:false} }
//
//    ];

    var data = new Array();

    var rows = 5;//51;//501;

    var rowElements = new Array();
    console.log( "header length="+_columnData_scanorder.length );
    for(var i = 0; i < _columnData_scanorder.length-1; i++) {
        rowElements.push(null);
    }

    var columnsType = new Array();
    var colHeader = new Array();

    for( var i=1; i<rows; i++ ) {

        var index = new Array();
        index = [i];
        var row = index.concat(rowElements);
        data.push(row);

    }

    for( var i=0; i<_columnData_scanorder.length-1; i++ ) {
        colHeader.push( _columnData_scanorder[i]['header'] );
        columnsType.push( _columnData_scanorder[i]['columns'] );
    }

    //$('#multi-dataTable').doubleScroll();

    //console.log(data);
    //console.log(colHeader);
    //console.log(columnsType);

    $(_htableid).handsontable({
        data: data,
        colHeaders: colHeader,
        minSpareRows: 1,
        contextMenu: true,
        manualColumnMove: true,
        manualColumnResize: true,
        stretchH: 'all',
        columns: columnsType
    });

    $(_htableid+' table').addClass('table table-striped table-hover');

    //$('.double-scroll').doubleScroll();

    //var Dragdealer = require('dragdealer').Dragdealer;
    //new Dragdealer('top-scrollbar-handsontable');

    //var tableSliderEl = $('#multi-dataTable').find('.dragdealer');
    //console.log(tableSliderEl);
    //console.log('slider width='+tableSliderEl.width());
//
//    var topSlider = $('#top-scrollbar-handsontable');
    //topSlider.width(tableSliderEl.width());
    //topSlider.append(tableSliderEl);

    //var tabWidth = $('#multi-dataTable').width();
    //console.log('tabWidth width='+tabWidth);

//    $(".div1").width(tabWidth);
//    $(".div2").width(tabWidth);
//
//    tabWidth = tabWidth-tabWidth/1.2;
//
//    $(".wrapper1").width(tabWidth);
//    $(".wrapper2").width(tabWidth);
//
//    $(".wrapper1").scroll(function(){
//        $(".wrapper2")
//            .scrollLeft($(".wrapper1").scrollLeft());
//    });
//    $(".wrapper2").scroll(function(){
//        $(".wrapper1")
//            .scrollLeft($(".wrapper2").scrollLeft());
//    });

}


function getDataForm() {

    var handsontable = $(_htableid).data('handsontable');

    var hdata = handsontable.getData();

    console.log('data len='+hdata.length);

    console.log( 'column'+'0'+',row'+'1'+':'+ hdata[0][1] );
    console.log( 'column'+'1'+',row'+'2'+':'+ hdata[1][2] );

    //for each row
    for( var i=0; i<hdata.length-1; i++ ) {

        //console.log( 'row'+(i+1)+':' + hdata[i] );

        //foe each column
        for( var ii=0; ii<hdata[i].length-1; ii++ ) {

            //console.log( 'column'+(ii+1)+':' + hdata[i][ii] );
            validateCell( i, ii, hdata[i][ii] );

        }


    }

    //console.log( 'hdata=' + handsontable );

    //var checkcell = $(_htableid).handsontable("getCell", 1, 2);
    //checkcell.style.color = "red";
    //checkcell.style.backgroundColor = '#F2DEDE';



}

function validateCell( row, column, value ) {

    console.log( row+','+column+' value=' + value );

    var columnHeader = _columnData_scanorder[column].header;
    var checkcell = $(_htableid).handsontable("getCell", row, column);

    if( !checkcell ) {
        return;
    }

    //checkcell.style.color = "red";
    //checkcell.style.backgroundColor = '#F2DEDE';

    switch( columnHeader )
    {
        case 'Accession Number':
            if( !value || value == '' || value == null ) {
                console.log('Accession Number: value null !!!!!!!!!!!!!');
                checkcell.style.backgroundColor = '#F2DEDE';
            }
            break;
        case 'Part Name':
            if( !value || value == '' || value == null ) {
                console.log('Part Name: value null !!!!!!!!!!!!!');
                checkcell.style.backgroundColor = '#F2DEDE';
            }
            break;
        default:
            if( !value || value == '' || value == null ) {
                checkcell.style.backgroundColor = '#F2DEDE';
            }
    }


}







function getSlideTypes() {

    var url = urlCommon+"slidetype";

    if( _slidetypes.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _slidetypes = data;
        });
    }
}


//**********************************************************************
// function waitfor - Wait until a condition is met
//
// Needed parameters:
//    test: function that returns a value
//    expectedValue: the value of the test function we are waiting for
//    msec: delay between the calls to test
//    callback: function to execute when the condition is met
// Parameters for debugging:
//    count: used to count the loops
//    source: a string to specify an ID, a message, etc
//**********************************************************************
function waitfor(test, expectedValue, msec, count, source, callback) {
    //console.log('waitfor, count='+count);

    if( count > 200 ) {
        //console.log('waitfor, exit on count');
        return;
    }

    // Check if condition met. If not, re-check later (msec).
    while( test() !== expectedValue ) {
        count++;
        setTimeout(function() {
            waitfor(test, expectedValue, msec, count, source, callback);
        }, msec);
        return;
    }
    // Condition finally met. callback() can be executed.
    //console.log(source + ': ' + test() + ', expected: ' + expectedValue + ', ' + count + ' loops.');
    callback();
}
