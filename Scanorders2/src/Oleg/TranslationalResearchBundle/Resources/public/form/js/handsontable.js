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

var _accessiontype = [];
var _accessiontypes_simple = [];

var _antibodies = [];
var _antibodies_simple = [];

var _barcodeCol = 8;

var _tdSize = 64;
var _tdPadding = 5;
var _rowHeight =  _tdSize + 2*_tdPadding;

//from: http://past.handsontable.com/demo/renderers_html.html
var imageRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    var escaped = Handsontable.helper.stringify(value),
        img;

    if (escaped.indexOf('http') === 0) {
        img = document.createElement('IMG');

        img.src = value;
        img.height = _tdSize;
        img.width = _tdSize;
        //img.margin = "5px 5px 5px 5px";
        //img.style.cssText = "margin: 5px;";
        img.style.marginTop = "5px";
        img.style.marginBottom = "5px";

        // Handsontable.dom.addEvent(img, 'mousedown', function (e){
        //     e.preventDefault(); // prevent selection quirk
        // });

        $(td).html(null);
        //$(td).text(null);

        //Handsontable.dom.empty(td);
        td.appendChild(img);
    }
    else {
        // render as text
        Handsontable.renderers.TextRenderer.apply(this, arguments);
    }

    return td;
};

var canvasRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    var escaped = Handsontable.helper.stringify(value),
        canvas;

    if (escaped.indexOf('http') === 0) {
        canvas = document.createElement('CANVAS');

        //canvas.src = value;
        canvas.height = _tdSize;
        canvas.width = _tdSize;
        //img.margin = "5px 5px 5px 5px";
        //img.style.cssText = "margin: 5px;";
        //canvas.style.marginTop = "5px";
        //canvas.style.marginBottom = "5px";

        canvas.id = "canvas-"+row+"-"+col;

        // Handsontable.dom.addEvent(img, 'mousedown', function (e){
        //     e.preventDefault(); // prevent selection quirk
        // });

        $(td).html(null);
        //$(td).text(null);

        //Handsontable.dom.empty(td);
        td.appendChild(canvas);
    }
    else {
        // render as text
        Handsontable.renderers.TextRenderer.apply(this, arguments);
    }

    return td;
};

//total 33
var _columnData_scanorder = [];

//$(document).ready(function() {
//
//});

function getAntobodies(holder,force) {

    var cycle = 'edit';

    var url = getCommonBaseUrl("util/common/generic/"+"antibody","employees");
    //console.log("getAntobodies url="+url);

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    url = url + "?cycle=" + cycle;

    if( cycle == "new" || cycle == "create" ) {
        url = url + "&opt=default";
    }

    //console.log("run url="+url);

    if( _antibodies.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            _antibodies = data;
        });
    }
}

function ajaxFinishedCondition() {

    //console.log('_accessiontype.length='+_accessiontype.length);

    if( !(_accessiontype.length > 0) ) {
        //console.log('NULL _accessiontype.length='+_accessiontype.length);
    }

    if( _accessiontype.length > 0 ) {

        if(
            _accessiontypes_simple.length == _accessiontype.length
            && _antibodies_simple.length == _antibodies.length
        ) {
            return true;
        }

        for(var i = 0; i < _accessiontype.length; i++) {
            var acctypeName = _accessiontype[i].text;
            if(  _accessiontype[i].abbreviation ) {
                acctypeName = _accessiontype[i].abbreviation;
            }
            //console.log('acctypeName='+acctypeName);
            _accessiontypes_simple.push(acctypeName);
        }

        for(var i = 0; i < _antibodies.length; i++) {
            var antibodyName = _antibodies[i].text;
            if(  _antibodies[i].abbreviation ) {
                antibodyName = _antibodies[i].abbreviation;
            }
            //console.log('antibodyName='+antibodyName);
            _antibodies_simple.push(antibodyName);
        }

        return true;
    } else {
        return false;
    }
}

