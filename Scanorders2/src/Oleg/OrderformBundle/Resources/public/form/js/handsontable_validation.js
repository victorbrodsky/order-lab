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
 * Created by oli2002 on 5/6/14.
 */

var _rowToProcessArr = new Array();
var _processedRowCount = 0;
var _mrnAccessionArr = new Array();
var _mrnDobArr = new Array();

var _mrnAccConflictRowArr = new Array();

var _tableValidated = false;

//1) check if cell validators are ok
//2) check for key empty cells
//3) check if previously generated keys are exists in DB (ajax)
//4) check for MRN-Accession conflicts
function validateHandsonTable() {

    if( _tableValidated === true ) {
        return true;
    }

    //this is a case when submit is pressed with 3 options for each conflict row
    //check if conflict was handled by a choice, otherwise, do validation again.
    var conflictLen = _mrnAccConflictRowArr.length;
    //console.log('conflictLen='+conflictLen);
    if( conflictLen > 0 ) {
        var errLen = $('.tablerowerror-added').length;
        if( errLen == 0 && checkIfMrnAccConflictHandled() ) {
            //console.log('conflict was handled !!!!!!!!!!!!!!');
            assignDataToDatalocker();
            //return false; //testing
            return true;
        }
    }

    $('.tableview-submit-btn').button('loading');

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
                        "For Accession Numbers the acceptable examples are S14-1 or SC14-100001 " +
                        "(must have a dash with no leading zeros after the dash such as S14-01; must start " +
                        "with either one or two letters followed by two digits; maximum number of characters is 11; " +
                        "must contain only letters or digits and one dash). All other Accession and MRN Types must have the " +
                        "maximum of 25 characters; must not start with one or more consequtive zeros; must be made up of letters, " +
                        "numbers and possibly a dash; the first and last character must be either digits or letters (not a dash). " +
                        "Example of an acceptable character string: DC-100000000211";

        var errorHtml = createTableErrorWell(errmsg);
        //var errorHtml = createTableErrorWell('Please make sure that all cells in the table form are valid. Number of error cells:'+errCells +'. Error cells are marked with red.');
        $('#validationerror').append(errorHtml);
        $('.tableview-submit-btn').button('reset');
        return false;
    }
    /////////// EOF Check cell validation ///////////


    var countRow = _sotable.countRows();
    //console.log( 'countRow=' + countRow );

    /////////// 2) Empty main cells validation ///////////
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
        //var errorHtml = createTableErrorWell('Please make sure that all fields in the table form are valid. Number of error rows:'+nonEmptyRows+'. Empty cells are marked with red.');
        $('#validationerror').append(errorHtml);
        $('.tableview-submit-btn').button('reset');
        return false;
    }
    /////////// EOF Empty main cells validation ///////////

    /////////// 3) Check existing keytypes and MRN-Accession conflicts //////////////
    for( var i=0; i<countRow-1; i++ ) { //for each row (except the last one)
        checkPrevGenAndConflictTable(i);
    }//for
    /////////// EOF Check existing keytypes and MRN-Accession conflicts //////////////

    //submit if no errors for all rows
    // Wait until idle (busy must be false)
    var _TIMEOUT = 300; // waitfor test rate [msec]
    waitfor( allRowProcessed, true, _TIMEOUT, 0, 'play->busy false', function() {

        //console.log("All rows processed!!!!!!!!!!!");
        $('.tableview-submit-btn').button('reset');

        if( _rowToProcessArr.length == 0 ) {
            var errorHtml = createTableErrorWell('No data to submit. All rows are empty or in the default state.');
            $('#validationerror').append(errorHtml);
            $('.tableview-submit-btn').button('reset');
            return false;
        }

        if( $('.tablerowerror-added').length == 0 ) {

            /////////////// Display MRN-ACC choices /////////////////////
            //Now we don't have any simple errors - errors without any choices to handle them.
            //Last validation step: show MRN-ACC conflict choices recorded as array _mrnAccConflictRowArr
            var index = 0;
            for( row in _mrnAccConflictRowArr ) {
                var mrnObj = _mrnAccConflictRowArr[row]["mrnObj"];
                var accObj = _mrnAccConflictRowArr[row]["accObj"];
                var message = _mrnAccConflictRowArr[row]["message"];

                createDataquality( mrnObj, accObj, message, index );
                setErrorToRow(row,conflictRenderer,true);
                index++;
            }
            if( index > 0 ) {
                //console.log("There are MRN-ACC conflicts ="+index);
                _tableValidated = false;
                return false;   //There are MRN-ACC conflicts
            }
            /////////////// EOF MRN-ACC choices /////////////////////

            //console.log("Submit form!!!!!!!!!!!!!!!");

            //get rows data from _rowToProcessArr
            assignDataToDatalocker();

            _tableValidated = true;

            //return false; //testing
            $('#table-scanorderform').submit();
        }

    });

    //console.log("END !!!!!!!!!!!");
    return false;
}

