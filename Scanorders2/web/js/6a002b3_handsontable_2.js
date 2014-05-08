/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/12/14
 * Time: 3:36 PM
 * To change this template use File | Settings | File Templates.
 */

var _htableid = "#multi-dataTable";

var _sotable = null;    //scan order table
var _tableMainIndexes = null; //table indexes for main columns: Acc Type, Acc, MRN Type, MRN, Part Name, Block Name

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

var _errorValidatorRows = new Array(); //keep rows with validator error

//var ip_validator_regexp = /^(?:\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b|null)$/;

//accession validator
var accession_validator = function (value) {
    if( isValueEmpty(value) ) {
        return true;
    }
    var notzeros = notAllZeros(value);
    var res = value.match(/^[a-zA-Z]{1,2}[0-9]{1,2}[-][1-9]{1}[0-9]{0,5}$/);      //S11-1, SS11-1, S1-10, not S11-01
    //console.log('acc validator: res='+res+', notzeros='+notzeros);
    if( res && notzeros ) {
        return true;
    }
    else {
        return false;
    }
}
var accession_validator_fn = function (value, callback) {
    callback( accession_validator(value) );
};
////////////////////////////

//25 non zeros characters
var general_validator = function (value) {
    if( isValueEmpty(value) ) {
        return true;
    }
    var notzeros = notAllZeros(value);
    var res = value.match(/^[a-zA-Z1-9][a-zA-Z0-9-]{0,23}[a-zA-Z0-9]{0,1}$/);
    //console.log('general validator: res='+res+', notzeros='+notzeros);
    if( res && notzeros ) {
        return true;
    }
    else {
        return false;
    }
};
var general_validator_fn = function (value, callback) {
    callback( general_validator(value) );
};
////////////////////////////

//noaccession-provided and nomrn-provided non zeros characters not limited
var generated_validator = function (value) {
    if( isValueEmpty(value) ) {
        return true;
    }
    var notzeros = notAllZeros(value);
    var res = value.match(/^[a-zA-Z1-9][a-zA-Z0-9-]{1,}$/);
    //console.log('general validator: res='+res+', notzeros='+notzeros);
    if( res && notzeros ) {
        return true;
    }
    else {
        return false;
    }
};
var generated_validator_fn = function (value, callback) {
    callback( generated_validator(value) );
};
////////////////////////////

//mm/dd/yyyy
var date_validator = function (value) {
    //console.log('value=('+value+')');
    if( isValueEmpty(value) ) {
        return true;
    }
    var res = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    var notzeros = notAllZeros(value);
    //console.log('date validator: res='+res+', notzeros='+notzeros);
    if( res && notzeros ) {
        //console.log('date2 ok');
        return true;
    }
    else {
        //console.log('date not ok');
        return false;
    }
};
var date_validator_fn = function (value, callback) {
    callback( date_validator(value) );
};
//////////////////////

function notAllZeros(value) {
    if( isValueEmpty(value) ) {
        return true;
    }

    var allzeros = value.match(/^[0]+$/);
    //console.log('allzeros='+allzeros);
    if( allzeros ) {
        return false;
    }
    else {
        return true;
    }
};


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

var forceRedRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    $(td).addClass('ht-validation-error');
};

var conflictRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    $(td).addClass('ht-conflict-error');
};

var conflictBorderRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    $(td).addClass('ht-conflictborder-error');
};

var redWithBorderRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);
    if( !validateCell(row,col,null) ) {
        $(td).addClass('ht-redwithconflictborder-error');
    } else {
        $(td).addClass('ht-conflictborder-error');
    }
};

