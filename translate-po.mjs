#!/usr/bin/env node
/**
 * Machine-translate blank .po entries using DeepL API.
 *
 * Usage:
 *   DEEPL_API_KEY=your-key node translate-po.mjs
 *   DEEPL_API_KEY=your-key node translate-po.mjs --locale=de_DE
 *
 * After running: npm run i18n:mo  (to compile .mo files)
 */

import gettextParser from "gettext-parser";
import fs from "fs";
import path from "path";

const DEEPL_API_KEY = process.env.DEEPL_API_KEY;

// WP locale → DeepL target language code.
// en_US is the source language — skipped.
// fa_IR and vi are not supported by DeepL — skipped with a warning.
const LOCALE_MAP = {
    cs_CZ: "CS",
    da_DK: "DA",
    de_DE: "DE",
    el: "EL",
    es_ES: "ES",
    et: "ET",
    fi: "FI",
    fr_FR: "FR",
    it_IT: "IT",
    ja: "JA",
    ko_KR: "KO",
    nb_NO: "NB",
    nl_NL: "NL",
    pt_PT: "PT-PT",
    sv_SE: "SV",
};

const UNSUPPORTED = ["fa_IR", "vi"];
const LANGUAGES_DIR = "languages";
const CHUNK_SIZE = 50; // DeepL max per request

/**
 * Detect whether the key is for the free or paid DeepL plan.
 * Free keys end with ":fx".
 */
function getDeeplUrl() {
    return DEEPL_API_KEY?.endsWith(":fx")
        ? "https://api-free.deepl.com/v2/translate"
        : "https://api.deepl.com/v2/translate";
}

async function deeplTranslate(texts, targetLang) {
    const url = getDeeplUrl();

    const res = await fetch(url, {
        method: "POST",
        headers: {
            Authorization: `DeepL-Auth-Key ${DEEPL_API_KEY}`,
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            text: texts,
            source_lang: "EN",
            target_lang: targetLang,
        }),
    });

    if (!res.ok) {
        const body = await res.text().catch(() => "");
        throw new Error(`DeepL ${res.status}: ${body}`);
    }

    const data = await res.json();
    return data.translations.map((t) => t.text);
}

async function translateLocale(locale, targetLang) {
    const poPath = path.join(LANGUAGES_DIR, `woocommerce-delivery-notes-${locale}.po`);

    if (!fs.existsSync(poPath)) {
        console.warn(`  ⚠ ${locale}: file not found — skipping`);
        return;
    }

    const content = fs.readFileSync(poPath, "utf8");
    const parsed = gettextParser.po.parse(content);

    // Collect entries that need translation.
    // Each item: { ctxt, msgid, pluralIndex, text }
    const queue = [];

    for (const [ctxt, messages] of Object.entries(parsed.translations)) {
        for (const [msgid, entry] of Object.entries(messages)) {
            if (!msgid) continue; // header entry

            const msgstrArr = entry.msgstr;
            const hasPluralForms = !!entry.msgid_plural;

            msgstrArr.forEach((str, idx) => {
                if (str.trim()) return; // already translated

                // For plural entries: index 0 = singular form, rest = plural form.
                const sourceText = idx === 0 || !hasPluralForms ? msgid : entry.msgid_plural;
                queue.push({ ctxt, msgid, pluralIndex: idx, text: sourceText });
            });
        }
    }

    if (!queue.length) {
        console.log(`  ✓ ${locale}: already fully translated`);
        return;
    }

    console.log(`  → ${locale} (${targetLang}): translating ${queue.length} strings…`);

    // Batch requests.
    const texts = queue.map((q) => q.text);
    const translated = [];

    for (let i = 0; i < texts.length; i += CHUNK_SIZE) {
        const chunk = texts.slice(i, i + CHUNK_SIZE);
        const results = await deeplTranslate(chunk, targetLang);
        translated.push(...results);

        if (i + CHUNK_SIZE < texts.length) {
            await new Promise((r) => setTimeout(r, 300));
        }
    }

    // Write translations back into the parsed structure.
    queue.forEach(({ ctxt, msgid, pluralIndex }, i) => {
        parsed.translations[ctxt][msgid].msgstr[pluralIndex] = translated[i];
    });

    const compiled = gettextParser.po.compile(parsed);
    fs.writeFileSync(poPath, compiled);
    console.log(`  ✓ ${locale}: done (${translated.length} strings written)`);
}

async function main() {
    if (!DEEPL_API_KEY) {
        console.error(
            "Error: DEEPL_API_KEY environment variable is not set.\n" +
                "Get a free key at https://www.deepl.com/en/pro-api\n" +
                "Then run: DEEPL_API_KEY=your-key node translate-po.mjs"
        );
        process.exit(1);
    }

    // Allow targeting a single locale: --locale=de_DE
    const localeArg = process.argv.find((a) => a.startsWith("--locale="));
    const onlyLocale = localeArg ? localeArg.split("=")[1] : null;

    if (onlyLocale && UNSUPPORTED.includes(onlyLocale)) {
        console.error(`${onlyLocale} is not supported by DeepL.`);
        process.exit(1);
    }

    if (onlyLocale && !LOCALE_MAP[onlyLocale]) {
        console.error(`Unknown locale: ${onlyLocale}`);
        process.exit(1);
    }

    const locales = onlyLocale ? [[onlyLocale, LOCALE_MAP[onlyLocale]]] : Object.entries(LOCALE_MAP);

    console.log(`Translating ${locales.length} locale(s) via DeepL…\n`);

    for (const [locale, targetLang] of locales) {
        try {
            await translateLocale(locale, targetLang);
        } catch (err) {
            console.error(`  ✗ ${locale}: ${err.message}`);
        }
    }

    if (!onlyLocale && UNSUPPORTED.length) {
        console.log(
            `\n⚠ Skipped (not supported by DeepL): ${UNSUPPORTED.join(", ")}\n` +
                `  These will need to be translated separately.`
        );
    }

    console.log("\nDone. Run  npm run i18n:mo  to compile the .mo files.");
}

main();
