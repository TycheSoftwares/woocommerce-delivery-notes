import { __ } from "@wordpress/i18n";
import { FormSection, Text, Textarea, MediaUpload } from "@admin/components/form";
import { TEXT_DOMAIN } from "../../constants";

function StoreSettings({ settings, updateSetting }) {
    return (
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
    );
}

export default StoreSettings;
