/**
 * Created by oli2002 on 5/6/14.
 */

//1) check if cell validators are ok
//2) check for key empty cells
//3) check if previously generated keys are exists in DB (ajax)
//4) check for MRN-Accession conflicts
function validateHandsonTable() {

    //set main indexes for the column such as Acc Type, Acc Number ...
    _tableMainIndexes = getTableDataIndexes();

    //clean all previous error wells
    $('.tablerowerror-added').remove();

    /////////// 1) Check cell validator ///////////
    var errCells = $('.htInvalid').length;
    if( errCells > 0 ) {
        var errorHtml = createTableErrorWell('Please make sure that all cells in the table form are valid. Number of error cells:'+errCells +'. Error cells are marked with red.');
        $('#validationerror').append(errorHtml);
    }
    /////////// EOF Check cell validation ///////////


    var countRow = _sotable.countRows();
    console.log( 'countRow=' + countRow );

    /////////// 2) Empty main cells validation ///////////
    var emptyRows = 0;
    for( var i=0; i<countRow-1; i++ ) { //for each row (except the last one)
        if( !validateEmptyHandsonRow(i) ) {
            emptyRows++;
        }
    } //for each row
    if( emptyRows > 0 ) {
        var errorHtml = createTableErrorWell('Please make sure that all fields in the table form are valid. Number of error rows:'+emptyRows+'. Error cells are marked with red.');
        $('#validationerror').append(errorHtml);
    }
    /////////// EOF Empty main cells validation ///////////

    /////////// 3) Check existing keytypes //////////////
    for( var i=0; i<countRow-1; i++ ) { //for each row (except the last one)
        checkExistingKeyTable(i)
    }
    /////////// EOF existing keytype ///////////////////

    /////////// 3) MRN-Accession conflict validation ///////////
//    var conflictRows = 0;
//    for( var i=0; i<countRow-1; i++ ) { //for each row (except the last one)
//        if( !validateConflictHandsonRow(i) ) {
//            conflictRows++;
//        }
//    }
//    if( conflictRows > 0 ) {
//        var errorHtml = createTableErrorWell('There are MRN-Accession Conflicts. Number of conflicting rows:'+conflictRows);
//        $('#validationerror').append(errorHtml);
//    }
    /////////// EOF MRN-Accession conflict validation ///////////

}

//check for MRN-Accession conflict in DB and in Table
function validateConflictHandsonRow( row ) {
    var dataRow = _sotable.getDataAtRow(row);
    var accType = dataRow[_tableMainIndexes.acctype];
    var acc = dataRow[_tableMainIndexes.acc];
    var mrnType = dataRow[_tableMainIndexes.mrntype];
    var mrn = dataRow[_tableMainIndexes.mrn];
    var part = dataRow[_tableMainIndexes.part];
    var block = dataRow[_tableMainIndexes.block];
    console.log('row:'+row+': accType='+accType+', acc='+acc+' <= accTypeIndex='+_tableMainIndexes.acctype+', accIndex='+_tableMainIndexes.acc);

    var existingErrors = 0;

    //check for DB conflict
    if(
        acc && acc !="" && accType && accType !="" &&
            mrn && mrn !="" && mrnType && mrnType !=""
        )
    {
        //console.log("validate accession-mrn-mrntype");
        acc = trimWithCheck(acc);
        accType = trimWithCheck(accType);

        $.ajax({
            url: urlCheck+"accession/check",
            type: 'GET',
            data: {key: acc, extra: accType},
            contentType: 'application/json',
            dataType: 'json',
            timeout: _ajaxTimeout,
            async: false,
            success: function (data) {

                if( data == -2 ) {
                    //Existing Auto-generated object does not exist in DB
                    //var errorHtml = createErrorWell(accInput,"accession");
                    //$('#validationerror').append(errorHtml);
                    existingErrors++;
                    return false;
                }

                if( data.id ) {

                    var mrn = data['parent'];
                    var mrntype = data['extraid'];
                    var mrnstring = data['mrnstring'];
                    var orderinfo = data['orderinfo'];

                    mrn = trimWithCheck(mrn);
                    mrntype = trimWithCheck(mrntype);
                    mrnValue = trimWithCheck(mrnValue);
                    mrntypeValue = trimWithCheck(mrntypeValue);

                    //console.log('mrn='+mrn+', mrntype='+mrntype);

                    if( mrn == mrnValue && ( mrntype == mrntypeValue || 13 == mrntypeValue ) ) {    //13 - Auto-generated MRN. Need it for edit or amend form
                        //console.log("validated successfully !");
                    } else {
                        //console.log('mrn='+mrn+', mrntype='+mrntype+ " do not match to form's "+" mrnValue="+mrnValue+", mrntypeValue="+mrntypeValue);

                        var nl = "\n";    //"&#13;&#10;";

                        var message_short = "MRN-ACCESSION CONFLICT:"+nl+"Entered Accession Number "+accValue+" ["+acctypeText+"] belongs to Patient with "+mrnstring+", not Patient with MRN "
                            +mrnValue+" ["+mrntypeText+"] as you have entered.";
                        var message = message_short + " Please correct either the MRN or the Accession Number above.";


                        var message1 = "If you believe MRN "+mrn+" and MRN "+mrnValue + " belong to the same patient, please mark here:";
                        var dataquality_message_1 = message_short+nl+"I believe "+mrnstring+" and MRN "+mrnValue+" ["+mrntypeText+"] belong to the same patient";
                        dataquality_message1.push(dataquality_message_1);

                        var message2 = "If you believe Accession Number "+accValue+" belongs to patient MRN "+mrnValue+" and not patient MRN "+mrn+" (as stated by "+orderinfo+"), please mark here:";
                        var dataquality_message_2 = message_short+nl+"I believe Accession Number "+accValue+" belongs to patient MRN "+mrnValue+" ["+mrntypeText+"] and not patient "+mrnstring+" (as stated by "+orderinfo+")";
                        dataquality_message2.push(dataquality_message_2);

                        var message3 = "If you have changed the involved MRN "+mrnValue+" or the Accession Number "+accValue+" in the form above, please mark here:";

                        if( !prototype ) {
                            //console.log('WARNING: conflict prototype is not found!!!');
                            return false;
                        }

                        var newForm = prototype.replace(/__dataquality__/g, index);

                        newForm = newForm.replace("MRN-ACCESSION CONFLICT", message);

                        newForm = newForm.replace("TEXT1", message1);
                        newForm = newForm.replace("TEXT2", message2);
                        newForm = newForm.replace("TEXT3", message3);

                        //console.log("newForm="+newForm);

                        var newElementsAppended = $('#validationerror').append(newForm);
                        //var newElementsAppended = newForm.appendTo("#validationerror");

                        //red
                        accInput.parent().addClass("has-error");
                        patientInputs.parent().addClass("has-error");

                        setDataqualityData( index, accValue, acctypeValue, mrnValue, mrntypeValue );

                        index++;
                        totalError++;

                        //console.log('end of conflict process');

                    }

                } else {
                    console.debug("validation: accession object not found");
                }
            },
            error: function ( x, t, m ) {
                console.debug("validation: get object ajax error accession");
                if( t === "timeout" ) {
                    getAjaxTimeoutMsg();
                }
                return false;
            }
        });

    }

}