function transresMakeColumnData() {

    var defaultAccessionTypeIndex = 0;
    var defaultAccessionType = $('#default-accession-type').val();
    //console.log("defaultAccessionType="+defaultAccessionType);
    if( defaultAccessionType ) {
        for(var i = 0; i < _accessiontypes_simple.length; i++) {
            //console.log(_accessiontypes_simple[i]+"=?"+defaultAccessionType);
            if( _accessiontypes_simple[i] == defaultAccessionType ) {
                defaultAccessionTypeIndex = i;
            }
        }
    }

    var defaultAntibodyIndex = null;
    var defaultAntibody = $('#default-antibody').val();
    //console.log("defaultAccessionType="+defaultAccessionType);
    if( defaultAccessionType ) {
        for(var i = 0; i < _antibodies_simple.length; i++) {
            //console.log(_accessiontypes_simple[i]+"=?"+defaultAccessionType);
            if( _antibodies_simple[i] == defaultAntibody ) {
                defaultAntibodyIndex = i;
            }
        }
    }

    _columnData_scanorder = [
        {
            header:'Source',
            default: defaultAccessionTypeIndex,
            columns: {
                type: 'autocomplete',
                source: _accessiontypes_simple,
                strict: true,
                filter: false,
            }
        },
        { header:'Accession ID', columns:{} },
        { header:'Part ID', columns:{} },
        { header:'Block ID', columns:{} },
        { header:'Slide ID', columns:{} },
        { header:'Stain Name', columns:{} },
        {
            header:'Antibody',
            default: defaultAntibodyIndex,
            columns: {
                type: 'autocomplete',
                source: _antibodies_simple,
                strict: true,
                filter: false,
            }
        },
        { header:'Other ID', columns:{} },
        { header:'Sample Name', columns:{} },
        //{ header:'Barcode Image', columns:{renderer:imageRenderer} },
        //{ header:'Barcode Image', columns:{renderer:canvasRenderer} },
        { header:'Barcode', columns:{} },
        { header:'Comment', columns:{} }
    ];

    //_barcodeCol = 7;
}