//total 31
var _columnData_scanorder = [

    //header: 1
    //{ header:'ID', columns:{} },

    //accession: 2
    { header:'Accession Type', default:0, columns:{type:'autocomplete', source:_accessiontypes_simple, strict:false, filter:false, renderer:redRendererAutocomplete} },
    { header:'Accession Number', columns:{validator: accession_validator_fn, allowInvalid: true, renderer:redRenderer} },

    //part: 1
    { header:'Part Name', default:0, columns:{type:'autocomplete', source:_partname_simple, strict:true, filter:false, renderer:redRendererAutocomplete} },

    //block: 1
    { header:'Block Name', default:0, columns:{type:'autocomplete', source:_blockname_simple, strict:true, filter:false, renderer:redRendererAutocomplete} },

    //slide: 4
    { header:'Stain', default:0, columns:{type:'autocomplete', source:_stains_simple, strict:false, filter:false, colWidths:'120px'} },
    { header:'Scan Magnificaiton', default:0, columns:{type:'dropdown', source:['20X','40X'], strict:false} },
    { header:'Diagnosis', columns:{} },
    { header:'Reason for Scan/Note', columns:{} },

    //patient: 7
    { header:'MRN Type', default:0, columns:{type:'autocomplete', source:_mrntypes_simple, strict:false, filter:false, renderer:redRendererAutocomplete} },
    { header:'MRN', columns:{colWidths:'100px', renderer:redRenderer, validator: general_validator_fn} },
    { header:'Patient Name', columns:{} },
    { header:'Patient Sex', default:0, columns:{type:'dropdown', source:['', 'Female','Male','Unspecified'], strict:true} },
    { header:'Patient DOB', columns:{type:'date', dateFormat: 'mm/dd/yy', validator: date_validator_fn, allowInvalid: true } },
    { header:'Patient Age', columns:{} },
    { header:'Clinical History', columns:{} },

    //procedure: 1
    { header:'Procedure Type', default:0, columns:{type:'dropdown', source:_procedures_simple, strict:true} },

    //part: 6
    { header:'Source Organ', columns:{type:'autocomplete', source:_organs_simple, strict:false, filter:false, colWidths:'100px'} },
    { header:'Gross Description', columns:{} },
    { header:'Differential Diagnoses', columns:{} },
    { header:'Type of Disease', default:0, columns:{type:'dropdown', source:['','Neoplastic','Non-Neoplastic','None','Unspecified'], strict:true} },
    { header:'Origin of Disease', default:0, columns:{type:'dropdown', source:['','Primary','Metastatic','Unspecified'], strict:true, colWidths:'100px'} },
    { header:'Primary Site of Disease Origin', columns:{type:'autocomplete', source:_organs_simple, strict:false, filter:false} },

    //block: 1
    { header:'Block Section Source', columns:{} },

    //slide: 7
    { header:'Slide Title', columns:{} },
    { header:'Slide Type', default:0, columns:{type:'autocomplete', source:_slidetypes_simple, strict:false, filter:false} },
    { header:'Microscopic Description', columns:{} },
    { header:'Special Stain', columns:{type:'autocomplete', source:_stains_simple, strict:false, filter:false, colWidths:'120px'} },
    { header:'Results of Special Stains', columns:{} },
    { header:'Link(s) to related image(s)', columns:{} },
    { header:'Region to Scan', default:0, columns:{type:'autocomplete', source:_scanregions_simple, strict:false, filter:false} }

];

