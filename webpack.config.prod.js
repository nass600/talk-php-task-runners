var path = require('path');
var webpack = require('webpack');
var CleanWebpackPlugin = require('clean-webpack-plugin');

module.exports = {
    entry: [
        './web/bundles/app/js/app.js',
        './web/bundles/app/scss/main.scss'
    ],
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'web/dist'),
        publicPath: '/dist/'
    },
    module: {
        loaders: [
            {
                test: /\.scss$/,
                use: [
                    {
                        loader: 'style-loader' // creates style nodes from JS strings
                    },
                    {
                        loader: 'css-loader' // translates CSS into CommonJS
                    },
                    {
                        loader: 'resolve-url-loader'
                    },
                    {
                        loader: 'sass-loader?sourceMap', // compiles Sass to CSS
                        options: {
                            includePaths: [
                                'web/bundles/app'
                            ]
                        }
                    }
                ]
            },
            {
                test: /\.(woff|svg|eot|ttf)\??.*$/,
                loader: 'url-loader?limit=50000&name=fonts/[name].[ext]'
            },
            {
                test: /img\/.*\.(jpe?g|png|gif|svg)$/i,
                loaders: [
                    {
                        loader: 'file-loader?name=img/[name].[ext]'
                    },
                    {
                        loader: 'image-webpack-loader',
                        query: {
                            bypassOnDebug: true,
                            gifsicle: {
                                interlaced: false
                            },
                            optipng: {
                                interlaced: false,
                                optimizationLevel: 7
                            }
                        }
                    }

                ]
            },
            {
                test: /\.jsx?$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
                query: {
                    presets: ['es2015', 'stage-0']
                }
            },
            {
                test: /\.json$/,
                loader: 'json-loader'
            }
        ]
    },
    plugins: [
        new CleanWebpackPlugin(['web/dist'], {
            verbose: true,
            dry: false
        }),
        new webpack.optimize.UglifyJsPlugin({
            compress: {
                warnings: false
            },
            output: {
                comments: false
            },
            mangle: true,
            minimize: true
        })
    ]
};
