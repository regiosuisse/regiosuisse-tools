const Encore = require('@symfony/webpack-encore');
const {env, sassVariables, embedSassVariables} = require('./webpack.env');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore.reset();

Encore
    .configureDefinePlugin(options => {
        Object.keys(env).forEach((key) => {
            options['process.env.' + key] = JSON.stringify(env[key]);
        });
        options.__VUE_OPTIONS_API__ = true;
        options.__VUE_PROD_DEVTOOLS__ = false;
    })
    .setOutputPath('public/embed/feedback/')
    .setPublicPath(env.HOST + '/embed/feedback')
    .setManifestKeyPrefix('embed/feedback/')
    .addEntry('embed-feedback', './assets/js/embed-feedback.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    //.enableSourceMaps(!Encore.isProduction())
    .enableSourceMaps(true)
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .addRule({
        test: /\.ya?ml$/,
        use: 'yaml-loader',
    })
    .enableSassLoader(() => {
        return {
            additionalData: embedSassVariables,
        };
    })
    .enableVueLoader(() => {
    }, {
        runtimeCompilerBuild: false
    })
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
    .copyFiles({
        from: './assets/custom/images',
        to: 'custom/images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
;

const embedFeedbackConfig = Encore.getWebpackConfig();

embedFeedbackConfig.name = 'embed-feedback';

Encore.reset();

Encore
    .configureDefinePlugin(options => {
        Object.keys(env).forEach((key) => {
            options['process.env.' + key] = JSON.stringify(env[key]);
        });
        options.__VUE_OPTIONS_API__ = true;
        options.__VUE_PROD_DEVTOOLS__ = false;
    })
    .setOutputPath('public/embed/feedback-latest/')
    .setPublicPath(env.HOST + '/embed/feedback-latest')
    .setManifestKeyPrefix('embed/feedback-latest/')
    .addEntry('embed-feedback-latest', './assets/js/embed-feedback.js')
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    //.enableSourceMaps(!Encore.isProduction())
    .enableSourceMaps(true)
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .addRule({
        test: /\.ya?ml$/,
        use: 'yaml-loader',
    })
    .enableSassLoader(() => {
        return {
            additionalData: embedSassVariables,
        };
    })
    .enableVueLoader(() => {
    }, {
        runtimeCompilerBuild: false
    })
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
    .copyFiles({
        from: './assets/custom/images',
        to: 'custom/images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
;

const embedFeedbackLatestConfig = Encore.getWebpackConfig();

embedFeedbackLatestConfig.name = 'embed-feedback-latest';

// export the final configuration as an array of multiple configurations
module.exports = [
    embedFeedbackConfig,
    embedFeedbackLatestConfig,
];
