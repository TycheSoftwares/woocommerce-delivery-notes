import Invoice from "../categories/invoice/preview";
import Receipt from "../categories/receipt/preview";
import DeliveryNote from "../categories/deliverynote/preview";
import PackingSlip from "../categories/packingslip/preview";
import CreditNote from "../categories/creditnote/preview";
import { useRef, useState, useEffect } from "@wordpress/element";

const DOCUMENT_WIDTH = 650;

function Preview({ template, settings, preview }) {
    const wrapperRef = useRef(null);
    const [scale, setScale] = useState(1);

    useEffect(() => {
        function updateScale() {
            if (!wrapperRef.current) return;
            const width = wrapperRef.current.offsetWidth;
            const newScale = width / DOCUMENT_WIDTH;
            setScale(Math.min(newScale, 1));
        }

        updateScale();
        window.addEventListener("resize", updateScale);
        return () => window.removeEventListener("resize", updateScale);
    }, []);

    return (
        <div ref={wrapperRef} className="wcdn-preview-wrapper">
            <div
                className="wcdn-preview-canvas"
                style={{
                    transform: `scale(${scale})`,
                    transformOrigin: "left top",
                }}
            >
                {template === "invoice" && (
                    <Invoice template={template} settings={settings} preview={preview} />
                )}
                {template === "receipt" && (
                    <Receipt template={template} settings={settings} preview={preview} />
                )}
                {template === "deliverynote" && (
                    <DeliveryNote template={template} settings={settings} preview={preview} />
                )}
                {template === "packingslip" && (
                    <PackingSlip template={template} settings={settings} preview={preview} />
                )}
                {template === "creditnote" && (
                    <CreditNote template={template} settings={settings} preview={preview} />
                )}
            </div>
        </div>
    );
}

export default Preview;
