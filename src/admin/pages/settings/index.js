import { __ } from "@wordpress/i18n";
import { TabPanel } from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import Skeleton from "./skeleton";
import { SaveBar } from "@admin/components/form";
import { TEXT_DOMAIN } from "../../constants";
import { fetch as fetchSettings, save as saveSettings } from "../../api/settings";
import { clearCache as clearTemplatesCache } from "../../api/templates";
import { toast } from "../../utils/toast";
import StoreSettings from "./StoreSettings";
import GeneralSettings from "./GeneralSettings";
import FontSettings from "./FontSettings";

function Settings() {
    const [settings, setSettings] = useState(null);
    const [isSaving, setIsSaving] = useState(false);
    const [isLoading, setIsLoading] = useState(true);
    const [hasChanges, setHasChanges] = useState(false);
    const [notice, setNotice] = useState(null);
    const [activeTab, setActiveTab] = useState("store");

    useEffect(() => {
        let mounted = true;

        fetchSettings()
            .then((data) => {
                if (!mounted) return;
                setSettings(data || null);
            })
            .catch((error) => {
                toast.error(error || __("Failed to load settings data", TEXT_DOMAIN));
            })
            .finally(() => {
                if (mounted) setIsLoading(false);
            });

        return () => {
            mounted = false;
        };
    }, []);

    const updateSetting = (field, value) => {
        setSettings((prev) => ({
            ...prev,
            [field]: value,
        }));

        setHasChanges(true);
    };

    const saveSettingsHandler = async (context = "") => {
        const is_reset_plugin_settings = "reset_plugin_usage_tracking" === context;

        if (!is_reset_plugin_settings && (isSaving || hasErrors)) {
            if (hasErrors) {
                toast.error(__("Please fix validation errors before saving.", TEXT_DOMAIN));
            }
            return;
        }

        setIsSaving(true);

        try {
            const response = await saveSettings(
                is_reset_plugin_settings ? { reset_plugin_usage_tracking: true } : settings
            );

            clearTemplatesCache();

            setTimeout(() => {
                setIsSaving(false);
                setHasChanges(false);

                setNotice({
                    status: "success",
                    message: response?.message,
                });
            }, 800);
        } catch (error) {
            setNotice({
                status: "error",
                message:
                    error ||
                    (is_reset_plugin_settings
                        ? __("Failed to reset plugin usage tracking settings.", TEXT_DOMAIN)
                        : __("Failed to save settings.", TEXT_DOMAIN)),
            });

            toast.error(error);
            setIsSaving(false);
        }
    };

    if (isLoading || !settings) {
        return <Skeleton />;
    }

    const validation = {
        yearFormat:
            settings.resetInvoiceNumberYearly && !settings.invoiceNumberFormat.includes("{year}"),
        nextNumber: parseInt(settings.nextInvoiceNumber) < (settings?.meta?.maxInvoiceNumber || 0),
    };

    const hasErrors = validation.yearFormat || validation.nextNumber;

    return (
        <div className="wcdn-card-page">
            <TabPanel
                className="wcdn-card-tabs"
                activeClass="is-active"
                tabs={[
                    {
                        name: "store",
                        title: __("Store Settings", TEXT_DOMAIN),
                    },
                    {
                        name: "general",
                        title: __("General Settings", TEXT_DOMAIN),
                    },
                    {
                        name: "fonts",
                        title: __("Font Settings", TEXT_DOMAIN),
                    },
                ]}
                onSelect={setActiveTab}
            >
                {(tab) => (
                    <>
                        {tab.name === "store" && (
                            <StoreSettings
                                settings={settings}
                                updateSetting={updateSetting}
                            />
                        )}

                        {tab.name === "general" && (
                            <GeneralSettings
                                settings={settings}
                                updateSetting={updateSetting}
                                validation={validation}
                                saveSettingsHandler={saveSettingsHandler}
                            />
                        )}

                        {tab.name === "fonts" && <FontSettings />}
                    </>
                )}
            </TabPanel>

            {activeTab !== "fonts" && (
                <SaveBar
                    onSave={saveSettingsHandler}
                    isSaving={isSaving}
                    hasChanges={hasChanges && !hasErrors}
                    notice={notice}
                    setNotice={setNotice}
                />
            )}
        </div>
    );
}

export default Settings;
