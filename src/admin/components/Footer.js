import { __ } from "@wordpress/i18n";
import Alert from "./Alert";

const TYCHE_URL = "https://www.tychesoftwares.com";
const ORDDD_URL =
    "https://www.tychesoftwares.com/products/woocommerce-order-delivery-date-pro-plugin/";
const SUPPORT_URL =
    "https://wordpress.org/support/plugin/woocommerce-delivery-notes/";
const REVIEW_URL =
    "https://wordpress.org/support/plugin/woocommerce-delivery-notes/reviews/#new-post";

function Footer() {
    if ( process.env.WCDN_WC_BUILD === 'true' ) {
        return null;
    }

    return (
        <div id="wcdn-footer">
            <Alert storageKey="wcdn_orddd_promo_dismissed">
                {__("Get our", "woocommerce-delivery-notes")}{" "}
                <a href={ORDDD_URL} target="_blank" rel="noreferrer">
                    {__("Order Delivery Date Pro", "woocommerce-delivery-notes")}
                </a>{" "}
                {__("plugin to schedule and manage your local deliveries and pickups in WooCommerce.", "woocommerce-delivery-notes")}
            </Alert>
            <p>
                <a
                    href={SUPPORT_URL}
                    target="_blank"
                    rel="noreferrer"
                    title={__("Open the support forum for this plugin on WordPress.org", "woocommerce-delivery-notes")}
                >
                    {__("Need Support?", "woocommerce-delivery-notes")}
                </a>{" "}
                {__("We're always happy to help you.", "woocommerce-delivery-notes")}
            </p>
            <p>
                {__("If this plugin helped you,", "woocommerce-delivery-notes")}{" "}
                <a
                    href={REVIEW_URL}
                    target="_blank"
                    rel="noreferrer"
                    title={__("Leave a review for this plugin on WordPress.org", "woocommerce-delivery-notes")}
                >
                    {__("please rate it", "woocommerce-delivery-notes")}
                </a>{" "}
                <span aria-hidden="true" style={{ color: "#f0ad00" }}>{"★".repeat(5)}</span>
            </p>
            <p>
                {__("Check out more plugins by", "woocommerce-delivery-notes")}{" "}
                <a
                    href={TYCHE_URL}
                    target="_blank"
                    rel="noreferrer"
                    title={__("Visit Tyche Softwares to explore more WooCommerce plugins", "woocommerce-delivery-notes")}
                >
                    {__("Tyche Softwares", "woocommerce-delivery-notes")}
                </a>
                {"."}
            </p>
        </div>
    );
}

export default Footer;
