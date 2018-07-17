/**
 * Created by ch3 on 7/16/2018.
 */

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

function testBarcode() {

    var elt  = {sym:"qrcode"};      //symdesc[$('#symbol').val()];
    //var elt  = symdesc[$('#symbol').val()];
    var text = '123';   //$('#symtext').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var altx = '123';   //$('#symaltx').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var opts = 'eclevel=M';  //eclevel=M //$('#symopts').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var rot  = 'N'; //document.querySelector('input[name="rot"]:checked').value;

    // Anti-aliased or monochrome fonts and scaling factors.
    var monochrome = false; //document.getElementById('fontMono').checked;
    var scaleX = 1; //+document.getElementById('scaleX').value || 2;
    var scaleY = 1; //+document.getElementById('scaleY').value || 2;


    var eltSym = "qrcode";
    if( elt.sym ) {
        eltSym = elt.sym;
    }
    console.log("eltSym="+eltSym);

    localStorage.setItem('bwipjsLastSymbol',  elt.sym);
    localStorage.setItem('bwipjsLastBarText', text);
    localStorage.setItem('bwipjsLastAltText', altx);
    localStorage.setItem('bwipjsLastOptions', opts);
    localStorage.setItem('bwipjsLastScaleX', scaleX);
    localStorage.setItem('bwipjsLastScaleY', scaleY);
    localStorage.setItem('bwipjsLastFontMono', monochrome ? 1 : 0);
    localStorage.setItem('bwipjsLastRotation', rot);

    // Initialize a barcode writer object.  This is the interface between
    // the low-level BWIPP code, the font manager, and the Bitmap object.
    var bw = new BWIPJS(bwipjs_fonts, monochrome);

    var canvas = document.getElementById('canvas');
    canvas.height = 1;
    canvas.width  = 1;
    canvas.style.visibility = 'hidden';

    // Convert the options to a dictionary object, so we can pass alttext with
    // spaces.
    var tmp = opts.split(' ');
    opts = {};
    for (var i = 0; i < tmp.length; i++) {
        if (!tmp[i]) {
            continue;
        }
        var eq = tmp[i].indexOf('=');
        if (eq == -1) {
            opts[tmp[i]] = true;
        } else {
            opts[tmp[i].substr(0, eq)] = tmp[i].substr(eq+1);
        }
    }

    // Add the alternate text
    if (altx) {
        opts.alttext = altx;
        opts.includetext = true;
    }
    // We use mm rather than inches for height - except pharmacode2 height
    // which is expected to be in mm
    if (+opts.height && elt.sym != 'pharmacode2') {
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
    bw.bitmap().pad(+opts.paddingwidth*scaleX || 0,
        +opts.paddingheight*scaleY || 0);

    var ts0 = Date.now();
    try {
        // Call into the BWIPP cross-compiled code.
        BWIPP()(bw, elt.sym, text, opts);

        // Allow the font manager to demand-load any required fonts
        // before calling render().
        var ts1 = Date.now();
        bwipjs_fonts.loadfonts(function(e) {
            if (e) {
                $('#output').text(e.stack || (''+e));
            } else {
                show();
            }
        });
    } catch (e) {
        // Watch for BWIPP generated raiseerror's.
        var msg = ''+e;
        if (msg.indexOf("bwipp.") >= 0) {
            $('#output').text(msg);
        } else if (e.stack) {
            $('#output').text(e.stack);
        } else {
            $('#output').text(e);
        }
        return;
    }

    // Draw the barcode to the canvas
    function show() {
        bw.render();
        var ts2 = Date.now();

        canvas.style.visibility = 'visible';
        setURL();
        $('#stats').text('Rendered in ' + (ts2-ts0) + ' msecs');
        $('.saveas').css('visibility', 'visible');
        saveCanvas.basename = elt.sym + '-' +
            text.replace(/[^a-zA-Z0-9._]+/g, '-');

        // Show proofs?
        if (location.search.indexOf('proofs=1') != -1) {
            var img = document.getElementById('proof-img');
            if (img) {
                img.src = 'proofs/' + elt.sym + '.png';
                img.style.visibility = 'visible';
            }
        }
    }
}

function barcodeInit() {
    var lastSymbol	= localStorage.getItem('bwipjsLastSymbol');
    var lastBarText	= localStorage.getItem('bwipjsLastBarText');
    var lastAltText	= localStorage.getItem('bwipjsLastAltText');
    var lastOptions = localStorage.getItem('bwipjsLastOptions');
    var lastRotate	= localStorage.getItem('bwipjsLastRotation');
    var lastScaleX  = +localStorage.getItem('bwipjsLastScaleX');
    var lastScaleY  = +localStorage.getItem('bwipjsLastScaleY');
    var lastFntMono	= +localStorage.getItem('bwipjsLastFontMono');

    var $sel = $('#symbol')
        .change(function(ev) {
            var desc = symdesc[$(this).val()];
            if (desc) {
                $('#symtext').val(desc.text);
                $('#symopts').val(desc.opts);
            } else {
                $('#symtext').val('');
                $('#symopts').val('');
            }
            $('#symaltx').val('');
            $('.saveas').css('visibility', 'hidden');
            $('#proof-img').css('visibility', 'hidden');
            $('#stats').text('');
            var canvas = document.getElementById('canvas');
            canvas.width = canvas.width;
        });

    if (lastSymbol) {
        $sel.val(lastSymbol);
    } else {
        $sel.prop('selectedIndex', 0);
    }
    $sel.trigger('change');

    if (lastBarText) {
        $('#symtext').val(lastBarText);
        $('#symaltx').val(lastAltText);
        $('#symopts').val(lastOptions);
    }
    if (lastScaleX && lastScaleY) {
        $('#scaleX').val(lastScaleX);
        $('#scaleY').val(lastScaleY);
    }
    // if (lastRotate) {
    //     console.log("lastRotate="+lastRotate);
    //     document.getElementById('rot' + lastRotate).checked = true;
    // }
    if (lastRotate) {
        console.log("lastRotate="+lastRotate);
        var rotEl = document.getElementById('rot' + lastRotate);
        if( !rotEl ) {
            rotEl = document.getElementById('rot' + 'N');
        }
        //document.getElementById('rot' + lastRotate).checked = true;
        rotEl.checked = true;
    }
    if (lastFntMono) {
        document.getElementById('fontMono').checked = true;
    }

    $('#scaleX').spinner({ min:1 })
        .on("spinstop", function(ev) {
            $('#scaleY').val(this.value);
        });
    $('#scaleY').spinner({ min:1 });
    $('#render').button().click(render);
    $('.saveas').css('visibility', 'hidden');

    if (location.search.indexOf('proofs=1') != -1) {
        // Show the images from BWIPP with Ghostscript
        var img = document.createElement('img');
        img.id					= 'proof-img';
        img.style.visibility 	= 'hidden';
        img.style.position		= 'absolute';
        img.style.top			= '0px';
        img.style.left			= '0px';
        $('#proof').append(img);
    }

    // Allow Enter to render
    $('#params').keypress(function(ev) {
        if (ev.which == 13) {
            render();
            ev.stopPropagation();
            ev.preventDefault();
            return false;
        }
    });

    document.getElementById('versions').textContent =
        'bwip-js ' + BWIPJS.VERSION + ' / BWIPP ' + BWIPP.VERSION;
}

$(document).ready(function() {
    barcodeInit();

    testBarcode();
});

function render() {
    var elt  = symdesc[$('#symbol').val()];
    var text = $('#symtext').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var altx = $('#symaltx').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var opts = $('#symopts').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var rot  = document.querySelector('input[name="rot"]:checked').value;

    // Anti-aliased or monochrome fonts and scaling factors.
    var monochrome = document.getElementById('fontMono').checked;
    var scaleX = +document.getElementById('scaleX').value || 2;
    var scaleY = +document.getElementById('scaleY').value || 2;

    // var elt  = {sym:"qrcode"};      //symdesc[$('#symbol').val()];
    // //var elt  = symdesc[$('#symbol').val()];
    // var text = '123';   //$('#symtext').val().replace(/^\s+/,'').replace(/\s+$/,'');
    // var altx = '123';   //$('#symaltx').val().replace(/^\s+/,'').replace(/\s+$/,'');
    // var opts = 'eclevel=M';  //eclevel=M //$('#symopts').val().replace(/^\s+/,'').replace(/\s+$/,'');
    // var rot  = 'N'; //document.querySelector('input[name="rot"]:checked').value;
    //
    // // Anti-aliased or monochrome fonts and scaling factors.
    // var monochrome = false; //document.getElementById('fontMono').checked;
    // var scaleX = 1; //+document.getElementById('scaleX').value || 2;
    // var scaleY = 1; //+document.getElementById('scaleY').value || 2;

    console.log("text="+text);
    console.log("altx="+altx);
    console.log("opts="+opts);
    console.log("rot="+rot);

    console.log("monochrome="+monochrome);
    console.log("scaleX="+scaleX);
    console.log("scaleY="+scaleY);


    localStorage.setItem('bwipjsLastSymbol',  elt.sym);
    localStorage.setItem('bwipjsLastBarText', text);
    localStorage.setItem('bwipjsLastAltText', altx);
    localStorage.setItem('bwipjsLastOptions', opts);
    localStorage.setItem('bwipjsLastScaleX', scaleX);
    localStorage.setItem('bwipjsLastScaleY', scaleY);
    localStorage.setItem('bwipjsLastFontMono', monochrome ? 1 : 0);
    localStorage.setItem('bwipjsLastRotation', rot);

    // Initialize a barcode writer object.  This is the interface between
    // the low-level BWIPP code, the font manager, and the Bitmap object.
    var bw = new BWIPJS(bwipjs_fonts, monochrome);

    // Clear the page
    $('#output').text('');
    $('#stats').text('');
    $('#proof-img').css('visibility', 'hidden');
    $('.saveas').css('visibility', 'hidden');

    var canvas = document.getElementById('canvas');
    canvas.height = 1;
    canvas.width  = 1;
    canvas.style.visibility = 'hidden';

    // Convert the options to a dictionary object, so we can pass alttext with
    // spaces.
    var tmp = opts.split(' ');
    opts = {};
    for (var i = 0; i < tmp.length; i++) {
        if (!tmp[i]) {
            continue;
        }
        var eq = tmp[i].indexOf('=');
        if (eq == -1) {
            opts[tmp[i]] = true;
        } else {
            opts[tmp[i].substr(0, eq)] = tmp[i].substr(eq+1);
        }
    }

    // Add the alternate text
    if (altx) {
        opts.alttext = altx;
        opts.includetext = true;
    }
    // We use mm rather than inches for height - except pharmacode2 height
    // which is expected to be in mm
    if (+opts.height && elt.sym != 'pharmacode2') {
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
    bw.bitmap().pad(+opts.paddingwidth*scaleX || 0,
        +opts.paddingheight*scaleY || 0);

    var ts0 = Date.now();
    try {
        // Call into the BWIPP cross-compiled code.
        BWIPP()(bw, elt.sym, text, opts);

        // Allow the font manager to demand-load any required fonts
        // before calling render().
        var ts1 = Date.now();
        bwipjs_fonts.loadfonts(function(e) {
            if (e) {
                $('#output').text(e.stack || (''+e));
            } else {
                show();
            }
        });
    } catch (e) {
        // Watch for BWIPP generated raiseerror's.
        var msg = ''+e;
        if (msg.indexOf("bwipp.") >= 0) {
            $('#output').text(msg);
        } else if (e.stack) {
            $('#output').text(e.stack);
        } else {
            $('#output').text(e);
        }
        return;
    }

    // Draw the barcode to the canvas
    function show() {
        bw.render();
        var ts2 = Date.now();

        canvas.style.visibility = 'visible';
        setURL();
        $('#stats').text('Rendered in ' + (ts2-ts0) + ' msecs');
        $('.saveas').css('visibility', 'visible');
        saveCanvas.basename = elt.sym + '-' +
            text.replace(/[^a-zA-Z0-9._]+/g, '-');

        // Show proofs?
        if (location.search.indexOf('proofs=1') != -1) {
            var img = document.getElementById('proof-img');
            if (img) {
                img.src = 'proofs/' + elt.sym + '.png';
                img.style.visibility = 'visible';
            }
        }
    }
}

function saveCanvas(type, ext) {
    console.log("saveCanvas !!!");
    var canvas = document.getElementById('canvas');
    canvas.toBlob(function (blob) {
        saveAs(blob, saveCanvas.basename + ext);
    }, type, 1);
}
function setURL() {
    var elt  = symdesc[$('#symbol').val()];
    var text = $('#symtext').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var altx = $('#symaltx').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var opts = $('#symopts').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var rot  = document.querySelector('input[name="rot"]:checked').value;
    var mono = document.getElementById('fontMono').checked;
    var scaleX = +document.getElementById('scaleX').value || 2;
    var scaleY = +document.getElementById('scaleY').value || scaleX;

    var url = 'http://bwipjs-api.metafloor.com/?bcid=' + elt.sym +
        '&text=' + encodeURIComponent(text) +
        (altx ? '&alttext=' + encodeURIComponent(altx) : '') +
        (opts ? '&' + opts.replace(/ +/g, '&') : '') +
        (rot != 'N' ? '&rotate=' + rot : '') +
        (scaleX == scaleY ? '&scale=' + scaleX
            : '&scaleX=' + scaleX + '&scaleY=' + scaleY) +
        (mono ? '&monochrome' : '');

    document.getElementById('apiurl').href = url;
}