function checkExistingKeyTable(row) {

    var dataRow = _sotable.getDataAtRow(row);
    var accType = dataRow[_tableMainIndexes.acctype];
    var acc = dataRow[_tableMainIndexes.acc];
    var mrnType = dataRow[_tableMainIndexes.mrntype];
    var mrn = dataRow[_tableMainIndexes.mrn];

    if( isValueEmpty(accType) || isValueEmpty(acc) || isValueEmpty(mrnType) || isValueEmpty(mrn) ) {
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
            console.log("after mrn check PrevGenKeyTable:"+response);
            if( !response ) {
                var errorHtml = createTableErrorWell('Previously auto-generated MRN Numbers is not correct. Please correct the auto-generated number.');
                $('#validationerror').append(errorHtml);
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
            console.log("after acc check PrevGenKey Table:"+response);
            if( !response ) {
                var errorHtml = createTableErrorWell('Previously auto-generated Accession Numbers is not correct. Please correct the auto-generated number.');
                $('#validationerror').append(errorHtml);
                //highlight error row
                //_sotable.selectCell(row,_tableMainIndexes.acc);
                _sotable.getCellMeta(row,_tableMainIndexes.acc).renderer = yellowRenderer;
            } else {
                if( response instanceof Array && "parentkeyvalue" in response ) {
                    //console.log("parentkeyvalue="+response['parentkeyvalue']);
                    mrnDB = response['parentkeyvalue'];
                    mrntypeDB = response['parentkeytype'];
                }
            }
        }
    ).
    //check conflict
    then(
        function(response) {
            if( mrnAccConflict( mrnDB, mrn, mrntypeDB, mrnTypeCorrect ) ) {
                var errorHtml = createTableErrorWell('MRN - Accession Numbers conflict.');
                $('#validationerror').append(errorHtml);
                _sotable.getCellMeta(row,_tableMainIndexes.acc).renderer = yellowRenderer;
            }
        }
    ).
    then(
        function(response) {
            console.log("Chaining OK, response="+response);
        },
        function(error) {
            console.error("Failed! error=", error);
        }
    );


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

    if( isValueEmpty(accType) || isValueEmpty(acc) || isValueEmpty(mrnType) || isValueEmpty(mrn) || isValueEmpty(part) || isValueEmpty(block) ) {
        return false;
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
