/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/12/14
 * Time: 3:36 PM
 * To change this template use File | Settings | File Templates.
 */


$(document).ready(function() {

    var colHeader = [
            "ID", "Accession Type", "Accession Number", "Part Name", "Block Name", "Stain", "Magnification", "Diagnosis", "Reason for Scan/Note",   //9
            "MRN Type", "MRN", "Name", "Sex", "DOB", "Age", "Clinical History",  //7
            "Procedure Type",   //1
            "Source Organ", "Gross Description", "Differential Diagnoses", "Type of Disease",   //4
            "Title", "Slide Type", "Microscopic Description", "Results of Special Stains", "Relevant Scanned Images",   //5
            "Region to scan"    //1
    ];
    //total: 27


    var data = new Array();

    var rows = 5;//51;//501;

    console.log( "header length="+colHeader.length );

    var rowElements = new Array();
    for(var i = 0; i < colHeader.length-1; i++) {
        rowElements.push(' ');
    }

    for( var i=1; i<rows; i++ ) {
        var index = new Array();
        index = [i];
        var row = index.concat(rowElements);
        data.push(row);
    }

    $("#multi-dataTable").handsontable({
        data: data,
        colHeaders: colHeader,
        minSpareRows: 1,
        contextMenu: true,
        manualColumnMove: true,
        manualColumnResize: true,
        stretchH: 'all'
    });


    //var Dragdealer = require('dragdealer').Dragdealer;
    //new Dragdealer('top-scrollbar-handsontable');

    //var tableSliderEl = $('.dragdealer').first();
    //console.log('slider width='+tableSliderEl.width());

    //var topSlider = $('#top-scrollbar-handsontable');
    //topSlider.width(tableSliderEl.width());
    //topSlider.append(tableSliderEl);

    $('#multi-dataTable').doubleScroll();

});