var path = require('path');
var DashboardPlugin = require('webpack-dashboard/plugin');

var host = '192.168.2.200';
var port = 3000;

module.exports = {
    entry: [
        './web/bundles/app/js/app.js',
        './web/bundles/app/scss/main.scss'
    ],
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'web/dist'),
        publicPath: 'http://' + host + '/dist/'
    },
    module: {
        loaders: [
            {
                test: /\.scss$/,
                use: [
                    {
                        loader: "style-loader" // creates style nodes from JS strings
                    },
                    {
                        loader: "css-loader" // translates CSS into CommonJS
                    },
                    {
                        loader: "resolve-url-loader"
                    },
                    {
                        loader: "sass-loader?sourceMap", // compiles Sass to CSS
                        options: {
                            includePaths: [
                                'web/bundles/app'
                            ]
                        }
                    }
                ]
            },
            {
                test: /\.woff$/,
                loader: "url-loader?limit=10000&mimetype=application/font-woff&name=fonts/[name].[ext]"
            },
            {
                test: /\.(eot|ttf|svg)$/,
                loader: "file-loader?name=fonts/[name].[ext]"
            },
            {
                test: /\.(svg|gif|png|jpg|jpeg)$/,
                loader: "file-loader?name=img/[name].[ext]"
            },
            {
                test: /\.js?$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
                query: {
                    presets: ['es2015', 'stage-0']
                }
            }
        ]
    },
    plugins: [
        new DashboardPlugin()
    ],
    devServer: {
        host: host,
        public: host + ':' + port,
        port: 3000,
        publicPath: 'http://' + host + ':' + port + '/dist/',
        watchOptions: {
            poll: true
        }
    }
};
