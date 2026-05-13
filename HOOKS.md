# WCDN Hooks & Filters Reference

**Plugin:** Print Invoice & Delivery Notes for WooCommerce

All hooks use the `wcdn_` prefix. Add your code to a custom plugin or your
theme's `functions.php`.

---

## Table of Contents

1. [Document Layout Actions](#1-document-layout-actions)
2. [Per-Template Actions](#2-per-template-actions)
3. [PDF Actions](#3-pdf-actions)
4. [Order Data Filters](#4-order-data-filters)
5. [Shop & Document Data Filters](#5-shop--document-data-filters)
6. [Template Rendering Filters](#6-template-rendering-filters)
7. [Print View Filters](#7-print-view-filters)
8. [Email Filters](#8-email-filters)
9. [PDF Generation Filters](#9-pdf-generation-filters)
10. [PDF Locale Font Filters](#10-pdf-locale-font-filters)

---

## 1. Document Layout Actions

These actions fire inside `base.php` as the document is rendered. They let
you inject custom HTML into any position in the document without copying the
template file.

**Callback signature (all layout actions):**
```php
function my_callback( array $order, string $template ) { }
```

`$order` is the formatted order data array (id, billing, shipping, items,
totals, etc.). `$template` is the document type key: `invoice`, `receipt`,
`deliverynote`, `packingslip`, or `creditnote`.

---

### `wcdn_before_document`
**Since:** 7.0.0

Fires at the very start of the document, inside `.wcdn-document` but before
any content.

```php
add_action( 'wcdn_before_document', function( $order, $template ) {
    if ( 'invoice' === $template ) {
        echo '<p style="color:red;font-weight:bold;">DRAFT</p>';
    }
}, 10, 2 );
```

---

### `wcdn_before_logo` *(Since 7.1.2)* / `wcdn_after_logo` *(Since 7.0.0)*

Fire before and after the shop logo block respectively. Both fire
unconditionally regardless of whether the logo is enabled.

```php
// Add a banner image above the logo.
add_action( 'wcdn_before_logo', function( $order, $template ) {
    echo '<img src="' . esc_url( get_site_url() . '/banner.png' ) . '" style="width:100%;" />';
}, 10, 2 );

// Add a tagline below the logo.
add_action( 'wcdn_after_logo', function( $order, $template ) {
    echo '<p style="text-align:center;font-style:italic;">Your Trusted Partner</p>';
}, 10, 2 );
```

---

### `wcdn_before_title` *(Since 7.1.2)* / `wcdn_after_title` *(Since 7.0.0)*

Fire before and after the document title heading (`<h1>`).

```php
// Show a "COPY" label on duplicate invoices.
add_action( 'wcdn_before_title', function( $order, $template ) {
    if ( 'invoice' === $template && get_post_meta( $order['id'], '_is_copy', true ) ) {
        echo '<p style="text-align:center;">[COPY]</p>';
    }
}, 10, 2 );

// Add a subtitle under the title.
add_action( 'wcdn_after_title', function( $order, $template ) {
    echo '<p style="text-align:center;font-size:0.85em;color:#888;">Tax Invoice</p>';
}, 10, 2 );
```

---

### `wcdn_before_branding` *(Since 7.1.2)* / `wcdn_after_branding` *(Since 7.0.0)*

Fire around the shop name/address/phone/email block.
`wcdn_after_branding` fires only when at least one branding field is visible.

```php
// Append a company registration number below the shop details.
add_action( 'wcdn_after_branding', function( $order, $template ) {
    echo '<p style="font-size:0.8em;">Reg No: 12345678 | VAT No: GB123456789</p>';
}, 10, 2 );
```

---

### `wcdn_before_addresses` *(Since 7.1.2)* / `wcdn_after_addresses` *(Since 7.0.0)*

Fire around the billing/shipping address grid and order meta block.
`wcdn_after_addresses` fires only when at least one address or meta column is visible.

```php
// Insert a delivery instructions field between branding and addresses.
add_action( 'wcdn_before_addresses', function( $order, $template ) {
    $instructions = get_post_meta( $order['id'], '_delivery_instructions', true );
    if ( $instructions ) {
        echo '<p><strong>Delivery Instructions:</strong> ' . esc_html( $instructions ) . '</p>';
    }
}, 10, 2 );
```

---

### `wcdn_before_items` / `wcdn_after_items`
**Since:** 7.0.0

Fire before and after the line-items table. Both fire only when the `$items`
array is non-empty.

```php
// Add a section heading above the items table.
add_action( 'wcdn_before_items', function( $order, $template ) {
    echo '<p style="font-weight:600;margin-top:10px;">Items Ordered</p>';
}, 10, 2 );

// Add a note below the items table.
add_action( 'wcdn_after_items', function( $order, $template ) {
    echo '<p style="font-size:0.8em;font-style:italic;">All prices include VAT.</p>';
}, 10, 2 );
```

---

### `wcdn_order_item_before` / `wcdn_order_item_after`
**Since:** 4.0.0 · Argument order updated in 7.0.0

Fire before and after the product cell content for each line item.

**Callback signature:**
```php
function my_callback( array $item, array $order, string $template ) { }
```

`$item` keys: `name`, `sku`, `price`, `quantity`, `total`, `product_id`,
`order_item_id`, `meta`, `addon`, `image_url`, `image_path`.

> **Note:** Prior to v7.0.0 the callback received `( $product, $order, $item )`
> as WooCommerce objects. Update any pre-v7 callbacks to the new signature.

```php
// Append a fulfilment status badge to each item.
add_action( 'wcdn_order_item_after', function( $item, $order, $template ) {
    $status = get_post_meta( $item['product_id'], '_fulfilment_status', true );
    if ( $status ) {
        echo '<span style="font-size:0.75em;color:#888;">' . esc_html( $status ) . '</span>';
    }
}, 10, 3 );
```

---

### `wcdn_before_totals` *(Since 7.1.2)* / `wcdn_after_totals` *(Since 7.0.0)*

Fire around the order totals table. Both fire unconditionally.

```php
// Add a note before the totals section.
add_action( 'wcdn_before_totals', function( $order, $template ) {
    echo '<p style="font-size:0.8em;">Payment due within 30 days.</p>';
}, 10, 2 );

// Append a custom totals row (e.g. loyalty points earned).
add_action( 'wcdn_after_totals', function( $order, $template ) {
    $points = get_post_meta( $order['id'], '_loyalty_points_earned', true );
    if ( $points ) {
        echo '<p style="text-align:right;font-size:0.85em;">Loyalty points earned: <strong>' . esc_html( $points ) . '</strong></p>';
    }
}, 10, 2 );
```

---

### `wcdn_before_pay_button` *(Since 7.1.2)* / `wcdn_after_pay_button` *(Since 7.0.0)*

Fire around the pay-now button. Both fire unconditionally.

```php
// Add a QR code below the pay-now button.
add_action( 'wcdn_after_pay_button', function( $order, $template ) {
    if ( ! empty( $order['payment_url'] ) ) {
        $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode( $order['payment_url'] );
        echo '<p style="text-align:center;"><img src="' . esc_url( $qr_url ) . '" width="80" height="80" /></p>';
    }
}, 10, 2 );
```

---

### `wcdn_before_notes` *(Since 7.1.2)* / `wcdn_after_notes` *(Since 7.0.0)*

Fire around the customer note block.
`wcdn_after_notes` fires only when the note is visible (setting on and note non-empty).

```php
// Add a label before the customer note section.
add_action( 'wcdn_before_notes', function( $order, $template ) {
    if ( ! empty( $order['customer_note'] ) ) {
        echo '<p style="font-weight:600;">Customer Note</p>';
    }
}, 10, 2 );
```

---

### `wcdn_before_policies` / `wcdn_after_policies`
**Since:** 7.1.2

Fire before and after the policies block. Both fire unconditionally.

```php
// Append a returns policy link below the policies section.
add_action( 'wcdn_after_policies', function( $order, $template ) {
    echo '<p style="font-size:0.8em;">Full returns policy: <a href="https://example.com/returns">example.com/returns</a></p>';
}, 10, 2 );
```

---

### `wcdn_before_complimentary_close` / `wcdn_after_complimentary_close`
**Since:** 7.1.2

Fire around the complimentary close block. Both fire unconditionally.

```php
// Inject a signature image above the complimentary close.
add_action( 'wcdn_before_complimentary_close', function( $order, $template ) {
    echo '<img src="' . esc_url( get_site_url() . '/signature.png' ) . '" style="height:40px;" />';
}, 10, 2 );
```

---

### `wcdn_before_footer` / `wcdn_after_footer`
**Since:** 7.1.2

Fire around the footer block. Both fire unconditionally.

```php
// Add a page number note at the very bottom.
add_action( 'wcdn_after_footer', function( $order, $template ) {
    echo '<p style="text-align:center;font-size:0.75em;color:#aaa;">Generated by Example Store</p>';
}, 10, 2 );
```

---

### `wcdn_after_document`
**Since:** 7.0.0

Fires at the very end of the document, before the closing `.wcdn-document`
div. Always fires.

```php
add_action( 'wcdn_after_document', function( $order, $template ) {
    echo '<p style="font-size:0.7em;color:#ccc;">Order ID: ' . esc_html( $order['id'] ) . '</p>';
}, 10, 2 );
```

---

## 2. Per-Template Actions

**Since:** 7.0.0

Each document type fires a pair of actions wrapping the `base.php` inclusion.
Use these when you only want to affect a single document type and need access
to the full raw `$data` array before or after rendering.

**Callback signature:**
```php
function my_callback( array $data ) { }
```

`$data` contains `order`, `shop`, `document`, `settings`, and `template` keys.

| Action | Fires |
|---|---|
| `wcdn_before_invoice_template` | Before the invoice base template |
| `wcdn_after_invoice_template` | After the invoice base template |
| `wcdn_before_receipt_template` | Before the receipt base template |
| `wcdn_after_receipt_template` | After the receipt base template |
| `wcdn_before_deliverynote_template` | Before the delivery note base template |
| `wcdn_after_deliverynote_template` | After the delivery note base template |
| `wcdn_before_packingslip_template` | Before the packing slip base template |
| `wcdn_after_packingslip_template` | After the packing slip base template |
| `wcdn_before_creditnote_template` | Before the credit note base template |
| `wcdn_after_creditnote_template` | After the credit note base template |

```php
// Add raw HTML only to packing slips, outside the normal document wrapper.
add_action( 'wcdn_after_packingslip_template', function( $data ) {
    echo '<div style="page-break-before:always;"></div>';
} );
```

---

## 3. PDF Actions

### `wcdn_after_pdf_generated`
**Since:** 7.0.0

Fires after a PDF file has been written to disk.

**Parameters:** `string $file` (absolute path), `int $order_id`, `string $template`

```php
// Copy each generated PDF to a remote storage bucket.
add_action( 'wcdn_after_pdf_generated', function( $file, $order_id, $template ) {
    my_upload_to_s3( $file, "invoices/{$order_id}/{$template}.pdf" );
}, 10, 3 );
```

---

## 4. Order Data Filters

These filters run in the PHP data layer, before the template receives any
values. Use them to change what data the template sees.

---

### `wcdn_order_items`
**Since:** 7.1.2

Filter the complete line items array before it is passed to the template.
Use this to sort, remove, or reorder items.

**Parameters:** `array $items`, `WC_Order $order`, `string $template`

Each item: `name`, `sku`, `price`, `quantity`, `total`, `product_id`,
`order_item_id`, `meta`, `addon`, `image_url`, `image_path`.

```php
// Sort items alphabetically by product name.
add_filter( 'wcdn_order_items', function( $items, $order, $template ) {
    usort( $items, fn( $a, $b ) => strcmp( $a['name'], $b['name'] ) );
    return $items;
}, 10, 3 );

// Sort items by SKU.
add_filter( 'wcdn_order_items', function( $items, $order, $template ) {
    usort( $items, fn( $a, $b ) => strcmp( $a['sku'], $b['sku'] ) );
    return $items;
}, 10, 3 );

// Remove items from a specific category on packing slips.
add_filter( 'wcdn_order_items', function( $items, $order, $template ) {
    if ( 'packingslip' !== $template ) {
        return $items;
    }
    return array_filter( $items, function( $item ) {
        return ! has_term( 'digital', 'product_cat', $item['product_id'] );
    } );
}, 10, 3 );
```

---

### `wcdn_order_item_product`
**Since:** 7.0.0

Replace or modify the `WC_Product` object resolved for a line item. Return
`null` to treat the item as having no product.

**Parameters:** `WC_Product|null $product`, `WC_Order_Item $item`

```php
// Use the parent product for variant-specific logic.
add_filter( 'wcdn_order_item_product', function( $product, $item ) {
    if ( $product && $product->is_type( 'variation' ) ) {
        return wc_get_product( $product->get_parent_id() );
    }
    return $product;
}, 10, 2 );
```

---

### `wcdn_order_item_name`
**Since:** 7.0.0

Modify the display name of a line item.

**Parameters:** `string $name`, `WC_Order_Item $item`

```php
// Append the variation attributes to the product name.
add_filter( 'wcdn_order_item_name', function( $name, $item ) {
    if ( $item instanceof WC_Order_Item_Product ) {
        $attrs = wc_get_formatted_variation( $item->get_product(), true );
        if ( $attrs ) {
            $name .= ' — ' . $attrs;
        }
    }
    return $name;
}, 10, 2 );
```

---

### `wcdn_order_item_quantity`
**Since:** 7.0.0

Modify the displayed quantity for a line item.

**Parameters:** `float $quantity`, `WC_Order_Item $item`

```php
// Always show whole numbers even for fractional quantities.
add_filter( 'wcdn_order_item_quantity', function( $quantity, $item ) {
    return ceil( $quantity );
}, 10, 2 );
```

---

### `wcdn_formatted_item_price`
**Since:** 7.0.0

Modify the formatted unit price string for a line item.

**Parameters:** `string $price`, `WC_Order_Item $item`, `WC_Order $order`

```php
// Show "FREE" instead of £0.00 for zero-price items.
add_filter( 'wcdn_formatted_item_price', function( $price, $item, $order ) {
    if ( (float) $item->get_subtotal() === 0.0 ) {
        return 'FREE';
    }
    return $price;
}, 10, 3 );
```

---

### `wcdn_product_meta_data`
**Since:** 5.8.0

Filter the raw WooCommerce meta data object for a line item before it is
formatted into `label/value` pairs.

**Parameters:** `array $meta_data` (array of `WC_Meta_Data` objects), `WC_Order_Item $item`

```php
// Remove a specific meta key from all documents.
add_filter( 'wcdn_product_meta_data', function( $meta_data, $item ) {
    return array_filter( $meta_data, fn( $m ) => $m->key !== '_gift_message' );
}, 10, 2 );
```

---

### `wcdn_order_item_fields`
**Since:** 7.0.0

Append extra `label/value` rows to an item's meta block.

**Parameters:** `array $fields`, `WC_Product $product`, `WC_Order $order`, `WC_Order_Item $item`

```php
// Add a barcode row to each item.
add_filter( 'wcdn_order_item_fields', function( $fields, $product, $order, $item ) {
    $barcode = get_post_meta( $product->get_id(), '_barcode', true );
    if ( $barcode ) {
        $fields[] = array(
            'label' => 'Barcode',
            'value' => $barcode,
        );
    }
    return $fields;
}, 10, 4 );
```

---

### `wcdn_order_totals`
**Since:** 7.0.0

Add, remove, or reorder rows in the totals table. Each row is a key in the
`$totals` array mapped to a formatted price string.

**Parameters:** `array $totals`, `WC_Order $order`, `string $template`

Standard keys: `subtotal`, `discount`, `tax`, `tax_lines`, `shipping`,
`fee_lines`, `total`, `has_refund`, `refunded`, `net_total`.

```php
// Add an environmental levy row after shipping.
add_filter( 'wcdn_order_totals', function( $totals, $order, $template ) {
    $levy = get_post_meta( $order->get_id(), '_eco_levy', true );
    if ( $levy ) {
        $totals['eco_levy'] = wc_price( $levy, array( 'currency' => $order->get_currency() ) );
    }
    return $totals;
}, 10, 3 );
```

---

### `wcdn_invoice_order_total_label`
**Since:** 5.6.0

Rename a specific totals row label (e.g. change "Total:" to "Amount Due:").

**Parameters:** `string $label`, `array $order`

```php
add_filter( 'wcdn_invoice_order_total_label', function( $label, $order ) {
    if ( 'Total:' === $label ) {
        return 'Amount Due:';
    }
    return $label;
}, 10, 2 );
```

---

### `wcdn_order_info_fields`
**Since:** 7.1.2

Append extra rows to the order meta block (the table showing invoice number,
date, payment method, etc.).

**Parameters:** `array $fields`, `WC_Order $order`

Each field: `[ 'label' => string, 'value' => string ]`.

```php
// Add a Purchase Order number row.
add_filter( 'wcdn_order_info_fields', function( $fields, $order ) {
    $po = get_post_meta( $order->get_id(), '_purchase_order_number', true );
    if ( $po ) {
        $fields['po_number'] = array(
            'label' => 'PO Number',
            'value' => $po,
        );
    }
    return $fields;
}, 10, 2 );
```

---

### `wcdn_order_meta_fields`
**Since:** 7.1.2

Filter the full list of order meta field rows shown in the document's order
data block. Use this to reorder, remove, or modify existing rows — something
`wcdn_order_info_fields` cannot do.

**Parameters:** `array $fields`, `array $order`, `array $settings`, `string $template`

Each field: `[ 'key', 'label', 'value', 'show' ]`.

```php
// Move the payment method row to the top.
add_filter( 'wcdn_order_meta_fields', function( $fields, $order, $settings, $template ) {
    usort( $fields, function( $a, $b ) {
        if ( 'paymentMethod' === $a['key'] ) return -1;
        if ( 'paymentMethod' === $b['key'] ) return 1;
        return 0;
    } );
    return $fields;
}, 10, 4 );

// Remove the invoice number row from delivery notes.
add_filter( 'wcdn_order_meta_fields', function( $fields, $order, $settings, $template ) {
    if ( 'deliverynote' === $template ) {
        return array_filter( $fields, fn( $f ) => 'invoiceNumber' !== $f['key'] );
    }
    return $fields;
}, 10, 4 );
```

---

### `wcdn_billing_address`
**Since:** 7.1.2

Filter the formatted billing address lines before they appear in the document.

**Parameters:** `array $lines`, `WC_Order $order`

Each element is one address line string.

```php
// Append a custom billing note as a final address line.
add_filter( 'wcdn_billing_address', function( $lines, $order ) {
    $note = get_post_meta( $order->get_id(), '_billing_note', true );
    if ( $note ) {
        $lines[] = $note;
    }
    return $lines;
}, 10, 2 );
```

---

### `wcdn_shipping_address`
**Since:** 7.1.2

Filter the formatted shipping address lines.

**Parameters:** `array $lines`, `WC_Order $order`

```php
// Append a delivery zone note to the shipping address.
add_filter( 'wcdn_shipping_address', function( $lines, $order ) {
    $zone = get_post_meta( $order->get_id(), '_shipping_zone', true );
    if ( $zone ) {
        $lines[] = 'Zone: ' . $zone;
    }
    return $lines;
}, 10, 2 );
```

---

### `wcdn_order_invoice_number`
**Since:** 7.0.0

Override the formatted invoice number string for a document.

**Parameters:** `string $invoice_number`

```php
// Prefix the invoice number with the current year.
add_filter( 'wcdn_order_invoice_number', function( $invoice_number ) {
    return date( 'Y' ) . '-' . $invoice_number;
} );
```

---

### `wcdn_order_invoice_date`
**Since:** 7.0.0

Override the formatted invoice date string.

**Parameters:** `string $formatted_date`, `int $timestamp`

```php
// Use a custom date format.
add_filter( 'wcdn_order_invoice_date', function( $formatted_date, $timestamp ) {
    return date_i18n( 'l, F j, Y', $timestamp );
}, 10, 2 );
```

---

## 5. Shop & Document Data Filters

---

### `wcdn_shop_data`
**Since:** 7.1.2

Filter the shop/store data array before it is passed to templates. Use this
to override any store field without editing the plugin settings.

**Parameters:** `array $shop`

Keys: `name`, `logo` (URL), `logo_path` (absolute path for PDF), `address`,
`phone`, `email`.

```php
// Override the store address on invoices for a specific sub-brand.
add_filter( 'wcdn_shop_data', function( $shop ) {
    $shop['name']    = 'Sub-Brand Ltd';
    $shop['address'] = '99 Example Street, London, EC1A 1BB';
    return $shop;
} );
```

---

### `wcdn_document_data`
**Since:** 7.1.2

Filter the document-level content array (footer, policies, complimentary
close, and RTL direction flag).

**Parameters:** `array $document`

Keys: `footer`, `policies`, `complimentaryClose`, `isRTL`.

```php
// Append a seasonal note to the footer.
add_filter( 'wcdn_document_data', function( $document ) {
    $document['footer'] .= '<br>Thank you for your holiday order!';
    return $document;
} );
```

---

### `wcdn_document_title`
**Since:** 7.0.0

Override the document type label used in admin UI and emails (e.g. "Invoice",
"Delivery Note").

**Parameters:** `string $title`, `string $template_type`

```php
add_filter( 'wcdn_document_title', function( $title, $template_type ) {
    if ( 'receipt' === $template_type ) {
        return 'Payment Confirmation';
    }
    return $title;
}, 10, 2 );
```

---

### `wcdn_watermark_text`
**Since:** 7.1.2

Override the watermark text displayed on a document programmatically, for
example based on the order status.

**Parameters:** `string $text`, `array $order`, `string $template`

```php
// Show "PAID" on completed orders, "UNPAID" on pending.
add_filter( 'wcdn_watermark_text', function( $text, $order, $template ) {
    if ( 'completed' === $order['status'] ) {
        return 'PAID';
    }
    if ( in_array( $order['status'], array( 'pending', 'on-hold' ), true ) ) {
        return 'UNPAID';
    }
    return $text;
}, 10, 3 );
```

---

## 6. Template Rendering Filters

---

### `wcdn_template_data`
**Since:** 7.0.0

Filter the complete data array passed to a template before any variables are
extracted. Gives access to `order`, `shop`, `document`, `settings`, and
`template` in a single filter.

**Parameters:** `array $data`, `string $template`, `string $type` (`pdf` or `html`)

```php
add_filter( 'wcdn_template_data', function( $data, $template, $type ) {
    // Inject a custom variable accessible as $my_var in base.php overrides.
    $data['my_var'] = 'hello';
    return $data;
}, 10, 3 );
```

---

### `wcdn_template_css`
**Since:** 7.0.0

Append or modify the combined CSS string (base + settings + context) that is
injected into the `<style>` tag of each rendered document.

**Parameters:** `string $css`, `string $context` (`pdf` or `html`), `array $data`

```php
// Add a custom font import for HTML previews.
add_filter( 'wcdn_template_css', function( $css, $context, $data ) {
    if ( 'html' === $context ) {
        $css .= '@import url("https://fonts.googleapis.com/css2?family=Lato&display=swap");';
        $css .= 'body { font-family: Lato, sans-serif; }';
    }
    return $css;
}, 10, 3 );
```

---

### `wcdn_dynamic_css`
**Since:** 7.0.0

Filter the CSS generated from the admin template settings (font sizes, colours,
alignments, etc.) before it is injected into the document.

**Parameters:** `string $css`, `array $settings`

```php
// Force the totals table text colour to black regardless of the setting.
add_filter( 'wcdn_dynamic_css', function( $css, $settings ) {
    $css .= '.wcdn-totals { color: #000 !important; }';
    return $css;
}, 10, 2 );
```

---

### `wcdn_locate_template`
**Since:** 7.0.0

Override the resolved path for a template file. Return an absolute path to
redirect the plugin to a different template file.

**Parameters:** `string|false $path`, `string $template`, `string $type`, `string $source` (`theme`, `plugin`, or `not_found`)

```php
// Use a completely custom template file for invoices.
add_filter( 'wcdn_locate_template', function( $path, $template, $type, $source ) {
    if ( 'invoice' === $template ) {
        return get_stylesheet_directory() . '/my-invoice-template.php';
    }
    return $path;
}, 10, 4 );
```

---

### `wcdn_allowed_statuses_{template}`
**Since:** 7.0.0

Control which WooCommerce order statuses enable a particular document type.
Replace `{template}` with the document type key.

**Parameters:** `array $statuses` (array of status slugs without the `wc-` prefix)

```php
// Allow packing slips only for "processing" and "on-hold" orders.
add_filter( 'wcdn_allowed_statuses_packingslip', function( $statuses ) {
    return array( 'processing', 'on-hold' );
} );

// Disable delivery notes for cancelled orders.
add_filter( 'wcdn_allowed_statuses_deliverynote', function( $statuses ) {
    return array_diff( $statuses, array( 'cancelled' ) );
} );
```

---

## 7. Print View Filters

---

### `wcdn_print_document_title`
**Since:** 7.1.2

Customise the browser tab title shown in the print preview window.

**Parameters:** `string $title`, `array $order_ids`, `string $template`

```php
add_filter( 'wcdn_print_document_title', function( $title, $order_ids, $template ) {
    return 'My Store — ' . ucfirst( $template );
}, 10, 3 );
```

---

### `wcdn_format_phone_number`
**Since:** 7.1.0

Format the billing phone number before it is rendered in the document.

**Parameters:** `string $phone`, `string $country` (ISO 3166-1 alpha-2 country code)

```php
// Add country dial code prefix for international orders.
add_filter( 'wcdn_format_phone_number', function( $phone, $country ) {
    if ( 'US' === $country && ! str_starts_with( $phone, '+1' ) ) {
        return '+1 ' . $phone;
    }
    return $phone;
}, 10, 2 );
```

---

### `wcdn_template_types_from_order`
**Since:** 7.0.0

Control which document types are available for a specific order. Return a
filtered array of template keys.

**Parameters:** `array $types` (e.g. `['invoice', 'receipt']`), `WC_Order $order`

```php
// Only show invoices for B2B orders (those with a VAT number).
add_filter( 'wcdn_template_types_from_order', function( $types, $order ) {
    $vat = get_post_meta( $order->get_id(), '_billing_vat', true );
    if ( ! $vat ) {
        return array_diff( $types, array( 'invoice' ) );
    }
    return $types;
}, 10, 2 );
```

---

### `wcdn_administrator_emails`
**Since:** 7.0.0

Override the list of admin email addresses used when sending documents to
administrators.

**Parameters:** `array $emails`

```php
add_filter( 'wcdn_administrator_emails', function( $emails ) {
    $emails[] = 'accounts@example.com';
    return $emails;
} );
```

---

## 8. Email Filters

---

### `wcdn_custom_email_message_body`
**Since:** 7.0.0

Override the HTML email body when the plugin sends a document as a custom
email (not a WooCommerce order email attachment).

**Parameters:** `string $message`, `WC_Order $order`, `string $template`

```php
add_filter( 'wcdn_custom_email_message_body', function( $message, $order, $template ) {
    return str_replace( 'Your order', 'Order #' . $order->get_order_number(), $message );
}, 10, 3 );
```

---

### `wcdn_custom_email_recipients`
**Since:** 7.0.0

Override the recipient list for custom document emails.

**Parameters:** `array $emails`, `WC_Order $order`, `string $template`

```php
// CC the sales team on every invoice email.
add_filter( 'wcdn_custom_email_recipients', function( $emails, $order, $template ) {
    if ( 'invoice' === $template ) {
        $emails[] = 'sales@example.com';
    }
    return $emails;
}, 10, 3 );
```

---

### `wcdn_print_view_in_browser_text_in_email`
**Since:** 4.9.0

Change the "Print" link text injected into WooCommerce order emails.

**Parameters:** `string $text`

```php
add_filter( 'wcdn_print_view_in_browser_text_in_email', function( $text ) {
    return 'View & Print Your Invoice';
} );
```

---

## 9. PDF Generation Filters

---

### `wcdn_pdf_paper_size`
**Since:** 7.0.0

Override the paper size used when generating PDFs.

**Parameters:** `string $size` (e.g. `A4`, `Letter`, `Legal`), `int $order_id`

```php
// Use US Letter for orders shipped to the US.
add_filter( 'wcdn_pdf_paper_size', function( $size, $order_id ) {
    $order = wc_get_order( $order_id );
    if ( $order && 'US' === $order->get_shipping_country() ) {
        return 'Letter';
    }
    return $size;
}, 10, 2 );
```

---

### `wcdn_pdf_orientation`
**Since:** 7.0.0

Override the page orientation.

**Parameters:** `string $orientation` (`portrait` or `landscape`), `int $order_id`

```php
// Use landscape for packing slips — this filter does not receive $template,
// so check a request variable or global context if needed.
add_filter( 'wcdn_pdf_orientation', function( $orientation, $order_id ) {
    return 'landscape';
}, 10, 2 );
```

---

### `wcdn_pdf_dpi`
**Since:** 7.0.0

Override the DPI used by Dompdf when rendering the PDF.

**Parameters:** `int $dpi` (default: `150`)

```php
// Higher DPI for better image quality at the cost of larger file size.
add_filter( 'wcdn_pdf_dpi', function( $dpi ) {
    return 200;
} );
```

---

### `wcdn_pdf_filename`
**Since:** 7.0.0

Override the filename of the generated PDF file (without directory path).

**Parameters:** `string $filename`, `int $order_id`, `string $template`

```php
add_filter( 'wcdn_pdf_filename', function( $filename, $order_id, $template ) {
    $order = wc_get_order( $order_id );
    return $template . '-' . $order->get_order_number() . '.pdf';
}, 10, 3 );
```

---

### `wcdn_pdf_generated_file`
**Since:** 7.0.0

Filter the path to a freshly generated (or cached) PDF file. Return a
different path to swap in a pre-generated file.

**Parameters:** `string $file` (absolute path), `int $order_id`, `string $template`

```php
// Log every PDF generation.
add_filter( 'wcdn_pdf_generated_file', function( $file, $order_id, $template ) {
    error_log( "WCDN PDF generated: {$template} for order {$order_id} → {$file}" );
    return $file;
}, 10, 3 );
```

---

### `wcdn_pdf_zoom_mode`
**Since:** 7.1.2

Override the PDF zoom mode. `layout` scales the entire document proportionally;
`text` scales only font sizes.

**Parameters:** `string $mode` (`layout` or `text`)

```php
add_filter( 'wcdn_pdf_zoom_mode', function( $mode ) {
    return 'text';
} );
```

---

## 10. PDF Locale Font Filters

These filters let you supply custom fonts for non-Latin scripts (CJK,
Arabic, Hebrew, etc.) without using the built-in Google Fonts downloader.

---

### `wcdn_pdf_locale_font_config`
**Since:** 7.0.0

Override or extend the built-in locale font configuration.

**Parameters:** `array|null $config`, `string $locale`

```php
add_filter( 'wcdn_pdf_locale_font_config', function( $config, $locale ) {
    if ( 'ja' === substr( $locale, 0, 2 ) ) {
        return array(
            'name'         => 'NotoSansJP',
            'google_name'  => 'Noto+Sans+JP',
            'display_name' => 'Noto Sans JP',
        );
    }
    return $config;
}, 10, 2 );
```

---

### `wcdn_pdf_locale_font_path`
**Since:** 7.0.0

Provide a local absolute path to a TTF/OTF file for the current locale.
When a path is returned, the Google Fonts downloader is skipped.

**Parameters:** `string $path` (empty string by default), `string $locale`

```php
add_filter( 'wcdn_pdf_locale_font_path', function( $path, $locale ) {
    if ( str_starts_with( $locale, 'zh_' ) ) {
        return WP_CONTENT_DIR . '/fonts/NotoSansSC-Regular.ttf';
    }
    return $path;
}, 10, 2 );
```

---

### `wcdn_pdf_locale_font_url`
**Since:** 7.0.0

Provide a remote TTF/OTF URL for the current locale. Dompdf will download
and cache it. Used when `wcdn_pdf_locale_font_path` returns empty.

**Parameters:** `string $url` (empty string by default), `string $locale`

```php
add_filter( 'wcdn_pdf_locale_font_url', function( $url, $locale ) {
    if ( str_starts_with( $locale, 'ko' ) ) {
        return 'https://fonts.example.com/NotoSansKR-Regular.ttf';
    }
    return $url;
}, 10, 2 );
```

---

### `wcdn_pdf_use_google_fonts_for_locale`
**Since:** 7.0.0

Disable the automatic Google Fonts download for locale fonts entirely.
Return `false` to prevent any network request.

**Parameters:** `bool $enabled` (default: `true`)

```php
// Disable Google Fonts (supply fonts via wcdn_pdf_locale_font_path instead).
add_filter( 'wcdn_pdf_use_google_fonts_for_locale', '__return_false' );
```
