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
 * Created by oli2002 on 6/26/14.
 */

var _htableid = "#multi-dataTable";
var _sotable = null;    //scan order table
var _tableMainIndexes = null; //table indexes for main columns: Acc Type, Acc, MRN Type, MRN, Part ID, Block ID

var _accessiontypes_simple = [];
var _mrntypes_simple = [];
var _partname_simple = [];
var _blockname_simple = [];
var _stains_simple = [];

var _errorValidatorRows = []; //keep rows with validator error

var _rowToProcessArr = [];
var _processedRowCount = 0;
var _mrnAccessionArr = [];

var _mrnAccConflictRowArr = [];

var _tableValidated = false;

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

var conflictBorderRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    $(td).addClass('ht-conflictborder-error');
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

//noaccession-provided and nomrn-provided non zeros characters not limited
var generated_validator = function (value) {
    if( isValueEmpty(value) ) {
        return true;
    }
    var notzeros = notAllZeros(value);
    var res = value.match(/^[a-zA-Z1-9][a-zA-Z0-9-]{1,}$/);
    //console.log('general validator: res='+res+', notzeros='+notzeros);
    if( res != null && notzeros ) {
        return true;
    }
    else {
        return false;
    }
};
var generated_validator_fn = function (value, callback) {
    callback( generated_validator(value) );
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

var conflictRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    $(td).addClass('ht-conflict-error');
};

var redWithBorderRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    if( !validateCell(row,col,null) ) {
        $(td).addClass('ht-redwithconflictborder-error');
    } else {
        $(td).addClass('ht-conflictborder-error');
    }
};

var _columnData_scanorder = [

    { header:'Accession Type', default:0, columns:{type:'autocomplete', source:_accessiontypes_simple, strict:false, filter:false, renderer:redRendererAutocomplete} },
    { header:'Accession Number', columns:{validator: accession_validator_fn, allowInvalid: true, renderer:redRenderer} },

    { header:'Part', default:0, columns:{type:'autocomplete', source:_partname_simple, strict:true, filter:false, renderer:redRendererAutocomplete} },
    { header:'Block', default:0, columns:{type:'autocomplete', source:_blockname_simple, strict:true, filter:false, renderer:redRendererAutocomplete} },
    { header:'Stain', columns:{type:'autocomplete', source:_stains_simple, strict:false, filter:false, colWidths:'120px'} },

    { header:'MRN Type', default:0, columns:{type:'autocomplete', source:_mrntypes_simple, strict:false, filter:false, renderer:redRendererAutocomplete} },
    { header:"Patient's MRN", columns:{colWidths:'100px', renderer:redRenderer, validator: general_validator_fn, allowInvalid: true} },

    { header:"Patient's First Name", columns:{} },
    { header:"Patient's Middle Name", columns:{} },
    { header:"Patient's Last Name", columns:{} }

];

$(document).ready(function() {
    
    setNavBar("scan");
    $(".element-with-tooltip").tooltip();
    //attachResearchEducationalTooltip();

    getComboboxMrnType(null,true);
    getComboboxAccessionType(null,true);
    getComboboxPartname(null,true);
    getComboboxBlockname(null,true);
    getComboboxStain(null,true);

    $(function(){
        var datepicker = $.fn.datepicker.noConflict;
        $.fn.bootstrapDP = datepicker;
        $('#priority_option').find('.datepicker').bootstrapDP();
    });

    //Handsontable.renderers.registerRenderer('redRenderer', redRenderer); //maps function to lookup string

    // Wait until idle (busy must be false)
    var _TIMEOUT = 300; // waitfor test rate [msec]

    //console.log('before wait for');
    waitfor( ajaxFinishedCondition, true, _TIMEOUT, 0, 'play->busy false', function() {
        //console.log('The show can resume !');
        handsonTableInit();
    });

    //validation on form submit
    $("#table-slidereturnrequests").on("submit", function () {
        return validateHandsonTable();
    });

});


