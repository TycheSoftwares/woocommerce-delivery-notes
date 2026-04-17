import { __, sprintf } from "@wordpress/i18n";
import { TabPanel, Button } from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import Skeleton from "./skeleton";
import {
    FormSection,
    Text,
    Textarea,
    Checkbox,
    MediaUpload,
    SaveBar,
    RadioGroup,
    Number,
} from "@admin/components/form";
import { TEXT_DOMAIN } from "../../constants";
import { fetch as fetchSettings, save as saveSettings } from "../../api/settings";
import { toast } from "../../utils/toast";
import { useData } from "@admin/data/context";

function Settings() {
    const [settings, setSettings] = useState(null);
    const [isSaving, setIsSaving] = useState(false);
    const [isLoading, setIsLoading] = useState(true);
    const [hasChanges, setHasChanges] = useState(false);
    const [notice, setNotice] = useState(null);
    const { setShowLoader } = useData();

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

        if (!is_reset_plugin_settings & (isSaving || hasErrors)) {
            if (hasErrors) {
                toast.error(__("Please fix validation errors before saving.", TEXT_DOMAIN));
            }
            return;
        }

        setIsSaving(true);
        setShowLoader(true);

        try {
            const response = await saveSettings(
                is_reset_plugin_settings ? { reset_plugin_usage_tracking: true } : settings
            );

            setTimeout(() => {
                setIsSaving(false);
                setHasChanges(false);
                setShowLoader(false);

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
            setShowLoader(false);
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
                ]}
            >
                {(tab) => (
                    <>
                        {tab.name === "store" && (
                            <div>
                                <FormSection title={__("Store Identity", TEXT_DOMAIN)}>
                                    <Text
                                        label={__("Store Name", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Your business or store name that will appear on all documents.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.storeName}
                                        onChange={(v) => updateSetting("storeName", v)}
                                    />

                                    <MediaUpload
                                        className="mt-20"
                                        label={__("Store Logo", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Logo that will be displayed at the top of documents",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.storeLogo}
                                        onChange={(v) => updateSetting("storeLogo", v)}
                                        help={__("Recommended size: 300×100px.", TEXT_DOMAIN)}
                                    />
                                </FormSection>

                                <FormSection title={__("Store Information", TEXT_DOMAIN)}>
                                    <Textarea
                                        label={__("Store Address", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "This address will appear on invoices and other documents as the sender/from address.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.storeAddress}
                                        onChange={(v) => updateSetting("storeAddress", v)}
                                        bottomLabel={__(
                                            "Complete address including street, city, state, postal code and country",
                                            TEXT_DOMAIN
                                        )}
                                    />

                                    <Text
                                        className="mt-20"
                                        label={__("Contact Email", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Primary email address for customer inquiries and support.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.email}
                                        onChange={(v) => updateSetting("email", v)}
                                    />

                                    <Text
                                        className="mt-20"
                                        label={__("Contact Phone", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Phone number printed on documents.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.phone}
                                        onChange={(v) => updateSetting("phone", v)}
                                    />
                                </FormSection>

                                <FormSection title={__("Footer Content", TEXT_DOMAIN)}>
                                    <Textarea
                                        label={__("Footer Text", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Appears at the bottom of printed documents.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.footerText}
                                        onChange={(v) => updateSetting("footerText", v)}
                                        bottomLabel={__(
                                            "This text will appear at the bottom of all documents",
                                            TEXT_DOMAIN
                                        )}
                                    />
                                </FormSection>

                                <FormSection title={__("Complimentary Close", TEXT_DOMAIN)}>
                                    <Textarea
                                        label={__("Complimentary Close", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "A formal closing statement, typically including a sign-off and signature line.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.complimentaryClose}
                                        onChange={(v) => updateSetting("complimentaryClose", v)}
                                        bottomLabel={__(
                                            "Closing message to appear on documents",
                                            TEXT_DOMAIN
                                        )}
                                    />
                                </FormSection>

                                <FormSection title={__("Policies", TEXT_DOMAIN)}>
                                    <Textarea
                                        label={__("Policies", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Legal and policy information to display on documents for customer reference.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.policies}
                                        onChange={(v) => updateSetting("policies", v)}
                                        bottomLabel={__(
                                            "Return policy, privacy policy, and terms & conditions",
                                            TEXT_DOMAIN
                                        )}
                                    />
                                </FormSection>
                            </div>
                        )}

                        {tab.name === "general" && (
                            <div>
                                <FormSection title={__("Print Page", TEXT_DOMAIN)}>
                                    <Text
                                        label={__("Print Page Endpoint", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "The URL path customers will ue to access their printable documents.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.printEndpoint}
                                        onChange={(v) => updateSetting("printEndpoint", v)}
                                        bottomLabel={__(
                                            "Custom URL slug for the print page",
                                            TEXT_DOMAIN
                                        )}
                                    />

                                    <Text
                                        className="mt-20"
                                        label={__("Page Title (Browser Tab)", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "The title shown in the browser tab when viewing a document. This does not affect the document content or PDF title.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.defaultDocumentLabel}
                                        onChange={(v) => updateSetting("defaultDocumentLabel", v)}
                                        bottomLabel={__(
                                            "Controls the text shown in the browser tab when a document is opened.",
                                            TEXT_DOMAIN
                                        )}
                                    />
                                </FormSection>

                                <FormSection title={__("Invoice", TEXT_DOMAIN)}>
                                    <Text
                                        label={__("Invoice Number Format", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Format for the Invoice Number used across all documents.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.invoiceNumberFormat}
                                        onChange={(v) => updateSetting("invoiceNumberFormat", v)}
                                        bottomLabel={
                                            settings.resetInvoiceNumberYearly &&
                                            !settings.invoiceNumberFormat.includes("{year}")
                                                ? __(
                                                      "Year placeholder is recommended when yearly reset is enabled.",
                                                      TEXT_DOMAIN
                                                  )
                                                : __(
                                                      "Available placeholders: {next_number}, {order_number}, {order_date}, {customer_name}, {year}, {month}, {day}, {site_name}, {customer_id}",
                                                      TEXT_DOMAIN
                                                  )
                                        }
                                        error={
                                            validation.yearFormat
                                                ? __(
                                                      "ERROR: Year placeholder {year} is required when yearly reset is enabled.",
                                                      TEXT_DOMAIN
                                                  )
                                                : ""
                                        }
                                    />

                                    <Number
                                        className="mt-20"
                                        label={__("Next Invoice Number", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "The next number that will be assigned to a newly generated invoice.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.nextInvoiceNumber}
                                        min={1}
                                        onChange={(v) => updateSetting("nextInvoiceNumber", v)}
                                        bottomLabel={__(
                                            "Used to generate invoice numbers sequentially. This will increment automatically after each invoice is created. Changing this will only affect future invoices.",
                                            TEXT_DOMAIN
                                        )}
                                        error={
                                            validation.nextNumber
                                                ? sprintf(
                                                      __(
                                                          "ERROR: Value must be greater than the current next invoice number value - %d.",
                                                          TEXT_DOMAIN
                                                      ),
                                                      settings.meta?.maxInvoiceNumber || 1
                                                  )
                                                : ""
                                        }
                                    />

                                    <Checkbox
                                        className="mt-20"
                                        label={__("Reset Invoice Numbers yearly", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Automatically restart invoice numbering at the beginning of each year.",
                                            TEXT_DOMAIN
                                        )}
                                        checked={settings.resetInvoiceNumberYearly}
                                        onChange={(v) =>
                                            updateSetting("resetInvoiceNumberYearly", v)
                                        }
                                    />

                                    {settings.resetInvoiceNumberYearly && (
                                        <Number
                                            className="mt-20"
                                            min={1}
                                            label={__(
                                                "Starting number for each new year",
                                                TEXT_DOMAIN
                                            )}
                                            tooltip={__(
                                                "The number assigned to the first invoice of every new year.",
                                                TEXT_DOMAIN
                                            )}
                                            value={settings.startingNumberForEachYear}
                                            onChange={(v) =>
                                                updateSetting("startingNumberForEachYear", v)
                                            }
                                        />
                                    )}
                                </FormSection>

                                <FormSection title={__("Document Formatting", TEXT_DOMAIN)}>
                                    <RadioGroup
                                        label={__("Text Direction", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Set the text direction for all documents. Use RTL for languages like Arabic or Hebrew.",
                                            TEXT_DOMAIN
                                        )}
                                        value={settings.textDirection}
                                        onChange={(v) => updateSetting("textDirection", v)}
                                        options={[
                                            {
                                                label: __("Left to Right (LTR)", TEXT_DOMAIN),
                                                value: "ltr",
                                            },
                                            {
                                                label: __("Right to Left (RTL)", TEXT_DOMAIN),
                                                value: "rtl",
                                            },
                                        ]}
                                    />
                                </FormSection>

                                <FormSection title={__("PDF Handling", TEXT_DOMAIN)}>
                                    <Checkbox
                                        label={__("Enable PDF generation", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Allow the plugin to generate PDF versions of documents for download and email attachment.",
                                            TEXT_DOMAIN
                                        )}
                                        checked={settings.enablePDF}
                                        onChange={(v) => updateSetting("enablePDF", v)}
                                    />

                                    <Checkbox
                                        className="mt-20"
                                        label={__("Store generated PDFs", TEXT_DOMAIN)}
                                        tooltip={__(
                                            "Save generated PDFs to your website for faster access.",
                                            TEXT_DOMAIN
                                        )}
                                        checked={settings.enablePDFStorage}
                                        onChange={(v) => updateSetting("enablePDFStorage", v)}
                                    />

                                    {settings.enablePDFStorage && (
                                        <Number
                                            className="mt-20"
                                            label={__("Days to Keep Generated PDFs", TEXT_DOMAIN)}
                                            tooltip={__(
                                                "Number of days for expiration after which generated PDFs will be deleted.",
                                                TEXT_DOMAIN
                                            )}
                                            value={settings.numberDaysPdfExpiration}
                                            onChange={(v) =>
                                                updateSetting("numberDaysPdfExpiration", v)
                                            }
                                            bottomLabel={__(
                                                "Number of days for expiration after which generated PDFs will be deleted.",
                                                TEXT_DOMAIN
                                            )}
                                        />
                                    )}
                                </FormSection>

                                <FormSection title={__("Print Links", TEXT_DOMAIN)}>
                                    <Checkbox
                                        label={__(
                                            "Show print link in customer emails",
                                            TEXT_DOMAIN
                                        )}
                                        checked={settings.showCustomerEmailLink}
                                        tooltip={__(
                                            "Add a link in order confirmation emails allowing customers to view and print their documents.",
                                            TEXT_DOMAIN
                                        )}
                                        onChange={(v) => updateSetting("showCustomerEmailLink", v)}
                                    />

                                    {settings.showCustomerEmailLink && (
                                        <Text
                                            className="mt-10 mb-30"
                                            label={__("Customer Email Link Text", TEXT_DOMAIN)}
                                            value={settings.customerEmailText}
                                            onChange={(v) => updateSetting("customerEmailText", v)}
                                            tooltip={__(
                                                "The text that will appear as the clickable link in customer emails.",
                                                TEXT_DOMAIN
                                            )}
                                        />
                                    )}

                                    <Checkbox
                                        className="mt-20"
                                        label={__("Show print link in admin emails", TEXT_DOMAIN)}
                                        checked={settings.showAdminEmailLink}
                                        onChange={(v) => updateSetting("showAdminEmailLink", v)}
                                        tooltip={__(
                                            "Add a print link in order notification emails sent to store administrators.",
                                            TEXT_DOMAIN
                                        )}
                                    />

                                    {settings.showAdminEmailLink && (
                                        <Text
                                            className="mt-10"
                                            label={__("Admin Email Link Text", TEXT_DOMAIN)}
                                            value={settings.adminEmailText}
                                            onChange={(v) => updateSetting("adminEmailText", v)}
                                            tooltip={__(
                                                "The text that will appear as the clickable link in admin notification emails.",
                                                TEXT_DOMAIN
                                            )}
                                        />
                                    )}
                                </FormSection>

                                <FormSection title={__("My Account Print Buttons", TEXT_DOMAIN)}>
                                    <Checkbox
                                        label={__(
                                            "Show print button on My Account Page",
                                            TEXT_DOMAIN
                                        )}
                                        checked={settings.showPrintButtonMyAccountPage}
                                        onChange={(v) =>
                                            updateSetting("showPrintButtonMyAccountPage", v)
                                        }
                                        tooltip={__(
                                            "Display a print button on the My Account page",
                                            TEXT_DOMAIN
                                        )}
                                    />

                                    {settings.showPrintButtonMyAccountPage && (
                                        <Text
                                            className="mt-10 mb-30"
                                            label={__("My Account Page Button Label", TEXT_DOMAIN)}
                                            value={settings.myAccountPageButtonLabel}
                                            onChange={(v) =>
                                                updateSetting("myAccountPageButtonLabel", v)
                                            }
                                            tooltip={__(
                                                "Text displayed on the print button on the My Account Page.",
                                                TEXT_DOMAIN
                                            )}
                                        />
                                    )}

                                    <Checkbox
                                        className="mt-20"
                                        label={__(
                                            "Show print button on View Order page",
                                            TEXT_DOMAIN
                                        )}
                                        checked={settings.showViewOrderButton}
                                        onChange={(v) => updateSetting("showViewOrderButton", v)}
                                        tooltip={__(
                                            "Display a print button on the order details page when viewing a specific order",
                                            TEXT_DOMAIN
                                        )}
                                    />

                                    {settings.showViewOrderButton && (
                                        <Text
                                            className="mt-10"
                                            label={__("View Order Button Label", TEXT_DOMAIN)}
                                            value={settings.viewOrderButtonLabel}
                                            onChange={(v) =>
                                                updateSetting("viewOrderButtonLabel", v)
                                            }
                                            tooltip={__(
                                                "Text displayed on the print button on View Order page",
                                                TEXT_DOMAIN
                                            )}
                                        />
                                    )}
                                </FormSection>

                                <FormSection title={__("Button Label for Templates", TEXT_DOMAIN)}>
                                    <Text
                                        label={__("Invoice Print Button Label", TEXT_DOMAIN)}
                                        value={settings.invoiceButtonLabel}
                                        onChange={(v) => updateSetting("invoiceButtonLabel", v)}
                                        tooltip={__(
                                            "Label for the invoice print button.",
                                            TEXT_DOMAIN
                                        )}
                                    />

                                    <Text
                                        className="mt-20"
                                        label={__("Delivery Note Print Button Label", TEXT_DOMAIN)}
                                        value={settings.deliveryNoteButtonLabel}
                                        onChange={(v) =>
                                            updateSetting("deliveryNoteButtonLabel", v)
                                        }
                                        tooltip={__(
                                            "Label for the delivery note print button.",
                                            TEXT_DOMAIN
                                        )}
                                    />

                                    <Text
                                        className="mt-20"
                                        label={__("Receipt Print Button Label", TEXT_DOMAIN)}
                                        value={settings.receiptButtonLabel}
                                        onChange={(v) => updateSetting("receiptButtonLabel", v)}
                                        tooltip={__(
                                            "Label for the receipt print button.",
                                            TEXT_DOMAIN
                                        )}
                                    />

                                    <Text
                                        className="mt-20"
                                        label={__("Credit Note Print Button Label", TEXT_DOMAIN)}
                                        value={settings.creditNoteButtonLabel}
                                        onChange={(v) => updateSetting("creditNoteButtonLabel", v)}
                                        tooltip={__(
                                            "Label for the credit note print button.",
                                            TEXT_DOMAIN
                                        )}
                                    />

                                    <Text
                                        className="mt-20"
                                        label={__("Packing Slip Print Button Label", TEXT_DOMAIN)}
                                        value={settings.packingSlipButtonLabel}
                                        onChange={(v) => updateSetting("packingSlipButtonLabel", v)}
                                        tooltip={__(
                                            "Label for the packing slip print button.",
                                            TEXT_DOMAIN
                                        )}
                                    />
                                </FormSection>

                                <FormSection title={__("Plugin Usage Tracking", TEXT_DOMAIN)}>
                                    <Button
                                        variant="secondary"
                                        className="wcdn-reset-plugin-usage-button"
                                        onClick={() =>
                                            saveSettingsHandler("reset_plugin_usage_tracking")
                                        }
                                    >
                                        {__("Reset Tracking Settings", TEXT_DOMAIN)}
                                    </Button>
                                </FormSection>
                            </div>
                        )}
                    </>
                )}
            </TabPanel>

            <SaveBar
                onSave={saveSettingsHandler}
                isSaving={isSaving}
                hasChanges={hasChanges && !hasErrors}
                notice={notice}
                setNotice={setNotice}
            />
        </div>
    );
}

export default Settings;
