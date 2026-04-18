import { RadioControl } from "@wordpress/components";
import TooltipLabel from "./Tooltip";

function RadioGroup({ label, tooltip, value, onChange, options = [], disabled, ...props }) {
    return (
        <div className={`wcdn-radio-group ${props.className ?? ""}`}>
            <TooltipLabel label={label} tooltip={tooltip} />
            <RadioControl
                selected={value}
                options={options}
                onChange={onChange}
                disabled={disabled}
            />
        </div>
    );
}

export default RadioGroup;
