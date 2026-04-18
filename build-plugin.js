const fs = require("fs-extra");
const path = require("path");
const archiver = require("archiver");
const { minify } = require("terser");
const postcss = require("postcss");
const cssnano = require("cssnano");

const PLUGIN_SLUG = "woocommerce-delivery-notes";
const DEST = `dist/${PLUGIN_SLUG}`;
const ZIP_PATH = `dist/${PLUGIN_SLUG}.zip`;

// Files/folders to include.
const INCLUDE = [
    "build",
    "vendor",
    "languages",
    "includes",
    "templates",
    "assets",
    `${PLUGIN_SLUG}.php`,
    "index.php",
    "uninstall.php",
    "README.md",
    "readme.txt",
    "changelog.txt",
];

// Non-bundled assets to minify in the dist output.
const JS_FILES = [
    "assets/js/tyche.js",
    "assets/js/dismiss-tracking-notice.js",
    "assets/js/plugin-deactivation.js",
];

const CSS_FILES = ["assets/css/admin.css"];

// Ensure required folders exist.
if (!fs.existsSync("build")) {
    throw new Error("❌ Missing /build. Run npm run build first.");
}

if (!fs.existsSync("vendor")) {
    throw new Error("❌ Missing /vendor. Run composer install.");
}

// Clean dist.
fs.removeSync("dist");
fs.mkdirpSync(DEST);

// Copy files.
INCLUDE.forEach((item) => {
    const src = path.resolve(item);
    const dest = path.join(DEST, item);

    if (fs.existsSync(src)) {
        fs.copySync(src, dest);
    } else {
        console.warn(`⚠️ Skipped missing: ${item}`);
    }
});

// Write .min.js alongside each JS file in dist (originals untouched).
const minifyJs = JS_FILES.map(async (file) => {
    const dest = path.join(DEST, file);

    if (!fs.existsSync(dest)) {
        return;
    }

    const code = fs.readFileSync(dest, "utf8");
    const result = await minify(code, { compress: true, mangle: true });
    const minDest = dest.replace(/\.js$/, ".min.js");

    fs.writeFileSync(minDest, result.code, "utf8");
    console.log(`✅ Minified JS: ${file.replace(/\.js$/, ".min.js")}`);
});

// Write .min.css alongside each CSS file in dist (originals untouched).
const minifyCss = CSS_FILES.map(async (file) => {
    const dest = path.join(DEST, file);

    if (!fs.existsSync(dest)) {
        return;
    }

    const code = fs.readFileSync(dest, "utf8");
    const result = await postcss([cssnano({ preset: "default" })]).process(code, { from: dest });
    const minDest = dest.replace(/\.css$/, ".min.css");

    fs.writeFileSync(minDest, result.css, "utf8");
    console.log(`✅ Minified CSS: ${file.replace(/\.css$/, ".min.css")}`);
});

// Wait for minification then create ZIP.
Promise.all([...minifyJs, ...minifyCss]).then(() => {
    const output = fs.createWriteStream(ZIP_PATH);
    const archive = archiver("zip", { zlib: { level: 9 } });

    archive.pipe(output);
    archive.directory(DEST, false);
    archive.finalize();

    output.on("close", () => {
        console.log(`✅ ZIP created: ${ZIP_PATH} (${archive.pointer()} bytes)`);
    });
});
