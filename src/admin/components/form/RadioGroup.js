import { RadioControl } from "@wordpress/components";
import TooltipLabel from "./Tooltip";

function RadioGroup({ label, tooltip, value, onChange, options = [], disabled, bottomLabel, ...props }) {
    const hasDescriptions = options.some((o) => o.description);

    return (
        <div className={`wcdn-radio-group ${props.className ?? ""}`}>
            <TooltipLabel label={label} tooltip={tooltip} />
            {hasDescriptions ? (
                <div className="wcdn-radio-options">
                    {options.map((option) => (
                        <label key={option.value} className={`wcdn-radio-option${disabled ? " is-disabled" : ""}`}>
                            <input
                                type="radio"
                                value={option.value}
                                checked={value === option.value}
                                onChange={() => !disabled && onChange(option.value)}
                                disabled={disabled}
                            />
                            <span className="wcdn-radio-option__body">
                                <span className="wcdn-radio-option__label">{option.label}</span>
                                <span className="wcdn-radio-option__description">{option.description}</span>
                            </span>
                        </label>
                    ))}
                </div>
            ) : (
                <RadioControl
                    selected={value}
                    options={options}
                    onChange={onChange}
                    disabled={disabled}
                />
            )}
            {bottomLabel && (
                <p className="wcdn-radio-group__help">{bottomLabel}</p>
            )}
        </div>
    );
}

export default RadioGroup;
