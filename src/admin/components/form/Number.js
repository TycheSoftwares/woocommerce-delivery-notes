import { __experimentalNumberControl as NumberControl } from "@wordpress/components";
import TooltipLabel from "./Tooltip";

function Number({ label, tooltip, value, onChange, help, bottomLabel, ...props }) {
    return (
        <>
            <NumberControl
                label={<TooltipLabel label={label} tooltip={tooltip} />}
                value={value}
                onChange={onChange}
                help={help}
                {...props}
            />

            {bottomLabel && "" !== bottomLabel && !props?.error && (
                <p className="wcdn-form-bottom-label">{bottomLabel}</p>
            )}

            {props?.error && <p className="wcdn-error">{props.error}</p>}
        </>
    );
}

export default Number;
