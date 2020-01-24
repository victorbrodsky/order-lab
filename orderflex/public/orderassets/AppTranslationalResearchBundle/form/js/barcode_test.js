/**
 * Created by ch3 on 7/16/2018.
 */

//USED in bwip-js/lib/xhr-fonts.js
function bwipjsGetFontDit() {
    var fontDir = null;
    // Get the url to this script.  The fonts will be in: ../fonts/
    if(0) {
        //var srcpath = document.querySelector('script[src$="xhr-fonts.js"]').getAttribute('src');
        var srcpath = "/order/bundles/appuserdirectory/bwip-js/lib/xhr-fonts.js";
        fontDir = srcpath.replace(/lib\/xhr-fonts.js$/, 'fonts/');
        //correct url: bwipjs_fonts.fontdir=/order/bundles/appuserdirectory/bwip-js/fonts/
    } else {
        var scripthPath = document.querySelector('script[src*="xhr-fonts.js"]');
        if( scripthPath ) {
            var srcpath = scripthPath.getAttribute('src');
            //var srcpath = "/order/bundles/appuserdirectory/bwip-js/lib/xhr-fonts.js";
            console.log("srcpath=" + srcpath);
            //order/bundles/appuserdirectory/bwip-js/lib/xhr-fonts.js?1531837781
            var urlArr = srcpath.split("lib/xhr-fonts.js");
            if (urlArr.length > 0) {
                fontDir = urlArr[0] + "fonts/";
            }
        }
    }
    if( !fontDir ) {
        // "/order/bundles/appuserdirectory/bwip-js/"
        var srcpath = $("#bwipjs-srcpath").val();
        if( srcpath ) {
            fontDir = srcpath + "fonts/";
        }
    }
    if( !fontDir ) {
        //var srcpath = "/order/bundles/appuserdirectory/bwip-js/";
        var srcpath = "orderassets/AppUserdirectoryBundle/bwip-js/";
        fontDir = srcpath + "fonts/";
    }
    console.log("fontDir="+fontDir);

    return fontDir;
}



//multiple barcodes: https://github.com/metafloor/bwip-js/issues/101
//https://github.com/metafloor/bwip-js/issues/73
function render(canvasId, barcodeText) {
    var bw = new BWIPJS(bwipjs_fonts,false);
    var canvas = document.getElementById(canvasId);
    canvas.height = 1;
    canvas.width  = 1;
    //bw.bitmap(new Bitmap);
    //bw.scale('5', '5');

    var scaleX = 0.5;
    var scaleY = 0.5;
    var rot = 'N';
    //var barcodeName = "azteccode";
    var barcodeName = "qrcode";

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
        //var ts2 = Date.now();

        canvas.style.visibility = 'visible';
        //setURL();

        //$('#stats').text('Rendered in ' + (ts2-ts0) + ' msecs');
        //$('.saveas').css('visibility', 'visible');
        //saveCanvas.basename = barcodeName + '-' + text.replace(/[^a-zA-Z0-9._]+/g, '-');

        // Show proofs?
        // if (location.search.indexOf('proofs=1') != -1) {
        //     var img = document.getElementById('proof-img');
        //     if (img) {
        //         img.src = 'proofs/' + barcodeName + '.png';
        //         img.style.visibility = 'visible';
        //     }
        // }
    }
}


