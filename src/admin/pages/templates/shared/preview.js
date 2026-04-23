import { __, sprintf } from "@wordpress/i18n";
import { dateI18n } from "@wordpress/date";
import { separate } from "@admin/utils";
import { TEXT_DOMAIN } from "@admin/constants";

function Preview({ template, settings, preview }) {
    const shop = preview?.shop ?? {};
    const order = preview?.order ?? {};
    const document = preview?.document ?? {};

    const items = "creditnote" === template ? order?.refund?.items ?? [] : order?.items ?? [];
    const totals = "creditnote" === template ? { total: order?.refund?.total } : order?.totals;
    const formatDate = (date) => {
        if (!date) return "";
        const fmt = settings.dateFormat || window._wpDateSettings?.formats?.date || "Y-m-d";
        return dateI18n(fmt, new Date(date).getTime());
    };

    const buildOrderMetaStyle = (settings, prefix) => {
        return {
            textAlign: settings[`${prefix}Align`],
            fontSize: settings[`${prefix}FontSize`],
            color: settings[`${prefix}TextColor`],
            fontWeight: settings[`${prefix}FontStyle`] === "bold" ? 600 : 400,
        };
    };
    const metaPosition = settings.orderMetaPosition ?? "columns";

    const orderMetaFields = [
        {
            key: "invoiceNumber",
            show: settings.showInvoiceNumber,
            label: settings.invoiceNumberText,
            value: order.invoiceNumber,
            style: buildOrderMetaStyle(settings, "invoiceNumber"),
        },
        {
            key: "documentDate",
            show: settings.showDocumentDate,
            label: settings.documentDateText,
            value: order.documentDate,
            style: buildOrderMetaStyle(settings, "documentDate"),
        },
        {
            key: "orderNumber",
            show: settings.showOrderNumber,
            label: settings.orderNumberText,
            value: order.orderNumber,
            style: buildOrderMetaStyle(settings, "orderNumber"),
        },
        {
            key: "orderDate",
            show: settings.showOrderDate,
            label: settings.orderDateText,
            value: formatDate(order?.date),
            style: buildOrderMetaStyle(settings, "orderDate"),
        },
        {
            key: "paymentMethod",
            show: settings.showPaymentMethod,
            label: settings.paymentMethodText,
            value: order.paymentMethod,
            style: buildOrderMetaStyle(settings, "paymentMethod"),
        },
        {
            key: "paymentDate",
            show: settings.showPaymentDate,
            label: settings.paymentDateText,
            value: formatDate(order?.paymentDate),
            style: buildOrderMetaStyle(settings, "paymentDate"),
        },
        {
            key: "shippingMethod",
            show: settings.showShippingMethod,
            label: settings.shippingMethodText,
            value: order.shippingMethod,
            style: buildOrderMetaStyle(settings, "shippingMethod"),
        },
        {
            key: "refundDate",
            show: settings.showRefundDate,
            label: settings.refundDateText,
            value: formatDate(order?.refund?.date),
            style: buildOrderMetaStyle(settings, "refundDate"),
        },
        {
            key: "refundReason",
            show: settings.showRefundReason,
            label: settings.refundReasonText,
            value: order?.refund?.reason,
            style: buildOrderMetaStyle(settings, "refundReason"),
        },
        ...(metaPosition === "below" && settings.showBillingPhone && order?.billing?.phone
            ? [
                  {
                      key: "billingPhone",
                      show: true,
                      label: settings.billingPhoneText ?? "Phone",
                      value: order.billing.phone,
                      style: {
                          fontSize: `${settings.billingPhoneFontSize ?? 14}px`,
                          fontWeight:
                              settings.billingPhoneFontStyle === "bold" ? 600 : 400,
                          textAlign: settings.billingPhoneAlign ?? "left",
                          color: settings.billingPhoneTextColor ?? "#000000",
                      },
                  },
              ]
            : []),
        ...(metaPosition === "below" && settings.showBillingEmail && order?.billing?.email
            ? [
                  {
                      key: "billingEmail",
                      show: true,
                      label: settings.billingEmailText ?? "Email",
                      value: order.billing.email,
                      style: {
                          fontSize: `${settings.billingEmailFontSize ?? 14}px`,
                          fontWeight:
                              settings.billingEmailFontStyle === "bold" ? 600 : 400,
                          textAlign: settings.billingEmailAlign ?? "left",
                          color: settings.billingEmailTextColor ?? "#000000",
                      },
                  },
              ]
            : []),
    ];
    const hasOrderMeta = orderMetaFields.some((f) => f.show && f.value);

    return (
        <div className={`wcdn-preview-document mt-30 ${document.isRTL ? "is-rtl" : ""}`}>
            {/* Watermark */}
            {settings.showWatermark &&
                settings.watermarkText &&
                (settings.watermarkLayout === "repeat" ? (
                    <div className="wcdn-preview-watermark-repeat">
                        {Array.from({ length: 12 }).map((_, i) => (
                            <span
                                key={i}
                                style={{
                                    color: settings.watermarkColor || "#000",
                                    opacity: settings.watermarkOpacity ?? 0.08,
                                    fontSize: settings.watermarkFontSize || "120px",
                                    transform: `rotate(${settings.watermarkAngle || -25}deg)`,
                                }}
                            >
                                {settings.watermarkText}
                            </span>
                        ))}
                    </div>
                ) : (
                    <div
                        className="wcdn-preview-watermark"
                        style={{
                            color: settings.watermarkColor || "#000",
                            opacity: settings.watermarkOpacity ?? 0.08,
                            fontSize: settings.watermarkFontSize || "120px",
                            transform: `translate(-50%, -50%) rotate(${
                                settings.watermarkAngle || -25
                            }deg)`,
                        }}
                    >
                        {settings.watermarkText}
                    </div>
                ))}

            {/* Logo */}
            {settings.showLogo && (
                <div className={`wcdn-preview-logo align-${settings.logoAlignment}`}>
                    <div
                        className="wcdn-preview-logo-box"
                        style={{
                            transform: `scale(${settings.logoScale / 100})`,
                        }}
                    >
                        {shop.logo ? (
                            <img
                                src={shop.logo}
                                alt={shop.name}
                                style={{
                                    transform: `scale(${settings.logoScale / 100})`,
                                }}
                            />
                        ) : (
                            <div
                                className="wcdn-preview-logo-box"
                                style={{
                                    transform: `scale(${settings.logoScale / 100})`,
                                }}
                            >
                                [SHOP LOGO]
                            </div>
                        )}
                    </div>
                </div>
            )}

            {/* Document Title */}
            <h1
                className="wcdn-preview-title"
                style={{
                    fontSize: settings.documentTitleFontSize,
                    color: settings.documentTitleTextColor,
                    textAlign: settings.documentTitleAlign,
                    fontWeight: settings.documentTitleFontStyle === "bold" ? 600 : 400,
                }}
            >
                {settings.documentTitle}
            </h1>

            {(settings.showShopName ||
                settings.showShopAddress ||
                settings.showShopPhone ||
                settings.showShopEmail) && (
                <>
                    <div className="wcdn-preview-divider" />

                    {/* Shop Section */}
                    <div className="wcdn-preview-shop">
                        {settings.showShopName && shop.name && (
                            <div
                                className="shop-name"
                                style={{
                                    fontSize: settings.shopNameFontSize,
                                    color: settings.shopNameTextColor,
                                    textAlign: settings.shopNameAlign,
                                    fontWeight: settings.shopNameFontStyle === "bold" ? 600 : 400,
                                }}
                            >
                                {shop.name}
                            </div>
                        )}

                        {settings.showShopAddress && shop.address && (
                            <div
                                className="shop-address"
                                style={{
                                    fontSize: settings.addressFontSize,
                                    color: settings.addressTextColor,
                                    textAlign: settings.addressAlign,
                                    fontWeight: settings.addressFontStyle === "bold" ? 600 : 400,
                                }}
                            >
                                {shop.address.split("\n").map((line, i) => (
                                    <span key={i}>
                                        {i > 0 && <br />}
                                        {line}
                                    </span>
                                ))}
                            </div>
                        )}

                        {settings.showShopPhone && shop.phone && (
                            <div
                                className="shop-phone"
                                style={{
                                    fontSize: settings.shopPhoneFontSize,
                                    color: settings.shopPhoneTextColor,
                                    fontWeight: settings.shopPhoneFontStyle === "bold" ? 600 : 400,
                                    textAlign: settings.shopPhoneAlign,
                                    marginTop: settings.shopPhoneMarginTop,
                                    marginBottom: settings.shopPhoneMarginBottom,
                                }}
                            >
                                {settings.shopPhoneText ? `${settings.shopPhoneText}: ` : ""}{shop.phone}
                            </div>
                        )}

                        {settings.showShopEmail && shop.email && (
                            <div
                                className="shop-email"
                                style={{
                                    fontSize: settings.shopEmailFontSize,
                                    color: settings.shopEmailTextColor,
                                    fontWeight: settings.shopEmailFontStyle === "bold" ? 600 : 400,
                                    textAlign: settings.shopEmailAlign,
                                    marginTop: settings.shopEmailMarginTop,
                                    marginBottom: settings.shopEmailMarginBottom,
                                }}
                            >
                                {settings.shopEmailText ? `${settings.shopEmailText}: ` : ""}{shop.email}
                            </div>
                        )}
                    </div>
                </>
            )}

            {/* Addresses & Order Meta */}
            {(() => {
                const metaBelow = metaPosition === "below";
                const showGrid =
                    (settings.showBillingAddress && order?.billing) ||
                    (settings.showShippingAddress && order?.shipping) ||
                    (!metaBelow && hasOrderMeta);
                const showMetaBelow = metaBelow && hasOrderMeta;

                if (!showGrid && !showMetaBelow) return null;

                return (
                    <>
                        <div className="wcdn-preview-divider" />

                        {showGrid && (
                            <div className="wcdn-preview-address-grid">
                                {settings.showBillingAddress && order?.billing && (
                                    <div
                                        style={{
                                            textAlign: settings.billingAddressAlign,
                                            fontSize: settings.billingAddressFontSize,
                                            fontWeight:
                                                settings.billingAddressFontStyle === "bold"
                                                    ? 600
                                                    : 400,
                                            color: settings.billingAddressTextColor,
                                        }}
                                    >
                                        <h4>{settings.billingAddressText}</h4>

                                        <p style={{ fontSize: settings.billingAddressFontSize }}>
                                            {order.billing.name}

                                            {order.billing.address?.map((line, i) => (
                                                <span key={i}>
                                                    <br />
                                                    {line}
                                                </span>
                                            ))}

                                            {metaPosition === "columns" &&
                                                settings.showBillingPhone &&
                                                order.billing.phone && (
                                                    <>
                                                        <br />
                                                        {settings.billingPhoneText ?? "Phone"}:{" "}
                                                        {order.billing.phone}
                                                    </>
                                                )}

                                            {metaPosition === "columns" &&
                                                settings.showBillingEmail &&
                                                order.billing.email && (
                                                    <>
                                                        <br />
                                                        {settings.billingEmailText ?? "Email"}:{" "}
                                                        {order.billing.email}
                                                    </>
                                                )}
                                        </p>
                                    </div>
                                )}

                                {settings.showShippingAddress && order?.shipping && (
                                    <div
                                        style={{
                                            textAlign: settings.shippingAddressAlign,
                                            fontSize: settings.shippingAddressFontSize,
                                            fontWeight:
                                                settings.shippingAddressFontStyle === "bold"
                                                    ? 600
                                                    : 400,
                                            color: settings.shippingAddressTextColor,
                                        }}
                                    >
                                        <h4>{settings.shippingAddressText}</h4>

                                        <p style={{ fontSize: settings.shippingAddressFontSize }}>
                                            {order.shipping.name}

                                            {order.shipping.address?.map((line, i) => (
                                                <span key={i}>
                                                    <br />
                                                    {line}
                                                </span>
                                            ))}

                                            {order.shipping.email && (
                                                <>
                                                    <br />
                                                    Email: {order.shipping.email}
                                                </>
                                            )}
                                        </p>
                                    </div>
                                )}

                                {/* Order Meta as column */}
                                {!metaBelow && hasOrderMeta && (
                                    <div className="wcdn-preview-order-meta">
                                        {separate(
                                            orderMetaFields
                                                .filter((field) => field.show && field.value)
                                                .map((field) => (
                                                    <p key={field.key} style={field.style}>
                                                        <span>{field.label}:</span> {field.value}
                                                    </p>
                                                )),
                                            ""
                                        )}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Order Data Header */}
                        {showMetaBelow &&
                            settings.showOrderDataHeader &&
                            settings.orderDataHeaderText && (
                                <p
                                    style={{
                                        marginTop: `${settings.orderDataHeaderSpacingTop ?? 20}px`,
                                        marginBottom: `${settings.orderDataHeaderSpacingBottom ?? 5}px`,
                                        fontSize: `${settings.orderDataHeaderFontSize ?? 17}px`,
                                        fontWeight:
                                            settings.orderDataHeaderFontStyle === "bold"
                                                ? 600
                                                : 400,
                                        textAlign: settings.orderDataHeaderAlign ?? "left",
                                        color: settings.orderDataHeaderTextColor ?? "#000000",
                                    }}
                                >
                                    {settings.orderDataHeaderText}
                                </p>
                            )}
                        {showMetaBelow &&
                            settings.showOrderDataHeader &&
                            settings.showOrderDataHeaderBorder !== false && (
                                <div className="wcdn-preview-divider" style={{ margin: "0 0 8px" }} />
                            )}

                        {/* Order Meta below addresses */}
                        {showMetaBelow && (
                            <table className="wcdn-preview-order-meta-below">
                                <tbody>
                                    {orderMetaFields
                                        .filter((field) => field.show && field.value)
                                        .map((field) => (
                                            <tr key={field.key}>
                                                <td className="label" style={field.style}>
                                                    {field.label}:
                                                </td>
                                                <td className="value" style={field.style}>
                                                    {field.value}
                                                </td>
                                            </tr>
                                        ))}
                                </tbody>
                            </table>
                        )}
                    </>
                );
            })()}

            {/* Items Table */}
            {items.length > 0 && (
                <>
                    <div className="wcdn-preview-divider" />

                    <table className="wcdn-preview-table">
                        {(settings?.displayPriceInProductDetailsTable ||
                            ("creditnote" === template && settings?.displayRefundItemsInTable)) && (
                            <colgroup>
                                <col className="wcdn-col-product" />
                                <col className="wcdn-col-price" />
                                <col className="wcdn-col-qty" />
                                <col className="wcdn-col-total" />
                            </colgroup>
                        )}
                        <thead>
                            <tr>
                                <th>
                                    {"creditnote" === template
                                        ? __("Refunded Item", TEXT_DOMAIN)
                                        : __("Product", TEXT_DOMAIN)}
                                </th>

                                {(settings?.displayPriceInProductDetailsTable ||
                                    ("creditnote" === template &&
                                        settings?.displayRefundItemsInTable)) && (
                                    <th>{__("Price", TEXT_DOMAIN)}</th>
                                )}

                                <th>{__("Quantity", TEXT_DOMAIN)}</th>

                                {(settings?.displayPriceInProductDetailsTable ||
                                    ("creditnote" === template &&
                                        settings?.displayRefundItemsInTable)) && (
                                    <th>
                                        {"creditnote" === template
                                            ? __("Total Refunded", TEXT_DOMAIN)
                                            : __("Total", TEXT_DOMAIN)}
                                    </th>
                                )}
                            </tr>
                        </thead>

                        <tbody>
                            {items.map((item, index) => (
                                <tr key={index}>
                                    <td>
                                        {item.addon ? (
                                            <>
                                                <div className="wcdn-item-addon-name">
                                                    {item.addon.name}
                                                </div>
                                                <div className="wcdn-item-addon-value">
                                                    {item.addon.value}
                                                </div>
                                            </>
                                        ) : (
                                            <div style={settings.showProductImages ? { display: "flex", alignItems: "flex-start", gap: 8 } : {}}>
                                                {settings.showProductImages && (
                                                    item.image_url
                                                        ? <img className="wcdn-item-image" src={item.image_url} width={settings.productImageSize ?? 40} height={settings.productImageSize ?? 40} alt="" style={{ flexShrink: 0 }} />
                                                        : <div className="wcdn-item-image-placeholder" style={{ width: settings.productImageSize ?? 40, height: settings.productImageSize ?? 40, flexShrink: 0 }} />
                                                )}
                                                <div style={{ flex: 1, minWidth: 0 }}>
                                                {item.name}
                                                {item.sku && (
                                                    <span className="wcdn-item-sku">
                                                        {sprintf(
                                                            __("(SKU: %s)", TEXT_DOMAIN),
                                                            item.sku
                                                        )}
                                                    </span>
                                                )}
                                                {item.meta?.length > 0 && (
                                                    <dl className="wcdn-item-meta">
                                                        {item.meta.map((row, i) => (
                                                            <span key={i}>
                                                                <dt>{row.label}</dt>
                                                                <dd>{row.value}</dd>
                                                            </span>
                                                        ))}
                                                    </dl>
                                                )}
                                                </div>
                                            </div>
                                        )}
                                    </td>

                                    {(settings?.displayPriceInProductDetailsTable ||
                                        ("creditnote" === template &&
                                            settings?.displayRefundItemsInTable)) && (
                                        <td dangerouslySetInnerHTML={{ __html: item.price }} />
                                    )}

                                    <td>{item.quantity}</td>

                                    {(settings.displayPriceInProductDetailsTable ||
                                        ("creditnote" === template &&
                                            settings?.displayRefundItemsInTable)) && (
                                        <td dangerouslySetInnerHTML={{ __html: item.total }} />
                                    )}
                                </tr>
                            ))}
                        </tbody>

                        {/* {order.adjusted_quantity != null &&
                            !(
                                settings.displayPriceInProductDetailsTable ||
                                ("creditnote" === template && settings?.displayRefundItemsInTable)
                            ) && (
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <strong>{order.adjusted_quantity}</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            )} */}
                    </table>
                </>
            )}

            {/* Totals */}
            {totals?.total !== undefined &&
                settings.displayPriceInProductDetailsTable &&
                "creditnote" !== template && (
                    <table
                        className="wcdn-preview-totals"
                        style={{ fontSize: settings.totalsFontSize }}
                    >
                        <colgroup>
                            <col className="wcdn-col-product" />
                            <col className="wcdn-col-price" />
                            <col className="wcdn-col-qty" />
                            <col className="wcdn-col-total" />
                        </colgroup>
                        {settings.showProductCharges !== false && settings.showSubtotal !== false && (
                            <tr>
                                <td colSpan={3} className="wcdn-preview-totals-label">{__("Subtotal:", TEXT_DOMAIN)}</td>
                                <td className="wcdn-preview-totals-value" dangerouslySetInnerHTML={{ __html: totals.subtotal }} />
                            </tr>
                        )}
                        {settings.showProductCharges !== false && settings.showTax !== false && (
                            totals.tax_lines?.length > 0
                                ? totals.tax_lines.map((line, i) => (
                                    <tr key={i}>
                                        <td colSpan={3} className="wcdn-preview-totals-label">{line.label}:</td>
                                        <td className="wcdn-preview-totals-value" dangerouslySetInnerHTML={{ __html: line.value }} />
                                    </tr>
                                ))
                                : totals.tax && (
                                    <tr>
                                        <td colSpan={3} className="wcdn-preview-totals-label">{__("Tax:", TEXT_DOMAIN)}</td>
                                        <td className="wcdn-preview-totals-value" dangerouslySetInnerHTML={{ __html: totals.tax }} />
                                    </tr>
                                )
                        )}
                        {settings.showProductCharges !== false && settings.showShipping !== false && (
                            <tr>
                                <td colSpan={3} className="wcdn-preview-totals-label">{__("Shipping:", TEXT_DOMAIN)}</td>
                                <td className="wcdn-preview-totals-value" dangerouslySetInnerHTML={{ __html: totals.shipping }} />
                            </tr>
                        )}

                        {totals.has_refund ? (
                            <>
                                <tr>
                                    <td colSpan={3} className="wcdn-preview-totals-label">{__("Order Total:", TEXT_DOMAIN)}</td>
                                    <td className="wcdn-preview-totals-value" dangerouslySetInnerHTML={{ __html: totals.total }} />
                                </tr>
                                <tr>
                                    <td colSpan={3} className="wcdn-preview-totals-label">{__("Refund:", TEXT_DOMAIN)}</td>
                                    <td className="wcdn-preview-totals-value" dangerouslySetInnerHTML={{ __html: totals.refunded }} />
                                </tr>
                                <tr className="wcdn-preview-total">
                                    <td colSpan={3} className="wcdn-preview-totals-label"><strong>{__("Total:", TEXT_DOMAIN)}</strong></td>
                                    <td className="wcdn-preview-totals-value">
                                        <strong>
                                            <span dangerouslySetInnerHTML={{ __html: totals.net_total }} />
                                            {totals.tax_label && (
                                                <span dangerouslySetInnerHTML={{ __html: totals.tax_label }} />
                                            )}
                                        </strong>
                                    </td>
                                </tr>
                            </>
                        ) : (
                            <tr className="wcdn-preview-total">
                                <td colSpan={3} className="wcdn-preview-totals-label"><strong>{__("Total:", TEXT_DOMAIN)}</strong></td>
                                <td className="wcdn-preview-totals-value" dangerouslySetInnerHTML={{ __html: totals.total }} />
                            </tr>
                        )}
                    </table>
                )}

            {/* Pay Now */}
            {settings.showPayNowButton && totals?.total && (
                <div className="wcdn-preview-pay">
                    <button
                        style={{
                            background: settings.payNowColor,
                        }}
                    >
                        {settings.payNowLabel} &mdash;{" "}
                        <span dangerouslySetInnerHTML={{ __html: totals.total }} />
                    </button>
                </div>
            )}

            {/* Customer Note */}
            {settings.showCustomerNote && (
                <>
                    <div className="wcdn-preview-divider" />

                    <div className="wcdn-preview-policies">
                        <span
                            style={{
                                fontSize: settings.customerNoteFontSize,
                                color: settings.customerNoteTextColor,
                                fontWeight: 600,
                            }}
                        >
                            {settings.customerNoteTitle}:
                        </span>{" "}
                        <span
                            style={{
                                fontSize: settings.customerNoteFontSize,
                                color: settings.customerNoteTextColor,
                                fontWeight: settings.customerNoteFontStyle === "bold" ? 600 : 400,
                            }}
                        >
                            {__("Sample Customer Note", TEXT_DOMAIN)}
                        </span>
                    </div>
                </>
            )}

            {/* Policies */}
            {settings.showPolicies && document.policies && (
                <>
                    <div className="wcdn-preview-divider" />

                    <div
                        className="wcdn-preview-policies"
                        style={{
                            fontSize: settings.policiesFontSize,
                            color: settings.policiesTextColor,
                        }}
                    >
                        {document.policies}
                    </div>
                </>
            )}

            {/* Complimentary Close */}
            {settings.showComplimentaryClose && document.complimentaryClose && (
                <>
                    <div className="wcdn-preview-divider" />

                    <div
                        className="wcdn-preview-business"
                        style={{
                            fontSize: settings.complimentaryCloseFontSize,
                            color: settings.complimentaryCloseTextColor,
                        }}
                    >
                        {document.complimentaryClose}
                    </div>
                </>
            )}

            {/* Footer */}
            {settings.showFooter && document.footer && (
                <>
                    <div className="wcdn-preview-divider" />

                    <div
                        className="wcdn-preview-footer"
                        style={{
                            fontSize: settings.footerFontSize,
                            color: settings.footerTextColor,
                        }}
                    >
                        {document.footer}
                    </div>
                </>
            )}
        </div>
    );
}

export default Preview;
