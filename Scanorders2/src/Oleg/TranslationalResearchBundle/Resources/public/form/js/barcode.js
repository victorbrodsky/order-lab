/**
 * Created by ch3 on 7/16/2018.
 */

function drawBarcodeImages() {
    drawBarcodeImagesJqueryQrcode();
    //drawBarcodeImagesQRCode();

    drawIdBarcode();
}

function drawIdBarcode() {
    var barcodeText = $("#work-request-id").text();
    if( barcodeText ) {
        barcodeText = barcodeText.trim();
        console.log("barcodeText=" + barcodeText);

        //var canvasId = "canvas-barcode-id";
        //var canvasEl = '<div><canvas id="'+canvasId+'" width=100 height=100></canvas></div>';
        //canvasEl = "<p>"+canvasEl+"</p>";
        //$("#id-barcode").append(canvasEl);

        render("id-barcode",barcodeText,"code128");
        //render("id-barcode","12344","qrcode");
    }
}

function drawBarcodeImagesJqueryQrcode() {
    var _barcodeSize = null;
    var barcodeSizeEl = $("#barcode_image_size");
    if( barcodeSizeEl && barcodeSizeEl.length > 0 ) {
        _barcodeSize = barcodeSizeEl.val();
    }
    //console.log("_tdSize="+_tdSize);
    if( !_barcodeSize ) {
        _barcodeSize = 54;
    }
    $(".barcode-value").each(function(e) {
        var barcodeText = $(this).text();
        //console.log("barcodeText=" + barcodeText);
        if( barcodeText ) {
            barcodeText = barcodeText.trim();
            var parentTr = $(this).parent();

            var imageTdEl = parentTr.find(".barcode-image");

            //var imageTdDomEl = imageTdEl[0];

            imageTdEl.qrcode({
                render : "canvas",
                //render : "table",
                width: _barcodeSize,
                height: _barcodeSize,
                text: barcodeText,
                //correctLevel: QRCode.CorrectLevel.H
            });
        }
    });
}

//packing slip barcode
function drawBarcodeImagesQRCode() {
    $(".barcode-value").each(function(e) {
        var barcodeText = $(this).text();
        console.log("barcodeText=" + barcodeText);
        if( barcodeText ) {
            barcodeText = barcodeText.trim();
            var parentTr = $(this).parent();

            var imageTdDomEl = parentTr.find(".barcode-image")[0];

            new QRCode(
                imageTdDomEl,
                {
                    text: barcodeText,
                    width: 64,
                    height: 64,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                }
            );
        }
    });


}

//https://github.com/wkhtmltopdf/wkhtmltopdf/issues/3654
function drawBarcodeImagesTest() {
    $(".barcode-value").each(function(e) {
        var barcodeText = $(this).text();
        console.log("barcodeText=" + barcodeText);
        if( barcodeText ) {
            barcodeText = barcodeText.trim();
            var parentTr = $(this).parent();

            var imageTdDomEl = parentTr.find(".barcode-image")[0];

            new QRCode(
                imageTdDomEl,
                {
                    text: barcodeText,
                    width: 42,
                    height: 42,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                }
            );


            // var src = $(imageTdDomEl).find("canvas").toDataURL('image/png');
            // console.log("src="+src);


            // var imageEl = '<img src="' + src + '" height="42" width="42">';
            // $(imageTdDomEl).append(imageEl);

            //$(imageTdDomEl).find("canvas").remove();

        }
    });
}


function bwipjsGetFontDit() {
    var fontDir = null;
    // Get the url to this script.  The fonts will be in: ../fonts/
    if(0) {
        //var srcpath = document.querySelector('script[src$="xhr-fonts.js"]').getAttribute('src');
        var srcpath = "/order/bundles/oleguserdirectory/bwip-js/lib/xhr-fonts.js";
        fontDir = srcpath.replace(/lib\/xhr-fonts.js$/, 'fonts/');
        //correct url: bwipjs_fonts.fontdir=/order/bundles/oleguserdirectory/bwip-js/fonts/
    } else {
        var scripthPath = document.querySelector('script[src*="xhr-fonts.js"]');
        if( scripthPath ) {
            var srcpath = scripthPath.getAttribute('src');
            //var srcpath = "/order/bundles/oleguserdirectory/bwip-js/lib/xhr-fonts.js";
            console.log("srcpath=" + srcpath);
            //order/bundles/oleguserdirectory/bwip-js/lib/xhr-fonts.js?1531837781
            var urlArr = srcpath.split("lib/xhr-fonts.js");
            if (urlArr.length > 0) {
                fontDir = urlArr[0] + "fonts/";
            }
        }
    }
    if( !fontDir ) {
        // "/order/bundles/oleguserdirectory/bwip-js/"
        var srcpath = $("#bwipjs-srcpath").val();
        if( srcpath ) {
            fontDir = srcpath + "fonts/";
        }
    }
    if( !fontDir ) {
        var srcpath = "/order/bundles/oleguserdirectory/bwip-js/";
        fontDir = srcpath + "fonts/";
    }
    console.log("fontDir="+fontDir);

    return fontDir;
}


