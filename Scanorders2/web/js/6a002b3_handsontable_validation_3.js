/**
 * Created by oli2002 on 5/6/14.
 */

var _processedRowCount = 0;
var _mrnAccessionArr = new Array();

//1) check if cell validators are ok
//2) check for key empty cells
//3) check if previously generated keys are exists in DB (ajax)
//4) check for MRN-Accession conflicts
function validateHandsonTable() {

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
        var errmsg = "Please review the cells marked bright red, in the highlight row(s), and correct the entered values to match the expected format. The expected formats are as follows:<br>"+ //"&#13;&#10;"+"<br>"+
            "CoPath Accession Number Example: S14-1 or SC14-1 (no leading zeros after the dash such as S14-01)<br>" +
            "All other Accession and MRN Types: max of 25 characters; non leading zero and special characters; non all zeros; non last special character. Example: SC100000000211";
        var errorHtml = createTableErrorWell(errmsg);
        //var errorHtml = createTableErrorWell('Please make sure that all cells in the table form are valid. Number of error cells:'+errCells +'. Error cells are marked with red.');
        $('#validationerror').append(errorHtml);
        $('#tableview-submit-btn').button('reset');
        return;
    }
    /////////// EOF Check cell validation ///////////


    var countRow = _sotable.countRows();
    //console.log( 'countRow=' + countRow );

    /////////// 2) Empty main cells validation ///////////
    var emptyRows = 0;
    for( var row=0; row<countRow-1; row++ ) { //for each row (except the last one)
        if( !validateEmptyHandsonRow(row) ) {
            setSpecialErrorToRow(row);
            emptyRows++;
        }
    } //for each row
    if( emptyRows > 0 ) {
        var errmsg = "Please review the cells marked light red and enter the missing information. " +
            "For every slide you are submitting, please make sure there are no empty fields marked light red in the row that describes it. " +
            "Your order form must contain at least one row with the filled required fields describing a single slide. " +
            "If you accidentally modified the contents of an irrelevant row, please either delete the row via a right-click menu or empty its cells."
        var errorHtml = createTableErrorWell(errmsg);
        //var errorHtml = createTableErrorWell('Please make sure that all fields in the table form are valid. Number of error rows:'+emptyRows+'. Empty cells are marked with red.');
        $('#validationerror').append(errorHtml);
        $('#tableview-submit-btn').button('reset');
        return;
    }
    /////////// EOF Empty main cells validation ///////////

    /////////// 3) Check existing keytypes and MRN-Accession conflicts //////////////
    for( var i=0; i<countRow-1; i++ ) { //for each row (except the last one)
        checkPrevGenAndConflictTable(i);
    }
    /////////// EOF Check existing keytypes and MRN-Accession conflicts //////////////

    //submit if no errors for all rows
    // Wait until idle (busy must be false)
    var _TIMEOUT = 300; // waitfor test rate [msec]
    waitfor( allRowProcessed, true, _TIMEOUT, 0, 'play->busy false', function() {
        if( $('.tablerowerror-added').length == 0 ) {
            submitTableScanOrder();
        }
    });

}

function submitTableScanOrder() {
    //getData (row: Number, col: Number, row2: Number, col2: Number)
    //var data = _sotable.getData( 0, 0, _sotable.countRows()-2, _sotable.countCols() );  //don't get data for the last row
    var data = _sotable.getData();  //don't get data for the last row
    console.log('######### submit data ##########:');
    console.log(data);

    var urlBase = $("#baseurl").val();
    var url = "http://"+urlBase+"/scan-order/multi-slide-table-view/submit";
    console.log('url='+url);

    data.unshift(_sotable.getColHeader());  //insert as the first element headers

    $.ajax({
        url: url,
        data: {"data": data}, //returns all cells' data
        dataType: 'json',
        type: 'POST',
        success: function (res) {
            if (res == 'ok') {
                console.log('Data saved');
            }
            else {
                console.log('Save error');
            }
        },
        error: function () {
            console.log('Save error.');
        }
    });

    return;
}

function allRowProcessed() {
    var countRow = _sotable.countRows();
    if( _processedRowCount == countRow-1 ) {
        return true;
    } else {
        return false;
    }

}


