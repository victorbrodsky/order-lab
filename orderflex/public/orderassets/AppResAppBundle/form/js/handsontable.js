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

var _ethnicities = [];
var _ethnicities_simple = [];

var _resapps = [];
var _resapps_simple = [];

var _actions_simple = [];
var _actions_duplicate = []; //action with "Update" only

//var _tdSize = 64;
//var _tdSize = 26;
var _tdSize = 36;
//var _tdSize = 56;
var _tdPadding = 5;
var _rowHeight =  _tdSize + 2*_tdPadding;

//total 33
var _columnData_scanorder = [];

var _seasonStartDate = null;
var _seasonEndDate = null;
var _residencyStartDate = null;
var _residencyEndDate = null;

function resappDisableRow(row,status) {
    if( _sotable ) {

        //var addClass = 'ht-validation-add';
        //var updateClass = 'ht-validation-update';
        //var dontaddClass = 'ht-validation-dontadd';

        var columnsLen = _columnData_scanorder.length;
        //console.log("columnsLen=" + columnsLen + ", row=" + row);
        for (var j = 1; j < columnsLen; j++) {
            //console.log("columns j=" + j);
            if( status == 'disable' ) {
                _sotable.getCellMeta(row, j).readOnly = true;
                //_sotable.getCellMeta(row, j).backgroundColor = dontaddClass;
                //_sotable.setCellMeta(row, j, 'className','dontaddClass');
                //$(_sotable.getCell(row, j)).css({"background-color": "#F2DEDE"})
            }
            if( status == 'enable' ) {
                _sotable.getCellMeta(row, j).readOnly = false;
                //_sotable.getCellMeta(row, j).backgroundColor = addClass;
                //_sotable.setCellMeta(row, j, 'className','addClass');
                //$(_sotable.getCell(row, j)).css({"background-color": "lightgreen"})
            }
        }
    }
}
var actionRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    //console.log("row="+row+", col="+col+",value="+value);

    Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);

    var addClass = 'ht-validation-add';
    var updateClass = 'ht-validation-update';
    var dontaddClass = 'ht-validation-dontadd';
    
    //var parentTr = $(td).parent('tr');
    //console.log("parentTr:");
    //console.log(parentTr);
    // var parentTr = parent.find('tr');
    // console.log("parentTr:");
    // console.log(parentTr);
    // var parentTr2 = parent[0];
    // console.log("parentTr2:");
    // console.log(parentTr2);

    //console.log("actionRenderer:"+value);
    if( value+"" == "Do not add" ) {
        //var cellBackgroundColor = "#ffcccc";
        //var cellClass = 'ht-validation-dontadd';

        //$(parentTr).css( "background-color", "red" );
        // $(parentTr).removeClass(addClass);
        // $(parentTr).removeClass(updateClass);
        // $(parentTr).addClass(dontaddClass);

        $(td).addClass(dontaddClass);
        $(td).removeClass(addClass);
        $(td).removeClass(updateClass);

        //console.log("actionRenderer: Do not add");
        resappDisableRow(row,'disable'); //testing
    }
    if( value+"" == "Update PDF & ID Only" ) {
        //var cellClass = "ht-validation-update";

        //$(parentTr).css( "background-color", "blue" );
        // $(parentTr).removeClass(addClass);
        // $(parentTr).removeClass(dontaddClass);
        // $(parentTr).addClass(updateClass);

        $(td).addClass(updateClass);
        $(td).removeClass(addClass);
        $(td).removeClass(dontaddClass);

        resappDisableRow(row,'disable');

        //TODO: enable field "ERAS Application ID" if empty
    }
    if( value+"" == "Create New Record" ) {
        //return false;
        //var cellClass = "ht-validation-add";

        //$(parentTr).css( "background-color", "green" );
        // $(parentTr).removeClass(updateClass);
        // $(parentTr).removeClass(dontaddClass);
        // $(parentTr).addClass(addClass);

        $(td).addClass(addClass);
        $(td).removeClass(updateClass);
        $(td).removeClass(dontaddClass);

        resappDisableRow(row,'enable');
    }

    //Add to ...
    //if( value.indexOf("Add to ") !== -1 ) {
    if( value && value.includes("Add to ") ) {
        $(td).addClass(updateClass);
        $(td).removeClass(addClass);
        $(td).removeClass(dontaddClass);

        resappDisableRow(row,'disable');
    }

    //if( col != 0 ) {
        //instance.getCellMeta(row, col).readOnly = true;
    //}
    //var thiscell = instance.getCell(row,col);
    //thiscell.style.backgroundColor = cellBackgroundColor;

    // var columnsLen = _columnData_scanorder.length;
    // for( var j = 0; j <= columnsLen; j++ ) {
    //     console.log("columns j="+j);
    //
    //     if( j+1 <= columnsLen) {
    //         instance.getCellMeta(row, j + 1).readOnly = true;
    //     }
    //
    //     //var thiscell = instance.getCell(row,j); //handsontable("getCell", row, j);
    //     //thiscell.style.backgroundColor = cellBackgroundColor;
    // }
};

