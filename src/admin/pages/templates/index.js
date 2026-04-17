import { __, sprintf } from "@wordpress/i18n";
import { TabPanel } from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import Skeleton from "./skeleton";
import Settings from "./components/settings";
import Preview from "./components/preview";
import { SaveBar } from "@admin/components/form";
import { fetch as fetchTemplates, save as saveTemplates } from "../../api/templates";
import { TEXT_DOMAIN } from "../../constants";
import { toast } from "../../utils/toast";

const TEMPLATE_LABELS = {
    invoice: __("Invoice", TEXT_DOMAIN),
    receipt: __("Receipt", TEXT_DOMAIN),
    deliverynote: __("Delivery Note", TEXT_DOMAIN),
    packingslip: __("Packing Slip", TEXT_DOMAIN),
    creditnote: __("Credit Note", TEXT_DOMAIN),
};

function Templates() {
    const [templates, setTemplates] = useState(null);
    const [preview, setPreview] = useState(null);
    const [config, setConfig] = useState(null);
    const [activeTemplate, setActiveTemplate] = useState("invoice");
    const [isSaving, setIsSaving] = useState(false);
    const [isLoading, setIsLoading] = useState(true);
    const [hasChanges, setHasChanges] = useState(false);
    const [notice, setNotice] = useState(null);
    const currentSettings = null !== templates ? templates[activeTemplate] : {};

    useEffect(() => {
        if (templates) return;

        fetchTemplates()
            .then((response) => {
                setTemplates(response.templates);
                setPreview(response.preview);
                setConfig(response.config);
            })
            .catch(() => {
                toast.error(__("Failed to load templates.", TEXT_DOMAIN));
            })
            .finally(() => {
                setIsLoading(false);
            });
    }, []);

    const updateTemplate = (field, value) => {
        setTemplates((prev) => ({
            ...prev,
            [activeTemplate]: {
                ...prev[activeTemplate],
                [field]: value,
            },
        }));

        setHasChanges(true);
    };

    const saveHandler = async () => {
        const label = TEMPLATE_LABELS[activeTemplate];

        setIsSaving(true);

        try {
            await saveTemplates({
                template: activeTemplate,
                data: templates[activeTemplate],
            });
            setHasChanges(false);
            setNotice({
                status: "success",
                message: sprintf(__("%s template saved successfully.", TEXT_DOMAIN), label),
            });
        } catch {
            toast.error(sprintf(__("Failed to save %s template.", TEXT_DOMAIN), label));
        }

        setIsSaving(false);
    };

    if (isLoading || !templates) {
        return <Skeleton />;
    }

    return (
        <div className="wcdn-card-page">
            <TabPanel
                className="wcdn-card-tabs"
                activeClass="is-active"
                tabs={[
                    { name: "invoice", title: __("Invoice", TEXT_DOMAIN) },
                    { name: "receipt", title: __("Receipt", TEXT_DOMAIN) },
                    { name: "deliverynote", title: __("Delivery Note", TEXT_DOMAIN) },
                    { name: "creditnote", title: __("Credit Note", TEXT_DOMAIN) },
                    { name: "packingslip", title: __("Packing Slip", TEXT_DOMAIN) },
                ]}
            >
                {(tab) => {
                    setTimeout(() => setActiveTemplate(tab.name), 0);

                    return (
                        <div className="wcdn-templates-layout">
                            <div className="wcdn-templates-left">
                                <Settings
                                    template={activeTemplate}
                                    data={currentSettings}
                                    update={updateTemplate}
                                    config={config[activeTemplate]}
                                />
                            </div>

                            <div className="wcdn-templates-right">
                                <Preview
                                    template={activeTemplate}
                                    settings={currentSettings}
                                    preview={preview}
                                />
                            </div>
                        </div>
                    );
                }}
            </TabPanel>

            <SaveBar
                onSave={saveHandler}
                isSaving={isSaving}
                hasChanges={hasChanges}
                notice={notice}
                setNotice={setNotice}
            />
        </div>
    );
}

export default Templates;
