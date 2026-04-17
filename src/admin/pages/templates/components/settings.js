import Invoice from "../categories/invoice/settings";
import Receipt from "../categories/receipt/settings";
import DeliveryNote from "../categories/deliverynote/settings";
import PackingSlip from "../categories/packingslip/settings";
import CreditNote from "../categories/creditnote/settings";

function Settings({ template, data, update, config }) {
    switch (template) {
        case "invoice":
            return <Invoice data={data} update={update} config={config} />;

        case "receipt":
            return <Receipt data={data} update={update} config={config} />;

        case "deliverynote":
            return <DeliveryNote data={data} update={update} config={config} />;

        case "packingslip":
            return <PackingSlip data={data} update={update} config={config} />;

        case "creditnote":
            return <CreditNote data={data} update={update} config={config} />;

        default:
            return null;
    }
}

export default Settings;
