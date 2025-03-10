//After modifing this, run: yarn watch
var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

var dotenv = require('dotenv');
dotenv.config({path: '.env'});
console.log('process.env.APP_SUBDIR='+process.env.APP_SUBDIR); //process.env.APP_SUBDIR=/c/wcm/pathology
var publicPathSubDir = '';
if( process.env.APP_SUBDIR ) {
    publicPathSubDir = process.env.APP_SUBDIR + '/';
}
console.log('publicPathSubDir='+publicPathSubDir); //process.env.APP_SUBDIR=/c/wcm/pathology
//console.log('process.env.APP_PREFIX_URL='+process.env.APP_PREFIX_URL);

const path = require('path');
const YAML = require('js-yaml');
//var fileContents = __dirname;
//console.log('fileContents='+fileContents);
const fileContents = path.resolve(__dirname, 'config', 'parameters.yml');
console.log('fileContents='+fileContents);
const params = YAML.load(fileContents);
publicPathSubDir2 = JSON.stringify(params.tenant_role);
console.log('publicPathSubDir2='+publicPathSubDir2);

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    //.setPublicPath('/build')
    .enableReactPreset()
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    // this is your *true* public path
    //For multitenancy, set APP_SUBDIR=/c/wcm/pathology in .env to make correct, true public path
    //.setPublicPath('/build') or .setPublicPath('/c/wcm/pathology/build')
    //.setPublicPath('/build')
    .setPublicPath('/'+publicPathSubDir+'build')

    .setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    //.addEntry('app', './assets/testing/js/app.js')
    //.addEntry('page1', './assets/js/page1.js')
    //.addEntry('page2', './assets/js/page2.js')

    //.addEntry('dashboard_base', './assets/dashboard/js/dashboard_base.js')
    .addEntry('dashboard', './assets/dashboard/js/dashboard.jsx')
    //.addEntry('userdates', './assets/userdates/js/app.jsx')
    //.addEntry('userdates', './assets/userdates/js/ScrollComponent.jsx')
    .addEntry('userdates', './assets/userdates/js/index.jsx')
    .addEntry('userdates-css', './assets/userdates/css/index.css')

    .addEntry('transresjs', './assets/transres/js/project.jsx')
    .addEntry('transresjs-edit', './assets/transres/js/project-edit.jsx')

    .addEntry('user-uppy', './assets/uppy/js/user-uppy.jsx')
    .addEntry('user-uppy-css', './assets/uppy/css/user-uppy-style.scss')

    .addEntry('antibodies', './assets/antibodies/js/index.jsx')
    .addEntry('antibodies-show', './assets/antibodies/js/show.jsx')
    .addEntry('antibodies-css', './assets/antibodies/css/index.css')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')
;

module.exports = Encore.getWebpackConfig();
