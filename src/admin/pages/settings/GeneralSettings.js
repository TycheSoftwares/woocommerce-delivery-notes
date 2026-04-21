import { __, sprintf } from "@wordpress/i18n";
import { Button } from "@wordpress/components";
import {
    FormSection,
    Text,
    Textarea,
    Checkbox,
    RadioGroup,
    Number,
} from "@admin/components/form";
import { TEXT_DOMAIN } from "../../constants";

function GeneralSettings({ settings, updateSetting, validation, saveSettingsHandler }) {
    return (
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
                    onChange={(v) => updateSetting("resetInvoiceNumberYearly", v)}
                />

                {settings.resetInvoiceNumberYearly && (
                    <Number
                        className="mt-20"
                        min={1}
                        label={__("Starting number for each new year", TEXT_DOMAIN)}
                        tooltip={__(
                            "The number assigned to the first invoice of every new year.",
                            TEXT_DOMAIN
                        )}
                        value={settings.startingNumberForEachYear}
                        onChange={(v) => updateSetting("startingNumberForEachYear", v)}
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
                        onChange={(v) => updateSetting("numberDaysPdfExpiration", v)}
                        bottomLabel={__(
                            "Number of days for expiration after which generated PDFs will be deleted.",
                            TEXT_DOMAIN
                        )}
                    />
                )}
            </FormSection>

            <FormSection title={__("Print Links", TEXT_DOMAIN)}>
                <Checkbox
                    label={__("Show print link in customer emails", TEXT_DOMAIN)}
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
                    label={__("Show print button on My Account Page", TEXT_DOMAIN)}
                    checked={settings.showPrintButtonMyAccountPage}
                    onChange={(v) => updateSetting("showPrintButtonMyAccountPage", v)}
                    tooltip={__(
                        "Display a print button on the My Account page",
                        TEXT_DOMAIN
                    )}
                />

                <Checkbox
                    className="mt-20"
                    label={__("Show print button on View Order page", TEXT_DOMAIN)}
                    checked={settings.showViewOrderButton}
                    onChange={(v) => updateSetting("showViewOrderButton", v)}
                    tooltip={__(
                        "Display a print button on the order details page when viewing a specific order",
                        TEXT_DOMAIN
                    )}
                />

            </FormSection>

            <FormSection title={__("Button Label for Templates", TEXT_DOMAIN)}>
                <Text
                    label={__("Invoice Print Button Label", TEXT_DOMAIN)}
                    value={settings.invoiceButtonLabel}
                    onChange={(v) => updateSetting("invoiceButtonLabel", v)}
                    tooltip={__("Label for the invoice print button.", TEXT_DOMAIN)}
                />

                <Text
                    className="mt-20"
                    label={__("Delivery Note Print Button Label", TEXT_DOMAIN)}
                    value={settings.deliveryNoteButtonLabel}
                    onChange={(v) => updateSetting("deliveryNoteButtonLabel", v)}
                    tooltip={__("Label for the delivery note print button.", TEXT_DOMAIN)}
                />

                <Text
                    className="mt-20"
                    label={__("Receipt Print Button Label", TEXT_DOMAIN)}
                    value={settings.receiptButtonLabel}
                    onChange={(v) => updateSetting("receiptButtonLabel", v)}
                    tooltip={__("Label for the receipt print button.", TEXT_DOMAIN)}
                />

                <Text
                    className="mt-20"
                    label={__("Credit Note Print Button Label", TEXT_DOMAIN)}
                    value={settings.creditNoteButtonLabel}
                    onChange={(v) => updateSetting("creditNoteButtonLabel", v)}
                    tooltip={__("Label for the credit note print button.", TEXT_DOMAIN)}
                />

                <Text
                    className="mt-20"
                    label={__("Packing Slip Print Button Label", TEXT_DOMAIN)}
                    value={settings.packingSlipButtonLabel}
                    onChange={(v) => updateSetting("packingSlipButtonLabel", v)}
                    tooltip={__("Label for the packing slip print button.", TEXT_DOMAIN)}
                />
            </FormSection>

            {process.env.WCDN_WC_BUILD !== 'true' && (
                <FormSection title={__("Plugin Usage Tracking", TEXT_DOMAIN)}>
                    <Button
                        variant="secondary"
                        className="wcdn-reset-plugin-usage-button"
                        onClick={() => saveSettingsHandler("reset_plugin_usage_tracking")}
                    >
                        {__("Reset Tracking Settings", TEXT_DOMAIN)}
                    </Button>
                </FormSection>
            )}
        </div>
    );
}

export default GeneralSettings;
