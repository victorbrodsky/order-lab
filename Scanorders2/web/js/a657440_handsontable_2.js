/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/12/14
 * Time: 3:36 PM
 * To change this template use File | Settings | File Templates.
 */

var _htableid = "#multi-dataTable";

var _sotable = null;    //scan order table

var _accessiontypes_simple = new Array();
var _mrntypes_simple = new Array();
var _partname_simple = new Array();
var _blockname_simple = new Array();
var _stains_simple = new Array();
var _procedures_simple = new Array();
var _organs_simple = new Array();
var _scanregions_simple = new Array();
var _slidetypes_simple = new Array();

var _slidetypes = new Array();

var ip_validator_regexp = /^(?:\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b|null)$/;

//total 31
var _columnData_scanorder = [

    //header: 1
    //{ header:'ID', columns:{} },

    //accession: 2
    { header:'Accession Type', default:0, columns:{type:'autocomplete', source:_accessiontypes_simple, strict:false} },
    { header:'Accession Number', columns:{validator: accession_validator_fn} },

    //part: 1
    { header:'Part Name', columns:{type:'autocomplete', source:_partname_simple, strict:false} },

    //block: 1
    { header:'Block Name', columns:{type:'autocomplete', source:_blockname_simple, strict:false} },

    //slide: 4
    { header:'Stain', default:0, columns:{type:'autocomplete', source:_stains_simple, strict:false, colWidths:'120px'} },
    { header:'Scan Magnificaiton', default:0, columns:{type:'autocomplete', source:['20X','40X'], strict:false} },
    { header:'Diagnosis', columns:{} },
    { header:'Reason for Scan/Note', columns:{} },

    //patient: 7
    { header:'MRN Type', default:0, columns:{type:'autocomplete', source:_mrntypes_simple, strict:false} },
    { header:'MRN', columns:{colWidths:'100px'} },
    { header:'Patient Name', columns:{} },
    { header:'Patient Sex', columns:{type:'autocomplete', source:['Female','Male','Unspecified'], strict:false} },
    { header:'Patient DOB', columns:{type:'date', dateFormat: 'mm/dd/yy'} },
    { header:'Patient Age', columns:{} },
    { header:'Clinical History', columns:{} },

    //procedure: 1
    { header:'Procedure Type', columns:{type:'autocomplete', source:_procedures_simple, strict:false} },

    //part: 6
    { header:'Source Organ', columns:{type:'autocomplete', source:_organs_simple, strict:false, colWidths:'100px'} },
    { header:'Gross Description', columns:{} },
    { header:'Differential Diagnoses', columns:{} },
    { header:'Type of Disease', columns:{type:'autocomplete', source:['Neoplastic','Non-Neoplastic','None','Unspecified'], strict:false} },
    { header:'Origin', columns:{type:'autocomplete', source:['Primary','Metastatic','Unspecified'], strict:false, colWidths:'100px'} },
    { header:'Primary Site of Disease Origin', columns:{type:'autocomplete', source:_organs_simple, strict:false} },

    //block: 1
    { header:'Block Section Source', columns:{} },

    //slide: 7
    { header:'Slide Title', columns:{} },
    { header:'Slide Type', default:0, columns:{type:'autocomplete', source:_slidetypes_simple, strict:false} },
    { header:'Microscopic Description', columns:{} },
    { header:'Special Stain', default:0, columns:{type:'autocomplete', source:_stains_simple, strict:false, colWidths:'120px'} },
    { header:'Results of Special Stains', columns:{} },
    { header:'Link(s) to related image(s)', columns:{} },
    { header:'Region to Scan', default:0, columns:{type:'autocomplete', source:_scanregions_simple, strict:false} }

];

