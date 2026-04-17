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

languages.forEach((lang) => {
    try {
        execSync(
            `msginit --no-translator --input=languages/woocommerce-delivery-notes.pot --locale=${lang} --output-file=languages/woocommerce-delivery-notes-${lang}.po`,
            { stdio: "inherit" }
        );
        const po = `languages/woocommerce-delivery-notes-${lang}.po`;
        let content = fs.readFileSync(po, "utf8");
        content = content.replace(/^"Language: .*"$/m, `"Language: ${lang}\\n"`);
        fs.writeFileSync(po, content);
    } catch (e) {
        console.warn(`⚠️ Skipped ${lang}`);
    }
});
