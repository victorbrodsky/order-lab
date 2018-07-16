/**
 * Created by ch3 on 7/16/2018.
 */

function bwipInit() {
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
    if (lastRotate) {
        document.getElementById('rot' + lastRotate).checked = true;
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

function render() {
    var elt = symdesc[$('#symbol')[0].selectedIndex];
    var text = $('#symtext').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var altx = $('#symaltx').val().replace(/^\s+/,'').replace(/\s+$/,'');
    var opts = $('#symopts').val().replace(/^\s+/,'').replace(/\s+$/,'');

    var bw = new BWIPJS;

    // Convert the options to a dictionary object, so we can pass alttext with
    // spaces.
    var tmp = opts.split(' ');
    opts = {};
    for (var i = 0; i < tmp.length; i++) {
        if (!tmp[i])
            continue;
        var eq = tmp[i].indexOf('=');
        if (eq == -1)
            opts[tmp[i]] = bw.value(true);
        else
            opts[tmp[i].substr(0, eq)] = bw.value(tmp[i].substr(eq+1));
    }

    // Add the alternate text
    if (altx)
        opts.alttext = bw.value(altx);

    // Add any hard-coded options required to fix problems in the javascript
    // emulation.
    opts.inkspread = bw.value(0);
    if (needyoffset[elt.sym] && !opts.textxalign && !opts.textyalign &&
        !opts.alttext && opts.textyoffset === undefined)
        opts.textyoffset = bw.value(-10);

    var rot  = 'N';
    var rots = [ 'rotL', 'rotR', 'rotI' ];
    for (var i = 0; i < rots.length; i++) {
        if (document.getElementById(rots[i]).checked) {
            rot = rots[i].charAt(3);
            break;
        }
    }

    bw.bitmap(new Bitmap);

    var scl = parseInt(document.getElementById('scale').value, 10) || 2;
    bw.scale(scl,scl);

    var div = document.getElementById('output');
    if (div)
        div.innerHTML = '';

    bw.push(text);
    bw.push(opts);

    try {
        bw.call(elt.sym);
        bw.bitmap().show('canvas', rot);
    } catch(e) {
        var s = '';
        if (e.fileName)
            s += e.fileName + ' ';
        if (e.lineNumber)
            s += '[line ' + e.lineNumber + '] ';
        alert(s + (s ? ': ' : '') + e.message);
    }
}

function saveCanvas(type, ext) {
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