$(document).ready(function() {

    //Handsontable.renderers.registerRenderer('redRenderer', redRenderer); //maps function to lookup string

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

        for(var i = 0; i < _accessiontype.length; i++) {
            _accessiontypes_simple.push( _accessiontype[i].text );
        }

        for(var i = 0; i < _mrntype.length; i++) {
            //console.log('mrntype='+ _mrntype[i].text);
            _mrntypes_simple.push( _mrntype[i].text );
        }

        for(var i = 0; i < _partname.length; i++) {
            //console.log('mrntype='+ _mrntype[i].text);
            _partname_simple.push( _partname[i].text );
        }

        for(var i = 0; i < _blockname.length; i++) {
            //console.log('mrntype='+ _mrntype[i].text);
            _blockname_simple.push( _blockname[i].text );
        }

        for(var i = 0; i < _stain.length; i++) {
            _stains_simple.push( _stain[i].text );
        }

        for(var i = 0; i < _procedure.length; i++) {
            _procedures_simple.push( _procedure[i].text );
        }

        for(var i = 0; i < _scanregion.length; i++) {
            _scanregions_simple.push( _scanregion[i].text );
        }

        for(var i = 0; i < _slidetypes.length; i++) {
            _slidetypes_simple.push( _slidetypes[i].text );
        }

        for(var i = 0; i < _organ.length; i++) {
            _organs_simple.push( _organ[i].text );
        }

        return true;

    } else {

        return false;
    }
}


function handsonTableInit() {

    var data = new Array();
    var columnsType = new Array();
    var colHeader = new Array();
    var rows = 11;//51;//501;

    // make init data
    for( var i=1; i<rows; i++ ) {   //foreach row

        var rowElement = new Array();
        //rowElement[0] = i;
        for( var ii=0; ii<_columnData_scanorder.length-1; ii++ ) {  //foreach column

            if( 'default' in _columnData_scanorder[ii] ) {
                var index = _columnData_scanorder[ii]['default'];
                rowElement[ii] = _columnData_scanorder[ii]['columns']['source'][index];
            } else {
                rowElement[ii] = null;
            }

        }//foreach column

        //console.log(rowElement);
        data.push(rowElement);

    }//foreach row

    // make header and columns
    for( var i=0; i<_columnData_scanorder.length; i++ ) {
        colHeader.push( _columnData_scanorder[i]['header'] );
        columnsType.push( _columnData_scanorder[i]['columns'] );
    }


    //$('#multi-dataTable').doubleScroll();

    //console.log(data);
    //console.log(colHeader);
    //console.log(columnsType);

//    Handsontable.renderers.registerRenderer('negativeValueRenderer', negativeValueRenderer); //maps function to lookup string


    $(_htableid).handsontable({
        data: data,
        colHeaders: colHeader,
        minSpareRows: 1,
        contextMenu: ['row_above', 'row_below', 'remove_row'],
        manualColumnMove: true,
        manualColumnResize: true,
        autoWrapRow: true,
        currentRowClassName: 'currentRowScanorder',
        currentColClassName: 'currentColScanorder',
        stretchH: 'all',
        columns: columnsType,
        cells: function (row, col, prop) {

            //var cellProperties = {};
            if( col < 0 ){
                return;
            }

            //////////// set renderer ////////////
            var columnHeader = _columnData_scanorder[col].header;
            if( !validateCell(row,col,null,null) ) {
                if( columnHeader == 'Part Name' || columnHeader == 'Block Name'  ) {
                    this.renderer = redRendererAutocomplete;
                } else {
                    this.renderer = redRenderer;
                }
            } else {
                this.renderer = null;
            }
            //////////// EOF set renderer ////////////

           //return this;
        },
        beforeChange: function (changes, source) {
            for( var i=0; i<changes.length; i++ ) {
//                //console.log(changes[i]);

                var row = changes[i][0];
                var col = changes[i][1];
                var oldvalue = changes[i][2];
                var value = changes[i][3];

                processKeyTypes( row, col, value, oldvalue );
            }
        }
//        afterChange: function (changes, source) {
//
//            if( !changes ) {
//                return;
//            }
//
//            for( var i=0; i<changes.length; i++ ) {
////                //console.log(changes[i]);
//
//                var row = changes[i][0];
//                var col = changes[i][1];
//                var oldvalue = changes[i][2];
//                var value = changes[i][3];
//
//                //generate Id for a new row
//                var totalrows = this.countRows();
//                console.log('totalrows='+totalrows);
//                console.log('row='+row+', col='+col+', value='+value);
//                if( totalrows == (row+2) ) {
//                    console.log('row='+row+', col='+col+', value='+value);
//                    var curId = _sotable.getDataAtCell(row,0);
//                    if( curId == '' || curId == null ) {
//                        var lastId = _sotable.getDataAtCell(row-1,0);
//                        _sotable.setDataAtCell(row,0,lastId+1);
//                    }
//                }
//            }//for
//
//        }//afterChange
    });

    //set bs table
    //$(_htableid+' table').addClass('table-striped table-hover');
    $(_htableid+' table').addClass('table-hover');

    //set scan order table object as global reference
    _sotable = $(_htableid).handsontable('getInstance');

}

