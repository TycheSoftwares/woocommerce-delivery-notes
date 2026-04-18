import { useState } from "@wordpress/element";
import { SelectControl as WPSelect, FormTokenField } from "@wordpress/components";
import TooltipLabel from "./Tooltip";

function Select({ label, tooltip, value, options, onChange, help, multiple, ...props }) {
    if (!multiple) {
        return (
            <WPSelect
                label={<TooltipLabel label={label} tooltip={tooltip} />}
                value={value}
                options={options}
                onChange={onChange}
                help={help}
                {...props}
            />
        );
    }

    const selectedValues = Array.isArray(value) ? value : [];

    const selectedLabels = selectedValues
        .map((v) => options.find((o) => o.value === v)?.label)
        .filter(Boolean);

    const suggestions = options
        .filter((o) => !selectedValues.includes(o.value))
        .map((o) => o.label);

    return (
        <div className={props.className}>
            {label && (
                <label className="components-base-control__label">
                    <TooltipLabel label={label} tooltip={tooltip} />
                </label>
            )}

            <FormTokenField
                label={null}
                help={null}
                value={selectedLabels}
                suggestions={suggestions}
                onChange={(tokens) => {
                    const values = tokens
                        .map((token) => {
                            const opt = options.find((o) => o.label === token);
                            return opt ? opt.value : null;
                        })
                        .filter(Boolean);

                    onChange(values);
                }}
                placeholder={props.bottomLabel}
            />
        </div>
    );
}

export default Select;
