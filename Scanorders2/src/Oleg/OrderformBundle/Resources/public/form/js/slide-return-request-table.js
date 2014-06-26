/**
 * Created by oli2002 on 6/26/14.
 */

var _htableid = "#multi-dataTable";
var _sotable = null;    //scan order table
var _tableMainIndexes = null; //table indexes for main columns: Acc Type, Acc, MRN Type, MRN, Part Name, Block Name

var _accessiontypes_simple = new Array();
var _mrntypes_simple = new Array();
var _partname_simple = new Array();
var _blockname_simple = new Array();
var _stains_simple = new Array();

var _errorValidatorRows = new Array(); //keep rows with validator error


//renderers
var redRendererAutocomplete = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);
    if( !validateCell(row,col,null) ) {
        //console.log('add error');
        $(td).addClass('ht-validation-error');
    } else {
        //console.log('remove error');
        $(td).removeClass('ht-validation-error');
    }
};

var redRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    if( !validateCell(row,col,null) ) {
        $(td).addClass('ht-validation-error');
    } else {
        $(td).removeClass('ht-validation-error');
    }

    //capitalizeAccession( row, col, value );
};

//25 non zeros characters
var general_validator = function (value) {
    if( isValueEmpty(value) ) {
        return true;
    }
    var notzeros = notAllZeros(value);
    var res = value.match(/^[a-zA-Z1-9][a-zA-Z0-9-]{0,23}[a-zA-Z0-9]{0,1}$/);
    //console.log('general validator: res='+res+', notzeros='+notzeros);
    if( res != null && notzeros ) {
        return true;
    }
    else {
        return false;
    }
};
var general_validator_fn = function (value, callback) {
    callback( general_validator(value) );
};

//accession validator
var accession_validator = function (value) {
    //console.log('acc validator: value='+value);
    if( isValueEmpty(value) ) {
        //console.log('acc validator: empty => ret true');
        return true;
    }
    var notzeros = notAllZeros(value);
    var res = value.match(/^[a-zA-Z]{1,2}[0-9]{2}[-][1-9]{1}[0-9]{0,5}$/);      //S11-1, SS11-1, S1-10, not S11-01
    //console.log('acc validator: res='+res+', notzeros='+notzeros);
    if( res != null && notzeros ) {
        //console.log('acc validator: ret true');
        return true;
    }
    else {
        //console.log('acc validator: ret false');
        return false;
    }
}
var accession_validator_fn = function (value, callback) {
    callback( accession_validator(value) );
};

var _columnData_scanorder = [

    { header:'MRN Type', default:0, columns:{type:'autocomplete', source:_mrntypes_simple, strict:false, filter:false, renderer:redRendererAutocomplete} },
    { header:"Patient's MRN", columns:{colWidths:'100px', renderer:redRenderer, validator: general_validator_fn, allowInvalid: true} },

    { header:"Patient's Last Name", columns:{} },
    { header:"Patient's First Name", columns:{} },
    { header:"Patient's Middle Name", columns:{} },

    { header:'Accession Type', default:0, columns:{type:'autocomplete', source:_accessiontypes_simple, strict:false, filter:false, renderer:redRendererAutocomplete} },
    { header:'Accession Number', columns:{validator: accession_validator_fn, allowInvalid: true, renderer:redRenderer} },

    { header:'Part', default:0, columns:{type:'autocomplete', source:_partname_simple, strict:true, filter:false, renderer:redRendererAutocomplete} },

    { header:'Block', default:0, columns:{type:'autocomplete', source:_blockname_simple, strict:true, filter:false, renderer:redRendererAutocomplete} },

    { header:'Stain', default:0, columns:{type:'autocomplete', source:_stains_simple, strict:false, filter:false, colWidths:'120px'} },

];

$(document).ready(function() {

    attachResearchEducationalTooltip();

    $(function(){
        var datepicker = $.fn.datepicker.noConflict;
        $.fn.bootstrapDP = datepicker;
        $('#priority_option').find('.datepicker').bootstrapDP();
    });

    //Handsontable.renderers.registerRenderer('redRenderer', redRenderer); //maps function to lookup string

    // Wait until idle (busy must be false)
    var _TIMEOUT = 300; // waitfor test rate [msec]
    waitfor( ajaxFinishedCondition, true, _TIMEOUT, 0, 'play->busy false', function() {
        //console.log('The show can resume !');
        handsonTableInit();
    });

    //validation on form submit
    $("#table-scanorderform").on("submit", function () {
        return validateHandsonTable();
    });

});