function handsonTableInit(handsometableDataArr,tableFormCycle) {

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

        var rowElement = [];
        //rowElement[0] = i;
        for( var ii=0; ii<_columnData_scanorder.length; ii++ ) {  //foreach column

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
                //console.log('headerTitle='+headerTitle);
                //console.log( handsometableDataArr[i-1] );
                if( typeof headerTitle != 'undefined' && typeof handsometableDataArr[i-1] != 'undefined' &&
                    headerTitle != '' && (i-1<handsometableDataArr.length) && headerTitle in handsometableDataArr[i-1]
                ) {
                // if( typeof headerTitle != 'undefined' && typeof handsometableDataArr[i-1] != 'undefined' &&
                //     headerTitle != '' && (i-1<handsometableDataArr.length)
                // ) {
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
        renderAllRows: true,
        currentRowClassName: 'currentRowScanorder',
        currentColClassName: 'currentColScanorder',
        stretchH: 'all',
        cells: function(r,c,prop) {
            var cellProperties = {};

            if( tableFormCycle == 'show' ) {
                cellProperties.readOnly = true;
            }

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

            //if( c == _barcodeCol ) {
                //console.log("c="+c+"; r="+r);
                //var cellValue =  this.getDataAtCell(r,c);
                //console.log("cellValue="+cellValue);
                //console.log(prop);
            //}

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
                transresBarcodeParser(row,newValue);
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

                //console.log("header="+_columnData_scanorder[columnNumber].header);
                //if( _columnData_scanorder[columnNumber].header != "Barcode") {
                //    return;
                //}

                //always re-generate images, because when any cell is modified, then table is refreshed
                setMultipleJqueryQrcode();

                if( columnNumber == _barcodeCol+1 ) {
                    //_sotable.setDataAtCell(rowNumber,columnNumber,null);
                    //setMultipleJqueryQrcode();
                }

                if( columnNumber != _barcodeCol ) {
                    //console.log("ignore changes in col="+columnNumber);
                    return;
                }

                // if( _sotable ) {
                //     console.log("_sotable exists");
                //     var ht = _sotable;
                // } else {
                //     console.log("_sotable does not exists");
                //     var ht = $(_htableid).handsontable('getInstance');
                // }



                //var barcode = _sotable.getDataAtRowProp(rowNumber, 'Barcode');

                if( oldValue != newValue ) {
                    transresBarcodeParser(rowNumber,newValue);
                }

                if( oldValue != newValue ) {
                    //var barcodeImage = transresTableBarcodeGeneration(newValue);
                    //console.log("barcodeImage=" + barcodeImage);
                    //_sotable.setDataAtCell(rowNumber, columnNumber + 1, barcodeImage);

                    // //Returns a TD element for the given row and column arguments, if it is rendered on screen.
                    // //Returns null if the TD is not rendered on screen (probably because that part of the table is not visible).
                    // var cellEl = _sotable.getCell(rowNumber, columnNumber + 1);
                    // //create canvas element in this TD
                    // //<canvas id="canvas" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>
                    // var canvasId = "canvas-"+rowNumber+"-"+columnNumber + 1;
                    // var canvasEl = '<canvas id="'+canvasId+'" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>';
                    //
                    // $(cellEl).append(canvasEl,newValue);
                    //
                    // render(canvasId);

                    //setBarcodeImageApi(newValue,rowNumber,columnNumber+1);
                    //setBarcodeCanvas(newValue,rowNumber,columnNumber+1);

                    //working: multiple imgs by API
                    if(0) {
                        var barcodeImageSrc = getBarcodeImageSrcApi(newValue);
                        _sotable.setDataAtCell(rowNumber, columnNumber + 1, barcodeImageSrc);
                    }

                    //working canvas single or multiple without handsontable
                    //setBarcodeCanvas(newValue,rowNumber,columnNumber+1);

                    if(0) {
                        var barcodeImageSrc = getBarcodeCanvasSrc(newValue,rowNumber,columnNumber+1);
                        //console.log("barcodeImageSrc="+barcodeImageSrc);
                        _sotable.setDataAtCell(rowNumber, columnNumber + 1, barcodeImageSrc);
                    }

                    //WORKING: problem does not generate for all rows, generate only the first row
                    //setBarcodeMultipleCanvas(_barcodeCol);

                    //WORKING: setQrcode(newValue,rowNumber,columnNumber+1);
                    //setMultipleQrcode();

                    //jquery.qrcode.min.js
                    //jqueryQrcode(newValue,rowNumber,columnNumber+1);
                    //setMultipleJqueryQrcode();

                }
            }
        }
    });

    //set bs table
    //$(_htableid+' table').addClass('table-striped table-hover');
    $(_htableid+' table').addClass('table-hover');

    //set scan order table object as global reference
    _sotable = $(_htableid).handsontable('getInstance');

    //generate barcodes
    //setBarcodeMultipleCanvas(_barcodeCol);
    //setMultipleQrcode(); //Working!
    setMultipleJqueryQrcode();

    //resizeTableHeight();

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
    var newHeight = countRow*(_tdSize + _tdPadding*4);
    _sotable.updateSettings({height: newHeight});
}

function setQrcode(barcodeText,rowNumber,columnNumber) {

    var cellEl = _sotable.getCell(rowNumber, columnNumber);
    var canvasId = "canvas-"+rowNumber+"-"+columnNumber;
    //var canvasEl = '<canvas id="'+canvasId+'" width=100 height=100 style="border:1px solid #fff;visibility:visible"></canvas>';
    var canvasEl = '<div id="'+canvasId+'" style="padding: '+_tdPadding+'px;"></div>';

    appendBarcode($(cellEl),canvasEl);

    new QRCode(
        document.getElementById(canvasId),
        {
            text: barcodeText,
            width: _tdSize,
            height: _tdSize,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        }
    );
}
function setMultipleQrcode() {
    //resizeTableHeight();

    var col = _barcodeCol;
    var countRow = _sotable.countRows();
    //console.log("countRow="+countRow+"; col="+col);
    for( var row=0; row<countRow; row++ ) { //for each row (except the last one)
        var barcode = _sotable.getDataAtCell(row,col);
        if( barcode ) {
            //console.log("barcode="+barcode+"; row="+row+"; col="+col);
            setQrcode(barcode,row,col+1);
        }
    } //for each row
}

