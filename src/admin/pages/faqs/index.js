import { __ } from "@wordpress/i18n";
import {
    Card,
    CardHeader,
    CardBody,
    Panel,
    PanelBody,
    __experimentalHeading as Heading,
} from "@wordpress/components";
import { TEXT_DOMAIN } from "../../constants";

function FAQs() {
    return (
        <Card isRounded={false} className="wcdn-card">
            <CardHeader className="wcdn-card-header wcdn-faq" isBorderless={true}>
                <Heading level={3}>{__("Frequently Asked Questions", TEXT_DOMAIN)}</Heading>
                <p className="wcdn-faq-subtitle">
                    {__(
                        "Find answers to common questions about the Print Invoices & Delivery Notes plugin.",
                        TEXT_DOMAIN
                    )}
                </p>
            </CardHeader>

            <CardBody style={{ paddingTop: 0 }}>
                <Panel className="wcdn-faq-panel">
                    {[
                        {
                            title: __("How do templates work?", TEXT_DOMAIN),
                            content: __(
                                "Templates are pre-designed layouts for your documents. You can enable or disable each template (Invoice, Receipt, Delivery Note, etc.) and customize their sections, fonts, colors, and content. Changes are reflected in real-time in the preview pane.",
                                TEXT_DOMAIN
                            ),
                        },
                        {
                            title: __("Why does the order print show a 404 page?", TEXT_DOMAIN),
                            content: __(
                                "This usually happens due to permalink settings — resave your WordPress Permalinks or WooCommerce Print Settings and ensure a My Account Page is selected in WooCommerce settings.",
                                TEXT_DOMAIN
                            ),
                        },
                        {
                            title: __("How can I translate the plugin?", TEXT_DOMAIN),
                            content: __(
                                "Upload your .mo and .po files to /wp-content/languages/plugins/ using the correct locale (e.g. woocommerce-delivery-notes-it_IT.mo).",
                                TEXT_DOMAIN
                            ),
                        },
                        {
                            title: __("Does this plugin modify order data?", TEXT_DOMAIN),
                            content: __(
                                "No, the plugin only reads WooCommerce order data to generate documents and does not modify any order information.",
                                TEXT_DOMAIN
                            ),
                        },
                        {
                            title: __(
                                "What is the difference between an invoice and a receipt?",
                                TEXT_DOMAIN
                            ),
                            content: __(
                                "An invoice requests payment before it is made, while a receipt confirms that payment has already been received.",
                                TEXT_DOMAIN
                            ),
                        },
                        {
                            title: __("Why can’t customers see the Print button?", TEXT_DOMAIN),
                            content: __(
                                "Ensure the print button option is enabled, the document template is active, and order status rules are properly configured.",
                                TEXT_DOMAIN
                            ),
                        },
                        {
                            title: __(
                                "How do I enable automatic PDF attachments to emails?",
                                TEXT_DOMAIN
                            ),
                            content: __(
                                "Enable PDF attachments for customer or admin emails in the Documents tab, and configure order status rules if needed.",
                                TEXT_DOMAIN
                            ),
                        },
                        {
                            title: __("Is the plugin GDPR compliant?", TEXT_DOMAIN),
                            content: __(
                                "Yes, the plugin stores minimal data beyond WooCommerce, but storing generated PDFs should be disclosed in your privacy policy.",
                                TEXT_DOMAIN
                            ),
                        },
                        {
                            title: __("Can I change item prices in documents?", TEXT_DOMAIN),
                            content: __(
                                "No, document prices come directly from WooCommerce orders, though you can choose the price source in the Templates settings.",
                                TEXT_DOMAIN
                            ),
                        },
                    ].map((faq, i) => (
                        <PanelBody title={faq.title} initialOpen={false}>
                            <p>{faq.content}</p>
                        </PanelBody>
                    ))}
                </Panel>

                <div className="wcdn-faq-footer">
                    <strong>{__("Still need help?", TEXT_DOMAIN)}</strong>{" "}
                    {__("Visit our", TEXT_DOMAIN)}{" "}
                    <a
                        href="https://www.tychesoftwares.com/docs/docs/print-invoice-delivery-notes-for-woocommerce/"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {__("documentation", TEXT_DOMAIN)}
                    </a>{" "}
                    {__("or", TEXT_DOMAIN)}{" "}
                    <a
                        href="https://support.tychesoftwares.com/help/2285384554"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {__("contact support.", TEXT_DOMAIN)}
                    </a>
                </div>
            </CardBody>
        </Card>
    );
}

export default FAQs;