//get rows data from _rowToProcessArr and assign this to datalocker field
function assignDataToDatalocker() {

    var headers = _sotable.getColHeader();

    //get rows data from _rowToProcessArr
    //var data = new Array();
    var data = {
        header: headers,
        row: []
    };
    //data.push(headers);

    for( var i=0; i<_rowToProcessArr.length; i++ ) {
        //console.log("data row="+_rowToProcessArr[i]);
        //data.push( _sotable.getDataAtRow( _rowToProcessArr[i] ) );
        var row = _rowToProcessArr[i];
        var rowArr = new Array();
        //add cell id to datalocker for each field
        for( var col=0; col<headers.length; col++ ) {
            //var cellid = _sotable.getCellMeta(row,cell).id;
            var cellId = _sotable.getCellMeta(row,col).id;
            var cellValue =  _sotable.getDataAtCell(row,col);
            //console.log("("+row+","+col+"): cellId="+cellId+", cellValue="+cellValue);
            rowArr.push({
                "id"    : cellId,
                "value" : cellValue
            });
        }

        data.row.push(rowArr);

    }
    //console.log(data);

    if( _btnClickedName != null ) {
        $("#oleg_orderformbundle_messagetype_clickedbtn").val( _btnClickedName );
    }

    //provide table data to controller
    //http://itanex.blogspot.com/2013/05/saving-handsontable-data.html
    var jsonstr = JSON.stringify(data);
    //console.log("jsonstr="+jsonstr);
    $("#oleg_orderformbundle_messagetype_datalocker").val( jsonstr );

}

function saveClick(btnname) {
    //console.log("btnname="+btnname);
    _btnClickedName = btnname;
}

function allRowProcessed() {
    //console.log( _processedRowCount +"=="+ _rowToProcessArr.length );
    if( _processedRowCount == _rowToProcessArr.length ) {
        return true;
    } else {
        return false;
    }
}

