import { __ } from "@wordpress/i18n";
import Settings from "../../shared/settings";

function PackingSlip({ data, update, config }) {
    return <Settings data={data} update={update} config={config} />;
}

export default PackingSlip;
