import { CheckboxControl as WPCheckbox } from "@wordpress/components";
import TooltipLabel from "./Tooltip";

function Checkbox({ label, tooltip, checked, onChange, help, ...props }) {
    return (
        <WPCheckbox
            label={<TooltipLabel label={label} tooltip={tooltip} />}
            checked={checked}
            onChange={onChange}
            help={props?.bottomLabel}
            {...props}
        />
    );
}

export default Checkbox;
