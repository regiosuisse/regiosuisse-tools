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
    .setOutputPath('public/embed/financial-supports/')
    .setPublicPath(env.HOST + '/embed/financial-supports')
    .setManifestKeyPrefix('embed/financial-supports/')
    .addEntry('embed-financial-supports', './assets/js/embed-financial-supports.js')
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

const embedFinancialSupportsConfig = Encore.getWebpackConfig();

embedFinancialSupportsConfig.name = 'embed-financial-supports';

Encore.reset();

Encore
    .configureDefinePlugin(options => {
        Object.keys(env).forEach((key) => {
            options['process.env.' + key] = JSON.stringify(env[key]);
        });
        options.__VUE_OPTIONS_API__ = true;
        options.__VUE_PROD_DEVTOOLS__ = false;
    })
    .setOutputPath('public/embed/financial-supports-latest/')
    .setPublicPath(env.HOST + '/embed/financial-supports-latest')
    .setManifestKeyPrefix('embed/financial-supports-latest/')
    .addEntry('embed-financial-supports-latest', './assets/js/embed-financial-supports.js')
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

const embedFinancialSupportsLatestConfig = Encore.getWebpackConfig();

embedFinancialSupportsLatestConfig.name = 'embed-financial-supports-latest';

// export the final configuration as an array of multiple configurations
module.exports = [
    embedFinancialSupportsConfig,
    embedFinancialSupportsLatestConfig,
];