function checkPrevGenAndConflictTable(row) {

    var dataRow = _sotable.getDataAtRow(row);
    var accType = dataRow[_tableMainIndexes.acctype];
    var acc = dataRow[_tableMainIndexes.acc];
    var mrnType = dataRow[_tableMainIndexes.mrntype];
    var mrn = dataRow[_tableMainIndexes.mrn];

    if( isValueEmpty(accType) || isValueEmpty(acc) || isValueEmpty(mrnType) || isValueEmpty(mrn) ) {
        $('#tableview-submit-btn').button('reset');
        return false;
    }

    var accTypeCorrect = null;
    var mrnTypeCorrect = null;
    var mrnDB = null;
    var mrntypeDB = null;

    //get mrn keytype id
    getKeyTypeID('patient',mrnType).
    //check existing mrn keytype
    then(
        function(response) {
            mrnTypeCorrect = response;
            //console.log("Success!", response);
            return checkPrevGenKeyTable('patient',mrn,mrnType,mrnTypeCorrect,false);
        }
    ).
    //add error for mrn
    then(
        function(response) {
            //console.log("after mrn check PrevGenKeyTable:"+response);
            if( !response ) {
                var errmsg = "Please review the cells marked yellow and make sure the same accession number is always listed as belonging to the same patient MRN. " +
                    "The same accession number can not be tied to two different patients.";
                var errorHtml = createTableErrorWell(errmsg);
                //var errorHtml = createTableErrorWell('Previously auto-generated MRN Numbers is not correct. Please correct the auto-generated number. Cell with error is marked with red.');
                $('#validationerror').append(errorHtml);
                //_sotable.getCellMeta(row,mrnType).renderer = forceRedRenderer;
                _sotable.getCellMeta(row,_tableMainIndexes.mrn).renderer = forceRedRenderer;
                _sotable.render();
            }
        }
    ).
    //get acc keytype id
    then(
        function() {
            return getKeyTypeID('accession',accType);
        }
    ).
    //check existing acc keytype
    then(
        function(response) {
            accTypeCorrect = response;
            //console.log("Success!", response);
            return checkPrevGenKeyTable('accession',acc,accType,accTypeCorrect,true);   //true-force run check for accession. We need it for MRN-Acc conflict check
        }
    ).
    //add error for acc
    then(
        function(response) {
            //console.log("after acc check PrevGenKey Table:"+response);
            if( !response ) {
                var errorHtml = createTableErrorWell('Previously auto-generated Accession Numbers is not correct. Please correct the auto-generated number. Cell with error is marked with red.');
                $('#validationerror').append(errorHtml);
                _sotable.getCellMeta(row,_tableMainIndexes.acc).renderer = forceRedRenderer;
                _sotable.render();
            } else {
                if( response instanceof Array && "parentkeyvalue" in response ) {
                    //console.log("parentkeyvalue="+response['parentkeyvalue']);
                    mrnDB = response['parentkeyvalue'];
                    mrntypeDB = response['parentkeytype'];
                }
            }
        }
    ).
    //check internal conflict within the table
    then(
        function(response) {
            var errLen = $('.tablerowerror-added').length;
            if( errLen == 0 && mrnAccInternalConflict( acc, accType, mrn, mrnType ) ) {
                var errorHtml = createTableErrorWell('MRN - Accession Numbers internal conflict within the table. Rows with error cells are marked with yellow.');
                $('#validationerror').append(errorHtml);
                setErrorToRow(row,conflictRenderer,true);
            }
        }
    ).
    //check conflict with DB
    then(
        function(response) {
            var errLen = $('.tablerowerror-added').length;
            if( errLen == 0 && mrnAccConflict( mrnDB, mrn, mrntypeDB, mrnTypeCorrect ) ) {
                var errorHtml = createTableErrorWell('MRN - Accession Numbers conflict. Rows with error cells are marked with yellow.');
                $('#validationerror').append(errorHtml);
                setErrorToRow(row,conflictRenderer,true);
            }
        }
    ).
    then(
        function(response) {
            console.log("Chaining OK, response="+response);
            _processedRowCount++;
        },
        function(error) {
            console.error("Failed! error=", error);
        }
    ).
    done(
        function(response) {
            //console.log("Done ", response);
            $('#tableview-submit-btn').button('reset');
        }
    );


}

