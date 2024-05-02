new Vue({
    el: '#deliverynote_template',
    data() {
        return {
            deliverynote: {
                document_setting_title: settings_object_deliverynotes.document_setting.document_setting_title,
                document_setting_font_size: settings_object_deliverynotes.document_setting.document_setting_font_size,
                document_setting_text_align: settings_object_deliverynotes.document_setting.document_setting_text_align,
                document_setting_text_colour: settings_object_deliverynotes.document_setting.document_setting_text_colour,

                company_name_font_size: settings_object_deliverynotes.company_name.company_name_font_size,
                company_name_text_align: settings_object_deliverynotes.company_name.company_name_text_align,
                company_name_text_colour: settings_object_deliverynotes.company_name.company_name_text_colour,

                company_address_text_align: settings_object_deliverynotes.company_address.company_address_text_align,
                company_address_font_size: settings_object_deliverynotes.company_address.company_address_font_size,
                company_address_text_colour: settings_object_deliverynotes.company_address.company_address_text_colour,

                billing_address_title: settings_object_deliverynotes.billing_address.billing_address_title,
                billing_address_text_align: settings_object_deliverynotes.billing_address.billing_address_text_align,
                billing_address_text_colour: settings_object_deliverynotes.billing_address.billing_address_text_colour,

                shipping_address_title: settings_object_deliverynotes.shipping_address.shipping_address_title,
                shipping_address_text_align: settings_object_deliverynotes.shipping_address.shipping_address_text_align,
                shipping_address_text_colour: settings_object_deliverynotes.shipping_address.shipping_address_text_colour,

                invoice_number_text: settings_object_deliverynotes.invoice_number.invoice_number_text,
                invoice_number_font_size: settings_object_deliverynotes.invoice_number.invoice_number_font_size,
                invoice_number_style: settings_object_deliverynotes.invoice_number.invoice_number_style,
                invoice_number_text_colour: settings_object_deliverynotes.invoice_number.invoice_number_text_colour,

                order_number_text: settings_object_deliverynotes.order_number.order_number_text,
                order_number_font_size: settings_object_deliverynotes.order_number.order_number_font_size,
                order_number_style: settings_object_deliverynotes.order_number.order_number_style,
                order_number_text_colour: settings_object_deliverynotes.order_number.order_number_text_colour,

                order_date_text: settings_object_deliverynotes.order_date.order_date_text,
                order_date_font_size: settings_object_deliverynotes.order_date.order_date_font_size,
                order_date_style: settings_object_deliverynotes.order_date.order_date_style,
                order_date_text_colour: settings_object_deliverynotes.order_date.order_date_text_colour,

                payment_method_text: settings_object_deliverynotes.payment_method.payment_method_text,
                payment_method_font_size: settings_object_deliverynotes.payment_method.payment_method_font_size,
                payment_method_style: settings_object_deliverynotes.payment_method.payment_method_style,
                payment_method_text_colour: settings_object_deliverynotes.payment_method.payment_method_text_colour,

                customer_note_title: settings_object_deliverynotes.customer_note.customer_note_title,
                customer_note_font_size: settings_object_deliverynotes.customer_note.customer_note_font_size,
                customer_note_text_colour: settings_object_deliverynotes.customer_note.customer_note_text_colour,

                complimentary_close_font_size: settings_object_deliverynotes.complimentary_close.complimentary_close_font_size,
                complimentary_close_text_colour: settings_object_deliverynotes.complimentary_close.complimentary_close_text_colour,

                policies_font_size: settings_object_deliverynotes.policies.policies_font_size,
                policies_text_colour: settings_object_deliverynotes.policies.policies_text_colour,

                footer_font_size: settings_object_deliverynotes.footer.footer_font_size,
                footer_text_colour: settings_object_deliverynotes.footer.footer_text_colour,

                // Checkbox.
                document_setting: settings_object_deliverynotes.document_setting.active,
                company_logo: settings_object_deliverynotes.company_logo.active,
                company_name: settings_object_deliverynotes.company_name.active,
                company_address: settings_object_deliverynotes.company_address.active,

                invoice_number: settings_object_deliverynotes.invoice_number.active,
                order_number: settings_object_deliverynotes.order_number.active,
                order_date: settings_object_deliverynotes.order_date.active,
                payment_method: settings_object_deliverynotes.payment_method.active,

                billing_address: settings_object_deliverynotes.billing_address.active,
                shipping_address: settings_object_deliverynotes.shipping_address.active,

                email_address: settings_object_deliverynotes.email_address.active,
                phone_number: settings_object_deliverynotes.phone_number.active,
                customer_note: settings_object_deliverynotes.customer_note.active,
                complimentary_close: settings_object_deliverynotes.complimentary_close.active,
                policies: settings_object_deliverynotes.policies.active,
                footer: settings_object_deliverynotes.footer.active,
                display_price_product_table: settings_object_deliverynotes.display_price_product_table.active,

            }
        }
    },
    mounted: function() {
        var self = this;
    },
})