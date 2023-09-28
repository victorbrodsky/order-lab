

// import { Uppy, Dashboard, Tus } from "https://releases.transloadit.com/uppy/v3.16.0/uppy.min.mjs";

$(document).ready(function () {
    const uppy = new Uppy();
    uppy.use(Dashboard, {target: '#files-drag-drop'});
    uppy.use(Tus, {endpoint: 'https://tusd.tusdemo.net/files/'});
});
