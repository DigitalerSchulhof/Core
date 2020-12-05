import * as path from "path";
import * as glob from "glob";
import * as webpack from "webpack";
import * as fs from "fs";

const config: webpack.Configuration = {
  entry: ({
    core: ["ts/events.ts", "ts/export.ts"],
    ...glob.sync("./module/*", {}).reduce<{ [key: string]: any }>((list, dir: string) => {
      const modul = [];
      if (fs.existsSync(dir + "/ts/events.ts")) {
        modul.push(dir + "/ts/events.ts");
      }
      if (fs.existsSync(dir + "/ts/export.ts")) {
        modul.push(dir + "/ts/export.ts");
      }
      if (modul.length > 0) {
        list[dir.substring(dir.lastIndexOf("/") + 1).toLocaleLowerCase()] = modul;
      }
      return list;
    }, {})
  }),
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
    filename: "[name].js",
    path: path.resolve(__dirname, "js"),
    library: "[name]",
    libraryTarget: "var",
    libraryExport: "default",
  },
  plugins: [
    new webpack.optimize.LimitChunkCountPlugin({
      maxChunks: 1,
    }),
  ]
};

console.log(config.entry);

export default config;