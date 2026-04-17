import { __ } from "@wordpress/i18n";
import { FormSection } from "@admin/components/form";
import SectionItem from "@admin/components/template/SectionItem";
import FieldRenderer from "@admin/components/template/FieldRenderer";

function Settings({ data, update, config }) {
    const disabled = !data.enabled;

    return config.map((section) => (
        <FormSection key={section.id} title={section.title} className="wcdn-template-settings">
            {section.items.map((item) => {
                if (item.type === "field") {
                    if (item.condition && !data[item.condition]) {
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
                    const enabled = item.toggle ? data[item.toggle] : true;

                    return (
                        <SectionItem
                            key={item.id}
                            label={item.label}
                            checked={enabled}
                            onToggle={item.toggle ? (v) => update(item.toggle, v) : undefined}
                            disabled={disabled}
                        >
                            {item.items.map((field) => (
                                <FieldRenderer
                                    key={field.field}
                                    {...field}
                                    type={field.fieldType}
                                    data={data}
                                    update={update}
                                    disabled={disabled}
                                    className="mt-20"
                                />
                            ))}
                        </SectionItem>
                    );
                }

                return null;
            })}
        </FormSection>
    ));
}

export default Settings;
