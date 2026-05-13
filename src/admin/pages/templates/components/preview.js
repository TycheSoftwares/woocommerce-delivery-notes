import Invoice from "../categories/invoice/preview";
import Receipt from "../categories/receipt/preview";
import DeliveryNote from "../categories/deliverynote/preview";
import PackingSlip from "../categories/packingslip/preview";
import CreditNote from "../categories/creditnote/preview";
import { useRef, useState, useEffect } from "@wordpress/element";

const PAPER_SIZES = {
    A4:     { width: 794,  height: 1123 },
    A3:     { width: 1123, height: 1587 },
    A5:     { width: 559,  height: 794  },
    letter: { width: 816,  height: 1056 },
    legal:  { width: 816,  height: 1344 },
};

function Preview({ template, settings, preview, pdfPaperSize }) {
    const paper = PAPER_SIZES[pdfPaperSize] ?? PAPER_SIZES.A4;
    const wrapperRef = useRef(null);
    const [scale, setScale] = useState(1);

    useEffect(() => {
        function updateScale() {
            if (!wrapperRef.current) return;
            const width = wrapperRef.current.offsetWidth;
            setScale(Math.min(width / paper.width, 1));
        }

        updateScale();
        window.addEventListener("resize", updateScale);
        return () => window.removeEventListener("resize", updateScale);
    }, [paper.width]);

    return (
        <div ref={wrapperRef} className="wcdn-preview-wrapper" style={{ minHeight: paper.height * scale }}>
            <div
                className="wcdn-preview-canvas"
                style={{
                    width: paper.width,
                    minHeight: paper.height,
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