//return true when check is finished
function checkPrevGenAndConflictTable(row) {

    //return Q.promise(function(resolve, reject) {

        var dataRow = _sotable.getDataAtRow(row);
        var accType = dataRow[_tableMainIndexes.acctype];
        var acc = dataRow[_tableMainIndexes.acc];
        var mrnType = dataRow[_tableMainIndexes.mrntype];
        var mrn = dataRow[_tableMainIndexes.mrn];
        var dob = dataRow[_tableMainIndexes.dob];

        if( isValueEmpty(accType) || isValueEmpty(acc) || isValueEmpty(mrnType) || isValueEmpty(mrn) ) {
            return false;
        }

        //don't validate the untouched OR is empty rows
        if( exceptionRow(row) ) {
            return false;
        }

        //console.log("check PrevGen And Conflicts, row=", row + ": accType="+accType+", acc="+acc);

        //required for MRN-ACC conflict
        var accTypeCorrect = null;
        var mrnTypeCorrect = null;
        var mrnDB = null;
        var mrntypeDB = null;
        var mrnstring = null;
        var message = null;

        //required for MRN-DOB conflict
        var mrnDBByPatient = null;
        var mrntypeIDDBByPatient = null;
        var dobDBByPatient = null;

        //get mrn keytype id
        getKeyTypeID('patient',mrnType).
        //check existing mrn keytype
        then(
            function(response) {
                mrnTypeCorrect = response;
                //console.log("Before checkPrevGenKeyTable for patient", response);
                return checkPrevGenKeyTable('patient',mrn,mrnType,mrnTypeCorrect,true);
            }
        ).
        //add error for mrn
        then(
            function(response) {
                //console.log("after mrn check PrevGenKeyTable:"+response);
                if( !response ) {
                    var errmsg = 'The MRN(s) you have specified to be Previously Auto-Generated "'+mrn+'" were not found. Please correct the MRN in the highlighted row(s) or change the MRN Type.';
                    var errorHtml = createTableErrorWell(errmsg);
                    $('#validationerror').append(errorHtml);
                    //_sotable.getCellMeta(row,mrnType).renderer = forceRedRenderer;
                    //_sotable.getCellMeta(row,_tableMainIndexes.mrn).renderer = forceRedRenderer;
                    setSpecialErrorToRow(row);
                    _sotable.render();
                } else {
                    if( response instanceof Array && "mrn" in response ) {
                        //we should have only one correct mrn and dob provided by a check controller
                        mrnDBByPatient = response['mrn'];
                        mrntypeIDDBByPatient = response['mrntypeID'];
                        dobDBByPatient = response['dob'];
                    }
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
                    var errmsg = 'The Accession Numbers you have specified to be Previously Auto-Generated "'+acc+'" were not found. Please correct the Accesion Number in the highlighted row(s) or hange the Accession Number Type.';
                    var errorHtml = createTableErrorWell(errmsg);
                    $('#validationerror').append(errorHtml);
                    //_sotable.getCellMeta(row,_tableMainIndexes.acc).renderer = forceRedRenderer;
                    setSpecialErrorToRow(row);
                    _sotable.render();
                } else {
                    if( response instanceof Array && "parentkeyvalue" in response ) {
                        //console.log("parentkeyvalue="+response['parentkeyvalue']);
                        mrnDB = response['parentkeyvalue'];
                        mrntypeDB = response['parentkeytype'];
                        mrnstring = response['mrnstring'];
                        message = response['message'];
                    }
                }
            }
        ).
        //check internal MRN-Accession Number conflict within the table
        then(
            function(response) {
                var errLen = $('.tablerowerror-added').length;
                if( errLen == 0 && mrnAccInternalConflict( acc, accType, mrn, mrnType ) ) {
                    //var errmsg = "Please review the cells marked yellow and make sure the same accession number is always listed as belonging to the same patient MRN. <br>" +
                    //"The same accession number can not be tied to two different patients.";
                    var errmsg = "Please review the cell(s) marked yellow and make sure each accession number is always listed as belonging " +
                                 "to the same patient's MRN. <br>" + "The same accession number can not be tied to two different patients.";
                    var errorHtml = createTableErrorWell(errmsg);
                    $('#validationerror').append(errorHtml);
                    setErrorToRow(row,conflictRenderer,true);
                }
            }
        ).
        //check internal MRN-DOB conflict within the table
        then(
            function(response) {
                var errLen = $('.tablerowerror-added').length;
                if( errLen == 0 && mrnDobInternalConflict( mrn, mrnType, dob ) ) {
                    var errmsg = "Please correct multiple different Date of Birth values for a patient with the same MRN listed in highlighted rows.";

                    var errorHtml = createTableErrorWell(errmsg);
                    $('#validationerror').append(errorHtml);
                    setErrorToRow(row,conflictRenderer,true);
                }
            }
        ).
        //check MRN-Accession conflict with DB
        then(
            function(response) {

                var errLen = $('.tablerowerror-added').length;

                //console.log("before check MRN-Accession conflict with DB: errLen="+errLen);

                if( errLen == 0 && !mrnMrnDBEqual( mrnDB, mrn, mrntypeDB, mrnTypeCorrect ) ) {

                    var mrnObj = Array();
                    mrnObj["mrnValueForm"] = mrn;
                    mrnObj["mrnValueDB"] = mrnDB;
                    mrnObj["mrntypeIDForm"] = mrnTypeCorrect;
                    mrnObj["mrntypeTextForm"] = mrnType;
                    mrnObj["mrnstring"] = mrnstring;
                    mrnObj["patientInput"] = null;

                    var accObj = Array();
                    accObj["accValueForm"] = acc;
                    accObj["accValueDB"] = null;
                    accObj["acctypeTextForm"] = accType;
                    accObj["acctypeIDForm"] = accTypeCorrect;
                    accObj["accInput"] = null;

                    _mrnAccConflictRowArr[row] = new Array();
                    _mrnAccConflictRowArr[row]['mrnObj'] = mrnObj;
                    _mrnAccConflictRowArr[row]['accObj'] = accObj;
                    _mrnAccConflictRowArr[row]['message'] = message;

                }//if

            }
        ).
        //check MRN-DOB conflict with DB
        then(
            function(response) {
                var errLen = $('.tablerowerror-added').length;
                var mrnAccErrorBoxesLen = $('#validationerror').find('.validationerror-added').length;

                if( errLen == 0 && mrnAccErrorBoxesLen == 0 && !mrnDobDBEqual( mrnDBByPatient, mrn, mrntypeIDDBByPatient, mrnTypeCorrect, dobDBByPatient, dob ) ) {
                    //var errmsg = "The Date of Birth value you have provided for the patient in the highlighted row is not equal to the Date of Birth " +
                    //"that is on file for the patient with this MRN. Please correct it or let the system administrator know about this issue.";
                    var errmsg = "The Date of Birth value of " + dob + " you have provided for the patient in the highlighted row with MRN " +
                        mrn + ", " + mrnType +
                        " is not equal to the " + dobDBByPatient + " Date of Birth that is on file for the patient with this MRN." +
                        " Please correct it or let the system administrator know about this issue";
                    var errorHtml = createTableErrorWell(errmsg);
                    $('#validationerror').append(errorHtml);
                    setErrorToRow(row,conflictRenderer,true);
                }
            }
        ).
    //    //recursive call
    //    then(
    //        function(response) {
    //            if( row+1 < countRow ) {
    //                checkPrevGenAndConflictTable(row+1, countRow);
    //            }
    //        }
    //    ).
        then(
            function(response) {
                //console.log("Chaining OK, row="+row+", response="+response);
                _processedRowCount++;
            }
//            function(error) {
//                console.error("Failed! error=" + error);
//                //console.log("Failed! error");
//                $('.tableview-submit-btn').button('reset');
//            }
        ).
        done(
            function(response) {
                console.log("Done ", response);
                $('.tableview-submit-btn').button('reset');
            }
        );

}

