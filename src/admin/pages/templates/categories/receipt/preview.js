import { __ } from "@wordpress/i18n";
import Preview from "../../shared/preview";

function Receipt({ template, settings, preview }) {
    return <Preview template={template} settings={settings} preview={preview} />;
}

export default Receipt;
