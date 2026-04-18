import { Tooltip as WPTooltip } from "@wordpress/components";

function Tooltip({ label, tooltip }) {
    if (!tooltip) {
        return label;
    }

    return (
        <div className="wcdn-label-with-tooltip">
            <span>{label}</span>

            <WPTooltip text={tooltip}>
                <span className="wcdn-tooltip-icon">?</span>
            </WPTooltip>
        </div>
    );
}

export default Tooltip;
