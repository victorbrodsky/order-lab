var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    //.addEntry('app', './assets/js/app.js')
    //.addEntry('page1', './assets/js/page1.js')
    //.addEntry('page2', './assets/js/page2.js')

    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/jquery/jquery-1.11.0.min.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/jquery-ui-1.11.2/jquery-ui.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/bootstrap/js/bootstrap.min.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/bootstrap/js/bootstrap.min.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/ladda/spin.min.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/ladda/ladda.min.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/datepicker/js/bootstrap-datepicker.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/select2/select2.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/inputmask/jquery.inputmask.bundle.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/pnotify/pnotify.custom.min.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-form.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-common.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-selectAjax.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-masking.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-formReady.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-basetitles.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-treeSelectAjax.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-validation.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-fileuploads.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-navbar.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/vakata-jstree/jstree.min.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-jstree.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/typeahead/typeahead.bundle.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-typeahead.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/fengyuanchen-image-cropper/cropper.min.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-crop-avatar.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/idletimeout/jquery.idletimeout.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/idletimeout/jquery.idletimer.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/form/js/user-idleTimeout.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/jasny/js/rowlink.js')
    // .addEntry('user', './public/orderassets/AppUserdirectoryBundle/q-1/q.js')


    // .addEntry('user', [
    //     './public/orderassets/AppUserdirectoryBundle/jquery/jquery-1.11.0.min.js',
    //     './public/orderassets/AppUserdirectoryBundle/jquery-ui-1.11.2/jquery-ui.js'
    //     //'./public/orderassets/AppUserdirectoryBundle/bootstrap/js/bootstrap.min.js',
    //     //'./public/orderassets/AppUserdirectoryBundle/bootstrap/js/bootstrap.min.js',
    //     //'./public/orderassets/AppUserdirectoryBundle/ladda/spin.min.js'
    // ])

    .addEntry('user','./assets/js/user.js')

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
