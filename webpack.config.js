const path = require("path");
var glob = require("glob");

module.exports = {
  entry: glob.sync("./ts/**/*.ts").concat(glob.sync("./module/*/ts/**/*.ts")),
  // devtool: "inline-source-map",
  module: {
    rules: [
      {
        test: /\.ts$/,
        loader: "ts-loader",
        exclude: /node_modules/,
        options: { context: path.resolve(__dirname), configFile: "tsconfig.json" },
      },
    ],
  },
  resolve: {
    modules: [
      __dirname
    ],
    extensions: [".ts"],
  },
  output: {
    filename: "js.js",
    path: path.resolve(__dirname),
  },
};