function cleanErrorTable() {
    _mrnAccessionArr.length = 0;
    _processedRowCount = 0;
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

function setSpecialErrorToRow(row) {
    var headers = _sotable.getColHeader();
    for( var col=0; col< headers.length; col++ ) {  //foreach column
        _sotable.getCellMeta(row,col).renderer = redWithBorderRenderer;
    }
    _sotable.render();
}


function mrnAccInternalConflict( acc, accType, mrn, mrnType ) {

    var conflict = false;

    if( _mrnAccessionArr.length > 0 ) {
        for( var i=0; i< _mrnAccessionArr.length; i++ ) {
            var accArr = _mrnAccessionArr[i].acc;
            var accTypeArr = _mrnAccessionArr[i].accType;
            var mrnArr = _mrnAccessionArr[i].mrn;
            var mrnTypeArr = _mrnAccessionArr[i].mrnType;
            if( acc == accArr && accType == accTypeArr ) {
                if( mrnAccConflict(mrnArr,mrn,mrnTypeArr,mrnType) ) {
                    //console.log('internal conflict detected');
                    setErrorToRow(i,conflictRenderer,true);
                    conflict = true;
                }
            }
        }
    }

    if( !conflict ) {
        var mrnacc = Array();
        mrnacc['acc'] = acc;
        mrnacc['accType'] = accType;
        mrnacc['mrn'] = mrn;
        mrnacc['mrnType'] = mrnType;
        _mrnAccessionArr.push(mrnacc);
    }

    return conflict;
}

function mrnAccConflict( mrnDB, mrn, mrntypeDB, mrnTypeCorrect ) {
    console.log("conflict:"+mrnDB + " " + mrn + " " + mrntypeDB + " " + mrnTypeCorrect);
    if( !mrnDB || !mrntypeDB ) {
        console.log("ERROR: DB's mrn and/or mrntype are null");
        return false;
    }
    mrnDB = trimWithCheck(mrnDB);
    mrn = trimWithCheck(mrn);
    mrntypeDB = trimWithCheck(mrntypeDB);
    mrnTypeCorrect = trimWithCheck(mrnTypeCorrect);
    if( mrnDB == mrn && mrntypeDB == mrnTypeCorrect ) {
        return false;
    } else {
        return true;
    }
}
//return true if ok, false if prev gen value is not found in DB
//force - if true then make ajax check even if there is no "Existing Auto-generated" type
function checkPrevGenKeyTable(name,keyvalue,keytype,keytypeCorrect,force) {

    return Q.promise(function(resolve, reject) {
        console.log(name+': keyvalue='+keyvalue+', keytype='+keytype);

        var makeCheck = true;

        if( keyvalue == '' || keytype == '' ) {
            console.log(name+": keytype or keyvalue are null");
            makeCheck = false;
        }

        if( !keytypeCorrect ) {
            console.log(name+": keytypeCorrect is null");
            makeCheck = false;
        }

        if( !force && name == 'accession' && keytype != 'Existing Auto-generated Accession Number' ) {
            console.log(name+": no check for prev gen is required");
            makeCheck = false;
        }

        if( !force && name == 'patient' && keytype != 'Existing Auto-generated MRN' ) {
            console.log(name+": no check for prev gen is required");
            makeCheck = false;
        }

        if( makeCheck ) {
            $.ajax({
                url: urlCheck+name+'/check',
                type: 'GET',
                data: {key: keyvalue, extra: keytypeCorrect},
                contentType: 'application/json',
                dataType: 'json',
                timeout: _ajaxTimeout,
                async: false,
                success: function (data) {
                    //console.debug("get element ajax ok");
                    if( data == -2 ) {
                        console.log("Existing Auto-generated object does not exist in DB for "+name);
                        resolve(false);
                    } else {
                        if( "extraid" in data ) {
                            var res = new Array();
                            res['parentkeyvalue'] = data['parent'];
                            res['parentkeytype'] = data['extraid'];
                            resolve(res);
                        } else {
                            resolve(true);
                        }
                    }
                },
                error: function ( x, t, m ) {
                    console.debug("validation: get object ajax error "+name);
                    if( t === "timeout" ) {
                        getAjaxTimeoutMsg();
                    }
                    reject(Error("Check Existing Error, name="+name));
                }
            });
        } else {
            resolve(true);
        }

    }); //promise
}
//return id of the keytype by keytype string
//if keytype does not exists in DB, return keytype string
function getKeyTypeID( name, keytype ) {
    return Q.promise(function(resolve, reject) {

        $.ajax({
            url: urlCheck+name+'/keytype/'+keytype,
            type: 'GET',
            //data: {keytype: keytype},
            contentType: 'application/json',
            dataType: 'json',
            timeout: _ajaxTimeout,
            async: false,
            success: function (data) {
                //console.debug("get element ajax ok");
                if( data && data != '' ) {
                    console.log(name+": keytype is found. keytype="+data);
                    resolve(data);
                } else {
                    console.log(name+": keytype is not found.");
                    resolve(keytype);
                }
            },
            error: function ( x, t, m ) {
                console.debug("keytype id: ajax error "+name);
                if( t === "timeout" ) {
                    getAjaxTimeoutMsg();
                }
                reject(Error("Check Existing Error"));
            }
        });
    }); //promise
}


function validateEmptyHandsonRow( row ) {
    var dataRow = _sotable.getDataAtRow(row);
    var accType = dataRow[_tableMainIndexes.acctype];
    var acc = dataRow[_tableMainIndexes.acc];
    var mrnType = dataRow[_tableMainIndexes.mrntype];
    var mrn = dataRow[_tableMainIndexes.mrn];
    var part = dataRow[_tableMainIndexes.part];
    var block = dataRow[_tableMainIndexes.block];
    //console.log('row:'+row+': accType='+accType+', acc='+acc+' <= accTypeIndex='+_tableMainIndexes.acctype+', accIndex='+_tableMainIndexes.acc);

    //don't validate the untouched OR is empty rows
    if( exceptionRow(row) ) {
        return true;
    }

    if( isValueEmpty(accType) || isValueEmpty(acc) || isValueEmpty(mrnType) || isValueEmpty(mrn) || isValueEmpty(part) || isValueEmpty(block) ) {
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
            case 'Part Name':
                res['part'] = i;
                break;
            case 'Block Name':
                res['block'] = i;
                break;
            default:
        }
    }
    return res;
}
