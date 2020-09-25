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
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/12/14
 * Time: 3:36 PM
 * To change this template use File | Settings | File Templates.
 */

var _htableid = "#transresDataTable";

var _sotable = null;    //scan order table
var _tableMainIndexes = null; //table indexes for main columns: Acc Type, Acc, MRN Type, MRN, Part ID, Block ID
var _colHeader = [];
var _rowToProcessArr = [];

//var _accessiontype = [];
//var _accessiontypes_simple = [];

var _residencytracks = [];
var _residencytracks_simple = [];

var _actions_simple = [];

//var _tdSize = 64;
//var _tdSize = 26;
var _tdSize = 36;
var _tdPadding = 5;
var _rowHeight =  _tdSize + 2*_tdPadding;

//from: http://past.handsontable.com/demo/renderers_html.html
// var imageRenderer = function (instance, td, row, col, prop, value, cellProperties) {
//     var escaped = Handsontable.helper.stringify(value),
//         img;
//
//     if (escaped.indexOf('http') === 0) {
//         img = document.createElement('IMG');
//
//         img.src = value;
//         img.height = _tdSize;
//         img.width = _tdSize;
//         //img.margin = "5px 5px 5px 5px";
//         //img.style.cssText = "margin: 5px;";
//         img.style.marginTop = "5px";
//         img.style.marginBottom = "5px";
//
//         // Handsontable.dom.addEvent(img, 'mousedown', function (e){
//         //     e.preventDefault(); // prevent selection quirk
//         // });
//
//         $(td).html(null);
//         //$(td).text(null);
//
//         //Handsontable.dom.empty(td);
//         td.appendChild(img);
//     }
//     else {
//         // render as text
//         Handsontable.renderers.TextRenderer.apply(this, arguments);
//     }
//
//     return td;
// };

// var canvasRenderer = function (instance, td, row, col, prop, value, cellProperties) {
//     var escaped = Handsontable.helper.stringify(value),
//         canvas;
//
//     if (escaped.indexOf('http') === 0) {
//         canvas = document.createElement('CANVAS');
//
//         //canvas.src = value;
//         canvas.height = _tdSize;
//         canvas.width = _tdSize;
//         //img.margin = "5px 5px 5px 5px";
//         //img.style.cssText = "margin: 5px;";
//         //canvas.style.marginTop = "5px";
//         //canvas.style.marginBottom = "5px";
//
//         canvas.id = "canvas-"+row+"-"+col;
//
//         // Handsontable.dom.addEvent(img, 'mousedown', function (e){
//         //     e.preventDefault(); // prevent selection quirk
//         // });
//
//         $(td).html(null);
//         //$(td).text(null);
//
//         //Handsontable.dom.empty(td);
//         td.appendChild(canvas);
//     }
//     else {
//         // render as text
//         Handsontable.renderers.TextRenderer.apply(this, arguments);
//     }
//
//     return td;
// };

//total 33
var _columnData_scanorder = [];

//$(document).ready(function() {
//
//});

function getResidencytracks() {

    var cycle = 'new';

    var url = getCommonBaseUrl("util/common/generic/"+"residencytracks","employees");
    //console.log("getAntobodies url="+url);

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "new" || cycle == "create" ) {
        url = url + "&opt=default";
    }

    //console.log("run url="+url);

    if( _residencytracks.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).done(function(data) {
            _residencytracks = data;
        });
    }
}

function ajaxFinishedCondition() {

    //console.log('_residencytracks.length='+_residencytracks.length+" =? _residencytracks_simple.length="+_residencytracks_simple.length);
    //return true; //testing

    // if( !(_residencytracks.length > 0) ) {
    //     //console.log('NULL _residencytracks.length='+_residencytracks.length);
    // }

    if( _actions_simple.length == 0 ) {
        _actions_simple.push("Add");
        //_actions_simple.push("Do not add");
        _actions_simple.push("");
    }


    if( _residencytracks.length > 0 ) {

        if(
            _residencytracks_simple.length >= _residencytracks.length
        ) {
            return true;
        }

        _residencytracks_simple.push(""); //add default empty residency track
        for(var i = 0; i < _residencytracks.length; i++) {
            var residencytrackName = _residencytracks[i].text;
            if(  _residencytracks[i].abbreviation ) {
                residencytrackName = _residencytracks[i].abbreviation;
            }
            //console.log('residencytrackName='+residencytrackName);
            _residencytracks_simple.push(residencytrackName);
        }
        //console.log("_residencytracks_simple:");
        //console.log(_residencytracks_simple);

        return true;
    } else {
        return false;
    }
}

