import { __ } from "@wordpress/i18n";
import Settings from "../../shared/settings";

function Invoice({ data, update, config }) {
    return <Settings data={data} update={update} config={config} />;
}

export default Invoice;
