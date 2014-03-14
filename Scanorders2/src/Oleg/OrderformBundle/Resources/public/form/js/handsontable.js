/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/12/14
 * Time: 3:36 PM
 * To change this template use File | Settings | File Templates.
 */


$(document).ready(function() {

    var data = [
        [   "ID", "Accession Type", "Accession Number", "Part Name", "Block Name", "Stain", "Magnification", "Diagnosis", "Reason for Scan/Note",
            "MRN Type", "Mrn", "Name", "Sex","DOB", "Age", "Clinical History",
            "Procedure Type",
            "Source Organ", "Relevant Paper or Abstract", "Gross Description", "Differential Diagnoses", "Type of Disease",
            "Title", "Slide Type", "Microscopic Description", "Results of Special Stains", "Relevant Scanned Images",
            "Region to scan"
        ]
//        ["2008", 10, 11, 12, 13],
//        ["2009", 20, 11, 14, 13],
//        ["2010", 30, 15, 12, 13]
    ];

    for( var i=1; i<501; i++ ) {
        var row = new Array();
        row = [i,'', '', '', ''];
        data.push(row);
    }

    $("#multi-dataTable").handsontable({
        data: data,
        minSpareRows: 1,
        colHeaders: true,
        contextMenu: true,
        manualColumnMove: true,
        manualColumnResize: true,
        stretchH: 'all'
    });

});