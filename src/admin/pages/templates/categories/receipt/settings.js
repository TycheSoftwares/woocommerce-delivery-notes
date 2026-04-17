import { __ } from "@wordpress/i18n";
import Settings from "../../shared/settings";

function Receipt({ data, update, config }) {
    return <Settings data={data} update={update} config={config} />;
}

export default Receipt;