function resappMakeColumnData() {

    var defaultActionIndex = 0;

    var defaultResidencytrackIndex = 0;
    //var defaultResidencytrack = $('#default-accession-type').val();
    //console.log("Residencytrack="+Residencytrack);
    // if( Residencytrack ) {
    //     for(var i = 0; i < _accessiontypes_simple.length; i++) {
    //         //console.log(_accessiontypes_simple[i]+"=?"+Residencytrack);
    //         if( _accessiontypes_simple[i] == Residencytrack ) {
    //             ResidencytrackIndex = i;
    //         }
    //     }
    // }

    _columnData_scanorder = [

        {
            header:'Action',
            default: defaultActionIndex,
            columns: {
                type: 'autocomplete',
                source: _actions_simple,
                strict: true,
                filter: false,
            }
        },

        { header:'AAMC ID', columns:{} },
        // { header:'ERAS Application ID', columns:{} },
        { header:'Application Receipt Date', columns:{} },

        //{ header:'Residency Track', columns:{} },
        {
            header:'Residency Track',
            default: defaultResidencytrackIndex,
            columns: {
                type: 'autocomplete',
                source: _residencytracks_simple,
                strict: true,
                filter: false,
            }
        },

        { header:'Application Season Start Date', columns:{} },
        { header:'Application Season End Date', columns:{} },
        { header:'Expected Residency Start Date', columns:{} },
        { header:'Expected Graduation Date', columns:{} },
        { header:'First Name', columns:{} },
        { header:'Last Name', columns:{} },
        { header:'Middle Name', columns:{} },
        { header:'Preferred Email', columns:{} },
        { header:'Medical School Graduation Date', columns:{} },
        { header:'Medical School Name', columns:{} },
        { header:'Degree', columns:{} },
        { header:'USMLE Step 1 Score', columns:{} },
        { header:'USMLE Step 2 CK Score', columns:{} },
        { header:'USMLE Step 3 Score', columns:{} },
        { header:'Country of Citizenship', columns:{} },
        { header:'Visa Status', columns:{} },
        { header:'Is the applicant a member of any of the following groups?', columns:{} },
        { header:'Number of first author publications', columns:{} },
        { header:'Number of all publications', columns:{} },
        { header:'AOA', columns:{} },
        { header:'Couple’s Match', columns:{} },
        { header:'Post-Sophomore Fellowship', columns:{} },

        { header:'Previous Residency Start Date', columns:{} },
        { header:'Previous Residency Graduation/Departure Date', columns:{} },
        { header:'Previous Residency Institution', columns:{} },
        { header:'Previous Residency City', columns:{} },
        { header:'Previous Residency State', columns:{} },
        { header:'Previous Residency Country', columns:{} },
        { header:'Previous Residency Track', columns:{} },
        
        // { header:'ERAS Application ID', columns:{} },
        { header:'ERAS Application', columns:{} },
        { header:'Duplicate?', columns:{} },
    ];

}

