const { execSync } = require("child_process");
const fs = require("fs");

const languages = [
    "en_US",
    "cs_CZ",
    "da_DK",
    "de_DE",
    "el",
    "es_ES",
    "et",
    "fa_IR",
    "fi",
    "fr_FR",
    "it_IT",
    "ja",
    "ko_KR",
    "nb_NO",
    "nl_NL",
    "pt_PT",
    "sv_SE",
    "vi",
];

const pot = "languages/woocommerce-delivery-notes.pot";

languages.forEach((lang) => {
    const po = `languages/woocommerce-delivery-notes-${lang}.po`;

    try {
        if (fs.existsSync(po)) {
            // Merge new strings from the .pot into the existing .po,
            // preserving all already-translated msgstr values.
            execSync(`msgmerge --update --backup=none --no-fuzzy-matching ${po} ${pot}`, {
                stdio: "inherit",
            });
        } else {
            // First time: create a fresh file from the .pot.
            execSync(
                `msginit --no-translator --input=${pot} --locale=${lang} --output-file=${po}`,
                { stdio: "inherit" }
            );
            let content = fs.readFileSync(po, "utf8");
            content = content.replace(/^"Language: .*"$/m, `"Language: ${lang}\\n"`);
            fs.writeFileSync(po, content);
        }

        console.log(`✓ ${lang}`);
    } catch (e) {
        console.warn(`⚠️ Skipped ${lang}: ${e.message}`);
    }
});