function handsonTableInit() {

    var data = new Array();
    var columnsType = new Array();
    var colHeader = new Array();
    var rows = 11;//21;//501;

    // make init data, i=0 to skip the first row
    for( var i=1; i<rows; i++ ) {   //foreach row

        var rowElement = new Array();
        //rowElement[0] = i;
        for( var ii=0; ii<_columnData_scanorder.length; ii++ ) {  //foreach column

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

    $(_htableid).handsontable({
        data: data,
        colHeaders: colHeader,
        columns: columnsType,
        minSpareRows: 1,
        contextMenu: ['row_above', 'row_below', 'remove_row'],
        manualColumnMove: true,
        manualColumnResize: true,
        //autoWrapRow: true,
        currentRowClassName: 'currentRowScanorder',
        currentColClassName: 'currentColScanorder',
        stretchH: 'all',
        beforeChange: function (changes, source) {
            for( var i=0; i<changes.length; i++ ) {
//                //console.log(changes[i]);
                var row = changes[i][0];
                var col = changes[i][1];
                var oldvalue = changes[i][2];
                var value = changes[i][3];

                //capitalize first two chars for Accession Number, when accession type 'NYH CoPath Anatomic Pathology Accession Number' is set
                var columnHeader = _columnData_scanorder[col].header;
                if( columnHeader == 'Accession Number' && changes[i][3] && changes[i][3] != '' && changes[i][3].charAt(0) ) {
                    //changes[i][3] = changes[i][3].slice(0,1).toUpperCase() + changes[i][3].slice(2);
                    changes[i][3] = changes[i][3].charAt(0).toUpperCase() + changes[i][3].slice(1); //capitalise first letter
                    if( changes[i][3].charAt(1) ) {
                        changes[i][3] = changes[i][3].charAt(0) + changes[i][3].charAt(1).toUpperCase() + changes[i][3].slice(2); //capitalise second letter
                    }
                }

                processKeyTypes( row, col, value, oldvalue );
            }
            //clean any error wells to make validation again
            cleanErrorTable();  //clean tablerowerror-added: simple errors
            cleanValidationAlert(); //clean validationerror-added: MRN-ACC choice errors
        },
        afterCreateRow: function (index, amount) {
            return; //TODO: testing
            if( !_sotable || typeof _sotable === 'undefined' ) {
                return;
            }

            //pre-populate cells
            for( var col=0; col<_columnData_scanorder.length; col++ ) {  //foreach column
                var value = null;
                if( 'default' in _columnData_scanorder[col] ) {
                    var indexSource = _columnData_scanorder[col]['default'];
                    value = _columnData_scanorder[col]['columns']['source'][indexSource];
                }

                //var row = this.countRows()-2;
                //console.log('row='+row);
                //console.log('amount='+amount+', index='+index+',col='+col+', value='+value);
                this.setDataAtCell(index-1,col,value);

            }//foreach column

        },
        afterValidate: function(isValid, value, row, prop, source) {
            if( isValid ) { //remove row from array
                _errorValidatorRows = jQuery.grep(_errorValidatorRows, function(value) {
                    return value != row;
                });
            } else {    //add row to array
                _errorValidatorRows.push(row);
            }
        }
    });

    //set bs table
    //$(_htableid+' table').addClass('table-striped table-hover');
    $(_htableid+' table').addClass('table-hover');

    //set scan order table object as global reference
    _sotable = $(_htableid).handsontable('getInstance');

}

//the cell's value for Types, Acc, MRN, Part, Block should not be empty
function validateCell( row, col, value ) {

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

    //if( value === undefined || value === null ) {
    if( isValueEmpty(value) ) {
        value = $(_htableid).handsontable('getData')[row][col];
    }

    switch( columnHeader )
    {
        case 'Accession Type':
        case 'MRN Type':
        case 'MRN':
        case 'Accession Number':
        case 'Part Name':
        case 'Block Name':
            //if( !value || value == '' || value == null ) {
            if( isValueEmpty(value) ) {
                //console.log(columnHeader+': value null !!!!!!!!!!!!!');
                valid = false;
            }
            break;
        default:
    }

    return valid;
}

function ajaxFinishedCondition() {
    if(
            _accessiontype.length > 0 &&
            _mrntype.length > 0 &&
            _stain.length > 0 &&
            _procedure.length > 0
    ) {

        for(var i = 0; i < _accessiontype.length; i++) {
            _accessiontypes_simple.push( _accessiontype[i].text );
        }

        for(var i = 0; i < _mrntype.length; i++) {
            //console.log('mrntype='+ _mrntype[i].text);
            _mrntypes_simple.push( _mrntype[i].text );
        }

        _partname_simple.push('');  //insert first empty value
        for(var i = 0; i < _partname.length; i++) {
            //console.log('mrntype='+ _mrntype[i].text);
            _partname_simple.push( _partname[i].text );
        }

        _blockname_simple.push(''); //insert first empty value
        for(var i = 0; i < _blockname.length; i++) {
            //console.log('mrntype='+ _mrntype[i].text);
            _blockname_simple.push( _blockname[i].text );
        }

        for(var i = 0; i < _stain.length; i++) {
            _stains_simple.push( _stain[i].text );
        }

        return true;

    } else {

        return false;
    }
}

function isValueEmpty(value) {
    if( value && typeof value !== 'undefined' && value != '' ) {
        return false;
    } else {
        return true;
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

