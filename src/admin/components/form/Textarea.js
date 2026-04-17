import { TextareaControl as WPTextarea } from "@wordpress/components";
import TooltipLabel from "./Tooltip";

function Textarea({ label, tooltip, value, onChange, help, bottomLabel, ...props }) {
    return (
        <>
            <WPTextarea
                label={<TooltipLabel label={label} tooltip={tooltip} />}
                value={value}
                onChange={onChange}
                help={help}
                {...props}
            />

            {bottomLabel && <p className="wcdn-form-bottom-label">{bottomLabel}</p>}
        </>
    );
}

export default Textarea;