var accession_validator_fn = function (value, callback) {
    var res = value.match(/^[0]+$/);
    if( res ) {
        callback(true);
    }
    else {
        callback(false);
    }
};

function processKeyTypes( row, col, value, oldvalue ) {

    var columnHeader = _columnData_scanorder[col].header;

    switch( columnHeader )
    {
        case 'MRN Type':
            if( value && value == 'Auto-generated MRN' ) {
                $.ajax({
                    url: urlCheck+"patient/generate",
                    timeout: _ajaxTimeout,
                    async: asyncflag
                }).success( function(data) {
                    if( data ) {
                        var autogenValue = data.mrn[0].text;
                        //console.log('autogenValue='+autogenValue);
                        setDataCell(row,col+1,autogenValue);
                        //readOnly: true
                    }
                }).error( function ( x, t, m ) {
                   console.log('ERROR auto generate MRN');
                });
            }
            if( oldvalue && oldvalue == 'Auto-generated MRN' && value != 'Auto-generated MRN' ) {
                cleanHTableCell( row, col+1, true )
            }
            break;
        case 'Accession Type':
            if( value && value == 'Auto-generated Accession Number' ) {
                //console.log('Accession Type: value null !!!!!!!!!!!!!');
                $.ajax({
                    url: urlCheck+"accession/generate",
                    timeout: _ajaxTimeout,
                    async: asyncflag
                }).success( function(data) {
                    if( data ) {
                        var autogenValue = data.accession[0].text;
                        //console.log('autogenValue='+autogenValue);
                        setDataCell(row,col+1,autogenValue);
                    }
                }).error( function ( x, t, m ) {
                    console.log('ERROR auto generate Accession Number');
                });
            }
            if( oldvalue && oldvalue == 'Auto-generated Accession Number' && value != 'Auto-generated Accession Number' ) {
                cleanHTableCell( row, col+1, true )
            }
            break;
        default:
            //
    }
}

function setDataCell( row, col, value ) {

    if( value && value != '' ) {    //set
        _sotable.setDataAtCell(row,col,value);
        _sotable.getCellMeta(row,col).readOnly = true;
    } else {    //clean
        _sotable.setDataAtCell(row,col,null);
        _sotable.getCellMeta(row,col).readOnly = false;
    }

}

function processDataForm( action ) {

    var handsontable = $(_htableid).data('handsontable');

    var hdata = handsontable.getData();

    console.log('data len='+hdata.length);
    console.log( 'column'+'0'+',row'+'1'+':'+ hdata[0][1] );
    console.log( 'column'+'1'+',row'+'2'+':'+ hdata[1][2] );

    //for each row (except the first one)
    for( var i=0; i<hdata.length-1; i++ ) {

        //console.log( 'row'+(i+1)+':' + hdata[i] );
        if( hdata[i] !== undefined && hdata[i] !== null && hdata[i] != '' ) {

            //for each column (except the first one)
            for( var ii=0; ii<hdata[i].length-1; ii++ ) {

                //console.log( 'column'+(ii+1)+':' + hdata[i][ii] );
                //validateCell( i, ii, hdata[i][ii], true );

                if( action == 'clean' ) {
                    cleanHTableCell(i,ii, false);
                }

            } //for column

        }

    } //for row

    //console.log( 'hdata=' + handsontable );

    //var checkcell = $(_htableid).handsontable("getCell", 1, 2);
    //checkcell.style.color = "red";
    //checkcell.style.backgroundColor = '#F2DEDE';



}

