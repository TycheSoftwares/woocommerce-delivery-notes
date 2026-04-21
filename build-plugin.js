const fs = require("fs-extra");
const path = require("path");
const archiver = require("archiver");
const { minify } = require("terser");
const postcss = require("postcss");
const cssnano = require("cssnano");

const PLUGIN_SLUG = "woocommerce-delivery-notes";
const variant = process.argv.includes("--variant=wc") ? "wc" : "non-wc";
const DEST_BASE = path.join("dist", variant === "wc" ? "wc-version" : "non-wc-version");
const DEST = path.join(DEST_BASE, PLUGIN_SLUG);
const ZIP_PATH = path.join(DEST_BASE, `${PLUGIN_SLUG}.zip`);

// Files/folders to copy into the distribution.
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

// Assets minified in the dist output (WC version skips the tracking JS).
const JS_FILES = [
    "assets/js/tyche.js",
    "assets/js/dismiss-tracking-notice.js",
    "assets/js/plugin-deactivation.js",
];

const CSS_FILES = ["assets/css/admin.css"];

// JS files removed entirely from the WC Marketplace version.
const WC_REMOVE_JS = [
    "assets/js/tyche.js",
    "assets/js/dismiss-tracking-notice.js",
    "assets/js/plugin-deactivation.js",
];

if (!fs.existsSync("build")) {
    throw new Error("Missing /build — run `npm run build` first.");
}

if (!fs.existsSync("vendor")) {
    throw new Error("Missing /vendor — run `npm run composer` first.");
}

// Clean only this variant's output folder (leaves the other variant intact).
fs.removeSync(DEST_BASE);
fs.mkdirpSync(DEST);

console.log(`\nPackaging ${variant === "wc" ? "WC Marketplace" : "Standard"} version…`);

// Copy all included files.
INCLUDE.forEach((item) => {
    const src = path.resolve(item);
    const dest = path.join(DEST, item);
    if (fs.existsSync(src)) {
        fs.copySync(src, dest);
    } else {
        console.warn(`  ⚠  Skipped missing: ${item}`);
    }
});

// Apply WC Marketplace modifications to the copied files.
if (variant === "wc") {
    console.log("  Applying WC Marketplace modifications…");
    patchWcVersion(DEST);
}

// Minify JS (WC version skips the removed files).
const jsToMinify = variant === "wc"
    ? JS_FILES.filter((f) => !WC_REMOVE_JS.includes(f))
    : JS_FILES;

const minifyJs = jsToMinify.map(async (file) => {
    const dest = path.join(DEST, file);
    if (!fs.existsSync(dest)) return;
    const code = fs.readFileSync(dest, "utf8");
    const result = await minify(code, { compress: true, mangle: true });
    fs.writeFileSync(dest.replace(/\.js$/, ".min.js"), result.code, "utf8");
    console.log(`  ✓ Minified ${file}`);
});

// Minify CSS.
const minifyCss = CSS_FILES.map(async (file) => {
    const dest = path.join(DEST, file);
    if (!fs.existsSync(dest)) return;
    const code = fs.readFileSync(dest, "utf8");
    const result = await postcss([cssnano({ preset: "default" })]).process(code, { from: dest });
    fs.writeFileSync(dest.replace(/\.css$/, ".min.css"), result.css, "utf8");
    console.log(`  ✓ Minified ${file}`);
});

// Create ZIP once all minification is done.
Promise.all([...minifyJs, ...minifyCss]).then(() => {
    const output = fs.createWriteStream(ZIP_PATH);
    const archive = archiver("zip", { zlib: { level: 9 } });

    archive.pipe(output);
    // Archive contents inside a woocommerce-delivery-notes/ top-level folder so
    // WordPress installs it with the correct slug when uploaded via the dashboard.
    archive.directory(DEST, PLUGIN_SLUG);
    archive.finalize();

    output.on("close", () => {
        const kb = Math.round(archive.pointer() / 1024);
        console.log(`  ✓ ZIP created: ${ZIP_PATH} (${kb} KB)\n`);
    });
});

// ---------------------------------------------------------------------------
// WC Marketplace patch functions
// ---------------------------------------------------------------------------

function patchWcVersion(dest) {
    // Remove the tracking/deactivation component directory.
    fs.removeSync(path.join(dest, "includes/component"));

    // Remove tracking JS assets.
    WC_REMOVE_JS.forEach((file) => {
        fs.removeSync(path.join(dest, file));
    });

    patchFilesPhp(path.join(dest, "includes/core/class-files.php"));
    patchBackendPhp(path.join(dest, "includes/admin/class-backend.php"));
    patchScriptsPhp(path.join(dest, "includes/admin/class-scripts.php"));
    patchSettingsPhp(path.join(dest, "includes/api/class-settings.php"));
    patchReadmeTxt(path.join(dest, "readme.txt"));
}

