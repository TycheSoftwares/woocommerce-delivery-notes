import { __ } from "@wordpress/i18n";
import Preview from "../../shared/preview";

function CreditNote({ template, settings, preview }) {
    return <Preview template={template} settings={settings} preview={preview} />;
}

export default CreditNote;
