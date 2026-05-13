import { __ } from "@wordpress/i18n";
import Button from "./Button";
import { useEffect, useState } from "@wordpress/element";
import Notice from "../Notice";

function SaveBar({ onSave, isSaving = false, hasChanges, notice, setNotice }) {
    const [atBottom, setAtBottom] = useState(false);

    useEffect(() => {
        const checkIfAtBottom = () => {
            const scrollPosition = window.innerHeight + window.scrollY;
            const pageHeight = document.documentElement.scrollHeight;
            setAtBottom(scrollPosition >= pageHeight - 5);
        };

        checkIfAtBottom();

        window.addEventListener("scroll", checkIfAtBottom);

        return () => window.removeEventListener("scroll", checkIfAtBottom);
    }, []);

    return (
        <div className={`wcdn-save-bar ${atBottom ? "is-bottom" : "is-floating"}`}>
            <div className="wcdn-save-bar-row">
                <Button variant="primary" onClick={onSave} isBusy={isSaving} disabled={!hasChanges}>
                    {__("Save Settings", "woocommerce-delivery-notes")}
                </Button>

                {notice && <Notice {...notice} onRemove={() => setNotice(null)} />}
            </div>
        </div>
    );
}

export default SaveBar;