function jqueryQrcode(barcodeText,rowNumber,columnNumber) {
    if( barcodeText ) {
        var cellEl = _sotable.getCell(rowNumber, columnNumber);

        //var canvasId = "canvas-"+rowNumber+"-"+columnNumber;
        //var divEl = '<div> id="'+canvasId+'"></div>';
        //appendBarcode($(cellEl),divEl);
        //$("#"+canvasId).qrcode({width: 64,height: 64,text: barcodeText});

        $(cellEl).css('padding', _tdPadding);

        $(cellEl).qrcode({
            render: "canvas",
            //render: "table",
            width: _tdSize,
            height: _tdSize,
            text: barcodeText,
            //correctLevel: QRCode.CorrectLevel.H
        });
    }
}
function setMultipleJqueryQrcode() {
    //if( $("#formcycle").val() == "show" ) {
        resizeTableHeight();
    //}

    var col = _barcodeCol;
    var countRow = _sotable.countRows();
    //console.log("countRow="+countRow+"; col="+col);
    for( var row=0; row<countRow; row++ ) { //for each row (except the last one)
        var barcode = _sotable.getDataAtCell(row,col);
        if( barcode ) {
            barcode = barcode.trim();
            //console.log("barcode="+barcode+"; row="+row+"; col="+col);
            jqueryQrcode(barcode,row,col+1);
        }
    } //for each row
}


function getBarcodeImageSrcApi(barcodeText) {
    var code = "qrcode";
    var src = "http://bwipjs-api.metafloor.com/?bcid="+code+"&text="+barcodeText+"&includetext&scale=0.5";
    return src;
}
//https://github.com/metafloor/bwip-js/wiki/Online-Barcode-API
function setBarcodeImageApi(barcodeText,rowNumber,columnNumber) {
    var code = "qrcode";
    var img = '<img alt="Barcoded value '+barcodeText+'" src="http://bwipjs-api.metafloor.com/?bcid='+code+'&text='+barcodeText+'&includetext&scale=0.5">';
    img = "<p>"+img+"</p>";

    var cellEl = _sotable.getCell(rowNumber, columnNumber);
    //create canvas element in this TD
    //<canvas id="canvas" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>
    //var canvasId = "canvas-"+rowNumber+"-"+columnNumber;
    //var canvasEl = '<canvas id="'+canvasId+'" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>';

    //$(cellEl).append(img);
    appendBarcode($("#barcodeholder"),img);
    appendBarcode($(cellEl),img);
}

function setBarcodeCanvas(barcodeText,rowNumber,columnNumber) {
    //console.log("setBarcodeCanvas: barcodeText="+barcodeText+"; rowNumber="+rowNumber+"; columnNumber="+columnNumber);
    //Returns a TD element for the given row and column arguments, if it is rendered on screen.
    //Returns null if the TD is not rendered on screen (probably because that part of the table is not visible).
    var cellEl = _sotable.getCell(rowNumber, columnNumber);
    //create canvas element in this TD
    //<canvas id="canvas" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>
    var canvasId = "canvas-"+rowNumber+"-"+columnNumber;
    //var canvasEl = '<canvas id="'+canvasId+'" width=100 height=100 style="border:1px solid #fff;visibility:visible"></canvas>';
    var canvasEl = '<div><canvas id="'+canvasId+'" width=100 height=100></canvas></div>';
    canvasEl = "<p>"+canvasEl+"</p>";

    appendBarcode($(cellEl),canvasEl);

    //appendBarcode($("#barcodeholder"),canvasEl);

    render(canvasId,barcodeText);

    //$("#"+canvasId).show();
}
function getBarcodeImg(barcodeText,rowNumber,columnNumber) {
    //console.log("getBarcodeCanvasSrc: barcodeText="+barcodeText+"; rowNumber="+rowNumber+"; columnNumber="+columnNumber);
    //Returns a TD element for the given row and column arguments, if it is rendered on screen.
    //Returns null if the TD is not rendered on screen (probably because that part of the table is not visible).
    var cellEl = _sotable.getCell(rowNumber, columnNumber);
    //create canvas element in this TD
    //<canvas id="canvas" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>
    var canvasId = "canvas-"+rowNumber+"-"+columnNumber;
    var canvasEl = '<canvas id="'+canvasId+'" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>';
    canvasEl = "<p>"+canvasEl+"</p>";

    //$(cellEl).append(canvasEl);
    //appendBarcode($("#barcodeholder"),canvasEl);
    appendBarcode($(cellEl),canvasEl);

    render(canvasId,barcodeText);

    var canvas = document.getElementById(canvasId);
    var src = canvas.toDataURL('image/png');
    //console.log("src="+src);

    //$(canvas).remove();
    var imageEl = '<img src="' + src + '" height="42" width="42">';
    appendBarcode($(cellEl),imageEl);

    return src;
}