// function validateActionCell( row, col, value ) {
//     if( _sotable == null ) {
//         _sotable = $(_htableid).handsontable('getInstance');
//     }
//
//     console.log("row="+row+", col="+col+",value="+value);
//
//     //var columnsLen = _columnData_scanorder.length;
//     //console.log("columnsLen="+columnsLen);
//     //var columnsLen = 22;
//     //console.log("columnsLen="+columnsLen);
//     if( value == "Do not add" ) {
//         var cellBackgroundColor = "#ffcccc";
//     }
//     if( value == "Update PDF" ) {
//         var cellBackgroundColor = "lightblue";
//         // for( var j = 0; j <= columnsLen; j++ ) {
//         //     console.log("columns j="+j);
//         //     _sotable.getCellMeta(rowNumber, j + 1).readOnly = true;
//         // }
//     }
//     if( value == "Create New Record" ) {
//         return false;
//         var cellBackgroundColor = "lightgreen";
//     }
//     //var thiscell = $(_htableid).handsontable("getCell", rowNumber, columnNumber);
//     //thiscell.style.backgroundColor = cellBackgroundColor;
//
//     return true;
// }


function getResidencytracks() {

    var cycle = 'new';

    //var url = getCommonBaseUrl("util/common/generic/"+"residencytracks","employees");
    //console.log("get CommonBaseUrl getAntobodies url="+url);
    var url = Routing.generate('employees_get_generic_select2', {'name': 'residencytracks'});
    //console.log("Routing getAntobodies url="+url);

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
            async: false //asyncflag
        }).done(function(data) {
            _residencytracks = data;
        });
    }
}
function getResidencyEthnicities() {
    var url = Routing.generate('resapp_get_ethnicities');
    //console.log("run url="+url);

    if( _ethnicities.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: false //asyncflag
        }).done(function(data) {
            _ethnicities = data;
        });
    }
}
function getResApplicationsForThisYear() {
    var url = Routing.generate('resapp_get_resapps_current_year');
    //console.log("run url="+url);

    if( _resapps.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: false //asyncflag
        }).done(function(data) {
            _resapps = data;
        });
    }
}
function getResidencyStartEndDates() {
    var url = Routing.generate('resapp_get_academic_start_end_dates');
    console.log("run url="+url);
    console.log("_seasonStartDate="+_seasonStartDate);


    //if( _seasonStartDate == null && _seasonEndDate == null && _residencyStartDate == null && _residencyEndDate ) {
    if( _seasonStartDate && _seasonEndDate && _residencyStartDate && _residencyEndDate ) {
        //skip because already called
    } else {
        console.log("start ajax _seasonStartDate="+_seasonStartDate);
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: false //asyncflag
        }).done(function(data) {
            _seasonStartDate = data['Season Start Date'];
            _seasonEndDate = data['Season End Date'];
            _residencyStartDate = data['Residency Start Date'];
            _residencyEndDate = data['Residency End Date'];
            console.log("_seasonStartDate="+_seasonStartDate+", _residencyStartDate="+_residencyStartDate);
        });
    }
}