$(document).ready(function() {
    //barcodeInit();

    //testBarcode();
    //render();

    //$('#render').click();

    // $("img").on("remove", function () {
    //     alert("img Element was removed: id="+$(this).attr('id'));
    // });
    // $("canvas").on("remove", function () {
    //     alert("Canvas Element was removed: id="+$(this).attr('id'));
    // });
    // $("td").on("remove", function () {
    //     alert("TD Element was removed: id="+$(this).attr('id'));
    // });
    //
    // $(document).bind("img", function(e) {
    //     alert("DOMNodeRemoved: " + e.target.nodeName);
    // });
    // $(document).bind("canvas", function(e) {
    //     alert("DOMNodeRemoved: " + e.target.nodeName);
    // });
    // $(document).bind("td", function(e) {
    //     alert("DOMNodeRemoved: " + e.target.nodeName);
    // });
});

//multiple barcodes: https://github.com/metafloor/bwip-js/issues/101
//https://github.com/metafloor/bwip-js/issues/73
function render(canvasId, barcodeText, barcodeName) {
    var bw = new BWIPJS(bwipjs_fonts,false);
    var canvas = document.getElementById(canvasId);
    canvas.height = 100;
    canvas.width  = 100;
    //bw.bitmap(new Bitmap);
    //bw.scale('5', '5');

    var scaleX = 1;
    var scaleY = 1;
    var rot = 'N';

     if( barcodeName === undefined ) {
        var barcodeName = "azteccode";
        barcodeName = "qrcode";
     }

    ///////////////////////////
    var opts = {};
    // Add the alternate text
    var altx = barcodeText;
    if (altx) {
        opts.alttext = altx;
        opts.includetext = true;
    }
    // We use mm rather than inches for height - except pharmacode2 height
    // which is expected to be in mm
    if (+opts.height && barcodeName != 'pharmacode2') {
        opts.height = opts.height / 25.4 || 0.5;
    }
    // Likewise, width.
    if (+opts.width) {
        opts.width = opts.width / 25.4 || 0;
    }
    // BWIPP does not extend the background color into the
    // human readable text.  Fix that in the bitmap interface.
    if (opts.backgroundcolor) {
        bw.bitmap(new Bitmap(canvas, rot, opts.backgroundcolor));
        delete opts.backgroundcolor;
    } else {
        bw.bitmap(new Bitmap(canvas, rot));
    }

    // Set the scaling factors
    bw.scale(scaleX, scaleY);

    // Add optional padding to the image
    //bw.bitmap().pad(+opts.paddingwidth*scaleX || 0,
    //    +opts.paddingheight*scaleY || 0);
    //////////////////////////


    try {
        //BWIPP()(bw, barcodeName, barcodeText);
        BWIPP()(bw, barcodeName, barcodeText, opts);

        bwipjs_fonts.loadfonts(function(e) {
            if (e) {
                console.log("loadfonts: e="+e);
                $('#output').text(e.stack || (''+e));
            } else {
                console.log("loadfonts: show");
                show(canvas,barcodeName);
            }
        });
    } catch (e) {
        console.log("catch e="+e);
        return;
    }
    //bw.bitmap().show(canvas);

    // Draw the barcode to the canvas
    function show(canvas,barcodeName) {

        bw.render();

        canvas.style.visibility = 'visible';

        // //$("canvas").style.visibility = 'visible';
        // $("canvas").each(function(element) {
        //     var thisCanvasId = $(this).attr('id');
        //     console.log("thisCanvasId="+thisCanvasId);
        //     var thisCanvas = document.getElementById(thisCanvasId);
        //     thisCanvas.style.visibility = 'visible';
        // });
        
    }
}