// Removes the Admin_Component load from class-files.php.
function patchFilesPhp(file) {
    let content = fs.readFileSync(file, "utf8");

    content = replaceOrWarn(
        content,
        "\t\t// Tyche Admin Components.\n" +
        "\t\tWCDN()::include_file( 'component/class-admin-component.php' );\n" +
        "\t\tnew Admin_Component();\n",
        "",
        file,
        "Admin_Component block"
    );

    fs.writeFileSync(file, content);
}

// Removes the tracker filter, tracking scripts enqueue, and tracker_data method.
function patchBackendPhp(file) {
    let content = fs.readFileSync(file, "utf8");

    // 1. Remove add_filter for tracker data.
    content = replaceOrWarn(
        content,
        "\t\tadd_filter( 'wcdn_ts_tracker_data', array( $this, 'tracker_data' ), 10, 1 );\n",
        "",
        file,
        "wcdn_ts_tracker_data filter"
    );

    // 2. Remove tyche.js + dismiss-tracking-notice.js enqueue + localize block.
    //    The block starts with the blank line after the style enqueue's closing brace
    //    and ends with the closing ); of wp_localize_script.
    content = content.replace(
        /\n\n\t\twp_enqueue_script\(\n\t\t\t'tyche',[\s\S]*?'tracking_notice'\s*=>\s*wp_create_nonce\( 'tracking_notice' \),\n\t\t\t\)\n\t\t\);\n/,
        "\n"
    );

    if ( content.includes("wcdn_ts_dismiss_notice") ) {
        console.warn(`  ⚠  ${file}: tracking scripts block was not fully removed`);
    }

    // 3. Remove tracker_data() method and its docblock.
    content = content.replace(
        /\n\t\/\*\*\n\t \* Append plugin-specific data to the tracker payload[\s\S]*?\n\t\}\n(?=\n\t\/\*\*\n\t \* Show an admin notice)/,
        "\n"
    );

    if ( content.includes("public function tracker_data") ) {
        console.warn(`  ⚠  ${file}: tracker_data() method was not removed`);
    }

    fs.writeFileSync(file, content);
}

// Removes the reset_plugin_usage_tracking handler from class-settings.php.
function patchSettingsPhp(file) {
    let content = fs.readFileSync(file, "utf8");

    content = content.replace(
        /\n\t\tif \( isset\( \$params\['reset_plugin_usage_tracking'\] \) \) \{[\s\S]*?\n\t\t\}\n\n(?=\t\t\$old_settings)/,
        "\n\n\t\t"
    );

    if ( content.includes("reset_plugin_usage_tracking") ) {
        console.warn(`  ⚠  ${file}: reset_plugin_usage_tracking block was not removed`);
    }

    fs.writeFileSync(file, content);
}

// Removes the tracker display notice filter and method from class-scripts.php.
function patchScriptsPhp(file) {
    let content = fs.readFileSync(file, "utf8");

    // Remove add_filter line for tracker display notice.
    content = replaceOrWarn(
        content,
        "\t\tadd_filter( WCDN_SLUG . '_ts_tracker_display_notice', array( &$this, 'display_tracking_notice' ) );\n",
        "",
        file,
        "ts_tracker_display_notice filter"
    );

    // Remove display_tracking_notice() docblock and method.
    content = content.replace(
        /\n\t\/\*\*\n\t \* Display Tracking Notice\.[\s\S]*?\n\t\}\n(?=\})/,
        "\n"
    );

    if ( content.includes("display_tracking_notice") ) {
        console.warn(`  ⚠  ${file}: display_tracking_notice was not fully removed`);
    }

    fs.writeFileSync(file, content);
}

// Removes the Flexi BOGO cross-promotion block from readme.txt.
function patchReadmeTxt(file) {
    let content = fs.readFileSync(file, "utf8");

    content = content.replace(
        /> ###🚀[^\n]*\n>\n>[^\n]*\n\n/,
        ""
    );

    fs.writeFileSync(file, content);
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function replaceOrWarn(content, search, replacement, file, label) {
    if (!content.includes(search)) {
        console.warn(`  ⚠  ${path.basename(file)}: "${label}" marker not found — patch skipped`);
        return content;
    }
    return content.replace(search, replacement);
}