function handsonTableInit() {

    //console.log('init table for slide request');

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

                //capitalize first two chars for Accession Number, when accession type 'NYH LIS Anatomic Pathology Accession Number' is set
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

function processKeyTypes( row, col, value, oldvalue ) {

    if( value == oldvalue )
        return;

    var columnHeader = _columnData_scanorder[col].header;

    switch( columnHeader )
    {
        case 'MRN Type':


            break;
        case 'Accession Type':

            ////////////// set validator ///////////////
            if( isValueEmpty(value) ) {
                break;
            }
            var accNum = _sotable.getDataAtCell(row,col+1);
            if( value == 'NYH CoPath Anatomic Pathology Accession Number' ) {
                _sotable.getCellMeta(row,col+1).validator = accession_validator_fn;
                _sotable.getCellMeta(row,col+1).valid = accession_validator(accNum);
            }
            else if( value == 'Auto-generated Accession Number' || value == 'Existing Auto-generated Accession Number' ) {
                _sotable.getCellMeta(row,col+1).validator = generated_validator_fn;
                _sotable.getCellMeta(row,col+1).valid = generated_validator(accNum);
            }
            else {
                _sotable.getCellMeta(row,col+1).validator = general_validator_fn;
                _sotable.getCellMeta(row,col+1).valid = general_validator(accNum);
            }
            ////////////// EOF set validator ///////////////

            break;
        default:
        //
    }
    return;
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
        case 'Part ID':
        case 'Block ID':
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

function notAllZeros(value) {
    if( isValueEmpty(value) ) {
        return true;
    }

    var allzeros = value.match(/^[0]+$/);
    //console.log('allzeros='+allzeros);
    if( allzeros != null ) {
        return false;
    }
    else {
        return true;
    }
};

function cleanErrorTable() {
    _mrnAccessionArr.length = 0;
    _processedRowCount = 0;
    _rowToProcessArr.length = 0;
    _mrnAccConflictRowArr.length = 0;
    $('.tablerowerror-added').remove();
    var rowsCount = _sotable.countRows();
    for( var row=0; row<rowsCount; row++ ) {  //foreach row
        setErrorToRow(row,conflictRenderer,false);
    }
    _sotable.render();
}

function setErrorToRow(row,type,setError) {
    var headers = _sotable.getColHeader();
    for( var col=0; col< headers.length; col++ ) {  //foreach column
        if( setError ) {
            _sotable.getCellMeta(row,col).renderer = type;  //conflictRenderer;
        } else {
            _sotable.getCellMeta(row,col).renderer = _columnData_scanorder[col].columns.renderer;
        }
    }
    _sotable.render();
}

function ajaxFinishedCondition() {

    //console.log('ajax finished condition check');

    //console.log('_accessiontype.length='+_accessiontype.length);
//    console.log('_mrntype.length='+_mrntype.length);
//    console.log('_partname.length='+_partname.length);
//    console.log('_blockname.length='+_blockname.length);
//    console.log('_stain.length='+_stain.length);

    if(
            _accessiontype.length > 0 &&
            _mrntype.length > 0 &&
            _partname.length > 0 &&
            _blockname.length > 0 &&
            _stain.length > 0
    ) {

        for(var i = 0; i < _accessiontype.length; i++) {
            //console.log("acctype="+_accessiontype[i].text);
            if( _accessiontype[i].text != 'Existing Auto-generated Accession Number' ) {
                _accessiontypes_simple.push( _accessiontype[i].text );
            }
        }

        for(var i = 0; i < _mrntype.length; i++) {
            if( _mrntype[i].text != 'Existing Auto-generated MRN' ) {
                _mrntypes_simple.push( _mrntype[i].text );
            }
        }

        _partname_simple.push('');  //insert first empty value
        for(var i = 0; i < _partname.length; i++) {
            _partname_simple.push( _partname[i].text );
        }

        _blockname_simple.push(''); //insert first empty value
        for(var i = 0; i < _blockname.length; i++) {
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

////**********************************************************************
//// function waitfor - Wait until a condition is met
////
//// Needed parameters:
////    test: function that returns a value
////    expectedValue: the value of the test function we are waiting for
////    msec: delay between the calls to test
////    callback: function to execute when the condition is met
//// Parameters for debugging:
////    count: used to count the loops
////    source: a string to specify an ID, a message, etc
////**********************************************************************
//function waitfor(test, expectedValue, msec, count, source, callback) {
//    //console.log('waitfor, count='+count);
//
//    if( count > 200 ) {
//        //console.log('waitfor, exit on count');
//        return;
//    }
//
//    // Check if condition met. If not, re-check later (msec).
//    while( test() !== expectedValue ) {
//        count++;
//        setTimeout(function() {
//            waitfor(test, expectedValue, msec, count, source, callback);
//        }, msec);
//        return;
//    }
//    // Condition finally met. callback() can be executed.
//    //console.log(source + ': ' + test() + ', expected: ' + expectedValue + ', ' + count + ' loops.');
//    callback();
//}


//////////////////// validation /////////////////////
//Validation should only check that each edited line has at least an accession number.
function validateHandsonTable() {

    if( _tableValidated === true ) {
        return true;
    }

    $('#tableview-submit-btn').button('loading');

    //set main indexes for the column such as Acc Type, Acc Number ...
    _tableMainIndexes = getTableDataIndexes();

    //clean all previous error wells
    cleanErrorTable();


    /////////// 1) Check cell validator ///////////
    //var errCells = $('.htInvalid').length;
    var errCells = 0;
    //console.log('_errorValidatorRows.length='+_errorValidatorRows.length);
    //console.log(_errorValidatorRows);

    //don't check cell validator if row is empty
    for( var i=0; i<_errorValidatorRows.length; i++) {
        var row = _errorValidatorRows[i];
        if( _sotable.isEmptyRow(row) ) {                        //no error row
            //console.log(row+": empty row!");
        } else {                                                //error row
            //console.log(row+": not empty row");
            setErrorToRow(row,conflictBorderRenderer,true);
            errCells++;
        }
    }

    //console.log('errCells='+errCells);
    if( errCells > 0 ) {
        var errmsg =    "Please review the cell(s) marked bright red in the highlighted row(s), and correct the entered values to match the expected format. " +
            "For CoPath Accession Numbers the acceptable examples are S14-1 or SC14-100001 " +
            "(must have a dash with no leading zeros after the dash such as S14-01; must start " +
            "with either one or two letters followed by two digits; maximum number of characters is 11; " +
            "must contain only letters or digits and one dash). All other Accession and MRN Types must have the " +
            "maximum of 25 characters; must not start with one or more consequtive zeros; must be made up of letters, " +
            "numbers and possibly a dash; the first and last character must be either digits or letters (not a dash). " +
            "Example of an acceptable character string: DC-100000000211";

        var errorHtml = createTableErrorWell(errmsg);
        $('#validationerror').append(errorHtml);
        $('#tableview-submit-btn').button('reset');
        return false;
    }
    /////////// EOF Check cell validation ///////////


    /////////// 2) Empty main cells validation ///////////
    var countRow = _sotable.countRows();
    var nonEmptyRows = 0;
    for( var row=0; row<countRow-1; row++ ) { //for each row (except the last one)
        if( !validateEmptyHandsonRow(row) ) {
            setSpecialErrorToRow(row);
            nonEmptyRows++;
        }
    } //for each row

    if( nonEmptyRows > 0 ) {
        var errmsg = "Please review the cell(s) marked light red in the highlighted row(s) and enter the missing required information.<br>" +
            "For every slide you are submitting please make sure there are no empty fields marked light red in the row that describes it.<br>" +
            "Your order form must contain at least one row with the filled required fields describing a single slide.<br>" +
            "If you have accidentally modified the contents of an irrelevant row, please either delete the row via a right-click menu or empty its cells.<br>";

        var errorHtml = createTableErrorWell(errmsg);
        $('#validationerror').append(errorHtml);
        $('#tableview-submit-btn').button('reset');
        return false;
    }


    //console.log("All rows processed!!!!!!!!!!!");
    $('#tableview-submit-btn').button('reset');

    if( _rowToProcessArr.length == 0 ) {
        var errorHtml = createTableErrorWell('No data to submit. All rows are empty or in the default state.');
        $('#validationerror').append(errorHtml);
        $('#tableview-submit-btn').button('reset');
        return false;
    }

    if( $('.tablerowerror-added').length == 0 ) {

        //console.log("Submit form!!!!!!!!!!!!!!!");

        //get rows data from _rowToProcessArr
        assignDataToDatalocker();

        _tableValidated = true;

        //return false; //testing
        $('#table-slidereturnrequests').submit();
    }

    return false;
}

//get rows data from _rowToProcessArr and assign this to datalocker field
function assignDataToDatalocker() {
    //get rows data from _rowToProcessArr
    var data = new Array();
    data.push(_sotable.getColHeader());
    for( var i=0; i<_rowToProcessArr.length; i++ ) {
        //console.log("data row="+_rowToProcessArr[i]);
        data.push( _sotable.getDataAtRow( _rowToProcessArr[i] ) );
    }
    //console.log(data);

    //provide table data to controller
    //http://itanex.blogspot.com/2013/05/saving-handsontable-data.html
    var jsonstr = JSON.stringify(data);
    //console.log("jsonstr="+jsonstr);
    //$("#oleg_orderformbundle_slidereturnrequesttype_datalocker").val( jsonstr );
    $('.slidereturnrequest-datalocker-field').val( jsonstr );
}

function validateEmptyHandsonRow( row ) {
    var dataRow = _sotable.getDataAtRow(row);
    var accType = dataRow[_tableMainIndexes.acctype];
    var acc = dataRow[_tableMainIndexes.acc];
    //console.log('row:'+row+': accType='+accType+', acc='+acc+' <= accTypeIndex='+_tableMainIndexes.acctype+', accIndex='+_tableMainIndexes.acc);

    //don't validate the untouched OR is empty rows
    if( exceptionRow(row) ) {
        return true;
    } else {
        _rowToProcessArr.push(row); //count rows to process. Later we will need it to check if all rows were processed by ajax
    }

    if( isValueEmpty(accType) || isValueEmpty(acc) ) {
        return false;
    }

    return true;
}

//check if the row is untouched (default) OR is empty
//return true if row was untouched or empty; return false if row was modified
function exceptionRow( row ) {

    //if row is empty
    if( _sotable.isEmptyRow(row) ) {
        //console.log("empty row!");
        return true;
    }

    //if columns have default state
    var headers = _sotable.getColHeader();
    for( var col=0; col<headers.length; col++ ) {
        var val = _sotable.getDataAtCell(row,col);
        var defVal = null;
        if( 'default' in _columnData_scanorder[col] ) {
            var index = _columnData_scanorder[col]['default'];
            defVal = _columnData_scanorder[col]['columns']['source'][index];
            //console.log(col+": "+val +"!="+ defVal);
            if( val != defVal ) {
                //console.log(col+": "+'no default!!!!!!!!!!!!!!');
                return false;
            }
        } else {
            //console.log(col+": "+"no default (val should be empty), val="+val);
            if( !isValueEmpty(val) ) {
                //console.log(col+": "+'no empty!!!!!!!!!!!!!!');
                return false;
            }
        }
    }

    return true;
}

function getTableDataIndexes() {
    var res = new Array();
    for( var i=0; i<_columnData_scanorder.length; i++ ) {
        var columnHeader = _columnData_scanorder[i].header;
        switch( columnHeader )
        {
            case 'MRN Type':
                res['mrntype'] = i;
                break;
            case 'MRN':
                res['mrn'] = i;
                break;
            case 'Accession Type':
                res['acctype'] = i;
                break;
            case 'Accession Number':
                res['acc'] = i;
                break;
            case 'Part ID':
                res['part'] = i;
                break;
            case 'Block ID':
                res['block'] = i;
                break;
            case 'Patient DOB':
                res['dob'] = i;
                break;
            default:
        }
    }
    return res;
}

function createTableErrorWell(errtext) {
    if( !errtext || errtext == '') {
        errtext = 'Please make sure that all fields in the table form are valid';
    }

    var errorHtml =
        '<div class="tablerowerror-added alert alert-danger">' +
            errtext +
            '</div>';

    return errorHtml;
}

function setSpecialErrorToRow(row) {
    var headers = _sotable.getColHeader();
    for( var col=0; col< headers.length; col++ ) {  //foreach column
        _sotable.getCellMeta(row,col).renderer = redWithBorderRenderer;
    }
    _sotable.render();
}

//return true if modified
function checkIfTableWasModified() {

    var modified = false;

    if( !_sotable || typeof _sotable === 'undefined' ) {
        return modified;
    }

    var countRow = _sotable.countRows();
    //console.log( 'countRow=' + countRow );

    for( var row=0; row<countRow-1; row++ ) { //for each row (except the last one)
        if( exceptionRow(row) === false ) {
            modified = true;
            break;
        }
    }

    //console.log( 'modified=' + modified );
    return modified;
}
