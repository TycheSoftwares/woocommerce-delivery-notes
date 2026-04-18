import { Notice as WPNotice, Icon } from "@wordpress/components";
import { check, close } from "@wordpress/icons";
import { useEffect } from "@wordpress/element";

function Notice({ status, message, onRemove, timeout = 10000 }) {
    useEffect(() => {
        if (!onRemove) return;

        const timer = setTimeout(() => {
            onRemove();
        }, timeout);

        return () => clearTimeout(timer);
    }, [timeout, onRemove]);

    return (
        <WPNotice
            status={status}
            isDismissible
            onRemove={onRemove}
            className={`wcdn-notice wcdn-notice-${status}`}
        >
            <div className="wcdn-notice-inner">
                <Icon icon={status === "success" ? check : close} size={20} />
                <span>{message}</span>
            </div>
        </WPNotice>
    );
}

export default Notice;
