import { RangeControl } from "@wordpress/components";
import { Text, Checkbox, Select, RadioGroup, Color, Textarea } from "@admin/components/form";

function FieldRenderer({
    type,
    field,
    label,
    data,
    update,
    options = [],
    min = 10,
    max = 40,
    bottomLabel,
    disabled,
    className = "",
    step,
}) {
    const value = data[field];

    switch (type) {
        case "text":
            return (
                <Text
                    className={className}
                    label={label}
                    value={value}
                    onChange={(v) => update(field, v)}
                    bottomLabel={bottomLabel}
                    disabled={disabled}
                />
            );

        case "checkbox":
            return (
                <Checkbox
                    className={className}
                    label={label}
                    checked={value}
                    onChange={(v) => update(field, v)}
                    disabled={disabled}
                    bottomLabel={bottomLabel}
                />
            );

        case "select":
        case "multiselect":
            return (
                <Select
                    className={className}
                    label={label}
                    value={value}
                    options={options}
                    onChange={(v) => update(field, v)}
                    disabled={disabled}
                    multiple={"multiselect" === type}
                    bottomLabel={bottomLabel}
                />
            );

        case "radio":
            return (
                <RadioGroup
                    className={className}
                    label={label}
                    value={value}
                    options={options}
                    onChange={(v) => update(field, v)}
                    disabled={disabled}
                />
            );

        case "slider":
            return (
                <RangeControl
                    className={className}
                    label={label}
                    value={value}
                    min={min}
                    max={max}
                    step={step}
                    onChange={(v) => update(field, v)}
                    renderTooltipContent={(v) => `${v}px`}
                    disabled={disabled}
                />
            );

        case "color":
            return (
                <Color
                    className={className}
                    label={label}
                    value={value}
                    onChange={(v) => update(field, v)}
                    disabled={disabled}
                />
            );

        case "textarea":
            return (
                <Textarea
                    className={className}
                    label={label}
                    value={value}
                    onChange={(v) => update(field, v)}
                    bottomLabel={bottomLabel}
                    disabled={disabled}
                />
            );

        default:
            return null;
    }
}

export default FieldRenderer;