function handsonTableInit(handsometableDataArr) {

    var data = [];
    var columnsType = [];
    //var colHeader = [];
    var rows = 2;//21;//501;

    if( handsometableDataArr && typeof handsometableDataArr != 'undefined' && handsometableDataArr.length != 0 ) {
        rows = handsometableDataArr.length+1;
    }
    //console.log('handsonTableInit rows='+rows+":");
    //console.log(handsometableDataArr);

    // make init data, i=0 to skip the first row
    for( var i=1; i<rows; i++ ) {   //foreach row

        //console.log('row i='+i);

        var rowElement = [];
        //rowElement[0] = i;
        for( var ii=0; ii<_columnData_scanorder.length; ii++ ) {  //foreach column

            //console.log('column ii='+ii);

            if( 'default' in _columnData_scanorder[ii] ) {
                var index = _columnData_scanorder[ii]['default'];
                rowElement[ii] = _columnData_scanorder[ii]['columns']['source'][index];
                //console.log('assign rowElement='+rowElement[ii]);
            } else {
                //console.log('assign rowElement is null');
                rowElement[ii] = null;
            }

            //load data
            //console.log('load data for row='+i);
            //if( typeof handsometableDataArr != 'undefined' ) {
            if( handsometableDataArr && typeof handsometableDataArr != 'undefined' && handsometableDataArr.length > 0 ) {
                var headerTitle = _columnData_scanorder[ii]['header'];
                console.log('headerTitle='+headerTitle);
                console.log( handsometableDataArr[i-1] );
                if( typeof headerTitle != 'undefined' && typeof handsometableDataArr[i-1] != 'undefined' &&
                    headerTitle != '' && (i-1<handsometableDataArr.length) &&
                    handsometableDataArr[i-1].length > 0 &&
                    headerTitle in handsometableDataArr[i-1]
                ) {
                    //console.log('handsometableDataArr[i-1]:(');
                    //console.log(handsometableDataArr[i-1]);
                    //console.log(')');
                    if( handsometableDataArr[i-1][headerTitle] ) {
                        var cellValue = handsometableDataArr[i-1][headerTitle]["value"];
                        //console.log( "cellValue="+cellValue );
                        //var cellId = handsometableDataArr[i-1][headerTitle]["id"];
                        //console.log('cellValue='+cellValue);
                        //var value = handsometableDataArr[i-1][headerTitle];
                        //console.log( "value="+value );
                        if( cellValue != null && cellValue != "" ) {
                            //console.log(headerTitle+': set cellValue('+i+','+ii+')='+cellValue);
                            rowElement[ii] = cellValue;
                        }
                    }
                }
            }

        }//foreach column

        //console.log(rowElement);
        data.push(rowElement);

    }//foreach row

    // make header and columns
    for( var i=0; i<_columnData_scanorder.length; i++ ) {
        _colHeader.push( _columnData_scanorder[i]['header'] );
        columnsType.push( _columnData_scanorder[i]['columns'] );
    }

    //console.log(columnsType);
    //$('#multi-dataTable').doubleScroll();

    //console.log("data:");
    //console.log(data);
    //console.log(_colHeader);
    //console.log(columnsType);

    $(_htableid).handsontable({
        data: data,
        colHeaders: _colHeader,
        columns: columnsType,

        //colWidths: [200, 200, 200, 60],
        // colHeaders: ["Title", "Description", "Comments", "Cover"],
        // columns: [
        //     {data: "Source", renderer: "html"},
        //     {data: "Accession ID", renderer: "html"},
        //     {data: "Part ID", renderer: "html"},
        //     {data: "cover", renderer: coverRenderer}
        // ],

        minSpareRows: 1,
        contextMenu: ['row_above', 'row_below', 'remove_row'],
        manualColumnMove: true,
        manualColumnResize: true,
        autoWrapRow: true,
        //autoRowSize: {syncLimit: 300},
        // rowHeight: function(row) {
        //     return _rowHeight;
        // },
        // defaultRowHeight: _rowHeight,
        //autoRowSize: true, //{syncLimit: 300},
        renderAllRows: true,
        currentRowClassName: 'currentRowScanorder',
        currentColClassName: 'currentColScanorder',
        stretchH: 'all',
        //stretchV: 'all',
        //overflow: 'auto',
        cells: function(r,c,prop) {
            var cellProperties = {};

            // if( tableFormCycle == 'show' ) {
            //     cellProperties.readOnly = true;
            // }

            //console.log("c="+c+"; r="+r);                      //c=7
            //console.log(_columnData_scanorder[c]);    //_columnData_scanorder[c].header="Barcode"
            if( c > 0 ) {
                var headerTitle = _columnData_scanorder[c]['header'];
                if( typeof headerTitle != 'undefined' && headerTitle != '' &&
                    handsometableDataArr && typeof handsometableDataArr != 'undefined' && typeof handsometableDataArr[r] != 'undefined' &&
                    typeof handsometableDataArr[r][headerTitle] != 'undefined' &&
                    handsometableDataArr[r][headerTitle] != null
                ) {
                    var cellId = handsometableDataArr[r][headerTitle]["id"];
                    //console.log("cellId="+cellId);
                    cellProperties.id = cellId;
                    //console.log('cellProperties:');
                    //console.log(cellProperties);
                }
            }
            
            return cellProperties;
        },
        afterPaste: function(data,coord) {
            //console.log('afterPaste: data=');
            //console.log(data);
            //console.log('afterPaste: coord=');
            //console.log(coord);

            var startRow = coord[0]['startRow'];
            var endRow = coord[0]['endRow'];
            //console.log('afterPaste: startRow='+startRow+'; endRow='+endRow);

            var index = 0;
            for( var row=startRow; row<=endRow; row++ ) {  //foreach column
                var newValue = data[index][0];
                //transresBarcodeParser(row,newValue);
                index++;
            }

        },
        afterChange: function (change, source) {
            if (source === 'loadData') {
                //console.log("ignore source="+source);
                return; //don't save this change
            }

            if (change != null) {
                var changeData = change[0];
                //console.log("changeData:");
                //console.log(changeData);

                var rowNumber = changeData[0];
                var columnNumber = changeData[1];
                var oldValue = changeData[2];
                var newValue = changeData[3];
                //console.log("prop="+prop);
                //console.log("columnNumber="+columnNumber+", rowNumber="+rowNumber+": oldValue="+oldValue+"; newValue="+newValue);
                

                
            }
        },
        afterCreateRow: function (index, amount) {

            //testing
            //return; //TODO: testing
            //console.log('testing after CreateRow: return');

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

            resizeTableHeight();
        }
    });

    //set bs table
    //$(_htableid+' table').addClass('table-striped table-hover');
    $(_htableid+' table').addClass('table-hover');

    //set scan order table object as global reference
    _sotable = $(_htableid).handsontable('getInstance');

    resizeTableHeight();

    // _sotable.addHook("afterCreateRow", function(){
    //     console.log("afterCreateRow");
    //     //_sotable.render();
    //     setMultipleJqueryQrcode();
    //
    // });
    // _sotable.addHook("afterRemoveRow", function(){
    //     console.log("afterRemoveRow");
    //     //_sotable.render();
    //     setMultipleJqueryQrcode();
    //
    // });
    // _sotable.addHook("afterChangesObserved", function(){
    //     console.log("afterChangesObserved");
    //     //_sotable.render();
    //     setMultipleJqueryQrcode();
    //
    // });

}