function conflictHandled(row) {
    var errorBoxes = $('#validationerror').find('.validationerror-added');

    //console.log( "errorBoxes.length="+errorBoxes.length );

    for (var i = 0; i < errorBoxes.length; i++) {

        var errorBox = errorBoxes.eq(i);
        var checkedEl = errorBox.find("input:checked");

        if( checkedEl && typeof checkedEl.attr("id") != "undefined" ) {
            var idArr = checkedEl.attr("id").split("_");

            //console.log("checkedEl="+checkedEl.val()+", id="+checkedEl.attr("id")+", class="+checkedEl.attr("class"));
            //console.log( "row="+row+", idArr[4]="+idArr[4] );

            var checkedVal = checkedEl.val();

            if(  checkedVal != "OPTION3" && row == idArr[4] ) {
                return true;
            }
        } else {
            errorBoxes.remove();
            return false;
        }

    }

    return false;
}

function cleanErrorTable() {
    _mrnDobArr.length = 0;
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

function setSpecialErrorToRow(row) {
    var headers = _sotable.getColHeader();
    for( var col=0; col< headers.length; col++ ) {  //foreach column
        _sotable.getCellMeta(row,col).renderer = redWithBorderRenderer;
    }
    _sotable.render();
}

function mrnDobInternalConflict( mrn, mrnType, dob ) {

    var conflict = false;

    if( isValueEmpty(dob) )
        return conflict;

    //console.log('check conflict internal mrn-DOB: mrn='+mrn+", mrnType="+mrnType+", dob="+dob + " arrlen="+_mrnDobArr.length);

    if( _mrnDobArr.length > 0 ) {
        for( var i=0; i< _mrnDobArr.length; i++ ) {
            var dobArr = _mrnDobArr[i].dob;
            var mrnArr = _mrnDobArr[i].mrn;
            var mrnTypeArr = _mrnDobArr[i].mrnType;
            //console.log('internal mrn-DOB: dob='+dob+", dobArr="+dobArr);
            if( mrnMrnDBEqual(mrnArr,mrn,mrnTypeArr,mrnType) ) {
                //console.log('mrnMrnDBEqual true');
                if( dob !== dobArr ) {
                    //console.log('internal mrn,mrntype-DOB conflict detected');
                    conflict = true;
                }
            }
        }
    }

    if( !conflict ) {
        var mrndob = Array();
        mrndob['dob'] = dob;
        mrndob['mrn'] = mrn;
        mrndob['mrnType'] = mrnType;
        _mrnDobArr.push(mrndob);
    }

    return conflict;
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
                if( !mrnMrnDBEqual(mrnArr,mrn,mrnTypeArr,mrnType) ) {
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

function mrnMrnDBEqual( mrnDB, mrn, mrntypeDB, mrnTypeCorrect ) {
    //console.log("conflict: ("+mrnDB + ") ?= (" + mrn + ") | (" + mrntypeDB + ") ?= (" + mrnTypeCorrect + ")");
    if( !mrnDB || !mrntypeDB ) {
        //console.log("ERROR: DB's mrn and/or mrntype are null");
        return true;
    }
    mrnDB = trimWithCheck(mrnDB);
    mrn = trimWithCheck(mrn);
    mrntypeDB = trimWithCheck(mrntypeDB);
    mrnTypeCorrect = trimWithCheck(mrnTypeCorrect);
    if( mrnDB == mrn && mrntypeDB == mrnTypeCorrect ) {
        return true;
    } else {
        return false;
    }
}

function mrnDobDBEqual( mrnDB, mrn, mrntypeDB, mrnTypeCorrect, dobDB, dob ) {

    //console.log("mrnDobDB Equal: ("+mrnDB + ") ?= (" + mrn + ") | (" + mrntypeDB + ") ?= (" + mrnTypeCorrect + ")" + "; dobDB="+dobDB+", dob="+dob);

    if( !mrnDB || !mrntypeDB || !mrn || !mrnTypeCorrect || !dobDB || !dob ) {
        //console.log("Do not compare: DB's mrn and/or mrntype are null");
        return true;
    }

    if( mrnMrnDBEqual(mrnDB, mrn, mrntypeDB, mrnTypeCorrect) ) {
        if( dobDB == null ) {
            dobDB = "";
        }
        if( dob == null ) {
            dob = "";
        }
        if( dobDB === dob ) {
            return true;
        }
    }

    return false;
}

//return true if ok, false if prev gen value is not found in DB
//force - if true then make ajax check even if there is no "Existing Auto-generated" type
function checkPrevGenKeyTable(name,keyvalue,keytype,keytypeCorrect,force) {

    return Q.promise(function(resolve, reject) {
        //console.log(name+': keyvalue='+keyvalue+', keytype='+keytype);

        var makeCheck = true;

        if( keyvalue == '' || keytype == '' ) {
            //console.log(name+": keytype or keyvalue are null");
            makeCheck = false;
        }

        if( !keytypeCorrect ) {
            //console.log(name+": keytypeCorrect is null");
            makeCheck = false;
        }

        if( !force && name == 'accession' && keytype != 'Existing Auto-generated Accession Number' ) {
            //console.log(name+": no check for prev gen is required");
            makeCheck = false;
        }

        if( !force && name == 'patient' && keytype != 'Existing Auto-generated MRN' ) {
            //console.log(name+": no check for prev gen is required");
            makeCheck = false;
        }

        if( makeCheck ) {
            $.ajax({
                url: getCommonBaseUrl("check/"+name+'/check'),    //urlCheck+name+'/check',
                type: 'GET',
                data: {key: keyvalue, extra: keytypeCorrect, inst: _institution},
                contentType: 'application/json',
                dataType: 'json',
                timeout: _ajaxTimeout,
                async: false,
                success: function (data) {
                    //console.debug("get element ajax ok");
                    if( data == -2 ) {
                        //console.log("Existing Auto-generated object does not exist in DB for "+name);
                        resolve(false);
                    } else {
                        if( "extraid" in data ) {
                            var res = new Array();
                            res['parentkeyvalue'] = data['parent'];
                            res['parentkeytype'] = data['extraid'];
                            res['parentdob'] = data['parentdob'];
                            res['mrnstring'] = data['mrnstring'];
                            res['message'] = data['message'];
                            resolve(res);
                        } else
                        if( "mrn" in data ) {
                            var res = new Array();
                            //console.log("dob="+data['dob'][0].text);
                            res['mrn'] = data['mrn'][0].text;
                            res['mrntypeID'] = data['mrn'][0].keytype;

                            if( data['dob'] && data['dob'][0] ) {
                                res['dob'] = data['dob'][0].text;
                            } else {
                                res['dob'] = null;
                            }

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
    } else {
        _rowToProcessArr.push(row); //count rows to process. Later we will need it to check if all rows were processed by ajax
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