function getBarcodeCanvasSrc(barcodeText,rowNumber,columnNumber) {
    //console.log("getBarcodeCanvasSrc: barcodeText="+barcodeText+"; rowNumber="+rowNumber+"; columnNumber="+columnNumber);
    //Returns a TD element for the given row and column arguments, if it is rendered on screen.
    //Returns null if the TD is not rendered on screen (probably because that part of the table is not visible).
    var cellEl = _sotable.getCell(rowNumber, columnNumber);
    //create canvas element in this TD
    //<canvas id="canvas" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>
    var canvasId = "canvas-"+rowNumber+"-"+columnNumber;
    var canvasEl = '<canvas id="'+canvasId+'" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>';
    canvasEl = "<p>"+canvasEl+"</p>";

    //$(cellEl).append(canvasEl);
    appendBarcode($("#barcodeholder"),canvasEl);
    //appendBarcode($(cellEl),canvasEl);

    render(canvasId,barcodeText);

    var canvas = document.getElementById(canvasId);
    var src = canvas.toDataURL('image/png');
    //console.log("src="+src);

    //$(canvas).remove();
    //var imageEl = '<img src="' + src + '" height="42" width="42">';
    //appendBarcode($(cellEl),imageEl);

    return src;
}
function renderBarcodeCanvasById(barcodeText,rowNumber,columnNumber) {
    //console.log("renderBarcodeCanvasById: barcodeText="+barcodeText+"; rowNumber="+rowNumber+"; columnNumber="+columnNumber);
    //<canvas id="canvas" width=1 height=1 style="border:1px solid #fff;visibility:hidden"></canvas>
    var canvasId = "canvas-"+rowNumber+"-"+columnNumber;
    //console.log("renderBarcodeCanvasById: canvasId="+canvasId);

    //$(cellEl).append(canvasEl);
    //appendBarcode($("#barcodeholder"),canvasEl);
    //appendBarcode($(cellEl),canvasEl);

    render(canvasId,barcodeText);
}

function setBarcodeMultipleCanvas(col) {
    var countRow = _sotable.countRows();
    //console.log("countRow="+countRow+"; col="+col);
    for( var row=0; row<countRow; row++ ) { //for each row (except the last one)
        var barcode = _sotable.getDataAtCell(row,col);
        if( barcode ) {
            //console.log("barcode="+barcode+"; row="+row+"; col="+col);
            //render(canvasId,barcodeText);
            setBarcodeCanvas(barcode,row,col+1);
            //getBarcodeImg(barcode,row,col+1);
        }
    } //for each row
}


function appendBarcode(holderEl,canvasEl) {
    holderEl.append(canvasEl);
}

