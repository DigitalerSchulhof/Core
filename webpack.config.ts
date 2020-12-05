import * as path from "path";
import * as glob from "glob";
import * as webpack from "webpack";
import * as fs from "fs";

const entries: webpack.Entry = {};
entries.core = glob.sync("ts/**/*.ts");
entries.core = entries.core.filter((el: string) => el.substr(-9) !== "export.ts");
entries.core.push("ts/export.ts");

glob.sync("module/*").forEach(dir => {
  let files = glob.sync(dir + "/ts/**/*.ts");
  files = files.filter((el: string) => el.substr(-9) !== "export.ts");
  if (files.length > 0) {
    if (fs.existsSync(dir + "/ts/export.ts")) {
      files.push(dir + "/ts/export.ts");
    }
    entries[dir.substring(dir.lastIndexOf("/") + 1)] = files;
  }
});

console.log(entries);

const config: webpack.Configuration = {
  entry: entries,
  devtool: "inline-source-map",

  module: {
    rules: [
      {
        test: /\.ts$/,
        loader: "ts-loader",
        exclude: /node_modules/,
        options: {
          context: path.resolve(__dirname),
          configFile: "tsconfig.json",
        },
      },
    ],
  },
  resolve: {
    modules: [__dirname],
    extensions: [".ts", ".js"],
  },
  output: {
    filename: "js.[name].js",
    path: path.resolve(__dirname, "js"),
    library: "[name]",
    libraryTarget: "var",
    libraryExport: "default"
  },
  plugins: [
    new webpack.optimize.LimitChunkCountPlugin({
      maxChunks: 1,
    }),
  ]
};

export default config;