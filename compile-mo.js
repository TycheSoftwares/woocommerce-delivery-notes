const { execSync } = require("child_process");
const fs = require("fs");

const files = fs.readdirSync("languages");

files.forEach((file) => {
    if (file.endsWith(".po")) {
        const mo = file.replace(".po", ".mo");

        execSync(`msgfmt languages/${file} -o languages/${mo}`, { stdio: "inherit" });
    }
});
