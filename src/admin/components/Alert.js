import { __ } from "@wordpress/i18n";
import { useState } from "@wordpress/element";

function Alert({ storageKey, children }) {
    const [visible, setVisible] = useState(
        () => localStorage.getItem(storageKey) !== "1"
    );

    if (!visible) return null;

    const dismiss = () => {
        localStorage.setItem(storageKey, "1");
        setVisible(false);
    };

    return (
        <div className="wcdn-alert">
            <span className="wcdn-alert-content">{children}</span>
            <button
                className="wcdn-alert-dismiss"
                onClick={dismiss}
                aria-label={__("Dismiss", "woocommerce-delivery-notes")}
            >
                ×
            </button>
        </div>
    );
}

export default Alert;