function ajaxFinishedCondition() {

    //console.log('_residencytracks.length='+_residencytracks.length+" =? _residencytracks_simple.length="+_residencytracks_simple.length);
    //return true; //testing

    // if( !(_residencytracks.length > 0) ) {
    //     //console.log('NULL _residencytracks.length='+_residencytracks.length);
    // }

    var done = 0;

    if( _actions_simple.length == 0 ) {
        _actions_simple.push("Do not add");
        _actions_simple.push("Create New Record");

        //_actions_simple.push("Update PDF & ID Only"); //testing: remove for prod
        //_actions_simple.push(null);

        done++;
    }

    if( _actions_duplicate.length == 0 ) {
        _actions_duplicate.push("Do not add");
        _actions_duplicate.push("Create New Record");
        _actions_duplicate.push("Update PDF & ID Only");
        done++;
    }

    // if( _actions_complex.length == 0 ) {
    //
    //     for( var i = 0; i < _actions_simple.length; i++ ) {
    //         _actions_complex[i] = _actions_simple[i];
    //     }
    //     _actions_complex.push("111");
    //     _actions_complex.push("222");
    //     _actions_complex.push("333");
    //     _actions_complex.push("444");
    //     _actions_complex.push("555");
    //
    //     done++;
    // }

    if( _residencytracks.length > 0 ) {

        if(
            _residencytracks_simple.length >= _residencytracks.length
        ) {
            //return true;
            done++;
        } else {
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

            //return true;
            done++;
        }

    } else {
        //return false;
    }


    if( _ethnicities.length > 0 ) {

        if(
            _ethnicities_simple.length >= _ethnicities.length
        ) {
            //return true;
            done++;
        } else {
            _ethnicities_simple.push(""); //add default empty _ethnicities
            for(var i = 0; i < _ethnicities.length; i++) {
                var ethnicityName = _ethnicities[i];
                //console.log('ethnicityName='+ethnicityName);
                _ethnicities_simple.push(ethnicityName);
            }
            //console.log("_ethnicities_simple:");
            //console.log(_ethnicities_simple);

            //return true;
            done++;
        }
    } else {
        //return false;
    }

    if( _resapps.length > 0 ) {

        if(
            _resapps_simple.length >= _resapps.length
        ) {
            //return true;
            done++;
        } else {
            //_resapps_simple.push(""); //add default empty _resapps
            _resapps_simple.push("Do not add");
            _resapps_simple.push("Create New Record");
            _resapps_simple.push("Update PDF & ID Only");
            for(var i = 0; i < _resapps.length; i++) {
                var resappInfo = _resapps[i];
                //console.log('resappInfo='+resappInfo);
                _resapps_simple.push(resappInfo);
            }
            //console.log("_resapps_simple:");
            //console.log(_resapps_simple);

            //return true;
            done++;
        }
    } else {
        //return false;
    }

    if( _seasonStartDate && _seasonEndDate && _residencyStartDate && _residencyEndDate ) {
        done++;
    } else {
        //not yet
    }

    if( done >= 5 ) {
        return true;
    }

    return false;
}