$(document).ready(function() {

    //Handsontable.renderers.registerRenderer('redRenderer', redRenderer); //maps function to lookup string

    getSlideTypes();

    // Wait until idle (busy must be false)
    var _TIMEOUT = 300; // waitfor test rate [msec]
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

        _procedures_simple.push('');
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

    //console.log(columnsType);
    //$('#multi-dataTable').doubleScroll();

    //console.log(data);
    //console.log(colHeader);
    //console.log(columnsType);

//    Handsontable.renderers.registerRenderer('negativeValueRenderer', negativeValueRenderer); //maps function to lookup string


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

//function capitalizeAccession( row, col, value ) {
//
//    if( !value || value == '' ) {
//        return;
//    }
//
//    //console.log('capitalize ' +row+','+col+':value='+value);
//    var columnHeader = _columnData_scanorder[col].header;
//    if( columnHeader == 'Accession Number' ) {
//
//        var upperCaseValue = value.slice(0,1).toUpperCase + value.slice(2);
//        _sotable.setDataAtCell(row,col,upperCaseValue);
//
////        if( value.match(/^[A-Z]/) ) {
////            var upperCaseValue = value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
////            _sotable.setDataAtCell(row,col,upperCaseValue);
////        }
////        if( value.match(/^[A-Z][A-Z]/) && !value.match(/^[A-Z][0-9]/) ) {
////            //var upperCaseValue = value.replace(/^[a-z]{1,2}/, function(m){ return m.toUpperCase() });
////            var upperCaseValue = value.charAt(0).toUpperCase() + value.charAt(1).toUpperCase() + value.slice(2).toLowerCase();
////            _sotable.setDataAtCell(row,col,upperCaseValue);
////        }
//    }
//    return;
//}

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

            ////////////// set validator ///////////////
            //if( !value || value == '' ) {
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

function setDataCell( row, col, value ) {

    //if( value && value != '' ) {    //set
    if( !isValueEmpty(value) ) {

        _sotable.setDataAtCell(row,col,value);
        _sotable.getCellMeta(row,col).readOnly = true;

    } else {    //clean

        var newValue = null;

        //if default exists => set to the element of source array, at the index specified by default
        if( 'default' in _columnData_scanorder[col] ) {
            var index = _columnData_scanorder[col]['default'];
            var newValue = _columnData_scanorder[col]['columns']['source'][index];
        }

        //console.log('clean data cell at '+row+","+col+", value="+newValue);

        _sotable.setDataAtCell(row,col,newValue);
        _sotable.getCellMeta(row,col).readOnly = false;

    }

}

//clean form
function processDataForm( action ) {

    var handsontable = $(_htableid).data('handsontable');

    var hdata = handsontable.getData();

    //console.log('data len='+hdata.length);
    //console.log( 'column'+'0'+',row'+'1'+':'+ hdata[0][1] );
    //console.log( 'column'+'1'+',row'+'2'+':'+ hdata[1][2] );

    //for each row (except the first one)
    for( var i=0; i<hdata.length; i++ ) {

        //console.log( 'row'+(i+1)+':' + hdata[i] );
        if( hdata[i] !== undefined && hdata[i] !== null && hdata[i] != '' ) {

            //for each column (except the first one)
            for( var ii=0; ii<hdata[i].length; ii++ ) {

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

    //if( !force && (value === undefined || value === null || value == '') ) {
    if( !force && isValueEmpty(value) ) {
        return; //don't clean empty cells
    }

    if( _sotable && _sotable.countRows() == (row+1) ) {
        return; //don't clean the last row (it is empty)
    }

    //console.log('clean: row='+row+', col='+col+', value='+value);

    var columnHeader = _columnData_scanorder[col].header;

    switch( columnHeader )
    {
        case 'MRN':
            //console.log('delete value='+value);
            if( _sotable.getDataAtCell(row,col-1) == 'Auto-generated MRN' || _sotable.getDataAtCell(row,col-1) == 'Existing Auto-generated MRN' ) {
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
            } else  {
                setDataCell(row,col,null);
            }
            break;
        case 'Accession Number':
            //console.log('Accession => value='+value);
            if( _sotable.getDataAtCell(row,col-1) == 'Auto-generated Accession Number' || _sotable.getDataAtCell(row,col-1) == 'Existing Auto-generated Accession Number' ) {
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
            } else {
                setDataCell(row,col,null);
            }

            break;
        case 'ID':
            //don't clean id
        default:
            setDataCell(row,col,null);
    }

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

//    if( mark ) {
//        var checkcell = _sotable.getCell(row,col);
//
//        if( checkcell && !valid ) {
//            checkcell.style.backgroundColor = '#F2DEDE';
//        }
//    }

    return valid;
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
