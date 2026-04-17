import { BaseControl } from "@wordpress/components";

function Color({ label, value, onChange, disabled, className = "" }) {
    return (
        <BaseControl label={label} className={`wcdn-color-control ${className}`}>
            <input
                type="color"
                value={value}
                onChange={(e) => onChange(e.target.value)}
                disabled={disabled}
            />
        </BaseControl>
    );
}

export default Color;