function cleanHTableCell( row, col, force ) {

    var value = _sotable.getDataAtCell(row,col);

    if( !force && (value === undefined || value === null || value == '') ) {
        return;
    }

    if( col == 0 ) {
        return;
    }

    if( _sotable && _sotable.countRows() == (row+1) ) {
        return;
    }

    //console.log('row='+row+', col='+col+', value='+value);

    var columnHeader = _columnData_scanorder[col].header;

    switch( columnHeader )
    {
        case 'MRN':
            //console.log('delete value='+value);
            $.ajax({
                url: urlCheck+"patient/delete/"+value+"?extra=13",
                type: 'DELETE',
                timeout: _ajaxTimeout,
                async: asyncflag
            }).success( function(data) {
                if( data >= 0 ) {
                    //console.debug("Delete Success, data="+data);
                    setDataCell(row,col,null);
                } else {
                    console.debug("Delete with data Error: data="+data);
                }
            }).error( function ( x, t, m ) {
                console.log('ERROR delete MRN');
            });

            break;
        case 'Accession Number':
            //console.log('Accession => value='+value);
            $.ajax({
                url: urlCheck+"accession/delete/"+value+"?extra=8",
                type: 'DELETE',
                timeout: _ajaxTimeout,
                async: asyncflag
            }).success( function(data) {
                if( data >= 0 ) {
                    //console.debug("Delete Success, data="+data);
                    setDataCell(row,col,null);
                } else {
                    console.debug("Delete with data Error: data="+data);
                }
            }).error( function ( x, t, m ) {
                console.log('ERROR delete Accession Number');
            });
            break;
        case 'ID':
            //don't clean id
        default:
            setDataCell(row,col,null);
    }

}

function validateCell( row, col, value, mark ) {

    if( _sotable == null ) {
        _sotable = $(_htableid).handsontable('getInstance');
    }

    //console.log( 'validate: '+_sotable.countRows()+':'+row+','+col+' value=' + value );

    var valid = true;

    if( _sotable.countRows() == (row+1) ) {
        //console.log( _sotable.countRows()+' => dont validate row=' + row );
        return valid;
    }

    var columnHeader = _columnData_scanorder[col].header;

    if( value === undefined || value === null ) {
        value = $(_htableid).handsontable('getData')[row][col];
    }

    switch( columnHeader )
    {
        case 'MRN':
            if( !value || value == '' || value == null ) {
                //console.log('Accession Number: value null !!!!!!!!!!!!!');
                valid = false;
            }
            break;
        case 'Accession Number':
            if( !value || value == '' || value == null ) {
                //console.log('Accession Number: value null !!!!!!!!!!!!!');
                valid = false;
            }
            break;
        case 'Part Name':
            if( !value || value == '' || value == null ) {
                //console.log('Part Name: value null !!!!!!!!!!!!!');
                valid = false;

            }
            break;
        case 'Block Name':
            if( !value || value == '' || value == null ) {
                //console.log('Block Name: value null !!!!!!!!!!!!!');
                valid = false;
            }
            break;
        default:

    }

    if( mark ) {
        var checkcell = _sotable.getCell(row,col);

        if( checkcell && !valid ) {
            checkcell.style.backgroundColor = '#F2DEDE';
        }
    }

    return valid;
}

//testing
function negativeValueRenderer(instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    if( parseInt(value, 10) < 0 ) { //if row contains negative number
        td.className = 'negative'; //add class "negative"
    }

    if (!value || value === '') {
        td.style.background = '#F2DEDE';
    }
    else {
        if (value === 'Nissan') {
            td.style.fontStyle = 'italic';
        }
        td.style.background = '';
    }
}

//renderers
var redRendererForKey = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);

    if( !validateCell(row, col, value, null)  ) {
        $(td).css({
            background: '#F2DEDE'
        });
    } else {
        $(td).css({
            background: null
        });
    }

};
var redRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
//    $(td).css({
//        background: '#F2DEDE'
//    });
    $(td).addClass('ht-validation-error');
};
var redRendererAutocomplete = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);
    $(td).css({
        background: '#F2DEDE'
    });
    $(td).addClass('ht-validation-error');
};

//not used renderers
var minWidthRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);
    var width = (value.clientWidth + 1) + "px";
    $(td).css({
        colWidths : width
    });
};
var yellowRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);
    $(td).css({
        background: 'yellow'
    });
};
var redRendererIfEmpty = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    if( !value || value == '' || value == null ) {
        $(td).css({
            background: '#F2DEDE'
        });
    } else {
        $(td).css({
            background: null
        });
    }
};





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