function transresTableBarcodeGeneration( barcodeField ) {
    //console.log("generate barcode");
    //generate barcode
    var barcode = null;

    //testing dummy image
    if(0) {
        //var image = '<img src="https://www.imgonline.com.ua/examples/random-pixels.jpg" height="42" width="42">';
        //var image = "https://www.imgonline.com.ua/examples/random-pixels.jpg";
        var image = "https://arifdiyanto.files.wordpress.com/2015/11/qrcodeuk.gif";
        //console.log("image=" + image);
    }

    //var imageEl = '<img src="' + image + '" height="42" width="42">';
    //$("#test-barcode-image").html(imageEl);

    //return image;

    //$("#test-barcode-image").html(image);
    //return image;

    //put barcode image to '.table-barcode-image'
    //barcodeField.closest(".table-barcode-image").html(image);

    // var canvas = document.createElement('canvas');
    // bwipjs(canvas, options, function(err, cvs) {
    //     console.log("bwipjs function");
    //     if (err) {
    //         // handle the error
    //         console.log(err);
    //     } else {
    //         console.log("set barcode image");
    //         // Don't need the second param since we have the canvas in scope...
    //         document.getElementById('test-datamatrix').src = canvas.toDataURL('image/png');
    //     }
    // });

    image = transresGenerateBarcode();

    return image;
}
// const BWIPJS  = require('./bwipjs');
// const BWIPP   = require('./bwipp');
// const fontlib = require('./node-fonts');
//use demo: https://github.com/metafloor/bwip-js/blob/master/demo.html
//http://jsfiddle.net/josh3736/tAaBe/
//QR alternative: https://davidshimjs.github.io/qrcodejs/
function transresGenerateBarcode() {
    // Initialize a barcode writer object.  This is the interface between
    // the low-level BWIPP code, the font-manager, and the Bitmap object.
    // The `fontlib` parameter is the font-manager, either the FreeType
    // interface or the bitmapped-fonts interface.
    // The boolean `monochrome` flag indicates whether to use
    // anti-aliased (false) or monochrome (true) font rendering.
    var bw = new BWIPJS(fontlib, false /*use anti-aliased fonts*/);

    // Add a bitmap instance
    bw.bitmap(new Bitmap);

    // Set the x,y scaling factors
    bw.scale(2, 2);

    // Create an options object.  See the bwipjs and BWIPP documentation
    // for possible values.
    // You can use any plain JavaScript values.  Numbers, bools and strings.
    var opts = {
        parsefnc:true,
        includetext:true,
        alttext:"(00)1234567890",
    };

    // Call into the BWIPP cross-compiled code.   BWIPP() is a factory
    // method that returns a function object.  You can call the
    // function object multiple times (and reuse the BWIPJS object as
    // well), but you will likely need to create a new Bitmap object
    // prior to each call.
    try {
        // This call is synchronous and can be CPU intensive.
        // Will throw if a runtime error is encountered
        BWIPP()(bw, 'code128', "^FNC1001234567890", opts);

        // If you are using bitmapped fonts with asynchronous loading (browser),
        // you must allow the font-manager to load any required fonts.
        // Node.js does not use this and calls bw.render() directly.
        bwipjs_fonts.loadfonts(function(err) {
            if (err) {
                // handle the font loading error
            } else {
                // Tell bwip-js to render the image.  This will invoke
                // the interfaces on your Bitmap object.  The callback is passed
                // to Bitmap.finalize().  That interface should call it when done,
                // supplying any expected parameters.
                bw.render(callback);
            }
        });
    } catch (e) {
        // handle error
    }
}

function transresBarcodeParser( rowNumber, barcodeStr ) {
    //S13-20926 A1 5 08/12/14

    var barcodeArr = barcodeStr.split(" ");
    //console.log("barcodeArr len="+barcodeArr.length);

    //1) get Accession
    if( barcodeArr.length > 1 ) {
        var accession = barcodeArr[0];
        //console.log("accession="+accession);
        _sotable.setDataAtCell(rowNumber,1,accession);

        var partBlock = barcodeArr[1];
        //console.log("partBlock.length="+partBlock.length);

        //A11
        if( partBlock.length > 1 ) {
            //console.log("Part="+partBlock.charAt(0));
            _sotable.setDataAtCell(rowNumber, 2, partBlock.charAt(0)); //get 'A'

            var blockId = partBlock.substring(1); //remove the first character from 'A11' => '11'
            _sotable.setDataAtCell(rowNumber, 3, blockId);
        }
    }
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