function resizeTableHeight() {
    //console.log("Setting height");
    var countRow = _sotable.countRows();
    if( countRow < 5 ) {
        countRow = 5;
    }
    if( countRow > 20 ) {
        countRow = 20;
    }
    //console.log("_tdSize="+_tdSize+", countRow="+countRow);
    //var newHeight = countRow*(_tdSize + _tdPadding*4);
    var newHeight = countRow*(_tdSize);
    _sotable.updateSettings({height: newHeight});
}
function resizeTableHeight_new() {
    //_sotable.recalculateAllRowsHeight();
    //return true;

    //console.log("Setting height");
    var countRow = _sotable.countRows();
    //var heightRow = _sotable.getRowHeight(indexRow);
    //var indexRow = _sotable.recalculateAllRowsHeight();
    //var heightRow = _sotable.getColumnHeaderHeight();
    //var heightRow = _sotable.getRowHeight(indexRow);
    console.log("heightRow="+heightRow+", countRow="+countRow);
    //var newHeight = countRow*(_tdSize + _tdPadding*4);
    var newHeight = countRow*heightRow;
    _sotable.updateSettings({height: newHeight});
}





// function setDataCell( row, col, value ) {
//
//     //if( value && value != '' ) {    //set
//     if( !isValueEmpty(value) ) {
//
//         _sotable.setDataAtCell(row,col,value);
//         _sotable.getCellMeta(row,col).readOnly = true;
//
//     } else {    //clean
//
//         var newValue = null;
//
//         //if default exists => set to the element of source array, at the index specified by default
//         if( 'default' in _columnData_scanorder[col] ) {
//             var index = _columnData_scanorder[col]['default'];
//             var newValue = _columnData_scanorder[col]['columns']['source'][index];
//         }
//
//         //console.log('clean data cell at '+row+","+col+", value="+newValue);
//
//         _sotable.setDataAtCell(row,col,newValue);
//         _sotable.getCellMeta(row,col).readOnly = false;
//
//     }
//
// }

