//yarn add @uppy/core @uppy/dashboard @uppy/tus @uppy/webcam

//import { Uppy, Dashboard, Tus } from 'https://releases.transloadit.com/uppy/v3.16.0/uppy.min.mjs'
import Uppy from '@uppy/core'
import Dashboard from '@uppy/dashboard'
import Tus from '@uppy/tus'
//import RemoteSources from '@uppy/remote-sources'
//import ImageEditor from '@uppy/image-editor'
import Webcam from '@uppy/webcam'
import XHRUpload from '@uppy/xhr-upload'

import '@uppy/core/dist/style.css'
import '@uppy/dashboard/dist/style.css'
import '@uppy/webcam/dist/style.css'


console.log("before new Uppy");

// const uppy = new Uppy({
//     debug: true,
//     autoProceed: false,
// })
// uppy.use(Dashboard, { target: '#files-drag-drop' })
// uppy.use(Tus, { endpoint: 'https://tusd.tusdemo.net/files/' })
// uppy.on('complete', (result) => {
//     console.log('Upload result:', result)
// })

//var endpointUrl = Routing.generate('employees_upload_chunk_file');
//var endpointUrl = Routing.generate('employees_upload_uppy_file');
var endpointUrl = Routing.generate('tus');


const uppy = new Uppy({
    debug: true,
    autoProceed: false,
    onBeforeFileAdded: (file, files) => {
        console.log("File "+file.name);

        // if( Object.hasOwn(files, file.id) ) {
        //     console.log("Duplicate file "+file.name);
        //     const name = Date.now() + '_' + file.name
        //         Object.defineProperty(file.data, 'name', {
        //         writable: true,
        //         value: name
        //     });
        //     return { ...file, name, meta: { ...file.meta, name } }
        // } else {
        //     console.log("New file "+file.name);
        // }
        // return file

        //Rename file: append the timestamp Date.now().toString()
       // var d = new Date();
        //var datestring = d.getDate()  + "-" + (d.getMonth()+1) + "-" + d.getFullYear() + "_" +
        //    d.getHours() + "-" + d.getMinutes() + "-" + d.getSeconds();
        //var datestring = convertNowDateToString();
        //const name = datestring + '_' + file.name
        const name = getReNamedFileName(file.name);
        Object.defineProperty(file.data, 'name', {
            writable: true,
            value: name
        });
        return { ...file, name, meta: { ...file.meta, name } }
    },
})

//uppy.use(Webcam)
uppy.use(Dashboard, {
    inline: true,
    target: '#files-drag-drop',
    //plugins: ['Webcam'],
    //width: 300,
    height: 300,
})
// uppy.use(XHRUpload, {
//    endpoint: endpointUrl, //'http://localhost:3020/upload.php',
// })
uppy.use(Tus, {
    endpoint: endpointUrl,
    limit:10,
    //resume: true,
    //autoRetry: true,
    retryDelays: [0, 1000, 3000, 5000],
    removeFingerprintOnSuccess: true
});


console.log("after Uppy");

function convertNowDateToString() {
    var d = new Date();

    var datestring = ("0" + d.getDate()).slice(-2) + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" +
        d.getFullYear() + "_" +
        ("0" + d.getHours()).slice(-2) + "-" +
        ("0" + d.getMinutes()).slice(-2) + "-" +
        ("0" + d.getSeconds()).slice(-2)

    return datestring;
}

function getReNamedFileName( filename ) {
    var extArr = ["dump.gz", "tar.gz", "tgz", "gz", "zip"];
    for( let i = 0; i < extArr.length; i++ ) {
        let ext = extArr[i];
        if( filename.indexOf(ext) !== -1 ) {
            //let thisExt = filename.split('.').pop();
            filename = addDateStringToFilename(filename,ext);
            return filename;
        }
    }
    filename = addDateStringToFilename(filename,null);
    return filename;
}

function addDateStringToFilename( filename, ext ) {
    if( ext == null ) {
        ext = filename.split('.').pop();
    }
    var prefix = convertNowDateToString();
    filename = filename.replace('.' + ext, "");
    filename = filename + '_' + prefix + '.' + ext;
    return filename;
}
