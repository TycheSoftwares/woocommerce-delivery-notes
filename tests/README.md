# Refund credit-note regression check

This uses the real WooCommerce email attachment and PDF rendering paths; it does
not require a new PHPUnit runner or mocked order classes.

Run only on a disposable **local/development** WordPress site with WooCommerce and
this plugin activated. Create an empty private artifact directory outside the web
root, then run from that site's WP-CLI directory:

```sh
WCDN_RUN_REFUND_TESTS=1 WCDN_TEST_ARTIFACT_DIR=/absolute/private/artifacts \
  wp eval-file /path/to/woocommerce-delivery-notes/tests/refund-credit-notes.php
```

The script intercepts mail, disables deferred transactional mail for the test,
uses synthetic products/orders/refunds and removes those marked fixtures in
`finally`. It restores the settings/counter values it changes. PHP fatal errors
can bypass cleanup, so use a disposable site and retain its backup. The plugin's
normal install/migration and scheduled cleanup initialization occur before this
script; it is not a database reset or an uninstall helper.

Coverage includes two amount-only partial refunds, an itemized refund, a final
refund, distinct refund-scoped PDF files, immutable earlier files, resending an
older refund, accurate amount-only rows, visible refund totals and unchanged
invoice PDFs. `results.json` and `refund-0.pdf` through `refund-3.pdf` are retained
in the private artifact directory. Inspect their actual monetary text as well:

```sh
pdftotext -layout /absolute/private/artifacts/refund-0.pdf -
pdftotext -layout /absolute/private/artifacts/refund-1.pdf -
```

They must show the first refund's EUR 3 and second refund's EUR 2 respectively,
not the original product's EUR 10. Currency formatting follows the test site's
locale. These checks do not establish tax-law compliance or migrate historical
invoices.