//clean form
// function processDataForm( action ) {
//
//     var handsontable = $(_htableid).data('handsontable');
//
//     var hdata = handsontable.getData();
//
//     //console.log('data len='+hdata.length);
//     //console.log( 'column'+'0'+',row'+'1'+':'+ hdata[0][1] );
//     //console.log( 'column'+'1'+',row'+'2'+':'+ hdata[1][2] );
//
//     //for each row (except the first one)
//     for( var i=0; i<hdata.length; i++ ) {
//
//         //console.log( 'row'+(i+1)+':' + hdata[i] );
//         if( hdata[i] !== undefined && hdata[i] !== null && hdata[i] != '' ) {
//
//             //for each column (except the first one)
//             for( var ii=0; ii<hdata[i].length; ii++ ) {
//
//                 //console.log( 'column'+(ii+1)+':' + hdata[i][ii] );
//                 //validateCell( i, ii, hdata[i][ii], true );
//
//                 if( action == 'clean' ) {
//                     cleanHTableCell(i,ii, false);
//                 }
//
//             } //for column
//
//         }
//
//     } //for row
//
//     //console.log( 'hdata=' + handsontable );
//
//     //var checkcell = $(_htableid).handsontable("getCell", 1, 2);
//     //checkcell.style.color = "red";
//     //checkcell.style.backgroundColor = '#F2DEDE';
//
// }

// function isValueEmpty(value) {
//     if( value && typeof value !== 'undefined' && value != '' ) {
//         return false;
//     } else {
//         return true;
//     }
// }

function transresValidateHandsonTable() {
    console.log("validateHandsonTable");

    if( !_sotable ) {
        return true;
    }

    transresHideBtn();

    var countRow = _sotable.countRows();
    //console.log("countRow="+countRow);
    for( var row=0; row<countRow-1; row++ ) { //for each row (except the last one)
        //console.log("row="+row);
        _rowToProcessArr.push(row);
    } //for each row

    //get rows data from _rowToProcessArr
    transresAssignDataToDatalocker();

    //transresShowBtn();

    //console.log("END !!!!!!!!!!!");
    //return true;

    return false;
}

//get rows data from _rowToProcessArr and assign this to datalocker field
function transresAssignDataToDatalocker() {

    var headers = _sotable.getColHeader();

    //get rows data from _rowToProcessArr
    //var data = [];
    var data = {
        header: headers,
        row: []
    };
    //data.push(headers);

    //console.log("_rowToProcessArr.length="+_rowToProcessArr.length);

    for( var i=0; i<_rowToProcessArr.length; i++ ) {
        //console.log("data row="+_rowToProcessArr[i]);
        //data.push( _sotable.getDataAtRow( _rowToProcessArr[i] ) );
        var row = _rowToProcessArr[i];
        var rowArr = [];
        //add cell id to datalocker for each field
        for( var col=0; col<headers.length; col++ ) {
            //var cellid = _sotable.getCellMeta(row,cell).id;
            //console.log("_sotable.getCellMeta(row,col):");
            //console.log(_sotable.getCellMeta(row,col));
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

    // if( _btnClickedName != null ) {
    //     $("#oleg_orderformbundle_messagetype_clickedbtn").val( _btnClickedName );
    // }

    //provide table data to controller
    //http://itanex.blogspot.com/2013/05/saving-handsontable-data.html
    var jsonstr = JSON.stringify(data);
    //var jsonstr = data;
    //console.log("jsonstr:");
    //console.log(jsonstr);
    $("#oleg_translationalresearchbundle_request_datalocker").val( jsonstr );
}


