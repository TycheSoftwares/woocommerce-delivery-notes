import { __ } from "@wordpress/i18n";
import { FormSection } from "@admin/components/form";
import SectionItem from "@admin/components/template/SectionItem";
import FieldRenderer from "@admin/components/template/FieldRenderer";

function Settings({ data, update, config }) {
    const disabled = !data.enabled;

    return config.map((section) => (
        <FormSection key={section.id} title={section.title} className="wcdn-template-settings">
            {section.items.map((item) => {
                const conditionMet = !item.condition || (
                    item.conditionValue !== undefined
                        ? data[item.condition] === item.conditionValue
                        : !!data[item.condition]
                );

                if (item.type === "field") {
                    if (!conditionMet) {
                        return null;
                    }

                    return (
                        <FieldRenderer
                            key={item.field}
                            {...item}
                            type={item.fieldType}
                            data={data}
                            update={update}
                            disabled={"enabled" === item.field ? false : disabled}
                            className="mt-20"
                        />
                    );
                }

                if (item.type === "group") {
                    if (!conditionMet) {
                        return null;
                    }

                    const enabled = item.toggle ? data[item.toggle] : true;

                    return (
                        <SectionItem
                            key={item.id}
                            label={item.label}
                            checked={enabled}
                            onToggle={item.toggle ? (v) => update(item.toggle, v) : undefined}
                            disabled={disabled}
                        >
                            {item.items.map((field) => {
                                const subConditionMet = !field.condition || (
                                    field.conditionValue !== undefined
                                        ? data[field.condition] === field.conditionValue
                                        : !!data[field.condition]
                                );

                                if (!subConditionMet) return null;

                                let fieldDisabled = disabled;
                                if (!fieldDisabled && field.disabledWhen) {
                                    fieldDisabled =
                                        data[field.disabledWhen.field] ===
                                        field.disabledWhen.value;
                                }
                                return (
                                    <FieldRenderer
                                        key={field.field}
                                        {...field}
                                        type={field.fieldType}
                                        data={data}
                                        update={update}
                                        disabled={fieldDisabled}
                                        className="mt-20"
                                    />
                                );
                            })}
                        </SectionItem>
                    );
                }

                return null;
            })}
        </FormSection>
    ));
}

export default Settings;