function resappMakeColumnData() {

    var defaultActionIndex = 0;
    var defaultEthnicityIndex = 0;

    //get default residency track
    var defaultResidencytrackIndex = 1;
    var defaultResidencytrackDefault = $('#default-residency-track').val();
    //console.log("defaultResidencytrackDefault="+defaultResidencytrackDefault);
    if( defaultResidencytrackDefault ) {
        for(var i = 0; i < _residencytracks_simple.length; i++) {
            //console.log(_accessiontypes_simple[i]+"=?"+Residencytrack);
            if( _residencytracks_simple[i] == defaultResidencytrackDefault ) {
                defaultResidencytrackIndex = i;
            }
        }
    }
    // var defaultResidencytrackIndex = 1;

    //$year = $this->getYear($cellValue);
    //$cellValue = "07/01/".$year;
    //Take in consideration before or after July 1st?
    // var year = new Date().getFullYear();
    // var seasonStartDate = "07/01/"+year;
    // year = year + 1;
    // var seasonEndDate = "06/30/"+year;
    //
    // var nextYear = year + 1;
    // var residencyStartDate = "07/01/"+nextYear;
    // nextYear = nextYear + 1;
    // var residencyEndDate = "06/30/"+nextYear;

    var seasonStartDate = _seasonStartDate;
    var seasonEndDate = _seasonEndDate;
    var residencyStartDate = _residencyStartDate;
    var residencyEndDate = _residencyEndDate;

    _columnData_scanorder = [

        {
            header:'Action',
            default: defaultActionIndex,
            columns: {
                type: 'autocomplete',
                source: _actions_simple,
                strict: false,
                filter: false,
                renderer: actionRenderer
            }
        },

        { header:'AAMC ID', columns:{} },

        { header:'Status', columns:{} },

        { header:'ERAS Application', columns:{} },

        { header:'ERAS Application ID', columns:{} },
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

        { header:'First Name', columns:{} },
        { header:'Last Name', columns:{} },
        { header:'Middle Name', columns:{} },
        { header:'Preferred Email', columns:{} },
        { header:'Medical School Graduation Date', columns:{} },
        { header:'Medical School Name', columns:{} },
        { header:'Degree', columns:{} },
        
        { header:'USMLE Step 1 Score', columns:{} },
        { header:'USMLE Step 2 CK Score', columns:{} },
        { header:'USMLE Step 2 CS Score', columns:{} },
        { header:'USMLE Step 3 Score', columns:{} },

        { header:'COMLEX Level 1 Score', columns:{} },
        { header:'COMLEX Level 2 CE Score', columns:{} },
        { header:'COMLEX Level 2 PE Score', columns:{} },
        { header:'COMLEX Level 3 Score', columns:{} },
        
        { header:'Country of Citizenship', columns:{} },
        { header:'Visa Status', columns:{} },

        // { header:'Is the applicant a member of any of the following groups?', columns:{} },
        {
            header:'Is the applicant a member of any of the following groups?',
            default: defaultEthnicityIndex,
            columns: {
                type: 'autocomplete',
                source: _ethnicities_simple,
                strict: false,
                filter: false,
            }
        },

        { header:'Number of first author publications', columns:{} },
        { header:'Number of all publications', columns:{} },
        { header:'AOA', columns:{} },
        { header:'Couple’s Match', columns:{} },
        { header:'Post-Sophomore Fellowship', columns:{} },

        { header:'Application Season Start Date', default:seasonStartDate, columns:{} },
        { header:'Application Season End Date', default:seasonEndDate, columns:{} },

        { header:'Expected Residency Start Date', default:residencyStartDate, columns:{} },
        { header:'Expected Graduation Date', default:residencyEndDate, columns:{} },

        { header:'Previous Residency Start Date', columns:{} },
        { header:'Previous Residency Graduation/Departure Date', columns:{} },
        { header:'Previous Residency Institution', columns:{} },
        { header:'Previous Residency City', columns:{} },
        { header:'Previous Residency State', columns:{} },
        { header:'Previous Residency Country', columns:{} },
        { header:'Previous Residency Track', columns:{} },
        
        // { header:'ERAS Application ID', columns:{} },
        //{ header:'ERAS Application', columns:{} },
        //{ header:'Status', columns:{} },
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
                if( 'source' in _columnData_scanorder[ii]['columns'] ) {
                    rowElement[ii] = _columnData_scanorder[ii]['columns']['source'][index];
                } else {
                    rowElement[ii] = index; //set default
                }
                //console.log('assign rowElement='+rowElement[ii]);
            } else {
                //console.log('assign rowElement is null');
                rowElement[ii] = null;
            }

            //load data
            //console.log('load data for row='+i);
            //if( typeof handsometableDataArr != 'undefined' ) {
            if( handsometableDataArr &&
                typeof handsometableDataArr != 'undefined' &&
                handsometableDataArr.length > 0
            ) {
                var headerTitle = _columnData_scanorder[ii]['header'];
                //console.log('headerTitle='+headerTitle);
                //console.log( handsometableDataArr[i-1] );
                if( typeof headerTitle != 'undefined' &&
                    typeof handsometableDataArr[i-1] != 'undefined' &&
                    headerTitle != '' && (i-1<handsometableDataArr.length) &&
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
                            //var testArr = [1,2,3];
                            //rowElement[ii] = testArr; //cellValue;
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
    //console.log("columnsType:");
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
            var headerTitle = null;
            var cellId = null;

            // if( tableFormCycle == 'show' ) {
            //     cellProperties.readOnly = true;
            // }

            //console.log("c="+c+"; r="+r);                      //c=7
            //console.log(_columnData_scanorder[c]);    //_columnData_scanorder[c].header="Barcode"
            if( c > 0 ) {
                headerTitle = _columnData_scanorder[c]['header'];
                //console.log("c="+c+"; r="+r+"; headerTitle="+headerTitle);
                if( typeof headerTitle != 'undefined' && headerTitle != '' &&
                    handsometableDataArr && typeof handsometableDataArr != 'undefined' && typeof handsometableDataArr[r] != 'undefined' &&
                    typeof handsometableDataArr[r][headerTitle] != 'undefined' &&
                    handsometableDataArr[r][headerTitle] != null
                ) {
                    cellId = handsometableDataArr[r][headerTitle]["id"];
                    //console.log("cellId="+cellId);
                    cellProperties.id = cellId;
                    //console.log('cellProperties:');
                    //console.log(cellProperties);
                }
            }

            //https://github.com/handsontable/handsontable/issues/4428
            //http://jsfiddle.net/handsoncode/wp7ynbng/1/
            //http://jsfiddle.net/e2rxvkb0/
            //Cell does not keep chosen value, require to choose second time. Use actionRenderer?
            //Status id = -1, value = "No match found"
            //if( headerTitle && headerTitle == "Status" ) {
                //console.log("headerTitle=" + headerTitle + ", cellId=" + cellId);
            //}
            if(
                c == 0 &&
                typeof handsometableDataArr[r] != 'undefined' &&
                typeof handsometableDataArr[r]["Status"] != 'undefined' &&
                typeof handsometableDataArr[r]["Status"]["id"] != 'undefined'
                //&& typeof handsometableDataArr[r]["ERAS Application"] != 'undefined' && handsometableDataArr[r]["ERAS Application"].length > 0 &&
                //typeof handsometableDataArr[r]["ERAS Application"]["id"] != 'undefined'
            ) {
            //if( headerTitle && headerTitle == "Action" ) {
                //console.log("c="+c+"; r="+r);
                var issueCellId = handsometableDataArr[r]["Status"]["id"];
                //var erasFileCellId = handsometableDataArr[r]["ERAS Application"]["id"];
                //var erasFileCellValue = handsometableDataArr[r]["ERAS Application"]["value"];
                //console.log("headerTitle=" + headerTitle + ", issueCellId=" + issueCellId+", erasFileCellId="+erasFileCellId+", erasFileCellValue="+erasFileCellValue);
                //console.log("headerTitle=" + headerTitle + ", issueCellId=" + issueCellId);
                if( issueCellId && issueCellId == -1 ) {
                //if( issueCellId && issueCellId == -1 && erasFileCellId && erasFileCellValue ) {
                    cellProperties.source = _resapps_simple; //overwrite with extended choices including "Add to John Smith’s application (ID 1234)"
                }
                
                if( issueCellId && issueCellId == -2 ) {
                    cellProperties.source = _actions_duplicate; //overwrite with extended choices including "Update PDF & ID Only"
                }
                
            }
            // if( headerTitle && headerTitle == "Status" && cellId && cellId == -1 ) {
            //     console.log("Use extended choices!!!");
            //     cellProperties.source = _resapps_simple; //extended choices including "Add to John Smith’s application (ID 1234)"
            // } else {
            //     cellProperties.source = _actions_simple;
            // }

            //var columns = this.getSettings().columns;
            //var columnsLen = columns.length;
            // var columnsLen = 22;
            // console.log("columnsLen="+columnsLen);
            // var newValue = $(_htableid).handsontable("getCell", r, 0);
            // if( newValue == "Do not add" ) {
            //     var cellBackgroundColor = "#ffcccc";
            // }
            // if( newValue == "Update PDF" ) {
            //     var cellBackgroundColor = "lightblue";
            // }
            // if( newValue == "Create New Record" ) {
            //     var cellBackgroundColor = "lightgreen";
            // }
            // cellProperties.style = 'style="backgroundColor: red"';
            
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
            return; //don't save this change

            //console.log("source="+source);
            if (source === 'loadData') {
                //console.log("ignore source="+source);
                return; //don't save this change
            }

            if (change != null) {
                //var changeData = change[0];
                //console.log("changeData:");
                //console.log(changeData);

                // var rowNumber = changeData[0];
                // var columnNumber = changeData[1];
                // var oldValue = changeData[2];
                // var newValue = changeData[3];
                //console.log("prop="+prop);
                //console.log("columnNumber="+columnNumber+", rowNumber="+rowNumber+": oldValue="+oldValue+"; newValue="+newValue);

                //var columns = this.getSettings().columns;
                //var columnsLen = columns.length;
                // var columnsLen = _columnData_scanorder.length;
                // console.log("columnsLen="+columnsLen);
                // //var columnsLen = 22;
                // //console.log("columnsLen="+columnsLen);
                // if( newValue == "Do not add" ) {
                //     var cellBackgroundColor = "#ffcccc";
                // }
                // if( newValue == "Update PDF" ) {
                //     var cellBackgroundColor = "lightblue";
                //     for( var j = 0; j <= columnsLen; j++ ) {
                //         console.log("columns j="+j);
                //         _sotable.getCellMeta(rowNumber, j + 1).readOnly = true;
                //     }
                // }
                // if( newValue == "Create New Record" ) {
                //     var cellBackgroundColor = "lightgreen";
                // }
                // var thiscell = $(_htableid).handsontable("getCell", rowNumber, columnNumber);
                // thiscell.style.backgroundColor = cellBackgroundColor;

            //     //var columns = this.getSettings().columns;
            //     //var columnsLen = columns.length;
            //     var columnsLen = 22;
            //     console.log("columnsLen="+columnsLen);
            //     if( newValue == "Do not add" ) {
            //         var cellBackgroundColor = "#ffcccc";
            //         for( var j = 0; j < columnsLen; j++ ) {
            //             console.log("columns j="+j);
            //             var thiscell = $(_htableid).handsontable("getCell", rowNumber, j);
            //             thiscell.style.backgroundColor = cellBackgroundColor;
            //         }
            //         // var cellBackgroundColor = "#ffcccc";
            //         // //rowNumber = 0;
            //         // var j = 2;
            //         // var thiscell = $(_htableid).handsontable("getCell", rowNumber, j);
            //         // thiscell.style.backgroundColor = cellBackgroundColor;
            //     }
            //     if( newValue == "Update PDF" ) {
            //         var cellBackgroundColor = "lightblue";
            //         for( var j = 0; j < columnsLen; j++ ) {
            //             console.log("columns j="+j);
            //             var thiscell = $(_htableid).handsontable("getCell", rowNumber, j);
            //             thiscell.style.backgroundColor = cellBackgroundColor;
            //         }
            //         //var thiscell = $(_htableid).handsontable("getCell", columnNumber, rowNumber);
            //         //checkcell.style.color = "red";
            //         //checkcell.style.backgroundColor = "red"; //'#F2DEDE';
            //         //$(_htableid).setCellMeta(rowNumber, columnNumber, 'className', 'res-app-status-legend-priority');
            //         // for( var j = 0; j < columns.length; j++ ) {
            //         //     //this.handsontable("getCell", columnNumber, rowNumber).css('background', 'blue');
            //         //     rowNumber = 2;
            //         //     j = 2;
            //         //     var thiscell = $(_htableid).handsontable("getCell", rowNumber, j);
            //         //     thiscell.style.backgroundColor = "lightblue";
            //         // }
            //         // var cellBackgroundColor = "lightblue";
            //         // //rowNumber = 0;
            //         // var j = 2;
            //         // var thiscell = $(_htableid).handsontable("getCell", rowNumber, j);
            //         // thiscell.style.backgroundColor = cellBackgroundColor;
            //     }
            //     if( newValue == "Create New Record" ) {
            //         var cellBackgroundColor = "lightgreen";
            //         for( var j = 0; j < columnsLen; j++ ) {
            //             console.log("columns j="+j);
            //             var thiscell = $(_htableid).handsontable("getCell", rowNumber, j);
            //             thiscell.style.backgroundColor = cellBackgroundColor;
            //         }
            //         // //rowNumber = 0;
            //         // var j = 2;
            //         // var thiscell = $(_htableid).handsontable("getCell", rowNumber, j);
            //         // thiscell.style.backgroundColor = cellBackgroundColor;
            //
            //         //$(_htableid).setCellMeta(rowNumber, columnNumber, 'className', 'res-app-status-legend-add');
            //         // for( var j = 0; j < columns.length; j++ ) {
            //         //     //this.handsontable("getCell", columnNumber, rowNumber).css('background', 'lightgreen');
            //         //     rowNumber = 2;
            //         //     j = 2;
            //         //     var thiscell = $(_htableid).handsontable("getCell", rowNumber, j);
            //         //     thiscell.style.backgroundColor = "lightgreen";
            //         // }
            //     }

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
                    if( 'source' in _columnData_scanorder[col]['columns'] ) {
                        value = _columnData_scanorder[col]['columns']['source'][indexSource];
                    } else {
                        value = indexSource;
                    }
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

    //$("#upload-extract-button").hide();
    //$("#uploading-extracting-message").hide();

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
    if( countRow > 15 ) {
        countRow = 15;
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
    //console.log("heightRow="+heightRow+", countRow="+countRow);
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

function resappValidateHandsonTable() {
    //console.log("resapp validateHandsonTable");

    if( !_sotable ) {
        return "Logical Error: table does not exists";
    }

    //resHideBtn();
    var validationError = null;
    _rowToProcessArr = [];

    var countRow = _sotable.countRows();
    //console.log("countRow="+countRow);
    for( var row=0; row<countRow-1; row++ ) { //for each row (except the last one)
        //console.log("row="+row);
        _rowToProcessArr.push(row);
    } //for each row

    //get rows data from _rowToProcessArr
    validationError = resappAssignDataToDatalocker();

    //resShowBtn();

    //console.log("END !!!!!!!!!!!");
    //return true;

    return validationError;
}

//get rows data from _rowToProcessArr and assign this to datalocker field
function resappAssignDataToDatalocker() {

    var validationError = [];
    var headers = _sotable.getColHeader();

    //get rows data from _rowToProcessArr
    var data = [];
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

            //validate
            //If “Create New Record” is selected and a record for the person already exists (search for the Last Name + First Name among
            // the existing applications in the current year’s applications without statuses of Hidden and Archived),
            // before beginning the bulk import, show a modal:
            //“Applications for LastName1 FirstName1, LastName2 FirstName2, … already exist in the system.
            // Would you like to create new (possibly duplicate) records for these applications?” (Yes) (No)
            //if(  )
            //validationError = validationError + "Test Error ";
            // if( col == 0 && cellValue == "Create New Record" ) {
            //     //call server to verify for duplicate "check for duplicate" (checkDuplicate: getDuplicateTableResApps and getDuplicateDbResApps)
            //     validationError = validationError + "Test Error 'Create New Record' row "+i+"<br>";
            // }

        }

        //validationError = validationError + "Test Error row "+i+"<br>";

        data.row.push(rowArr);
    }//for
    //console.log(data);

    // if( _btnClickedName != null ) {
    //     $("#oleg_orderformbundle_messagetype_clickedbtn").val( _btnClickedName );
    // }

    //var validationFieldsError = resappValidateFieldsHandsonTable(data);

    //provide table data to controller
    //http://itanex.blogspot.com/2013/05/saving-handsontable-data.html
    var jsonstr = JSON.stringify(data);
    //var jsonstr = data;
    //console.log("jsonstr:");
    //console.log(jsonstr);

    var validationError = resappCheckDuplicate(jsonstr);

    $("#oleg_resappbundle_bulkupload_datalocker").val( jsonstr );
    
    //var validationError = {validationDuplicateError:validationDuplicateError, validationFieldsError:validationFieldsError};

    return validationError;
}

//Do it in one ajax request for all "Create New Record"
function resappCheckDuplicate(jsonstr) {
    var validationError = "DefaultTestError";
    var url = Routing.generate('resapp_check_duplicate');
    $.ajax({
        type: "POST",
        url: url,
        timeout: _ajaxTimeout,
        async: false, //asyncflag
        //contentType: 'application/json',
        //dataType: 'json',
        data: {tabledata: jsonstr}
    }).done(function(validationResult) {
        validationError = validationResult; //"Test Error";
    });

    return validationError;
}

// //use
// function resappValidateFieldsHandsonTable(data) {
//
//     for( var i=0; i<_rowToProcessArr.length; i++ ) {
//
//     }
//
//     //testing
//     return "First Name is empty";
// }

